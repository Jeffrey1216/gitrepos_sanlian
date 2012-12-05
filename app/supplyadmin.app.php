<?php

/*��Ӧ�̺�̨������*/
class SupplyadminApp extends MallbaseApp
{
    /* ��Ӧ�̺�̨���� */
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
            
			header('Location: index.php?app=supplyadmin&act=supplyadmin'); //��ת��֤�������
        } 
    }
    /* ��Ӧ�̺�̨��ҳ */
    function supplyadmin()
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
	    	}else
	    	{
	    		/* �̻���̨��ҳ*/
		        $this->display('supplyadmin.index.html');
	    	}
    	}
    }
    
    /* ��Ӧ�̺�̨-������Ϣ                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 */
    function supply_info()
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

            $goods_mod = &m('goods');
            $goods_num = $goods_mod->get_count_of_store($user['has_store']);
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
        $this->display('supplyadmin.userinfo.html');
        
    }
    
    
    function supply_profile()
    {
    	$this->display('supplyadmin.member.profile.html');
    }
    function supply_box()
    {
    	$this->display('supplyadmin.message.box.html');
    }
    function supply_brandlist()
    {
    	$this->display('supplyadmin.brandlist.html');
    }
    function supply_footer()
    {
    	$this->display('supplyadmin.footer.html');
    }
	function supply_friend()
    {
    	$this->display('supplyadmin.friend.index.html');
    }
	function supply_importtaobao()
    {
    	$this->display('supplyadmin.importtaobao.html');
    }
	function supply_email()
    {
    	$this->display('supplyadmin.member.email.html');
    }
	function supply_header()
    {
    	$this->display('supplyadmin.member.header.html');
    }
	function supply_mobile()
    {
    	$this->display('supplyadmin.member.mobile.html');
    }
	function supply_password()
    {
    	$this->display('supplyadmin.member.password.html');
    }
	function supply_message()
    {
    	$this->display('supplyadmin.message.html');
    }
	function supply_send()
    {
    	$this->display('supplyadmin.message.send.html');
    }
	function supply_view()
    {
    	$this->display('supplyadmin.message.view.html');
    }
	function supply_mycategory()
    {
    	$this->display('supplyadmin.mycategory.index.html');
    }
	function supply_batch()
    {
    	$this->display('supplyadmin.mygoods.batch.html');
    }
	function supply_form()
    {
    	$this->display('supplyadmin.mygoods.form.html');
    }
	function supply_mygoods()
    {
    	$this->display('supplyadmin.mygoods.index.html');
    }
	function supply_mygoodsview()
    {
    	$this->display('supplyadmin.mygoods.view.html');
    }
	function supply_seller()
    {
    	$this->display('supplyadmin.sellerorder.index.html');
    }
	function supply_sellerorder()
    {
    	$this->display('supplyadmin.sellerorder.view.html');
    }
	function checkout()
    {
    	$this->display('supplyadmin.checkout.index.html');
    }
	function checkoutpaila()
    {
    	$this->display('supplyadmin.checkout.view.html');
    }
	function checkoutview()
    {
    	$this->display('supplyadmin.checkout.view.html');
    }

	function showorder() {	
		$this->display('supplyadmin.showorder.html');
	}
	
}

?>