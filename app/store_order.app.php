<?php

/**
 *    售货员控制器，其扮演实际交易中柜台售货员的角色，你可以这么理解她：你告诉我（售货员）要买什么东西，我会询问你你要的收货地址是什么之类的问题
 ＊        并根据你的回答来生成一张单子，这张单子就是“订单”
 *
 *    @author    Garbin
 *    @param    none
 *    @return    void
 */
class Store_orderApp extends StoreadminbaseApp
{
    /**
     *    填写收货人信息，选择配送，支付方式。
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    {
        $goods_info = $this->_get_goods_info();
        if (!$goods_info)
        {	
            //购物车是空的 
            $this->show_storeadmin_warning('goods_empty');
            return;
        }
        if (!IS_POST)
        {
            /* 根据商品类型获取对应订单类型 */
            $goods_type = &gt($goods_info['type']);
            $order_type = &ot($goods_info['otype']);
			
            /* 显示订单表单 */
            $form = $order_type->get_store_order_form();
            if ($form === false)
            {
                $this->show_storeadmin_warning($order_type->get_error());

                return;
            }     
            $this->assign('goods_info', $goods_info);
            $this->assign('image_url',IMAGE_URL);
            $this->assign($form['data']);
            $this->display($form['template']);
        }
        else
        {
            /* 根据商品类型获取对应的订单类型 */
        	$goods_type  =&gt($goods_info['type']);
            $order_type  =&ot($goods_info['otype']);

            /* 将这些信息传递给订单类型处理类生成订单(你根据我提供的信息生成一张订单) */
            $order_id = $order_type->submit_store_order(array(
                'goods_info'    =>  $goods_info,      //商品信息（包括列表，总价，总量，所属店铺，类型）,可靠的!
                'post'          =>  $_POST,           //用户填写的订单信息
            ));
            if (!$order_id)
            {
                $this->show_storeadmin_warning($order_type->get_error());

                return;
            }
			
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
            /* 下单完成后清理商品，如清空购物车*/
            $this->_clear_goods();
			//获取店铺名称
			$_store_goods_mod = &m('storegoods');
			$store_info = $_store_goods_mod->getRow('select s.store_name,so.order_sn from pa_store_order so left join pa_store s on so.seller_id = s.store_id where so.order_id ='.$order_id);
        	$smslog =&  m('smslog'); 
	      	import('class.smswebservice');    //导入短信发送类
	   		$sms = SmsWebservice::instance(); //实例化短信接口类
		    $smscontent = "店铺:{$store_info['store_name']},已经成功付款，订单号:{$store_info['order_sn']}。请尽快修改物流费用,并联系店面管理部！";
		    $mobile = DEF_MOBILE;
		    $verifytype = "storebuygoods";	
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
	       	
            /* 发送邮件 */
//            $model_order =& m('storeorder');
//
//            /* 获取订单信息 */
//            $order_info = $model_order->get($order_id);

            /* 发送事件 */
//            $feed_images = array();
//            foreach ($goods_info['items'] as $_gi)
//            {
//                $feed_images[] = array(
//                    'url'   => SITE_URL . '/' . $_gi['goods_image'],
//                    'link'  => SITE_URL . '/' . url('app=goods&id=' . $_gi['goods_id']),
//                );
//            }
//            $this->send_feed('order_created', array(
//                'user_id'   => $this->visitor->get('user_id'),
//                'user_name' => addslashes($this->visitor->get('user_name')),
//                'seller_id' => $order_info['seller_id'],
//                'seller_name' => $order_info['seller_name'],
//                'store_url' => SITE_URL . '/' . url('app=store&id=' . $order_info['seller_id']),
//                'images'    => $feed_images,
//            ));

            $buyer_address = $this->visitor->get('email');

//            $model_supply =& m('supply'); //做出修改, 此处的卖家是供应商
//            $supply_info  = $model_supply->get($goods_info['supply_id']);
//            $seller_address= $supply_info['email'];

//            /* 发送给买家下单通知 */
//            $buyer_mail = get_mail('tobuyer_new_order_notify', array('order' => $order_info));
//            $this->_mailto($buyer_address, addslashes($buyer_mail['subject']), addslashes($buyer_mail['message']));
//
//            /* 发送给卖家新订单通知 */
//            $seller_mail = get_mail('toseller_new_order_notify', array('order' => $order_info));
//            $this->_mailto($seller_address, addslashes($seller_mail['subject']), addslashes($seller_mail['message']));
			
            /* 到收银台付款 */			
            header('Location:index.php?app=store_cashier&order_id=' . $order_id); 
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
        
    	$return = array();
        /* 从购物车中取商品 */
		$cart_model =& m('commoncart');
        $sql = "SELECT gs.gprice,gs.price as sprice, gs.credit,gs.spec_id,c.goods_id,c.goods_name,gs.zprice as price,c.goods_image,c.quantity,c.specification FROM pa_common_cart c left join pa_goods_spec gs on c.spec_id=gs.spec_id 
				where c.buyer_id = " . $this->visitor->get('user_id');
		$cart_items      =  $cart_model->getAll($sql);
	    if ($cart_items)
	    {
	    	foreach ($cart_items as $key=>$item)
	        {
	            /* 小计 */
	            $cart_items[$key]['subtotal']   = $item['price'] * $item['quantity'];
				empty($item['goods_image']) && $item['goods_image'] = IMAGE_URL.Conf::get('default_goods_image');
	            $return['amount']     += $cart_items[$key]['subtotal'];   //各店铺的总金额
	            $return['quantity']   += $cart_items[$key]['quantity'];   //各店铺的总数量
	        }
	
	       $return['items']        =   $cart_items;
	       $return['type']         =   'material';
	       $return['otype']        =   'normal';
	    }
       
       return $return;
    }

    /**
     *    下单完成后清理商品
     *
     *    @author    Garbin
     *    @return    void
     */
    function _clear_goods()
    {
    	$userid = $this->visitor->get('user_id');
        $model_cart =& m('commoncart');
        $model_cart->drop("buyer_id={$userid}");
    }


}
?>
