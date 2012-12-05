<?php

/**
 *    �ۻ�Ա�������������ʵ�ʽ����й�̨�ۻ�Ա�Ľ�ɫ���������ô�������������ң��ۻ�Ա��Ҫ��ʲô�������һ�ѯ������Ҫ���ջ���ַ��ʲô֮�������
 ��        ��������Ļش�������һ�ŵ��ӣ����ŵ��Ӿ��ǡ�������
 *
 *    @author    Garbin
 *    @param    none
 *    @return    void
 */
class OrderApp extends ShoppingbaseApp
{
	var $order_goods_mod;
    /**
     *    ��д�ջ�����Ϣ��ѡ�����ͣ�֧����ʽ��
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
            /* ���ﳵ�ǿյ� */
            $this->show_warning('goods_empty');
            return ;
        }
		foreach ($cart_itmes as $rec_id => $goods)
        {
             
             $money+= $goods['price'] * $goods['quantity'];  
             $credit+= $goods['credit'];
             $quantity_tatol+= $goods['quantity'];  
        }
        //�����ܼ۵���
        $credit_total = empty($credit) ? 0.00 : floatval($credit);
        $amount_total = empty($money) ? 0.00 : floatval($money);
        
        //��ѯ������л���
        $user_id = $_SESSION['user_info']['user_id'];
        $member_model = & m('member');
        $member_info = $member_model->get("user_id='{$user_id}'");
   
        $member_credit = $member_info['credit'] - $member_info['frozen_credit']; //�û����û������
        
        $this->assign('member_credit',$member_credit);
        $this->assign('real_name',$member_info['real_name']);
        $this->assign('member_mobile',$member_info['mobile']);
		$this->assign('credit_total',$credit_total);
		$this->assign('amount_total',$amount_total);
		$this->assign('quantity_tatol',$quantity_tatol);
        

        /*  ����� */
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
            /* ������Ʒ���ͻ�ȡ��Ӧ�������� */
            $goods_type     =&  gt($goods_info['type']);
            $order_type     =&  ot($goods_info['otype']);
            /* ��ʾ������ */
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
            /* �ڴ˻�ȡ���ɶ�������������Ҫ�أ��û��ύ�����ݣ�POST������Ʒ��Ϣ��������Ʒ�б���Ʒ�ܼۣ���Ʒ�����������ͣ����������� */
            $store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : STORE_ID;
            if ($goods_info === false)
            {
                /* ���ﳵ�ǿյ� */
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
        	//�û��ֵֿ���������
        	if($_POST['shipp'] == 1)
        	{
        		$shipping_fee = $goods_info['shipmoney']; 
        		$_POST['use_credit'] += $shipping_fee;
        		$_POST['money'] -=  $shipping_fee;
        	}
        	//���֧���ֽ���Ϊ0����֧����ʽһ��Ϊ����֧��
        	if ($_POST['money'] == 0)
        	{
        		$_POST['payType'] = 2; //����֧��
        	}
            
            /* ������Ʒ���ͻ�ȡ��Ӧ�Ķ������� */
            $goods_type =& gt($goods_info['type']);
            $order_type =& ot($goods_info['otype']);
            /* ����Щ��Ϣ���ݸ��������ʹ��������ɶ���(��������ṩ����Ϣ����һ�Ŷ���) */
            
            $order_id = $order_type->submit_order(array(
                'goods_info'    =>  $goods_info,      //��Ʒ��Ϣ�������б��ܼۣ��������������̣����ͣ�,�ɿ���!
                'post'          =>  $_POST,           //�û���д�Ķ�����Ϣ
            ));
            if (!$order_id)
            {
                $this->show_warning($order_type->get_error());

                return;
            }
			/* ���member�е�real_nameΪ�գ���Ĭ�ϴ���*/
			if(empty($member_info['real_name']) && isset($_POST['consignee'])){
				$data_phone = array("real_name" => trim($_POST['consignee']));
			}
			$model_member=& m('member');
			$model_member->edit($user_id,$data_phone);
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
            
            /* �µ���ɺ�������Ʒ������չ��ﳵ�����Ź�������״̬תΪ���µ�֮��� */
            $this->_clear_goods($order_id);

            /* �����ʼ� */
            $model_order =& m('order');

            /* ��ȥ��Ʒ��� */
            //$model_order->change_stock('-', $order_id);

            /* ��ȡ������Ϣ */
            $order_info = $model_order->get($order_id);
            /* �����¼� */
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
		            //�����û����ֲ���
		          	changeMemberCreditOrMoney(intval($order_info['buyer_id']),$_POST['use_credit'],FROZEN_CREDIT);
		          	
		            //������־
		            $credit_notes_info = array(
		            	'user_id'	    => intval($order_info['buyer_id']),
		            	'frozen_credit'	=> $_POST['use_credit'],
		            	'change_desc'   => '������ID��'.$order_info['seller_id'].',��������'.$order_info['seller_name'].'�� �����û����֣�'.$_POST['use_credit'].'PL',
		            	'order_id'      => $order_id,
			            'change_time'	=>	time(),
		            	'change_type'	=> 	31,
		            );
		            //�����û����ֶ����¼
		            
		            add_account_log($credit_notes_info); 
	            }
        	}
            $buyer_address = $this->visitor->get('email');
            $model_member =& m('member');
            $member_info  = $model_member->get($goods_info['user_id']);
            $seller_address= $member_info['email'];
            
//            $user_info = $model_member->get($order_info['buyer_id']);
//            /* ���͸�����µ�֪ͨ */
//            $buyer_mail = get_mail('tobuyer_new_order_notify', array('order' => $order_info));
//            $this->_mailto($buyer_address, addslashes($buyer_mail['subject']), addslashes($buyer_mail['message']));
//
//            /* ���͸������¶���֪ͨ */
//            $seller_mail = get_mail('toseller_new_order_notify', array('order' => $order_info));
//            $this->_mailto($seller_address, addslashes($seller_mail['subject']), addslashes($seller_mail['message']));
//
//            $this->send_sms_verify($user_info['mobile'], $order_info['order_sn'], $order_id); 
            
            /* �����µ����� */
            $model_goodsstatistics =& m('goodsstatistics');
            $goods_ids = array();
            foreach ($goods_info['items'] as $goods)
            {
                $goods_ids[] = $goods['goods_id'];
            }
            $model_goodsstatistics->edit($goods_ids, 'orders=orders+1'); 
            /* ������̨���� */			
            header('Location:index.php?app=cashier&order_id=' . $order_id);
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
        $return = array(
            'items'     =>  array(),    //��Ʒ�б�
            'quantity'  =>  0,          //��Ʒ����
            'amount'    =>  0,          //��Ʒ�ܼ�
        	'credit_total' => 0,		//��Ʒ�ܵ����ͻ���
            'store_id'  =>  0,          //��������
            'store_name'=>  '',         //��������
            'type'      =>  null,       //��Ʒ����
            'otype'     =>  'normal',   //��������
            'allow_coupon'  => true,    //�Ƿ�����ʹ���Ż�ȯ
        );
        switch ($_GET['goods'])
        {
            case 'groupbuy':
                /* �Ź�����Ʒ */
                $group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;
                $user_id  = $this->visitor->get('user_id');
                if (!$group_id || !$user_id)
                {
                    return false;
                }
                /* ��ȡ�Ź���¼��ϸ��Ϣ */
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

                /* �����Ϣ */
                $model_goodsspec = &m('goodsspec');
                $goodsspec = $model_goodsspec->find('goods_id='. $groupbuy_info['goods_id']);
                /* ��ȡ��Ʒ��Ϣ */
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
                /* �ӹ��ﳵ��ȡ��Ʒ */
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
						$v['price'] = $promotion_info['pr_price'];  //�����۸�
						$v['credit'] = $promotion_info['pr_credit'];//��������������
						$cart_items[$k] = $v;
					}	
				}		
                //ת��ͼƬurl,��������ͬ��������
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
                	$return['quantity'] += $goods['quantity'];                      //��Ʒ����
                    $return['amount']   += $goods['quantity'] * $goods['price'];    //��Ʒ�ܼ�
                    $return['credit_total'] +=  $goods['credit']; //�����ܻ���      	
                    $cart_items[$rec_id]['subtotal']    =   $goods['quantity'] * $goods['price'];   //С��
                    $return['shipmoney'] += $goods['shipmoney']; //���㹺�ﳵ��Ʒ�ܵ���������
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
     *    �µ���ɺ�������Ʒ
     *
     *    @author    Garbin
     *    @return    void
     */
    function _clear_goods($order_id)
    {
        switch ($_GET['goods'])
        {
            case 'groupbuy':
                /* �Ź�����Ʒ */
                $model_groupbuy =& m('groupbuy');
                $model_groupbuy->updateRelation('be_join', $_GET['group_id'], $this->visitor->get('user_id'), array(
                    'order_id'  => $order_id,
                ));
            break;
            default://���ﳵ�е���Ʒ
                /* ������������ָ�����ﳵ */
                $_GET['store_id'] = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
                $store_id = $_GET['store_id'];
                if (!$store_id)
                {
                    return false;
                }
                $model_cart =& m('cart');
                $model_cart->drop("store_id = {$store_id} AND session_id='" . SESS_ID . "'");
                //�Ż�ȯ��Ϣ����
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
     * ����Ż�ȯ��Ч��
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

        // �����Ʒ�۸����Ż�ȯҪ��ļ۸�

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
            $this->show_warning('�ֻ���Ϊ�գ�');  //�ֻ�����Ϊ��
           	return;
        }else
        {
        	if (is_mobile($mobile))
        	{
        		$smslog =&  m('smslog'); 
	        
        		$ms =& ms();    //�����û�ϵͳ
        	
        		//��������������php���л�����ʱδ���ÿ���soap��չ��������ʱ��ʹ��webservice��ʽ
        		import('class.smswebservice');    //������ŷ�����
        		$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
        		$verify = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);//��֤��
        		$verifytype = $_GET['verifytype']?$_GET['verifytype']:'buy_verify'; //������֤������
        		if ($verifytype=='modifymobile')
        		{
        			$smscontent = str_replace('{verify}',$verify,Lang::get('smscontent.modifymobile_verify'));
        		}else 
        		{
        			$smscontent = str_replace('{verify}',$verify,'�𾴵Ŀͻ�,���Ķ�����Ϊ:'.$order_sn . "ȡ���ֻ���֤��Ϊ{verify}����������ף����������죡");
        		}
				//echo "OK";
        		$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ�����֤�����
        		//���ŷ��ͳɹ�
        		$order_mod = & m('order');
        		if ($result == 0) 
        		{
        			//����֤��д��SESSION
        			$time = time();
        			
        			$order_mod->edit($order_id, array('rule_num' => $verify));

        			//ִ�ж�����־д�����
        			$smsdata['mobile'] = $mobile;
        			$smsdata['smscontent'] = $smscontent;
        			$smsdata['type'] = $verifytype; //ע����֤����
        			$smsdata['sendtime'] = $time;
        			
        			$smslog->add($smsdata);
        			
        		}else
        		{
	        		$this->show_warning('���ŷ���ʧ�ܣ�');
	        		return;
        		}
        	} else {
        		$this->show_warning('�ֻ������ʽ����ȷ��');
        		return;
        	} 
        return;
   		}
     }
}
?>
