<?php

/**
 *    收银台控制器，其扮演的是收银员的角色，你只需要将你的订单交给收银员，收银员按订单来收银，她专注于这个过程
 *
 *    @author    Garbin
 */
class Store_cashierApp extends StoreadminbaseApp
{
    /**
     *    根据提供的订单信息进行支付
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    {
        /* 外部提供订单号 */
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {
            $this->show_storeadmin_warning('no_such_order');

            return;
        }
        
        /* 内部根据订单号收银,获取收多少钱，使用哪个支付接口 */
        $order_model =& m('storeorder');
        $order_info  = $order_model->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_storeadmin_warning('no_such_order');

            return;
        }
        /* 订单有效性判断 */
        if ($order_info['payment_code'] != 'cod' && $order_info['status'] != ORDER_PENDING)
        {
            $this->show_storeadmin_warning('no_such_order');
            return;
        }
        
        $orderextm_model =& m('storeorderextm');
        $info = $orderextm_model->get($order_id);
        $order_info +=$info;
            
        $payment_model =& m('payment');
        /* 若还没有选择支付方式，则让其选择支付方式 */
        $payments = $payment_model->get_balance();

        if (empty($payments))
        {
            $this->show_storeadmin_warning('store_no_payment');

            return;
        }
        //查看可用余额
    	$model_member =& m('member');
        $member_info  = $model_member->get($order_info['buyer_id']);
        $member_info['money'] = $member_info['money'] - $member_info['frozen_money'];
        
        $this->assign('money', $member_info['money']);
        $this->assign('payments', $payments);
        $this->assign('order', $order_info);

        $this->display('store_cashier.payment.html');
    }

    /**
     *    确认支付
     *
     *    @author    Garbin
     *    @return    void
     */
    function goto_pay()
    {
    	
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $payment_id = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;
        if (!$order_id)
        {
            $this->show_storeadmin_warning('no_such_order');

            return;
        }
        if (!$payment_id)
        {
            $this->show_storeadmin_warning('no_such_payment');

            return;
        }
        $order_model =& m('storeorder');
        $order_info  = $order_model->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_storeadmin_warning('no_such_order');

            return;
        }
        
        if ($order_info['op_status']!=2)
        {
        	$this->show_storeadmin_warning('请联系店面管理部确认订单信息！');

            return;
        }

        /* 验证支付方式 */
        $payment_model =& m('payment');
        $payment_info  = $payment_model->get($payment_id);
        if (!$payment_info)
        {
            $this->show_storeadmin_warning('no_such_payment');

            return;
        }

        /* 保存支付方式 */
        $edit_data = array(
            'payment_id'    =>  $payment_info['payment_id'],
            'payment_code'  =>  $payment_info['payment_code'],
            'payment_name'  =>  $payment_info['payment_name'],
        );

        $order_model->edit($order_id, $edit_data);
        
        /* 开始支付 */
        /* 生成支付URL或表单 */
        $payment    = $this->_get_payment($payment_info['payment_code'], $payment_info);

        //直接支付
        $result = $payment->_pay($order_info);
        if ($result == -1 )
        {
            $this->show_storeadmin_warning('balance_shortage',
            '去充值..', 'index.php?app=shopadmin&act=recharge');
            return;
        }else
        {
        	$this->show_storeadmin_message('订单支付成功',
            '返回..', 'index.php?app=shopadmin&act=store_info');
        }  
    }

    /**
     *    线下支付消息
     *
     *    @author    Garbin
     *    @return    void
     */
    function offline_pay()
    {
        if (!IS_POST)
        {
            return;
        }
        $order_id       = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $pay_message    = isset($_POST['pay_message']) ? trim($_POST['pay_message']) : '';
        if (!$order_id)
        {
            $this->show_storeadmin_message('no_such_order');
            return;
        }
        if (!$pay_message)
        {
            $this->show_storeadmin_warning('no_pay_message');

            return;
        }
        $order_model =& m('storeorder');
        $order_info  = $order_model->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_storeadmin_warning('no_such_order');

            return;
        }
        $edit_data = array(
            'pay_message' => $pay_message
        );

        $order_model->edit($order_id, $edit_data);

        /* 线下支付完成并留下pay_message,发送给卖家付款完成提示邮件 */
        $model_member =& m('member');
        $seller_info   = $model_member->get($order_info['seller_id']);
        $mail = get_mail('toseller_offline_pay_notify', array('order' => $order_info, 'pay_message' => $pay_message));
        $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

        $this->show_storeadmin_message('pay_message_successed',
            'view_order',   'index.php?app=buy_myorders',
            'close_window', 'javascript:window.close();');
    }

    function _goto_pay($order_id)
    {
        header('Location:index.php?app=store_cashier&order_id=' . $order_id);
    }
}

?>

