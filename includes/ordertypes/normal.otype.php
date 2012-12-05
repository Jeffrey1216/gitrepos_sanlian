<?php

/**
 *    ��ͨ��������
 *
 *    @author    Garbin
 *    @usage    none
 */
class NormalOrder extends BaseOrder
{
    var $_name = 'normal';

    /**
     *    �鿴����
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

        /* ��ȡ��Ʒ�б� */
        $data['goods_list'] =   $this->_get_goods_list($order_id);

        /* �����Ϣ */
        $data['order_extm'] =   $this->_get_order_extm($order_id);

        /* ֧����ʽ��Ϣ */
        if ($order_info['payment_id'])
        {
            $payment_model      =& m('payment');
            $payment_info       =  $payment_model->get("payment_id={$order_info['payment_id']}");
            $data['payment_info']   =   $payment_info;
        }

        /* ����������־ */
        $data['order_logs'] =   $this->_get_order_logs($order_id);

        return array('data' => $data);
    }

    /* ��ʾ������ */
    /** 
     * 	�������޸�
     * 	@alter  bottle
     * 	����ж����ɶ���ģ��
     * 	���$store_idΪ2�Ļ�.  ������Ϊ�������� $template = 'paila_order.form.html';
     **/
    function get_order_form($store_id)
    {
        $data = array();
        //�ж�ģ��.
        if(trim($_GET['goods']) == 'quickCart') {
        	$template = intval($store_id) == PAILAMALL ? 'paila_quick_order.form.html' : 'quick_order.form.html';
        } else {
        	$template = intval($store_id) == PAILAMALL ? 'paila_order.form.html' : 'order.form.html';
        }
        
        $visitor =& env('visitor');

        /* ��ȡ�ҵ��ջ���ַ */
        $data['my_address']         = $this->_get_my_address($visitor->get('user_id'));
        $data['addresses']          =   ecm_json_encode($data['my_address']);
        $data['regions']            = $this->_get_regions();

        /* ���ͷ�ʽ */
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
    
	/* ��ʾ���̶����� */
    /** 
     * 	�����ύ����Ӧ�̵Ķ���	
     * 	�������޸�
     * 	@alter  bottle
     * 	����ж����ɶ���ģ��
     **/
    function get_store_order_form()
    {
        $data = array();
        
        //�ж�ģ��.
        $template = 'store_order.form.html';
        

        $visitor =& env('visitor');

        /* ��ȡ�ҵ��ջ���ַ */
        $data['my_address']         = $this->_get_my_address($visitor->get('user_id'));
        $data['addresses']          =   ecm_json_encode($data['my_address']);
        $data['regions']            = $this->_get_regions();

        return array('data' => $data, 'template' => $template);
    }

    /**
     *    �ύ���ɶ������ⲿ������Ҫ�µĵ�����Ʒ���ͼ��û���д�ı������Լ���Ʒ���ݣ������ɺö����󷵻ض���ID
     *
     *    @author    Garbin
     *    @param     array $data
     *    @return    int
     */
    function submit_order($data)
    {
    	$store_id = 0; //�̻�ID
        /* �ͷ�goods_info��post�������� */
        extract($data);
        /* ������������Ϣ */
        $base_info = $this->_handle_order_info($goods_info, $post);
        //��ȡ$store_id;
        $store_id = $base_info['seller_id'];    
       
        if (!$base_info)
        {
            /* ������Ϣ��֤��ͨ�� */

            return 0;
        }
       	/* �������ջ�����Ϣ */
        $consignee_info = $this->_handle_consignee_info($goods_info, $post);

	    if (!$consignee_info)
	    {
	     	/* �ջ�����Ϣ��֤��ͨ�� */
	        return 0;
	    }
		$use_credit = empty($post['use_credit']) ? 0.00 : floatval($post['use_credit']);
		      
        /* ����˵����������Ϣ���ǿɿ��ģ����Կ�ʼ����� */
        /* ���붩��������Ϣ */
        //������ʵ���ܽ����ܻ����ڴ˼�ȥ�ۿ۵ȷ���
        /* //ԭ�еĹر�
        $base_info['order_amount']  =   $base_info['goods_amount'] + $consignee_info['shipping_fee'] - $base_info['discount'];
        */
		//�µ�֧����ʽ
		$base_info['order_amount']  =   $base_info['goods_amount'] + $goods_info['shipmoney'];

		$order_money = floatval(number_format(floatval($base_info['order_amount']), 2));
		$new_money = floatval(number_format(floatval($_POST['money']), 2));
		
   		if ($order_money < $new_money)
        {
			// �Ƿ�;����ͨ�� 
        	$this->_error('֧���������');
	        return 0;
        }
        //��ѯ������л���
        $user_id = $_SESSION['user_info']['user_id'];
        $member_model = & m('member');
        $member_info = $member_model->get("user_id='{$user_id}'");
        $member_credit = $member_info['credit'] - $member_info['frozen_credit']; //�û����û������

        if ($use_credit > $member_credit)
		{
			$this->_error('���Ŀ��û��ֲ��㣬�뷵�ض�������ѡ��');
        	return 0;
		}
        //��ǰ���������Ļ�����.  ֻ���ڶ������е�һ����ʾ .  ��δ�ɹ��˻�����Ч
        
 		$base_info['buyer_mobile'] = $member_info['mobile']; //�����˵��ֻ���
        //��Ӷ���״̬
        $order_model =& m('order');
        $order_id    = $order_model->add($base_info);
        if (!$order_id)
        {
            /* ���������Ϣʧ�� */
            $this->_error('create_order_failed');

            return 0;
        }

        /* �����ջ�����Ϣ */
        $consignee_info['order_id'] = $order_id;
        $consignee_info['shipping_fee'] = $goods_info['shipmoney'];
        $order_extm_model =& m('orderextm');
        $order_extm_model->add($consignee_info);
        /* ������Ʒ��Ϣ */
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
        $abc =$order_goods_model->add(addslashes_deep($goods_items)); //��ֹ����ע��
        return $order_id;
    }
	/**
     *    �ύ�����̻��������ⲿ������Ҫ�µĵ�����Ʒ���ͼ��û���д�ı������Լ���Ʒ���ݣ������ɺö����󷵻ض���ID
     *
     *    @author    Garbin
     *    @param     array $data
     *    @return    int
     */
    function submit_store_order($data)
    {
    	$store_id = 0; //�̻�ID
        /* �ͷ�goods_info��post�������� */
        extract($data);
        /* ������������Ϣ */
        $base_info = $this->_handle_store_order_info($goods_info, $post);
        
        if (!$base_info)
        {
            /* ������Ϣ��֤��ͨ�� */

            return 0;
        }

        /* �������ջ�����Ϣ */
        $consignee_info = $this->_handle_store_consignee_info($goods_info, $post);
        
        if (!$consignee_info)
        {
            /* �ջ�����Ϣ��֤��ͨ�� */
            return 0;
        }

        /* ����˵����������Ϣ���ǿɿ��ģ����Կ�ʼ����� */
        /* ���붩��������Ϣ */
        //������ʵ���ܽ��
        $base_info['order_amount']  =  $base_info['goods_amount'];
        $base_info['pay_message']   =  $post['pay_message'];
        //��Ӷ���״̬
        $order_model =& m('storeorder');
        $order_id = $order_model->add($base_info);
		
        
        if (!$order_id)
        {
            /* ���������Ϣʧ�� */
            $this->_error('create_order_failed');

            return 0;
        }
        /* �����ջ�����Ϣ */
        $consignee_info['order_id'] = $order_id;
        $consignee_info['shipping_fee'] = 0;
        $order_extm_model =& m('storeorderextm');
        $order_extm_model->add($consignee_info);
		
        
        /* ������Ʒ��Ϣ */
        $goods_items = array();
        
        foreach ($goods_info['items'] as $key => $value)
        {
        	
            $goods_items[] = array(
                'order_id'      =>  $order_id,
                'goods_id'      =>  $value['goods_id'],
                'goods_name'    =>  $value['goods_name'],
                'spec_id'       =>  $value['spec_id'],
                'specification' =>  $value['specification'],
                'price'         =>  $value['price'],//���̽�����
                'quantity'      =>  $value['quantity'],
                'goods_image'   =>  $this->mkOrderGoodsImage($value['goods_image']),
            	'gprice'        =>  $value['gprice'],//���ҹ�Ӧ��
            	'sprice'        =>  $value['sprice'], //�ۼ�
            	'credit'		=>	$value['credit'],
            );
        }
        $order_goods_model =& m('storeordergoods');
        $order_goods_model->add(addslashes_deep($goods_items)); //��ֹ����ע��

        return $order_id;
    }
    /**
     * 	@Function Ŀ¼����, ��������ͼƬĿ¼
     * 	@Return string $path ͼƬ����·��	
     * 	@Param string $sourceImg ԴͼƬ 
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
     *    �鿴����
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

        /* ��ȡ��Ʒ�б� */
        $data['goods_list'] =   $this->_get_store_goods_list($order_id);

        /* �����Ϣ */
        $data['order_extm'] =   $this->_get_store_order_extm($order_id);

        /* ֧����ʽ��Ϣ */
        if ($order_info['payment_id'])
        {
            $payment_model      =& m('payment');
            $payment_info       =  $payment_model->get("payment_id={$order_info['payment_id']}");
            $data['payment_info']   =   $payment_info;
        }

        /* ����������־ */
        $data['order_logs'] =   $this->_get_store_order_logs($order_id);
        
        $data['store_info'] = $this->_get_storeinfo($order_info['buyer_id']);

        return array('data' => $data);
    }
  
}

?>