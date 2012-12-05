<?php

/*�̻���̨������*/
class ShopadminApp extends MallbaseApp
{
    /* �̻���̨���� */
    function index()
    {
    	$username = trim($_GET['username']);  //�û���
        $password = trim($_GET['password']);  //����
        $key = trim($_GET['key']);
        if ($key!='shjeb600b1fd579f47433b88e8d85291')
        {
        	$this->show_warning('������ר�õ��̻��ͻ��˹���');
            return;
        }elseif (empty($username) || empty($password))
        {
        	$this->show_warning('����Ȩ���ʸ�ҳ��');
        	return;
        }
        
        $ms =& ms(); //���ӻ�Աϵͳ
        $user_id = $ms->user->auth($username, $password);
        if (!$user_id)
        {
            /* δͨ����֤����ʾ������Ϣ */
            echo 'Fail1';

            return;
        }else
        {
            /* ͨ����֤��ִ�е�½���� */
            $this->_do_login($user_id);
            
            /* ͬ����½�ⲿϵͳ */
            $synlogin = $ms->user->synlogin($user_id);
            
			header('Location: index.php?app=shopadmin&act=storeadmin'); //��ת��֤�������
        } 
    }

    
    /* �̻���̨��ҳ */
    function storeadmin()
    {
    	$flag = $_GET['flag'];
    	if ($this->visitor->has_login) //�ж��û��Ƿ��Ѿ�����
    	{
	    	if (!$this->visitor->get('manage_store'))
	        {
	            /* �����ǵ��̹���Ա */
	            echo 'Fail2';
	            $this->visitor->logout();
	            return;
	        }
	
	        /* �����̿���״̬ */
	        $state = $this->visitor->get('state');
	        
	        if ($state == 0)    
	        {
	        	/* ���̻�δͨ�����*/
	            echo 'Fail3';
				$this->visitor->logout();
	            return;
	        }
	        elseif ($state == 2)
	        {
	        	/* �����ѱ��ر�*/
	            echo 'Fail4';
				$this->visitor->logout();
	            return;
	        }
	        
    		if (!$flag)
    		{
    			$_SESSION['clientshop'] = 'true';
	    		echo 'True1';   //����ɹ�
	    		//header('Location: index.php?app=shopadmin&act=storeadmin&flag=true');
	    	}else
	    	{
	    		$store_id = $this->visitor->get('store_id');
	    		//echo "<script>alert({$store_id});</script>";
	    		$store_mod = & m('store');
	    		$store_info = $store_mod->get($store_id);
	    		$this->assign('is_paila_store',$store_info['is_paila_store']);
	    		$is_paila_mall = false;
	    		if($store_id == PAILAMALL) {
	    			//�������̳�
	    			$is_paila_mall = true;
	    		} else {
	    			$is_paila_mall = false;
	    		}
	    		$is_system_store = false;
	    		if ($store_id==2||$store_id==3||$store_id==4)
	    		{
	    			$is_system_store = true;
	    		}
	    		$this->assign('is_system_store',$is_system_store);
	    		
	    		$this->assign('is_paila_mall',$is_paila_mall);
	    		/* �̻���̨��ҳ*/
		        $this->display('storeadmin.index.html');
	    	}
    	}
    }
    
    /* �̻���̨-������Ϣ                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 */
    function store_info()
    {
        /* ����¶���Ϣ���� */
        $cache_server =& cache_server();
        $cache_server->delete('new_pm_of_user_' . $this->visitor->get('user_id'));

        $user = $this->visitor->get();
        $user_mod =& m('member');
        $info = $user_mod->get_info($user['user_id']);
        $user['portrait'] = portrait($user['user_id'], $info['portrait'], 'middle');
        $this->assign('user', $user);

        /* �������úͺ����� */
        if ($user['has_store'])
        {
            $store_mod =& m('store');
            $store = $store_mod->get_info($user['has_store']);
            
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $store['credit_image'] = $this->_view->res_base . '/images/' . $store_mod->compute_credit($store['credit_value'], $step);
            $this->assign('store', $store);
            $this->assign('store_closed', STORE_CLOSED);
        }
        $goodsqa_mod = & m('goodsqa');
        $groupbuy_mod = & m('groupbuy');
        $order_mod =& m('order');
        
        /* �������ѣ����������ʹ��������� */
        if ($user['has_store'])
        {

            $sql7 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id = '{$user['user_id']}' AND status = '" . ORDER_SUBMITTED . "'";
            $sql8 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id = '{$user['user_id']}' AND status = '" . ORDER_ACCEPTED . "'";
            $sql9 = "SELECT COUNT(*) FROM {$goodsqa_mod->table} WHERE store_id = '{$user['user_id']}' AND reply_content ='' ";
            $sql10 = "SELECT COUNT(*) FROM {$groupbuy_mod->table} WHERE store_id='{$user['user_id']}' AND state = " .GROUP_END;
            $seller_stat = array(
                'submitted' => $order_mod->getOne($sql7),
                'accepted'  => $order_mod->getOne($sql8),
                'replied'   => $goodsqa_mod->getOne($sql9),
                'groupbuy_end'   => $goodsqa_mod->getOne($sql10),
            );
			
            $this->assign('seller_stat', $seller_stat);
        }
        /* �������ѣ� ���̵ȼ�����Ч�ڡ���Ʒ�����ռ� */
        if ($user['has_store'])
        {
            $store_mod =& m('store');
            $store = $store_mod->get_info($user['has_store']);
			
            $grade_mod = & m('sgrade');
            $grade = $grade_mod->get_info($store['sgrade']);

            $storegoods_mod = &m('storegoods');
            //$goods_num = $storegoods_mod->get_count_of_store($user['has_store']);
            $goods_num = $storegoods_mod->getOne("select count(*) from pa_store_goods where store_id = " . $user['has_store']);

            $uploadedfile_mod = &m('uploadedfile');
            $space_num = $uploadedfile_mod->get_file_size($user['has_store']);
            $sgrade = array(
                'grade_name' => $grade['grade_name'],
                'add_time' => empty($store['end_time']) ? 0 : sprintf('%.2f', ($store['end_time'] - gmtime())/86400),
                'goods' => array(
                    'used' => $goods_num,
                    'total' => $grade['goods_limit']),
                'space' => array(
                    'used' => sprintf("%.2f", floatval($space_num)/(1024 * 1024)),
                    'total' => $grade['space_limit']),
                    );
            $this->assign('sgrade', $sgrade);

        }
        $this->display('storeadmin.userinfo.html');
    }
    
    //���̳�ֵ
    function recharge()
    {
    	if (!IS_POST)
    	{
    		$this->display("recharge.form.html");
    	}
    	else
    	{
    		$balance = empty($_POST['balance']) ? 0 : floatval($_POST['balance']);	
    		if ($balance <= 0)
    		{
    			$this->show_storeadmin_warning("��������ȷ�ĳ�ֵ���");
    			return;
    		}
    		$rechargeorder_mod = & m('rechargeorder');
    		$chargeinfo = $rechargeorder_mod->get("buyer_id = {$this->visitor->get('user_id')} AND status=0 AND type=2 AND payment_id=0");
    		
    		//�ж��Ƿ��������ɵ��û���ֵ��������
    		if ($chargeinfo)
    		{
    			$data['add_time'] = gmtime();
    			$data['balance']  = $balance;
    			$data['order_sn'] = $this->_get_order_sn();
    			$order_id = $rechargeorder_mod->edit($chargeinfo['order_id'],$data);
    			if ($order_id)
    			{
    				$chargeinfo['balance']  = $data['balance'];
    				$chargeinfo['order_sn'] = $data['order_sn'];
    			}else
    			{
    				$this->show_storeadmin_warning('���ɶ���ʧ��');
    				return;
    			}
    		}else
    		{
	    		//����֧������
	    		$data = array(
	    			'balance' => $balance,
	    			'buyer_id' => $this->visitor->get('user_id'),
	    			'buyer_name' => $this->visitor->get('user_name'),
	    			'order_sn' => $this->_get_order_sn(),
	    			'status' => 0,
	    			'add_time' => gmtime(),
	    			'casher_time' => 0,
	    			'type'	=> 2
	    		); 
	    		$order_id = $rechargeorder_mod->add($data);
	    		$chargeinfo = $rechargeorder_mod->get($order_id);
    		}
    		if (!$chargeinfo)
    		{
    			$this->show_storeadmin_warning('���ɶ���ʧ��');
    			return;
    		}
    		
	    	$payment_model =& m('payment');
		
            /* ����û��ѡ��֧����ʽ��������ѡ��֧����ʽ */
            $payments = $payment_model->get_alipay();
            if (empty($payments))
            {
                $this->show_storeadmin_warning('��ֵ�����Ѿ��رգ�');

                return;
            }

            $all_payments = array('online' => array(), 'offline' => array());
            foreach ($payments as $key => $payment)
            {
                if ($payment['is_online'])
                {
                    $all_payments['online'][] = $payment;
                }
            }
            $this->assign('order', $chargeinfo);
            $this->assign('payments', $all_payments);
            $this->display('recharge.payment.html');
    	}
    }
    
    function goto_pay()
    {
    	$payment_id = empty($_POST['payment_id']) ? 0 : intval($_POST['payment_id']);
    	$order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
    	if ($payment_id == 0)
    	{
    		$this->show_storeadmin_warning('��Ч��֧����ʽ');
    		return;
    	}
    	$payment_model =& m('payment');
    	$_payment_info = $payment_model->get($payment_id);
    	/* ��֤֧����ʽ�Ƿ���ã������ڰ������У�������ʹ�� */
        if (!$payment_model->in_white_list($_payment_info['payment_code']))
        {
            $this->show_storeadmin_warning('payment_disabled_by_system');

            return;
        }
        $payment_info  = $payment_model->get("payment_code = '{$_payment_info['payment_code']}' AND store_id=0");

        /* ����̨û������֧����ʽ��������ʹ�� */
        if (!$payment_info['enabled'])
        {
            $this->show_storeadmin_warning('payment_disabled');

            return;
        }
        $rechargeorder_mod = & m('rechargeorder');
        //д�붨��֧����ʽ����Ϣ
        $rechargeorder_mod->edit($order_id, array(
        	'payment_id' => $payment_id,
        	'payment_code' => $_payment_info['payment_code'],
        ));
        $order_info = $rechargeorder_mod->get($order_id);
        $payment    = $this->_get_payment($_payment_info['payment_code'], $payment_info);
        $payment_form = $payment->get_recharge_payform($order_info);
        $this->assign('payform', $payment_form);
        $this->assign('order', $order_info);
        header('Content-Type:text/html;charset=' . CHARSET);
        $this->display('recharge.payform.html');
    }
    
	/**
     *    ���ɶ�����
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_order_sn()
    {
        /* ѡ��һ������ķ��� */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        $order_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $model_order =& m('rechargeorder');
        $orders = $model_order->find('order_sn=' . $order_sn);
        if (empty($orders))
        {
            /* �����ʹ����������� */
            return $order_sn;
        }

        /* ������ظ��ģ����������� */
        return $this->_gen_order_sn();
    }
    
/**
     *    ֧����ɺ󷵻ص�URL���ڴ�ֻ������ʾ�����Զ��������κ��޸Ĳ���,���ﲻ�ϸ���֤�����ı䶩��״̬
     *
     *    @author    Garbin
     *    @return    void
     */
    function return_info()
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
			$order_mod = & m('order');
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
        $model_order =& m('rechargeorder');
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
        $notify_result = $payment->verify_recharge_notify($order_info);
        if ($notify_result === false)
        {
            /* ֧��ʧ�� */
            $this->show_warning($payment->get_error());

            return;
        }
        
        $is_success = true;
        $this->assign("is_success" , $is_success);

        #TODO ��ʱ�ڴ�Ҳ�ı䶩��״̬Ϊ������ԣ�ʵ�ʷ���ʱӦ�Ѵ˶�ȥ��������״̬�ĸı���notifyΪ׼
        $this->_change_order_status($order_id);

        /* ֻ��֧��ʱ��ʹ�õ�return_url������������ʾ����Ϣ��֧���ɹ�����ʾ��Ϣ */
        $this->_curlocal(LANG::get('pay_successed'));
        $this->assign('order', $order_info);
        $this->assign('payment', $payment_info);
        $this->display('rechargepaynotify.index.html');
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
			$order_mod = & m('order');
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
        $model_order =& m('rechargeorder');
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
        $notify_result = $payment->verify_recharge_notify($order_info, true);
        if ($notify_result === false)
        {
            /* ֧��ʧ�� */
            $payment->verify_result(false);
            return;
        }
		
        //�ı䶩��״̬
        $this->_change_order_status($order_id);
        $payment->verify_result(true);
    }
    
    function _change_order_status($order_id)
    {
    	$model_order =& m('rechargeorder');
        $order_info  = $model_order->get($order_id);
        if ($order_info['status'] == 0)
        {
        	$model_order->edit($order_id, array(
        		'casher_time' => gmtime(),
        		'status' => 1,
        	));
        	changeMemberCreditOrMoney($order_info['buyer_id'] , $order_info['balance'] , ADD_MONEY);
	    	
        	if ($order_info['type'] == 1)
        	{
        		//��д�����¼
		    	$param = array(
		    		'user_id' => $order_info['buyer_id'],
		    		'user_money' => $order_info['balance'],
		    		'change_time' => gmtime(),
		    		'change_desc' => "��Ա��ֵ����{$order_info['balance']}",
		    		'change_type' => 3,
		    		'order_id' => $order_id,
		    	);
        	}elseif ($order_info['type'] == 2)
        	{
        		//��д�����¼
		    	$param = array(
		    		'user_id' => $order_info['buyer_id'],
		    		'user_money' => $order_info['balance'],
		    		'change_time' => gmtime(),
		    		'change_desc' => "���̳�ֵ����{$order_info['balance']}",
		    		'change_type' => 5,
		    		'order_id' => $order_id,
		    	);
        	}
	    	add_account_log($param);
        }
    }
}

?>