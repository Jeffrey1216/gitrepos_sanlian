<?php

/**
 *    ����̨������������ݵ�������Ա�Ľ�ɫ����ֻ��Ҫ����Ķ�����������Ա������Ա����������������רע���������
 *
 *    @author    Garbin
 */
class CashierApp extends ShoppingbaseApp
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
                   	//����ű�������js
        	$this->import_resource(array(
        	 'script' =>   array(
        		array(
                    'path' => 'jquery.ui/jquery.bgiframe-2.1.2.js',
                    'attr' => '',
                ),
        		array(
                    'path' => 'jquery.ui/jquery.ui.core.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.widget.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.mouse.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.button.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.draggable.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.position.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.resizable.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.dialog.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.effect.js',
                	'attr' => '',
                ),
               ),
               'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
              )
             );
        if (!$order_id)
        {
            $this->show_warning('no_such_order');
			
            return;
        }
        /* �ڲ����ݶ���������,��ȡ�ն���Ǯ��ʹ���ĸ�֧���ӿ� */
        $order_model =& m('order');
        $order_info  = $order_model->getRow("select o.*,s.address from pa_order o left join pa_store s on o.seller_id = s.store_id where order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_warning('no_such_order');
            return;
        }
        $this->assign("store_id" , STORE_ID);
    	$orderextm_mod = & m('orderextm');
        $order_extm_info = $orderextm_mod->get($order_id);
		if($order_info['pay_type'] == 2 ) {
			if ($order_info['status'] == ORDER_PENDING) //����δ֧��״̬��
			{
				//���½���Ϊ������ 
	            $param = array(
	            	'status' => ORDER_ACCEPTED,
	            	'payment_id' => 99,
	            	'payment_name' => '����֧��',
	            	'payment_code' => 'credit',
	            	'pay_time' => time(),
	            	'short_code' => create_short_code(),
	            );
	            changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['use_credit'],SUBTRACK_CREDIT);
	    		$log_info1 = array(
		            	'user_id'	    => intval($order_info['buyer_id']),
		            	'user_credit'	        => '-'.$order_info['use_credit'],
		            	'change_desc'   => '������ID��'.$order_info['seller_id'].',��������'.$order_info['seller_name'].'�� ����ʹ�û��֣�'.$order_info['use_credit'].'PL',
		            	'order_id'      => $order_info['order_id'],
			            'change_time'	=>	time(),
		            	'change_type'	=> 	2,
		            ); 
		        add_account_log($log_info1);
	            $order_model->edit($order_id,$param);
	            $this->assign('order', $order_info);
	            $model_order =& m('order');
           		/* ��ȥ��Ʒ��� */
            	$model_order->change_stock('-', $order_id);
            	
            	$smslog =&  m('smslog'); 
            	import('class.smswebservice');    //������ŷ�����
        		$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
	        		
            	if ($order_info['seller_id'] == STORE_ID)
        		{
				    $smscontent = "�ͻ�:{$order_info['buyer_name']}���Ѿ��ɹ����������:{$order_info['order_sn']}���뾡�췢��";
				    $mobile = DEF_MOBILE;
				    $verifytype = "buygoods";	
	        		$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ�����֤�����
	        		//���ŷ��ͳɹ�
	        		if ($result == 0) 
	        		{
	        			//����֤��д��SESSION
	        			$time = time();
	        			//ִ�ж�����־д�����
	        			$smsdata['mobile'] = $mobile;
	        			$smsdata['smscontent'] = $smscontent;
	        			$smsdata['type'] = $verifytype; //ע����֤����
	        			$smsdata['sendtime'] = $time;
	        			$smslog->add($smsdata);
	       			}
        		}else 
        		{
        			$smscontent = "�𾴵�:{$order_info['buyer_name']}�����Ѿ��ɹ�������Ķ�����:{$order_info['order_sn']}��ȡ����֤��:{$param['short_code']}��ȡ����ַ:{$order_info['address']}���뾡�쵽��ȡ��";
				    $mobile = $order_info['buyer_mobile'];
				    $verifytype = "buygoodsverify";	
			      	$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ������Ѳ���
			      	//���ŷ��ͳɹ�
			        if ($result == 0) 
			        {
			        	$time = time();
			        	//ִ�ж�����־д�����
			        	$smsdata['mobile'] = $mobile;
			        	$smsdata['smscontent'] = $smscontent;
			        	$smsdata['type'] = $verifytype; //��������
			        	$smsdata['sendtime'] = $time;
			       		$smslog->add($smsdata);
			       	}
        		}
	        	$this->display('paynotify.index.html');
	        	return;
			}
		}
        
        //�û��Ѿ����֧���Ķ���
        if ($order_info['status'] == ORDER_ACCEPTED)
        {
        	$this->assign('order', $order_info);
        	$this->display('paynotify.index.html');
        	return;
        }
        /* ������Ч���ж� */
        if ($order_info['payment_code'] != 'cod' && $order_info['status'] != ORDER_PENDING)
        {
            $this->show_warning('no_such_order');
            return;
        }
        $payment_model =& m('payment');
        /* ����û��ѡ��֧����ʽ��������ѡ��֧����ʽ */
            $payments = $payment_model->get_enabled(0);
           
            if (empty($payments))
            {
                $this->show_warning('û�п���֧����ʽ��');

                return;
            }
            /* �ҳ����ͷ�ʽ���ж��Ƿ����ʹ�û������� */
            $model_extm =& m('orderextm');
            $consignee_info = $model_extm->get($order_id);
            if (!empty($consignee_info))
            {
                /* ��Ҫ���ͷ�ʽ */
                $model_shipping =& m('shipping');
                $shipping_info = $model_shipping->get($consignee_info['shipping_id']);
                $cod_regions   = unserialize($shipping_info['cod_regions']);
                $cod_usable = true;//Ĭ�Ͽ���
                if (is_array($cod_regions) && !empty($cod_regions))
                {
                    /* ȡ��֧�ֻ�����������������¼����� */
                    $all_regions = array();
                    $model_region =& m('region');
                    foreach ($cod_regions as $region_id => $region_name)
                    {
                        $all_regions = array_merge($all_regions, $model_region->get_descendant($region_id));
                    }

                    /* �鿴������ָ���ĵ����Ƿ��ڿɻ�������ĵ����б��У�������ڣ�����ʾ��������ĸ��ʽ */
                    if (!in_array($consignee_info['region_id'], $all_regions))
                    {
                        $cod_usable = false;
                    }
                }
                else
                {
                    $cod_usable = false;
                }
                if (!$cod_usable)
                {
                    /* ���б���ȥ����������ķ�ʽ */
                    foreach ($payments as $_id => $_info)
                    {
                        if ($_info['payment_code'] == 'cod')
                        {
                            /* �����װ�������˻����������ӿ�ѡ�б���ȥ�� */
                            unset($payments[$_id]);
                        }
                    }
                }
            }
            
            $all_payments = array('online' => array(), 'offline' => array());
            foreach ($payments as $key => $payment)
            {
                if ($payment['is_online'])
                {
                    $all_payments['online'][] = $payment;
                }
                else
                {
                    $all_payments['offline'][] = $payment;
                }
            }
            $order_info['paymoney'] =  $order_info['order_amount'] - $order_info['use_credit']; //���ʵ����Ҫ֧�����ֽ�
            $this->assign('order', $order_info);
            $this->assign('payments', $all_payments);
            $this->_curlocal(
                LANG::get('cashier')
            );
        
            $this->_config_seo('title', Lang::get('confirm_payment') . ' - ' . Conf::get('site_title'));
            $this->display('cashier.payment.html');
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
            $this->show_warning('no_such_order');

            return;
        }
     
        if (!$payment_id)
        {
            $this->show_warning('no_such_payment');

            return;
        }

        /* ��֤֧����ʽ */
        $payment_model =& m('payment');
        $payment_info  = $payment_model->get($payment_id);
        
        if (!$payment_info)
        {
            $this->show_warning('no_such_payment');

            return;
        }

        /* ����֧����ʽ */
        $edit_data = array(
            'payment_id'    =>  $payment_info['payment_id'],
            'payment_code'  =>  $payment_info['payment_code'],
            'payment_name'  =>  $payment_info['payment_name'],
        );

        $order_model =& m('order');
        $order_model->edit($order_id, $edit_data);
        $order_info  = $order_model->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if($payment_id == 5)
        {
            if($order_info['testing_tpwd'] != 1)
	        {
	            if(!empty($_POST['traderpassword']))
		        {
		        	$ms =& ms();
		    		$paypw = $ms->user->traderAuth($this->visitor->get('user_id'),$_POST['traderpassword']);
		    		if(!$paypw)
		    		{
		    			$this->show_warning('�Ƿ���֤');
		    			return ;
		    		}
		        }
	        }
        }
        if (empty($order_info))
        {
            $this->show_warning('no_such_order');

            return;
        }
        if ($order_info['seller_id']!=STORE_ID && $payment_id==4)
        {
        	$this->show_message('��ѡ�񵽵�֧�����뾡�쵽������֧����ȡ����',"�鿴�ҵĶ���", "index.php?app=buyer_order","�����ҵ�����", "index.php?app=member");
	        return;
        }else
        {
         	 /* ����ֱ�ӵ�����֧�� */
	         /* ��֤֧����ʽ�Ƿ���ã������ڰ������У�������ʹ�� */
	         if (!$payment_model->in_white_list($order_info['payment_code']))
	         {
	            $this->show_warning('payment_disabled_by_system');
	
	            return;
	         }
	
	         $payment_info  = $payment_model->get("payment_code = '{$order_info['payment_code']}' AND store_id=0");
	         /* ����̨û������֧����ʽ��������ʹ�� */
	         if (!$payment_info['enabled'])
	         {
	              $this->show_warning('payment_disabled');
	              return;
	         }
         }
         /* ����֧��URL��� */
         $order_info['order_amount'] =  $order_info['order_amount'] - $order_info['use_credit']; //���ʵ����Ҫ֧�����ֽ�
         $payment    = $this->_get_payment($order_info['payment_code'], $payment_info);
         $payment_form = $payment->get_payform($order_info);
		 if ($payment_form == -1)
		 {
		 	$this->show_warning('���Ļ�Ա�˻����㣬������ѡ��֧����ʽ��');
		 	return;
		 }
         /* ��ת����ʵ����̨ */
         $this->_config_seo('title', Lang::get('cashier'));
         $this->assign('payform', $payment_form);
         $this->assign('payment', $payment_info);
         $this->assign('order', $order_info);
         header('Content-Type:text/html;charset=' . CHARSET);
         $this->display('cashier.payform.html');
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
            $this->show_warning('no_such_order');
            return;
        }
        if (!$pay_message)
        {
            $this->show_warning('no_pay_message');
            return;
        }
        $order_model =& m('order');
        $order_info  = $order_model->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_warning('no_such_order');
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

        $this->show_message('pay_message_successed',
            'view_order',   'index.php?app=buyer_order',
            'close_window', 'javascript:window.close();');
    }
}

?>
