<?php

/**
 *    收银台控制器，其扮演的是收银员的角色，你只需要将你的订单交给收银员，收银员按订单来收银，她专注于这个过程
 *
 *    @author    Garbin
 */
class CashierApp extends ShoppingbaseApp
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
                   	//导入脚本弹窗的js
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
        /* 内部根据订单号收银,获取收多少钱，使用哪个支付接口 */
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
			if ($order_info['status'] == ORDER_PENDING) //订单未支付状态下
			{
				//更新交易为待发货 
	            $param = array(
	            	'status' => ORDER_ACCEPTED,
	            	'payment_id' => 99,
	            	'payment_name' => '积分支付',
	            	'payment_code' => 'credit',
	            	'pay_time' => time(),
	            	'short_code' => create_short_code(),
	            );
	            changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['use_credit'],SUBTRACK_CREDIT);
	    		$log_info1 = array(
		            	'user_id'	    => intval($order_info['buyer_id']),
		            	'user_credit'	        => '-'.$order_info['use_credit'],
		            	'change_desc'   => '（店铺ID：'.$order_info['seller_id'].',店铺名：'.$order_info['seller_name'].'） 购物使用积分：'.$order_info['use_credit'].'PL',
		            	'order_id'      => $order_info['order_id'],
			            'change_time'	=>	time(),
		            	'change_type'	=> 	2,
		            ); 
		        add_account_log($log_info1);
	            $order_model->edit($order_id,$param);
	            $this->assign('order', $order_info);
	            $model_order =& m('order');
           		/* 减去商品库存 */
            	$model_order->change_stock('-', $order_id);
            	
            	$smslog =&  m('smslog'); 
            	import('class.smswebservice');    //导入短信发送类
        		$sms = SmsWebservice::instance(); //实例化短信接口类
	        		
            	if ($order_info['seller_id'] == STORE_ID)
        		{
				    $smscontent = "客户:{$order_info['buyer_name']}，已经成功付款，订单号:{$order_info['order_sn']}。请尽快发货";
				    $mobile = DEF_MOBILE;
				    $verifytype = "buygoods";	
	        		$result= $sms->SendSms2($mobile,$smscontent); //执行发送短信验证码操作
	        		//短信发送成功
	        		if ($result == 0) 
	        		{
	        			//将验证码写入SESSION
	        			$time = time();
	        			//执行短信日志写入操作
	        			$smsdata['mobile'] = $mobile;
	        			$smsdata['smscontent'] = $smscontent;
	        			$smsdata['type'] = $verifytype; //注册验证短信
	        			$smsdata['sendtime'] = $time;
	        			$smslog->add($smsdata);
	       			}
        		}else 
        		{
        			$smscontent = "尊敬的:{$order_info['buyer_name']}，您已经成功付款，您的订单号:{$order_info['order_sn']}，取货验证码:{$param['short_code']}，取货地址:{$order_info['address']}，请尽快到店取货";
				    $mobile = $order_info['buyer_mobile'];
				    $verifytype = "buygoodsverify";	
			      	$result= $sms->SendSms2($mobile,$smscontent); //执行发送短信提醒操作
			      	//短信发送成功
			        if ($result == 0) 
			        {
			        	$time = time();
			        	//执行短信日志写入操作
			        	$smsdata['mobile'] = $mobile;
			        	$smsdata['smscontent'] = $smscontent;
			        	$smsdata['type'] = $verifytype; //短信提醒
			        	$smsdata['sendtime'] = $time;
			       		$smslog->add($smsdata);
			       	}
        		}
	        	$this->display('paynotify.index.html');
	        	return;
			}
		}
        
        //用户已经完成支付的订单
        if ($order_info['status'] == ORDER_ACCEPTED)
        {
        	$this->assign('order', $order_info);
        	$this->display('paynotify.index.html');
        	return;
        }
        /* 订单有效性判断 */
        if ($order_info['payment_code'] != 'cod' && $order_info['status'] != ORDER_PENDING)
        {
            $this->show_warning('no_such_order');
            return;
        }
        $payment_model =& m('payment');
        /* 若还没有选择支付方式，则让其选择支付方式 */
            $payments = $payment_model->get_enabled(0);
           
            if (empty($payments))
            {
                $this->show_warning('没有开启支付方式！');

                return;
            }
            /* 找出配送方式，判断是否可以使用货到付款 */
            $model_extm =& m('orderextm');
            $consignee_info = $model_extm->get($order_id);
            if (!empty($consignee_info))
            {
                /* 需要配送方式 */
                $model_shipping =& m('shipping');
                $shipping_info = $model_shipping->get($consignee_info['shipping_id']);
                $cod_regions   = unserialize($shipping_info['cod_regions']);
                $cod_usable = true;//默认可用
                if (is_array($cod_regions) && !empty($cod_regions))
                {
                    /* 取得支持货到付款地区的所有下级地区 */
                    $all_regions = array();
                    $model_region =& m('region');
                    foreach ($cod_regions as $region_id => $region_name)
                    {
                        $all_regions = array_merge($all_regions, $model_region->get_descendant($region_id));
                    }

                    /* 查看订单中指定的地区是否在可货到付款的地区列表中，如果不在，则不显示货到付款的付款方式 */
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
                    /* 从列表中去除货到付款的方式 */
                    foreach ($payments as $_id => $_info)
                    {
                        if ($_info['payment_code'] == 'cod')
                        {
                            /* 如果安装并启用了货到付款，则将其从可选列表中去除 */
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
            $order_info['paymoney'] =  $order_info['order_amount'] - $order_info['use_credit']; //算出实际需要支付的现金
            $this->assign('order', $order_info);
            $this->assign('payments', $all_payments);
            $this->_curlocal(
                LANG::get('cashier')
            );
        
            $this->_config_seo('title', Lang::get('confirm_payment') . ' - ' . Conf::get('site_title'));
            $this->display('cashier.payment.html');
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
            $this->show_warning('no_such_order');

            return;
        }
     
        if (!$payment_id)
        {
            $this->show_warning('no_such_payment');

            return;
        }

        /* 验证支付方式 */
        $payment_model =& m('payment');
        $payment_info  = $payment_model->get($payment_id);
        
        if (!$payment_info)
        {
            $this->show_warning('no_such_payment');

            return;
        }

        /* 保存支付方式 */
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
		    			$this->show_warning('非法验证');
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
        	$this->show_message('您选择到店支付，请尽快到派啦店支付并取货！',"查看我的订单", "index.php?app=buyer_order","返回我的派啦", "index.php?app=member");
	        return;
        }else
        {
         	 /* 否则直接到网关支付 */
	         /* 验证支付方式是否可用，若不在白名单中，则不允许使用 */
	         if (!$payment_model->in_white_list($order_info['payment_code']))
	         {
	            $this->show_warning('payment_disabled_by_system');
	
	            return;
	         }
	
	         $payment_info  = $payment_model->get("payment_code = '{$order_info['payment_code']}' AND store_id=0");
	         /* 若后台没有启用支付方式，则不允许使用 */
	         if (!$payment_info['enabled'])
	         {
	              $this->show_warning('payment_disabled');
	              return;
	         }
         }
         /* 生成支付URL或表单 */
         $order_info['order_amount'] =  $order_info['order_amount'] - $order_info['use_credit']; //算出实际需要支付的现金
         $payment    = $this->_get_payment($order_info['payment_code'], $payment_info);
         $payment_form = $payment->get_payform($order_info);
		 if ($payment_form == -1)
		 {
		 	$this->show_warning('您的会员账户余额不足，请重新选择支付方式！');
		 	return;
		 }
         /* 跳转到真实收银台 */
         $this->_config_seo('title', Lang::get('cashier'));
         $this->assign('payform', $payment_form);
         $this->assign('payment', $payment_info);
         $this->assign('order', $order_info);
         header('Content-Type:text/html;charset=' . CHARSET);
         $this->display('cashier.payform.html');
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
        /* 线下支付完成并留下pay_message,发送给卖家付款完成提示邮件 */
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
