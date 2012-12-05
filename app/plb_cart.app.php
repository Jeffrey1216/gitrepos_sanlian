<?php
class Plb_cartApp extends MallbaseApp{
	public function __construct()
    {
        $this->Plb_cartApp();
    }

    public function Plb_cartApp()
    {
    	parent::__construct();
    	$credit_order_mod = & m('creditorder');
    }
    
    //��ʾ���ﳵ��Ϣ
	function index()
	{
		$credit_cart_mod = & m('creditcart');
		$user_id = $this->visitor->get('user_id');
		$cart_info = $credit_cart_mod->getRow("select * from pa_credit_cart c left join pa_credit_goods g on c.credit_id = g.id where c.buyer_id = " . $user_id);
		if(!$cart_info)
		{
			$this->display("plb_cart.empty.html");
			return;
		}
		$this->assign('cart',$cart_info);
		$this->display("pl.cart.index.html");
	}
	
	//���ﳵ���
	function add() 
	{
		$credit_goods_mod = & m('creditgoods');
		$credit_cart_mod = & m('creditcart');
		$credit_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if (0 == $credit_id)
		{
			$this->show_warning('���������Ʒ�����ڣ�');
			return;
		}
		$goods_info=$credit_goods_mod->getRow("select * from pa_credit_goods where id = " .$credit_id);
		$buyer_id = $this->visitor->get('user_id');
		$buyer_name = $this->visitor->get('user_name');
		//var_dump($this->visitor->get('user_name'));
		//die;
		$seller_id = $goods_info['user_id'];
		$seller_name = $goods_info['user_name'];
		$price = $goods_info['price'];
		$credit = $goods_info['credit_num'];
		
		if(!$this->visitor->has_login)
		{
			header("Location:index.php?app=member&act=login");
			return;
		}
		if(!$goods_info)
		{
			$this->show_warning('���������Ʒ�����ڣ�');
			return;
		}
		
		if($buyer_id == $seller_id)
		{
			$this->show_warning("���ܹ����Լ�����Ʒ��");
			return;
		}
		
		if($goods_info['type'] == 3)
		{
			$this->show_warning('���������Ʒ���۳���������ѡ��');
			return;
		}
		
		$cart_info = $credit_cart_mod->getRow("select * from pa_credit_cart where buyer_id = " .$buyer_id);

		if($cart_info)
		{
			$this->show_warning("��һ��ֻ���Թ���һ����Ʒ!");
			return;
		}
		$data=array();
    	$data['credit_id'] = $credit_id;
    	$data['seller_id'] = $seller_id;
    	$data['seller_name'] = $seller_name;
    	$data['buyer_id'] = $buyer_id;
    	$data['buyer_name'] = $buyer_name;
    	$data['price'] = $price;
    	$data['credit'] = $credit;
    	$data['time'] = time();
    	$cart = $credit_cart_mod->add($data);
    	
    	if (!$cart)
    	{
    		$this->show_warning("��ӹ��ﳵʧ��,��������ӣ�");
    		return;
    	}
    	
    	header("Location:index.php?app=plb_cart&credit_id=$credit_id");
    	
	}
	
	//ɾ��
	function drop()
	{
		$credit_cart_mod = & m('creditcart');
		$credit_id = empty($_GET['credit_id']) ? 0 : intval($_GET['credit_id']);
		$cart = $credit_cart_mod->getRow("select * from pa_credit_cart where credit_id = " .$credit_id);
		$id = intval($cart['id']);
		$credit_cart_mod->drop($id);
		header("Location:index.php?app=plb_cart&act=cart");
	}
	
	//���ﳵΪ��
	function cart()
    {
        $this->display('plb_cart.empty.html');
    }
    
    //��д����
    function order()
    {
    	$credit_cart_mod = & m('creditcart');
    	$credit_order_mod = & m('creditorder');
    	$credit_goods_mod = & m('creditgoods');
		$user_id = $this->visitor->get('user_id');
		$credit_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if (0 == $credit_id)
		{
			$this->show_warning('���ﳵ�ǿյ�!');
			return;
		}
		//var_dump($credit_id);
		
		$cartinfo = $credit_cart_mod->getRow("select c.credit_id,c.id,c.seller_id,c.seller_name,c.price,c.credit,g.info from pa_credit_cart c 
											left join pa_credit_goods g on c.credit_id = g.id where c.buyer_id = " . $user_id);
		
		$cid = $cartinfo['id'];//��ȡɾ����id
		$seller_id = $cartinfo['seller_id'];
		$seller_name = $cartinfo['seller_name'];
		$buyer_name = $this->visitor->get('user_name');
		$order_amount = $cartinfo['price'];
		$credit = $cartinfo['credit'];
		$id = $cartinfo['credit_id'];//�ж��Ƿ��۳���id
		$this->assign('cart',$cartinfo);
		
    	if(!IS_POST)
    	{
    		//ȷ�϶���ǰ���ж�
			$goods_info = $credit_goods_mod->getRow("select * from pa_credit_goods where id = " .$credit_id);
	    	if($goods_info['type'] == 3)
			{
				$this->show_warning('���������Ʒ���۳���������ѡ��');
				return;
			}
    		$this->display("plb_order.form.html");
    	} else {
	    	//�ύ����ǰ���ж�
			if (!$id)
			{
				$this->show_warning('���ﳵ�ǿյ�!');
				return;
			}
	    	$goods_info1=$credit_goods_mod->getRow("select * from pa_credit_goods where id = " .$id);
	    	if($goods_info1['type'] == 3)
			{
				$this->show_warning('���������Ʒ���۳���������ѡ��');
				return;
			}
    		
    		//�Զ���ȡ������
			//$order_sn = strtoupper(substr(uniqid(rand()), -10));//��Ӣ����ĸ�Ķ�����
			mt_srand((double)microtime()*1000000);//�����ֶ�����
			$timestamp = gmtime();
			$y = date('y',$timestamp);
			$z = date('z',$timestamp);
			$order_sn = $y.str_pad($z,3,'0',STR_PAD_LEFT).str_pad(mt_rand(1,99999),5,'0',STR_PAD_LEFT);;
    		$data=array();
    		$data['order_sn'] = $order_sn;
	    	$data['seller_id'] = $seller_id;
    		$data['seller_name'] = $seller_name;
    		$data['buyer_id'] = $user_id;
     		$data['buyer_name'] = $buyer_name;
    		$data['add_time'] = time();
    		$data['content'] = trim($_POST['content']);
    		$data['order_amount'] = $order_amount;
    		$data['credit'] = $credit;
    		$data['credit_id'] = $credit_id;
    		$order = $credit_order_mod->add($data);
    		$credit_cart_mod->drop($cid);
    		header("Location:index.php?app=plb_cart&act=payment&id={$order}");
    	 }
    	
    }
    function payment()
    {
    	$id= empty($_GET['id']) ? 0 : intval($_GET['id']); //�ⲿ�ṩ����ID
    	if (!id)
    	{
    		$this->show_warning("δ�ҵ�����!");
    		return;
    	}
    	$credit_order_mod = & m('creditorder');
    	$user_id=$this->visitor->get('user_id');
    	
    	$credit_order_info = $credit_order_mod->getRow("select * from pa_credit_order where buyer_id = " .$user_id . " and id = " .$id);
    	if (!$credit_order_info)
    	{
    		$this->show_warning("δ�ҵ�����!");
    		return;
    	}
    	
    	if ($credit_order_info['payment_code'] != 'cod' && $credit_order_info['status'] != ORDER_PENDING) //������Ч���ж� 
    	{
    		$this->show_warning("����������!");
    		return;
    	}
    	$payment_model =& m('payment');
    	if (!$credit_order_info['payment_id']) //�ж��Ƿ�ѡ��֧����ʽ, ���δѡ��, ����ѡ��֧����ʽ
    	{
	    	/* ����û��ѡ��֧����ʽ��������ѡ��֧����ʽ */
	        $payments = $payment_model->get_enabled(0);
            if (empty($payments))
            {
                $this->show_warning('û�п�ѡ��֧����ʽ!');

                return;
            }
            
            foreach ($payments as $key => $payment)
            {
                if ($payment['is_online'])
                {
                    $all_payments['online'][] = $payment;
                }
                else
                {
                    $all_payments['offline'][] = $payment;
                }
            }
            $this->assign('order',$credit_order_info);
            $this->assign('payments', $all_payments);
            $this->display("plb.payment.html");
    	} else {  //�����ѡ��֧����ʽ, ֱ����ת֧������
    		/* ����ֱ�ӵ�����֧�� */
            /* ��֤֧����ʽ�Ƿ���ã������ڰ������У�������ʹ�� */
            if (!$payment_model->in_white_list($credit_order_info['payment_code']))
            {
                $this->show_warning('payment_disabled_by_system');

                return;
            }
            
    		$payment_info  = $payment_model->get("payment_code = '{$credit_order_info['payment_code']}' AND store_id=0");
            /* ����̨û������֧����ʽ��������ʹ�� */
            if (!$payment_info['enabled'])
            {
                $this->show_warning('payment_disabled');

                return;
            }
            
            /* ����֧��URL��� */
            $payment    = $this->_get_payment($credit_order_info['payment_code'], $payment_info);
            $payment_form = $payment->get_credit_payform($credit_order_info);
            
            /* ���¸���� Ŀǰ��֧�����¸���ķ�ʽ  */
            if (!$payment_info['is_online'])
            {
            	$this->show_warning("Ŀǰ��֧�����¸���! ��ѡ������֧����ʽ.!");
            	return;
                $this->_curlocal(
                    Lang::get('post_pay_message')
                );
            }

            /* ��ת����ʵ����̨ */
            $this->_config_seo('title', Lang::get('cashier'));
            $this->assign('payform', $payment_form);
            $this->assign('payment', $payment_info);
            $this->assign('order', $credit_order_info);
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->display('plb.payform.html');
    	}
    }
    
	/**
     *    ȷ��֧��
     *
     *    @author    Garbin
     *    @return    void
     */
    function goto_pay()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $payment_id = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }
        if (!$payment_id)
        {
            $this->show_warning('no_such_payment');

            return;
        }
        $order_model =& m('creditorder');
        $order_info  = $order_model->get("id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_warning('no_such_order');

            return;
        }

        #���ܲ�����
        if ($order_info['payment_id'])
        {
            $this->_goto_pay($order_id);
            return;
        }

        /* ��֤֧����ʽ */
        $payment_model =& m('payment');
        $payment_info  = $payment_model->get($payment_id);
        if (!$payment_info)
        {
            $this->show_warning('no_such_payment');

            return;
        }

        /* ����֧����ʽ */
        $edit_data = array(
            'payment_id'    =>  $payment_info['payment_id'],
            'payment_code'  =>  $payment_info['payment_code'],
            'payment_name'  =>  $payment_info['payment_name'],
        );

        /* ����ǻ��������ı䶩��״̬ */
        if ($payment_info['payment_code'] == 'cod')
        {
            $edit_data['status']    =   ORDER_SUBMITTED;
        }

        $order_model->edit($order_id, $edit_data);

        /* ��ʼ֧�� */
        $this->_goto_pay($order_id);
    }
    
	function _goto_pay($order_id)
    {
        header('Location:index.php?app=plb_cart&act=payment&id=' . $order_id);
    }
/**
     *    ֧����ɺ󷵻ص�URL���ڴ�ֻ������ʾ�����Զ��������κ��޸Ĳ���,���ﲻ�ϸ���֤�����ı䶩��״̬
     *
     *    @author    Garbin
     *    @return    void
     */
    function returnurl()
    {
        //������֧�������Ƹ�ͨ�ȵ�����״̬�ı�ʱ��֪ͨ��ַ
        
    	$order_id   = 0;
        if(isset($_POST['order_id']))
        {
            $order_id = intval($_POST['order_id']);
        }
        else if(isset($_GET['order_id']))
        {
            $order_id = intval($_GET['order_id']);
        } else {
        	$sign = $_GET['sign'];
	    	import('AES');    //������ŷ�����
	    	$aes = new AES(true);
	    	$key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";// 128λ��Կ
			$keys = $aes->makeKey($key);
			//���ܺ��ǩ���ַ���
			$cpt = $aes->decryptString($sign, $keys);
			//�������ܺ�ǩ���ַ���
			@list($ordersn,$money, $verify) = explode('&', $cpt, 3);
			$order_mod=& m('creditorder');
			$order_info_old = $order_mod->get(array('conditions' => " order_sn = '" . $ordersn . "' "));
			$order_id = $order_info_old['order_id'];
        }
        if (!$order_id)
        {
            /* ��Ч��֪ͨ���� */
            $this->show_warning('forbidden');

            return;
        }

        /* ��ȡ������Ϣ */
        $model_order =& m('creditorder');
        $order_info  = $model_order->get($order_id);
        if (empty($order_info))
        {
            /* û�иö��� */
            $this->show_warning('forbidden');

            return;
        }

        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("payment_code='{$order_info['payment_code']}' AND store_id=0");
        if (empty($payment_info))
        {
            /* û��ָ����֧����ʽ */
            $this->show_warning('no_such_payment');

            return;
        }

        /* ������Ӧ��֧����ʽ */
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);

        /* ��ȡ��֤��� */
        $notify_result = $payment->verify_notify($order_info);
        if ($notify_result === false)
        {
            /* ֧��ʧ�� */
            $this->show_warning($payment->get_error());

            return;
        }
        $is_success = true;
        $this->assign("is_success" , $is_success);

        #TODO ��ʱ�ڴ�Ҳ�ı䶩��״̬Ϊ������ԣ�ʵ�ʷ���ʱӦ�Ѵ˶�ȥ��������״̬�ĸı���notifyΪ׼
        $this->_change_order_status($order_id, $order_info['extension'], $notify_result);

        /* ֻ��֧��ʱ��ʹ�õ�return_url������������ʾ����Ϣ��֧���ɹ�����ʾ��Ϣ */
        $this->_curlocal(LANG::get('pay_successed'));
        $this->assign('order', $order_info);
        $this->assign('payment', $payment_info);
        $this->display('paynotify.index.html');
    }
    
	

    /**
     *    ֧����ɺ��ⲿ���ص�֪ͨ��ַ���ڴ˻���ж���״̬�ĸı䣬�����ϸ���֤���ı䶩��״̬
     *
     *    @author    Garbin
     *    @return    void
     */
    function notify()
    {
        //������֧�������Ƹ�ͨ�ȵ�����״̬�ı�ʱ��֪ͨ��ַ
        $order_id   = 0;
        if(isset($_POST['order_id']))
        {
            $order_id = intval($_POST['order_id']);
        }
        else if(isset($_GET['order_id']))
        {
            $order_id = intval($_GET['order_id']);
        } else {
        	$sign = $_GET['sign'];
	    	import('AES');    //������ŷ�����
	    	$aes = new AES(true);
	    	$key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";// 128λ��Կ
			$keys = $aes->makeKey($key);
			//���ܺ��ǩ���ַ���
			$cpt = $aes->decryptString($sign, $keys);
			//�������ܺ�ǩ���ַ���
			@list($ordersn,$money, $verify) = explode('&', $cpt, 3);
			$order_mod = & m('creditorder');
			$order_info_old = $order_mod->get(array('conditions' => " order_sn = '" . $ordersn . "' "));
			$order_id = $order_info_old['order_id'];
        }
        if (!$order_id)
        {
            /* ��Ч��֪ͨ���� */
            $this->show_warning('no_such_order');
            return;
        }

        /* ��ȡ������Ϣ */
        $model_order =& m('creditorder');
        $order_info  = $model_order->get($order_id);
        if (empty($order_info))
        {
            /* û�иö��� */
            $this->show_warning('no_such_order');
            return;
        }
		
        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("payment_code='{$order_info['payment_code']}' AND store_id=0");
        if (empty($payment_info))
        {
            /* û��ָ����֧����ʽ */
            $this->show_warning('no_such_payment');
            return;
        }
			
        /* ������Ӧ��֧����ʽ */
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);
		
        /* ��ȡ��֤��� */
        $notify_result = $payment->verify_notify($order_info, true);
        if ($notify_result === false)
        {
            /* ֧��ʧ�� */
            $payment->verify_result(false);
            return;
        }
		
        //�ı䶩��״̬
        $this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        $payment->verify_result(true);
        $this->finish($order_id);

        $this->_curlocal(LANG::get('pay_successed'));
        $this->assign('order', $order_info);
        $this->assign('payment', $payment_info);
        $this->display('paynotify.index.html');
    }
    
	/**
     *    �ı䶩��״̬
     *
     *    @author    Garbin
     *    @param     int $order_id
     *    @param     string $order_type
     *    @param     array  $notify_result
     *    @return    void
     */
    function _change_order_status($order_id, $order_type, $notify_result)
    {
        /* ����֤������ݸ��������ʹ��� */
        $order_type  =& ot($order_type);
        $order_type->respond_credit_notify($order_id, $notify_result);    //��Ӧ֪ͨ
    }
    
    /**
     * 		֧����һ���
     * 
     */
    public function finish($order_id)
    {
		if (!$order_id)
		{
			$this->show_warning('û�п��ö���!');
			return;
		}    	
		$order_mod = & m('creditorder');
		$order_info = $order_mod->get($order_id);
		if (!$order_info)
		{
			$this->show_warning('û�п��ö���!');
			return;
		}
    }
}
?>