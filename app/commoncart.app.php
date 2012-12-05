<?php

/**
 *    购物车控制器，负责会员购物车的管理工作，她与下一步售货员的接口是：购物车告诉售货员，我要买的商品是我购物车内的商品
 *
 *    @author    Garbin
 */

class CommoncartApp extends StoreadminbaseApp
{
    /**
     *    列出购物车中的商品
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        $carts = $this->_get_carts();

        if (empty($carts))
        {
            $this->_cart_empty();

            return;
        }
        $this->assign('image_url',IMAGE_URL);
        $this->assign('carts', $carts);
 
        $this->display('storeadmin.buymallgoods.shopcart.html');
    }

    //加入购物车方法 ()
    public function addToCart() {
    	$spec_id   = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0; //属性
        $quantity   = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0; //数量
        if (!$spec_id || !$quantity)
        {
            return;
        }

        /* 是否有商品 */
        $spec_model =& m('goodsspec');
        $spec_info  =  $spec_model->get(array(
            'fields'        => 'g.zprice,g.credit, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.simage_url,gs.spec_id ,gs.spec_1, gs.spec_2, gs.price ,gs.zprice,gs.gprice',
            'conditions'    => $spec_id,
            'join'          => 'belongs_to_goods',
        ));

        if (!$spec_info)
        {
            $this->json_error('no_such_goods');
            /* 商品不存在 */
            return;
        }
		
        if($spec_info['price'] <= 0 || $spec_info['zprice'] <= 0) {
        	$this->json_error('price_error');
        	//$this->json_result($spec_info['sprice']);
        	return;
        }
        
        /* 如果是自己店铺的商品，则不能购买 */
        if ($this->visitor->get('manage_store'))
        {
            if ($spec_info['store_id'] == $this->visitor->get('manage_store'))
            {
                $this->json_error('can_not_buy_yourself');

                return;
            }
        }

        /* 是否添加过 */
        $model_cart =& m('commoncart');
        $userid = $this->visitor->get('user_id');
        $item_info  = $model_cart->get(" buyer_id={$userid} AND goods_id={$spec_info['goods_id']} AND  spec_id={$spec_id} ");
        if (!empty($item_info))
        {
            $this->json_error('goods_already_in_cart');

            return;
        }


        $spec_1 = $spec_info['spec_name_1'] ? $spec_info['spec_name_1'] . ':' . $spec_info['spec_1'] : $spec_info['spec_1'];
        $spec_2 = $spec_info['spec_name_2'] ? $spec_info['spec_name_2'] . ':' . $spec_info['spec_2'] : $spec_info['spec_2'];

        $specification = $spec_1 . ' ' . $spec_2;

        /* 将商品加入购物车 */
        $cart_item = array(
            'buyer_id'      => $userid,
            'spec_id'       => $spec_id,
            'goods_id'      => $spec_info['goods_id'],
            'goods_name'    => addslashes($spec_info['goods_name']),
            'specification' => addslashes(trim($specification)),
            'price'         => $spec_info['zprice'],
            'quantity'      => $quantity,
            'goods_image'   => addslashes($spec_info['simage_url']),
        );

        /* 添加并返回购物车统计即可 */
        $cart_model =&  m('commoncart');
        $cart_model->add($cart_item);
        $this->json_error('加入购物车成功');
        return;
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
        $model_cart =& m('commoncart');
        $droped_rows = $model_cart->drop('cart_id=' . $rec_id );
        if (!$droped_rows)
        {
            return;
        }

        /* 返回结果 */
        $cart_status = $this->_get_cart_status();
        $this->json_result(array(
            'cart'      =>  $cart_status['status'],                     //返回总的购物车状态                
            'amount'    =>  $cart_status['status']['amount'],  //店铺购物车总计
        ), 'drop_item_successed');
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
        $card_id  = isset($_GET['card_id']) ? intval($_GET['card_id']) : 0;
        $quantity = isset($_GET['quantity'])? intval($_GET['quantity']): 0;
        if (!$card_id || !$quantity)
        {
            /* 不合法的请求 */
            return;
        }

        /* 修改数量 */
        $model_cart =& m('commoncart');

        /* 获取购物车中的信息，用于获取价格并计算小计 */
        $cart_info = $model_cart->get($card_id);
        if (empty($cart_info))
        {
            /* 并没有添加该商品到购物车 */
            return;
        }

        /* 修改数量 */
        $model_cart->edit($card_id, array(
            'quantity'  =>  $quantity,
        ));
		
        /* 小计 */
        $subtotal   =   $quantity * floatval($cart_info['price']);
        
        /* 返回JSON结果 */
        $cart_status = $this->_get_cart_status();
        
        $this->json_result(array(
            'cart'      =>  $cart_status['status'],                     //返回总的购物车状态
            'subtotal'  =>  $subtotal ,									//小计                          
            'amount'    =>  $cart_status['status']['amount'],  //店铺购物车总计
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
                'quantity'  =>  0,      //总数量
                'amount'    =>  0,      //总金额
            ),
            'carts'     =>  array(),    //购物车列表，包含每个购物车的状态
        );

        /* 获取所有购物车 */
        $carts = $this->_get_carts();

        if (empty($carts))
        {
            return $data;
        }
        
        $data['status']['quantity'] = $carts['quantity'];
        $data['status']['amount']   = $carts['amount'];

        return $data;
    }

    /**
     *    购物车为空
     *
     *    @author    Garbin
     *    @return    void
     */
    function _cart_empty()
    {
        $this->display('commoncart.empty.html');
    }

    /**
     *    以购物车为单位获取购物车列表及商品项
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_carts()
    {
        $carts = array();
        /*
      		获取所有购物车中的内容 
        */
        $where_user_id = $this->visitor->get('user_id') ? "buyer_id=" . $this->visitor->get('user_id') : '';
        $cart_model =& m('commoncart');
        $cart_items = $cart_model->find(array(
            'conditions'    => $where_user_id,
        ));
        if (empty($cart_items))
        {
            return $carts;
        }
        foreach ($cart_items as $item)
        {
            /* 小计 */
            $item['subtotal']   = $item['price'] * $item['quantity'];
			empty($item['goods_image']) && $item['goods_image'] = IMAGE_URL.Conf::get('default_goods_image');
            $carts['amount']     += $item['subtotal'];   //各店铺的总金额
            $carts['quantity']   += $item['quantity'];   //各店铺的总数量
            $carts['goods'][]    = $item;
        }
        return $carts;
    }
}

?>
