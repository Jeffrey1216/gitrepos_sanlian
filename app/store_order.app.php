<?php

/**
 *    �ۻ�Ա�������������ʵ�ʽ����й�̨�ۻ�Ա�Ľ�ɫ���������ô�������������ң��ۻ�Ա��Ҫ��ʲô�������һ�ѯ������Ҫ���ջ���ַ��ʲô֮�������
 ��        ��������Ļش�������һ�ŵ��ӣ����ŵ��Ӿ��ǡ�������
 *
 *    @author    Garbin
 *    @param    none
 *    @return    void
 */
class Store_orderApp extends StoreadminbaseApp
{
    /**
     *    ��д�ջ�����Ϣ��ѡ�����ͣ�֧����ʽ��
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
            //���ﳵ�ǿյ� 
            $this->show_storeadmin_warning('goods_empty');
            return;
        }
        if (!IS_POST)
        {
            /* ������Ʒ���ͻ�ȡ��Ӧ�������� */
            $goods_type = &gt($goods_info['type']);
            $order_type = &ot($goods_info['otype']);
			
            /* ��ʾ������ */
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
            /* ������Ʒ���ͻ�ȡ��Ӧ�Ķ������� */
        	$goods_type  =&gt($goods_info['type']);
            $order_type  =&ot($goods_info['otype']);

            /* ����Щ��Ϣ���ݸ��������ʹ��������ɶ���(��������ṩ����Ϣ����һ�Ŷ���) */
            $order_id = $order_type->submit_store_order(array(
                'goods_info'    =>  $goods_info,      //��Ʒ��Ϣ�������б��ܼۣ��������������̣����ͣ�,�ɿ���!
                'post'          =>  $_POST,           //�û���д�Ķ�����Ϣ
            ));
            if (!$order_id)
            {
                $this->show_storeadmin_warning($order_type->get_error());

                return;
            }
			
            /*  ����Ƿ�����ջ��˵�ַ  */
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
            /* �µ���ɺ�������Ʒ������չ��ﳵ*/
            $this->_clear_goods();
			//��ȡ��������
			$_store_goods_mod = &m('storegoods');
			$store_info = $_store_goods_mod->getRow('select s.store_name,so.order_sn from pa_store_order so left join pa_store s on so.seller_id = s.store_id where so.order_id ='.$order_id);
        	$smslog =&  m('smslog'); 
	      	import('class.smswebservice');    //������ŷ�����
	   		$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
		    $smscontent = "����:{$store_info['store_name']},�Ѿ��ɹ����������:{$store_info['order_sn']}���뾡���޸���������,����ϵ���������";
		    $mobile = DEF_MOBILE;
		    $verifytype = "storebuygoods";	
	      	$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ������Ѳ���
	      	//���ŷ��ͳɹ�
	        if ($result == 0) 
	        {
	        	$time = time();
	        	//ִ�ж�����־д�����
	        	$smsdata['mobile'] = $mobile;
	        	$smsdata['smscontent'] = $smscontent;
	        	$smsdata['type'] = $verifytype; //��������
	        	$smsdata['sendtime'] = $time;
	       		$smslog->add($smsdata);
	       	}
	       	
            /* �����ʼ� */
//            $model_order =& m('storeorder');
//
//            /* ��ȡ������Ϣ */
//            $order_info = $model_order->get($order_id);

            /* �����¼� */
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

//            $model_supply =& m('supply'); //�����޸�, �˴��������ǹ�Ӧ��
//            $supply_info  = $model_supply->get($goods_info['supply_id']);
//            $seller_address= $supply_info['email'];

//            /* ���͸�����µ�֪ͨ */
//            $buyer_mail = get_mail('tobuyer_new_order_notify', array('order' => $order_info));
//            $this->_mailto($buyer_address, addslashes($buyer_mail['subject']), addslashes($buyer_mail['message']));
//
//            /* ���͸������¶���֪ͨ */
//            $seller_mail = get_mail('toseller_new_order_notify', array('order' => $order_info));
//            $this->_mailto($seller_address, addslashes($seller_mail['subject']), addslashes($seller_mail['message']));
			
            /* ������̨���� */			
            header('Location:index.php?app=store_cashier&order_id=' . $order_id); 
        }
    }

    /**
     *    ��ȡ�ⲿ���ݹ�������Ʒ
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_goods_info()
    {
        
    	$return = array();
        /* �ӹ��ﳵ��ȡ��Ʒ */
		$cart_model =& m('commoncart');
        $sql = "SELECT gs.gprice,gs.price as sprice, gs.credit,gs.spec_id,c.goods_id,c.goods_name,gs.zprice as price,c.goods_image,c.quantity,c.specification FROM pa_common_cart c left join pa_goods_spec gs on c.spec_id=gs.spec_id 
				where c.buyer_id = " . $this->visitor->get('user_id');
		$cart_items      =  $cart_model->getAll($sql);
	    if ($cart_items)
	    {
	    	foreach ($cart_items as $key=>$item)
	        {
	            /* С�� */
	            $cart_items[$key]['subtotal']   = $item['price'] * $item['quantity'];
				empty($item['goods_image']) && $item['goods_image'] = IMAGE_URL.Conf::get('default_goods_image');
	            $return['amount']     += $cart_items[$key]['subtotal'];   //�����̵��ܽ��
	            $return['quantity']   += $cart_items[$key]['quantity'];   //�����̵�������
	        }
	
	       $return['items']        =   $cart_items;
	       $return['type']         =   'material';
	       $return['otype']        =   'normal';
	    }
       
       return $return;
    }

    /**
     *    �µ���ɺ�������Ʒ
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
