<?php

/**
 *    售货员控制器，其扮演实际交易中柜台售货员的角色，你可以这么理解她：你告诉我（售货员）要买什么东西，我会询问你你要的收货地址是什么之类的问题
 ＊        并根据你的回答来生成一张单子，这张单子就是“订单”
 *
 *    @author    Garbin
 *    @param    none
 *    @return    void
 */
class OrderApp extends ShoppingbaseApp
{
	var $order_goods_mod;
    /**
     *    填写收货人信息，选择配送，支付方式。
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    {
    	$model_order = &m('order');
        $goods_info = $this->_get_goods_info();
        $cart_itmes = $goods_info['items'];
    	if (!is_array($cart_itmes))
        {
            /* 购物车是空的 */
            $this->show_warning('goods_empty');
            return ;
        }
		foreach ($cart_itmes as $rec_id => $goods)
        {
             
             $money+= $goods['price'] * $goods['quantity'];  
             $credit+= $goods['credit'];
             $quantity_tatol+= $goods['quantity'];  
        }
        //订单总价等于
        $credit_total = empty($credit) ? 0.00 : floatval($credit);
        $amount_total = empty($money) ? 0.00 : floatval($money);
        
        //查询买家所有积分
        $user_id = $_SESSION['user_info']['user_id'];
        $member_model = & m('member');
        $member_info = $member_model->get("user_id='{$user_id}'");
   
        $member_credit = $member_info['credit'] - $member_info['frozen_credit']; //用户可用积分余额
        
        $this->assign('member_credit',$member_credit);
        $this->assign('real_name',$member_info['real_name']);
        $this->assign('member_mobile',$member_info['mobile']);
		$this->assign('credit_total',$credit_total);
		$this->assign('amount_total',$amount_total);
		$this->assign('quantity_tatol',$quantity_tatol);
        

        /*  检查库存 */
        $goods_beyond = $this->_check_beyond_stock($goods_info['items']);
        if ($goods_beyond)
        {
            $str_tmp = '';
            foreach ($goods_beyond as $goods)
            {
                $str_tmp .= '<br /><br />' . $goods['goods_name'] . '&nbsp;&nbsp;' . $goods['specification'] . '&nbsp;&nbsp;' . Lang::get('stock') . ':' . $goods['stock'];
            }
            $this->show_warning(sprintf(Lang::get('quantity_beyond_stock'), $str_tmp));
            return;
        }
		
        
        if (!IS_POST)
        {
            /* 根据商品类型获取对应订单类型 */
            $goods_type     =&  gt($goods_info['type']);
            $order_type     =&  ot($goods_info['otype']);
            /* 显示订单表单 */
            $form = $order_type->get_order_form($goods_info['store_id']);
            if ($form === false)
            {
                $this->show_warning($order_type->get_error());

                return;
            }
            $this->_curlocal(
                LANG::get('create_order')
            );
            
            
            $this->_config_seo('title', Lang::get('confirm_order') . ' - ' . Conf::get('site_title'));
            $this->assign('goods_info', $goods_info);
            
            $this->assign('goods_count',count($goods_info));
            $this->assign($form['data']);
            $this->assign('store_id', STORE_ID);
            $this->display('order.form.html');          
        }
        else
        {
            /* 在此获取生成订单的两个基本要素：用户提交的数据（POST），商品信息（包含商品列表，商品总价，商品总数量，类型），所属店铺 */
            $store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : STORE_ID;
            if ($goods_info === false)
            {
                /* 购物车是空的 */
                $this->show_warning('goods_empty');

                return;
            }
            
            $_POST['use_credit'] = $_POST['get_credit'] = $_POST['money'] = 0;
			
        	if(is_array($_POST['cartinfo']))
        	{
        		foreach ($goods_info['items'] as $key => $value)
        		{
	        		if(in_array($value['gs_id'],$_POST['cartinfo']))
	        		{
	        		    $_POST['use_credit'] += $value['subtotal'];
	        		    $m_credit +=  $value['credit'];
	        		}else
	        		{
	        		    $_POST['use_credit'] += 0;
	        		    $m_credit +=  0;
	        		}
        		}
        		$_POST['get_credit'] = $credit_total-$m_credit;
        		if ($store_id == STORE_ID)
         		{
         			$_POST['money'] = $amount_total + $goods_info['shipmoney'] - $_POST['use_credit'];
         		}else{
         			$_POST['money'] = $amount_total - $_POST['use_credit'];
         		}
        	}else
        	{
        		$_POST['use_credit'] = 0;
        		$_POST['get_credit'] = $credit_total;
        		if ($store_id == STORE_ID)
         		{
         			$_POST['money'] = $amount_total + $goods_info['shipmoney'];
         		}else{
         			$_POST['money'] = $amount_total;
         		}
        	}
        	//用积分抵扣物流费用
        	if($_POST['shipp'] == 1)
        	{
        		$shipping_fee = $goods_info['shipmoney']; 
        		$_POST['use_credit'] += $shipping_fee;
        		$_POST['money'] -=  $shipping_fee;
        	}
        	//如果支付现金金额为0，则支付方式一定为积分支付
        	if ($_POST['money'] == 0)
        	{
        		$_POST['payType'] = 2; //积分支付
        	}
            
            /* 根据商品类型获取对应的订单类型 */
            $goods_type =& gt($goods_info['type']);
            $order_type =& ot($goods_info['otype']);
            /* 将这些信息传递给订单类型处理类生成订单(你根据我提供的信息生成一张订单) */
            
            $order_id = $order_type->submit_order(array(
                'goods_info'    =>  $goods_info,      //商品信息（包括列表，总价，总量，所属店铺，类型）,可靠的!
                'post'          =>  $_POST,           //用户填写的订单信息
            ));
            if (!$order_id)
            {
                $this->show_warning($order_type->get_error());

                return;
            }
			/* 如果member中的real_name为空，则默认存入*/
			if(empty($member_info['real_name']) && isset($_POST['consignee'])){
				$data_phone = array("real_name" => trim($_POST['consignee']));
			}
			$model_member=& m('member');
			$model_member->edit($user_id,$data_phone);
            /*  检查是否添加收货人地址  */
            if (isset($_POST['save_address']) && (intval(trim($_POST['save_address'])) == 1))
            {
                 $data = array(
                    'user_id'       => $this->visitor->get('user_id'),
                    'consignee'     => trim($_POST['consignee']),
                    'region_id'     => $_POST['region_id'],
                    'region_name'   => $_POST['region_name'],
                    'address'       => trim($_POST['address']),
                    'zipcode'       => trim($_POST['zipcode']),
                    'phone_tel'     => trim($_POST['phone_tel']),
                    'phone_mob'     => trim($_POST['phone_mob']),
                );
                $model_address =& m('address');
                $model_address->add($data);
            }
            
            /* 下单完成后清理商品，如清空购物车，或将团购拍卖的状态转为已下单之类的 */
            $this->_clear_goods($order_id);

            /* 发送邮件 */
            $model_order =& m('order');

            /* 减去商品库存 */
            //$model_order->change_stock('-', $order_id);

            /* 获取订单信息 */
            $order_info = $model_order->get($order_id);
            /* 发送事件 */
            $feed_images = array();
        	
            foreach ($goods_info['items'] as $_gi)
            {
                $feed_images[] = array(
                    'url'   => SITE_URL . '/' . $_gi['goods_image'],
                    'link'  => SITE_URL . '/' . url('app=goods&id=' . $_gi['goods_id']),
                );
            }
            $this->send_feed('order_created', array(
                'user_id'   => $this->visitor->get('user_id'),
                'user_name' => addslashes($this->visitor->get('user_name')),
                'seller_id' => $order_info['seller_id'],
                'seller_name' => $order_info['seller_name'],
                'store_url' => SITE_URL . '/' . url('app=store&id=' . $order_info['seller_id']),
                'images'    => $feed_images,
            ));
            
            if ($_POST['money'] > 0)
        	{
	            if ($_POST['use_credit']>0){
		            //冻结用户积分操作
		          	changeMemberCreditOrMoney(intval($order_info['buyer_id']),$_POST['use_credit'],FROZEN_CREDIT);
		          	
		            //更新日志
		            $credit_notes_info = array(
		            	'user_id'	    => intval($order_info['buyer_id']),
		            	'frozen_credit'	=> $_POST['use_credit'],
		            	'change_desc'   => '（店铺ID：'.$order_info['seller_id'].',店铺名：'.$order_info['seller_name'].'） 冻结用户积分：'.$_POST['use_credit'].'PL',
		            	'order_id'      => $order_id,
			            'change_time'	=>	time(),
		            	'change_type'	=> 	31,
		            );
		            //增加用户积分冻结记录
		            
		            add_account_log($credit_notes_info); 
	            }
        	}
            $buyer_address = $this->visitor->get('email');
            $model_member =& m('member');
            $member_info  = $model_member->get($goods_info['user_id']);
            $seller_address= $member_info['email'];
            
//            $user_info = $model_member->get($order_info['buyer_id']);
//            /* 发送给买家下单通知 */
//            $buyer_mail = get_mail('tobuyer_new_order_notify', array('order' => $order_info));
//            $this->_mailto($buyer_address, addslashes($buyer_mail['subject']), addslashes($buyer_mail['message']));
//
//            /* 发送给卖家新订单通知 */
//            $seller_mail = get_mail('toseller_new_order_notify', array('order' => $order_info));
//            $this->_mailto($seller_address, addslashes($seller_mail['subject']), addslashes($seller_mail['message']));
//
//            $this->send_sms_verify($user_info['mobile'], $order_info['order_sn'], $order_id); 
            
            /* 更新下单次数 */
            $model_goodsstatistics =& m('goodsstatistics');
            $goods_ids = array();
            foreach ($goods_info['items'] as $goods)
            {
                $goods_ids[] = $goods['goods_id'];
            }
            $model_goodsstatistics->edit($goods_ids, 'orders=orders+1'); 
            /* 到收银台付款 */			
            header('Location:index.php?app=cashier&order_id=' . $order_id);
        }
    }

    /**
     *    获取外部传递过来的商品
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_goods_info()
    {
        $return = array(
            'items'     =>  array(),    //商品列表
            'quantity'  =>  0,          //商品总量
            'amount'    =>  0,          //商品总价
        	'credit_total' => 0,		//商品总的赠送积分
            'store_id'  =>  0,          //所属店铺
            'store_name'=>  '',         //店铺名称
            'type'      =>  null,       //商品类型
            'otype'     =>  'normal',   //订单类型
            'allow_coupon'  => true,    //是否允许使用优惠券
        );
        switch ($_GET['goods'])
        {
            case 'groupbuy':
                /* 团购的商品 */
                $group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;
                $user_id  = $this->visitor->get('user_id');
                if (!$group_id || !$user_id)
                {
                    return false;
                }
                /* 获取团购记录详细信息 */
                $model_groupbuy =& m('groupbuy');
                $groupbuy_info = $model_groupbuy->get(array(
                    'join'  => 'be_join, belong_store, belong_goods',
                    'conditions'    => $model_groupbuy->getRealFields("groupbuy_log.user_id={$user_id} AND groupbuy_log.group_id={$group_id} AND groupbuy_log.order_id=0 AND this.state=" . GROUP_FINISHED),
                    'fields'    => 'store.store_id, store.store_name, goods.goods_id, goods.goods_name, goods.default_image, groupbuy_log.quantity, groupbuy_log.spec_quantity, this.spec_price ,goods.simage_url',
                ));

                if (empty($groupbuy_info))
                {
                    return false;
                }

                /* 库存信息 */
                $model_goodsspec = &m('goodsspec');
                $goodsspec = $model_goodsspec->find('goods_id='. $groupbuy_info['goods_id']);
                /* 获取商品信息 */
                $spec_quantity = unserialize($groupbuy_info['spec_quantity']);
                $spec_price    = unserialize($groupbuy_info['spec_price']);
                $amount = 0;
                $groupbuy_items = array();
                $goods_image = empty($groupbuy_info['default_image']) ? Conf::get('default_goods_image') : $groupbuy_info['default_image'];
                foreach ($spec_quantity as $spec_id => $spec_info)
                {
                    $the_price = $spec_price[$spec_id]['price'];
                    $subtotal = $spec_info['qty'] * $the_price;
                    $groupbuy_items[] = array(
                        'goods_id'  => $groupbuy_info['goods_id'],
                        'goods_name'  => $groupbuy_info['goods_name'],
                        'spec_id'  => $spec_id,
                        'specification'  => $spec_info['spec'],
                        'price'  => $the_price,
                        'quantity'  => $spec_info['qty'],
                        'goods_image'  => IMAGE_URL.$goods_image,
                    	'simage_url'	=> IMAGE_URL.$groupbuy_info['simage_url'],
                        'stock' => $goodsspec[$spec_id]['stock'],
                    );
                    $amount += $subtotal;
                }

                $return['items']        =   $groupbuy_items;
                $return['quantity']     =   $groupbuy_info['quantity'];
                $return['amount']       =   $amount;
                $return['store_id']     =   $groupbuy_info['store_id'];
                $return['store_name']   =   $groupbuy_info['store_name'];
                $return['type']         =   'material';
                $return['otype']        =   'groupbuy';
                $return['allow_coupon'] =   false;
            break;
            default:
                /* 从购物车中取商品 */
                $_GET['store_id'] = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
                $store_id = $_GET['store_id'];
                if (!$store_id)
                {
                    return false;
                }
                
				$cart_model =& m('cart');
				$sql = "SELECT c.gs_id,c.specification,gs.zprice,gs.gprice,gs.commodity_code,gs.spec_1,gs.spec_2,gs.spec_id,gs.weight,g.goods_id,g.goods_name,g.spec_name_1,g.spec_name_2,c.pr_id,gs.credit,gs.price,c.goods_image,g.default_image,g.yimage_url,g.mimage_url,g.smimage_url,g.dimage_url,g.simage_url,c.autotrophy,c.quantity,c.shipmoney FROM pa_cart c left join pa_goods_spec gs on c.spec_id=gs.spec_id left join pa_goods g on gs.goods_id = g.goods_id
				where c.user_id = " . $this->visitor->get('user_id') . " AND c.store_id = {$store_id} AND c.session_id='" . SESS_ID . "'";
				$cart_items      =  $cart_model->getAll($sql);
				foreach ($cart_items as $k => $v)
				{
					if($v['pr_id'] == 0){
						$store_goods_model = & m('storegoods');
						$store_goodsinfo  =  $store_goods_model->getRow("select * from pa_store_goods where store_id=".$store_id." AND gs_id=".$v['gs_id']);
						$v['stock'] = $store_goodsinfo['stock'];
						$cart_items[$k] = $v;
					}else {
						$promotion_mod = & m('promotion');
						$promotion_info = $promotion_mod->get_promotion($v['pr_id']);
						$v['stock'] = $promotion_info['pr_stock'];
						$v['price'] = $promotion_info['pr_price'];  //促销价格
						$v['credit'] = $promotion_info['pr_credit'];//促销赠送派啦币
						$cart_items[$k] = $v;
					}	
				}		
                //转化图片url,给它加上同步服务器
				foreach($cart_items as $k => $item) {
                	$cart_items[$k]['goods_image'] = IMAGE_URL.$item['goods_image'];
                	$cart_items[$k]['default_image'] = IMAGE_URL.$item['default_image'];
                	$cart_items[$k]['yimage_url'] = IMAGE_URL.$item['yimage_url'];
                	$cart_items[$k]['mimage_url'] = IMAGE_URL.$item['mimage_url'];
                	$cart_items[$k]['smimage_url'] = IMAGE_URL.$item['smimage_url'];
                	$cart_items[$k]['dimage_url'] = IMAGE_URL.$item['dimage_url'];
                	$cart_items[$k]['simage_url'] = IMAGE_URL.$item['simage_url'];
                	$cart_items[$k]['newcredit'] = $cart_items[$k]['credit'];
                	$cart_items[$k]['credit'] = $cart_items[$k]['credit'] * $cart_items[$k]['quantity'];
                }
                if (empty($cart_items))
                {
                    return false;
                }


                $store_model =& m('store');
                $store_info = $store_model->get($store_id);	
                
                foreach ($cart_items as $rec_id => $goods)
                {
                	$return['quantity'] += $goods['quantity'];                      //商品总量
                    $return['amount']   += $goods['quantity'] * $goods['price'];    //商品总价
                    $return['credit_total'] +=  $goods['credit']; //赠送总积分      	
                    $cart_items[$rec_id]['subtotal']    =   $goods['quantity'] * $goods['price'];   //小计
                    $return['shipmoney'] += $goods['shipmoney']; //计算购物车商品总的物流费用
                    empty($goods['goods_image']) && $cart_items[$rec_id]['goods_image'] = Conf::get('default_goods_image');						
                }
                $return['items']        =   $cart_items;
                $return['store_id']     =   $store_id;
                $return['store_name']   =   $store_info['store_name'];
                $return['type']         =   'material';
                $return['otype']        =   'normal';
            break;
        }	
        return $return;
    }

    /**
     *    下单完成后清理商品
     *
     *    @author    Garbin
     *    @return    void
     */
    function _clear_goods($order_id)
    {
        switch ($_GET['goods'])
        {
            case 'groupbuy':
                /* 团购的商品 */
                $model_groupbuy =& m('groupbuy');
                $model_groupbuy->updateRelation('be_join', $_GET['group_id'], $this->visitor->get('user_id'), array(
                    'order_id'  => $order_id,
                ));
            break;
            default://购物车中的商品
                /* 订单下完后清空指定购物车 */
                $_GET['store_id'] = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
                $store_id = $_GET['store_id'];
                if (!$store_id)
                {
                    return false;
                }
                $model_cart =& m('cart');
                $model_cart->drop("store_id = {$store_id} AND session_id='" . SESS_ID . "'");
                //优惠券信息处理
                if (isset($_POST['coupon_sn']) && !empty($_POST['coupon_sn']))
                {
                    $sn = trim($_POST['coupon_sn']);
                    $couponsn_mod =& m('couponsn');
                    $couponsn = $couponsn_mod->get("coupon_sn = '{$sn}'");
                    if ($couponsn['remain_times'] > 0)
                    {
                        $couponsn_mod->edit("coupon_sn = '{$sn}'", "remain_times= remain_times - 1");
                    }
                }
            break;
        }
    }
    /**
     * 检查优惠券有效性
     */
    function check_coupon()
    {
        $coupon_sn = $_GET['coupon_sn'];
        $store_id = $_GET['store_id'];
        if (empty($coupon_sn))
        {
            $this->js_result(false);
        }
        $coupon_mod =& m('couponsn');
        $coupon = $coupon_mod->get(array(
            'fields' => 'coupon.*,couponsn.remain_times',
            'conditions' => "coupon_sn.coupon_sn = '{$coupon_sn}' AND coupon.store_id = " . $store_id,
            'join'  => 'belongs_to_coupon'));
        if (empty($coupon))
        {
            $this->json_result(false);
            exit;
        }
        if ($coupon['remain_times'] < 1)
        {
            $this->json_result(false);
            exit;
        }
        $time = gmtime();
        if ($coupon['start_time'] > $time)
        {
            $this->json_result(false);
            exit;
        }


        if ($coupon['end_time'] < $time)
        {
            $this->json_result(false);
            exit;
        }

        // 检查商品价格与优惠券要求的价格

        $model_cart =& m('cart');
        $item_info  = $model_cart->find("store_id={$store_id} AND session_id='" . SESS_ID . "'");
        $price = 0;
        foreach ($item_info as $val)
        {
            $price = $price + $val['price'] * $val['quantity'];
        }
        if ($price < $coupon['min_amount'])
        {
            $this->json_result(false);
            exit;
        }
        $this->json_result(array('res' => true, 'price' => $coupon['coupon_value']));
        exit;

    }

    function _check_beyond_stock($goods_items)
    {
        $goods_beyond_stock = array();
        foreach ($goods_items as $rec_id => $goods)
        {
            if ($goods['quantity'] > $goods['stock'])
            {
                $goods_beyond_stock[$goods['spec_id']] = $goods;
            }
        }
        return $goods_beyond_stock;
    }
    
    function getRegionId() {
    	$aid = empty($_GET['aid']) ? 0 : intval($_GET["aid"]);
    	if($aid == 0) {
    		$this->json_error('get_value_null');
    		return;
    	}
    	$_address_mod = & m('address');
    	$address_info = $_address_mod->get($aid);
    	$region_id = $address_info['region_id'];
    	$this->json_result($region_id);
    }
	function send_sms_verify($mobile, $order_sn, $order_id)
    {
        if (!$mobile)
        {
            $this->show_warning('手机号为空！');  //手机号码为空
           	return;
        }else
        {
        	if (is_mobile($mobile))
        	{
        		$smslog =&  m('smslog'); 
	        
        		$ms =& ms();    //连接用户系统
        	
        		//由于虚拟主机中php运行环境暂时未配置开启soap扩展，所以暂时不使用webservice方式
        		import('class.smswebservice');    //导入短信发送类
        		$sms = SmsWebservice::instance(); //实例化短信接口类
        		$verify = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);//验证码
        		$verifytype = $_GET['verifytype']?$_GET['verifytype']:'buy_verify'; //短信验证码类型
        		if ($verifytype=='modifymobile')
        		{
        			$smscontent = str_replace('{verify}',$verify,Lang::get('smscontent.modifymobile_verify'));
        		}else 
        		{
        			$smscontent = str_replace('{verify}',$verify,'尊敬的客户,您的订单号为:'.$order_sn . "取货手机验证码为{verify}【派啦网】祝福您购物愉快！");
        		}
				//echo "OK";
        		$result= $sms->SendSms2($mobile,$smscontent); //执行发送短信验证码操作
        		//短信发送成功
        		$order_mod = & m('order');
        		if ($result == 0) 
        		{
        			//将验证码写入SESSION
        			$time = time();
        			
        			$order_mod->edit($order_id, array('rule_num' => $verify));

        			//执行短信日志写入操作
        			$smsdata['mobile'] = $mobile;
        			$smsdata['smscontent'] = $smscontent;
        			$smsdata['type'] = $verifytype; //注册验证短信
        			$smsdata['sendtime'] = $time;
        			
        			$smslog->add($smsdata);
        			
        		}else
        		{
	        		$this->show_warning('短信发送失败！');
	        		return;
        		}
        	} else {
        		$this->show_warning('手机号码格式不正确！');
        		return;
        	} 
        return;
   		}
     }
}
?>
