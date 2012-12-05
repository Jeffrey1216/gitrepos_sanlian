<?php

/**
 *    ����̨������������ݵ�������Ա�Ľ�ɫ����ֻ��Ҫ����Ķ�����������Ա������Ա����������������רע���������
 *
 *    @author    Garbin
 */
class Store_cashierApp extends StoreadminbaseApp
{
    /**
     *    �����ṩ�Ķ�����Ϣ����֧��
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    {
        /* �ⲿ�ṩ������ */
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {
            $this->show_storeadmin_warning('no_such_order');

            return;
        }
        
        /* �ڲ����ݶ���������,��ȡ�ն���Ǯ��ʹ���ĸ�֧���ӿ� */
        $order_model =& m('storeorder');
        $order_info  = $order_model->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_storeadmin_warning('no_such_order');

            return;
        }
        /* ������Ч���ж� */
        if ($order_info['payment_code'] != 'cod' && $order_info['status'] != ORDER_PENDING)
        {
            $this->show_storeadmin_warning('no_such_order');
            return;
        }
        
        $orderextm_model =& m('storeorderextm');
        $info = $orderextm_model->get($order_id);
        $order_info +=$info;
            
        $payment_model =& m('payment');
        /* ����û��ѡ��֧����ʽ��������ѡ��֧����ʽ */
        $payments = $payment_model->get_balance();

        if (empty($payments))
        {
            $this->show_storeadmin_warning('store_no_payment');

            return;
        }
        //�鿴�������
    	$model_member =& m('member');
        $member_info  = $model_member->get($order_info['buyer_id']);
        $member_info['money'] = $member_info['money'] - $member_info['frozen_money'];
        
        $this->assign('money', $member_info['money']);
        $this->assign('payments', $payments);
        $this->assign('order', $order_info);

        $this->display('store_cashier.payment.html');
    }

    /**
     *    ȷ��֧��
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
        	$this->show_storeadmin_warning('����ϵ�������ȷ�϶�����Ϣ��');

            return;
        }

        /* ��֤֧����ʽ */
        $payment_model =& m('payment');
        $payment_info  = $payment_model->get($payment_id);
        if (!$payment_info)
        {
            $this->show_storeadmin_warning('no_such_payment');

            return;
        }

        /* ����֧����ʽ */
        $edit_data = array(
            'payment_id'    =>  $payment_info['payment_id'],
            'payment_code'  =>  $payment_info['payment_code'],
            'payment_name'  =>  $payment_info['payment_name'],
        );

        $order_model->edit($order_id, $edit_data);
        
        /* ��ʼ֧�� */
        /* ����֧��URL��� */
        $payment    = $this->_get_payment($payment_info['payment_code'], $payment_info);

        //ֱ��֧��
        $result = $payment->_pay($order_info);
        if ($result == -1 )
        {
            $this->show_storeadmin_warning('balance_shortage',
            'ȥ��ֵ..', 'index.php?app=shopadmin&act=recharge');
            return;
        }else
        {
        	$this->show_storeadmin_message('����֧���ɹ�',
            '����..', 'index.php?app=shopadmin&act=store_info');
        }  
    }

    /**
     *    ����֧����Ϣ
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

        /* ����֧����ɲ�����pay_message,���͸����Ҹ��������ʾ�ʼ� */
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

