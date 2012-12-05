<?php

/**
 *    ֧������֪ͨ�ӿ�
 *
 *    @author    Garbin
 *    @usage    none
 */
class Group_paynotifyApp extends MallbaseApp
{
	
	public function __construct() {
		$this->Group_paynotifyApp();
	}
	
	public function Group_paynotifyApp() {
		parent::__construct();
		$this->_group_order_mod = & m("grouporder");
	}
    /**
     *    ֧����ɺ󷵻ص�URL���ڴ�ֻ������ʾ�����Զ��������κ��޸Ĳ���,���ﲻ�ϸ���֤�����ı䶩��״̬
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
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
			$order_mod = & m('grouporder');
			$order_info_old = $order_mod->get(" order_sn = '" . $ordersn . "' ");
			$order_id = $order_info_old['order_id'];
        }
        if (!$order_id)
        {
            /* ��Ч��֪ͨ���� */
            $this->show_warning('forbidden');

            return;
        }
		
        /* ��ȡ������Ϣ */
        $model_order =& m('grouporder');
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
        $model_project =& m('groupproject');
        
    	$project_list = $model_project->getRow("select gp.id,go.quantity,gp.max_quantity,go.status from pa_group_project gp 
        left join pa_group_order go on gp.id = go.project_id where go.order_id = " . $order_id);

    	if(intval($project_list['status']) != ORDER_ACCEPTED) {
	        if(intval($project_list['max_quantity']) <= 0) { //�Ѿ�����ɱ .  
	        	$this->assign("is_success",false);
	        } else {
	        	#TODO ��ʱ�ڴ�Ҳ�ı䶩��״̬Ϊ������ԣ�ʵ�ʷ���ʱӦ�Ѵ˶�ȥ��������״̬�ĸı���notifyΪ׼
	        	//$this->_change_order_status($order_id, $order_info['extension'], $notify_result);
	        	$this->_curlocal('֧���ɹ�');
	        	$this->assign('is_success',true);
	        }
    	} else {
    		#TODO ��ʱ�ڴ�Ҳ�ı䶩��״̬Ϊ������ԣ�ʵ�ʷ���ʱӦ�Ѵ˶�ȥ��������״̬�ĸı���notifyΪ׼
        	//$this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        	$this->_curlocal('֧���ɹ�');
        	$this->assign('is_success',true);
    	}

        /* ֻ��֧��ʱ��ʹ�õ�return_url������������ʾ����Ϣ��֧���ɹ�����ʾ��Ϣ */
        
        
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
			$order_mod = & m('grouporder');
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
        $model_order =& m('grouporder');
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
        
    	$model_project =& m('groupproject');
        $group_order_extm_mod = & m('grouporderextm');
    	$project_list = $model_project->getRow("select gp.id,go.buyer_id,go.quantity,gp.max_quantity,go.status from pa_group_project gp 
        left join pa_group_order go on gp.id = go.project_id where go.order_id = " . $order_id);
    	$order_extm = $group_order_extm_mod->get($order_id);
    	if(intval($project_list['status']) != ORDER_ACCEPTED) {
	        if(intval($project_list['max_quantity']) <= 0) { //�Ѿ�����ɱ .  
	      		if($order_extm['phone_mob'] != '' && intval($project_list['status']) == ORDER_PENDING) {
	      			$this->sendSms($project_list['buyer_id'],$order_extm['phone_mob'], "�𾴵��������û�:�����������Ʒ������.�����ĵȴ�ϵͳ�˿���µ�400-166-1616��ѯ!лл");
	        	}	
	        	$this->_change_order_status($order_id, $order_info['extension'], $notify_result,true);
	      		return;
	        } 
    	} 
		
    	if($order_extm['phone_mob'] != '' && intval($project_list['status']) == ORDER_PENDING) {
      		$this->sendSms($project_list['buyer_id'],$order_extm['phone_mob'], "�𾴵��������û�:�����������Ʒ�Ѿ��ɹ�����! ���ǻᾡ���������,ף�㹺�����..");
        }
        //�ı䶩��״̬
        $this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        $payment->verify_result(true);
        
        //��Ʒ����
		$this->_group_order_mod->change_stock("-", $order_id);
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
    
    private function sendSms($buyer_id,$mobile,$content) {
    	import('class.smswebservice');    //������ŷ�����
        $sms = SmsWebservice::instance(); //ʵ�������Žӿ���
        $state = $sms->SendSms($mobile,$content);
        $param = array(
        	'user_id' => $buyer_id,
        	'store_id' => 0,
        	'mobile' => $mobile,
        	'smscontent' => $content,
        	'type' => 'groupbuyreturn',
        	'sendtime' => time(),
        );
        $sms->log($param);
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
    function _change_order_status($order_id, $order_type, $notify_result , $is_over = false)
    {
        $this->respond_notify($order_id, $notify_result,$is_over);    //��Ӧ֪ͨ
    }
    
	/**
     *    ��Ӧ֧��֪ͨ
     *
     *    @author    Garbin
     *    @param     int    $order_id
     *    @param     array  $notify_result
     *    @return    bool
     */
    function respond_notify($order_id, $notify_result,$is_over = false)
    {
        $model_order =& m('grouporder');
        $where = "order_id = {$order_id}";
        $data = array('status' => $notify_result['target']);
        if(!$is_over) {
	        switch ($notify_result['target'])
	        {
	            case ORDER_ACCEPTED:
	                $where .= ' AND status=' . ORDER_PENDING;   //ֻ�д�����Ķ����Żᱻ�޸�Ϊ�Ѹ���
	                $data['pay_time']   =   gmtime();
	            break;
	            case ORDER_SHIPPED:
	                $where .= ' AND status=' . ORDER_ACCEPTED;  //ֻ�еȴ������Ķ����Żᱻ�޸�Ϊ�ѷ���
	                $data['ship_time']  =   gmtime();
	            break;
	            case ORDER_FINISHED:
	                $where .= ' AND status=' . ORDER_SHIPPED;   //ֻ���ѷ����Ķ����Żᱻ�Զ��޸�Ϊ�������
	                $data['finished_time'] = gmtime();
	            break;
	            case ORDER_CANCLED:;                         //�κ�����¶����Թر�
	                /* �ӻ���Ʒ��� */
	                
	            break;
	        }
        } else {
        	$where .= ' AND status =' . ORDER_PENDING;   // �����˿���.
        	$data['status'] = ORDER_REFUND;
        	$data['refund_cause'] = '��Ʒ�Ѿ�����. ������Ѹ���!.';
	        $data['finished_time'] = gmtime();       
        }

        return $model_order->edit($where, $data);
    }
}

?>
