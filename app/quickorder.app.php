<?php
class QuickorderApp extends MallbaseApp {
		
		public function index() {
		// һ������ֻ��һ����ʽ
		//$goods_info = $this->_get_goods_info();
		$goods_list = array();
		$goods_info = $this->_get_paila_goods_info();
		$memberinfo = $this->_get_member_info();	
		//var_dump($goods_info);
		$buyermobile = $memberinfo['mobile'];
        if ( $goods_info === false)
        {
            /* ���ﳵ�ǿյ� */
            $this->show_storeadmin_warning('goods_empty');

            return;
        }
   		
        if (!IS_POST)
        {
        	//���ɶ�����Ϣ
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
        	//��Ʒ����
	        foreach ($goods_info['items'] as $k => $v)
	        {
	        	if ($v['discount'] > 0.35) //������������֧����
	        	{
	        		$goods_list['brand'][] = $v;
	        		$sql .= "(NULL, " . $order_id . ", '" . $v['goods_id'] . "', '" . $v['goods_name'] . "', '" . $v['spec_id'] . "', 0, '" . $v['price'] . "', '" . $v['quantity'] . "', '" . $v['simage_url'] . "', '', '', '0', '1' , '". $v['cprice'] ."', '". $v['sprice'] ."', '". $v['gprice'] ."','". $v['zprice']."', '".$v['credit']."'),";   
	        	}
	        	else 
	        	{
	        		$goods_list['paila'][] = $v;
	        		$sql .= "(NULL, " . $order_id . ", '" . $v['goods_id'] . "', '" . $v['goods_name'] . "', '" . $v['spec_id'] . "', 0, '" . $v['price'] . "', '" . $v['quantity'] . "', '" . $v['simage_url'] . "', '', '', '1', '1' , '". $v['cprice'] ."', '". $v['sprice'] ."', '". $v['gprice'] ."','". $v['zprice']."', '".$v['credit']."'),";
	        		$credit += $v['price'] * $v['quantity']; //3��5��������Ʒ�� �۸� = ���� 
	        	}
	        } 	
	        $sql = substr($sql, 0, -1);
	        $order_goods_mod->db->query($sql);
	        //������־
	        $log = array(
	        	'order_id' => $order_id,
	        	'operator' => $this->visitor->get('user_name'),
	        	'order_status' => '��״̬',
	        	'changed_status' => '������',
	        	'remark' => '���֧��',
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
       		$this->display("paila_quick_order.index.html"); //֧��ҳ
        	
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
		        			$this->show_storeadmin_warning("֧��������֤ʧ��");
		        			return ;
		        		}
		        		
		        	}else{
		        		$this->show_storeadmin_warning("֧�����벻��Ϊ��");
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

			foreach ($goods_info['items'] as $k => $v) { // ���붨����Ʒ��¼
	        	//ɾ�����ﳵ�е���Ʒ
        		$rec_id = $v['rec_id'];
		        /* �ӹ��ﳵ��ɾ�� */
		        $droped_rows = $model_cart->drop('rec_id=' . $rec_id . ' AND session_id=\'' . SESS_ID . '\'', 'store_id');
		        // �����̻����
		        $paila_goods_mod->db->query("update pa_paila_goods set stock = stock - " . intval($v['quantity']) . ' where goods_id=' . $v['goods_id'] . ' and spec_id=' . $v['spec_id']);
		     
	        	} 
 	         //������־
	        $log = array(
	        	'order_id' => $order_id,
	        	'operator' => $this->visitor->get('user_name'),
	        	'order_status' => "������",
	        	'changed_status' => '���׳ɹ�',
	        	'remark' => '���֧��',
	        	'log_time' => time()
	        );
       		$order_log_mod->add($log);
       		$order_data = array(
       			'pay_time' => time(),
       			'payment_id' => 100,
       			'payment_name' => '���֧��',
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
        	$this->display('quick_cashier.index.html'); //����ҳ
        }
	}
	
	/**
     *    ���ɶ�����
     *
     *    @author    Garbin
     *    @return    string
     */
    function _gen_order_sn()
    {
        /* ѡ��һ������ķ��� */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        $order_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $model_order =& m('order');
        $orders = $model_order->find('order_sn=' . $order_sn);
        if (empty($orders))
        {
            /* �����ʹ����������� */
            return $order_sn;
        }

        /* ������ظ��ģ����������� */
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
     *    �����������֧��������
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_paila_quick_order_sn()
    {
        /* ѡ��һ������ķ��� */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        //�������ǰ׺
        $prefix = 'QPL';
        $order_sn = $prefix . $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $model_order =& m('quickorder');
        $orders = $model_order->find('order_sn="' . $order_sn.'"');
        if (empty($orders))
        {
            /* �����ʹ����������� */
            return $order_sn;
        }

        /* ������ظ��ģ����������� */
        return $this->_gen_order_sn();
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
            'otype'     =>  'offline',   //��������
            'allow_coupon'  => false,    //�Ƿ�����ʹ���Ż�ȯ
        );
        switch ($_GET['goods'])
        {
            default:
                /* �ӹ��ﳵ��ȡ��Ʒ */
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
                //ת��ͼƬurl,��������ͬ��������
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
                    $return['quantity'] += $goods['quantity'];                      //��Ʒ����
                    $return['amount']   += $goods['quantity'] * $goods['price'];    //��Ʒ�ܼ�
                    $return['credit_total'] += $goods['quantity'] * $goods['credit']; //�����ܻ���
                    $cart_items[$rec_id]['subtotal']    =   $goods['quantity'] * $goods['price'];   //С��
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
     *    ��ȡ�ⲿ���ݹ�����������Ʒ
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_paila_goods_info()
    {
        $return = array(
            'items'     =>  array(),    //��Ʒ�б�
            'quantity'  =>  0,          //��Ʒ����
            'amount'    =>  0,          //��Ʒ�ܼ�
        	'credit_total' => 0,		//��Ʒ�ܵ����ͻ���
            'store_id'  =>  0,          //��������
            'store_name'=>  '',         //��������
            'type'      =>  null,       //��Ʒ����
            'otype'     =>  'offline',   //��������
            'allow_coupon'  => false,    //�Ƿ�����ʹ���Ż�ȯ
        );
        switch ($_GET['goods'])
        {
            default:
                /* �ӹ��ﳵ��ȡ��Ʒ */
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
                //ת��ͼƬurl,��������ͬ��������
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
                    $return['quantity'] += $goods['quantity'];                      //��Ʒ����
                    $return['amount']   += $goods['quantity'] * $goods['price'];    //��Ʒ�ܼ�
                    $return['credit_total'] += $goods['quantity'] * $goods['credit']; //�����ܻ���
                    $cart_items[$rec_id]['subtotal']    =   $goods['quantity'] * $goods['price'];   //С��
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
				user_name => '��δע��',
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
	    		$this->json_error('��֤ʧ�ܣ�');
	    	} else {
	    		$this->json_result();	
	    	}	    	
	    }
}