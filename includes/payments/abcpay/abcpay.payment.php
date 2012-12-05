<?php

/**
 * 			农行支付接口
 **/
class AbcpayPayment extends BasePayment {
	
	//支付网关接口
	public $_gateway = 'http://59.54.54.69:8080/axis/abcpay.jsp';

	public $_code = 'abcpay';

	public $_notifyURL = 'http://59.54.54.69:8080/axis/AbcPayResult.jsp';
	
	private $key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";
	
	/**
	 * 	@获取支付表单
	 * 	@param array $order_info 侍支付的定单信息
	 * 	
	 * 	@return array
	 **/
	
	public function get_payform($order_info) {
		$orderDesc = 'pailaOrder';
		$orderDate = date('Y/m/d');  //订单日期	
		$orderTime = date('H:i:s');  //订单时间
		//使用农行支付前,更新订单号
		$order_sn = $this->_gen_order_sn(); //生成定单号
		$model_order =& m('order');
		$model_order->edit($order_info['order_id'],array('order_sn' => $order_sn));
		//构造支付请求表单数据
		$params = array(
			'OrderNo' => $order_sn,
			'OrderDesc' => $orderDesc,
			'OrderDate' => $orderDate,
			'OrderTime'	=> $orderTime,
			'OrderAmount' => $order_info['order_amount'],
			'OrderURL' => "http://www.paila100.com/order/abcpay/order.php?action=queryorder",
			'ProductType' => 1,
			'PaymentType' => 1,
			'NotifyType' => 1,
			'ResultNotifyURL' => $this->_notifyURL,
			'MerchantRemarks' => $orderDesc,
			'PaymentLinkType' => 1,
		);
		return $this->_create_payform('POST', $params);
	}
	
	/**
	 * 	@获取支付表单
	 * 	@param array $order_info 侍支付的定单信息
	 * 	
	 * 	@return array
	 **/
	
	public function get_credit_payform($order_info) {
		$orderDesc = 'creditOrder';
		$orderDate = date('Y/m/d');  //订单日期	
		$orderTime = date('H:i:s');  //订单时间
		//使用农行支付前,更新订单号
		$order_sn = $this->_gen_order_sn(); //生成定单号
		$model_order =& m('creditorder');
		$model_order->edit($order_info['id'],array('order_sn' => $order_sn));
		//构造支付请求表单数据
		$params = array(
			'OrderNo' => $order_sn,
			'OrderDesc' => $orderDesc,
			'OrderDate' => $orderDate,
			'OrderTime'	=> $orderTime,
			'OrderAmount' => $order_info['order_amount'],
			'OrderURL' => "http://www.paila100.com/order/abcpay/order.php?action=queryorder",
			'ProductType' => 1,
			'PaymentType' => 1,
			'NotifyType' => 1,
			'ResultNotifyURL' => $this->_notifyURL,
			'MerchantRemarks' => $orderDesc,
			'PaymentLinkType' => 1,
		);
		return $this->_create_payform('POST', $params);
	}
	
	/**
	 * 	@获取支付表单
	 * 	@param array $order_info 侍支付的定单信息
	 * 	
	 * 	@return array
	 **/
	
	public function get_group_payform($order_info) {
		$orderDesc = 'tuan';
		$orderDate = date('Y/m/d');  //订单日期	
		$orderTime = date('H:i:s');  //订单时间
		//构造支付请求表单数据
		$params = array(
			'OrderNo' => $order_info['order_sn'],
			'OrderDesc' => $orderDesc,
			'OrderDate' => $orderDate,
			'OrderTime'	=> $orderTime,
			'OrderAmount' => $order_info['order_amount'],
			'OrderURL' => "http://www.paila100.com/order/abcpay/order.php?action=queryorder",
			'ProductType' => 1,
			'PaymentType' => 1,
			'NotifyType' => 1,
			'ResultNotifyURL' => $this->_notifyURL,
			'MerchantRemarks' => $orderDesc,
			'PaymentLinkType' => 1,
		);
		return $this->_create_payform('POST', $params);
	}
	
	function verify_notify($order_info , $strict = false) {
		if(empty($order_info)) {
			$this->_error('order_info_empty');

            return false;
		} 
		$sign = $_GET['sign'];
    	import('AES');    //导入短信发送类
    	$aes = new AES(true);
    	$key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";// 128位密钥
		$keys = $aes->makeKey($key);
		//解密后的签名字符串
		$cpt = $aes->decryptString($sign, $keys);
		
		//劈开解密后签名字符串
		@list($ordersn,$money, $verify) = explode('&', $cpt, 3);
		
		if($verify=='true') {
			if ($order_info['order_sn'] != $ordersn)
	        {
	            /* 通知中的订单与欲改变的订单不一致 */
	            $this->_error('order_inconsistent');
	
	            return false;
	        }
	        $order_info['order_amount'] =  $order_info['order_amount'] - $order_info['use_credit']; //算出实际需要支付的现金
			if ($order_info['order_amount'] != $money)
	        {
	            /* 支付的金额与实际金额不一致 */
	            $this->_error('price_inconsistent');
	
	            return false;
	        }
	        if($order_info['status'] == ORDER_PENDING) {
	        	/* 如果是等待付款中，则说明是即时到账交易，这时将状态改为已付款 */
                $order_status = ORDER_ACCEPTED;
                return array(
            		'target'    =>  $order_status,
        		);
	        }
	        return array(
            	'target'    =>  $order_info['status'],
        	);
		} else {
			$this->_error('签名验证失败!');
			return false;
		}
	}
	/**
     *    生成订单号
     *
     *    @author    Garbin
     *    @return    string
     */
    function _gen_order_sn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        $order_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $model_order =& m('order');
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