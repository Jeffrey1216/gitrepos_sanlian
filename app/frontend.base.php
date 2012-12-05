<?php

/**
 *    ǰ̨������������
 *
 *    @author    Garbin
 *    @usage    none
 */
class FrontendApp extends ECBaseApp
{
    function __construct()
    {
        $this->FrontendApp();
    }
    function FrontendApp()
    {
        Lang::load(lang_file('common'));
        Lang::load(lang_file(APP));
        parent::__construct();

        // �ж��̳��Ƿ�ر�
        if (!Conf::get('site_status'))
        {
            $this->show_warning(Conf::get('closed_reason'));
            exit;
        }
        # ������action֮ǰ���޷����ʵ�visitor����
    }
    function _config_view()
    {
        parent::_config_view();
        $this->_view->template_dir  = ROOT_PATH . '/themes';
        $this->_view->compile_dir   = ROOT_PATH . '/temp/compiled/mall';
        $this->_view->res_base      = SITE_URL . '/themes';
        $this->_config_seo(array(
            'title' => Conf::get('site_title'),
            'description' => Conf::get('site_description'),
            'keywords' => Conf::get('site_keywords')
        ));
    }
    function display($tpl)
    { 
    	$this->assign('user_id',$this->visitor->get('user_id'));
    	$this->assign('navigator', $this->navigator());
    	$credit_cart = & m('creditcart');
    	$credit_cart_num = $credit_cart->getOne('select count(*) from pa_credit_cart where buyer_id = ' . $this->visitor->get('user_id'));
    	$this->assign('credit_cart_num', $credit_cart_num);
        $cart =& m('cart');
        //$plb_cart = & m('creditcart');
        //$this->assign('credit_cart_goods_kinds',$plb_cart->get_credit_kinds(SESS_ID, $this->visitor->get('user_id')));
        $this->assign('cart_goods_kinds', $cart->get_kinds(SESS_ID, $this->visitor->get('user_id')));   
        $navs = $this->_get_navs();
        $this->assign('middle', $navs['middle']);//�Զ��嵼��
        $this->assign('acc_help', ACC_HELP);        // �������ķ���code
        $this->assign('site_title', Conf::get('site_title'));
        $this->assign('site_logo', Conf::get('site_logo'));
        $this->assign('statistics_code', Conf::get('statistics_code')); // ͳ�ƴ���
        $this->assign('navs',$this->_get_agro_nav());
        /*ũҵר��*/
        $this->assign('agcate',$this->_get_agro_nav());
        /* �������� */
        $this->assign('hot_keywords', $this->_get_hot_keywords());
        $this->assign('notic', $this->not());
        //���ӵײ����°���
        $article_mod = & m("article");
        $acategory_mod = & m("acategory");
        //������· 
        $freshman = $article_mod->find(array('conditions'=>'if_show=1 AND store_id=0 AND code = "" and cate_id=4 '));
        $this->assign('freshman',$freshman);
        //���ͷ�ʽ
        $delivery = $article_mod->find(array('conditions'=>'if_show=1 AND store_id=0 AND code = "" and cate_id=8 '));
        $this->assign('delivery',$delivery);
        //֧����ʽ
        $payment_info_foot = $article_mod->find(array('conditions'=>'if_show=1 AND store_id=0 AND code = "" and cate_id=9 '));
        $this->assign('payment_info_foot',$payment_info_foot);
        //�ۺ����
        $sale = $article_mod->find(array('conditions'=>'if_show=1 AND store_id=0 AND code = "" and cate_id=10 '));
        $this->assign('sale',$sale);
        
        $flink = &m("friend_link");
        $appt = $_GET['app'];
        $this->assign("imdir",IMAGE_URL);
        $this->assign('logo',IMAGE_URL);
        if($appt=="default"||$appt==""){
        //����ͼƬ����
        $tem = 1;
        $this->assign("tem",$tem);
        $flt = $flink->find(array('conditions'=>'type=2','limit'=>'10','order'=>'show_order ASC'));
        $this->assign("flt",$flt);
        $flx = $flink->find(array('conditions'=>'type=3','limit'=>'10','order'=>'show_order ASC'));
        $this->assign("flx",$flx);
        }
        //ȫվ��������
        $flz = $flink->find(array('conditions' =>'type=1','limit'=>'10','order'=>'show_order ASC'));
        $this->assign("flz",$flz);
        //������Ϣ
        $helpInfo = $article_mod->find(array('conditions'=>'if_show=1 AND store_id=0 AND code = "" and cate_id=11 '));
        $this->assign('helpInfo',$helpInfo);
        //�ײ�����
        $ACC = $acategory_mod->get_ACC();
        $about = $article_mod->find(array('conditions' => 'cate_id='.$ACC[ACC_ABOUT],'fields' => 'title','order' => 'sort_order'));
        $this->assign('about',$about);
        //���ӵײ��������ӵ����ݶ�ȡ
        $this->assign('partnerdata', $this->get_partnerdata()); //��������
        $current_url = explode('/', $_SERVER['REQUEST_URI']);
        $count = count($current_url);
        $this->assign('current_url',  $count > 1 ? $current_url[$count-1] : $_SERVER['REQUEST_URI']);// �������õ���״̬(�Ժ���ܻ�������)
        parent::display($tpl);
    }
    
    public function navigator()
    {
    	$gcategory_mod =& bm('gcategory', array());
        $gcategories = $gcategory_mod->get_list(-1, true);
       	import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        $data = $tree->getArrayListNav(0);
        //�޸�$data  �����Ƽ��ͻ
        $brand_mod = & m('brand');
        $specialSubject_mod = & m('specialsubject');
        foreach($data as $k => $v) {
          	$data[$k]['brand_info'] = $brand_mod->find(array('conditions' => 'cate_id='.$v['id']));
            $data[$k]['specialsubject_info'] = $specialSubject_mod->find(array('conditions' => 'cate_id='.$v['id']));
        }
        return $data;
    }
    
	function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }
    /**
     *    ��ȡ������������
     *
     *    @author    lihuoliang
     *    @param	 $num    �������ӵ�����
     *    @return    array
     */
	function get_partnerdata($num = 0)
    {
        if ($num == 0)
        {
            $num = 10;
        }

        $cache_server =& cache_server(); //���ò���ʼ�����������ʵ��
        $key = 'partnerdata';			 //���û����ļ���Ψһ��ʶkey
        $cachetime = 86400;			     //���û����ʱ�䣨��λ�룩
        $data = $cache_server->get($key);//��ȡkey��Ӧ�Ļ�������
        if($data === false)
        {
            $partner_mod =& m('partner');
            $data = $partner_mod->find(array(
                'conditions' => "store_id = 0",
                'order' => 'sort_order',
                'limit' => $num,
            ));
            $cache_server->set($key, $data, $cachetime);  //����д�뻺������
        }

        return $data;
    }
    /***
     * 	��ȡũҵר��ͷ�ļ�������ͷ��
     * 	@author    xioyu
     * 	@return    string
     * ***/
    function _get_agro_nav()
    {
    	$gcategroy_mod = & m('gcategory');
    	$sql = "SELECT g.cate_id,g.cate_name from pa_gcategory g  where g.parent_id in
    	       (select g.cate_id from pa_gcategory g where g.parent_id=0 and g.mall_type=1)";
    	$agro_navs = $gcategroy_mod->getAll($sql); 
    	return $agro_navs;
    }
    function login()
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
            /* ��ֹ��½�ɹ�����ת����½���˳���ҳ�� */
            $ret_url = strtolower($ret_url);            
            if (str_replace(array('act=login', 'act=logout',), '', $ret_url) != $ret_url)
            {
                $ret_url = SITE_URL . '/index.php';
            }

            if (Conf::get('captcha_status.login'))
            {
                $this->assign('captcha', 1);
            }
            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js'));
            $this->assign('ret_url', rawurlencode($ret_url));
            $this->_curlocal(LANG::get('user_login'));
            $this->_config_seo('title', Lang::get('user_login') . ' - ' . Conf::get('site_title'));
            $this->display('login.html');
            /* ͬ���˳��ⲿϵͳ */
            if (!empty($_GET['synlogout']))
            {
                $ms =& ms();
                echo $synlogout = $ms->user->synlogout();
            }
        }
        else
        {
        	/* �ж��Ƿ����˵������֤�룬����������ж���֤���Ƿ���ȷ */
            if (Conf::get('captcha_status.login') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
            {
                $this->show_warning('captcha_failed');

                return;
            }

            $user_name = trim($_POST['user_name']);
            $password  = $_POST['password'];

            $ms =& ms();
            $user_id = $ms->user->auth($user_name, $password);
            if (!$user_id)
            {
                /* δͨ����֤����ʾ������Ϣ */
                $this->show_warning($ms->user->get_error());

                return;
            }
            else
            {
                /* ͨ����֤��ִ�е�½���� */
                $this->_do_login($user_id);

                /* ͬ����½�ⲿϵͳ */
                $synlogin = $ms->user->synlogin($user_id);
            }
			//Lang::get('login_successed') . $synlogin,
            $this->show_message(Lang::get('login_successed') . $synlogin,
                'back_before_login', rawurldecode($_POST['ret_url']),
                'enter_member_center', 'index.php?app=member'
            );
        }
    }

    function pop_warning ($msg, $dialog_id = '',$url = '')
    {
        if($msg == 'ok')
        {
            if(empty($dialog_id))
            {
                $dialog_id = APP . '_' . ACT;
            }
            if (!empty($url))
            {
                echo "<script type='text/javascript'>window.parent.location.href='".$url."';</script>";
            }
           
            echo "<script type='text/javascript'>window.parent.js_success('" . $dialog_id ."');</script>";
        }
        else
        {
            header("Content-Type:text/html;charset=".CHARSET);
            $msg = is_array($msg) ? $msg : array(array('msg' => $msg));
            $errors = '';
            foreach ($msg as $k => $v)
            {
                $error = $v[obj] ? Lang::get($v[msg]) . " [" . Lang::get($v[obj]) . "]" : Lang::get($v[msg]);
                $errors .= $errors ? "<br />" . $error : $error;
            }
            echo "<script type='text/javascript'>window.parent.js_fail('" . $errors . "');</script>";
        }
    }

    function logout()
    {
        $this->visitor->logout();
        //���session
        unset($_SESSION['clientshop']);

        /* ��ת����¼ҳ��ִ��ͬ���˳����� */
        header("Location: index.php?app=member&act=login&synlogout=1");
        return;
    }

    /* ִ�е�¼���� */
    function _do_login($user_id)
    {
        $mod_user =& m('member');

        $user_info = $mod_user->get(array(
            'conditions'    => "user_id = '{$user_id}'",
            'join'          => 'has_store',                 //�������ҿ����Ƿ��е���
            'fields'        => 'user_id, user_name, reg_time, last_login, last_ip, store_id, mobile',
        ));

        /* ����ID */
        $my_store = empty($user_info['store_id']) ? 0 : $user_info['store_id'];

        /* ��֤������������ */
        //unset($user_info['store_id']);

        /* ������� */
        $this->visitor->assign($user_info);

        /* �����û���¼��Ϣ */
        $mod_user->edit("user_id = '{$user_id}'", "last_login = '" . gmtime()  . "', last_ip = '" . real_ip() . "', logins = logins + 1");

        /* ���¹��ﳵ�е����� */
        $mod_cart =& m('cart');
        $mod_cart->edit("(user_id = '{$user_id}' OR session_id = '" . SESS_ID . "') AND store_id <> '{$my_store}'", array(
            'user_id'    => $user_id,
            'session_id' => SESS_ID,
        ));

        /* ȥ���ظ����� */
        $cart_items = $mod_cart->find(array(
            'conditions'    => "user_id='{$user_id}' GROUP BY spec_id",
            'fields'        => 'COUNT(spec_id) as spec_count, spec_id, rec_id',
        ));
        if (!empty($cart_items))
        {
            foreach ($cart_items as $rec_id => $cart_item)
            {
                if ($cart_item['spec_count'] > 1)
                {
                    $mod_cart->drop("user_id='{$user_id}' AND spec_id='{$cart_item['spec_id']}' AND rec_id <> {$cart_item['rec_id']}");
                }
            }
        }
    }

    /* ȡ�õ��� */
    function _get_navs()
    {
        $cache_server =& cache_server();
        $key = 'common.navigation';
        $data = $cache_server->get($key);
        $data = false;
        if($data === false)
        {
            $data = array(
                'header' => array(),
                'middle' => array(),
                'footer' => array(),
            );
            $nav_mod =& m('navigation');
            $rows = $nav_mod->find(array(
                'order' => 'type, sort_order',
            ));
            foreach ($rows as $row)
            {
                $data[$row['type']][] = $row;
            }
            $cache_server->set($key, $data, 86400);
        }
        return $data;
    }

    /**
     *    ��ȡJS������
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function jslang()
    {
        $lang = Lang::fetch(lang_file('jslang'));
        parent::jslang($lang);
    }

    /**
     *    ��ͼ�ص�����[��ʾС�Ҽ�]
     *
     *    @author    Garbin
     *    @param     array $options
     *    @return    void
     */
    function display_widgets($options)
    {
        $area = isset($options['area']) ? $options['area'] : '';
        $page = isset($options['page']) ? $options['page'] : '';
        if (!$area || !$page)
        {
            return;
        }
        include_once(ROOT_PATH . '/includes/widget.base.php');
        /* ��ȡ��ҳ��ĹҼ�������Ϣ */
        $widgets = get_widget_config($this->_get_template_name(), $page);
		
        /* ���û�и����� */
        if (!isset($widgets['config'][$area]))
        {
            return;
        }
        /* ���������ڵĹҼ�������ʾ���� */
        foreach ($widgets['config'][$area] as $widget_id)
        {
            $widget_info = $widgets['widgets'][$widget_id];
            $wn     =   $widget_info['name'];
            $options=   $widget_info['options'];

            $widget =& widget($widget_id, $wn, $options);
            $widget->display();
        }
    }

    /**
     *    ��ȡ��ǰʹ�õ�ģ������
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_template_name()
    {
        return 'default';
    }

    /**
     *    ��ȡ��ǰʹ�õķ������
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_style_name()
    {
        return 'default';
    }

    /**
     *    ��ǰλ��
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _curlocal($arr)
    {
        $curlocal = array(array(
            'text'  => Lang::get('index'),
            'url'   => SITE_URL . '/index.php',
        ));
        if (is_array($arr))
        {
            $curlocal = array_merge($curlocal, $arr);
        }
        else
        {
            $args = func_get_args();
            if (!empty($args))
            {
                $len = count($args);
                for ($i = 0; $i < $len; $i += 2)
                {
                    $curlocal[] = array(
                        'text'  =>  $args[$i],
                        'url'   =>  $args[$i+1],
                    );
                }
            }
        }

        $this->assign('_curlocal', $curlocal);
    }
    function _init_visitor()
    {
        $this->visitor =& env('visitor', new UserVisitor());
    }
	function not()
	{	
    	$article_mod = &m('article');
    	$n= $article_mod->find(array(
        	'join' => 'belongs_to_acategory',
        	'conditions' =>	'acategory.cate_id = 2 AND article.if_show=1',
        	'order'	=> 'article.add_time DESC',
        	'fields'	=> 'article.article_id,article.title,article.content,article.cate_id,article.add_time,acategory.cate_id,acategory.cate_name',
        	'limit' => 5 ,
        ));       
	    foreach($n as $value) {
	        foreach($value as $k=>$v) {
	        	if($k == 'add_time') {
	        		$value[$k] = date("y-m-d",$v);
	        		$notice[] = $value;
	        	}
	        }
        }

	    return $notice;
	}
}
/**
 *    ǰ̨������
 *
 *    @author    Garbin
 *    @usage    none
 */
class UserVisitor extends BaseVisitor
{
    var $_info_key = 'user_info';

    /**
     *    �˳���¼
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function logout()
    {
        /* �����ﳵ�е�������session_id��Ϊ�� */
        $mod_cart =& m('cart');
        $mod_cart->edit("user_id = '" . $this->get('user_id') . "'", array(
            'session_id' => '',
        ));

        /* �˳���¼ */
        parent::logout();
    }
}
/**
 *    �̳ǿ���������
 *
 *    @author    Garbin
 *    @usage    none
 */
class MallbaseApp extends FrontendApp
{
    function _run_action()
    {
        /* ֻ�е�¼���û��ſɷ��� */
        if (!$this->visitor->has_login && in_array(APP, array('apply')))
        {
            header('Location: index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

            return;
        }

        parent::_run_action();
    }

    function _config_view()
    {
        parent::_config_view();

        $template_name = $this->_get_template_name();
        $style_name    = $this->_get_style_name();

        $this->_view->template_dir = ROOT_PATH . "/themes/mall/{$template_name}";
        $this->_view->compile_dir  = ROOT_PATH . "/temp/compiled/mall/{$template_name}";
        $this->_view->res_base     = SITE_URL . "/themes/mall/{$template_name}/styles/{$style_name}";
    }

    /* ȡ��֧����ʽʵ�� */
    function _get_payment($code, $payment_info)
    {
        include_once(ROOT_PATH . '/includes/payment.base.php');
        include(ROOT_PATH . '/includes/payments/' . $code . '/' . $code . '.payment.php');	
        $class_name = ucfirst($code) . 'Payment';

        return new $class_name($payment_info);
    }

    /**
     *   ��ȡ��ǰ��ʹ�õ�ģ������
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_template_name()
    {
        $template_name = Conf::get('template_name');
        if (!$template_name)
        {
            $template_name = 'default';
        }

        return $template_name;
    }

    /**
     *    ��ȡ��ǰģ������ʹ�õķ������
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_style_name()
    {
        $style_name = Conf::get('style_name');
        if (!$style_name)
        {
            $style_name = 'default';
        }

        return $style_name;
    }
}

/**
 *    ����������ϵͳ������
 *
 *    @author    Garbin
 *    @usage    none
 */
class ShoppingbaseApp extends MallbaseApp
{
    function _run_action()
    {
        /* ֻ�е�¼���û��ſɷ��� */
        if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user')))
        {
            if (!IS_AJAX)
            {
                header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

                return;
            }
            else
            {
                $this->json_error('login_please');
                return;
            }
        }

        parent::_run_action();
    }
}
/**
 *    �û�������ϵͳ������
 *
 *    @author    Garbin
 *    @usage    none
 */
class MemberbaseApp extends MallbaseApp
{
    function _run_action()
    {
        /* ֻ�е�¼���û��ſɷ��� */
        if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user','send_sms_verify','check_mobile')))
        {
            if (!IS_AJAX)
            {
                header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

                return;
            }
            else
            {
                $this->json_error('login_please');
                return;
            }
        }

        parent::_run_action();
    }
    /**
     *    ��ǰѡ�еĲ˵���
     *
     *    @author    Garbin
     *    @param     string $item
     *    @return    void
     */
    function _curitem($item)
    {
        $this->assign('has_store', $this->visitor->get('has_store'));
        $this->assign('_member_menu', $this->_get_member_menu());
        $this->assign('_curitem', $item);
    }
    /**
     *    ��ǰѡ�е��Ӳ˵�
     *
     *    @author    Garbin
     *    @param     string $item
     *    @return    void
     */
    function _curmenu($item)
    {
        $_member_submenu = $this->_get_member_submenu();
        foreach ($_member_submenu as $key => $value)
        {
            $_member_submenu[$key]['text'] = $value['text'] ? $value['text'] : Lang::get($value['name']);
        }
        $this->assign('_member_submenu', $_member_submenu);
        $this->assign('_curmenu', $item);
    }
    /***
     *	��ȡ��ǰ��Ա������Ϣ
     *	@author xiaoyu 
     * 	@return string
     * 
     ***/
    function _get_user_info()
    {
    	$user = $this->visitor->get();
        $user_mod =& m('member');
        $info = $user_mod->get_info($user['user_id']);
        $profile['portrait'] = IMAGE_URL.$profile['portrait'];
        //$user['portrait'] = portrait($user['user_id'], $info['portrait'], 'middle');
        $user['money'] = $info['money'];
        $user['credit'] = $info['credit'];
        $user['time'] = $info['reg_time'];
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
    }
 	/***
     *	��ȡ��ǰ��Ա������Ϣ
     *	@author xiaoyu 
     * 	@return string
     * 
     ***/
    function _get_user_basic($type)
    {
    	$user_id = $this->visitor->get('user_id');
        $user_mod =& m('member');
        $info = $user_mod->get_info($user_id);
		return $info[$type];
    }
 	/***
     *	��ȡ��ǰ��Ա������Ϣ
     *	@author xiaoyu 
     * 	@return string
     * 
     ***/
    function _get_channel_basic()
    {
    	$user_id = $this->visitor->get('user_id');
        $user_mod =& m('customermanager');
        $info = $user_mod->get($user_id);
		return $info;
    }
 	/***
     *	��ȡ��ǰ����
     *	@author xiaoyu 
     * 	@return string
     * 
     ***/
    function _get_store_basic()
    {
    	$user_id = $this->visitor->get('user_id');
        $store_mod =& m('store');
        $info = $store_mod->get($user_id);
		return $info;
    }
    /**
     *    ��ȡ�Ӳ˵��б�
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_member_submenu()
    {
        return array();
    }
    /**
     *    ��ȡ�û�����ȫ�ֲ˵��б�
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_member_menu()
    {
        $menu = array();
		/* �������� */
		$menu['order_message'] = array(
		    'name' => '��������',
		    'text' => Lang::get('��������'),
		    'submenu' => array(
			'my_order' => array(
			    'text' => Lang::get('my_order'),
			    'url' => 'index.php?app=buyer_order',
			    'name' => 'my_order',
			    'icon' => 'ico5',
			),
			'cart' => array(
			    'text' => '�ҵĹ��ﳵ',
			    'url' => 'index.php?app=cart',
			    'name' => 'cart',
			),
			'adress_manager' => array(
			    'text' => '�ջ���ַ����',
			    'url' => 'index.php?app=my_address',
			    'name' => 'adress_manager',
			),
		    ),
		);

		/* �ҵ�PaiLa */
        $menu['my_ecmall'] = array(
            'name'  => '��Ա����',
            'text'  => Lang::get('��Ա����'),
            'submenu'   => array(
        /*      	 'overview'  => array(
                    'text'  => Lang::get('overview'),
                    'url'   => 'index.php?app=member',
                    'name'  => 'overview',
                    'icon'  => 'ico1',
                ),
                'my_profile'  => array(
                    'text'  => Lang::get('my_profile'),
                    'url'   => 'index.php?app=member&act=profile',
                    'name'  => 'my_profile',
                    'icon'  => 'ico2',
                ),  
                'message'  => array(
                    'text'  => Lang::get('message'),
                    'url'   => 'index.php?app=message&act=newpm',
                    'name'  => 'message',
                    'icon'  => 'ico3',
                ), 
                'friend'  => array(
                    'text'  => Lang::get('friend'),
                    'url'   => 'index.php?app=friend',
                    'name'  => 'friend',
                    'icon'  => 'ico4',
                ),  
                'my_credit'  => array(
                    'text'  => Lang::get('channel'),
                    'url'   => 'index.php?app=member&act=channel',
                    'name'  => 'channel',
                ),*/
               'my_profile'  => array(
                    'text'  => '�޸ĸ�������',
                    'url'   => 'index.php?app=member&act=profile',
                    'name'  => 'my_profile',
                ),
                'loginpassword'  => array(
                    'text'  => '�޸ĵ�¼����',
                    'url'   => 'index.php?app=member&act=password',
                    'name'  => 'loginpassword',
                ),
                 'paypassword'  => array(
                    'text'  => '�޸�֧������',
                    'url'   => 'index.php?app=member&act=passwordpayment',
                    'name'  => 'paypassword',
                ),
                  'phonepassword'  => array(
                    'text'  => '�޸İ��ֻ�����',
                    'url'   => 'index.php?app=member&act=mobile',
                    'name'  => 'phonepassword',
                ),
                   'askManager'  => array(
                    'text'  => '�����Ź�Ա',
                    'url'   => 'index.php?app=member&act=askForManager',
                    'name'  => 'askManager',
                ),
                
                
       			
                /*'my_favorite'  => array(
                    'text'  => Lang::get('my_favorite'),
                    'url'   => 'index.php?app=my_favorite',
                    'name'  => 'my_favorite',
                    'icon'  => 'ico6',
                ),
//                'plb_credit'  => array(
//                    'text'  => '�����ҽ���',
//                    'url'   => 'index.php?app=member&act=credit',
//                    'name'  => 'credit',
//                ),*/
                
               /* 
                 'my_manager'  => array(
                    'text'  => '�Ź�Ա����',
                    'url'   => 'index.php?app=member&act=manager',
                    'name'  => 'manager',
                ),
                'plb_credit'  => array(
                    'text'  => '�����ҽ���',
                    'url'   => 'index.php?app=member&act=credit',
                    'name'  => 'credit',
                ),
                'voucher_center'  => array(
                    'text'  => '��ֵ����',
                    'url'   => 'index.php?app=voucher_center',
                    'name'  => 'credit',
                ),
                
                /*'pay' => array(
                	'text' => '����',
                	'url'  => 'index.php?app=member&act=paypl',
                	'name' => 'paypl',
                ),*/
            ),
        );
        /*���׹���*/
		$menu['message'] = array(
        'name'  => '���׹���',
        'text'  => Lang::get('���׹���'),
		'submenu'   => array(
            'voucher_center'  => array(
                    'text'  => '��ݳ�ֵ',
                    'url'   => 'index.php?app=voucher_center',
                    'name'  => 'credit',
                ),
                    'detailed'  => array(
                    'text'  => '���׼�¼',
                    'url'   => 'index.php?app=member&act=detailed',
                    'name'  => 'detailed',
                ),
            ),
           );
           

                 
            /*��Ϣ����*/
		$menu['message_info'] = array(
        'name'  => '��Ϣ��',
        'text'  => Lang::get('��Ϣ��'),
		'submenu'   => array(
            'newpm'  => array(
                    'text'  => '�ռ���',
                    'url'   => 'index.php?app=message&act=newpm',
                    'name'  => 'newpm',
                ),
              'my_new_info' => array(
                    'text'  => '������',
                    'url'   => 'index.php?app=message&act=send',
                    'name'  => 'my_new_info',
                ),
            ),
           );
       if($this->_get_channel_basic()){
       		/*�Ź�Ա����*/
	       $menu['channel_message'] = array(
	         'name'  => '�Ź�Ա����',
	         'text'  => Lang::get('�Ź�Ա����'),
			 'submenu'   => array(
	       	      'my_manager'  => array(
	                    'text'  => '������Ϣ',
	                    'url'   => 'index.php?app=member&act=manager',
	                    'name'  => 'manager',
	                ),
	              'uninvitgroup'  => array(
	                    'text'  => '�ҵĻ�Ա',
	                    'url'   => 'index.php?app=member&act=uninvitgroup',
	                    'name'  => 'uninvitgroup',
	                ),
	              'selGains'  => array(
	                    'text'  => '�ҵ�����',
	                    'url'   => 'index.php?app=member&act=selGains',
	                    'name'  => 'selGains',
	                ),
	              'invitgroup' => array(
	                	'text'	=> '�����Ź�Ա',
	                	'url' 	=> 'index.php?app=member&act=invitgroup',
	                	'name'  => 'invitgroup',
	                ),
	              /*
	              'autotrophy'  => array(
	                    'text'  => '��Ʒ����',
	                    'url'   => 'index.php?app=member&act=autotrophy',
	                    'name'  => 'autotrophy',
	                ),
	              'unmember'  => array(
	                    'text'  => 'ֱ����Ա',
	                    'url'   => 'index.php?app=member&act=unmember',
	                    'name'  => 'unmember',
	                ),
	                */
	               ),
	              );
               if (!$this->visitor->get('has_store') && Conf::get('store_allow'))
		        {

		             $menu['channel_message'] = array(
			         'name'  => '�Ź�Ա����',
			         'text'  => Lang::get('�Ź�Ա����'),
					 'submenu'   => array(
		             	  'my_manager'  => array(
				                    'text'  => '������Ϣ',
				                    'url'   => 'index.php?app=member&act=manager',
				                    'name'  => 'manager',
				                ),
			              'uninvitgroup'  => array(
			                    'text'  => '�ҵĻ�Ա',
			                    'url'   => 'index.php?app=member&act=uninvitgroup',
			                    'name'  => 'uninvitgroup',
			          			),
			              'selGains'  => array(
			                    'text'  => '�ҵ�����',
			                    'url'   => 'index.php?app=member&act=selGains',
			                    'name'  => 'selGains',
		                		),
		                  'invitgroup' => array(
			                	'text'	=> '�����Ź�Ա',
			                	'url' 	=> 'index.php?app=member&act=invitgroup',
			                	'name'  => 'invitgroup',
			                	),
		                  'applyStore' => array(
		                		'text' => Lang::get('apply_store'),
	                			'url'  => 'index.php?app=apply',
		                		'name' => 'applyStore',
		                		),
	                		),
	                );
		        }
	        if ($this->visitor->get('manage_store'))
       		{
        		$menu['overview']['is_seller'] = 'yes';
       		}
       } 
       	/*  ������� */
   /*   $menu['im_buyer'] = array(
            'name'  => 'im_buyer',
            'text'  => Lang::get('im_buyer'),
            'submenu'   => array(
                'my_order'  => array(
                    'text'  => Lang::get('my_order'),
                    'url'   => 'index.php?app=buyer_order',
                    'name'  => 'my_order',
                    'icon'  => 'ico5',
                ),
                'credit_order'  => array(
                    'text'  => '�����Ҷ���',
                    'url'   => 'index.php?app=member&act=credit_order',
                    'name'  => 'credit_order',
                    //'icon'  => 'ico5',
                ),
                'my_groupbuy'  => array(
                    'text'  => Lang::get('my_groupbuy'),
                    'url'   => 'index.php?app=buyer_group_order',
                    'name'  => 'my_groupbuy',
                    'icon'  => 'ico21',
                ),
                'my_question' =>array(
                    'text'  => Lang::get('my_question'),
                    'url'   => 'index.php?app=my_question',
                    'name'  => 'my_question',
                    'icon'  => 'ico17',

                ),
                'my_favorite'  => array(
                    'text'  => Lang::get('my_favorite'),
                    'url'   => 'index.php?app=my_favorite',
                    'name'  => 'my_favorite',
                    'icon'  => 'ico6',
                ),
                'my_address'  => array(
                    'text'  => Lang::get('my_address'),
                    'url'   => 'index.php?app=my_address',
                    'name'  => 'my_address',
                    'icon'  => 'ico7',
                ),
                'my_coupon'  => array(
                    'text'  => Lang::get('my_coupon'),
                    'url'   => 'index.php?app=my_coupon',
                    'name'  => 'my_coupon',
                    'icon'  => 'ico20',
                ),
            ),
        );   */
       /* if (!$this->visitor->get('has_store') && Conf::get('store_allow'))
        {
             	û��ӵ�е��̣��ҿ������룬����ʾ���뿪������ */
            /*$menu['im_seller'] = array(
                'name'  => 'im_seller',
                'text'  => Lang::get('im_seller'),
                'submenu'   => array(),
            );

            $menu['im_seller']['submenu']['overview'] = array(
                'text'  => Lang::get('apply_store'),
                'url'   => 'index.php?app=apply',
                'name'  => 'apply_store',
            );
            $menu['overview'] = array(
                'text' => Lang::get('apply_store'),
                'url'  => 'index.php?app=apply',
            );
        }*/
        if ($this->visitor->get('manage_store'))
        {
        	$menu['overview']['is_seller'] = 'yes';
//            /* ָ����Ҫ����ĵ��� */
//            $menu['im_seller'] = array(
//                'name'  => 'im_seller',
//                'text'  => Lang::get('im_seller'),
//                'submenu'   => array(),
//            );
//
//            $menu['im_seller']['submenu']['my_goods'] = array(
//                    'text'  => Lang::get('my_goods'),
//                    'url'   => 'index.php?app=my_goods',
//                    'name'  => 'my_goods',
//                    'icon'  => 'ico8',
//            );
//            $menu['im_seller']['submenu']['groupbuy_manage'] = array(
//                    'text'  => Lang::get('groupbuy_manage'),
//                    'url'   => 'index.php?app=seller_groupbuy',
//                    'name'  => 'groupbuy_manage',
//                    'icon'  => 'ico22',
//            );
//            $menu['im_seller']['submenu']['my_qa'] = array(
//                    'text'  => Lang::get('my_qa'),
//                    'url'   => 'index.php?app=my_qa',
//                    'name'  => 'my_qa',
//                    'icon'  => 'ico18',
//            );
//            $menu['im_seller']['submenu']['my_category'] = array(
//                    'text'  => Lang::get('my_category'),
//                    'url'   => 'index.php?app=my_category',
//                    'name'  => 'my_category',
//                    'icon'  => 'ico9',
//            );
//            $menu['im_seller']['submenu']['order_manage'] = array(
//                    'text'  => Lang::get('order_manage'),
//                    'url'   => 'index.php?app=seller_order',
//                    'name'  => 'order_manage',
//                    'icon'  => 'ico10',
//            );
//            $menu['im_seller']['submenu']['my_store']  = array(
//                    'text'  => Lang::get('my_store'),
//                    'url'   => 'index.php?app=my_store',
//                    'name'  => 'my_store',
//                    'icon'  => 'ico11',
//            );
//            $menu['im_seller']['submenu']['my_theme']  = array(
//                    'text'  => Lang::get('my_theme'),
//                    'url'   => 'index.php?app=my_theme',
//                    'name'  => 'my_theme',
//                    'icon'  => 'ico12',
//            );
//            #ȡ����������֧����ʽ����
////            $menu['im_seller']['submenu']['my_payment'] =  array(
////                    'text'  => Lang::get('my_payment'),
////                    'url'   => 'index.php?app=my_payment',
////                    'name'  => 'my_payment',
////                    'icon'  => 'ico13',
////            );
//            $menu['im_seller']['submenu']['my_shipping'] = array(
//                    'text'  => Lang::get('my_shipping'),
//                    'url'   => 'index.php?app=my_shipping',
//                    'name'  => 'my_shipping',
//                    'icon'  => 'ico14',
//            );
//            $menu['im_seller']['submenu']['my_navigation'] = array(
//                    'text'  => Lang::get('my_navigation'),
//                    'url'   => 'index.php?app=my_navigation',
//                    'name'  => 'my_navigation',
//                    'icon'  => 'ico15',
//            );
//            $menu['im_seller']['submenu']['my_partner']  = array(
//                    'text'  => Lang::get('my_partner'),
//                    'url'   => 'index.php?app=my_partner',
//                    'name'  => 'my_partner',
//                    'icon'  => 'ico16',
//            );
//            $menu['im_seller']['submenu']['coupon']  = array(
//                    'text'  => Lang::get('coupon'),
//                    'url'   => 'index.php?app=coupon',
//                    'name'  => 'coupon',
//                    'icon'  => 'ico19',
//            );
        }
        if($this->_get_channel_basic())
        {
        	$menu['my_ecmall']['submenu'] = array(
        		'myinfo'  => array(
                    'text'  => '�޸ĸ�������',
                    'url'   => 'index.php?app=member&act=profile',
                    'name'  => 'my_profile',
                ),
                'loginpassword'  => array(
                    'text'  => '�޸ĵ�¼����',
                    'url'   => 'index.php?app=member&act=password',
                    'name'  => 'loginpassword',
                ),
                 'paypassword'  => array(
                    'text'  => '�޸�֧������',
                    'url'   => 'index.php?app=member&act=passwordpayment',
                    'name'  => 'paypassword',
                ),
                  'phonepassword'  => array(
                    'text'  => '�޸İ��ֻ�����',
                    'url'   => 'index.php?app=member&act=mobile',
                    'name'  => 'phonepassword',
                ),
		'memberCardInfo'  => array(
			'text'  => '�����˻�',
			'url'   => 'index.php?app=member_card',
			'name'  => 'memberCardInfo',
		)
        	);
        }
        if($this->_get_store_basic() || $this->_get_channel_basic())
        {
			        /*���׹���*/
				$menu['message'] = array(
		        'name'  => '���׹���',
		        'text'  => Lang::get('���׹���'),
				'submenu'   => array(
		            'voucher_center'  => array(
		                    'text'  => '��ݳ�ֵ',
		                    'url'   => 'index.php?app=voucher_center',
		                    'name'  => 'credit',
		                ),
		                'carry'  => array(
		                    'text'  => '��Աת��',
		                    'url'   => 'index.php?app=member&act=carry',
		                    'name'  => 'carry',
		                ),
		                    'detailed'  => array(
		                    'text'  => '���׼�¼',
		                    'url'   => 'index.php?app=member&act=detailed',
		                    'name'  => 'detailed',
		                ),
		            ),
		           );
        }
        return $menu;
    }
}

/**
 *    ���̹�����ϵͳ������
 *
 *    @author    Garbin
 *    @usage    none
 */
class StoreadminbaseApp extends MemberbaseApp
{
    function _run_action()
    {
        /* ֻ�е�¼���û��ſɷ��� */
        if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user')))
        {
            if (!IS_AJAX)
            {
                header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

                return;
            }
            else
            {
                $this->json_error('login_please');
                return;
            }
        }
    	/* ����Ƿ��ǿͻ��˵���*/
        if ($_SESSION['clientshop'] != 'true')
        {
            $this->show_warning('������ר�õ��̻��ͻ��˹���');
            return;
        }
        $referer = $_SERVER['HTTP_REFERER'];
        if (strpos($referer, 'act=login') === false)
        {
            $ret_url = $_SERVER['HTTP_REFERER'];
            $ret_text = 'go_back';
        }
        else
        {
            $ret_url = SITE_URL . '/index.php';
            $ret_text = 'back_index';
        }

        /* ����Ƿ��ǵ��̹���Ա */
        if (!$this->visitor->get('manage_store'))
        {
            /* �����ǵ��̹���Ա */
            $this->show_warning(
                'not_storeadmin',
                'apply_now', 'index.php?app=apply',
                $ret_text, $ret_url
            );

            return;
        }

        /* ����Ƿ���Ȩ */
        $privileges = $this->_get_privileges();
        if (!$this->visitor->i_can('do_action', $privileges))
        {
            $this->show_warning('no_permission', $ret_text, $ret_url);

            return;
        }

        /* �����̿���״̬ */
        $state = $this->visitor->get('state');
        if ($state == 0)
        {
            $this->show_warning('apply_not_agree', $ret_text, $ret_url);

            return;
        }
        elseif ($state == 2)
        {
            $this->show_warning('store_is_closed', $ret_text, $ret_url);

            return;
        }

        /* ��鸽�ӹ��� */
        if (!$this->_check_add_functions())
        {
            $this->show_warning('not_support_function', $ret_text, $ret_url);
            return;
        }

        parent::_run_action();
    }
    function _get_privileges()
    {
        $store_id = $this->visitor->get('manage_store');
        $privs = $this->visitor->get('s');

        if (empty($privs))
        {
            return '';
        }

        foreach ($privs as $key => $admin_store)
        {
            if ($admin_store['store_id'] == $store_id)
            {
                return $admin_store['privs'];
            }
        }
    }
    
    /* ��ȡ��ǰ������ʹ�õ����� */
    function _get_theme()
    {
        $model_store =& m('store');
        $store_info  = $model_store->get($this->visitor->get('manage_store'));
        $theme = !empty($store_info['theme']) ? $store_info['theme'] : 'default|default';
        list($curr_template_name, $curr_style_name) = explode('|', $theme);
        return array(
            'template_name' => $curr_template_name,
            'style_name'    => $curr_style_name,
        );
    }

    function _check_add_functions()
    {
        $apps_functions = array( // app��function��Ӧ��ϵ
            'seller_groupbuy' => 'groupbuy',
            'coupon' => 'coupon',
        );
        if (isset($apps_functions[APP]))
        {
            $store_mod =& m('store');
            $settings = $store_mod->get_settings($this->_store_id);
            $add_functions = isset($settings['functions']) ? $settings['functions'] : ''; // ���ӹ���
            if (!in_array($apps_functions[APP], explode(',', $add_functions)))
            {
                return false;
            }
        }
        return true;
    }
}

/**
 *    ���̿�����������
 *
 *    @author    Garbin
 *    @usage    none
 */
class StorebaseApp extends FrontendApp
{
    var $_store_id;

    /**
     * ���õ���id
     *
     * @param int $store_id
     */
    function set_store($store_id)
    {
        $this->_store_id = intval($store_id);

        /* ����store id�����ͼ���ж������� */
        $this->_init_view();
        $this->_config_view();
    }

    function _config_view()
    {
        parent::_config_view();
        $template_name = $this->_get_template_name();
        $style_name    = $this->_get_style_name();

        $this->_view->template_dir = ROOT_PATH . "/themes/store/{$template_name}";
        $this->_view->compile_dir  = ROOT_PATH . "/temp/compiled/store/{$template_name}";
        $this->_view->res_base     = SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}";
    }

    /**
     * ȡ�õ�����Ϣ
     */
    function get_store_data()
    {
        $cache_server =& cache_server();
        $key = 'function_get_store_data_' . $this->_store_id;
        $store = $cache_server->get($key);
        if ($store === false)
        {
            $store = $this->_get_store_info();
            if (empty($store))
            {
                $this->show_warning('the_store_not_exist');
                exit;
            }
            if ($store['state'] == 2)
            {
                $this->show_warning('the_store_is_closed');
                exit;
            }
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $store_mod =& m('store');
            $store['credit_image'] = $this->_view->res_base . '/images/' . $store_mod->compute_credit($store['credit_value'], $step);

            empty($store['store_logo']) && $store['store_logo'] = Conf::get('default_store_logo');
            $store['store_owner'] = $this->_get_store_owner();
            $store['store_navs']  = $this->_get_store_nav();
            $goods_mod =& m('goods');
            //$store['goods_count'] = $goods_mod->get_count_of_store($this->_store_id);
            //$store['store_gcates']= $this->_get_store_gcategory();
            $store['sgrade'] = $this->_get_store_grade('grade_name');
            $functions = $this->_get_store_grade('functions');
            $store['functions'] = array();
            if ($functions)
            {
                $functions = explode(',', $functions);
                foreach ($functions as $k => $v)
                {
                    $store['functions'][$v] = $v;
                }
            }
            $cache_server->set($key, $store, 1800);
        }
        return $store;
    }

    /* ȡ�õ�����Ϣ */
    function _get_store_info()
    {
        if (!$this->_store_id)
        {
            /* δ����ǰ���ؿ� */
            return array();
        }
        static $store_info = null;
        if ($store_info === null)
        {
            $store_mod  =& m('store');
            $store_info = $store_mod->get_info($this->_store_id);
        }

        return $store_info;
    }

    /* ȡ�õ�����Ϣ */
    function _get_store_owner()
    {
        $user_mod =& m('member');
        $user = $user_mod->get($this->_store_id);

        return $user;
    }

    /* ȡ�õ��̵��� */
    function _get_store_nav()
    {
        $article_mod =& m('article');
        return $article_mod->find(array(
            'conditions' => "store_id = '{$this->_store_id}' AND cate_id = '" . STORE_NAV . "' AND if_show = 1",
            'order' => 'sort_order',
            'fields' => 'title',
        ));
    }
    /*  ȡ�ĵ��̵ȼ�   */

    function _get_store_grade($field)
    {
        $store_info = $store_info = $this->_get_store_info();
        $sgrade_mod =& m('sgrade');
        $result = $sgrade_mod->get_info($store_info['sgrade']);
        return $result[$field];
    }
    /* ȡ�õ��̷��� */
    function _get_store_gcategory()
    {
        $gcategory_mod =& bm('gcategory', array('_store_id' => $this->_store_id));
        $gcategories = $gcategory_mod->get_list(-1, true);
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getArrayList(0);
    }

    /**
     *    ��ȡ��ǰ�������趨��ģ������
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_template_name()
    {
        $store_info = $this->_get_store_info();
        $theme = !empty($store_info['theme']) ? $store_info['theme'] : 'default|default';
        list($template_name, $style_name) = explode('|', $theme);
        return $template_name;
    }

    /**
     *    ��ȡ��ǰ�������趨�ķ������
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_style_name()
    {
        $store_info = $this->_get_store_info();
        $theme = !empty($store_info['theme']) ? $store_info['theme'] : 'default|default';
        list($template_name, $style_name) = explode('|', $theme);

        return $style_name;
    }
}

/* ʵ����Ϣ������ӿ� */
class MessageBase extends MallbaseApp {};

/* ʵ��ģ�������ӿ� */
class BaseModule  extends FrontendApp {};

/* ��Ϣ������ */
require(ROOT_PATH . '/core/controller/message.base.php');

?>

