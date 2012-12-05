<?php
	class Voucher_centerApp extends MemberbaseApp
	{
		var $_member_mod;
	    function __construct()
	    {
	        $this->Voucher_centerApp();
	    }
	    function Voucher_centerApp()
	    {
	        parent::__construct();
	        $this->_member_mod = &m('member');
	    }
	    
	    function index()
	    {
	    	/* 当前用户基本信息*/
            $this->_get_user_info();
	    	if (!IS_POST)
    		{
    			/* 当前位置 */
	        	$this->_curlocal(LANG::get('member_center'),    url('app=member'),
	                         LANG::get('overview'));
	      		/* 当前用户中心菜单 */
	       		$this->_curitem('credit');
	        	$this->_config_seo('title', Lang::get('member_center'));
    			$this->display("voucher_center.form.html");
    		}
    		else
    		{
	    		$balance = empty($_POST['balance']) ? 0 : floatval($_POST['balance']);	
	    		if ($balance <= 0)
	    		{
	    			$this->show_warning("请填入正确的充值金额");
	    			return;
	    		}
	    		$rechargeorder_mod = & m('rechargeorder');
	    		$chargeinfo = $rechargeorder_mod->get("buyer_id = {$this->visitor->get('user_id')} AND status=0 AND type=1 AND payment_id=0");
	    		
	    		//判断是否有已生成的用户充值订单存在
	    		if ($chargeinfo)
	    		{
	    			$data['add_time'] = gmtime();
	    			$data['balance']  = $balance;
	    			$data['order_sn'] = $this->_get_order_sn();
	    			$order_id = $rechargeorder_mod->edit($chargeinfo['order_id'],$data);
	    			if ($order_id)
	    			{
	    				$chargeinfo['balance']  = $data['balance'];
	    				$chargeinfo['order_sn'] = $data['order_sn'];
	    			}else
	    			{
	    				$this->show_warning('生成订单失败');
	    				return;
	    			}
	    		}else
	    		{
		    		//创建支付定单
		    		$data = array(
		    			'balance' => $balance,
		    			'buyer_id' => $this->visitor->get('user_id'),
		    			'buyer_name' => $this->visitor->get('user_name'),
		    			'order_sn' => $this->_get_order_sn(),
		    			'status' => 0,
		    			'add_time' => gmtime(),
		    			'casher_time' => 0,
		    			'type'	=> 1
		    		); 
		    		$order_id = $rechargeorder_mod->add($data);
		    		$chargeinfo = $rechargeorder_mod->get($order_id);
	    		}
	    		if (!$chargeinfo)
	    		{
	    			$this->show_warning('生成订单失败');
	    			return;
	    		}
	    		
		    	$payment_model =& m('payment');
			
	            /* 若还没有选择支付方式，则让其选择支付方式 */
	            $payments = $payment_model->get_alipay();
	            if (empty($payments))
	            {
	                $this->show_warning('充值功能已经关闭！');
	
	                return;
	            }
	
	            $all_payments = array('online' => array(), 'offline' => array());
	            foreach ($payments as $key => $payment)
	            {
	                if ($payment['is_online'])
	                {
	                    $all_payments['online'][] = $payment;
	                }
	            }
	            $this->assign('order', $chargeinfo);
	            $this->assign('payments', $all_payments);
		    	/* 当前位置 */
	        	$this->_curlocal(LANG::get('member_center'),    url('app=member'),
	                         LANG::get('overview'));
	      		/* 当前用户中心菜单 */
	       		$this->_curitem('overview');
				$this->_config_seo('title', Lang::get('member_center'));
		    	$this->display('voucher_center.payment.html');
	   		}
		}
		function goto_pay()
	    {
	    	$payment_id = empty($_POST['payment_id']) ? 0 : intval($_POST['payment_id']);
	    	$order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
	    	if ($payment_id == 0)
	    	{
	    		$this->show_warning('无效的支付方式');
	    		return;
	    	}
	    	$payment_model =& m('payment');
	    	$_payment_info = $payment_model->get($payment_id);
	    	/* 验证支付方式是否可用，若不在白名单中，则不允许使用 */
	        if (!$payment_model->in_white_list($_payment_info['payment_code']))
	        {
	            $this->show_warning('payment_disabled_by_system');
	
	            return;
	        }
	        $payment_info  = $payment_model->get("payment_code = '{$_payment_info['payment_code']}' AND store_id=0");
	
	        /* 若后台没有启用支付方式，则不允许使用 */
	        if (!$payment_info['enabled'])
	        {
	            $this->show_warning('payment_disabled');
	
	            return;
	        }
	        $rechargeorder_mod = & m('rechargeorder');
	        //写入定单支付方式等信息
	        $rechargeorder_mod->edit($order_id, array(
	        	'payment_id' => $payment_id,
	        	'payment_code' => $_payment_info['payment_code'],
	        ));
	        $order_info = $rechargeorder_mod->get($order_id);
	        $payment    = $this->_get_payment($_payment_info['payment_code'], $payment_info);
	        $payment_form = $payment->get_recharge_payform($order_info);
	        $this->assign('payform', $payment_form);
	        $this->assign('order', $order_info);
	        header('Content-Type:text/html;charset=' . CHARSET);
	        $this->display('voucher_center.payform.html');
	    }
		 /**
	     *    生成订单号
	     *
	     *    @author    Garbin
	     *    @return    string
	     */
	    function _get_order_sn()
	    {
	        /* 选择一个随机的方案 */
	        mt_srand((double) microtime() * 1000000);
	        $timestamp = gmtime();
	        $y = date('y', $timestamp);
	        $z = date('z', $timestamp);
	        $order_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
	
	        $model_order =& m('rechargeorder');
	        $orders = $model_order->find('order_sn=' . $order_sn);
	        if (empty($orders))
	        {
	            /* 否则就使用这个订单号 */
	            return $order_sn;
	        }
	        /* 如果有重复的，则重新生成 */
	        return $this->_gen_order_sn();
	    }
	}
?>