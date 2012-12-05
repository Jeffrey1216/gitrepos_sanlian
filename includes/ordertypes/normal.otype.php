<?php

/**
 *    普通订单类型
 *
 *    @author    Garbin
 *    @usage    none
 */
class NormalOrder extends BaseOrder
{
    var $_name = 'normal';

    /**
     *    查看订单
     *
     *    @author    Garbin
     *    @param     int $order_id
     *    @param     array $order_info
     *    @return    array
     */
    function get_order_detail($order_id, $order_info)
    {
        if (!$order_id)
        {
            return array();
        }

        /* 获取商品列表 */
        $data['goods_list'] =   $this->_get_goods_list($order_id);

        /* 配关信息 */
        $data['order_extm'] =   $this->_get_order_extm($order_id);

        /* 支付方式信息 */
        if ($order_info['payment_id'])
        {
            $payment_model      =& m('payment');
            $payment_info       =  $payment_model->get("payment_id={$order_info['payment_id']}");
            $data['payment_info']   =   $payment_info;
        }

        /* 订单操作日志 */
        $data['order_logs'] =   $this->_get_order_logs($order_id);

        return array('data' => $data);
    }

    /* 显示订单表单 */
    /** 
     * 	本方法修改
     * 	@alter  bottle
     * 	添加判断生成定单模板
     * 	如果$store_id为2的话.  本店铺为派拉店铺 $template = 'paila_order.form.html';
     **/
    function get_order_form($store_id)
    {
        $data = array();
        //判断模板.
        if(trim($_GET['goods']) == 'quickCart') {
        	$template = intval($store_id) == PAILAMALL ? 'paila_quick_order.form.html' : 'quick_order.form.html';
        } else {
        	$template = intval($store_id) == PAILAMALL ? 'paila_order.form.html' : 'order.form.html';
        }
        
        $visitor =& env('visitor');

        /* 获取我的收货地址 */
        $data['my_address']         = $this->_get_my_address($visitor->get('user_id'));
        $data['addresses']          =   ecm_json_encode($data['my_address']);
        $data['regions']            = $this->_get_regions();

        /* 配送方式 */
        $data['shipping_methods']   = $this->_get_shipping_methods($store_id);
        $data['store_info']         = $this->_get_storeinfo($store_id);
        if (empty($data['shipping_methods']))
        {
            $this->_error('no_shipping_methods');

            return false;
        }
        $data['shippings']  = ecm_json_encode($data['shipping_methods']);
        foreach ($data['shipping_methods'] as $shipping)
        {
            $data['shipping_options'][$shipping['shipping_id']] = $shipping['shipping_name'];
        }

        return array('data' => $data, 'template' => $template);
    }
    
	/* 显示商铺订单表单 */
    /** 
     * 	商铺提交给供应商的订单	
     * 	本方法修改
     * 	@alter  bottle
     * 	添加判断生成定单模板
     **/
    function get_store_order_form()
    {
        $data = array();
        
        //判断模板.
        $template = 'store_order.form.html';
        

        $visitor =& env('visitor');

        /* 获取我的收货地址 */
        $data['my_address']         = $this->_get_my_address($visitor->get('user_id'));
        $data['addresses']          =   ecm_json_encode($data['my_address']);
        $data['regions']            = $this->_get_regions();

        return array('data' => $data, 'template' => $template);
    }

    /**
     *    提交生成订单，外部告诉我要下的单的商品类型及用户填写的表单数据以及商品数据，我生成好订单后返回订单ID
     *
     *    @author    Garbin
     *    @param     array $data
     *    @return    int
     */
    function submit_order($data)
    {
    	$store_id = 0; //商户ID
        /* 释放goods_info和post两个变量 */
        extract($data);
        /* 处理订单基本信息 */
        $base_info = $this->_handle_order_info($goods_info, $post);
        //获取$store_id;
        $store_id = $base_info['seller_id'];    
       
        if (!$base_info)
        {
            /* 基本信息验证不通过 */

            return 0;
        }
       	/* 处理订单收货人信息 */
        $consignee_info = $this->_handle_consignee_info($goods_info, $post);

	    if (!$consignee_info)
	    {
	     	/* 收货人信息验证不通过 */
	        return 0;
	    }
		$use_credit = empty($post['use_credit']) ? 0.00 : floatval($post['use_credit']);
		      
        /* 至此说明订单的信息都是可靠的，可以开始入库了 */
        /* 插入订单基本信息 */
        //订单总实际总金额，可能还会在此减去折扣等费用
        /* //原有的关闭
        $base_info['order_amount']  =   $base_info['goods_amount'] + $consignee_info['shipping_fee'] - $base_info['discount'];
        */
		//新的支付方式
		$base_info['order_amount']  =   $base_info['goods_amount'] + $goods_info['shipmoney'];

		$order_money = floatval(number_format(floatval($base_info['order_amount']), 2));
		$new_money = floatval(number_format(floatval($_POST['money']), 2));
		
   		if ($order_money < $new_money)
        {
			// 非法途径不通过 
        	$this->_error('支付金额有误！');
	        return 0;
        }
        //查询买家所有积分
        $user_id = $_SESSION['user_info']['user_id'];
        $member_model = & m('member');
        $member_info = $member_model->get("user_id='{$user_id}'");
        $member_credit = $member_info['credit'] - $member_info['frozen_credit']; //用户可用积分余额

        if ($use_credit > $member_credit)
		{
			$this->_error('您的可用积分不足，请返回订单重新选择！');
        	return 0;
		}
        //当前定单返还的积分数.  只是在定单表中的一个表示 .  表单未成功此积分无效
        
 		$base_info['buyer_mobile'] = $member_info['mobile']; //购买人的手机号
        //添加定单状态
        $order_model =& m('order');
        $order_id    = $order_model->add($base_info);
        if (!$order_id)
        {
            /* 插入基本信息失败 */
            $this->_error('create_order_failed');

            return 0;
        }

        /* 插入收货人信息 */
        $consignee_info['order_id'] = $order_id;
        $consignee_info['shipping_fee'] = $goods_info['shipmoney'];
        $order_extm_model =& m('orderextm');
        $order_extm_model->add($consignee_info);
        /* 插入商品信息 */
        $goods_items = array();
        foreach ($goods_info['items'] as $key => $value)
        {
        	if(is_array($post['cartinfo']))
        	{
        		if(in_array($value['gs_id'],$post['cartinfo']))
        		{
        		    $is_usecredit = 1;
        		}else
        		{
        		    $is_usecredit = 0;
        		}
        	}else
        	{
        		$is_usecredit = 0;
        	}
            $goods_items[] = array(
                'order_id'      =>  $order_id,
                'goods_id'      =>  $value['goods_id'],
                'goods_name'    =>  $value['goods_name'],
                'spec_id'       =>  $value['spec_id'],
                'specification' =>  $value['specification'],
                'price'         =>  $value['price'],
                'quantity'      =>  $value['quantity'],
                'goods_image'   =>  $this->mkOrderGoodsImage($value['simage_url']),
            	'gprice'        =>  $value['gprice'],
            	'zprice'        =>  $value['zprice'],
            	'gs_id'         =>  $value['gs_id'],
            	'commodity_code' => $value['commodity_code'],
                'credit'        =>  $value['newcredit'],
            	'is_usecredit'  =>  $is_usecredit,
            	'pr_id'			=>  $value['pr_id'],
            	'autotrophy'	=>	$value['autotrophy'],
            );
            $is_usecredit = 0;
        }
        $order_goods_model =& m('ordergoods');
        $abc =$order_goods_model->add(addslashes_deep($goods_items)); //防止二次注入
        return $order_id;
    }
	/**
     *    提交生成商户订单，外部告诉我要下的单的商品类型及用户填写的表单数据以及商品数据，我生成好订单后返回订单ID
     *
     *    @author    Garbin
     *    @param     array $data
     *    @return    int
     */
    function submit_store_order($data)
    {
    	$store_id = 0; //商户ID
        /* 释放goods_info和post两个变量 */
        extract($data);
        /* 处理订单基本信息 */
        $base_info = $this->_handle_store_order_info($goods_info, $post);
        
        if (!$base_info)
        {
            /* 基本信息验证不通过 */

            return 0;
        }

        /* 处理订单收货人信息 */
        $consignee_info = $this->_handle_store_consignee_info($goods_info, $post);
        
        if (!$consignee_info)
        {
            /* 收货人信息验证不通过 */
            return 0;
        }

        /* 至此说明订单的信息都是可靠的，可以开始入库了 */
        /* 插入订单基本信息 */
        //订单总实际总金额
        $base_info['order_amount']  =  $base_info['goods_amount'];
        $base_info['pay_message']   =  $post['pay_message'];
        //添加定单状态
        $order_model =& m('storeorder');
        $order_id = $order_model->add($base_info);
		
        
        if (!$order_id)
        {
            /* 插入基本信息失败 */
            $this->_error('create_order_failed');

            return 0;
        }
        /* 插入收货人信息 */
        $consignee_info['order_id'] = $order_id;
        $consignee_info['shipping_fee'] = 0;
        $order_extm_model =& m('storeorderextm');
        $order_extm_model->add($consignee_info);
		
        
        /* 插入商品信息 */
        $goods_items = array();
        
        foreach ($goods_info['items'] as $key => $value)
        {
        	
            $goods_items[] = array(
                'order_id'      =>  $order_id,
                'goods_id'      =>  $value['goods_id'],
                'goods_name'    =>  $value['goods_name'],
                'spec_id'       =>  $value['spec_id'],
                'specification' =>  $value['specification'],
                'price'         =>  $value['price'],//店铺进货价
                'quantity'      =>  $value['quantity'],
                'goods_image'   =>  $this->mkOrderGoodsImage($value['goods_image']),
            	'gprice'        =>  $value['gprice'],//厂家供应价
            	'sprice'        =>  $value['sprice'], //售价
            	'credit'		=>	$value['credit'],
            );
        }
        $order_goods_model =& m('storeordergoods');
        $order_goods_model->add(addslashes_deep($goods_items)); //防止二次注入

        return $order_id;
    }
    /**
     * 	@Function 目录操作, 创建定单图片目录
     * 	@Return string $path 图片完整路径	
     * 	@Param string $sourceImg 源图片 
     */
    
    
	private function mkOrderGoodsImage($sourceImg) {
		 $error_file_path = $sourceImg;
		 $year = date("Y");
		 $month = date("m");
		 $day = date("d");
		 $path = ROOT_PATH.'/data/files/mall/orderImg/'.$year.'/'.$month.'/'.$day;
		 createFolder($path);
		 $source = substr($sourceImg,strlen(IMAGE_URL)-1);
		 $fileName = substr($sourceImg,strrpos($sourceImg,'/'));
		 $s =  ROOT_PATH.$source;
		 $f = $path.$fileName;
		 if(file_exists($s) && is_file($f)) {
		 	if(!copy($s,$f)) {
		 		return $error_file_path;
		 	}
		 } else {
		 	return $error_file_path;
		 }
		 return '/data/files/mall/orderImg/'.$year.'/'.$month.'/'.$day.'/'.$fileName;
	}
	/**
     *    查看订单
     *
     *    @author    Garbin
     *    @param     int $order_id
     *    @param     array $order_info
     *    @return    array
     */
    function get_store_order_detail($order_id, $order_info)
    {
        if (!$order_id)
        {
            return array();
        }

        /* 获取商品列表 */
        $data['goods_list'] =   $this->_get_store_goods_list($order_id);

        /* 配关信息 */
        $data['order_extm'] =   $this->_get_store_order_extm($order_id);

        /* 支付方式信息 */
        if ($order_info['payment_id'])
        {
            $payment_model      =& m('payment');
            $payment_info       =  $payment_model->get("payment_id={$order_info['payment_id']}");
            $data['payment_info']   =   $payment_info;
        }

        /* 订单操作日志 */
        $data['order_logs'] =   $this->_get_store_order_logs($order_id);
        
        $data['store_info'] = $this->_get_storeinfo($order_info['buyer_id']);

        return array('data' => $data);
    }
  
}

?>