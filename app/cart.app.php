<?php

/**
 *    ���ﳵ�������������Ա���ﳵ�Ĺ�������������һ���ۻ�Ա�Ľӿ��ǣ����ﳵ�����ۻ�Ա����Ҫ�����Ʒ���ҹ��ﳵ�ڵ���Ʒ
 *
 *    @author    Garbin
 */

class CartApp extends MallbaseApp
{
    /**
     *    �г����ﳵ�е���Ʒ
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        $store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
        $carts = $this->_get_carts($store_id);

        $this->_curlocal(
            LANG::get('cart')
        );
        $this->_config_seo('title', Lang::get('confirm_goods') . ' - ' . Conf::get('site_title'));
        if (empty($carts))
        {
            $this->_cart_empty();
            return;
        }
        $this->assign('carts', $carts);
        $this->display('cart.index.html');
    }

    /**
     *    ������Ʒ(���ݲ�ͬ������ʽ������ͬ�ķ��ؽ��)
     *
     *    @author    Garbin
     *    @return    void
     */
    function add()
    {
    	
        $spec_id   = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
        $quantity   = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0;
       	$store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : STORE_ID;
       	$stock = 0;
       	
        
       	if ($store_id != 0)
       	{
       		$storegoods_mod = & m('storegoods');
       		$storegoods_info = $storegoods_mod->getRow("select sg.*,g.autotrophy from pa_store_goods sg left join pa_goods g on g.goods_id=sg.goods_id where spec_id = " . $spec_id . " and store_id = " . $store_id);
       		$stock = $storegoods_info['stock'];
       	}
		
        if (!$spec_id || !$quantity)
        {
            return;
        }

        /* �Ƿ�����Ʒ */
        $spec_model =& m('goodsspec');
        /*
        $spec_info  =  $spec_model->get(array(
            'fields'        => 'g.store_id,gs.credit, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.default_image,gs.spec_id ,gs.spec_1, gs.spec_2, gs.stock, gs.price',
            'conditions'    => $spec_id,
            'join'          => 'belongs_to_goods,belongs_to_store,g.goods,on g.goods_id = store_goods.goods_id',
        ));*/
        $conditions = " where 1=1";
		if($spec_id)
		{
			$conditions .= " and gs.spec_id=".$spec_id;
		}
		if($store_id)
		{
			$conditions .= " and gs.store_id=".$store_id;
		}
        $sql = 'select g.*,gc.*,gs.store_id,gs.stock from pa_store_goods gs left join pa_goods_spec gc on gc.spec_id = gs.spec_id left join pa_goods g on gc.goods_id = g.goods_id '.$conditions;
		$spec_info = $spec_model->getRow($sql);
        if (!$spec_info)
        {
            $this->json_error('no_such_goods');
            /* ��Ʒ������ */
            return;
        }
        
        if ($store_id == 0)
        {
            $this->json_error('no_such_goods');
            /* ��Ʒ������ */
            return;
        }

        /* ������Լ����̵���Ʒ�����ܹ��� */
        if ($this->visitor->get('manage_store'))
        {
            if ($store_id == $this->visitor->get('manage_store'))
            {
                $this->json_error('can_not_buy_yourself');

                return;
            }
        }

        /* �Ƿ���ӹ� */
        $model_cart =& m('cart');
        $item_info  = $model_cart->get("spec_id={$spec_id} AND session_id='" . SESS_ID . "'");
        if (!empty($item_info))
        {
            $this->json_error('goods_already_in_cart');

            return;
        }

        if ($quantity > $stock)
        {
            $this->json_error('no_enough_goods');
            return;
        }

        $spec_1 = $spec_info['spec_name_1'] ? $spec_info['spec_name_1'] . ':' . $spec_info['spec_1'] : $spec_info['spec_1'];
        $spec_2 = $spec_info['spec_name_2'] ? $spec_info['spec_name_2'] . ':' . $spec_info['spec_2'] : $spec_info['spec_2'];

        $specification = $spec_1 . ' ' . $spec_2;
        
        //������Ʒ���������� 
        $shipmoney = 0;
        if ($store_id==intval(STORE_ID))
        {
        	$shipmoney = $quantity >$spec_info['logistics_num'] ? abs(floatval(($quantity - $spec_info['logistics_num']) * 5 + 10)) : 10; //���
        }
        

        /* ����Ʒ���빺�ﳵ */
        $cart_item = array(
            'user_id'       => $this->visitor->get('user_id'),
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
        	'gs_id'		=> $storegoods_info['gs_id'],
            'commodity_code' => $spec_info['commodity_code'],
        	'autotrophy'     => $storegoods_info['autotrophy'],
        	'shipmoney' => $shipmoney,
        );
        /* ��Ӳ����ع��ﳵͳ�Ƽ��� */
        $cart_model =&  m('cart');
        $cart_model->add($cart_item);
        $cart_status = $this->_get_cart_status();

        /* ���±���ӽ����ﳵ�Ĵ��� */
        $model_goodsstatistics =& m('goodsstatistics');
        $model_goodsstatistics->edit($spec_info['goods_id'], 'carts=carts+1');
        $this->json_result(array(
            'cart'      =>  $cart_status['status'],  //���ع��ﳵ״̬
        ), 'addto_cart_successed');
    }

    /**
     *    ������Ʒ
     *
     *    @author    Garbin
     *    @return    void
     */
    function drop()
    {
        /* ����rec_id��ɾ�������ع��ﳵͳ�Ƽ��� */
        $rec_id = isset($_GET['rec_id']) ? intval($_GET['rec_id']) : 0;
        if (!$rec_id)
        {
            return;
        }

        /* �ӹ��ﳵ��ɾ�� */
        $model_cart =& m('cart');
        $droped_rows = $model_cart->drop('rec_id=' . $rec_id . ' AND session_id=\'' . SESS_ID . '\'', 'store_id');
        if (!$droped_rows)
        {
            return;
        }

        /* ���ؽ�� */
        $dropped_data = $model_cart->getDroppedData();
        $store_id     = $dropped_data[$rec_id]['store_id'];
        $cart_status = $this->_get_cart_status();
        $this->json_result(array(
            'cart'  =>  $cart_status['status'],                      //�����ܵĹ��ﳵ״̬
            'amount'=>  $cart_status['carts'][$store_id]['amount']   //����ָ�����̵Ĺ��ﳵ״̬
        ),'drop_item_successed');
    }

    /**
     *    ���¹��ﳵ����Ʒ������������ƷΪ��λ��AJAX����
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function update()
    {
        $spec_id  = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
        $quantity = isset($_GET['quantity'])? intval($_GET['quantity']): 0;
        $store_id  = isset($_GET['store_id']) ? intval($_GET['store_id']) : STORE_ID;
        if (!$spec_id || !$quantity)
        {
            /* ���Ϸ������� */
            return;
        }  
		
    	if ($store_id != 0)
       	{
       		$model_spec =& m('goodsspec');
	        $spec_info  =  $model_spec->get($spec_id);
       		$storegoods_mod = & m('storegoods');
       		$storegoods_info = $storegoods_mod->getRow("select * from pa_store_goods where spec_id = " . $spec_id . " and store_id = " . $store_id);
       		$spec_info['stock'] = $storegoods_info['stock'];
       	}
       	
        if (empty($spec_info))
        {
            /* û�иù�� */
            $this->json_error('no_such_spec');
            return;
        }

        if ($quantity > $spec_info['stock'])
        {
            /* �������� */
            $this->json_error('no_enough_goods');
            return;
        }

        /* �޸����� */
        $where = "spec_id={$spec_id} AND session_id='" . SESS_ID . "'";
        $model_cart =& m('cart');

        /* ��ȡ���ﳵ�е���Ϣ�����ڻ�ȡ�۸񲢼���С�� */
        $cart_spec_info = $model_cart->get($where);
        if (empty($cart_spec_info))
        {
            /* ��û����Ӹ���Ʒ�����ﳵ */
            return;
        }

        $store_id = $cart_spec_info['store_id'];
		
        //���¼�����Ʒ���������� 
        $shipmoney = 0;
        if ($store_id==intval(STORE_ID))
        {
        	$shipmoney = $quantity >$spec_info['logistics_num'] ? abs(floatval(($quantity - $spec_info['logistics_num']) * 5 + 10)) : 10; //���
        }
        /* �޸����� */
        $model_cart->edit($where, array(
            'quantity'  =>  $quantity,
        	'shipmoney' => $shipmoney,
        ));

        /* С�� */
        $subtotal   =   $quantity * $cart_spec_info['price'];
        /* ��������С�� */
        $credit = $quantity * $cart_spec_info['credit'];
		
        /* ����JSON��� */
        $cart_status = $this->_get_cart_status();
        //var_dump($cart_status['carts'][$store_id]['credit_total']);
        $this->json_result(array(
            'cart'      =>  $cart_status['status'],                     //�����ܵĹ��ﳵ״̬
            'subtotal'  =>  $subtotal,									//С��
        	'credit'	=> 	$credit,									//��������С��                                  
            'amount'    =>  $cart_status['carts'][$store_id]['amount'],  //���̹��ﳵ�ܼ�
        	'credit_total' => $cart_status['carts'][$store_id]['credit_total']
        ), 'update_item_successed');
    }

    /**
     *    ��ȡ���ﳵ״̬
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_cart_status()
    {
        /* Ĭ�ϵķ��ظ�ʽ */
        $data = array(
            'status'    =>  array(
        		'credit_total' => 0,	//�ܻ���
                'quantity'  =>  0,      //������
                'amount'    =>  0,      //�ܽ��
                'kinds'     =>  0,      //������
            ),
            'carts'     =>  array(),    //���ﳵ�б�����ÿ�����ﳵ��״̬
        );

        /* ��ȡ���й��ﳵ */
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
     *    ���ﳵΪ��
     *
     *    @author    Garbin
     *    @return    void
     */
    function _cart_empty()
    {
        $this->display('cart.empty.html');
    }

    /**
     *    �Թ��ﳵΪ��λ��ȡ���ﳵ�б���Ʒ��
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_carts($store_id = 0)
    {
        $carts = array();
        /* ��ȡ���й��ﳵ�е����� */
        $where_store_id = $store_id ? ' AND cart.store_id=' . $store_id : '';
        /* ֻ�����Լ����ﳵ����Ŀ���ܹ��� */
        $where_user_id = $this->visitor->get('user_id') ? " AND c.user_id=" . $this->visitor->get('user_id') : '';
        $cart_model =& m('cart');
        $cart_items = $cart_model->getAll("select c.*,gc.zprice,gc.gprice,gc.spec_1,gc.spec_2,g.spec_name_1,g.spec_name_2,s.store_name,g.simage_url,c.spec_id from pa_cart c left join pa_store s on c.store_id = s.store_id left join pa_goods g on c.goods_id = g.goods_id  left join pa_goods_spec gc on gc.spec_id=c.spec_id  where c.session_id='" . SESS_ID . "'"."$where_store_id"."$where_user_id");
        foreach($cart_items as $k => $v)
       	{
       		$cart_items[$k]['simage_url'] = IMAGE_URL.$cart_items[$k]['simage_url'];
       	}
        if (empty($cart_items))
        {
            return $carts;
        }
        $kinds = array();
        foreach ($cart_items as $item)
        {
            /* С�� */
            $item['subtotal']   = $item['price'] * $item['quantity'];
            /* ����С�� */
            $item['credit_total'] = $item['credit'] * $item['quantity'];
            $kinds[$item['store_id']][$item['goods_id']] = 1;
            /* �Ե���IDΪ���� */
            empty($item['goods_image']) && $item['goods_image'] = Conf::get('default_goods_image');
            $carts[$item['store_id']]['store_name'] = $item['store_name'];
            $carts[$item['store_id']]['amount']     += $item['subtotal'];   //�����̵��ܽ��
            $carts[$item['store_id']]['credit_total'] += $item['credit_total'];
            $carts[$item['store_id']]['quantity']   += $item['quantity'];   //�����̵�������
            $carts[$item['store_id']]['goods'][]    = $item;
            $carts[$item['store_id']]['store_id'] = $item['store_id'];
        }	
        foreach ($carts as $_store_id => $cart)
        {
            $carts[$_store_id]['kinds'] =   count(array_keys($kinds[$_store_id]));  //�����̵���Ʒ������	
        }
        
        return $carts;
        
    }
    /**
     *    ������Ʒ������Ʒ(���ݲ�ͬ������ʽ������ͬ�ķ��ؽ��)
     *
     *    @author    Garbin
     *    @return    void
     */
    function pr_add()
    {
    	
        $pr_id   = isset($_GET['pr_id']) ? intval($_GET['pr_id']) : 0;
        $quantity   = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0;
       	$store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : STORE_ID;
       	$stock = 0;
       	
		
        if (!$pr_id || !$quantity)
        {
            return;
        }

        /* �Ƿ�����Ʒ */
        $promotion_mod = &m('promotion');
        /*
        $spec_info  =  $spec_model->get(array(
            'fields'        => 'g.store_id,gs.credit, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.default_image,gs.spec_id ,gs.spec_1, gs.spec_2, gs.stock, gs.price',
            'conditions'    => $spec_id,
            'join'          => 'belongs_to_goods,belongs_to_store,g.goods,on g.goods_id = store_goods.goods_id',
        ));*/

		$promotion_info = $promotion_mod->get_promotion($pr_id);
        if (!$promotion_info)
        {
            $this->json_error('no_such_goods');
            /* ��Ʒ������ */
            return;
        }
        $stock = $promotion_info['pr_stock'];
        if ($store_id == 0)
        {
            $this->json_error('no_such_goods');
            /* ��Ʒ������ */
            return;
        }

        /* ������Լ����̵���Ʒ�����ܹ��� */
        if ($this->visitor->get('manage_store'))
        {
            if ($store_id == $this->visitor->get('manage_store'))
            {
                $this->json_error('can_not_buy_yourself');

                return;
            }
        }

        /* �Ƿ���ӹ� */
        $model_cart =& m('cart');
        $item_info  = $model_cart->get("pr_id={$pr_id} AND session_id='" . SESS_ID . "'");
        if (!empty($item_info))
        {
            $this->json_error('goods_already_in_cart');

            return;
        }

        if ($quantity > $stock)
        {
            $this->json_error('no_enough_goods');
            return;
        }

        $spec_1 = $promotion_info['spec_name_1'] ? $promotion_info['spec_name_1'] . ':' . $promotion_info['spec_1'] : $promotion_info['spec_1'];
        $spec_2 = $promotion_info['spec_name_2'] ? $promotion_info['spec_name_2'] . ':' . $promotion_info['spec_2'] : $promotion_info['spec_2'];

        //������Ʒ����������
        $shipmoney = 0;
        if ($store_id==intval(STORE_ID))
        {
	        if ($promotion_info['pr_art']==0)
	        {
	        	$shipmoney = $quantity >$promotion_info['logistics_num'] ? abs(floatval(($quantity - $promotion_info['logistics_num']) * 5 + 10)) : 10; //���
	        }else
	        {
	        	//���ʷ�
	        	$shipmoney = 0; //���
	        }
        }
        
        $specification = $spec_1 . ' ' . $spec_2;
        /* ����Ʒ���빺�ﳵ */
        $cart_item = array(
            'user_id'       => $this->visitor->get('user_id'),
            'session_id'    => SESS_ID,
            'store_id'      => $store_id,
            'spec_id'       => $promotion_info['spec_id'],
            'goods_id'      => $promotion_info['goods_id'],
            'goods_name'    => addslashes($promotion_info['goods_name']),
            'specification' => addslashes(trim($specification)),
            'price'         => $promotion_info['pr_price'],
        	'credit'		=> $promotion_info['pr_credit'],
            'quantity'      => $quantity,
            'goods_image'   => addslashes($promotion_info['default_image']),
        	'gs_id'		=> $promotion_info['gs_id'],
            'commodity_code' => $promotion_info['commodity_code'],
        	'pr_id'		=> $promotion_info['promotion_id'],
        	'shipmoney' => $shipmoney,
        );
        /* ��Ӳ����ع��ﳵͳ�Ƽ��� */
        $cart_model =&  m('cart');
        $cart_model->add($cart_item);
        $cart_status = $this->_get_cart_status();

        /* ���±���ӽ����ﳵ�Ĵ��� */
        $model_goodsstatistics =& m('goodsstatistics');
        $model_goodsstatistics->edit($promotion_info['goods_id'], 'carts=carts+1');
        $this->json_result(array(
            'cart'      =>  $cart_status['status'],  //���ع��ﳵ״̬
        ), 'addto_cart_successed');
    }
    function pr_update()
    {
        $pr_id  = isset($_GET['pr_id']) ? intval($_GET['pr_id']) : 0;
        $quantity = isset($_GET['quantity'])? intval($_GET['quantity']): 0;
        $store_id  = isset($_GET['store_id']) ? intval($_GET['store_id']) : STORE_ID;
        if (!$pr_id || !$quantity)
        {
            /* ���Ϸ������� */
            return;
        }  
		
    	if ($store_id != 0)
       	{
       		$promotion_mod =& m('promotion');
	        $promotion_info  =  $promotion_mod->get_promotion($pr_id);
       	}
        if (empty($promotion_info))
        {
            /* û�иù�� */
            $this->json_error('no_such_promotion');
            return;
        }

        if ($quantity > $promotion_info['pr_stock'])
        {
            /* �������� */
            $this->json_error('no_enough_goods');
            return;
        }

        /* �޸����� */
        $where = "pr_id={$pr_id} AND session_id='" . SESS_ID . "'";
        $model_cart =& m('cart');

        /* ��ȡ���ﳵ�е���Ϣ�����ڻ�ȡ�۸񲢼���С�� */
        $cart_spec_info = $model_cart->get($where);

        if (empty($cart_spec_info))
        {
            /* ��û����Ӹ���Ʒ�����ﳵ */
            return;
        }

        $store_id = $cart_spec_info['store_id'];

    	//���¼�����Ʒ���������� 
        $shipmoney = 0;
        if ($store_id==intval(STORE_ID))
        {
	        if ($promotion_info['pr_art']==0)
	        {
	        	$shipmoney = $quantity >$promotion_info['logistics_num'] ? abs(floatval(($quantity - $promotion_info['logistics_num']) * 5 + 10)) : 10; //���
	        }else
	        {
	        	//���ʷ�
	        	$shipmoney = 0; //���
	        }
        }
        /* �޸����� */
        $model_cart->edit($where, array(
            'quantity'  =>  $quantity,
        	'shipmoney' => $shipmoney,
        ));

        /* С�� */
        $subtotal   =   $quantity * $cart_spec_info['price'];
        /* ��������С�� */
        $credit = $quantity * $cart_spec_info['credit'];
		
        /* ����JSON��� */
        $cart_status = $this->_get_cart_status();
        //var_dump($cart_status['carts'][$store_id]['credit_total']);
        $this->json_result(array(
            'cart'      =>  $cart_status['status'],                     //�����ܵĹ��ﳵ״̬
            'subtotal'  =>  $subtotal,									//С��
        	'credit'	=> 	$credit,									//��������С��                                  
            'amount'    =>  $cart_status['carts'][$store_id]['amount'],  //���̹��ﳵ�ܼ�
        	'credit_total' => $cart_status['carts'][$store_id]['credit_total']
        ), 'update_item_successed');
    }
}

?>
