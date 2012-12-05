<?php

/**
 *    ���ﳵ�������������Ա���ﳵ�Ĺ�������������һ���ۻ�Ա�Ľӿ��ǣ����ﳵ�����ۻ�Ա����Ҫ�����Ʒ���ҹ��ﳵ�ڵ���Ʒ
 *
 *    @author    Garbin
 */

class CommoncartApp extends StoreadminbaseApp
{
    /**
     *    �г����ﳵ�е���Ʒ
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

    //���빺�ﳵ���� ()
    public function addToCart() {
    	$spec_id   = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0; //����
        $quantity   = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0; //����
        if (!$spec_id || !$quantity)
        {
            return;
        }

        /* �Ƿ�����Ʒ */
        $spec_model =& m('goodsspec');
        $spec_info  =  $spec_model->get(array(
            'fields'        => 'g.zprice,g.credit, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.simage_url,gs.spec_id ,gs.spec_1, gs.spec_2, gs.price ,gs.zprice,gs.gprice',
            'conditions'    => $spec_id,
            'join'          => 'belongs_to_goods',
        ));

        if (!$spec_info)
        {
            $this->json_error('no_such_goods');
            /* ��Ʒ������ */
            return;
        }
		
        if($spec_info['price'] <= 0 || $spec_info['zprice'] <= 0) {
        	$this->json_error('price_error');
        	//$this->json_result($spec_info['sprice']);
        	return;
        }
        
        /* ������Լ����̵���Ʒ�����ܹ��� */
        if ($this->visitor->get('manage_store'))
        {
            if ($spec_info['store_id'] == $this->visitor->get('manage_store'))
            {
                $this->json_error('can_not_buy_yourself');

                return;
            }
        }

        /* �Ƿ���ӹ� */
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

        /* ����Ʒ���빺�ﳵ */
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

        /* ��Ӳ����ع��ﳵͳ�Ƽ��� */
        $cart_model =&  m('commoncart');
        $cart_model->add($cart_item);
        $this->json_error('���빺�ﳵ�ɹ�');
        return;
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
        $model_cart =& m('commoncart');
        $droped_rows = $model_cart->drop('cart_id=' . $rec_id );
        if (!$droped_rows)
        {
            return;
        }

        /* ���ؽ�� */
        $cart_status = $this->_get_cart_status();
        $this->json_result(array(
            'cart'      =>  $cart_status['status'],                     //�����ܵĹ��ﳵ״̬                
            'amount'    =>  $cart_status['status']['amount'],  //���̹��ﳵ�ܼ�
        ), 'drop_item_successed');
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
        $card_id  = isset($_GET['card_id']) ? intval($_GET['card_id']) : 0;
        $quantity = isset($_GET['quantity'])? intval($_GET['quantity']): 0;
        if (!$card_id || !$quantity)
        {
            /* ���Ϸ������� */
            return;
        }

        /* �޸����� */
        $model_cart =& m('commoncart');

        /* ��ȡ���ﳵ�е���Ϣ�����ڻ�ȡ�۸񲢼���С�� */
        $cart_info = $model_cart->get($card_id);
        if (empty($cart_info))
        {
            /* ��û����Ӹ���Ʒ�����ﳵ */
            return;
        }

        /* �޸����� */
        $model_cart->edit($card_id, array(
            'quantity'  =>  $quantity,
        ));
		
        /* С�� */
        $subtotal   =   $quantity * floatval($cart_info['price']);
        
        /* ����JSON��� */
        $cart_status = $this->_get_cart_status();
        
        $this->json_result(array(
            'cart'      =>  $cart_status['status'],                     //�����ܵĹ��ﳵ״̬
            'subtotal'  =>  $subtotal ,									//С��                          
            'amount'    =>  $cart_status['status']['amount'],  //���̹��ﳵ�ܼ�
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
                'quantity'  =>  0,      //������
                'amount'    =>  0,      //�ܽ��
            ),
            'carts'     =>  array(),    //���ﳵ�б�����ÿ�����ﳵ��״̬
        );

        /* ��ȡ���й��ﳵ */
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
     *    ���ﳵΪ��
     *
     *    @author    Garbin
     *    @return    void
     */
    function _cart_empty()
    {
        $this->display('commoncart.empty.html');
    }

    /**
     *    �Թ��ﳵΪ��λ��ȡ���ﳵ�б���Ʒ��
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_carts()
    {
        $carts = array();
        /*
      		��ȡ���й��ﳵ�е����� 
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
            /* С�� */
            $item['subtotal']   = $item['price'] * $item['quantity'];
			empty($item['goods_image']) && $item['goods_image'] = IMAGE_URL.Conf::get('default_goods_image');
            $carts['amount']     += $item['subtotal'];   //�����̵��ܽ��
            $carts['quantity']   += $item['quantity'];   //�����̵�������
            $carts['goods'][]    = $item;
        }
        return $carts;
    }
}

?>
