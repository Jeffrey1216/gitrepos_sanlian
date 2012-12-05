<?php

/**
 *    支付网关通知接口
 *
 *    @author    Garbin
 *    @usage    none
 */
class PaynotifyApp extends MallbaseApp
{
    /**
     *    支付完成后返回的URL，在此只进行提示，不对订单进行任何修改操作,这里不严格验证，不改变订单状态
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        //这里是支付宝，财付通等当订单状态改变时的通知地址
    	$order_id   = 0;
        if(isset($_POST['order_id']))
        {
            $order_id = intval($_POST['order_id']);
        }
        else if(isset($_GET['order_id']))
        {
            $order_id = intval($_GET['order_id']);
        } else {
        	$sign = $_GET['sign'];
	    	import('AES');    //导入短信发送类
	    	$aes = new AES(true);
	    	$key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";// 128位密钥
			$keys = $aes->makeKey($key);
			//解密后的签名字符串
			$cpt = $aes->decryptString($sign, $keys);
			//劈开解密后签名字符串
			@list($ordersn,$money, $verify) = explode('&', $cpt, 3);
			$order_mod = & m('order');
			$order_info_old = $order_mod->get(array('conditions' => " order_sn = '" . $ordersn . "' "));
			$order_id = $order_info_old['order_id'];
        }
        if (!$order_id)
        {
            /* 无效的通知请求 */
            $this->show_warning('forbidden');

            return;
        }

        /* 获取订单信息 */
        $model_order =& m('order');
        $order_info  = $model_order->get($order_id);
        if (empty($order_info))
        {
            /* 没有该订单 */
            $this->show_warning('forbidden');

            return;
        }

        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("payment_code='{$order_info['payment_code']}' AND store_id=0");
        if (empty($payment_info))
        {
            /* 没有指定的支付方式 */
            $this->show_warning('no_such_payment');

            return;
        }

        /* 调用相应的支付方式 */
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);

        /* 获取验证结果 */
        $notify_result = $payment->verify_notify($order_info);
        if ($notify_result === false)
        {
            /* 支付失败 */
            $this->show_warning($payment->get_error());

            return;
        }
        $is_success = true;
        $this->assign("is_success" , $is_success);

        #TODO 临时在此也改变订单状态为方便调试，实际发布时应把此段去掉，订单状态的改变以notify为准
        if ($order_info['status'] == ORDER_PENDING)
        {
        	$this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        }

        /* 只有支付时会使用到return_url，所以这里显示的信息是支付成功的提示信息 */
        $this->_curlocal(LANG::get('pay_successed'));
        $this->assign('order', $order_info);
        $this->assign('payment', $payment_info);
        $this->display('paynotify.index.html');
    }

    /**
     *    支付完成后，外部网关的通知地址，在此会进行订单状态的改变，这里严格验证，改变订单状态
     *
     *    @author    Garbin
     *    @return    void
     */
    function notify()
    {
        //这里是支付宝，财付通等当订单状态改变时的通知地址
        $order_id   = 0;
        if(isset($_POST['order_id']))
        {
            $order_id = intval($_POST['order_id']);
        }
        else if(isset($_GET['order_id']))
        {
            $order_id = intval($_GET['order_id']);
        } else {
        	$sign = $_GET['sign'];
	    	import('AES');    //导入短信发送类
	    	$aes = new AES(true);
	    	$key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";// 128位密钥
			$keys = $aes->makeKey($key);
			//解密后的签名字符串
			$cpt = $aes->decryptString($sign, $keys);
			//劈开解密后签名字符串
			@list($ordersn,$money, $verify) = explode('&', $cpt, 3);
			$order_mod = & m('order');
			$order_info_old = $order_mod->get(array('conditions' => " order_sn = '" . $ordersn . "' "));
			$order_id = $order_info_old['order_id'];
        }
        if (!$order_id)
        {
            /* 无效的通知请求 */
            $this->show_warning('no_such_order');
            return;
        }

        /* 获取订单信息 */
        $model_order =& m('order');
        $order_info  = $model_order->get($order_id);
        if (empty($order_info))
        {
            /* 没有该订单 */
            $this->show_warning('no_such_order');
            return;
        }
		
        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("payment_code='{$order_info['payment_code']}' AND store_id=0");
        if (empty($payment_info))
        {
            /* 没有指定的支付方式 */
            $this->show_warning('no_such_payment');
            return;
        }
			
        /* 调用相应的支付方式 */
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);
		
        /* 获取验证结果 */
        $notify_result = $payment->verify_notify($order_info, true);
        if ($notify_result === false)
        {
            /* 支付失败 */
            $payment->verify_result(false);
            return;
        }
		
        //改变订单状态
        if ($order_info['status'] == ORDER_PENDING)
        {
        	$this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        }
        $payment->verify_result(true);

        if ($notify_result['target'] == ORDER_ACCEPTED)
        {
            /* 发送邮件给卖家，提醒付款成功 */
            $model_member =& m('member');
            $seller_info  = $model_member->get($order_info['seller_id']);

            $mail = get_mail('toseller_online_pay_success_notify', array('order' => $order_info));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            /* 异步发送 */
            $this->_sendmail(false);
        }
        $this->_curlocal(LANG::get('pay_successed'));
        $this->assign('order', $order_info);
        $this->assign('payment', $payment_info);
        $this->display('paynotify.index.html');
    }

    /**
     *    改变订单状态
     *
     *    @author    Garbin
     *    @param     int $order_id
     *    @param     string $order_type
     *    @param     array  $notify_result
     *    @return    void
     */
    function _change_order_status($order_id, $order_type, $notify_result)
    {
        /* 将验证结果传递给订单类型处理 */
        $order_type  =& ot($order_type);
        $order_type->respond_notify($order_id, $notify_result);    //响应通知
        /* 减去商品库存 */
        $model_order =& m('order');
        $model_order->change_stock('-', $order_id);
        
        $order_info  = $model_order->getRow("select o.*,s.address from pa_order o left join pa_store s on o.seller_id = s.store_id where order_id={$order_id} ");
        if ($order_info['use_credit']>0)
        {
        	$data['pay_type'] = 3;
        }else
        {
        	$data['pay_type'] = 1;
        }
        $data['cash'] = $order_info['order_amount'] - $order_info['use_credit'];
        $data['short_code'] = create_short_code(); //获取提货验证码
        
        $model_order->edit($order_id,$data);
        
        $smslog =&  m('smslog'); 
      	import('class.smswebservice');    //导入短信发送类
   		$sms = SmsWebservice::instance(); //实例化短信接口类
	   		
        if ($order_info['seller_id'] == STORE_ID)
        {
		    $smscontent = "客户:{$order_info['buyer_name']}，已经成功付款，订单号:{$order_info['order_sn']}。请尽快发货";
		    $mobile = DEF_MOBILE;
		    $verifytype = "buygoods";	
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
        }else 
        {
        	$smscontent = "尊敬的:{$order_info['buyer_name']}，您已经成功付款，您的订单号:{$order_info['order_sn']}，取货验证码:{$data['short_code']}，取货地址:{$order_info['address']}，请尽快到店取货";
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
    }
}

?>
