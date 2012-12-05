<?php

/**
 * 			ũ��֧���ӿ�
 **/
class AbcpayPayment extends BasePayment {
	
	//֧�����ؽӿ�
	public $_gateway = 'http://59.54.54.69:8080/axis/abcpay.jsp';

	public $_code = 'abcpay';

	public $_notifyURL = 'http://59.54.54.69:8080/axis/AbcPayResult.jsp';
	
	private $key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";
	
	/**
	 * 	@��ȡ֧����
	 * 	@param array $order_info ��֧���Ķ�����Ϣ
	 * 	
	 * 	@return array
	 **/
	
	public function get_payform($order_info) {
		$orderDesc = 'pailaOrder';
		$orderDate = date('Y/m/d');  //��������	
		$orderTime = date('H:i:s');  //����ʱ��
		//ʹ��ũ��֧��ǰ,���¶�����
		$order_sn = $this->_gen_order_sn(); //���ɶ�����
		$model_order =& m('order');
		$model_order->edit($order_info['order_id'],array('order_sn' => $order_sn));
		//����֧�����������
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
	 * 	@��ȡ֧����
	 * 	@param array $order_info ��֧���Ķ�����Ϣ
	 * 	
	 * 	@return array
	 **/
	
	public function get_credit_payform($order_info) {
		$orderDesc = 'creditOrder';
		$orderDate = date('Y/m/d');  //��������	
		$orderTime = date('H:i:s');  //����ʱ��
		//ʹ��ũ��֧��ǰ,���¶�����
		$order_sn = $this->_gen_order_sn(); //���ɶ�����
		$model_order =& m('creditorder');
		$model_order->edit($order_info['id'],array('order_sn' => $order_sn));
		//����֧�����������
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
	 * 	@��ȡ֧����
	 * 	@param array $order_info ��֧���Ķ�����Ϣ
	 * 	
	 * 	@return array
	 **/
	
	public function get_group_payform($order_info) {
		$orderDesc = 'tuan';
		$orderDate = date('Y/m/d');  //��������	
		$orderTime = date('H:i:s');  //����ʱ��
		//����֧�����������
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
    	import('AES');    //������ŷ�����
    	$aes = new AES(true);
    	$key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";// 128λ��Կ
		$keys = $aes->makeKey($key);
		//���ܺ��ǩ���ַ���
		$cpt = $aes->decryptString($sign, $keys);
		
		//�������ܺ�ǩ���ַ���
		@list($ordersn,$money, $verify) = explode('&', $cpt, 3);
		
		if($verify=='true') {
			if ($order_info['order_sn'] != $ordersn)
	        {
	            /* ֪ͨ�еĶ��������ı�Ķ�����һ�� */
	            $this->_error('order_inconsistent');
	
	            return false;
	        }
	        $order_info['order_amount'] =  $order_info['order_amount'] - $order_info['use_credit']; //���ʵ����Ҫ֧�����ֽ�
			if ($order_info['order_amount'] != $money)
	        {
	            /* ֧���Ľ����ʵ�ʽ�һ�� */
	            $this->_error('price_inconsistent');
	
	            return false;
	        }
	        if($order_info['status'] == ORDER_PENDING) {
	        	/* ����ǵȴ������У���˵���Ǽ�ʱ���˽��ף���ʱ��״̬��Ϊ�Ѹ��� */
                $order_status = ORDER_ACCEPTED;
                return array(
            		'target'    =>  $order_status,
        		);
	        }
	        return array(
            	'target'    =>  $order_info['status'],
        	);
		} else {
			$this->_error('ǩ����֤ʧ��!');
			return false;
		}
	}
	/**
     *    ���ɶ�����
     *
     *    @author    Garbin
     *    @return    string
     */
    function _gen_order_sn()
    {
        /* ѡ��һ������ķ��� */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        $order_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $model_order =& m('order');
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