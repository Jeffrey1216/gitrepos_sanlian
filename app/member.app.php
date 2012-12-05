<?php

/**
 *    Desc
 *
 *    @author    Garbin
 *    @usage    none
 */

define('ACCOUNT_MANAGER',1); //客户经理
define('KEY_ACCOUNT_MANAGER',2); //大客户经理
define('GROUP_ACCOUNT_MANAGER',3); //集团客户经理
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
        /* 清除新短消息缓存 */
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

        /* 店铺信用和好评率 */
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
        /* 买家提醒：待付款、待确认、待评价订单数 */
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
        /* 取得一周内的定单 */
        //当前时间
        $time = time();
        //上周时间戳
        $previousWeek = $time - (7 * 24 * 60 * 60);
        $order_info = $order_mod->find(array(
        	'conditions' => "buyer_id = '{$user['user_id']}' AND add_time >=".$previousWeek,
        	'join'	=> 'has_orderextm'
        ));
        //添加定单商品信息
        $orderGoods_mod = & m("ordergoods");
        foreach($order_info as $k => $order) {
        	$ordergoods = $orderGoods_mod->find(array('conditions'=>'order_id='.$order['order_id']));
        	$order_info[$k]['order_goods'] = $ordergoods;
        }
        //设置分页
        $sql = "select count(*) from pa_order where buyer_id='{$user['user_id']}' AND add_time>='{$previousWeek}'";
        $num = $order_mod->getone($sql);
        $page = $this->_get_page(5);
        $page['item_count'] = $num;
        $this->_format_page($page);
        $this->assign('page_info',$page);
 		$this->assign('order_info',$order_info);
        /* 卖家提醒：待处理订单和待发货订单 */
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

        /* 卖家提醒： 店铺等级、有效期、商品数、空间 */
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
        /*资讯留言条数*/
		$goods_qa = $this->_my_qa_mod->getOne('select count(*) from pa_goods_qa where user_id='.$this->visitor->get(user_id));
		$this->assign('goods_qa',$goods_qa);
		/*今日活动相对挂件中的今日活动*/
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
		/*派啦热卖*/
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
		/*新品推荐*/
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
		/*精品推荐*/
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

        /* 待审核提醒 */
        if ($user['state'] != '' && $user['state'] == STORE_APPLYING)
        {
            $this->assign('applying', 1);
        }
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    url('app=member'),
                         LANG::get('overview'));

        /* 当前用户中心菜单 */
        $this->_curitem('overview');
        $this->_config_seo('title', Lang::get('member_center'));
        $this->assign('invite_url',SITE_URL.'/index.php?app=member&act=register&invitecode='.base64_encode($user['user_id']));
        $this->display('member.index.html');
    }
    
    /*
     *  交易详情
     */
	function detailed()
    {
    	date_default_timezone_set('Asia/Shanghai');
    	$day = empty($_GET['day']) ? 0 : intval($_GET['day']); //7代表 7天内的订单
    	$conditions = "1 = 1";
    	if($day === 0) { //查询所有
    		$conditions .= '';
    	} else {
    		$now_time = time();
    		$pro_time = $now_time - ($day * 24 * 60 * 60);
    		$conditions .= " AND account_log.change_time > " . $pro_time . " AND account_log.change_time < " . $now_time;
    	}
    	import('Page.class');
        $count = $this->_get_account($conditions,false,true); //总条数
        $listRows= 10;        //每页显示条数
        $page=new Page($count,$listRows); //初始化对象
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
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    url('app=member'),
                         LANG::get('overview'));
       	/* 当前用户基本信息*/
        $this->_get_user_info();
        /* 当前用户中心菜单 */
        $this->assign('day',$day);
        $this->_curitem('detailed');
        $this->_config_seo('title', Lang::get('member_center'));

        $this->display('member.detailed.html');
    }
    
    /**
     * 	获取积分记录
     */
    public function _get_account($conditions, $page,$count=false) {
    	/* 只取通过审核的派啦商城的商品 */
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
        	/* 取得记录列表 */
	        $notes_list = $account_mod->get_account_orders(array(
	            'conditions' => $conditions,
	 			'order'	=> 'account_log.change_time DESC',
	            'limit' => $page->firstRow.','.$page->listRows,
	        ));

	        return $notes_list;
        } 
    }

	/*
     *  交易详情
     */
	function balance()
    {
        /* 清除新短消息缓存 */
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
        /* 店铺信用和好评率 */
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
        /* 买家提醒：待付款、待确认、待评价订单数 */
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
        /* 取得一周内的定单 */
        //当前时间
        $time = time();
        //上周时间戳
        $previousWeek = $time - (7 * 24 * 60 * 60);
        $order_info = $order_mod->find(array(
        	'conditions' => "buyer_id = '{$user['user_id']}' AND add_time >=".$previousWeek,
        	'join'	=> 'has_orderextm'
        ));
        //添加定单商品信息
        $orderGoods_mod = & m("ordergoods");
        foreach($order_info as $k => $order) {
        	$ordergoods = $orderGoods_mod->find(array('conditions'=>'order_id='.$order['order_id']));
        	$order_info[$k]['order_goods'] = $ordergoods;
        }
        //设置分页
        $sql = "select count(*) from pa_order where buyer_id='{$user['user_id']}' AND add_time>='{$previousWeek}'";
        $num = $order_mod->getone($sql);
        $page = $this->_get_page(5);
        $page['item_count'] = $num;
        $this->_format_page($page);
        $this->assign('page_info',$page);
 		$this->assign('order_info',$order_info);
        /* 卖家提醒：待处理订单和待发货订单 */
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
        /* 卖家提醒： 店铺等级、有效期、商品数、空间 */
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

        /* 待审核提醒 */
        if ($user['state'] != '' && $user['state'] == STORE_APPLYING)
        {
            $this->assign('applying', 1);
        }
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    url('app=member'),
                         LANG::get('overview'));

        /* 当前用户中心菜单 */
        $this->_curitem('overview');
        $this->_config_seo('title', Lang::get('member_center'));
        $this->display('member.balance.html');
    }

    /**
     *    注册一个新用户
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
            //增加邀请人功能
			if ($_GET['invitecode']){
				$invitecode = intval(base64_decode($_GET['invitecode']));
				if (is_int($invitecode)){
					$member =& m('member');
					$inviteinfo = $member->get($invitecode);
					$this->assign('inviteinfo',$inviteinfo);
				}
			}
			 //底部文章
			$article_mod = & m("article");
        	$acategory_mod = & m("acategory");
	        $ACC = $acategory_mod->get_ACC();
	        $about = $article_mod->find(array('conditions' => 'cate_id='.$ACC[ACC_ABOUT],'fields' => 'title','order' => 'sort_order'));
	        $this->assign('about',$about);
            /* 导入jQuery的表单验证插件 */
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
                /* 两次输入的密码不一致 */
                $this->show_warning('inconsistent_password');
                return;
            }
            
            /* 注册并登陆 */
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
                $this->show_warning('两次输入的支付密码不一致');
                return;
            }
            if (!is_tpwd($tpwd))
            {
            	$this->show_warning('支付密码必须为6位数字');

                return;
            }
            
            $ms =& ms(); //连接用户中心
            //增加邀请人
            if ($_POST['invite_id'])
            {
	            if (!is_mobile($_POST['invite_id']))
	            {
	                $this->show_warning('邀请人手机号码不正确');
	
	                return;
	            }
	            $info = $ms->user->get($_POST['invite_id'],false,true);
				if (!$info) {
					$this->show_warning('您输入的邀请人不存在');
	
	                return;
				}
				$data['invite_id'] = $info['user_id'];
            }
            $data['mobile'] = $mobile;
            $data['trader_password'] = $ms->user->getMd5TraderPassword($tpwd);
            $mobilearea = &  m('mobile');  //实例化手机区域表
            $data['mobilearea'] = $mobilearea->get_areaname_by_mobile($mobile);
            $user_id = $ms->user->register($user_name, $password, $email,$data);

            if (!$user_id)
            {
                $this->show_warning($ms->user->get_error());
				
                return;
            }
            
            //注册送积分
            //$this->sendCredit($user_id, 5);
            //如果此用户是他人邀请注册--并注册成功---增加一次活动抽奖机会给邀请人
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
//            	$infos['remark'] = '邀请用户：'.$user_name.'获得一次大派送活动抽奖机会';
//            	$awardcount = &m('activityawardcount'); //实例化活动抽奖统计表
//        		$awardnum  = &m('activityawardnum');	//会员抽奖机会表
//        		$awardnum->edit('uid='.$inviteinfo['user_id'].' AND act_id = '.PAISONG,'num = num + 1'); //增加一次抽奖机会
//        		$awardcount->add($infos);
//            }
            $this->_hook('after_register', array('user_id' => $user_id));
            //登录
            $this->_do_login($user_id);
            
            /* 同步登陆外部系统 */
            $synlogin = $ms->user->synlogin($user_id);

            #TODO 可能还会发送欢迎邮件

            $this->show_message(Lang::get('register_successed') . $synlogin,
                'back_before_register', rawurldecode($_POST['ret_url']),
                'enter_member_center', 'index.php?app=member',
                'apply_store', 'index.php?app=apply'
            );
        }
    }
	

    /**
     *    检查用户是否存在
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
     *    修改基本信息
     *
     *    @author    Hyber
     *    @usage    none
     */
    function profile(){

        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('basic_information'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
            $this->_curmenu('new_basic_information');
			/* 当前用户基本信息*/
            $this->_get_user_info();
            $ms =& ms();    //连接用户系统
            $edit_avatar = $ms->user->set_avatar($this->visitor->get('user_id')); //获取头像设置方式

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
            /* edit by lihuoliang 2011/08/24 (修改后台方式获取模板)*/
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
     *    修改密码
     *
     *    @author    Hyber
     *    @usage    none
     */
    function password(){
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('edit_password'));

            /* 当前用户中心菜单 */
            $this->_curitem('loginpassword');
            /* 当前用户基本信息*/
            $this->_get_user_info();

            /* 当前所处子菜单 */
            $this->_curmenu('edit_password');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_password'));
        	/* edit by lihuoliang 2011/08/24 (修改后台方式获取模板)*/
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
            /* 两次密码输入必须相同 */
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

            /* 修改密码 */
            $ms =& ms();    //连接用户系统
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'password'  => $new_password
            ));
            if (!$result)
            {
                /* 修改不成功，显示原因 */
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
     *  支付密码
     *
     *    @author    Hyber
     *    @usage    none
     */
    function passwordPayment(){
        $user_id = $this->visitor->get('user_id');
        $member_mod = &m('member');
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('edit_passwordpayment'));

            /* 当前用户中心菜单 */
            $this->_curitem('paypassword');
            /* 当前用户基本信息*/
            $this->_get_user_info();

            /* 当前所处子菜单 */
            $this->_curmenu('edit_passwordpayment');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_password'));
            $member_info = $member_mod->get($user_id);
            $this->assign('member',$member_info);
        	/* edit by lihuoliang 2011/08/24 (修改后台方式获取模板)*/
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
				$this->show_warning('你没有输入手机验证码!');
				return;
			}
			if (!$new_password)
			{
				$this->show_warning('请输入新的交易密码!');
				return;
			}
			if ($new_password != $re_passowrd)
			{
				$this->show_warning('两次输入的密码不一致!');
				return;
			}
			if ($verify != $_SESSION['smsverifydata']['verify'])
			{
				$this->show_warning('手机验证码输入不正确!');
				return;
			}
			//写入信息
			$userObj = & ms();
			$userObj->user->updateTraderAuth($user_id, $new_password, $re_passowrd);
			$this->show_message('修改支付密码成功!.');
        }	
    }
    /**
     *    修改电子邮箱
     *
     *    @author    Hyber
     *    @usage    none
     */
    function email(){
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('edit_email'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');
            /* 当前用户基本信息*/
            $this->_get_user_info();

            /* 当前所处子菜单 */
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

            $ms =& ms();    //连接用户系统
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
     *    修改手机号
     *
     *    @author   lihuoliang
     *    @usage    none
     */
    function mobile(){
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('edit_mobile'));

            /* 当前用户中心菜单 */
            $this->_curitem('phonepassword');
            /* 当前用户基本信息*/
            $this->_get_user_info();

            /* 当前所处子菜单 */
            $this->_curmenu('edit_mobile');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_mobile'));
        	/* edit by lihuoliang 2011/08/24 (修改后台方式获取模板)*/
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

            /* 修改手机号 */
            $ms =& ms();    //连接用户系统
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'mobile'  => $new_mobile
            ));
            if (!$result)
            {
                /* 修改不成功，显示原因 */
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
     * Feed设置
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
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('feed_settings'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
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
     *    三级菜单
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
     * 上传头像
     *
     * @param int $user_id
     * @return mix false表示上传失败,空串表示没有上传,string表示上传文件地址
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
     *    AJAX发送短信验证码
     *
     *    @author    lihuoliang
     *    @return    string
     */
    function send_sms_verify()
    {
    	//获取手机号码
        $mobile = empty($_POST['mobile']) ? null : trim($_POST['mobile']);

        if (!$mobile)
        {
            echo 1;  //手机号码为空
        }else
        {
        	if (is_mobile($mobile))
        	{
        		$smslog =&  m('smslog'); 
	        	$todaysmscount = $smslog->get_today_smscount($mobile); //当天短信的发送总量
	        	
				if ($todaysmscount>=5)
				{
					echo 6;
					return;
        		}
        		$ms =& ms();    //连接用户系统
        		$info = $ms->user->get($mobile,false,true);//判断手机号码是否存在
        		if ($info)
        		{
        			echo 3;
        		}else 
        		{
        			//由于虚拟主机中php运行环境暂时未配置开启soap扩展，所以暂时不使用webservice方式
        			import('class.smswebservice');    //导入短信发送类
        			$sms = SmsWebservice::instance(); //实例化短信接口类
        			$verify = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);//验证码
        			$verifytype = $_GET['verifytype']?$_GET['verifytype']:'register_verify'; //短信验证码类型
        			if ($verifytype=='modifymobile')
        			{
        				$smscontent = str_replace('{verify}',$verify,Lang::get('smscontent.modifymobile_verify'));
        			}else 
        			{
        				$smscontent = str_replace('{verify}',$verify,Lang::get('smscontent.register_verify'));
        			}
					//echo "OK";
        			$result= $sms->SendSms2($mobile,$smscontent); //执行发送短信验证码操作
        			//短信发送成功
        			if ($result == 0) 
        			{
        				//将验证码写入SESSION
        				$time = time();
        				$_SESSION['smsverifydata']['mobile'] =  $mobile;
        				$_SESSION['smsverifydata']['verify'] =  $verify;
        				$_SESSION['smsverifydata']['dateline'] =  $time;
        				//执行短信日志写入操作
        				$smsdata['mobile'] = $mobile;
        				$smsdata['smscontent'] = $smscontent;
        				$smsdata['type'] = $verifytype; //注册验证短信
        				$smsdata['sendtime'] = $time;
        				
        				$smslog->add($smsdata);
        				echo 4;
        			}else
        			{
	        			echo 5; //短信发送失败
        			}
        		}
        	}else 
        	{
        		echo 2; //非正确的手机号码
        	}
        }
        return;
    }
    
    /**
     *    检查手机号否存在
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
    	//检查用户是否绑定渠道
    	if(!$this->checkIsChannel()) { //未绑定
    		if(!IS_POST) {
    			/* 当前位置 */
	            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
	                             LANG::get('basic_information'));
	
	            /* 当前用户中心菜单 */
	            $this->_curitem('my_profile');
	
	            /* 当前所处子菜单 */
	            $this->_curmenu('basic_information');
    			$this->display("member.channelForm.html");
    		} else {
    			$uname = empty($_POST['uname']) ? '' : trim($_POST['uname']);
    			$password = empty($_POST['password']) ? '' : md5(trim($_POST['password']));
    			$sn = empty($_POST['sn']) ? '' : trim($_POST['sn']);
    			$channel_user_info = $channel_user_mod->get(array('conditions' => ' channel_name="'.$uname.'" AND password="'.$password.'" AND sn="'.$sn.'"'));
    			if(!$channel_user_info) {
    				$this->show_warning("未找到渠道信息, 请检查输入信息再试！");
    			} else {
    				if($channel_user_info['level'] != 3) {
    					$channel_fee_info = $channel_fee_mod->get(array('conditions' => " level=" . $channel_user_info['level'] . " AND area_id=" . $channel_user_info['area_id']));
	    				if(!$channel_fee_info) {
	    					$this->show_warning("渠道信息处理出错! 请重试,如无法解决,请联系客服!");
	    					return;
	    				}
	    				$user_info = $this->_member_mod->get($user_id);
	    				if($user_info['is_bind_channel'] == 1) {
	    					$this->show_warning("已绑定渠道,不能重复绑定!");
	    					return;
	    				}
	    				//写入channle_user 用户信息
	    				$channel_user_mod->edit($channel_user_info['channel_id'],array('sid' => $user_id));
	    				$this->_member_mod->edit($user_id,array('channel_id' => $channel_user_info['channel_id'] , 'is_bind_channel' => 1));
	    				
	    				$this->show_message('成功绑定派啦渠道商');	
    				} else {
    					$channel_fee_info = $channel_fee_mod->get(array('conditions' => " level=" . $channel_user_info['level'] . " AND area_id=" . $channel_user_info['area_id']));
	    				if(!$channel_fee_info) {
	    					$this->show_warning("渠道信息处理出错! 请重试,如无法解决,请联系客服!");
	    					return;
	    				}
    					$user_info = $this->_member_mod->get($user_id);
	    				if($user_info['is_bind_channel'] == 1) {
	    					$this->show_warning("已绑定渠道,不能重复绑定!");
	    					return;
	    				}
	    				$store_info = $store_mod->get($user_id);
	    				if(!$store_info) {
	    					$this->show_warning("此会员还不是商户,请先申请成为商户!");
	    					return;
	    				}
	    				if($store_info['is_bind_channel'] == 1) {
	    					$this->show_warning("已绑定渠道,不能重复绑定!");
	    					return;
	    				}
	    				//写入channle_user 用户信息
	    				$channel_user_mod->edit($channel_user_info['channel_id'],array('sid' => $this->visitor->info['store_id']));
	    				$store_mod->edit($this->visitor->info['store_id'],array('channel_id' => $channel_user_info['channel_id'] , 'is_bind_channel' => 1));
	    				$this->_member_mod->edit($user_id,array('channel_id' => $channel_user_info['channel_id'] , 'is_bind_channel' => 1));
	    				
	    				$this->show_message('成功绑定派啦渠道商');	
	    			}
    			}
    		}
    	} else {
    		/* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('basic_information'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
            $this->_curmenu('basic_information');
    		/* 个人账户信息 */
	    	$info = $this->get($user_id,false,false,true);
	    	
	    	$pms = $this->get_list($user_id, 100, 'newpm'); 
	    	$channelfee =& m('channelfee');    //加盟费用查询
	     	$fee = $channelfee->get(" level=" . $info['level'] . " and area_id = " . $info['area_id'] ."");
	     	//var_dump($pms['count']);exit;
	    	$this->assign('count', $pms['count']); 		    	   
	        $this->assign('ip', $_SERVER["REMOTE_ADDR"]);   
	        $this->assign('user', $info);  
	        $this->assign('fee', $fee); 
	        /* 个人资料 */
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
     *  查看是当前用户是否绑定渠道
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
    
    //团购员基本信息
    function manager()
    { 
    	$user_id = $this->visitor->get('user_id'); //当前用户ID
    	$this->_curitem('manager');
    	/* 当前用户基本信息*/
        $this->_get_user_info();
    	$customerManager_mod = & m('customermanager');
    	$customerManager_info = $customerManager_mod->get('user_id = ' . $user_id);    	
	    if(!$customerManager_info)  //不是客户经理
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
    
    //派啦币交易
    function credit()
    {
    	date_default_timezone_set("Asia/Shanghai");
    	$this->_curitem('overview');
    	$user_id = $this->visitor->get('user_id'); //当前用户ID
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
    
	//派啦币交易发布
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
		$num=$_POST['opt'];//获取交易周期天数
		$cycle=$num * 24 * 60 * 60;//计算交易周期秒数
    	if (IS_POST){
    			if($credit_num > $cnum)
    			{
    				$this->show_warning('您帐号的派啦币数目不足当前发布数目！');
    				return;
    			}
    			if(!$credit_num)
    			{
    				$this->show_warning('您尚未输入要出售的PL币数目，请返回重新输入');
    				return;
    			}
    			if(!$price)
    			{
    				$this->show_warning('您尚未输入出售此PL币的价格，请返回重新输入');
    				return;
    			}
    			if(!$info)
    			{
    				$this->show_warning('您尚未输入发布信息，请返回重新输入');
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
	
	//出售信息修改
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
	
	//派啦币订单
	function credit_order()
	{
		$this->_curitem('overview');
		$credit_order_mod = & m('creditorder');
		//分页
		$page_num = 5;
    	$page = $this->_get_page($page_num);
		$user_id = $this->visitor->get('user_id');
		$this->assign('user_id',$user_id);
		$order_sn = empty($_GET['order_sn']) ? 0 : intval($_GET['order_sn']);
		//下单时间条件
		$add_time_from=empty($_GET['add_time_from']) ? 0 : strtotime($_GET['add_time_from']);//将获取的date时间转换成时间戳的形式
		$add_time_to=empty($_GET['add_time_to']) ? 0 : strtotime($_GET['add_time_to']);
		$status=empty($_GET['status']) ? 0 : intval($_GET['status']);
		$opt=$_GET['opt'];
		$conditions = " 1 = 1 and o.buyer_id = " .$user_id . " or o.seller_id = " .$user_id;
		//订单状态
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
				$this->show_warning("订单状态查找程序出错！");
				break;
		}
		//订单号
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
				$this->show_warning("查找程序出错！");
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
	
    public function withdraw() //提现
    {
    	$this->_curitem('my_profile');
    	$user_id = $this->visitor->get('user_id');
    	//用户信息
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
    			$this->show_warning('请填写提现金额!');
    			return;
    		}
    		if($_POST['draw_bank'] == '')
    		{
    			$this->show_warning('开户行不能为空!');
    			return;
    		}
    		if($_POST['draw_name'] == '')
    		{
    			$this->show_warning('写开户名不能为空!');
    			return;
    		}
    		if($_POST['draw_accounts'] == '')
    		{
    			$this->show_warning('银行账号不能为空!');
    			return;
    		}
    		if ($_SESSION['withdraw']['verify'] != $_POST['verify']) //验证不通过
    		{
    			$this->show_warning('验证码输入不正确!');
    			return;
    		}   		
    		if($_SESSION['withdraw']['verify'] == '' || $_SESSION['withdraw']['verify'] == null)
    		{	
    			$this->show_warning('验证码不能为空!');
    			return;
    		}
    		//验证自身是否有足够资金可供提现
    		$draw_amount = floatval($_POST['withdraw_amount']);
    		if ($draw_amount > $member_info['money']) 
    		{
    			$this->show_warning('您提现的金额超出您所有的金额!');
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
    			$this->show_warning('申请失败, 请重试!');
    			return;
    		}
    		//会员余额更新
    		changeMemberCreditOrMoney($user_id,$draw_amount,FROZEN_MONEY);
			//更新操作记录日志
			$param = array(
		        	'user_id' => $user_id,
		        	'frozen_money' => $draw_amount,
					'change_time' => time(),
		            'change_desc' => "会员申请提现:".$draw_amount.",系统冻结余额：".$draw_amount,
		            'change_type'	=> 4,
		        );
			add_account_log($param);
	   		$this->show_message('申请成功,操作人员将会对您的申请进行审核!.');
    	}
    }
    
	public function sendDrawVerity() {
		//获取手机号码
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
            echo 1;  //手机号码为空
        }else
        {
        	if (is_mobile($mobile))
        	{
        		$smslog =&  m('smslog'); 
	        	$todaysmscount = $smslog->get_today_smscount($mobile); //当天短信的发送总量
	        	
				if ($todaysmscount>=5)
				{
					echo 6;
					return;
        		}
        		//由于虚拟主机中php运行环境暂时未配置开启soap扩展，所以暂时不使用webservice方式
        		import('class.smswebservice');    //导入短信发送类
        		$sms = SmsWebservice::instance(); //实例化短信接口类
        		$verify = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);//验证码
        		$verifytype = 'withdraw'; //短信验证码类型 , 这里是团购点推荐
        		$smscontent = "尊敬的客户，这里是我们的团购点提现验证，您的验证码为【{$verify}】，请确定目前是由您本人操作,快乐购物、快捷支付尽在www.paila100.com【派啦网】" ;
				
        		$result= $sms->SendSms2($mobile,$smscontent); //执行发送短信验证码操作
        		//短信发送成功
        		if ($result == 0) 
        		{
        			//将验证码写入SESSION
        			$time = time();
        			$_SESSION['withdraw']['mobile'] =  $mobile;
        			$_SESSION['withdraw']['verify'] =  $verify;
        			$_SESSION['withdraw']['dateline'] =  $time;
        			//执行短信日志写入操作
        			$smsdata['mobile'] = $mobile;
        			$smsdata['smscontent'] = $smscontent;
        			$smsdata['type'] = $verifytype; 
        			$smsdata['sendtime'] = $time;
        			
        			$smslog->add($smsdata);
        			echo 4;
        		}else
        		{
        			echo 5; //短信发送失败
        		}
        	}else 
        	{
        		echo 2; //非正确的手机号码
        	}
        }
        return;
	}
	//派啦币付款，以及账户余额付款
	public function paypl(){
		$this->_curitem('paypl');
		$user_id = $this->visitor->get('user_id');
    	//用户信息
    	$member_info = $this->_member_mod->getRow("select *,m.money from pa_member m where m.user_id=".$user_id);
		//var_dump($member_info);
		$loseid = $member_info['user_id'];

		$loseprice = empty($_POST['price']) ? 0 : intval($_POST['price']);
		if(IS_POST){
			if (!$_POST['user_name'])
			{
				$this->show_warning('用户名不能为空 !');
				return;
			}
			if (!$_POST['user_id'])
			{
				$this->show_warning('账号不能为空 !');
				return;
			}
			if (!$_POST['price'])
			{
				$this->show_warning('金额不能为空 !');
				return;
			}
			$user_name=$_POST['user_id'];
			$member_gaininfo = $this->_member_mod->getRow("select * from pa_member where user_name='".$user_name."'");
			if ($member_gaininfo == false)
			{
				$this->show_warning('付款人不存在 !');
				return;
			}
			if ($member_gaininfo['real_name'] != $_POST['user_name'])
			{
				$this->show_warning('请输入正确的付款人姓名!');
				return;
			}
			$gainprice = empty($member_gaininfo['credit']) ? 0 : intval($member_gaininfo['credit']);
			$gainid = $member_gaininfo['user_id'];
			
		}
		$this->display("member.pay.html");
	}

    //查询收益详细
    public function selGainsDetail() 
    {
    	$this->_curitem('my_profile');
    	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	if ($id == 0)
    	{
    		$this->show_warning('查询的收益记录不存在 !');
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
    //推荐成为客户经理
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
	        	$this->show_message("对不起,您所推荐的用户不存在 ! 请检查是否填写错误!");
	        	return;
	        }
	    	$user_id = $user_info['user_id'];
	    	$this->_curitem('my_profile');
	    	//检查是否有2000PL
	    	$customerLevel_mod = & m('customerlevel');
	    	$customerLevel_info = $customerLevel_mod->get($level);	
	    	
	 		//本人级别
	 		$customerManager_mod = & m('customermanager');
	 		$customerAsk_mod = & m('customerask');
	 		$customerManager_info = $customerManager_mod->get('user_id = ' . $user_id);
	 		$customerAsk_info = $customerAsk_mod->get('user_id = ' . $user_id);
	 		//推荐人信息
	 		$recomManager_info = $customerManager_mod->get('user_id = ' . $this->visitor->get('user_id'));
	 		//是否已经在申请
	 		if ($customerAsk_info) 
	 		{
	 			$this->show_warning('您的申请正在审核中..! 请不要再次申请 !');
	 			return;
	 		}

	 		if (!$_POST['real_name'])
    		{
    			$this->show_warning('请填写您的真实姓名 !');
    			return;
    		}
    			
    		if (!$_POST['region_id'])
    		{
    			$this->show_warning('请选择你所在的地区!');
    			return;
    		}
    		if (!$_FILES['identity_card'])
    		{
    			$this->show_warning('请上传您的身份证照片!');
    			return;
    		}
    		
    		if ($_POST['verify'] != $_SESSION['recomManager']['verify'])
    		{
    			$this->show_warning('您输入的验证码不正确!');
    			return;
    		}
    		//将上传的文件移动到新位置
    		//定义文件上传位置
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
	    		$this->show_warning('提交申请失败!.');
	    		return;
	    	}
	    	
	    	$this->show_message('提交申请成功!','go_back','index.php?app=member&act=manager');
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
	 * ajax 发送短信
	 */
	public function sendManagerVerify() {
		//获取手机号码
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
	        	$todaysmscount = $smslog->get_today_smscount($mobile); //当天短信的发送总量
	        	
				if ($todaysmscount>=5)
				{
					$this->json_error("message_only_five");
					return;
        		}
        		//由于虚拟主机中php运行环境暂时未配置开启soap扩展，所以暂时不使用webservice方式
        		import('class.smswebservice');    //导入短信发送类
        		$sms = SmsWebservice::instance(); //实例化短信接口类
        		$verify = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);//验证码
        		$verifytype = 'recomManager'; //短信验证码类型 , 这里是团购点推荐
        		$smscontent = "尊敬的客户，这里是我们的团购点推荐验证，您的验证码为【{$verify}】，请确定目前是由您本人操作,快乐购物、快捷支付尽在www.paila100.com【派啦网】" ;
				
        		$result= $sms->SendSms2($mobile,$smscontent); //执行发送短信验证码操作
        		//短信发送成功
        		if ($result == 0) 
        		{
        			//将验证码写入SESSION
        			$time = time();
        			$_SESSION['recomManager']['mobile'] =  $mobile;
        			$_SESSION['recomManager']['verify'] =  $verify;
        			$_SESSION['recomManager']['dateline'] =  $time;
        			//执行短信日志写入操作
        			$smsdata['mobile'] = $mobile;
        			$smsdata['smscontent'] = $smscontent;
        			$smsdata['type'] = $verifytype; 
        			$smsdata['sendtime'] = $time;
        			
        			$smslog->add($smsdata);
        			$this->json_result($mobile, "send_message_end");
        			return;
        		}else
        		{
        			$this->json_error('send_message_field'); //短信发送失败
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
    	/* 当前用户基本信息*/
       	$this->_get_user_info();
    	/* 当前所处子菜单 */
        $this->_curmenu('basic_ask');
    	
    	$user_info = $this->_member_mod->get($user_id);
    	$customerLevel_mod = & m('customerlevel');
 		//本人级别
 		$customerManager_mod = & m('customermanager');
 		$customerAsk_mod = & m('customerask');
 		$customerManager_info = $customerManager_mod->get('user_id = ' . $user_id);
 		$customerAsk_info = $customerAsk_mod->get('user_id = ' . $user_id);
 		//是否已经在申请
 		if ($customerAsk_info) 
 		{
 			$this->show_warning('您的申请正在审核中..! 请不要再次申请 !');
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
		    			$this->show_message("用户没有邀请您为团购员，请不要用别人的链接地址");
		    			return;
		    		}
		    		$manage = $customerManager_mod->get(intval($ids['0']));
		    		if (!$manage)
		    		{
		    			$this->show_message("邀请人不是团购员");
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
    			//验证
    			if (!$_POST['real_name'])
    			{
    				$this->show_warning('请填写您的真实姓名 !');
    				return;
    			}
    			
    			if (!is_mobile($_POST['mobile']))
    			{
    				$this->show_warning('你的联系电话填写不合法!');
    				return;
    			}
    			if (!$_POST['region_id'])
    			{
    				$this->show_warning('请选择你所在的地区!');
    				return;
    			}
    			if (!$_FILES['identity_card'])
    			{
    				$this->show_warning('请上传您的身份证照片!');
    				return;
    			}
    			if (!$_POST['card_number'])
    			{
    				$this->show_warning('请输入银行账号!');
    				return ;
    			}
    			if (!$_POST['bank_name'])
    			{
    				$this->show_warning('请输入开户行!');
    				return ;
    			}else {
    				$bank = get_bank();
    				if(empty($bank[$_POST['bank_name']]))
    				{
    					$this->show_warning("选择开户行出错!");
    					return ;
    				}
    			}
    			//邀请人信息
    			if(!$_POST['inviter_param'])
    			{
    				$inviter_id = CHANNEL_ID;
    				$inviter_info=$customerManager_mod->get($inviter_id);
    			}else {
    			   	$user_name = $_POST['inviter_param'];
		    		$ms =& ms();
			    	$info = $ms->user->get($user_name, true);
					
			        //当使用用户名找不到会员信息的时候----使用手机号验证
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
		                $this->show_message("推荐人不存在，或者输入错误！");
		                return;
		            }
		            
		            if ($inv_id==$user_id)
		            {
		            	$this->show_message("自己不能推荐自己！");
		                return;
		            }
		            
			        $inviter_info=$customerManager_mod->get($inv_id);
			        if (!$inviter_info)
			        {
			        	$this->show_message("您填写的推荐人不是团购员，不能推荐您成为团购员！");
		                return;
			        }
    			}
    			//将上传的文件移动到新位置
    			//定义文件上传位置
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
    		$this->show_warning('您已经是团购员不能提交申请 !');
 			return;
    	}
    	if (!$customerAsk_mod->add($data))
    	{
    		$this->show_warning('提交申请失败!.');
    		return;
    	}    	
    	$this->show_message('提交申请成功!','go_back','index.php?app=member');
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
    	$user_id = $this->visitor->get('user_id'); //当前用户ID
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
    		$this->show_message("你目前还不是团购员，不能邀请其他用户！");
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
			
	        //当使用用户名找不到会员信息的时候----使用手机号验证
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
                $this->show_message("被邀请人不存在，或者输入错误！");
                return;
            }
            
            if ($inv_id==$user_id)
            {
            	$this->show_message("不能邀请自己！");
                return;
            }
            
	        $invit_manager=$cust_manager_mod->get($inv_id);
	        if ($invit_manager)
	        {
	        	$this->show_message("被邀请人已经是团购员，不能被邀请！");
                return;
	        }
	        
    		$intNum = $this->invitNum($user_id,$inv_id);
    		$inturl = SITE_URL."/index.php?app=member&act=askForManager&invitecode=".$intNum;
    		$int_info = $this->_member_mod->get($inv_id);
    	    /* 连接用户系统 */
            $ms =& ms();
            $msg_id = $ms->pm->send($user_id,$inv_id, '邀请团购员', "尊敬的".$int_info['user_name']."，".$this->visitor->get(user_name)."，邀请您成为团购员,\<a href=\"$inturl\"\>\<b\>点击此链接\</b\>\</a\>："."填写您的基本信息");
    		if(!msg_id)
    		{
    			$this->show_warning("邀请团购员失败");
    			return ;
    		}else {
    			$this->show_message('邀请成功', '继续邀请' , 'index.php?app=member&act=invitgroup');
    			return ;
    		}
    	}
    	
    }
    //团购员邀请码生成函数
    function invitNum($user_id,$inv_id)
    {
    	return base64_encode($user_id.",".$inv_id);
    }
  	function uninvitgroup()
  	{
  		$this->_curitem('uninvitgroup');
  		/* 当前用户基本信息*/
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
			$page = $this->_get_page();   //获取分页信息
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
        	$this->show_message('对不起，此功能暂未开通！');
        	return ;
        }
  		$this->_curitem('carry');
  		/* 当前用户基本信息*/
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
			
	        //当使用用户名找不到会员信息的时候----使用手机号验证
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
                $this->show_message('收款人账号不存在');
                return;
            }
			if($inv_id == $user_id)
			{
				$this->show_message('不能自己付款给自己');
				return ;
			}
        	if($ca_type != 1)
        	{
        		$this->show_message("请选择付款方式");
        		return ;
        	}
        	if(0 == $money)
        	{
        		$this->show_message("请填写正确的金额");
        		return ;
        	}
			$paypw = $ms->user->traderAuth($user_id,$paypassword);
			if(!$paypw)
			{
				$this->show_warning("支付密码有误");
				return ;
			}
        	$member =& m('member');
        	$member_info = $member->get($user_id);
			switch ($ca_type)
			{
				case 1 : 
						if($money > $member_info['money'])
						{
							$this->show_message('付款金额不能大于自己的余额');
							return ;
						}
						changeMemberCreditOrMoney(intval($member_info['user_id']),$money,SUBTRACK_MONEY);
						changeMemberCreditOrMoney(intval($inv_id),$money,ADD_MONEY);
						$pdata = array(
							'user_id' => intval($member_info['user_id']),
							'user_money'    => -$money,
							'change_time'	=> time(),
							'change_desc'	=> '余额转账给会员：'.$info['user_name'].'，￥'.$money.'元',
							'change_type'	=> '42',
						);
						$gdata = array(
							'user_id'       => intval($info['user_id']) ,
							'user_money'    => $money,
							'change_time'	=> time(),
							'change_desc'	=> '收到会员：'.$member_info['user_name'].'，转账余额￥'.$money.'元',
							'change_type'	=> '44',
						);
						add_account_log($pdata);
						add_account_log($gdata);
			            $pmsg_id = $ms->pm->send(MSG_SYSTEM,$member_info['user_id'], '转账通知', "尊敬的用户".$member_info['user_name']."，您转账给会员".$info['user_name']."已经成功，转账金额￥".$money.'元');
			            $gmsg_id = $ms->pm->send(MSG_SYSTEM,$info['user_id'], '收款通知', "尊敬的用户".$info['user_name']."，会员".$member_info['user_name']."给您转账成功，您的余额账户增加金额￥".$money.'元');
				break;
			}
			$this->show_message('付款成功!','继续付款','index.php?app=member&act=carry');
        }
  	}
  	/***
  	 * 获取团购员查询条件
     * @author xiaoyu
     * @return array
  	 ****/
  	function _get_params()
  	{
  		$page = $this->_get_page();   //获取分页信息
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
  				if (trim($_GET['pub']) == '初级')
  				{
  					$lv = 1;
  				}
  				if (trim($_GET['pub']) == '中级')
  				{
  					$lv = 2;
  				}
  				if (trim($_GET['pub']) == '高级')
  				{
  					$lv = 3;
  				}
  				if (trim($_GET['pub']) == '顶级')
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
    //团购员查看收益
    public function selGains()
    {
    	$this->_curitem('selGains');
    	$user_id = $this->visitor->get('user_id');
    	/* 当前用户基本信息*/
       	$this->_get_user_info();
    	$conditions = "1 = 1";
		$param = array(
			50 => '团购员拉取广告费收益',
			51 => '团购员推荐店铺收益',
		//	52 => '团购员购买大礼包收益',
			53 => '会员购物团购员返利收益'
		);
		if(empty($_GET['selg_id']))
		{
			$conditions .= " and change_type in (50,51,52,53) ";
		}else {
			$conditions .= " and change_type = ".$_GET['selg_id'];
			$this->assign('selg_id',$_GET['selg_id']);
		}
    	import('Page.class');
        $count = $this->_get_account($conditions,false,true); //总条数
        $listRows= 10;        //每页显示条数
        $page=new Page($count,$listRows); //初始化对象
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
	/* 检查商品分类：添加、编辑商品表单验证时用到 */
    function check_mgregion()
    {
        $region_id = isset($_GET['region_id']) ? intval($_GET['region_id']) : 0;

        echo ecm_json_encode($this->_check_mgregion($region_id));
    }
    /**
     * 检查商品分类（必选，且是叶子结点）
     *
     * @param   int     $cate_id    商品分类id
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
