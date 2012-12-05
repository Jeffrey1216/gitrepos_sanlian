<?php

/**
 *    支付网关通知接口
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
     *    支付完成后返回的URL，在此只进行提示，不对订单进行任何修改操作,这里不严格验证，不改变订单状态
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
	    	import('AES');    //导入短信发送类
	    	$aes = new AES(true);
	    	$key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";// 128位密钥
			$keys = $aes->makeKey($key);
			//解密后的签名字符串
			$cpt = $aes->decryptString($sign, $keys);
			//劈开解密后签名字符串
			@list($ordersn,$money, $verify) = explode('&', $cpt, 3);
			$order_mod = & m('grouporder');
			$order_info_old = $order_mod->get(" order_sn = '" . $ordersn . "' ");
			$order_id = $order_info_old['order_id'];
        }
        if (!$order_id)
        {
            /* 无效的通知请求 */
            $this->show_warning('forbidden');

            return;
        }
		
        /* 获取订单信息 */
        $model_order =& m('grouporder');
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
        $model_project =& m('groupproject');
        
    	$project_list = $model_project->getRow("select gp.id,go.quantity,gp.max_quantity,go.status from pa_group_project gp 
        left join pa_group_order go on gp.id = go.project_id where go.order_id = " . $order_id);

    	if(intval($project_list['status']) != ORDER_ACCEPTED) {
	        if(intval($project_list['max_quantity']) <= 0) { //已经被秒杀 .  
	        	$this->assign("is_success",false);
	        } else {
	        	#TODO 临时在此也改变订单状态为方便调试，实际发布时应把此段去掉，订单状态的改变以notify为准
	        	//$this->_change_order_status($order_id, $order_info['extension'], $notify_result);
	        	$this->_curlocal('支付成功');
	        	$this->assign('is_success',true);
	        }
    	} else {
    		#TODO 临时在此也改变订单状态为方便调试，实际发布时应把此段去掉，订单状态的改变以notify为准
        	//$this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        	$this->_curlocal('支付成功');
        	$this->assign('is_success',true);
    	}

        /* 只有支付时会使用到return_url，所以这里显示的信息是支付成功的提示信息 */
        
        
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
			$order_mod = & m('grouporder');
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
        $model_order =& m('grouporder');
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
        
    	$model_project =& m('groupproject');
        $group_order_extm_mod = & m('grouporderextm');
    	$project_list = $model_project->getRow("select gp.id,go.buyer_id,go.quantity,gp.max_quantity,go.status from pa_group_project gp 
        left join pa_group_order go on gp.id = go.project_id where go.order_id = " . $order_id);
    	$order_extm = $group_order_extm_mod->get($order_id);
    	if(intval($project_list['status']) != ORDER_ACCEPTED) {
	        if(intval($project_list['max_quantity']) <= 0) { //已经被秒杀 .  
	      		if($order_extm['phone_mob'] != '' && intval($project_list['status']) == ORDER_PENDING) {
	      			$this->sendSms($project_list['buyer_id'],$order_extm['phone_mob'], "尊敬的派啦网用户:您所购买的商品已售完.请耐心等待系统退款或致电400-166-1616查询!谢谢");
	        	}	
	        	$this->_change_order_status($order_id, $order_info['extension'], $notify_result,true);
	      		return;
	        } 
    	} 
		
    	if($order_extm['phone_mob'] != '' && intval($project_list['status']) == ORDER_PENDING) {
      		$this->sendSms($project_list['buyer_id'],$order_extm['phone_mob'], "尊敬的派啦网用户:您所购买的商品已经成功付款! 我们会尽快给您发货,祝你购物愉快..");
        }
        //改变订单状态
        $this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        $payment->verify_result(true);
        
        //商品数减
		$this->_group_order_mod->change_stock("-", $order_id);
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
    
    private function sendSms($buyer_id,$mobile,$content) {
    	import('class.smswebservice');    //导入短信发送类
        $sms = SmsWebservice::instance(); //实例化短信接口类
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
     *    改变订单状态
     *
     *    @author    Garbin
     *    @param     int $order_id
     *    @param     string $order_type
     *    @param     array  $notify_result
     *    @return    void
     */
    function _change_order_status($order_id, $order_type, $notify_result , $is_over = false)
    {
        $this->respond_notify($order_id, $notify_result,$is_over);    //响应通知
    }
    
	/**
     *    响应支付通知
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
	                $where .= ' AND status=' . ORDER_PENDING;   //只有待付款的订单才会被修改为已付款
	                $data['pay_time']   =   gmtime();
	            break;
	            case ORDER_SHIPPED:
	                $where .= ' AND status=' . ORDER_ACCEPTED;  //只有等待发货的订单才会被修改为已发货
	                $data['ship_time']  =   gmtime();
	            break;
	            case ORDER_FINISHED:
	                $where .= ' AND status=' . ORDER_SHIPPED;   //只有已发货的订单才会被自动修改为交易完成
	                $data['finished_time'] = gmtime();
	            break;
	            case ORDER_CANCLED:;                         //任何情况下都可以关闭
	                /* 加回商品库存 */
	                
	            break;
	        }
        } else {
        	$where .= ' AND status =' . ORDER_PENDING;   // 定单退款中.
        	$data['status'] = ORDER_REFUND;
        	$data['refund_cause'] = '商品已经售完. 但买家已付款!.';
	        $data['finished_time'] = gmtime();       
        }

        return $model_order->edit($where, $data);
    }
}

?>
