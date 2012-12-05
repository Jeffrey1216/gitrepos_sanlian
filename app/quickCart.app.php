<?php

/**
 *    购物车控制器，负责会员购物车的管理工作，她与下一步售货员的接口是：购物车告诉售货员，我要买的商品是我购物车内的商品
 *
 *    @author    Garbin
 */

class QuickCartApp extends MallbaseApp
{
    /**
     *    列出购物车中的商品
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        $store_id = $this->visitor->get('store_id');
        $carts = $this->_get_carts($store_id);       
        $this->_curlocal(
            LANG::get('cart')
        );
        $this->_config_seo('title', Lang::get('confirm_goods') . ' - ' . Conf::get('site_title'));

        if (empty($carts))
        {
            $this->show_storeadmin_message('购物车为空！','go_back','index.php?app=kjzf&act=quick_brand_index&mobile='.$_GET['mobile']);

            return;
        }
        $carts =array($carts);    
        $this->assign('carts', $carts);
        $this->assign('mobile',$_GET['mobile']); 
        $this->assign('uid',$_GET['uid']);  
        $this->assign('storeid',$store_id);    
        $this->display('quickcart.index.html');
    }

    /**
     *    放入商品(根据不同的请求方式给出不同的返回结果)
     *
     *    @author    Garbin
     *    @return    void
     */
    function addToCart()
    {
        $spec_id   = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
        $quantity   = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0;
        $mobile = $_GET['mobile'];
        $store_id = $this->visitor->get('store_id');
        $uid = $_GET['uid'];
        if (!$spec_id || !$quantity)
        {
            return;
        }

        /* 是否有商品 */
        $spec_model =& m('goodsspec');
        //原方法
        /*
        $spec_info  =  $spec_model->get(array(
            'fields'        => 'g.store_id,g.credit,g.area_type, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.default_image,gs.spec_id ,gs.spec_1, gs.spec_2, pg.stock, gs.price',
            'conditions'    => $spec_id,
            'join'          => 'belongs_to_goods',
        ));*/
        $spec_in  = $spec_model->getAll("SELECT g.store_id,g.credit,g.area_type, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.default_image,gs.spec_id ,gs.spec_1, gs.spec_2, pg.stock, gs.price,gs.spec_id FROM pa_goods_spec gs LEFT JOIN pa_goods g  ON gs.goods_id = g.goods_id left join pa_paila_goods pg on pg.goods_id=g.goods_id WHERE gs.spec_id =".$spec_id . " and pg.store_id = " . $this->visitor->get('store_id'));
        $spec_info =$spec_in[0];			
        if (!$spec_info) //品牌商城
        {
            $this->json_error('no_such_goods');
            /* 商品不存在 */
            return;
        }
        //派拉商城的库存不在goods_spec表里面
        $paila_goods_mod = & m('pailagoods');
        /*if($spec_info['area_type'] == 'pailamall') {
        	$paila_spec_info = $paila_goods_mod->get(array(
        		'conditions'	=> " spec_id = " . intval($spec_id) . " AND store_id = " . intval($this->visitor->info['store_id']) . "",
        	));
        	if(!$paila_spec_info) {
        		$this->json_error('no_such_goods');
	            // 商品不存在 
	            return;
        	}
        }*/

        /* 是否添加过 */
        $model_cart =& m('quickcart');
        $item_info  = $model_cart->get("spec_id={$spec_id} AND session_id='" . SESS_ID . "' AND user_mobile=".$mobile." AND store_id=".$store_id);
        $sql="spec_id={$spec_id} AND session_id='" . SESS_ID . "'";
        if (!empty($item_info))
        {
            $this->json_error('goods_already_in_cart');

            return;
        }

        if ($quantity > $spec_info['stock'])
        {
            $this->json_error('no_enough_goods');
            return;
        }

        $spec_1 = $spec_info['spec_name_1'] ? $spec_info['spec_name_1'] . ':' . $spec_info['spec_1'] : $spec_info['spec_1'];
        $spec_2 = $spec_info['spec_name_2'] ? $spec_info['spec_name_2'] . ':' . $spec_info['spec_2'] : $spec_info['spec_2'];

        $specification = $spec_1 . ' ' . $spec_2;
		
        $real_store_id = $spec_info['area_type'] == 'pailamall' ? $paila_spec_info['store_id'] : $spec_info['store_id'] ;
        /* 将商品加入购物车 */
        $cart_item = array(
            'user_id'       => $uid,
            'session_id'    => SESS_ID,
            'store_id'      => $store_id,
            'spec_id'       => $spec_id,
            'goods_id'      => $spec_info['goods_id'],
            'goods_name'    => addslashes($spec_info['goods_name']),
            'specification' => addslashes(trim($specification)),
            'price'         => $spec_info['price'],
        	'credit'		=> $spec_info['credit'],
            'quantity'      => $quantity,
            'goods_image'   => addslashes($spec_info['default_image']),
        	'user_mobile'   => $mobile,
        );

        /* 添加并返回购物车统计即可 */
        $cart_model =&  m('quickcart');
        $cart_model->add($cart_item);
        $cart_status = $this->_get_cart_status();

        /* 更新被添加进购物车的次数 */
        $model_goodsstatistics =& m('goodsstatistics');
        $model_goodsstatistics->edit($spec_info['goods_id'], 'carts=carts+1');

        $this->json_result(array(
            'cart'      =>  $cart_status['status'],  //返回购物车状态
        ), 'addto_cart_successed');
    }

    /**
     *    丢弃商品
     *
     *    @author    Garbin
     *    @return    void
     */
    function drop()
    {
        /* 传入rec_id，删除并返回购物车统计即可 */
        $rec_id = isset($_GET['rec_id']) ? intval($_GET['rec_id']) : 0;
        if (!$rec_id)
        {
            return;
        }	
        /* 从购物车中删除 */
        $model_cart =& m('quickcart');
        $droped_rows = $model_cart->drop('rec_id=' . $rec_id . ' AND session_id=\'' . SESS_ID . '\'', 'store_id');
        if (!$droped_rows)
        {
            return;
        }

        /* 返回结果 */
        $dropped_data = $model_cart->getDroppedData();
        $store_id     = $dropped_data[$rec_id]['store_id'];
        $cart_status = $this->_get_cart_status();
        $this->json_result(array(
            'cart'  =>  $cart_status['status'],                      //返回总的购物车状态
            'amount'=>  $cart_status['carts'][$store_id]['amount']   //返回指定店铺的购物车状态
        ),'drop_item_successed');
    }

    /**
     *    更新购物车中商品的数量，以商品为单位，AJAX更新
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function update()
    {
        $spec_id  = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
        $quantity = isset($_GET['quantity'])? intval($_GET['quantity']): 0;
        if (!$spec_id || !$quantity)
        {
            /* 不合法的请求 */
            return;
        }

        /* 判断库存是否足够 */
        $model_spec =& m('goodsspec');
        $spec_info  =  $model_spec->get($spec_id);
        if (empty($spec_info))
        {
            /* 没有该规格 */
            $this->json_error('no_such_spec');
            return;
        }

        if ($quantity > $spec_info['stock'])
        {
            /* 数量有限 */
            $this->json_error('no_enough_goods');
            return;
        }

        /* 修改数量 */
        $where = "spec_id={$spec_id} AND session_id='" . SESS_ID . "'";
        $model_cart =& m('quickcart');

        /* 获取购物车中的信息，用于获取价格并计算小计 */
        $cart_spec_info = $model_cart->get($where);
        if (empty($cart_spec_info))
        {
            /* 并没有添加该商品到购物车 */
            return;
        }

        $store_id = $cart_spec_info['store_id'];

        /* 修改数量 */
        $model_cart->edit($where, array(
            'quantity'  =>  $quantity,
        ));

        /* 小计 */
        $subtotal   =   $quantity * $cart_spec_info['price'];
        /* 返还积分小计 */
        $credit = $quantity * $cart_spec_info['credit'];
        /* 返回JSON结果 */
        $cart_status = $this->_get_cart_status();
        //var_dump($cart_status['carts'][$store_id]['credit_total']);
        $this->json_result(array(
            'cart'      =>  $cart_status['status'],                     //返回总的购物车状态
            'subtotal'  =>  $subtotal,									//小计
        	'credit'	=> 	$credit,									//返还积分小计                                  
            'amount'    =>  $cart_status['carts']['amount'],  //店铺购物车总计
        	'credit_total' => $cart_status['carts']['credit_total']
        ), 'update_item_successed');
    }

    /**
     *    获取购物车状态
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_cart_status()
    {
        /* 默认的返回格式 */
        $data = array(
            'status'    =>  array(
        		'credit_total' => 0,	//总积分
                'quantity'  =>  0,      //总数量
                'amount'    =>  0,      //总金额
                'kinds'     =>  0,      //总种类
            ),
            'carts'     =>  array(),    //购物车列表，包含每个购物车的状态
        );

        /* 获取所有购物车 */
        $carts = $this->_get_carts();

        if (empty($carts))
        {
            return $data;
        }
        $data['carts']  =   $carts;
        foreach ($carts as $store_id => $cart)
        {
        	$data['status']['credit_total'] += $cart['credit_total'];
            $data['status']['quantity'] += $cart['quantity'];
            $data['status']['amount']   += $cart['amount'];
            $data['status']['kinds']    += $cart['kinds'];
        }

        return $data;
    }

    

    /**
     *    以购物车为单位获取购物车列表及商品项
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_carts($store_id = 0)
    {
        $carts = array();
		
        /* 获取所有购物车中的内容 */
        $where_store_id = $store_id ? ' AND quick_cart.store_id=' . $store_id : '';	
        /* 只有是自己购物车的项目才能购买 */
        if ($_GET['uid'] != 0)
        {
	        $where_user_id = $_GET['uid'] ? " AND quick_cart.user_id=" . $_GET['uid'] : '';
	        $cart_model =& m('quickcart');
	        $cart_items = $cart_model->find(array(
	            'conditions'    => 'session_id = \'' . SESS_ID . "'" . $where_store_id . $where_user_id,
	            'fields'        => 'this.*,store.store_name,store.store_id',
	            'join'          => 'belongs_to_store',
	        ));
        }else {
        	$where_user_mobile = $_GET['mobile'] ? " AND quick_cart.user_mobile=" . $_GET['mobile'] : '';
        	$cart_model =& m('quickcart');
	        $cart_items = $cart_model->find(array(
	            'conditions'    => 'session_id = \'' . SESS_ID . "'" . $where_store_id . $where_user_mobile,
	            'fields'        => 'this.*,store.store_name,store.store_id',
	            'join'          => 'belongs_to_store',
	        ));
        	
        }
        if (empty($cart_items))
        {
            return $carts;
        }
        $kinds = array();
        foreach ($cart_items as $item)
        {
            /* 小计 */
            $item['subtotal']   = $item['price'] * $item['quantity'];
            /* 积分小计 */
            $item['credit_total'] = $item['credit'] * $item['quantity'];
            /* 以店铺ID为索引 
            empty($item['goods_image']) && $item['goods_image'] = Conf::get('default_goods_image');
            $carts[$item['store_id']]['store_name'] = $item['store_name'];
            $carts[$item['store_id']]['amount']     += $item['subtotal'];   //各店铺的总金额
            $carts[$item['store_id']]['credit_total'] += $item['credit_total'];
            $carts[$item['store_id']]['quantity']   += $item['quantity'];   //各店铺的总数量
            $carts[$item['store_id']]['goods'][]    = $item;*/
            /* 实体店购物车汇总 */
            $carts['store_name'] = $item['store_name'];
            $carts['amount']     += $item['subtotal'];   //各店铺的总金额
            $carts['credit_total'] += $item['credit_total'];
            $carts['quantity']   += $item['quantity'];   //各店铺的总数量
            $carts['store_id']  = $item['store_id'];    //实体店ID
            $carts['goods'][]    = $item;            
        }
        return $carts;
    }
}

?>
