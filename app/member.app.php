<?php

/**
 *    Desc
 *
 *    @author    Garbin
 *    @usage    none
 */

define('ACCOUNT_MANAGER',1); //�ͻ�����
define('KEY_ACCOUNT_MANAGER',2); //��ͻ�����
define('GROUP_ACCOUNT_MANAGER',3); //���ſͻ�����
define('PAGE_NUM', 20);
class MemberApp extends MemberbaseApp
{
	var $_customer_gains_mod;
    var $_feed_enabled = false;
    var $_my_qa_mod;
    var $_widget_mod;
    function __construct()
    {
        $this->MemberApp();
    }
    function MemberApp()
    {
        parent::__construct();
        $ms =& ms();
        $this->_feed_enabled = $ms->feed->feed_enabled();
        $this->assign('feed_enabled', $this->_feed_enabled);
        $this->_member_mod = & m("member");
        $this->_my_qa_mod = & m('goodsqa');
        $this->_widget_mod = &m('widget');
    }
    function index()
    {
        /* ����¶���Ϣ���� */
        $cache_server =& cache_server();
        $cache_server->delete('new_pm_of_user_' . $this->visitor->get('user_id'));
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));

        $user = $this->visitor->get();
        $user_mod =& m('member');
        $info = $user_mod->get_info($user['user_id']);
        $user['money'] = $info['money'];
        $user['credit'] = $info['credit'];
        if($user['portrait'] == '')
        {
        	 $user['portrait'] = SITE_URL.'/themes/mall/default/styles/default/images/90X90logo.jpg';
        }else
        {
        	 $user['portrait'] = IMAGE_URL.$user['portrait'];
        }
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
        /* ������ѣ��������ȷ�ϡ������۶����� */
        $order_mod =& m('order');
        $sql1 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_PENDING . "'";
        $sql2 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_SHIPPED . "'";
        $sql3 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_FINISHED . "' AND evaluation_status = 0";
        $sql4 = "SELECT COUNT(*) FROM {$goodsqa_mod->table} WHERE user_id = '{$user['user_id']}' AND reply_content !='' AND if_new = '1' ";
        $sql5 = "SELECT COUNT(*) FROM " . DB_PREFIX ."groupbuy_log AS log LEFT JOIN {$groupbuy_mod->table} AS gb ON gb.group_id = log.group_id WHERE log.user_id='{$user['user_id']}' AND gb.state = " .GROUP_CANCELED;
        $sql6 = "SELECT COUNT(*) FROM " . DB_PREFIX ."groupbuy_log AS log LEFT JOIN {$groupbuy_mod->table} AS gb ON gb.group_id = log.group_id WHERE log.user_id='{$user['user_id']}' AND gb.state = " .GROUP_FINISHED;
        $buyer_stat = array(
            'pending'  => $order_mod->getOne($sql1),
            'shipped'  => $order_mod->getOne($sql2),
            'finished' => $order_mod->getOne($sql3),
            'my_question' => $goodsqa_mod->getOne($sql4),
            'groupbuy_canceled' => $groupbuy_mod->getOne($sql5),
            'groupbuy_finished' => $groupbuy_mod->getOne($sql6),
        );
        
        $sum = array_sum($buyer_stat);
        $buyer_stat['sum'] = $sum;
        $this->assign('buyer_stat', $buyer_stat);
        /* ȡ��һ���ڵĶ��� */
        //��ǰʱ��
        $time = time();
        //����ʱ���
        $previousWeek = $time - (7 * 24 * 60 * 60);
        $order_info = $order_mod->find(array(
        	'conditions' => "buyer_id = '{$user['user_id']}' AND add_time >=".$previousWeek,
        	'join'	=> 'has_orderextm'
        ));
        //��Ӷ�����Ʒ��Ϣ
        $orderGoods_mod = & m("ordergoods");
        foreach($order_info as $k => $order) {
        	$ordergoods = $orderGoods_mod->find(array('conditions'=>'order_id='.$order['order_id']));
        	$order_info[$k]['order_goods'] = $ordergoods;
        }
        //���÷�ҳ
        $sql = "select count(*) from pa_order where buyer_id='{$user['user_id']}' AND add_time>='{$previousWeek}'";
        $num = $order_mod->getone($sql);
        $page = $this->_get_page(5);
        $page['item_count'] = $num;
        $this->_format_page($page);
        $this->assign('page_info',$page);
 		$this->assign('order_info',$order_info);
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
            //$goods_num = $goods_mod->get_count_of_store($user['has_store']);
            $uploadedfile_mod = &m('uploadedfile');
            $space_num = $uploadedfile_mod->get_file_size($user['has_store']);
            $sgrade = array(
                'grade_name' => $grade['grade_name'],
                'add_time' => empty($store['end_time']) ? 0 : sprintf('%.2f', ($store['end_time'] - gmtime())/86400),
                'goods' => array(
                    //'used' => $goods_num,
                    'total' => $grade['goods_limit']),
                'space' => array(
                    'used' => sprintf("%.2f", floatval($space_num)/(1024 * 1024)),
                    'total' => $grade['space_limit']),
                    );
            $this->assign('sgrade', $sgrade);

        }
        /*��Ѷ��������*/
		$goods_qa = $this->_my_qa_mod->getOne('select count(*) from pa_goods_qa where user_id='.$this->visitor->get(user_id));
		$this->assign('goods_qa',$goods_qa);
		/*���ջ��ԹҼ��еĽ��ջ*/
		$widget_name = 'paila_mall_day';
		$widget = $this->_widget_mod->getRow('select * from pa_widget where widget_name ='."'$widget_name'");
		$data = unserialize($widget['widget_data']);
		$data = array(
			0 => $data['images'][rand(0,1)],
			1 => $data['images'][rand(2,3)],
			2 => $data['images'][rand(4,5)],
		);
		$this->assign('data',$data);
		$store_goods = &m('storegoods');
		$sql = "select sg.gs_id,sg.store_id,g.goods_id,g.goods_name,g.price,g.smimage_url,g.smimage_url from pa_goods g left join pa_store_goods sg on g.goods_id = sg.goods_id where g.if_show = 1 and g.closed = 0 ";	
        $goods_mod = &m('goods');
		/*��������*/
		$is_hot_info = $goods_mod->getAll($sql.' and g.is_hot = 1 and g.status = 1 GROUP BY g.goods_id ORDER BY add_time DESC limit 9');
		foreach($is_hot_info as $k=>$v)
		{
			$is_hot_info[$k]['smimage_url'] = IMAGE_URL.$v['smimage_url'];	
		}
		$is_hot = array(
			0 =>$is_hot_info[rand(0,2)],
			1 =>$is_hot_info[rand(3,5)],
			2 =>$is_hot_info[rand(6,8)],
		);
		$this->assign('is_hot',$is_hot);
		/*��Ʒ�Ƽ�*/
		$is_new_info = $goods_mod->getAll($sql.' and g.is_new = 1 and g.status = 1 GROUP BY g.goods_id ORDER BY add_time DESC limit 9');
    	foreach($is_new_info as $k=>$v)
		{
			$is_new_info[$k]['smimage_url'] = IMAGE_URL.$v['smimage_url'];	
		}
		$is_new = array(
			0 =>$is_new_info[rand(0,2)],
			1 =>$is_new_info[rand(3,5)],
			2 =>$is_new_info[rand(6,8)],
		);
		$this->assign('is_new',$is_new);
		/*��Ʒ�Ƽ�*/
		$is_best_info = $goods_mod->getAll($sql.' and g.is_best = 1 and g.status = 1 GROUP BY g.goods_id ORDER BY add_time DESC limit 9');
  	 	foreach($is_best_info as $k=>$v)
		{
			$is_best_info[$k]['smimage_url'] = IMAGE_URL.$v['smimage_url'];	
		}
		$is_best = array(
			0 =>$is_best_info[rand(0,2)],
			1 =>$is_best_info[rand(3,5)],
			2 =>$is_best_info[rand(6,8)],
		);
		$this->assign('is_best',$is_best);

        /* ��������� */
        if ($user['state'] != '' && $user['state'] == STORE_APPLYING)
        {
            $this->assign('applying', 1);
        }
        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    url('app=member'),
                         LANG::get('overview'));

        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('overview');
        $this->_config_seo('title', Lang::get('member_center'));
        $this->assign('invite_url',SITE_URL.'/index.php?app=member&act=register&invitecode='.base64_encode($user['user_id']));
        $this->display('member.index.html');
    }
    
    /*
     *  ��������
     */
	function detailed()
    {
    	date_default_timezone_set('Asia/Shanghai');
    	$day = empty($_GET['day']) ? 0 : intval($_GET['day']); //7���� 7���ڵĶ���
    	$conditions = "1 = 1";
    	if($day === 0) { //��ѯ����
    		$conditions .= '';
    	} else {
    		$now_time = time();
    		$pro_time = $now_time - ($day * 24 * 60 * 60);
    		$conditions .= " AND account_log.change_time > " . $pro_time . " AND account_log.change_time < " . $now_time;
    	}
    	import('Page.class');
        $count = $this->_get_account($conditions,false,true); //������
        $listRows= 10;        //ÿҳ��ʾ����
        $page=new Page($count,$listRows); //��ʼ������
        $user = $this->_member_mod->getRow('select * from pa_member where user_id ='.$this->visitor->get('user_id'));
        $this->assign('users',$user);
        $type = get_change_type();
        $node_list = $this->_get_account($conditions,$page);
        foreach($node_list as $k=>$v)
        {
        	$node_list[$k]['change_time'] = $v['change_time'];
        	$node_list[$k]['change_type'] = $type[$v['change_type']];
        }
    	$p=$page->show();
		$this->assign('page',$p);
		$this->assign('node_list',$node_list);
        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    url('app=member'),
                         LANG::get('overview'));
       	/* ��ǰ�û�������Ϣ*/
        $this->_get_user_info();
        /* ��ǰ�û����Ĳ˵� */
        $this->assign('day',$day);
        $this->_curitem('detailed');
        $this->_config_seo('title', Lang::get('member_center'));

        $this->display('member.detailed.html');
    }
    
    /**
     * 	��ȡ���ּ�¼
     */
    public function _get_account($conditions, $page,$count=false) {
    	/* ֻȡͨ����˵������̳ǵ���Ʒ */
        $conditions .= " AND account_log.user_id= " . $this->visitor->info['user_id'];
        //$conditions .= " AND `status` = 1 ";
        $account_mod = & m("accountlog");
    	if ($count)
        {
        	$account_mod->get_account_orders(array(
	            'conditions' => $conditions,
	            'count' => $count
	        ));
	        return $account_mod->getCount();
        }else 
        {
        	/* ȡ�ü�¼�б� */
	        $notes_list = $account_mod->get_account_orders(array(
	            'conditions' => $conditions,
	 			'order'	=> 'account_log.change_time DESC',
	            'limit' => $page->firstRow.','.$page->listRows,
	        ));

	        return $notes_list;
        } 
    }

	/*
     *  ��������
     */
	function balance()
    {
        /* ����¶���Ϣ���� */
        $cache_server =& cache_server();
        $cache_server->delete('new_pm_of_user_' . $this->visitor->get('user_id'));
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));

        $user = $this->visitor->get();
        $user_mod =& m('member');
        $info = $user_mod->get_info($user['user_id']);
        $user['portrait'] = portrait($user['user_id'], $info['portrait'], 'middle');
        $user['money'] = $info['money'];
        $user['credit'] = $info['credit'];
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
        /* ������ѣ��������ȷ�ϡ������۶����� */
        $order_mod =& m('order');
        $sql1 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_PENDING . "'";
        $sql2 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_SHIPPED . "'";
        $sql3 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_FINISHED . "' AND evaluation_status = 0";
        $sql4 = "SELECT COUNT(*) FROM {$goodsqa_mod->table} WHERE user_id = '{$user['user_id']}' AND reply_content !='' AND if_new = '1' ";
        $sql5 = "SELECT COUNT(*) FROM " . DB_PREFIX ."groupbuy_log AS log LEFT JOIN {$groupbuy_mod->table} AS gb ON gb.group_id = log.group_id WHERE log.user_id='{$user['user_id']}' AND gb.state = " .GROUP_CANCELED;
        $sql6 = "SELECT COUNT(*) FROM " . DB_PREFIX ."groupbuy_log AS log LEFT JOIN {$groupbuy_mod->table} AS gb ON gb.group_id = log.group_id WHERE log.user_id='{$user['user_id']}' AND gb.state = " .GROUP_FINISHED;
        $buyer_stat = array(
            'pending'  => $order_mod->getOne($sql1),
            'shipped'  => $order_mod->getOne($sql2),
            'finished' => $order_mod->getOne($sql3),
            'my_question' => $goodsqa_mod->getOne($sql4),
            'groupbuy_canceled' => $groupbuy_mod->getOne($sql5),
            'groupbuy_finished' => $groupbuy_mod->getOne($sql6),
        );
        $sum = array_sum($buyer_stat);
        $buyer_stat['sum'] = $sum;
        $this->assign('buyer_stat', $buyer_stat);
        /* ȡ��һ���ڵĶ��� */
        //��ǰʱ��
        $time = time();
        //����ʱ���
        $previousWeek = $time - (7 * 24 * 60 * 60);
        $order_info = $order_mod->find(array(
        	'conditions' => "buyer_id = '{$user['user_id']}' AND add_time >=".$previousWeek,
        	'join'	=> 'has_orderextm'
        ));
        //��Ӷ�����Ʒ��Ϣ
        $orderGoods_mod = & m("ordergoods");
        foreach($order_info as $k => $order) {
        	$ordergoods = $orderGoods_mod->find(array('conditions'=>'order_id='.$order['order_id']));
        	$order_info[$k]['order_goods'] = $ordergoods;
        }
        //���÷�ҳ
        $sql = "select count(*) from pa_order where buyer_id='{$user['user_id']}' AND add_time>='{$previousWeek}'";
        $num = $order_mod->getone($sql);
        $page = $this->_get_page(5);
        $page['item_count'] = $num;
        $this->_format_page($page);
        $this->assign('page_info',$page);
 		$this->assign('order_info',$order_info);
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

        /* ��������� */
        if ($user['state'] != '' && $user['state'] == STORE_APPLYING)
        {
            $this->assign('applying', 1);
        }
        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    url('app=member'),
                         LANG::get('overview'));

        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('overview');
        $this->_config_seo('title', Lang::get('member_center'));
        $this->display('member.balance.html');
    }

    /**
     *    ע��һ�����û�
     *
     *    @author    Garbin
     *    @return    void
     */
    function register()
    {
        if ($this->visitor->has_login)
        {
            $this->show_warning('has_login');

            return;
        }
        if (!IS_POST)
        {
            if (!empty($_GET['ret_url']))
            {
                $ret_url = trim($_GET['ret_url']);
            }
            else
            {
                if (isset($_SERVER['HTTP_REFERER']))
                {
                    $ret_url = $_SERVER['HTTP_REFERER'];
                }
                else
                {
                    $ret_url = SITE_URL . '/index.php';
                }
            }
            $this->assign('ret_url', rawurlencode($ret_url));
            $this->_curlocal(LANG::get('user_register'));
            $this->_config_seo('title', Lang::get('user_register') . ' - ' . Conf::get('site_title'));

            if (Conf::get('captcha_status.register'))
            {
                $this->assign('captcha', 1);
            }
            //���������˹���
			if ($_GET['invitecode']){
				$invitecode = intval(base64_decode($_GET['invitecode']));
				if (is_int($invitecode)){
					$member =& m('member');
					$inviteinfo = $member->get($invitecode);
					$this->assign('inviteinfo',$inviteinfo);
				}
			}
			 //�ײ�����
			$article_mod = & m("article");
        	$acategory_mod = & m("acategory");
	        $ACC = $acategory_mod->get_ACC();
	        $about = $article_mod->find(array('conditions' => 'cate_id='.$ACC[ACC_ABOUT],'fields' => 'title','order' => 'sort_order'));
	        $this->assign('about',$about);
            /* ����jQuery�ı���֤��� */
            $this->import_resource('jquery.plugins/jquery.validate.js');
            $this->display('member.register.html');
        }
        else
        {
            if (!$_POST['agree'])
            {
                $this->show_warning('agree_first');
                return;
            }
            if (Conf::get('captcha_status.register') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
            {
                $this->show_warning('captcha_failed');
                return;
            }
            if ($_POST['password'] != $_POST['password_confirm'])
            {
                /* ������������벻һ�� */
                $this->show_warning('inconsistent_password');
                return;
            }
            
            /* ע�Ტ��½ */
            $user_name = trim($_POST['user_name']);
            $password  = $_POST['password'];
            $email     = trim($_POST['email']);
            $mobile     = trim($_POST['mobile']);
            $smsverify     = trim($_POST['sms_verify']);
            $tpwd = $_POST['tpwd'];
            $tpwd2 = $_POST['tpwd2'];
            $passlen = strlen($password);
            $user_name_len = strlen($user_name);
            if ($user_name_len < 3 || $user_name_len > 25)
            {
                $this->show_warning('user_name_length_error');

                return;
            }
            if ($passlen < 6 || $passlen > 20)
            {
                $this->show_warning('password_length_error');

                return;
            }
            if ($email)
            {
	            if (!is_email($email))
	            {
	                $this->show_warning('email_error');
	
	                return;
	            }
            }else
            {
            	$email = $mobile.'@qq.com';
            }
        	if (!is_mobile($mobile))
            {
                $this->show_warning('mobile_error');

                return;
            }
            
            if ($_SESSION['smsverifydata']['verify'] != $smsverify || $_SESSION['smsverifydata']['mobile'] != $mobile)
            {
            	$this->show_warning('smsverify_error');

                return;
            }
            $time = time() - $_SESSION['smsverifydata']['dateline'];
        	if ($time>3600)
            {
            	$this->show_warning('smsverify_invalid');

                return;
            }
        
        	if ($tpwd != $tpwd2)
            {
                $this->show_warning('���������֧�����벻һ��');
                return;
            }
            if (!is_tpwd($tpwd))
            {
            	$this->show_warning('֧���������Ϊ6λ����');

                return;
            }
            
            $ms =& ms(); //�����û�����
            //����������
            if ($_POST['invite_id'])
            {
	            if (!is_mobile($_POST['invite_id']))
	            {
	                $this->show_warning('�������ֻ����벻��ȷ');
	
	                return;
	            }
	            $info = $ms->user->get($_POST['invite_id'],false,true);
				if (!$info) {
					$this->show_warning('������������˲�����');
	
	                return;
				}
				$data['invite_id'] = $info['user_id'];
            }
            $data['mobile'] = $mobile;
            $data['trader_password'] = $ms->user->getMd5TraderPassword($tpwd);
            $mobilearea = &  m('mobile');  //ʵ�����ֻ������
            $data['mobilearea'] = $mobilearea->get_areaname_by_mobile($mobile);
            $user_id = $ms->user->register($user_name, $password, $email,$data);

            if (!$user_id)
            {
                $this->show_warning($ms->user->get_error());
				
                return;
            }
            
            //ע���ͻ���
            //$this->sendCredit($user_id, 5);
            //������û�����������ע��--��ע��ɹ�---����һ�λ�齱�����������
//            if($inviteinfo)
//            {
//            	$infos['uid'] = $inviteinfo['user_id'];
//            	$infos['username'] = $inviteinfo['user_name'];
//            	$infos['mobile'] = $inviteinfo['mobile'];
//            	$infos['num'] = 1;
//            	$infos['action'] = 'invite';
//            	$infos['type'] = 'get';
//            	$infos['add_time'] = time();
//            	$infos['act_id'] = PAISONG;
//            	$infos['remark'] = '�����û���'.$user_name.'���һ�δ����ͻ�齱����';
//            	$awardcount = &m('activityawardcount'); //ʵ������齱ͳ�Ʊ�
//        		$awardnum  = &m('activityawardnum');	//��Ա�齱�����
//        		$awardnum->edit('uid='.$inviteinfo['user_id'].' AND act_id = '.PAISONG,'num = num + 1'); //����һ�γ齱����
//        		$awardcount->add($infos);
//            }
            $this->_hook('after_register', array('user_id' => $user_id));
            //��¼
            $this->_do_login($user_id);
            
            /* ͬ����½�ⲿϵͳ */
            $synlogin = $ms->user->synlogin($user_id);

            #TODO ���ܻ��ᷢ�ͻ�ӭ�ʼ�

            $this->show_message(Lang::get('register_successed') . $synlogin,
                'back_before_register', rawurldecode($_POST['ret_url']),
                'enter_member_center', 'index.php?app=member',
                'apply_store', 'index.php?app=apply'
            );
        }
    }
	

    /**
     *    ����û��Ƿ����
     *
     *    @author    Garbin
     *    @return    void
     */
    function check_user()
    {
        $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
        if (!$user_name)
        {
            echo ecm_json_encode(false);

            return;
        }
        $ms =& ms();

        echo ecm_json_encode($ms->user->check_username($user_name));
    }
    function check_manager_exit(){
    	$name = empty($_GET['user_name']) ? '' : trim($_GET['user_name']);
    	$cust_manager_mod =& m('customermanager');
    	$d2=$cust_manager_mod->get("user_name ='".$name."'");
    	if(isset($d2['user_id'])){
    		echo  'false';
    		return;
    	}
    	echo true;
    	return ;
    }
    function check_user_exit(){
    	$name = empty($_GET['user_name']) ? '' : trim($_GET['user_name']);
    	$member_mod=& m('member');
    	$d1=$member_mod->get("user_name ='".$name."'");
    	$cust_manager_mod =& m('customermanager');
    	$d2=$cust_manager_mod->get("user_name ='".$name."'");
    	if(isset($d1['user_id'])){
    		if(!isset($d2['user_id'])){
    			echo 'true';
    			return;
    		}
    	}else{
    		echo 'false';
    		return;
    	}    	
    }
	function check_user_valid(){
        $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
        if (!$user_name)
        {
            echo ecm_json_encode(false);

            return;
        }
        $ms =& ms();
        echo ecm_json_encode($ms->user->check_user_valid($user_name));
	}
    /**
     *    �޸Ļ�����Ϣ
     *
     *    @author    Hyber
     *    @usage    none
     */
    function profile(){

        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('basic_information'));

            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('my_profile');

            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('new_basic_information');
			/* ��ǰ�û�������Ϣ*/
            $this->_get_user_info();
            $ms =& ms();    //�����û�ϵͳ
            $edit_avatar = $ms->user->set_avatar($this->visitor->get('user_id')); //��ȡͷ�����÷�ʽ

            $model_user =& m('member');
            $profile    = $model_user->get_info(intval($user_id));
            if($profile['portrait']=="")
            {
            	$profile['portrait'] = SITE_URL."/themes/mall/default/styles/default/images/120X120logo.jpg";
            }else 
            {
            	$profile['portrait'] = IMAGE_URL.$profile['portrait'];
            }
            $this->assign('profile',$profile);
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->assign('edit_avatar', $edit_avatar);
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_profile'));
            /* edit by lihuoliang 2011/08/24 (�޸ĺ�̨��ʽ��ȡģ��)*/
            if ($_GET['from']=='storeadmin')
            {
            	$this->display('storeadmin.member.profile.html');
            }else
            {
            	$this->display('member.profile.html');
            }
        }
        else
        {
            $data = array(
                'real_name' => $_POST['real_name'],
                'gender'    => $_POST['gender'],
                'birthday'  => $_POST['birthday'],
                'im_msn'    => $_POST['im_msn'],
                'im_qq'     => $_POST['im_qq'],
            );

            if (!empty($_FILES['portrait']))
            {
                $portrait = $this->_upload_portrait($user_id);
                if ($portrait === false)
                {
                    return;
                }
                $data['portrait'] = $portrait;
            }

            $model_user =& m('member');
            $model_user->edit($user_id , $data);
            if ($model_user->has_error())
            {
            	if ($_POST['from'] == 'storeadmin')
            	{
            		$this->show__storeadmin_warning($model_user->get_error());
            	}else{
            		$this->show_warning($model_user->get_error());
            	}
                return;
            }
        	if ($_POST['from'] == 'storeadmin')
            {
            	$this->show_storeadmin_message('edit_profile_successed');
            }else{
            	$this->show_message('edit_profile_successed');
            }
        }
    }
    /**
     *    �޸�����
     *
     *    @author    Hyber
     *    @usage    none
     */
    function password(){
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('edit_password'));

            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('loginpassword');
            /* ��ǰ�û�������Ϣ*/
            $this->_get_user_info();

            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('edit_password');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_password'));
        	/* edit by lihuoliang 2011/08/24 (�޸ĺ�̨��ʽ��ȡģ��)*/
            if ($_GET['from']=='storeadmin')
            {
            	$this->display('storeadmin.member.password.html');
            }else
            {
            	$this->display('member.password.html');
            }
        }
        else
        {
            /* �����������������ͬ */
            $orig_password      = $_POST['orig_password'];
            $new_password       = $_POST['new_password'];
            $confirm_password   = $_POST['confirm_password'];
            if ($new_password != $confirm_password)
            {
            	if ($_POST['from'] == 'storeadmin')
            	{
            		$this->show_storeadmin_warning('twice_pass_not_match');
            	}else{
            		$this->show_warning('twice_pass_not_match');
            	}
                return;
            }
            if (!$new_password)
            {
            	if ($_POST['from'] == 'storeadmin')
            	{
            		$this->show_storeadmin_warning('no_new_pass');
            	}else{
            		$this->show_warning('no_new_pass');
            	}
                return;
            }
            $passlen = strlen($new_password);
            if ($passlen < 6 || $passlen > 20)
            {
            	if ($_POST['from'] == 'storeadmin')
            	{
            		$this->show_storeadmin_warning('password_length_error');
            	}else{
            		$this->show_warning('password_length_error');
            	}

                return;
            }

            /* �޸����� */
            $ms =& ms();    //�����û�ϵͳ
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'password'  => $new_password
            ));
            if (!$result)
            {
                /* �޸Ĳ��ɹ�����ʾԭ�� */
            	if ($_POST['from'] == 'storeadmin')
            	{
            		$this->show_storeadmin_warning($ms->user->get_error());
            	}else{
            		$this->show_warning($ms->user->get_error());
            	}

                return;
            }
        	if ($_POST['from'] == 'storeadmin')
            {
            	$this->show_storeadmin_message('edit_password_successed');
            }else{
            	$this->show_message('edit_password_successed');
            }
        }
    }
     /**
     *  ֧������
     *
     *    @author    Hyber
     *    @usage    none
     */
    function passwordPayment(){
        $user_id = $this->visitor->get('user_id');
        $member_mod = &m('member');
        if (!IS_POST)
        {
            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('edit_passwordpayment'));

            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('paypassword');
            /* ��ǰ�û�������Ϣ*/
            $this->_get_user_info();

            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('edit_passwordpayment');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_password'));
            $member_info = $member_mod->get($user_id);
            $this->assign('member',$member_info);
        	/* edit by lihuoliang 2011/08/24 (�޸ĺ�̨��ʽ��ȡģ��)*/
            if ($_GET['from']=='storeadmin')
            {
            	$this->display('storeadmin.member.password.html');
            }else
            {
            	$this->display('member.passwordpayment.html');
            }
        }
        else
        {
        	$verify = empty($_POST['smsverify']) ? false : trim($_POST['smsverify']);
			$new_password = empty($_POST['new_password']) ? false : trim($_POST['new_password']);
			$re_passowrd = empty($_POST['re_password']) ? false : trim($_POST['re_password']);
			if (!$verify)
			{
				$this->show_warning('��û�������ֻ���֤��!');
				return;
			}
			if (!$new_password)
			{
				$this->show_warning('�������µĽ�������!');
				return;
			}
			if ($new_password != $re_passowrd)
			{
				$this->show_warning('������������벻һ��!');
				return;
			}
			if ($verify != $_SESSION['smsverifydata']['verify'])
			{
				$this->show_warning('�ֻ���֤�����벻��ȷ!');
				return;
			}
			//д����Ϣ
			$userObj = & ms();
			$userObj->user->updateTraderAuth($user_id, $new_password, $re_passowrd);
			$this->show_message('�޸�֧������ɹ�!.');
        }	
    }
    /**
     *    �޸ĵ�������
     *
     *    @author    Hyber
     *    @usage    none
     */
    function email(){
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('edit_email'));

            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('my_profile');
            /* ��ǰ�û�������Ϣ*/
            $this->_get_user_info();

            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('new_basic_information');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_email'));
            
        	if ($_GET['from']=='storeadmin')
            {
            	$this->display('storeadmin.member.email.html');
            }else
            {
            	$this->display('member.email.html');
            }
        }
        else
        {
            $orig_password  = $_POST['orig_password'];
            $email          = isset($_POST['email']) ? trim($_POST['email']) : '';
            if (!$email)
            {
	            if ($_POST['from'] == 'storeadmin')
	            {
	            	$this->show_storeadmin_warning('email_required');
	            }else{
	            	$this->show_warning('email_required');
	            }
	            
                return;
            }
            if (!is_email($email))
            {
	            if ($_POST['from'] == 'storeadmin')
	            {
	            	$this->show_storeadmin_warning('email_error');
	            }else{
	            	$this->show_warning('email_error');
	            }
	            
                return;
            }

            $ms =& ms();    //�����û�ϵͳ
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'email' => $email
            ));
            if (!$result)
            {
	            if ($_POST['from'] == 'storeadmin')
	            {
	            	$this->show_storeadmin_warning($ms->user->get_error());
	            }else{
	            	$this->show_warning($ms->user->get_error());
	            }
	            
                return;
            }
        	if ($_POST['from'] == 'storeadmin')
            {
            	$this->show_storeadmin_message('edit_email_successed');
            }else{
            	$this->show_message('edit_email_successed');
            }
        }
    }
    
    /**
     *    �޸��ֻ���
     *
     *    @author   lihuoliang
     *    @usage    none
     */
    function mobile(){
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('edit_mobile'));

            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('phonepassword');
            /* ��ǰ�û�������Ϣ*/
            $this->_get_user_info();

            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('edit_mobile');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_mobile'));
        	/* edit by lihuoliang 2011/08/24 (�޸ĺ�̨��ʽ��ȡģ��)*/
            if ($_GET['from']=='storeadmin')
            {
            	$this->display('storeadmin.member.mobile.html');
            }else
            {
            	$this->display('member.mobile.html');
            }
        }
        else
        {
            $orig_password  = $_POST['orig_password'];
            $new_mobile     = $_POST['new_mobile'];
            $smsverify     = $_POST['sms_verify'];
            
        	if (!is_mobile($new_mobile))
            {
            	if ($_POST['from'] == 'storeadmin')
            	{
            		$this->show_storeadmin_warning('mobile_error');
            	}else{
            		$this->show_warning('mobile_error');
            	}
            	
                return;
            }
            if ($_SESSION['smsverifydata']['verify'] != $smsverify || $_SESSION['smsverifydata']['mobile'] != $new_mobile)
            {
            	if ($_POST['from'] == 'storeadmin')
            	{
            		$this->show_storeadmin_warning('smsverify_error');
            	}else{
            		$this->show_warning('smsverify_error');
            	}

                return;
            }
            $time = time() - $_SESSION['smsverifydata']['dateline'];
        	if ($time>3600)
            {
            	if ($_POST['from'] == 'storeadmin')
            	{
            		$this->show_storeadmin_warning('smsverify_invalid');
            	}else{
            		$this->show_warning('smsverify_invalid');
            	}
            	
                return;
            }

            /* �޸��ֻ��� */
            $ms =& ms();    //�����û�ϵͳ
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'mobile'  => $new_mobile
            ));
            if (!$result)
            {
                /* �޸Ĳ��ɹ�����ʾԭ�� */
            	if ($_POST['from'] == 'storeadmin')
            	{
            		$this->show_storeadmin_warning($ms->user->get_error());
            	}else{
            		$this->show_warning($ms->user->get_error());
            	}

                return;
            }
        	if ($_POST['from'] == 'storeadmin')
            {
            	$this->show_storeadmin_message('edit_mobile_successed');
            }else{
            	$this->show_message('edit_mobile_successed');
            }
        }
    }

    /**
     * Feed����
     *
     * @author Garbin
     * @param
     * @return void
     **/
    function feed_settings()
    {
        if (!$this->_feed_enabled)
        {
            $this->show_warning('feed_disabled');
            return;
        }
        if (!IS_POST)
        {
            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('feed_settings'));

            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('my_profile');

            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('feed_settings');
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('feed_settings'));

            $user_feed_config = $this->visitor->get('feed_config');
            $default_feed_config = Conf::get('default_feed_config');
            $feed_config = !$user_feed_config ? $default_feed_config : unserialize($user_feed_config);

            $buyer_feed_items = array(
                'store_created' => Lang::get('feed_store_created.name'),
                'order_created' => Lang::get('feed_order_created.name'),
                'goods_collected' => Lang::get('feed_goods_collected.name'),
                'store_collected' => Lang::get('feed_store_collected.name'),
                'goods_evaluated' => Lang::get('feed_goods_evaluated.name'),
                'groupbuy_joined' => Lang::get('feed_groupbuy_joined.name')
            );
            $seller_feed_items = array(
                'goods_created' => Lang::get('feed_goods_created.name'),
                'groupbuy_created' => Lang::get('feed_groupbuy_created.name'),
            );
            $feed_items = $buyer_feed_items;
            if ($this->visitor->get('manage_store'))
            {
                $feed_items = array_merge($feed_items, $seller_feed_items);
            }
            $this->assign('feed_items', $feed_items);
            $this->assign('feed_config', $feed_config);
            $this->display('member.feed_settings.html');
        }
        else
        {
            $feed_settings = serialize($_POST['feed_config']);
            $m_member = &m('member');
            $m_member->edit($this->visitor->get('user_id'), array(
                'feed_config' => $feed_settings,
            ));
            $this->show_message('feed_settings_successfully');
        }
    }

     /**
     *    �����˵�
     *
     *    @author    Hyber
     *    @return    void
     */
    function _get_member_submenu()
    {
        $submenus =  array(
            array(
                'name'  => 'basic_information',
                'url'   => 'index.php?app=member&amp;act=profile',
            ),
            array(
                'name'  => 'edit_email',
                'url'   => 'index.php?app=member&amp;act=email',
            ),
            );
        if ($this->_feed_enabled)
        {
            $submenus[] = array(
                'name'  => 'feed_settings',
                'url'   => 'index.php?app=member&amp;act=feed_settings',
            );
        }

        return $submenus;
    }

    /**
     * �ϴ�ͷ��
     *
     * @param int $user_id
     * @return mix false��ʾ�ϴ�ʧ��,�մ���ʾû���ϴ�,string��ʾ�ϴ��ļ���ַ
     */
    function _upload_portrait($user_id)
    {
        $file = $_FILES['portrait'];
        if ($file['error'] != UPLOAD_ERR_OK)
        {
            return '';
        }
        import('SimpleImage.class');
        import('uploader.lib');
        $uploader = new Uploader();
        $simpleimage = new SimpleImage();
        
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->addFile($file);
        if ($uploader->file_info() === false)
        {
            $this->show_warning($uploader->get_error(), 'go_back', 'index.php?app=member&amp;act=profile');
            return false;
        }
        $uploader->root_dir(ROOT_PATH);
        $file_path = $uploader->save('data/files/mall/portrait/' . ceil($user_id / 500), $user_id);
        
        $small = dirname($file_path) . '/' . basename($file_path);
        $simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $small, 120, 120);
        return $small;
    }
    
    /**
     *    AJAX���Ͷ�����֤��
     *
     *    @author    lihuoliang
     *    @return    string
     */
    function send_sms_verify()
    {
    	//��ȡ�ֻ�����
        $mobile = empty($_POST['mobile']) ? null : trim($_POST['mobile']);

        if (!$mobile)
        {
            echo 1;  //�ֻ�����Ϊ��
        }else
        {
        	if (is_mobile($mobile))
        	{
        		$smslog =&  m('smslog'); 
	        	$todaysmscount = $smslog->get_today_smscount($mobile); //������ŵķ�������
	        	
				if ($todaysmscount>=5)
				{
					echo 6;
					return;
        		}
        		$ms =& ms();    //�����û�ϵͳ
        		$info = $ms->user->get($mobile,false,true);//�ж��ֻ������Ƿ����
        		if ($info)
        		{
        			echo 3;
        		}else 
        		{
        			//��������������php���л�����ʱδ���ÿ���soap��չ��������ʱ��ʹ��webservice��ʽ
        			import('class.smswebservice');    //������ŷ�����
        			$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
        			$verify = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);//��֤��
        			$verifytype = $_GET['verifytype']?$_GET['verifytype']:'register_verify'; //������֤������
        			if ($verifytype=='modifymobile')
        			{
        				$smscontent = str_replace('{verify}',$verify,Lang::get('smscontent.modifymobile_verify'));
        			}else 
        			{
        				$smscontent = str_replace('{verify}',$verify,Lang::get('smscontent.register_verify'));
        			}
					//echo "OK";
        			$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ�����֤�����
        			//���ŷ��ͳɹ�
        			if ($result == 0) 
        			{
        				//����֤��д��SESSION
        				$time = time();
        				$_SESSION['smsverifydata']['mobile'] =  $mobile;
        				$_SESSION['smsverifydata']['verify'] =  $verify;
        				$_SESSION['smsverifydata']['dateline'] =  $time;
        				//ִ�ж�����־д�����
        				$smsdata['mobile'] = $mobile;
        				$smsdata['smscontent'] = $smscontent;
        				$smsdata['type'] = $verifytype; //ע����֤����
        				$smsdata['sendtime'] = $time;
        				
        				$smslog->add($smsdata);
        				echo 4;
        			}else
        			{
	        			echo 5; //���ŷ���ʧ��
        			}
        		}
        	}else 
        	{
        		echo 2; //����ȷ���ֻ�����
        	}
        }
        return;
    }
    
    /**
     *    ����ֻ��ŷ����
     *
     *    @author    lihuoliang
     *    @return    void
     */
    function check_mobile()
    {
    	if ($_GET['type']==1)
    	{
    		$mobile = empty($_GET['invite_id']) ? null : trim($_GET['invite_id']);
	        if (!$mobile)
	        {
	            echo ecm_json_encode(false);
	
	            return;
	        }
	        $ms =& ms();
	        $flag = false;
			$info = $ms->user->get($mobile,false,true);
			if ($info) {
				$flag = true;
			}
	        echo ecm_json_encode($flag);
    	}else
    	{
    		$mobile = empty($_GET['mobile']) ? null : trim($_GET['mobile']);
	        if (!$mobile)
	        {
	            echo ecm_json_encode(false);
	
	            return;
	        }
	        $ms =& ms();
	        $flag = true;
			$info = $ms->user->get($mobile,false,true);
			if ($info) {
				$flag = false;
			}
	        echo ecm_json_encode($flag);
    	}
    }
    
    public function channel() {
    	$channel_user_mod = & m('channeluser');
    	$channel_fee_mod = & m('channelfee');
    	$store_mod = & m('store');
    	$user_id = $this->visitor->get('user_id');
    	//����û��Ƿ������
    	if(!$this->checkIsChannel()) { //δ��
    		if(!IS_POST) {
    			/* ��ǰλ�� */
	            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
	                             LANG::get('basic_information'));
	
	            /* ��ǰ�û����Ĳ˵� */
	            $this->_curitem('my_profile');
	
	            /* ��ǰ�����Ӳ˵� */
	            $this->_curmenu('basic_information');
    			$this->display("member.channelForm.html");
    		} else {
    			$uname = empty($_POST['uname']) ? '' : trim($_POST['uname']);
    			$password = empty($_POST['password']) ? '' : md5(trim($_POST['password']));
    			$sn = empty($_POST['sn']) ? '' : trim($_POST['sn']);
    			$channel_user_info = $channel_user_mod->get(array('conditions' => ' channel_name="'.$uname.'" AND password="'.$password.'" AND sn="'.$sn.'"'));
    			if(!$channel_user_info) {
    				$this->show_warning("δ�ҵ�������Ϣ, ����������Ϣ���ԣ�");
    			} else {
    				if($channel_user_info['level'] != 3) {
    					$channel_fee_info = $channel_fee_mod->get(array('conditions' => " level=" . $channel_user_info['level'] . " AND area_id=" . $channel_user_info['area_id']));
	    				if(!$channel_fee_info) {
	    					$this->show_warning("������Ϣ�������! ������,���޷����,����ϵ�ͷ�!");
	    					return;
	    				}
	    				$user_info = $this->_member_mod->get($user_id);
	    				if($user_info['is_bind_channel'] == 1) {
	    					$this->show_warning("�Ѱ�����,�����ظ���!");
	    					return;
	    				}
	    				//д��channle_user �û���Ϣ
	    				$channel_user_mod->edit($channel_user_info['channel_id'],array('sid' => $user_id));
	    				$this->_member_mod->edit($user_id,array('channel_id' => $channel_user_info['channel_id'] , 'is_bind_channel' => 1));
	    				
	    				$this->show_message('�ɹ�������������');	
    				} else {
    					$channel_fee_info = $channel_fee_mod->get(array('conditions' => " level=" . $channel_user_info['level'] . " AND area_id=" . $channel_user_info['area_id']));
	    				if(!$channel_fee_info) {
	    					$this->show_warning("������Ϣ�������! ������,���޷����,����ϵ�ͷ�!");
	    					return;
	    				}
    					$user_info = $this->_member_mod->get($user_id);
	    				if($user_info['is_bind_channel'] == 1) {
	    					$this->show_warning("�Ѱ�����,�����ظ���!");
	    					return;
	    				}
	    				$store_info = $store_mod->get($user_id);
	    				if(!$store_info) {
	    					$this->show_warning("�˻�Ա�������̻�,���������Ϊ�̻�!");
	    					return;
	    				}
	    				if($store_info['is_bind_channel'] == 1) {
	    					$this->show_warning("�Ѱ�����,�����ظ���!");
	    					return;
	    				}
	    				//д��channle_user �û���Ϣ
	    				$channel_user_mod->edit($channel_user_info['channel_id'],array('sid' => $this->visitor->info['store_id']));
	    				$store_mod->edit($this->visitor->info['store_id'],array('channel_id' => $channel_user_info['channel_id'] , 'is_bind_channel' => 1));
	    				$this->_member_mod->edit($user_id,array('channel_id' => $channel_user_info['channel_id'] , 'is_bind_channel' => 1));
	    				
	    				$this->show_message('�ɹ�������������');	
	    			}
    			}
    		}
    	} else {
    		/* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('basic_information'));

            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('my_profile');

            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('basic_information');
    		/* �����˻���Ϣ */
	    	$info = $this->get($user_id,false,false,true);
	    	
	    	$pms = $this->get_list($user_id, 100, 'newpm'); 
	    	$channelfee =& m('channelfee');    //���˷��ò�ѯ
	     	$fee = $channelfee->get(" level=" . $info['level'] . " and area_id = " . $info['area_id'] ."");
	     	//var_dump($pms['count']);exit;
	    	$this->assign('count', $pms['count']); 		    	   
	        $this->assign('ip', $_SERVER["REMOTE_ADDR"]);   
	        $this->assign('user', $info);  
	        $this->assign('fee', $fee); 
	        /* �������� */
	        $this->display("member.showChannel.html");
    	}
    }
    
	function get($flag, $is_name = false,$is_mobile = false,$is_sid = false)
    {
        if ($is_name) {
            $conditions = "channel_name='{$flag}'";
        }elseif ($is_mobile) {
        	$conditions = "mobile='{$flag}'";
        } elseif ($is_sid) {
        	$conditions = "sid='{$flag}'";
        } else {
            $conditions = intval($flag);
        }
        return $this->_local_get($conditions);
    }
    
	function _local_get($conditions)
    {
        $supply_user =& m('channeluser');
        return $supply_user->get($conditions);
    }
    
	/**
     *  �鿴�ǵ�ǰ�û��Ƿ������
     */
    public function checkIsChannel() {
    	$user_id = $this->visitor->get('user_id');
    	$channel_mod = & m("channeluser");
    	$user_info = $this->_member_mod->get(array('conditions' => 'user_id='.$user_id));
    	if($user_info['is_bind_channel'] == 1) {
	    	if($user_info['channel_id']) {
				$channel_id = $user_info['channel_id'];
				$channel_info = $channel_mod->get($channel_id);
				if($channel_info['sid'] == $user_id) {
					return true;
				} else {
					return false;
				}
	    	} else {
	    		return false;
	    	}
    	} else {
    		return false;
    	}
    }
    
	function get_list($user_id, $page, $folder = 'privatepm')
    {
        $limit = $page['limit'];
        $condition = '';
       // var_dump($folder);
        switch ($folder)
        {
            case 'privatepm':
                $condition = '((from_id = ' . $user_id . ' AND status IN (2,3)) OR (to_id = ' . $user_id . ' AND status IN (1,3)) AND from_id > 0)';
            break;
            case 'systempm':
                $condition = 'from_id = ' . MSG_SYSTEM . ' AND to_id = ' . $user_id;
            break;
            case 'announcepm':
                $condition = 'from_id = 0 AND to_id = 0';
            break;
            default:
                $condition = ' to_id = ' . $user_id . ' and status=0';
            break;
        }
        $model_message =& m('channelmessage');
        $messages = $model_message->find(array(
            'fields'        =>'this.*',
            'conditions'    =>  $condition,
            'count'         => true,
            'limit'         => $limit,
            'order'         => 'msg_id desc',
        ));

        return array(
            'count' => $model_message->getCount(),
            'data' => $messages
        );
    }
    
    //�Ź�Ա������Ϣ
    function manager()
    { 
    	$user_id = $this->visitor->get('user_id'); //��ǰ�û�ID
    	$this->_curitem('manager');
    	/* ��ǰ�û�������Ϣ*/
        $this->_get_user_info();
    	$customerManager_mod = & m('customermanager');
    	$customerManager_info = $customerManager_mod->get('user_id = ' . $user_id);    	
	    if(!$customerManager_info)  //���ǿͻ�����
    	{
    		$this->display("member.manager.html");	
    	} else {
    		$info = $customerManager_mod->getRow("select * from pa_customer_manager cm left join pa_customer_level cl on 
    		cm.customer_level = cl.level_id where user_id = " . $user_id);
    		$info['member_expense_yield_percent'] = $info['member_expense_yield'] * 100;
    		$info['benefit_ratio'] = $info['benefit_ratio'] * 100;
    		$this->assign('info', $info);
    		$this->display('member.managerinfo.html');
    	}
    }
    
    //�����ҽ���
    function credit()
    {
    	date_default_timezone_set("Asia/Shanghai");
    	$this->_curitem('overview');
    	$user_id = $this->visitor->get('user_id'); //��ǰ�û�ID
    	$page_num = 20;
    	$page = $this->_get_page($page_num);
    	$creditGoods_mod = & m(creditgoods);
    	if(!IS_POST){
    	$count = $creditGoods_mod->getOne("select count(*) from pa_credit_goods where user_id= " .$user_id);
    	$page['item_count'] = $count;
    	$credit_info = $creditGoods_mod->getAll("select * from pa_credit_goods where user_id = " .$user_id . " order by time desc limit " . $page['limit']);
    	$this->_format_page($page);
    	foreach ($credit_info as $k => $v){
    		$credit_info[$k]['cycle']=$v['cycle']/60/60/24;
    		$credit_info[$k]['time']=date('Y-m-d H:i',$v['time']);
    	}
    	$this->assign('page_info', $page);
    	$this->assign('credit_info',$credit_info);
    	$this->display("credit.index.html");
    	}else{
    		$this->display("credit.add.html");
    	}
    }
    
	//�����ҽ��׷���
	function add(){
		$this->_curitem('overview');
		$creditGoods_mod = & m('creditgoods');
		$member_mod = & m('member');
		$user_id=$this->visitor->get('user_id');
		$user_name=$this->visitor->get('user_name');
		$credit_num = floatval($_POST['credit_num']);
		$info = trim($_POST['info']);
		$price = floatval($_POST['price']);
		$cnum = $member_mod->getOne("select m.credit from pa_member m where m.user_id = " .$user_id);
		$num=$_POST['opt'];//��ȡ������������
		$cycle=$num * 24 * 60 * 60;//���㽻����������
    	if (IS_POST){
    			if($credit_num > $cnum)
    			{
    				$this->show_warning('���ʺŵ���������Ŀ���㵱ǰ������Ŀ��');
    				return;
    			}
    			if(!$credit_num)
    			{
    				$this->show_warning('����δ����Ҫ���۵�PL����Ŀ���뷵����������');
    				return;
    			}
    			if(!$price)
    			{
    				$this->show_warning('����δ������۴�PL�ҵļ۸��뷵����������');
    				return;
    			}
    			if(!$info)
    			{
    				$this->show_warning('����δ���뷢����Ϣ���뷵����������');
    				return;
    			}
    			$data=array();
    			$data['user_id']=$user_id;
    			$data['user_name']=$user_name;
    			$data['credit_num']=$credit_num;
    			$data['type']=intval($_POST['type']);
    			$data['price']=$price;
    			$data['info']=$info;
    			$data['time']=time();
    			$data['cycle']=$cycle;
    			$credit =$creditGoods_mod->add($data);
    			header("Location:index.php?app=member&act=credit");
    	} else {
    		$this->display("credit.add.html");
    	}
		
	}
	
	//������Ϣ�޸�
	function credit_edit()
	{
		//echo 123;
		$this->_curitem('overview');
		$credit_goods_mod = & m('creditgoods');
		$id=$_GET['id'];
		
		if(!IS_POST)
		{
			$credit_goods_info = $credit_goods_mod->getRow("select * from pa_credit_goods where id =  " .$id);
			$this->assign('credit',$credit_goods_info);
			$this->display('credit.edit.html');
		}else{
			$cycle=intval($_POST['cycle']);
			$time=$cycle * 24 * 60 * 60;
			$data=array();
			$data['credit_num']= floatval($_POST['credit_num']);
			$data['type']= intval($_POST['type']);
			$data['price']= floatval($_POST['price']);
			$data['info']= trim($_POST['info']);
			$data['cycle']= $time;
			$credit_goods_mod->edit($id,$data);
			header("Location:index.php?app=member&act=credit");
		}
		
	}
	
	//�����Ҷ���
	function credit_order()
	{
		$this->_curitem('overview');
		$credit_order_mod = & m('creditorder');
		//��ҳ
		$page_num = 5;
    	$page = $this->_get_page($page_num);
		$user_id = $this->visitor->get('user_id');
		$this->assign('user_id',$user_id);
		$order_sn = empty($_GET['order_sn']) ? 0 : intval($_GET['order_sn']);
		//�µ�ʱ������
		$add_time_from=empty($_GET['add_time_from']) ? 0 : strtotime($_GET['add_time_from']);//����ȡ��dateʱ��ת����ʱ�������ʽ
		$add_time_to=empty($_GET['add_time_to']) ? 0 : strtotime($_GET['add_time_to']);
		$status=empty($_GET['status']) ? 0 : intval($_GET['status']);
		$opt=$_GET['opt'];
		$conditions = " 1 = 1 and o.buyer_id = " .$user_id . " or o.seller_id = " .$user_id;
		//����״̬
		/*switch ($status){
			case 0:
				$conditions .= " AND 1 = 1 ";	
				$this->assign('status',0);
				break;
			case 11:
				$conditions .= " AND o.status = " .$status;
				$this->assign('status',11);
				break;
			case 20:
				$conditions .= " AND o.status = " .$status;
				$this->assign('status',20);
				break;
			case 40:
				$conditions .= " AND o.status = " .$status;
				$this->assign('status',40);
				break;
			default :
				$this->show_warning("����״̬���ҳ������");
				break;
		}
		//������
		if($order_sn != 0)
		{
			$conditions .= " AND o.order_sn = " .$order_sn;
			$this->assign('order_sn',$order_sn);
		}
		switch ($opt){
			case 1:
				$conditions .= " AND o.seller_id = " .$user_id;
				$this->assign('opt',1);
				break;
			case 2:
				$conditions .= " AND o.buyer_id = " .$user_id;
				$this->assign('opt',2);
				break;
			default :
				$this->show_warning("���ҳ������");
				break;
		}*/
		$count = $credit_order_mod->getOne("select count(*) from pa_credit_order o left join pa_credit_goods g on o.credit_id = g.id where " .$conditions); 
		$page['item_count'] = $count;
		$order_info = $credit_order_mod->getAll("select *,o.id from pa_credit_order o left join pa_credit_goods g on o.credit_id = g.id where " . $conditions . " order by o.add_time desc limit " . $page['limit']);
		$this->_format_page($page);
		$this->assign('page_info', $page);
		$this->assign('order_info',$order_info);
		$this->display("credit.order.html");
	}
	
    public function withdraw() //����
    {
    	$this->_curitem('my_profile');
    	$user_id = $this->visitor->get('user_id');
    	//�û���Ϣ
    	$member_info = $this->_member_mod->getRow("select m.mobile,m.user_id,m.money,m.frozen_money from 
    	pa_member m left join pa_customer_manager cm on m.user_id = cm.user_id where m.user_id = " . $user_id);
    	$member_info['money'] = floatval($member_info['money'] - $member_info['frozen_money']);
    	if (!IS_POST)
    	{
    		$this->assign('withdraw',$withdraw);
    		$this->assign('member_info', $member_info);
    		$this->display('member.managerCashOut.html');
    	} else {
	
    		if ($_POST['withdraw_amount'] == '') 
    		{
    			$this->show_warning('����д���ֽ��!');
    			return;
    		}
    		if($_POST['draw_bank'] == '')
    		{
    			$this->show_warning('�����в���Ϊ��!');
    			return;
    		}
    		if($_POST['draw_name'] == '')
    		{
    			$this->show_warning('д����������Ϊ��!');
    			return;
    		}
    		if($_POST['draw_accounts'] == '')
    		{
    			$this->show_warning('�����˺Ų���Ϊ��!');
    			return;
    		}
    		if ($_SESSION['withdraw']['verify'] != $_POST['verify']) //��֤��ͨ��
    		{
    			$this->show_warning('��֤�����벻��ȷ!');
    			return;
    		}   		
    		if($_SESSION['withdraw']['verify'] == '' || $_SESSION['withdraw']['verify'] == null)
    		{	
    			$this->show_warning('��֤�벻��Ϊ��!');
    			return;
    		}
    		//��֤�����Ƿ����㹻�ʽ�ɹ�����
    		$draw_amount = floatval($_POST['withdraw_amount']);
    		if ($draw_amount > $member_info['money']) 
    		{
    			$this->show_warning('�����ֵĽ��������еĽ��!');
    			return;
    		}
    		$data = array(
    			'user_id' => $user_id,
	    		'withdraw_amount' => $draw_amount,
	    		'withdraw_time' => time(),
	    		'draw_type' => 4,
	    		'draw_bank' => trim($_POST['draw_bank']),
	    		'draw_name' => trim($_POST['draw_name']),
	    		'draw_accounts' => trim($_POST['draw_accounts']),
    			'status' => 1,
    			'reason' => ''
    		);  		
    		$customerWithdrawAsk_mod = & m('customerwithdrawask');
    		if (!$customerWithdrawAsk_mod->add($data))
    		{
    			$this->show_warning('����ʧ��, ������!');
    			return;
    		}
    		//��Ա������
    		changeMemberCreditOrMoney($user_id,$draw_amount,FROZEN_MONEY);
			//���²�����¼��־
			$param = array(
		        	'user_id' => $user_id,
		        	'frozen_money' => $draw_amount,
					'change_time' => time(),
		            'change_desc' => "��Ա��������:".$draw_amount.",ϵͳ������".$draw_amount,
		            'change_type'	=> 4,
		        );
			add_account_log($param);
	   		$this->show_message('����ɹ�,������Ա�������������������!.');
    	}
    }
    
	public function sendDrawVerity() {
		//��ȡ�ֻ�����
		$user_id = empty($_POST['user_id']) ? null : trim($_POST['user_id']);
		
        $user_info = $this->_member_mod->getRow('select * from pa_member where user_id = ' . $user_id);
        if (!$user_info)
        {
        	echo -1;
        	return;
        }
        if ($user_info['mobile'] == '' || !$user_info['mobile']) 
        {
        	echo -2;
        	return;
        }
        $mobile = $user_info['mobile'];

        if (!$mobile)
        {
            echo 1;  //�ֻ�����Ϊ��
        }else
        {
        	if (is_mobile($mobile))
        	{
        		$smslog =&  m('smslog'); 
	        	$todaysmscount = $smslog->get_today_smscount($mobile); //������ŵķ�������
	        	
				if ($todaysmscount>=5)
				{
					echo 6;
					return;
        		}
        		//��������������php���л�����ʱδ���ÿ���soap��չ��������ʱ��ʹ��webservice��ʽ
        		import('class.smswebservice');    //������ŷ�����
        		$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
        		$verify = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);//��֤��
        		$verifytype = 'withdraw'; //������֤������ , �������Ź����Ƽ�
        		$smscontent = "�𾴵Ŀͻ������������ǵ��Ź���������֤��������֤��Ϊ��{$verify}������ȷ��Ŀǰ���������˲���,���ֹ�����֧������www.paila100.com����������" ;
				
        		$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ�����֤�����
        		//���ŷ��ͳɹ�
        		if ($result == 0) 
        		{
        			//����֤��д��SESSION
        			$time = time();
        			$_SESSION['withdraw']['mobile'] =  $mobile;
        			$_SESSION['withdraw']['verify'] =  $verify;
        			$_SESSION['withdraw']['dateline'] =  $time;
        			//ִ�ж�����־д�����
        			$smsdata['mobile'] = $mobile;
        			$smsdata['smscontent'] = $smscontent;
        			$smsdata['type'] = $verifytype; 
        			$smsdata['sendtime'] = $time;
        			
        			$smslog->add($smsdata);
        			echo 4;
        		}else
        		{
        			echo 5; //���ŷ���ʧ��
        		}
        	}else 
        	{
        		echo 2; //����ȷ���ֻ�����
        	}
        }
        return;
	}
	//�����Ҹ���Լ��˻�����
	public function paypl(){
		$this->_curitem('paypl');
		$user_id = $this->visitor->get('user_id');
    	//�û���Ϣ
    	$member_info = $this->_member_mod->getRow("select *,m.money from pa_member m where m.user_id=".$user_id);
		//var_dump($member_info);
		$loseid = $member_info['user_id'];

		$loseprice = empty($_POST['price']) ? 0 : intval($_POST['price']);
		if(IS_POST){
			if (!$_POST['user_name'])
			{
				$this->show_warning('�û�������Ϊ�� !');
				return;
			}
			if (!$_POST['user_id'])
			{
				$this->show_warning('�˺Ų���Ϊ�� !');
				return;
			}
			if (!$_POST['price'])
			{
				$this->show_warning('����Ϊ�� !');
				return;
			}
			$user_name=$_POST['user_id'];
			$member_gaininfo = $this->_member_mod->getRow("select * from pa_member where user_name='".$user_name."'");
			if ($member_gaininfo == false)
			{
				$this->show_warning('�����˲����� !');
				return;
			}
			if ($member_gaininfo['real_name'] != $_POST['user_name'])
			{
				$this->show_warning('��������ȷ�ĸ���������!');
				return;
			}
			$gainprice = empty($member_gaininfo['credit']) ? 0 : intval($member_gaininfo['credit']);
			$gainid = $member_gaininfo['user_id'];
			
		}
		$this->display("member.pay.html");
	}

    //��ѯ������ϸ
    public function selGainsDetail() 
    {
    	$this->_curitem('my_profile');
    	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	if ($id == 0)
    	{
    		$this->show_warning('��ѯ�������¼������ !');
    		return;
    	}
    	$accountlog_mod = & m('accountlog');
    	$log_info = $accountlog_mod->getRow("select * from pa_account_log a 
    		left join pa_customer_manager c on a.user_id = c.user_id  left join 
    		pa_customer_level l on c.customer_level = l.level_id where a.log_id = " . $id);
    	$this->assign('change_type', get_change_type());
    	$this->assign('info', $log_info);
    	$this->display('member.selgainsdetail.html');
    }
    
    function check_id_card(){
    	$card = empty($_GET['identity_num']) ? '' : trim($_GET['identity_num']);
    	$cust_manager_mod =& m('customermanager');
    	$d=$cust_manager_mod->get("identity_num ='".$card."'");
    	if (isset($d['user_id']))
    	{
    		echo 'false';
    		return;
    	}
    	echo 'true';
    	return;
    }
    function check_user_name(){
    	$name = empty($_GET['user_name']) ? '' : trim($_GET['user_name']);
    	$cust_manager_mod =& m('customermanager');
    	$d=$cust_manager_mod->get("name ='".$name."'");
    	if (isset($d['user_id']))
    	{
    		echo 'false';
    		return;
    	}
    	echo 'true';
    	return;    	
    }
    //�Ƽ���Ϊ�ͻ�����
    public function recomManager()
    {
    	$this->_curitem('my_profile');
    	$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'mlselection.js',
                    'attr' => '',
                ),
                array(
                	'path' => 'jquery.plugins/jquery.validate.js',
                	'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
    	if (!IS_POST)
    	{
    		$this->_get_regions();
    		
    		$this->display('member.recomManagerForm.html');
    	} else {
	    	//import('credit.func');
	    	$level = 1;
	    	$user_name = empty($_POST['user_name']) ? null : trim($_POST['user_name']);
			
	        $user_info = $this->_member_mod->getRow('select * from pa_member where user_name = "' . $user_name . '"');
	        if (!$user_info)
	        {
	        	$this->show_message("�Բ���,�����Ƽ����û������� ! �����Ƿ���д����!");
	        	return;
	        }
	    	$user_id = $user_info['user_id'];
	    	$this->_curitem('my_profile');
	    	//����Ƿ���2000PL
	    	$customerLevel_mod = & m('customerlevel');
	    	$customerLevel_info = $customerLevel_mod->get($level);	
	    	
	 		//���˼���
	 		$customerManager_mod = & m('customermanager');
	 		$customerAsk_mod = & m('customerask');
	 		$customerManager_info = $customerManager_mod->get('user_id = ' . $user_id);
	 		$customerAsk_info = $customerAsk_mod->get('user_id = ' . $user_id);
	 		//�Ƽ�����Ϣ
	 		$recomManager_info = $customerManager_mod->get('user_id = ' . $this->visitor->get('user_id'));
	 		//�Ƿ��Ѿ�������
	 		if ($customerAsk_info) 
	 		{
	 			$this->show_warning('�����������������..! �벻Ҫ�ٴ����� !');
	 			return;
	 		}

	 		if (!$_POST['real_name'])
    		{
    			$this->show_warning('����д������ʵ���� !');
    			return;
    		}
    			
    		if (!$_POST['region_id'])
    		{
    			$this->show_warning('��ѡ�������ڵĵ���!');
    			return;
    		}
    		if (!$_FILES['identity_card'])
    		{
    			$this->show_warning('���ϴ��������֤��Ƭ!');
    			return;
    		}
    		
    		if ($_POST['verify'] != $_SESSION['recomManager']['verify'])
    		{
    			$this->show_warning('���������֤�벻��ȷ!');
    			return;
    		}
    		//���ϴ����ļ��ƶ�����λ��
    		//�����ļ��ϴ�λ��
    		$uploadDir = 'data/files/manager/';
    		$uploadFile = md5($user_id) . strrchr($_FILES['identity_card']['name'],'.');
    		if (!file_exists($uploadDir))
    		{
    			mkdir($uploadDir,0777,true);
    		}
    		$data = array(
	    		'user_id' => $user_id,
	    		'user_name' => $user_info['user_name'],
	    		'user_level' => !($customerManager_info['customer_level']) ? 0 : intval($customerManager_info['customer_level']),
	    		'need_level' => $level,
    		);
    		move_uploaded_file(trim($_FILES['identity_card']['tmp_name']), $uploadDir . $uploadFile);
    		$data['real_name'] = trim($_POST['real_name']);
    		$data['sex'] = trim($_POST['sex']);
    		$data['identity_num'] = trim($_POST['identity_num']);
    		$data['identity_card'] = $uploadDir . $uploadFile;
    		$data['tel_phone'] = $user_info['mobile'];
    		$data['email'] = trim($_POST['email']);
    		$data['region_id'] = trim($_POST['region_id']);
    		$data['region_name'] = trim($_POST['region_name']);
    		$data['address'] = trim($_POST['address']);
    		$data['recom_user_id'] = $this->visitor->get('user_id');
    		$data['recom_user_name'] = $this->visitor->get('user_name');
    		$data['recom_real_name'] = $recomManager_info['real_name'];
    		$data['recom_level'] = $recomManager_info['customer_level'];
    		
    		if (!$customerAsk_mod->add($data))
	    	{
	    		$this->show_warning('�ύ����ʧ��!.');
	    		return;
	    	}
	    	
	    	$this->show_message('�ύ����ɹ�!','go_back','index.php?app=member&act=manager');
	    }
    }
    
    public function getUserMobile()
    {
    	$user_name = empty($_GET['username']) ? '' : trim($_GET['username']);
    	$user_info = $this->_member_mod->getRow('select mobile from pa_member where user_name = "' . $user_name . '"');
    	if (!$user_info) return;
    	$user_mobile = $user_info['mobile'];
    	$mobile = substr_replace($user_mobile, '****', 3, 4);
    	$this->json_result($mobile);
    }
    
    
	/**
	 * ajax ���Ͷ���
	 */
	public function sendManagerVerify() {
		//��ȡ�ֻ�����
		$user_name = empty($_POST['user_name']) ? null : trim($_POST['user_name']);
		$user_name = iconv('utf-8', 'gbk', $user_name);

        $user_info = $this->_member_mod->getRow("select * from pa_member where user_name = '" . $user_name . "'");
        if (!$user_info)
        {
        	$this->json_error("userundifine");
        	return;
        }
        if ($user_info['mobile'] == '' || !$user_info['mobile']) 
        {
        	$this->json_error("user_have_none_mobile");
        	return;
        }
        $mobile = $user_info['mobile'];

        if (!$mobile)
        {
            $this->json_error("user_have_none_mobile");
        	return;
        }else
        {
        	if (is_mobile($mobile))
        	{
        		$smslog =&  m('smslog'); 
	        	$todaysmscount = $smslog->get_today_smscount($mobile); //������ŵķ�������
	        	
				if ($todaysmscount>=5)
				{
					$this->json_error("message_only_five");
					return;
        		}
        		//��������������php���л�����ʱδ���ÿ���soap��չ��������ʱ��ʹ��webservice��ʽ
        		import('class.smswebservice');    //������ŷ�����
        		$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
        		$verify = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);//��֤��
        		$verifytype = 'recomManager'; //������֤������ , �������Ź����Ƽ�
        		$smscontent = "�𾴵Ŀͻ������������ǵ��Ź����Ƽ���֤��������֤��Ϊ��{$verify}������ȷ��Ŀǰ���������˲���,���ֹ�����֧������www.paila100.com����������" ;
				
        		$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ�����֤�����
        		//���ŷ��ͳɹ�
        		if ($result == 0) 
        		{
        			//����֤��д��SESSION
        			$time = time();
        			$_SESSION['recomManager']['mobile'] =  $mobile;
        			$_SESSION['recomManager']['verify'] =  $verify;
        			$_SESSION['recomManager']['dateline'] =  $time;
        			//ִ�ж�����־д�����
        			$smsdata['mobile'] = $mobile;
        			$smsdata['smscontent'] = $smscontent;
        			$smsdata['type'] = $verifytype; 
        			$smsdata['sendtime'] = $time;
        			
        			$smslog->add($smsdata);
        			$this->json_result($mobile, "send_message_end");
        			return;
        		}else
        		{
        			$this->json_error('send_message_field'); //���ŷ���ʧ��
        			return;
        		}
        	}else 
        	{
        		$this->json_error('mobile_error');
        		return;
        	}
        }
        return;
	}
    public function askForManager() 
    {
    	$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'mlselection.js',
                    'attr' => '',
                ),
                array(
                	'path' => 'jquery.plugins/jquery.validate.js',
                	'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
    	$user_id = $this->visitor->get('user_id');
    	$this->_curitem('askManager');
    	/* ��ǰ�û�������Ϣ*/
       	$this->_get_user_info();
    	/* ��ǰ�����Ӳ˵� */
        $this->_curmenu('basic_ask');
    	
    	$user_info = $this->_member_mod->get($user_id);
    	$customerLevel_mod = & m('customerlevel');
 		//���˼���
 		$customerManager_mod = & m('customermanager');
 		$customerAsk_mod = & m('customerask');
 		$customerManager_info = $customerManager_mod->get('user_id = ' . $user_id);
 		$customerAsk_info = $customerAsk_mod->get('user_id = ' . $user_id);
 		//�Ƿ��Ѿ�������
 		if ($customerAsk_info) 
 		{
 			$this->show_warning('�����������������..! �벻Ҫ�ٴ����� !');
 			return;
 		}

 		if (!$customerManager_info)
    	{
    		if(!IS_POST) {
    			if(!empty($_GET['invitecode']))
    			{
    				$invitecode = base64_decode($_GET['invitecode']);
	    			$ids = explode(",",$invitecode);
		    		if($this->visitor->get(user_id) != intval($ids[1]))
		    		{
		    			$this->show_message("�û�û��������Ϊ�Ź�Ա���벻Ҫ�ñ��˵����ӵ�ַ");
		    			return;
		    		}
		    		$manage = $customerManager_mod->get(intval($ids['0']));
		    		if (!$manage)
		    		{
		    			$this->show_message("�����˲����Ź�Ա");
		    			return;
		    		}
		    		$member = $this->_member_mod->get(intval($ids['0']));
		    		if ($member)
		    		{
		    			$this->assign('member',$member);
		    		}	
    			}
    			$this->_get_regions();
    			$this->assign('bank',get_bank());
    			$this->display('member.manageraskform.html');
    			return;
    		} else {
    			//��֤
    			if (!$_POST['real_name'])
    			{
    				$this->show_warning('����д������ʵ���� !');
    				return;
    			}
    			
    			if (!is_mobile($_POST['mobile']))
    			{
    				$this->show_warning('�����ϵ�绰��д���Ϸ�!');
    				return;
    			}
    			if (!$_POST['region_id'])
    			{
    				$this->show_warning('��ѡ�������ڵĵ���!');
    				return;
    			}
    			if (!$_FILES['identity_card'])
    			{
    				$this->show_warning('���ϴ��������֤��Ƭ!');
    				return;
    			}
    			if (!$_POST['card_number'])
    			{
    				$this->show_warning('�����������˺�!');
    				return ;
    			}
    			if (!$_POST['bank_name'])
    			{
    				$this->show_warning('�����뿪����!');
    				return ;
    			}else {
    				$bank = get_bank();
    				if(empty($bank[$_POST['bank_name']]))
    				{
    					$this->show_warning("ѡ�񿪻��г���!");
    					return ;
    				}
    			}
    			//��������Ϣ
    			if(!$_POST['inviter_param'])
    			{
    				$inviter_id = CHANNEL_ID;
    				$inviter_info=$customerManager_mod->get($inviter_id);
    			}else {
    			   	$user_name = $_POST['inviter_param'];
		    		$ms =& ms();
			    	$info = $ms->user->get($user_name, true);
					
			        //��ʹ���û����Ҳ�����Ա��Ϣ��ʱ��----ʹ���ֻ�����֤
			        if (!$info) 
			        {
				        if (is_mobile($user_name)) 
				        {
				        	$info = $ms->user->get($user_name, false,true);
				        }
			        }
			        
			        $inv_id = $info['user_id'];
		            if (!$inv_id)
		            {
		                $this->show_message("�Ƽ��˲����ڣ������������");
		                return;
		            }
		            
		            if ($inv_id==$user_id)
		            {
		            	$this->show_message("�Լ������Ƽ��Լ���");
		                return;
		            }
		            
			        $inviter_info=$customerManager_mod->get($inv_id);
			        if (!$inviter_info)
			        {
			        	$this->show_message("����д���Ƽ��˲����Ź�Ա�������Ƽ�����Ϊ�Ź�Ա��");
		                return;
			        }
    			}
    			//���ϴ����ļ��ƶ�����λ��
    			//�����ļ��ϴ�λ��
    			$uploadDir = 'data/files/manager/';
    			$uploadFile = md5($this->visitor->get('user_id')) . strrchr($_FILES['identity_card']['name'],'.');
    			if (!file_exists($uploadDir))
    			{
    				mkdir($uploadDir,0777,true);
    			}
    			move_uploaded_file(trim($_FILES['identity_card']['tmp_name']), $uploadDir . $uploadFile);
    			$data['user_id'] = $user_id;
    			$data['user_name'] = $this->visitor->get('user_name');
    			$data['real_name'] = trim($_POST['real_name']);
    			$data['sex'] = trim($_POST['sex']);
    			$data['identity_num'] = trim($_POST['identity_num']);
    			$data['identity_card'] = $uploadDir . $uploadFile;
    			$data['tel_phone'] = $_POST['mobile'];
    			$data['need_level'] = '1';
    			$data['email'] = trim($_POST['email']);
    			$data['region_id'] = trim($_POST['region_id']);
    			$data['region_name'] = trim($_POST['region_name']);
    			$data['address'] = trim($_POST['address']);
    			$data['recom_user_id'] =$inviter_info['user_id'];
    			$data['recom_user_name'] =$inviter_info['user_name'];
    			$data['recom_real_name'] = $inviter_info['real_name'];
    			$data['recom_level'] = $inviter_info['customer_level'];
    			$data['card_number'] = trim($_POST['card_number']);
    			$data['bank_name'] = trim($_POST['bank_name']);
    		}
    	}else 
    	{
    		$this->show_warning('���Ѿ����Ź�Ա�����ύ���� !');
 			return;
    	}
    	if (!$customerAsk_mod->add($data))
    	{
    		$this->show_warning('�ύ����ʧ��!.');
    		return;
    	}    	
    	$this->show_message('�ύ����ɹ�!','go_back','index.php?app=member');
    }
    
    public function checkPromote($level,$user_id,$num)
    {
    	$customerManager_mod = & m('customermanager');
    	$customerManager_list = $customerManager_mod->find('parent_id = ' . $user_id);
    	$count = 0;
    	foreach ($customerManager_list as $k => $v)
    	{
    		if ($v['customer_level'] == $level) 
    		{
    			$count++;
    		}
    	}
    	if ($count >= $num) 
    	{
    		return true;
    	}
    	
    	return false;
    }
    
    public function checkOutstandingAchievement($outstand,$user_id) 
    {
    	$customerManager_mod = & m('customermanager');
    	
    	$customerManager_info = $customerManager_mod->get('user_id = ' . $user_id);
    	if(!$customerManager_info) 
    	{
    		return true;
    	}
    	if ($outstand > $customerManager_info['outstanding_achievement_total'])
    	{
    		return false;
    	}
    	
    	return true;
    }
    
	public function _get_regions()
    {
        $model_region =& m('region');
        $regions = $model_region->get_list(0);
        if ($regions)
        {
            $tmp  = array();
            foreach ($regions as $key => $value)
            {
                $tmp[$key] = $value['region_name'];
            }
            $regions = $tmp;
        }
        $this->assign('regions', $regions);
    }
    
    public function autotrophy()
    {
    	$user_id = $this->visitor->get('user_id'); //��ǰ�û�ID
    	$this->_curitem('my_profile');
    	$_storegoods_mod = & m('storegoods');
    	$page = $this->_get_page(12);
    	$info = $_storegoods_mod->_get_autotrophy($page);

    	$page['item_count'] = $info['count'];
    	$this->_format_page($page);
    	$this->assign('page_info', $page);
    	$this->assign('goods_list', $info['goods_list']);
    	$this->display('customer_autotrophy.index.html');
    }
    public function invitgroup()
    {
    	$user_id = $this->visitor->get('user_id');
    	$cust_manager_mod =& m('customermanager');
    	$manager=$cust_manager_mod->get($user_id);
    	if(!$manager)
    	{
    		$this->show_message("��Ŀǰ�������Ź�Ա���������������û���");
    		return ;
    	}
    	$this->_curitem('invitgroup');
    	if(!IS_POST)
    	{
    		$this->display('member.recomManagerForm.html');
    	}else {
    		
    		$user_name = empty($_POST['user_name']) ? '' : trim($_POST['user_name']);
    		$ms =& ms();
	    	$info = $ms->user->get($user_name, true);
			
	        //��ʹ���û����Ҳ�����Ա��Ϣ��ʱ��----ʹ���ֻ�����֤
	        if (!$info) 
	        {
		        if (is_mobile($user_name)) 
		        {
		        	$info = $ms->user->get($user_name, false,true);
		        }
	        }
	        
	        $inv_id = $info['user_id'];
            if (!$inv_id)
            {
                $this->show_message("�������˲����ڣ������������");
                return;
            }
            
            if ($inv_id==$user_id)
            {
            	$this->show_message("���������Լ���");
                return;
            }
            
	        $invit_manager=$cust_manager_mod->get($inv_id);
	        if ($invit_manager)
	        {
	        	$this->show_message("���������Ѿ����Ź�Ա�����ܱ����룡");
                return;
	        }
	        
    		$intNum = $this->invitNum($user_id,$inv_id);
    		$inturl = SITE_URL."/index.php?app=member&act=askForManager&invitecode=".$intNum;
    		$int_info = $this->_member_mod->get($inv_id);
    	    /* �����û�ϵͳ */
            $ms =& ms();
            $msg_id = $ms->pm->send($user_id,$inv_id, '�����Ź�Ա', "�𾴵�".$int_info['user_name']."��".$this->visitor->get(user_name)."����������Ϊ�Ź�Ա,\<a href=\"$inturl\"\>\<b\>���������\</b\>\</a\>��"."��д���Ļ�����Ϣ");
    		if(!msg_id)
    		{
    			$this->show_warning("�����Ź�Աʧ��");
    			return ;
    		}else {
    			$this->show_message('����ɹ�', '��������' , 'index.php?app=member&act=invitgroup');
    			return ;
    		}
    	}
    	
    }
    //�Ź�Ա���������ɺ���
    function invitNum($user_id,$inv_id)
    {
    	return base64_encode($user_id.",".$inv_id);
    }
  	function uninvitgroup()
  	{
  		$this->_curitem('uninvitgroup');
  		/* ��ǰ�û�������Ϣ*/
        $this->_get_user_info();
    	$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'mlselection.js',
                    'attr' => '',
                ),
                array(
                	'path' => 'jquery.plugins/jquery.validate.js',
                	'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
       	$unid = empty($_GET['unid']) ? $this->visitor->get('user_id') : intval($_GET['unid']);
       	$customermanager_mod = & m('customermanager');
		$params = $this->_get_params();
		if($params['condition']){
	  		$unintvitgroup = $customermanager_mod->customer_all_info($unid,$params);
	  		$params['page']['item_count'] = $unintvitgroup['num'];
	  		$this->assign('ungroup',$unintvitgroup['info']);
	  		$this->_format_page($params['page']);
	        $this->assign('page_info', $params['page']);
		}else {
			$page = $this->_get_page();   //��ȡ��ҳ��Ϣ
			if($params['user_name'])
			{
				$contidions = $params['user_name'];
			}
			$unmember=$this->_member_mod->getAll("select * from pa_member where invite_id=".$unid.$contidions.' limit '.$page['limit']);
			$item_count = $this->_member_mod->getRow('select count(*) as count from pa_member where invite_id='.$unid.$contidions);
	  		$page['item_count'] = $item_count['count'];
	  		$this->assign('unmember',$unmember);
	  		$this->_format_page($page);
	        $this->assign('page_info', $page);
		}
  		$this->display('unmember.new.html');
  	}
  	function carry()
  	{
  		$user = $this->visitor->get();
        $store_mod =& m('store');
        $store_info = $store_mod->get(intval($user['user_id']));
        if($this->_get_channel_basic() && $store_info == false)
        {
        	$this->show_message('�Բ��𣬴˹�����δ��ͨ��');
        	return ;
        }
  		$this->_curitem('carry');
  		/* ��ǰ�û�������Ϣ*/
       	$this->_get_user_info();
    	$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'mlselection.js',
                    'attr' => '',
                ),
                array(
                	'path' => 'jquery.plugins/jquery.validate.js',
                	'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        if(!IS_POST)
        {
	        $this->display('member.carry.html');
        }else {
        	$ca_type   = empty($_POST['ca_type']) ? 0 : intval($_POST['ca_type']);
        	$user_name = empty($_POST['user_num']) ? 0 : trim($_POST['user_num']);
        	$user_id   =  $this->visitor->get('user_id');
        	$money	   = empty($_POST['money']) ? 0 : format_money(floatval($_POST['money']));
        	$paypassword = empty($_POST['paypassword']) ? 0 : intval($_POST['paypassword']); 
        	$ms =& ms();
        	$info = $ms->user->get($user_name, true);
			
	        //��ʹ���û����Ҳ�����Ա��Ϣ��ʱ��----ʹ���ֻ�����֤
	        if (!$info) 
	        {
		        if (is_mobile($user_name)) 
		        {
		        	$info = $ms->user->get($user_name, false,true);
		        }
	        }
	        $inv_id = $info['user_id'];
            if (!$inv_id)
            {
                $this->show_message('�տ����˺Ų�����');
                return;
            }
			if($inv_id == $user_id)
			{
				$this->show_message('�����Լ�������Լ�');
				return ;
			}
        	if($ca_type != 1)
        	{
        		$this->show_message("��ѡ�񸶿ʽ");
        		return ;
        	}
        	if(0 == $money)
        	{
        		$this->show_message("����д��ȷ�Ľ��");
        		return ;
        	}
			$paypw = $ms->user->traderAuth($user_id,$paypassword);
			if(!$paypw)
			{
				$this->show_warning("֧����������");
				return ;
			}
        	$member =& m('member');
        	$member_info = $member->get($user_id);
			switch ($ca_type)
			{
				case 1 : 
						if($money > $member_info['money'])
						{
							$this->show_message('������ܴ����Լ������');
							return ;
						}
						changeMemberCreditOrMoney(intval($member_info['user_id']),$money,SUBTRACK_MONEY);
						changeMemberCreditOrMoney(intval($inv_id),$money,ADD_MONEY);
						$pdata = array(
							'user_id' => intval($member_info['user_id']),
							'user_money'    => -$money,
							'change_time'	=> time(),
							'change_desc'	=> '���ת�˸���Ա��'.$info['user_name'].'����'.$money.'Ԫ',
							'change_type'	=> '42',
						);
						$gdata = array(
							'user_id'       => intval($info['user_id']) ,
							'user_money'    => $money,
							'change_time'	=> time(),
							'change_desc'	=> '�յ���Ա��'.$member_info['user_name'].'��ת����'.$money.'Ԫ',
							'change_type'	=> '44',
						);
						add_account_log($pdata);
						add_account_log($gdata);
			            $pmsg_id = $ms->pm->send(MSG_SYSTEM,$member_info['user_id'], 'ת��֪ͨ', "�𾴵��û�".$member_info['user_name']."����ת�˸���Ա".$info['user_name']."�Ѿ��ɹ���ת�˽�".$money.'Ԫ');
			            $gmsg_id = $ms->pm->send(MSG_SYSTEM,$info['user_id'], '�տ�֪ͨ', "�𾴵��û�".$info['user_name']."����Ա".$member_info['user_name']."����ת�˳ɹ�����������˻����ӽ�".$money.'Ԫ');
				break;
			}
			$this->show_message('����ɹ�!','��������','index.php?app=member&act=carry');
        }
  	}
  	/***
  	 * ��ȡ�Ź�Ա��ѯ����
     * @author xiaoyu
     * @return array
  	 ****/
  	function _get_params()
  	{
  		$page = $this->_get_page();   //��ȡ��ҳ��Ϣ
  		$params['page'] = $page;
  		$bra = empty($_GET['pub']) ? '' : trim($_GET['pub']); 
  		switch ($_GET['type']){
  			case 1 : 
  				$params['condition'] = false;
  				$params['user_name'] = " and user_name like  '%".$bra."%'";
  				break;
  			case 2 :
  				$params['condition'] = true;
  				$params['user_name'] = " and user_name like  '%".$bra."%'";
  				break;
  			case 3 :
  				$params['condition'] = true;
  				if($bra == '')
  				{
  					$bra = 1;
  				}
  				$params['algebra'] = " and algebra = ".$bra;
  				break;
  			case 4 :
  				$lv = 1;
  				if (trim($_GET['pub']) == '����')
  				{
  					$lv = 1;
  				}
  				if (trim($_GET['pub']) == '�м�')
  				{
  					$lv = 2;
  				}
  				if (trim($_GET['pub']) == '�߼�')
  				{
  					$lv = 3;
  				}
  				if (trim($_GET['pub']) == '����')
  				{
  					$lv = 4;
  				}				
  				$params['condition'] = true;
  				$params['lv'] = " and customer_level = ".$lv;
  				break;
  			default:
  				$params['condition'] = true;
  				break;
  		}
  		$this->assign('type',$_GET['type']);
  		return $params; 
  	}
    //�Ź�Ա�鿴����
    public function selGains()
    {
    	$this->_curitem('selGains');
    	$user_id = $this->visitor->get('user_id');
    	/* ��ǰ�û�������Ϣ*/
       	$this->_get_user_info();
    	$conditions = "1 = 1";
		$param = array(
			50 => '�Ź�Ա��ȡ��������',
			51 => '�Ź�Ա�Ƽ���������',
		//	52 => '�Ź�Ա������������',
			53 => '��Ա�����Ź�Ա��������'
		);
		if(empty($_GET['selg_id']))
		{
			$conditions .= " and change_type in (50,51,52,53) ";
		}else {
			$conditions .= " and change_type = ".$_GET['selg_id'];
			$this->assign('selg_id',$_GET['selg_id']);
		}
    	import('Page.class');
        $count = $this->_get_account($conditions,false,true); //������
        $listRows= 10;        //ÿҳ��ʾ����
        $page=new Page($count,$listRows); //��ʼ������
        $customer_manager_mod = & m('customermanager');
        $user = $customer_manager_mod->getRow('select * from pa_customer_manager where  user_id ='.$this->visitor->get('user_id'));
        $this->assign('users',$user);
        $type = get_change_type();
        $node_list = $this->_get_account($conditions,$page);
        foreach($node_list as $k=>$v)
        {
        	$node_list[$k]['change_time'] = $v['change_time'];
        	$node_list[$k]['change_type'] = $type[$v['change_type']];
        }
    	$p=$page->show();
    	$this->assign('param',$param);
		$this->assign('page',$p);
		$this->assign('node_list',$node_list);
        $this->_config_seo('title', Lang::get('member_center'));
    	$this->display('member.selgnew.html');
    }
	/* �����Ʒ���ࣺ��ӡ��༭��Ʒ����֤ʱ�õ� */
    function check_mgregion()
    {
        $region_id = isset($_GET['region_id']) ? intval($_GET['region_id']) : 0;

        echo ecm_json_encode($this->_check_mgregion($region_id));
    }
    /**
     * �����Ʒ���ࣨ��ѡ������Ҷ�ӽ�㣩
     *
     * @param   int     $cate_id    ��Ʒ����id
     * @return  bool
     */
    function _check_mgregion($region_id)
    {
    	$region_mod = & m('region');
        if ($region_id > 0)
        {
            $info = $region_mod->is_leaf($region_id);
            if ($info)
            {
                return true;
            }
        }

        return false;
    }
}

?>
