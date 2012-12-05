<?php

/**
 *    ���ﳵ�������������Ա���ﳵ�Ĺ�������������һ���ۻ�Ա�Ľӿ��ǣ����ﳵ�����ۻ�Ա����Ҫ�����Ʒ���ҹ��ﳵ�ڵ���Ʒ
 *
 *    @author    Garbin
 */

class QuickCartApp extends MallbaseApp
{
    /**
     *    �г����ﳵ�е���Ʒ
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
            $this->show_storeadmin_message('���ﳵΪ�գ�','go_back','index.php?app=kjzf&act=quick_brand_index&mobile='.$_GET['mobile']);

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
     *    ������Ʒ(���ݲ�ͬ������ʽ������ͬ�ķ��ؽ��)
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

        /* �Ƿ�����Ʒ */
        $spec_model =& m('goodsspec');
        //ԭ����
        /*
        $spec_info  =  $spec_model->get(array(
            'fields'        => 'g.store_id,g.credit,g.area_type, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.default_image,gs.spec_id ,gs.spec_1, gs.spec_2, pg.stock, gs.price',
            'conditions'    => $spec_id,
            'join'          => 'belongs_to_goods',
        ));*/
        $spec_in  = $spec_model->getAll("SELECT g.store_id,g.credit,g.area_type, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.default_image,gs.spec_id ,gs.spec_1, gs.spec_2, pg.stock, gs.price,gs.spec_id FROM pa_goods_spec gs LEFT JOIN pa_goods g  ON gs.goods_id = g.goods_id left join pa_paila_goods pg on pg.goods_id=g.goods_id WHERE gs.spec_id =".$spec_id . " and pg.store_id = " . $this->visitor->get('store_id'));
        $spec_info =$spec_in[0];			
        if (!$spec_info) //Ʒ���̳�
        {
            $this->json_error('no_such_goods');
            /* ��Ʒ������ */
            return;
        }
        //�����̳ǵĿ�治��goods_spec������
        $paila_goods_mod = & m('pailagoods');
        /*if($spec_info['area_type'] == 'pailamall') {
        	$paila_spec_info = $paila_goods_mod->get(array(
        		'conditions'	=> " spec_id = " . intval($spec_id) . " AND store_id = " . intval($this->visitor->info['store_id']) . "",
        	));
        	if(!$paila_spec_info) {
        		$this->json_error('no_such_goods');
	            // ��Ʒ������ 
	            return;
        	}
        }*/

        /* �Ƿ���ӹ� */
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
        /* ����Ʒ���빺�ﳵ */
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

        /* ��Ӳ����ع��ﳵͳ�Ƽ��� */
        $cart_model =&  m('quickcart');
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
        $model_cart =& m('quickcart');
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
        if (!$spec_id || !$quantity)
        {
            /* ���Ϸ������� */
            return;
        }

        /* �жϿ���Ƿ��㹻 */
        $model_spec =& m('goodsspec');
        $spec_info  =  $model_spec->get($spec_id);
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
        $model_cart =& m('quickcart');

        /* ��ȡ���ﳵ�е���Ϣ�����ڻ�ȡ�۸񲢼���С�� */
        $cart_spec_info = $model_cart->get($where);
        if (empty($cart_spec_info))
        {
            /* ��û����Ӹ���Ʒ�����ﳵ */
            return;
        }

        $store_id = $cart_spec_info['store_id'];

        /* �޸����� */
        $model_cart->edit($where, array(
            'quantity'  =>  $quantity,
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
            'amount'    =>  $cart_status['carts']['amount'],  //���̹��ﳵ�ܼ�
        	'credit_total' => $cart_status['carts']['credit_total']
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
     *    �Թ��ﳵΪ��λ��ȡ���ﳵ�б���Ʒ��
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_carts($store_id = 0)
    {
        $carts = array();
		
        /* ��ȡ���й��ﳵ�е����� */
        $where_store_id = $store_id ? ' AND quick_cart.store_id=' . $store_id : '';	
        /* ֻ�����Լ����ﳵ����Ŀ���ܹ��� */
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
            /* С�� */
            $item['subtotal']   = $item['price'] * $item['quantity'];
            /* ����С�� */
            $item['credit_total'] = $item['credit'] * $item['quantity'];
            /* �Ե���IDΪ���� 
            empty($item['goods_image']) && $item['goods_image'] = Conf::get('default_goods_image');
            $carts[$item['store_id']]['store_name'] = $item['store_name'];
            $carts[$item['store_id']]['amount']     += $item['subtotal'];   //�����̵��ܽ��
            $carts[$item['store_id']]['credit_total'] += $item['credit_total'];
            $carts[$item['store_id']]['quantity']   += $item['quantity'];   //�����̵�������
            $carts[$item['store_id']]['goods'][]    = $item;*/
            /* ʵ��깺�ﳵ���� */
            $carts['store_name'] = $item['store_name'];
            $carts['amount']     += $item['subtotal'];   //�����̵��ܽ��
            $carts['credit_total'] += $item['credit_total'];
            $carts['quantity']   += $item['quantity'];   //�����̵�������
            $carts['store_id']  = $item['store_id'];    //ʵ���ID
            $carts['goods'][]    = $item;            
        }
        return $carts;
    }
}

?>
