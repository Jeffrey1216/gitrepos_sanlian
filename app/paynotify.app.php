<?php

/**
 *    ֧������֪ͨ�ӿ�
 *
 *    @author    Garbin
 *    @usage    none
 */
class PaynotifyApp extends MallbaseApp
{
    /**
     *    ֧����ɺ󷵻ص�URL���ڴ�ֻ������ʾ�����Զ��������κ��޸Ĳ���,���ﲻ�ϸ���֤�����ı䶩��״̬
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        //������֧�������Ƹ�ͨ�ȵ�����״̬�ı�ʱ��֪ͨ��ַ
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
	    	import('AES');    //������ŷ�����
	    	$aes = new AES(true);
	    	$key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";// 128λ��Կ
			$keys = $aes->makeKey($key);
			//���ܺ��ǩ���ַ���
			$cpt = $aes->decryptString($sign, $keys);
			//�������ܺ�ǩ���ַ���
			@list($ordersn,$money, $verify) = explode('&', $cpt, 3);
			$order_mod = & m('order');
			$order_info_old = $order_mod->get(array('conditions' => " order_sn = '" . $ordersn . "' "));
			$order_id = $order_info_old['order_id'];
        }
        if (!$order_id)
        {
            /* ��Ч��֪ͨ���� */
            $this->show_warning('forbidden');

            return;
        }

        /* ��ȡ������Ϣ */
        $model_order =& m('order');
        $order_info  = $model_order->get($order_id);
        if (empty($order_info))
        {
            /* û�иö��� */
            $this->show_warning('forbidden');

            return;
        }

        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("payment_code='{$order_info['payment_code']}' AND store_id=0");
        if (empty($payment_info))
        {
            /* û��ָ����֧����ʽ */
            $this->show_warning('no_such_payment');

            return;
        }

        /* ������Ӧ��֧����ʽ */
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);

        /* ��ȡ��֤��� */
        $notify_result = $payment->verify_notify($order_info);
        if ($notify_result === false)
        {
            /* ֧��ʧ�� */
            $this->show_warning($payment->get_error());

            return;
        }
        $is_success = true;
        $this->assign("is_success" , $is_success);

        #TODO ��ʱ�ڴ�Ҳ�ı䶩��״̬Ϊ������ԣ�ʵ�ʷ���ʱӦ�Ѵ˶�ȥ��������״̬�ĸı���notifyΪ׼
        if ($order_info['status'] == ORDER_PENDING)
        {
        	$this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        }

        /* ֻ��֧��ʱ��ʹ�õ�return_url������������ʾ����Ϣ��֧���ɹ�����ʾ��Ϣ */
        $this->_curlocal(LANG::get('pay_successed'));
        $this->assign('order', $order_info);
        $this->assign('payment', $payment_info);
        $this->display('paynotify.index.html');
    }

    /**
     *    ֧����ɺ��ⲿ���ص�֪ͨ��ַ���ڴ˻���ж���״̬�ĸı䣬�����ϸ���֤���ı䶩��״̬
     *
     *    @author    Garbin
     *    @return    void
     */
    function notify()
    {
        //������֧�������Ƹ�ͨ�ȵ�����״̬�ı�ʱ��֪ͨ��ַ
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
	    	import('AES');    //������ŷ�����
	    	$aes = new AES(true);
	    	$key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";// 128λ��Կ
			$keys = $aes->makeKey($key);
			//���ܺ��ǩ���ַ���
			$cpt = $aes->decryptString($sign, $keys);
			//�������ܺ�ǩ���ַ���
			@list($ordersn,$money, $verify) = explode('&', $cpt, 3);
			$order_mod = & m('order');
			$order_info_old = $order_mod->get(array('conditions' => " order_sn = '" . $ordersn . "' "));
			$order_id = $order_info_old['order_id'];
        }
        if (!$order_id)
        {
            /* ��Ч��֪ͨ���� */
            $this->show_warning('no_such_order');
            return;
        }

        /* ��ȡ������Ϣ */
        $model_order =& m('order');
        $order_info  = $model_order->get($order_id);
        if (empty($order_info))
        {
            /* û�иö��� */
            $this->show_warning('no_such_order');
            return;
        }
		
        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("payment_code='{$order_info['payment_code']}' AND store_id=0");
        if (empty($payment_info))
        {
            /* û��ָ����֧����ʽ */
            $this->show_warning('no_such_payment');
            return;
        }
			
        /* ������Ӧ��֧����ʽ */
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);
		
        /* ��ȡ��֤��� */
        $notify_result = $payment->verify_notify($order_info, true);
        if ($notify_result === false)
        {
            /* ֧��ʧ�� */
            $payment->verify_result(false);
            return;
        }
		
        //�ı䶩��״̬
        if ($order_info['status'] == ORDER_PENDING)
        {
        	$this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        }
        $payment->verify_result(true);

        if ($notify_result['target'] == ORDER_ACCEPTED)
        {
            /* �����ʼ������ң����Ѹ���ɹ� */
            $model_member =& m('member');
            $seller_info  = $model_member->get($order_info['seller_id']);

            $mail = get_mail('toseller_online_pay_success_notify', array('order' => $order_info));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            /* �첽���� */
            $this->_sendmail(false);
        }
        $this->_curlocal(LANG::get('pay_successed'));
        $this->assign('order', $order_info);
        $this->assign('payment', $payment_info);
        $this->display('paynotify.index.html');
    }

    /**
     *    �ı䶩��״̬
     *
     *    @author    Garbin
     *    @param     int $order_id
     *    @param     string $order_type
     *    @param     array  $notify_result
     *    @return    void
     */
    function _change_order_status($order_id, $order_type, $notify_result)
    {
        /* ����֤������ݸ��������ʹ��� */
        $order_type  =& ot($order_type);
        $order_type->respond_notify($order_id, $notify_result);    //��Ӧ֪ͨ
        /* ��ȥ��Ʒ��� */
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
        $data['short_code'] = create_short_code(); //��ȡ�����֤��
        
        $model_order->edit($order_id,$data);
        
        $smslog =&  m('smslog'); 
      	import('class.smswebservice');    //������ŷ�����
   		$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
	   		
        if ($order_info['seller_id'] == STORE_ID)
        {
		    $smscontent = "�ͻ�:{$order_info['buyer_name']}���Ѿ��ɹ����������:{$order_info['order_sn']}���뾡�췢��";
		    $mobile = DEF_MOBILE;
		    $verifytype = "buygoods";	
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
        }else 
        {
        	$smscontent = "�𾴵�:{$order_info['buyer_name']}�����Ѿ��ɹ�������Ķ�����:{$order_info['order_sn']}��ȡ����֤��:{$data['short_code']}��ȡ����ַ:{$order_info['address']}���뾡�쵽��ȡ��";
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
    }
}

?>
