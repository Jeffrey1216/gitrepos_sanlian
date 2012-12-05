<?php

/**
 *    余额支付
 *
 *    @author    Garbin
 *    @usage    none
 */
class BalancepayPayment extends BasePayment
{
    var $_code = 'balancepay';
    
    function _pay($order_info)
    {
    	//查看余额是否足够
    	$model_member =& m('member');
        $member_info  = $model_member->get($order_info['buyer_id']);
        $member_info['money'] = $member_info['money'] - $member_info['frozen_money'];
        if ($member_info['money']<$order_info['pay_amount'])
        {
            return -1;
        }
        
        if ($order_info['pay_amount']>0)
        {
	    	//支付成功-直接扣除店铺余额, 更新账户记录.  
	    	changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['pay_amount'],SUBTRACK_MONEY);
	    	$log_info = array(
		            	'user_id'	    => intval($order_info['buyer_id']),
		            	'user_money'    => '-'.$order_info['pay_amount'],
		            	'change_desc'   => '店铺进货扣除账户金额：'.$order_info['pay_amount'].'元',
		            	'order_id'      => $order_info['order_id'],
			            'change_time'	=>	time(),
		            	'change_type'	=> 	7,
		            ); 
		    add_account_log($log_info);
        }
    	//并更改订单状态为已付款
    	$_storeorder_mod = & m('storeorder');
    	$param = array(
            'status'    => ORDER_ACCEPTED,
            'pay_time'  => time(),
        );
    	$_storeorder_mod->edit($order_info['order_id'], $param);
    	return 0;
    }
    
	/**
	 * 会员余额支付
	 * 	
	 * 	@return array
	 **/
	
	public function get_payform($order_info) {
		if ($order_info['status'] == ORDER_PENDING)
        {
			$order_model =& m('order');
	        $order_info  = $order_model->getRow("select o.*,s.address from pa_order o left join pa_store s on o.seller_id = s.store_id where order_id={$order_info['order_id']}");
			$model_member =& m('member');
	        $member_info  = $model_member->get($order_info['buyer_id']);
	        $member_info['money'] = $member_info['money'] - $member_info['frozen_money'];
	        if ($member_info['money']<$order_info['order_amount'])
	        {
	            return -1;
	        }
	        
	        //支付成功-直接扣除会员余额, 更新会员账户记录.  
	    	changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['order_amount'],SUBTRACK_MONEY);
	    	$log_info = array(
		            	'user_id'	    => intval($order_info['buyer_id']),
		            	'user_money'	        => '-'.$order_info['order_amount'],
		            	'change_desc'   => '（店铺ID：'.$order_info['seller_id'].',店铺名：'.$order_info['seller_name'].'） 消费会员金额：'.$order_info['order_amount'].'元',
		            	'order_id'      => $order_info['order_id'],
			            'change_time'	=>	time(),
		            	'change_type'	=> 	41,
		            ); 
		    add_account_log($log_info); 
			if ($order_info['use_credit'] == 0)
	    	{
	    		$paytype = 6;
	    	}else
	    	{
	    		//使用了积分真正的扣除用户积分--并解冻冻结积分部分
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
	    		changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['use_credit'],CANCLE_FROZEN_CREDIT);
	    		$log_info2 = array(
		            	'user_id'	    => intval($order_info['buyer_id']),
		            	'frozen_credit'	        => '-'.$order_info['use_credit'],
		            	'change_desc'   => '（店铺ID：'.$order_info['seller_id'].',店铺名：'.$order_info['seller_name'].'）取消冻结积分：'.$order_info['use_credit'].'PL',
		            	'order_id'      => $order_info['order_id'],
			            'change_time'	=>	time(),
		            	'change_type'	=> 	32,
		            ); 
		        add_account_log($log_info2); 
	    		$paytype = 4;
	    	}
	    	   
	    	//并更改订单状态为已付款-待发货
	        $param = array(
	        	'pay_type'  => $paytype,
	        	'use_money' => $order_info['order_amount'],
	            'status'    => ORDER_ACCEPTED,
	            'pay_time'  => time(),
	        	'short_code' => create_short_code(),
	        );
	        
	        $order_model->edit($order_info['order_id'],$param);
	         /* 减去商品库存 */
	        $order_model->change_stock('-', $order_info['order_id']);
	        
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
        }		
        header('Location:index.php?app=cashier&order_id=' . $order_info['order_id']);
	}
}

?>