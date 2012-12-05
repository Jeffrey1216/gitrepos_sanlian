<?php
class Plb_cartApp extends MallbaseApp{
	public function __construct()
    {
        $this->Plb_cartApp();
    }

    public function Plb_cartApp()
    {
    	parent::__construct();
    	$credit_order_mod = & m('creditorder');
    }
    
    //显示购物车信息
	function index()
	{
		$credit_cart_mod = & m('creditcart');
		$user_id = $this->visitor->get('user_id');
		$cart_info = $credit_cart_mod->getRow("select * from pa_credit_cart c left join pa_credit_goods g on c.credit_id = g.id where c.buyer_id = " . $user_id);
		if(!$cart_info)
		{
			$this->display("plb_cart.empty.html");
			return;
		}
		$this->assign('cart',$cart_info);
		$this->display("pl.cart.index.html");
	}
	
	//购物车添加
	function add() 
	{
		$credit_goods_mod = & m('creditgoods');
		$credit_cart_mod = & m('creditcart');
		$credit_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if (0 == $credit_id)
		{
			$this->show_warning('您购买的商品不存在！');
			return;
		}
		$goods_info=$credit_goods_mod->getRow("select * from pa_credit_goods where id = " .$credit_id);
		$buyer_id = $this->visitor->get('user_id');
		$buyer_name = $this->visitor->get('user_name');
		//var_dump($this->visitor->get('user_name'));
		//die;
		$seller_id = $goods_info['user_id'];
		$seller_name = $goods_info['user_name'];
		$price = $goods_info['price'];
		$credit = $goods_info['credit_num'];
		
		if(!$this->visitor->has_login)
		{
			header("Location:index.php?app=member&act=login");
			return;
		}
		if(!$goods_info)
		{
			$this->show_warning('您购买的商品不存在！');
			return;
		}
		
		if($buyer_id == $seller_id)
		{
			$this->show_warning("不能购买自己的商品！");
			return;
		}
		
		if($goods_info['type'] == 3)
		{
			$this->show_warning('您购买的商品已售出，请重新选择！');
			return;
		}
		
		$cart_info = $credit_cart_mod->getRow("select * from pa_credit_cart where buyer_id = " .$buyer_id);

		if($cart_info)
		{
			$this->show_warning("您一次只可以购买一件商品!");
			return;
		}
		$data=array();
    	$data['credit_id'] = $credit_id;
    	$data['seller_id'] = $seller_id;
    	$data['seller_name'] = $seller_name;
    	$data['buyer_id'] = $buyer_id;
    	$data['buyer_name'] = $buyer_name;
    	$data['price'] = $price;
    	$data['credit'] = $credit;
    	$data['time'] = time();
    	$cart = $credit_cart_mod->add($data);
    	
    	if (!$cart)
    	{
    		$this->show_warning("添加购物车失败,请重新添加！");
    		return;
    	}
    	
    	header("Location:index.php?app=plb_cart&credit_id=$credit_id");
    	
	}
	
	//删除
	function drop()
	{
		$credit_cart_mod = & m('creditcart');
		$credit_id = empty($_GET['credit_id']) ? 0 : intval($_GET['credit_id']);
		$cart = $credit_cart_mod->getRow("select * from pa_credit_cart where credit_id = " .$credit_id);
		$id = intval($cart['id']);
		$credit_cart_mod->drop($id);
		header("Location:index.php?app=plb_cart&act=cart");
	}
	
	//购物车为空
	function cart()
    {
        $this->display('plb_cart.empty.html');
    }
    
    //填写订单
    function order()
    {
    	$credit_cart_mod = & m('creditcart');
    	$credit_order_mod = & m('creditorder');
    	$credit_goods_mod = & m('creditgoods');
		$user_id = $this->visitor->get('user_id');
		$credit_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if (0 == $credit_id)
		{
			$this->show_warning('购物车是空的!');
			return;
		}
		//var_dump($credit_id);
		
		$cartinfo = $credit_cart_mod->getRow("select c.credit_id,c.id,c.seller_id,c.seller_name,c.price,c.credit,g.info from pa_credit_cart c 
											left join pa_credit_goods g on c.credit_id = g.id where c.buyer_id = " . $user_id);
		
		$cid = $cartinfo['id'];//获取删除的id
		$seller_id = $cartinfo['seller_id'];
		$seller_name = $cartinfo['seller_name'];
		$buyer_name = $this->visitor->get('user_name');
		$order_amount = $cartinfo['price'];
		$credit = $cartinfo['credit'];
		$id = $cartinfo['credit_id'];//判断是否售出的id
		$this->assign('cart',$cartinfo);
		
    	if(!IS_POST)
    	{
    		//确认订单前的判断
			$goods_info = $credit_goods_mod->getRow("select * from pa_credit_goods where id = " .$credit_id);
	    	if($goods_info['type'] == 3)
			{
				$this->show_warning('您购买的商品已售出，请重新选择！');
				return;
			}
    		$this->display("plb_order.form.html");
    	} else {
	    	//提交订单前的判断
			if (!$id)
			{
				$this->show_warning('购物车是空的!');
				return;
			}
	    	$goods_info1=$credit_goods_mod->getRow("select * from pa_credit_goods where id = " .$id);
	    	if($goods_info1['type'] == 3)
			{
				$this->show_warning('您购买的商品已售出，请重新选择！');
				return;
			}
    		
    		//自动获取订单号
			//$order_sn = strtoupper(substr(uniqid(rand()), -10));//有英文字母的订单号
			mt_srand((double)microtime()*1000000);//纯数字订单号
			$timestamp = gmtime();
			$y = date('y',$timestamp);
			$z = date('z',$timestamp);
			$order_sn = $y.str_pad($z,3,'0',STR_PAD_LEFT).str_pad(mt_rand(1,99999),5,'0',STR_PAD_LEFT);;
    		$data=array();
    		$data['order_sn'] = $order_sn;
	    	$data['seller_id'] = $seller_id;
    		$data['seller_name'] = $seller_name;
    		$data['buyer_id'] = $user_id;
     		$data['buyer_name'] = $buyer_name;
    		$data['add_time'] = time();
    		$data['content'] = trim($_POST['content']);
    		$data['order_amount'] = $order_amount;
    		$data['credit'] = $credit;
    		$data['credit_id'] = $credit_id;
    		$order = $credit_order_mod->add($data);
    		$credit_cart_mod->drop($cid);
    		header("Location:index.php?app=plb_cart&act=payment&id={$order}");
    	 }
    	
    }
    function payment()
    {
    	$id= empty($_GET['id']) ? 0 : intval($_GET['id']); //外部提供订单ID
    	if (!id)
    	{
    		$this->show_warning("未找到订单!");
    		return;
    	}
    	$credit_order_mod = & m('creditorder');
    	$user_id=$this->visitor->get('user_id');
    	
    	$credit_order_info = $credit_order_mod->getRow("select * from pa_credit_order where buyer_id = " .$user_id . " and id = " .$id);
    	if (!$credit_order_info)
    	{
    		$this->show_warning("未找到订单!");
    		return;
    	}
    	
    	if ($credit_order_info['payment_code'] != 'cod' && $credit_order_info['status'] != ORDER_PENDING) //订单有效性判断 
    	{
    		$this->show_warning("订单不可用!");
    		return;
    	}
    	$payment_model =& m('payment');
    	if (!$credit_order_info['payment_id']) //判断是否选择支付方式, 如果未选择, 让其选择支付方式
    	{
	    	/* 若还没有选择支付方式，则让其选择支付方式 */
	        $payments = $payment_model->get_enabled(0);
            if (empty($payments))
            {
                $this->show_warning('没有可选的支付方式!');

                return;
            }
            
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
            $this->assign('order',$credit_order_info);
            $this->assign('payments', $all_payments);
            $this->display("plb.payment.html");
    	} else {  //如果有选择支付方式, 直接跳转支付网关
    		/* 否则直接到网关支付 */
            /* 验证支付方式是否可用，若不在白名单中，则不允许使用 */
            if (!$payment_model->in_white_list($credit_order_info['payment_code']))
            {
                $this->show_warning('payment_disabled_by_system');

                return;
            }
            
    		$payment_info  = $payment_model->get("payment_code = '{$credit_order_info['payment_code']}' AND store_id=0");
            /* 若后台没有启用支付方式，则不允许使用 */
            if (!$payment_info['enabled'])
            {
                $this->show_warning('payment_disabled');

                return;
            }
            
            /* 生成支付URL或表单 */
            $payment    = $this->_get_payment($credit_order_info['payment_code'], $payment_info);
            $payment_form = $payment->get_credit_payform($credit_order_info);
            
            /* 线下付款的 目前不支持线下付款的方式  */
            if (!$payment_info['is_online'])
            {
            	$this->show_warning("目前不支持线下付款! 请选择线上支付方式.!");
            	return;
                $this->_curlocal(
                    Lang::get('post_pay_message')
                );
            }

            /* 跳转到真实收银台 */
            $this->_config_seo('title', Lang::get('cashier'));
            $this->assign('payform', $payment_form);
            $this->assign('payment', $payment_info);
            $this->assign('order', $credit_order_info);
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->display('plb.payform.html');
    	}
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
        $order_model =& m('creditorder');
        $order_info  = $order_model->get("id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_warning('no_such_order');

            return;
        }

        #可能不合适
        if ($order_info['payment_id'])
        {
            $this->_goto_pay($order_id);
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

        /* 如果是货到付款，则改变订单状态 */
        if ($payment_info['payment_code'] == 'cod')
        {
            $edit_data['status']    =   ORDER_SUBMITTED;
        }

        $order_model->edit($order_id, $edit_data);

        /* 开始支付 */
        $this->_goto_pay($order_id);
    }
    
	function _goto_pay($order_id)
    {
        header('Location:index.php?app=plb_cart&act=payment&id=' . $order_id);
    }
/**
     *    支付完成后返回的URL，在此只进行提示，不对订单进行任何修改操作,这里不严格验证，不改变订单状态
     *
     *    @author    Garbin
     *    @return    void
     */
    function returnurl()
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
			$order_mod=& m('creditorder');
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
        $model_order =& m('creditorder');
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
        $this->_change_order_status($order_id, $order_info['extension'], $notify_result);

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
			$order_mod = & m('creditorder');
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
        $model_order =& m('creditorder');
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
        $this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        $payment->verify_result(true);
        $this->finish($order_id);

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
        $order_type->respond_credit_notify($order_id, $notify_result);    //响应通知
    }
    
    /**
     * 		支付买家积分
     * 
     */
    public function finish($order_id)
    {
		if (!$order_id)
		{
			$this->show_warning('没有可用订单!');
			return;
		}    	
		$order_mod = & m('creditorder');
		$order_info = $order_mod->get($order_id);
		if (!$order_info)
		{
			$this->show_warning('没有可用订单!');
			return;
		}
    }
}
?>