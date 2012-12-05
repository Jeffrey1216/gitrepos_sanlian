<?php
class QuickorderApp extends MallbaseApp {
		
		public function index() {
		// 一个店铺只有一个方式
		//$goods_info = $this->_get_goods_info();
		$goods_list = array();
		$goods_info = $this->_get_paila_goods_info();
		$memberinfo = $this->_get_member_info();	
		//var_dump($goods_info);
		$buyermobile = $memberinfo['mobile'];
        if ( $goods_info === false)
        {
            /* 购物车是空的 */
            $this->show_storeadmin_warning('goods_empty');

            return;
        }
   		
        if (!IS_POST)
        {
        	//生成订单信息
        	$order_info = array(
        	'order_sn' => $this->_gen_order_sn(),
        	'type' => 'material',
        	'extension' => 'normal',
        	'seller_id' => $goods_info['store_id'],
        	'seller_name' => $goods_info['store_name'],
        	'buyer_id' => $memberinfo['user_id'],
        	'buyer_name' => $memberinfo['user_name'],
        	'buyer_email' => $memberinfo['email'],
        	'add_time' => time(),
        	'payment_id' => PAILAPAY_ID,
        	'payment_name' => PAILAPAY_NAME,
        	'payment_code' => PAILAPAY_COD,
        	'out_trade_sn' => '',
        	'pay_time' =>'',
        	'pay_message' => '',
        	'ship_time' => '',
        	'invoice_no' => '',
        	'finished_time' => '',
        	'goods_amount' => $goods_info['amount'],
        	'discount' => '',
        	'order_amount' => $goods_info['amount'],
        	'evaluation_status' => '',
        	'evaluation_time' => '',
        	'anonymous' => '',
        	'postscript' => '',
        	'pay_alter' => '',
        	'area_type' => '',
        	'pay_type' => '',
        	'use_credit' => '',
        	'get_credit' => '',
        	'need_invoice' => '',
        	'invoice_header' => '',
        	'assign_store_id' => '',
        	'is_settle_accounts' => '',
        	'status' => ORDER_PENDING,
        	'ship_reason' => '',
        	'ship_query' => '',
        	'refund_cause' => '',
        	'ship_no' => '',
        	'refund_name' => '',
        	'refund_time' => '',
        	'rule_num' => '',
        	'buyer_mobile' => $memberinfo['mobile'],
 	        );  
 	        $order_mod = & m ('order');
 	        $order_goods_mod = & m('ordergoods');
 	        $order_log_mod = & m('orderlog');
       		$order_id = $order_mod->add($order_info);
       		$sql = "insert into pa_order_goods values";
       		$credit = 0.00;
        	//商品分类
	        foreach ($goods_info['items'] as $k => $v)
	        {
	        	if ($v['discount'] > 0.35) //不能用派啦币支付的
	        	{
	        		$goods_list['brand'][] = $v;
	        		$sql .= "(NULL, " . $order_id . ", '" . $v['goods_id'] . "', '" . $v['goods_name'] . "', '" . $v['spec_id'] . "', 0, '" . $v['price'] . "', '" . $v['quantity'] . "', '" . $v['simage_url'] . "', '', '', '0', '1' , '". $v['cprice'] ."', '". $v['sprice'] ."', '". $v['gprice'] ."','". $v['zprice']."', '".$v['credit']."'),";   
	        	}
	        	else 
	        	{
	        		$goods_list['paila'][] = $v;
	        		$sql .= "(NULL, " . $order_id . ", '" . $v['goods_id'] . "', '" . $v['goods_name'] . "', '" . $v['spec_id'] . "', 0, '" . $v['price'] . "', '" . $v['quantity'] . "', '" . $v['simage_url'] . "', '', '', '1', '1' , '". $v['cprice'] ."', '". $v['sprice'] ."', '". $v['gprice'] ."','". $v['zprice']."', '".$v['credit']."'),";
	        		$credit += $v['price'] * $v['quantity']; //3。5折以下商品， 价格 = 积分 
	        	}
	        } 	
	        $sql = substr($sql, 0, -1);
	        $order_goods_mod->db->query($sql);
	        //订单日志
	        $log = array(
	        	'order_id' => $order_id,
	        	'operator' => $this->visitor->get('user_name'),
	        	'order_status' => '无状态',
	        	'changed_status' => '待付款',
	        	'remark' => '快捷支付',
	        	'log_time' => time()
	        );
       		$order_log_mod->add($log);
       		if ($memberinfo['credit'] >= $credit)
       		{
       			$allowUseCredit = $credit;
       		} else {
       			$allowUseCredit = $memberinfo['credit'];
       		}
       		$this->assign('allowUseCredit', $allowUseCredit);
       		$this->assign('goods_info', $goods_info);
       		$this->assign("memberinfo",$memberinfo);
       		$this->assign("order_id",$order_id);
       		$this->display("paila_quick_order.index.html"); //支付页
        	
        }else
        {
        	$order_id = empty($_POST['order_id']) ? 0 :  intval($_POST['order_id']);
        	$paila_pay_cash = empty($_POST['paila_pay_cash']) ? 0 :  floatval($_POST['paila_pay_cash']);
        	$paila_get_credit = empty($_POST['paila_get_credit']) ? 0 :  floatval($_POST['paila_get_credit']);
        	$paila_pay_credit = empty($_POST['paila_pay_credit']) ? 0 :  floatval($_POST['paila_pay_credit']);
        	if ($paila_pay_cash != 0 && $paila_pay_credit != 0)
        	{
        		$pay_type = 3;
        	} else if ($paila_pay_cash == 0)
        	{
        		$pay_type = 2;
        	} else if ($paila_pay_credit == 0)
        	{
        		$pay_type = 1;
        	}
        	$meber_id = $memberinfo['user_id'];
        	if($pay_type !== 1){
	        	if ($meber_id !== 0)
	        	{
		        	if ($_POST['testpass'])
		        	{
		        		$testpass = empty($_POST['testpass']) ? 0 : intval($_POST['testpass']);
		        		
		        		$userObj = & ms();
		        		if (!$userObj->user->traderAuth($memberinfo['user_id'], $testpass))
		        		{
		        			$this->show_storeadmin_warning("支付密码验证失败");
		        			return ;
		        		}
		        		
		        	}else{
		        		$this->show_storeadmin_warning("支付密码不能为空");
		        		return ;
		        	}
	        	}
        	}
        	$order_mod = & m ('order');
 	        $order_goods_mod = & m('ordergoods');
 	        $order_log_mod = & m('orderlog');
 	        $member_mod = & m('member');
 	        $paila_goods_mod = & m('pailagoods');
 	        $model_cart = & m('quickcart');

			foreach ($goods_info['items'] as $k => $v) { // 插入定单商品记录
	        	//删除购物车中的物品
        		$rec_id = $v['rec_id'];
		        /* 从购物车中删除 */
		        $droped_rows = $model_cart->drop('rec_id=' . $rec_id . ' AND session_id=\'' . SESS_ID . '\'', 'store_id');
		        // 减少商户库存
		        $paila_goods_mod->db->query("update pa_paila_goods set stock = stock - " . intval($v['quantity']) . ' where goods_id=' . $v['goods_id'] . ' and spec_id=' . $v['spec_id']);
		     
	        	} 
 	         //订单日志
	        $log = array(
	        	'order_id' => $order_id,
	        	'operator' => $this->visitor->get('user_name'),
	        	'order_status' => "待付款",
	        	'changed_status' => '交易成功',
	        	'remark' => '快捷支付',
	        	'log_time' => time()
	        );
       		$order_log_mod->add($log);
       		$order_data = array(
       			'pay_time' => time(),
       			'payment_id' => 100,
       			'payment_name' => '快捷支付',
       			'payment_code' => 'quickorder',
       			'ship_time' => time(),
       			'finished_time' => time(),
       			'pay_type' => $pay_type,
       			'use_credit' => $paila_pay_credit,
       			'get_credit' => $paila_get_credit,
       			'status' => ORDER_FINISHED,
       		);
       		$order_mod->edit($order_id, $order_data);
			$this->assign('goods_info', $goods_info);
       		$this->assign("memberinfo",$memberinfo);
       		$goods_list = $order_goods_mod->getAll("select * from pa_order_goods where order_id = " . $order_id);
       		$order_info = $order_mod->get($order_id);
       		$this->assign("goods_list",$goods_list);
       		$this->assign("order_info",$order_info);
       		$this->assign("paila_pay_cash",$paila_pay_cash);
       		$this->assign("paila_get_credit",$paila_get_credit);
       		$this->assign("paila_pay_credit",$paila_pay_credit);
        	$this->display('quick_cashier.index.html'); //定单页
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
	/**
     *    生成派拉快捷支付订单号
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_paila_quick_order_sn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        //随机定单前缀
        $prefix = 'QPL';
        $order_sn = $prefix . $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $model_order =& m('quickorder');
        $orders = $model_order->find('order_sn="' . $order_sn.'"');
        if (empty($orders))
        {
            /* 否则就使用这个订单号 */
            return $order_sn;
        }

        /* 如果有重复的，则重新生成 */
        return $this->_gen_order_sn();
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
            'otype'     =>  'offline',   //订单类型
            'allow_coupon'  => false,    //是否允许使用优惠券
        );
        switch ($_GET['goods'])
        {
            default:
                /* 从购物车中取商品 */
                $_GET['store_id'] = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
                $store_id = $_GET['store_id'];
                if (!$store_id)
                {
                    return false;
                }
                $cart_model =& m('quickcart');
				$sql = "SELECT * FROM pa_quick_cart c left join pa_goods_spec gs on c.spec_id=gs.spec_id left join pa_goods g on gs.goods_id = g.goods_id 
				where  c.session_id='" . SESS_ID . "'";
				$cart_items      =  $cart_model->getAll($sql);
                //转化图片url,给它加上同步服务器
                foreach($cart_items as $k => $item) {
                	$cart_items[$k]['goods_image'] = IMAGE_URL.$item['goods_image'];
                	$cart_items[$k]['default_image'] = IMAGE_URL.$item['default_image'];
                	$cart_items[$k]['yimage_url'] = IMAGE_URL.$item['yimage_url'];
                	$cart_items[$k]['mimage_url'] = IMAGE_URL.$item['mimage_url'];
                	$cart_items[$k]['smimage_url'] = IMAGE_URL.$item['smimage_url'];
                	$cart_items[$k]['dimage_url'] = IMAGE_URL.$item['dimage_url'];
                	$cart_items[$k]['simage_url'] = IMAGE_URL.$item['simage_url'];
                	
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
                    $return['credit_total'] += $goods['quantity'] * $goods['credit']; //赠送总积分
                    $cart_items[$rec_id]['subtotal']    =   $goods['quantity'] * $goods['price'];   //小计
                    empty($goods['goods_image']) && $cart_items[$rec_id]['goods_image'] = Conf::get('default_goods_image');
                }

                $return['items']        =   $cart_items;
                $return['store_id']     =   $store_id;
                $return['store_name']   =   $store_info['store_name'];
                $return['type']         =   'material';
                $return['otype']        =   'offline';
            break;
        }

        return $return;
    }
	/**
     *    获取外部传递过来的派拉商品
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_paila_goods_info()
    {
        $return = array(
            'items'     =>  array(),    //商品列表
            'quantity'  =>  0,          //商品总量
            'amount'    =>  0,          //商品总价
        	'credit_total' => 0,		//商品总的赠送积分
            'store_id'  =>  0,          //所属店铺
            'store_name'=>  '',         //店铺名称
            'type'      =>  null,       //商品类型
            'otype'     =>  'offline',   //订单类型
            'allow_coupon'  => false,    //是否允许使用优惠券
        );
        switch ($_GET['goods'])
        {
            default:
                /* 从购物车中取商品 */
                $_GET['store_id'] = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
                $store_id = $_GET['store_id'];
                if (!$store_id)
                {
                    return false;
                }
                $cart_model =& m('quickcart');
				$sql = "SELECT * FROM pa_quick_cart c left join pa_goods_spec gs on c.spec_id=gs.spec_id left join pa_goods g on gs.goods_id = g.goods_id 
				where  c.user_id = " . $_GET['uid'] . " AND c.store_id = {$store_id} AND c.session_id='" . SESS_ID . "'";
				
				$cart_items      =  $cart_model->getAll($sql);
                //转化图片url,给它加上同步服务器
                foreach($cart_items as $k => $item) {
                	$cart_items[$k]['goods_image'] = IMAGE_URL.$item['goods_image'];
                	$cart_items[$k]['default_image'] = IMAGE_URL.$item['default_image'];
                	$cart_items[$k]['yimage_url'] = IMAGE_URL.$item['yimage_url'];
                	$cart_items[$k]['mimage_url'] = IMAGE_URL.$item['mimage_url'];
                	$cart_items[$k]['smimage_url'] = IMAGE_URL.$item['smimage_url'];
                	$cart_items[$k]['dimage_url'] = IMAGE_URL.$item['dimage_url'];
                	$cart_items[$k]['simage_url'] = IMAGE_URL.$item['simage_url'];
                	
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
                    $return['credit_total'] += $goods['quantity'] * $goods['credit']; //赠送总积分
                    $cart_items[$rec_id]['subtotal']    =   $goods['quantity'] * $goods['price'];   //小计
                    empty($goods['goods_image']) && $cart_items[$rec_id]['goods_image'] = Conf::get('default_goods_image');
                }

                $return['items']        =   $cart_items;
                $return['store_id']     =   $store_id;
                $return['store_name']   =   $store_info['store_name'];
                $return['type']         =   'material';
                $return['otype']        =   'offline';
            break;
        }

        return $return;
    }
    public function test() {
    	$this->display("quick_cashier.index.html ");	
    }
	public function testuser()
	{
		$mobile = $_GET['mobile'];
		if(empty($mobile))
		{
			echo ecm_json_encode(false);
			return;
		}
		$member_model = & m("member");
		$member_info = $member_model->getAll("select * from pa_member where mobile='".$mobile."'");
		if ($member_info){
			echo ecm_json_encode(true);
			return;
		}else{
			echo ecm_json_encode(false);
			return;
		}
	}
	public function _get_member_info()
	{
		$userin = $_GET['mobile'];
		$member_model = & m("member");
		$member_info = $member_model->getRow("select * from pa_member where mobile='".$userin."'");
		if (empty($member_info))
		{
			$member_info = array(
				user_id => 0,
				user_name => '您未注册',
				mobile => $_GET['mobile'],
				money => '0',
				credit => '0',
				);
		}
		return $member_info;
	}
  	function paypassword(){
	    	$info = & ms ();
  			$user_id = empty($_GET['cat']) ? 0 : intval($_GET['cat']);
	    	$traderPassword = $_GET['traderPassword'];
	    	$inf = $info->user->traderAuth($user_id, $traderPassword);	
	    	if (!$inf)
	    	{
	    		$this->json_error('验证失败！');
	    	} else {
	    		$this->json_result();	
	    	}	    	
	    }
}