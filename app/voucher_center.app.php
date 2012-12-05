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
	    	/* ��ǰ�û�������Ϣ*/
            $this->_get_user_info();
	    	if (!IS_POST)
    		{
    			/* ��ǰλ�� */
	        	$this->_curlocal(LANG::get('member_center'),    url('app=member'),
	                         LANG::get('overview'));
	      		/* ��ǰ�û����Ĳ˵� */
	       		$this->_curitem('credit');
	        	$this->_config_seo('title', Lang::get('member_center'));
    			$this->display("voucher_center.form.html");
    		}
    		else
    		{
	    		$balance = empty($_POST['balance']) ? 0 : floatval($_POST['balance']);	
	    		if ($balance <= 0)
	    		{
	    			$this->show_warning("��������ȷ�ĳ�ֵ���");
	    			return;
	    		}
	    		$rechargeorder_mod = & m('rechargeorder');
	    		$chargeinfo = $rechargeorder_mod->get("buyer_id = {$this->visitor->get('user_id')} AND status=0 AND type=1 AND payment_id=0");
	    		
	    		//�ж��Ƿ��������ɵ��û���ֵ��������
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
	    				$this->show_warning('���ɶ���ʧ��');
	    				return;
	    			}
	    		}else
	    		{
		    		//����֧������
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
	    			$this->show_warning('���ɶ���ʧ��');
	    			return;
	    		}
	    		
		    	$payment_model =& m('payment');
			
	            /* ����û��ѡ��֧����ʽ��������ѡ��֧����ʽ */
	            $payments = $payment_model->get_alipay();
	            if (empty($payments))
	            {
	                $this->show_warning('��ֵ�����Ѿ��رգ�');
	
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
		    	/* ��ǰλ�� */
	        	$this->_curlocal(LANG::get('member_center'),    url('app=member'),
	                         LANG::get('overview'));
	      		/* ��ǰ�û����Ĳ˵� */
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
	    		$this->show_warning('��Ч��֧����ʽ');
	    		return;
	    	}
	    	$payment_model =& m('payment');
	    	$_payment_info = $payment_model->get($payment_id);
	    	/* ��֤֧����ʽ�Ƿ���ã������ڰ������У�������ʹ�� */
	        if (!$payment_model->in_white_list($_payment_info['payment_code']))
	        {
	            $this->show_warning('payment_disabled_by_system');
	
	            return;
	        }
	        $payment_info  = $payment_model->get("payment_code = '{$_payment_info['payment_code']}' AND store_id=0");
	
	        /* ����̨û������֧����ʽ��������ʹ�� */
	        if (!$payment_info['enabled'])
	        {
	            $this->show_warning('payment_disabled');
	
	            return;
	        }
	        $rechargeorder_mod = & m('rechargeorder');
	        //д�붨��֧����ʽ����Ϣ
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
	     *    ���ɶ�����
	     *
	     *    @author    Garbin
	     *    @return    string
	     */
	    function _get_order_sn()
	    {
	        /* ѡ��һ������ķ��� */
	        mt_srand((double) microtime() * 1000000);
	        $timestamp = gmtime();
	        $y = date('y', $timestamp);
	        $z = date('z', $timestamp);
	        $order_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
	
	        $model_order =& m('rechargeorder');
	        $orders = $model_order->find('order_sn=' . $order_sn);
	        if (empty($orders))
	        {
	            /* �����ʹ����������� */
	            return $order_sn;
	        }
	        /* ������ظ��ģ����������� */
	        return $this->_gen_order_sn();
	    }
	}
?>