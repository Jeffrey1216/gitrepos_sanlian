<?php

define('IMAGE_FILE_TYPE', 'gif|jpg|jpeg|png'); // ͼƬ���ͣ��ϴ�ͼƬʱʹ��

define('SIZE_GOODS_IMAGE', '2097152');   // ��Ʒ��С����2M
define('SIZE_STORE_LOGO', '20480');      // ����LOGO��С����2OK
define('SIZE_STORE_BANNER', '1048576');  // ����BANNER��С����1M
define('SIZE_STORE_CERT', '409600');     // ����֤��ִ�մ�С����400K
define('SIZE_STORE_PARTNER', '102400');  // ���̺������ͼƬ��С����100K
define('SIZE_CSV_TAOBAO', '2097152');     // �Ա�����CSV��С����2M

/* ����״̬ */
define('STORE_APPLYING', 0); // ������
define('STORE_OPEN',     1); // ����
define('STORE_CLOSED',   2); // �ر�

/* ����״̬ */
define('ORDER_SUBMITTED', 10);                 // ��Ի���������ԣ�������һ��״̬�������ѷ���
define('ORDER_PENDING', 11);                   // �ȴ���Ҹ���
define('ORDER_ACCEPTED', 20);                  // ����Ѹ���ȴ����ҷ���
define('ORDER_SHIPPED', 30);                   // �����ѷ���
define('ORDER_FINISHED', 40);                  // ���׳ɹ�
define('ORDER_CANCELED', 0);                   // ������ȡ��
define('ORDER_REFUND',50);					   // �˿���
define('ORDER_REFUND_FINISH',60);			   // ���˿�

/* �������·���ID */
define('STORE_NAV',    -1); // ���̵���
define('ACATE_HELP',    1); // �̳ǰ���
define('ACATE_NOTICE',  2); // �̳ǿ�Ѷ�����棩
define('ACATE_SYSTEM',  3); // ��������

/* ϵͳ���·���code�ֶ� */
define('ACC_NOTICE', 'notice');                 //acategory����code�ֶ�Ϊnoticeʱ�����̳ǹ������
define('ACC_SYSTEM', 'system');                 //acategory����code�ֶ�Ϊsystemʱ���������������
define('ACC_HELP', 'help');                     //acategory����code�ֶ�Ϊhelpʱ�����̳ǰ������
define('ACC_ABOUT' , 'about');					//acategory����code�ֶ�Ϊaboutʱ���������������
define('ACC_ACTIVITY' , 'activity');			//acategory����code�ֶ�Ϊactivityʱ�������»���
define('ACC_CHANNEL' , 'channel');			    //acategory����code�ֶ�Ϊchannelʱ���������̰������
define('ACC_BRAND' , 'brand');			    //acategory����code�ֶ�Ϊbrandʱ����Ʒ���й����
define('ACC_CREDIT' , 'credit');			    //acategory����code�ֶ�Ϊbrandʱ����Ʒ���й����
define('ACC_CX' , 'cx');			    //acategory����code�ֶ�ΪCXʱ��-�������̳����
define('ACC_PCX' , 'pcx');			    //acategory����code�ֶ�ΪCXʱ��-�����̳����
define('ACC_AGRO', 'agro');					//acategory����code�ֶ�Ϊagroʱ������ɫ�鱨���

/* �ʼ������ȼ� */
define('MAIL_PRIORITY_LOW',     1);
define('MAIL_PRIORITY_MID',     2);
define('MAIL_PRIORITY_HIGH',    3);

/* �����ʼ���Э������ */
define('MAIL_PROTOCOL_LOCAL',       0, true);
define('MAIL_PROTOCOL_SMTP',        1, true);

/* ���ݵ��õ����� */
define('TYPE_GOODS', 1);

/* �ϴ��ļ����� */
define('BELONG_ARTICLE',    1);
define('BELONG_GOODS',      2);
define('BELONG_STORE',      3);

/* ������������ */
!defined('ENABLED_SUBDOMAIN') && define('ENABLED_SUBDOMAIN', 0);

/* ���� */
define('CHARSET', substr(LANG, 3));
define('IS_AJAX', isset($_REQUEST['ajax']));
/* ����Ϣ�ı�־ */
define('MSG_SYSTEM' , 0); //ϵͳ��Ϣ

/* �Ź��״̬ */
define('GROUP_PENDING',  0);            // δ����
define('GROUP_ON',       1);            // ���ڽ���
define('GROUP_END',      2);            // �ѽ���
define('GROUP_FINISHED', 3);            // �����
define('GROUP_CANCELED', 4);            // ��ȡ��

define('GROUP_CANCEL_INTERVAL', 5);     // �Ź��������Զ�ȡ���ļ������

/* ֪ͨ���� */
define('NOTICE_MAIL',   1); // �ʼ�֪ͨ
define('NOTICE_MSG',    2); // վ�ڶ���Ϣ


define('PAISONG',1); //����������ר��id


/**
 *    ECBaseApp
 *
 *    @author    Garbin
 *    @usage    none
 */
class ECBaseApp extends BaseApp
{
    var $outcall;
    private $manager_arr;
    
    function __construct()
    {
        $this->ECBaseApp();
    }
    function ECBaseApp()
    {
        parent::__construct();

        if (!defined('MODULE')) // ��ʱ���������˴���Ӧ��ģ��������⴦��
        {
            /* GZIP */
            if ($this->gzip_enabled())
            {
                ob_start('ob_gzhandler');
            }
            else
            {
                ob_start();
            }
			$this->manager_arr = array();
            /* ��utf8ת�� */
            if (CHARSET != 'utf-8' && isset($_REQUEST['ajax']))
            {
                $_FILES = ecm_iconv_deep('utf-8', CHARSET, $_FILES);
                $_GET = ecm_iconv_deep('utf-8', CHARSET, $_GET);
                $_POST = ecm_iconv_deep('utf-8', CHARSET, $_POST);
            }

            /* ���������� */
            $setting =& af('settings');
            Conf::load($setting->getAll());
			
            /* ��ǰpl��Ĭ�ϵ���ID */
			define('STORE_ID', Conf::get('own_store'));
			
			/* ��ǰpl��Ĭ������ID */
			define('CHANNEL_ID', Conf::get('channel_id'));
			/* ��ǰĬ��ͼƬ��������ַ */
			
			define('IMAGE_URL', Conf::get('image_url'));
			
			/* ��ǰpl��Ĭ�������ֻ��� */
			define('DEF_MOBILE', Conf::get('need_mobile'));

			/* ��ǰpl����������ֻ��� */
			define('CHANNEL_VERIFY_MOBILE', Conf::get('channel_verify_mobile'));
			
			/* ��ǰpl�Ĳ�������ֻ��� */
			define('FI_VERIFY_MOBILE', Conf::get('fi_verify_mobile'));
			
			/* ��ǰĬ�ϵ�ҵ�� */
			define('ACHIEVEMENT', Conf::get('achievement'));
			
            /* ��ʼ��������(���ڴ˿��ܲ�������) */
            $this->_init_visitor();

            /* �ƻ������ػ����� */
            $this->_run_cron();
        }
    }
    function _init_visitor()
    {
    }

    /**
     *    ��ʼ��Session
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _init_session()
    {
        import('session.lib');
        $this->_session = new SessionProcessor(db(), '`pa_sessions`', '`pa_sessions_data`', 'PL_ID');
        define('SESS_ID', $this->_session->get_session_id());
        /* ����ʱ�Ĺ��ﳵ��Ŀ */
        $this->_session->add_related_table('`pa_cart`', 'cart', 'session_id', 'user_id=0');
        $this->_session->my_session_start();
        env('session', $this->_session);
    }
    function _config_view()
    {
        $this->_view->caching       = ((DEBUG_MODE & 1) == 0);  // �Ƿ񻺴�
        $this->_view->force_compile = ((DEBUG_MODE & 2) == 2);  // �Ƿ���Ҫǿ�Ʊ���
        $this->_view->direct_output = ((DEBUG_MODE & 4) == 4);  // �Ƿ�ֱ�����
        $this->_view->gzip          = (defined('ENABLED_GZIP') && ENABLED_GZIP === 1);
        $this->_view->lib_base      = SITE_URL . '/includes/libraries/javascript';
    }
    /**
     *    ת����ģ��
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function do_action($action)
    {
        /* ָ����Ҫ���е�ģ�������ģ������� */
        (!empty($_GET['module']) && !defined('MODULE')) && $action = 'run_module';
        parent::do_action($action);
    }

    function _run_action()
    {
        /*
        if (!$this->visitor->i_can('do_action'))
        {
            if (!$this->visitor->has_login)
            {
                $this->login();
            }
            else
            {
                $this->show_warning($this->visitor->get_error());
            }

            return;
        }
        */
        if ($this->_hook('on_run_action'))
        {
            return;
        }
        parent::_run_action();

        if ($this->_hook('end_run_action'))
        {
            return;
        }
    }

    function run_module()
    {
        $module_name = empty($_REQUEST['module']) ? false : strtolower(trim(str_replace('/', '', $_REQUEST['module'])));
        if (!$module_name)
        {
            $this->show_warning('no_such_module');

            return;
        }
        $file = defined('IN_BACKEND') ? 'admin' : 'index';
        $module_class_file = ROOT_PATH . '/external/modules/' . $module_name . '/' . $file . '.module.php';
        require(ROOT_PATH . '/includes/module.base.php');
        require($module_class_file);
        define('MODULE', $module_name);
        $module_class_name = ucfirst($module_name) . 'Module';

        /* �ж�ģ���Ƿ����� */
        $model_module =& m('module');
        $find_data = $model_module->find('index:' . $module_name);
        if (empty($find_data))
        {
            /* û�а�װ */
            $this->show_warning('no_such_module');

            return;
        }
        $info = current($find_data);
        if (!$info['enabled'])
        {
            /* ��δ���� */
            $this->show_warning('module_disabled');

            return;
        }

        /* ����ģ������ */
        Conf::load(array($module_name . '_config' => unserialize($info['module_config'])));

        /* ����ģ�� */
        $module = new $module_class_name();
        c($module);
        $module->do_action(ACT);
        $module->destruct();
    }


    function login()
    {
        $this->display('login.html');
    }
    function logout()
    {
        $this->visitor->logout();
    }
    function jslang($lang)
    {
        header('Content-Encoding:'.CHARSET);
        header("Content-Type: application/x-javascript\n");
        header("Expires: " .date(DATE_RFC822, strtotime("+1 hour")). "\n");
        if (!$lang)
        {
            echo 'var lang = null;';
        }
        else
        {
            echo 'var lang = ' . ecm_json_encode($lang) . ';';
            echo <<<EOT
lang.get = function(key){
    eval('var langKey = lang.' + key);
    if(typeof(langKey) == 'undefined'){
        return key;
    }else{
        return langKey;
    }
}
EOT;
        }
    }

    /**
     *    ���
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _hook($event, $data = array())
    {
        if ($this->outcall)
        {
            return;
        }
        static $plugins = null;
        $conf_file = ROOT_PATH . '/data/plugins.inc.php';
        if ($plugins === null)
        {
            is_file($conf_file) && $plugins = include($conf_file);
            if (!is_array($plugins))
            {
                $plugins = false;
            }
        }
        if (!isset($plugins[$event]))
        {
            return null;
        }

        /* ��ȡ���ò���б� */
        $plugin_list = $plugins[$event];
        if (empty($plugin_list))
        {
            return null;
        }
        foreach ($plugin_list as $plugin_name => $plugin_info)
        {
            $plugin_main_file = ROOT_PATH . "/external/plugins/{$plugin_name}/main.plugin.php";
            if (is_file($plugin_main_file))
            {
                include_once($plugin_main_file);
            }
            $plugin_class_name = ucfirst($plugin_name) . 'Plugin';
            $plugin = new $plugin_class_name($data, $plugin_info);
            $this->outcall = true;

            /* ����һ���������Ҫֹͣ��ǰ������������᷵��true */
            $stop_flow = $this->_run_plugin($plugin);
            $plugin = null;
            $this->outcall = false;

            /* ֹͣԭ���������� */
            if ($stop_flow)
            {
                return $stop_flow;
            }
        }
    }

    /**
     *    ���в��
     *
     *    @author    Garbin
     *    @param     Plugin $plugin
     *    @return    void
     */
    function _run_plugin(&$plugin)
    {
        $plugin->execute();
    }

    /**
     *    head��ǩ�ڵ�����
     *
     *    @author    Garbin
     *    @param     string $contents
     *    @return    void
     */
    function headtag($string)
    {
        $this->_init_view();
        $this->assign('_head_tags', $this->_view->fetch('str:' . $string));
    }

    /**
     *    ������Դ��ģ��
     *
     *    @author    Garbin
     *    @param     mixed $resources
     *    @return    string
     */
    function import_resource($resources, $spec_type = null)
    {
        $headtag = '';
        if (is_string($resources) || $spec_type)
        {
            !$spec_type && $spec_type = 'script';
            $resources = $this->_get_resource_data($resources);
            foreach ($resources as $params)
            {
                $headtag .= $this->_get_resource_code($spec_type, $params) . "\r\n";
            }
            $this->headtag($headtag);
        }
        elseif (is_array($resources))
        {
        	
            foreach ($resources as $type => $res)
            {
            	$headtag .= $this->import_resource($res, $type);
            }
            $this->headtag($headtag);
        }
        return $headtag;
    }
    
    /**
     * ����seo��Ϣ
     *
     * @param array/string $seo_info
     * @return void
     */
    function _config_seo($seo_info, $ext_info = null)
    {
        if (is_string($seo_info))
        {
            $this->_assign_seo($seo_info, $ext_info);
        }
        elseif (is_array($seo_info))
        {
            foreach ($seo_info as $type => $info)
            {
                $this->_assign_seo($type, $info);
            }
        }
    }
    
    function _assign_seo($type, $info)
    {
        $this->_init_view();
        $_seo_info = $this->_view->get_template_vars('_seo_info');
        if (is_array($_seo_info))
        {
            $_seo_info[$type] = $info;
        }
        else
        {
            $_seo_info = array($type => $info);
        }
        $this->assign('_seo_info', $_seo_info);
        $this->assign('page_seo', $this->_get_seo_code($_seo_info));
    }
    
    function _get_seo_code($_seo_info)
    {
        $html = '';
        foreach ($_seo_info as $type => $info)
        {
            $info = trim(htmlspecialchars($info));
            switch ($type)
            {
                case 'title' :
                    $html .= "<{$type}>{$info}</{$type}>";
                    break;
                case 'description' :
                case 'keywords' :
                default :
                    $html .= "<meta name=\"{$type}\" content=\"{$info}\" />";
                    break;
            }
            $html .= "\r\n";
        }        
        return $html;
    }

    /**
     *    ��ȡ��Դ����
     *
     *    @author    Garbin
     *    @param     mixed $resources
     *    @return    array
     */
    function _get_resource_data($resources)
    {
        $return = array();
        if (is_string($resources))
        {
            $items = explode(',', $resources);
            array_walk($items, create_function('&$val, $key', '$val = trim($val);'));
            foreach ($items as $path)
            {
                $return[] = array('path' => $path, 'attr' => '');
            }
        }
        elseif (is_array($resources))
        {
            foreach ($resources as $item)
            {
                !isset($item['attr']) && $item['attr'] = '';
                $return[] = $item;
            }
        }

        return $return;
    }

    /**
     *    ��ȡ��Դ�ļ���HTML����
     *
     *    @author    Garbin
     *    @param     string $type
     *    @param     array  $params
     *    @return    string
     */
    function _get_resource_code($type, $params)
    {
        switch ($type)
        {
            case 'script':
                $pre = '<script charset="utf-8" type="text/javascript"';
                $path= ' src="' . $this->_get_resource_url($params['path']) . '"';
                $attr= ' ' . $params['attr'];
                $tail= '></script>';
            break;
            case 'style':
                $pre = '<link rel="stylesheet" type="text/css"';
                $path= ' href="' . $this->_get_resource_url($params['path']) . '"';
                $attr= ' ' . $params['attr'];
                $tail= ' />';
            break;
        }
        $html = $pre . $path . $attr . $tail;

        return $html;
    }

    /**
     *    ��ȡ��ʵ����Դ·��
     *
     *    @author    Garbin
     *    @param     string $res
     *    @return    void
     */
    function _get_resource_url($res)
    {
        $res_par = explode(':', $res);
        $url_type = $res_par[0];
        $return  = '';
        switch ($url_type)
        {
            case 'url':
                $return = $res_par[1];
            break;
            case 'res':
                $return = '{res file="' . $res_par[1] . '"}';
            break;
            default:
                $res_path = empty($res_par[1]) ? $res : $res_par[1];
                $return = '{lib file="' . $res_path . '"}';
            break;
        }

        return $return;
    }

    function display($f)
    {
        if ($this->_hook('on_display', array('display_file' => & $f)))
        {
            return;
        }
        $this->assign('site_url', SITE_URL);
        $this->assign('image_url', IMAGE_URL);
        $this->assign('paila_version', VERSION);
        $this->assign('random_number', rand());

        /* ������ */
        $this->assign('lang', Lang::get());

        /* �û���Ϣ */
        $this->assign('visitor', isset($this->visitor) ? $this->visitor->info : array());

        /* ����Ϣ */
        $this->assign('new_message', isset($this->visitor) ? $this->_get_new_message() : '');
        $this->assign('charset', CHARSET);
        $this->assign('price_format', Conf::get('price_format'));
        $this->assign('async_sendmail', $this->_async_sendmail());
        $this->_assign_query_info();

        parent::display($f);

        if ($this->_hook('end_display', array('display_file' => & $f)))
        {
            return;
        }
    }

    /* ҳ���ѯ��Ϣ */
    function _assign_query_info()
    {
        $query_time = pl_microtime() - START_TIME;

        $this->assign('query_time', $query_time);
        $db =& db();
        $this->assign('query_count', $db->_query_count);
        //$this->assign('query_user_count', $this->_session->get_users_count());

        /* �ڴ�ռ����� */
        if (function_exists('memory_get_usage'))
        {
            $this->assign('memory_info', memory_get_usage() / 1048576);
        }

        $this->assign('gzip_enabled', $this->gzip_enabled());
        $this->assign('site_domain', urlencode(get_domain()));
        $this->assign('pl_version', VERSION . ' ' . RELEASE);
    }

    function gzip_enabled()
    {
        static $enabled_gzip = NULL;

        if ($enabled_gzip === NULL)
        {
            $enabled_gzip = (defined('ENABLED_GZIP') && ENABLED_GZIP === 1 && function_exists('ob_gzhandler'));
        }

        return $enabled_gzip;
    }

    /**
     *    ��ʾ���󾯸�
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function show_warning()
    {
        $args = func_get_args();
        call_user_func_array('show_warning', $args);
    }


    /**
     *    ��ʾ��ʾ��Ϣ
     *
     *    @author    Garbin
     *    @return    void
     */
    function show_message()
    {
        $args = func_get_args();
        call_user_func_array('show_message', $args);
    }
    
    /**
     *    ��ʾ�̻������̨���󾯸�
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function show_storeadmin_warning()
    {
        $args = func_get_args();
        call_user_func_array('show_storeadmin_warning', $args);
    }


    /**
     *    ��ʾ�̻������̨��ʾ��Ϣ
     *
     *    @author    lihuoliang
     *    @return    void
     */
    function show_storeadmin_message()
    {
        $args = func_get_args();
        call_user_func_array('show_storeadmin_message', $args);
    }
    /**
     * Make a error message by JSON format
     *
     * @param   string  $msg
     *
     * @return  void
     */
    function json_error ($msg='', $retval=null, $jqremote = false)
    {
        if (!empty($msg))
        {
            $msg = Lang::get($msg);
        }
        $result = array('done' => false , 'msg' => $msg);
        if (isset($retval)) $result['retval'] = $retval;

        $this->json_header();
        $json = ecm_json_encode($result);
        if ($jqremote === false)
        {
            $jqremote = isset($_GET['jsoncallback']) ? trim($_GET['jsoncallback']) : false;
        }
        if ($jqremote)
        {
            $json = $jqremote . '(' . $json . ')';
        }

        echo $json;
    }

    /**
     * Make a successfully message
     *
     * @param   mixed   $retval
     * @param   string  $msg
     *
     * @return  void
     */
    function json_result ($retval = '', $msg = '', $jqremote = false)
    {
        if (!empty($msg))
        {
            $msg = Lang::get($msg);
        }
        $this->json_header();
        $json = ecm_json_encode(array('done' => true , 'msg' => $msg , 'retval' => $retval));
        if ($jqremote === false)
        {
            $jqremote = isset($_GET['jsoncallback']) ? trim($_GET['jsoncallback']) : false;
        }
        if ($jqremote)
        {
            $json = $jqremote . '(' . $json . ')';
        }

        echo $json;
    }

    /**
     * Send a Header
     *
     * @author weberliu
     *
     * @return  void
     */
    function json_header()
    {
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Content-type:text/plain;charset=" . CHARSET, true);
    }

    /**
     *    ��֤��
     *
     *    @author    Garbin
     *    @return    void
     */
    function _captcha($width, $height)
    {
        import('captcha.lib');
        $word = generate_code();
        $_SESSION['captcha'] = base64_encode($word);
        $code = new Captcha(array(
            'width' => $width,
            'height'=> $height,
        ));
        $code->display($word);
    }

    /**
     *    ��ȡ��ҳ��Ϣ
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_page($page_per = 10)
    {
        $page = empty($_REQUEST['page']) ? 1 : intval($_REQUEST['page']);
        $start = ($page -1) * $page_per;

        return array('limit' => "{$start},{$page_per}", 'curr_page' => $page, 'pageper' => $page_per);
    }
	/**
	 * �·�ҳ
	 * 
	 * */
	function get_pages($page_per = 20)
    {
        $page = empty($_REQUEST['page']) ? 1 : intval($_REQUEST['page']);
        $start = ($page -1) * $page_per;
        return array('limit' => "{$start},{$page_per}", 'curr_page' => $page, 'pageper' => $page_per);
    }
    
    /**
     * ��ʽ����ҳ��Ϣ
     * @param   array   $page
     * @param   int     $num    ��ʾ��ҳ������
     */
    function _format_page(&$page, $num = 7)
    {
        $page['page_count'] = ceil($page['item_count'] / $page['pageper']);
        $mid = ceil($num / 2) - 1;
        if ($page['page_count'] <= $num)
        {
            $from = 1;
            $to   = $page['page_count'];
        }
        else
        {
            $from = $page['curr_page'] <= $mid ? 1 : $page['curr_page'] - $mid + 1;
            $to   = $from + $num - 1;
            $to > $page['page_count'] && $to = $page['page_count'];
        }

        /*
        if (preg_match('/[&|\?]?page=\w+/i', $_SERVER['REQUEST_URI']) > 0)
        {
            $url_format = preg_replace('/[&|\?]?page=\w+/i', '', $_SERVER['REQUEST_URI']);
        }
        else
        {
            $url_format = $_SERVER['REQUEST_URI'];
        }
        */

        /* ����app=goods&act=view֮���URL */
        if (preg_match('/[&|\?]?page=\w+/i', $_SERVER['QUERY_STRING']) > 0)
        {
            $url_format = preg_replace('/[&|\?]?page=\w+/i', '', $_SERVER['QUERY_STRING']);
        }
        else
        {
            $url_format = $_SERVER['QUERY_STRING'];
        }

        $page['page_links'] = array();
        $page['page_all_links'] = array();
        $page['first_link'] = ''; // ��ҳ����        
        $page['first_suspen'] = ''; // ��ҳʡ�Ժ�
        $page['last_link'] = ''; // βҳ����
        $page['last_suspen'] = ''; // βҳʡ�Ժ�
        for ($i = $from; $i <= $to; $i++)
        {
            $page['page_links'][$i] = url("{$url_format}&page={$i}");
        }
    	for ($i = 1; $i <= $page['page_count']; $i++)
        {
            $page['page_all_links'][$i]['link_url'] = url("{$url_format}&page={$i}");
            $page['page_all_links'][$i]['page_num'] = $i;
        }
        if (($page['curr_page'] - $from) < ($page['curr_page'] -1) && $page['page_count'] > $num)
        {
            $page['first_link'] = url("{$url_format}&page=1");
            if (($page['curr_page'] -1) - ($page['curr_page'] - $from) != 1)
            {
                $page['first_suspen'] = '..';
            }
        }
        if (($to - $page['curr_page']) < ($page['page_count'] - $page['curr_page']) && $page['page_count'] > $num)
        {
            $page['last_link'] = url("{$url_format}&page=" . $page['page_count']);
            if (($page['page_count'] - $page['curr_page']) - ($to - $page['curr_page']) != 1)
            {
                $page['last_suspen'] = '..';
            }
        }

        $page['prev_link'] = $page['curr_page'] > $from ? url("{$url_format}&page=" . ($page['curr_page'] - 1)) : "";
        $page['next_link'] = $page['curr_page'] < $to ? url("{$url_format}&page=" . ($page['curr_page'] + 1)) : "";
    }
    
    
    function _format_pages(&$page, $num = 7)
    {
    
        $page['page_count'] = ceil($page['item_count'] / $page['pageper']);
        $mid = ceil($num / 2) - 1;
        if ($page['page_count'] <= $num)
        {
            $from = 1;
            $to   = $page['page_count'];
        }
        else
        {
            $from = $page['curr_page'] <= $mid ? 1 : $page['curr_page'] - $mid + 1;
            $to   = $from + $num - 1;
            $to > $page['page_count'] && $to = $page['page_count'];
        }

        /*
        if (preg_match('/[&|\?]?page=\w+/i', $_SERVER['REQUEST_URI']) > 0)
        {
            $url_format = preg_replace('/[&|\?]?page=\w+/i', '', $_SERVER['REQUEST_URI']);
        }
        else
        {
            $url_format = $_SERVER['REQUEST_URI'];
        }
        */

        /* ����app=goods&act=view֮���URL */
        if (preg_match('/[&|\?]?page=\w+/i', $_SERVER['QUERY_STRING']) > 0)
        {
            $url_format = preg_replace('/[&|\?]?page=\w+/i', '', $_SERVER['QUERY_STRING']);
        }
        else
        {
            $url_format = $_SERVER['QUERY_STRING'];
        }

        $page['page_links'] = array();
        $page['first_link'] = ''; // ��ҳ����        
        $page['first_suspen'] = ''; // ��ҳʡ�Ժ�
        $page['last_link'] = ''; // βҳ����
        $page['last_suspen'] = ''; // βҳʡ�Ժ�
        for ($i = $from; $i <= $to; $i++)
        {
            $page['page_links'][$i] = url("{$url_format}&page={$i}");
        }
        if (($page['curr_page'] - $from) < ($page['curr_page'] -1) && $page['page_count'] > $num)
        {
            $page['first_link'] = url("{$url_format}&page=1");
            if (($page['curr_page'] -1) - ($page['curr_page'] - $from) != 1)
            {
                $page['first_suspen'] = '..';
            }
        }
        if (($to - $page['curr_page']) < ($page['page_count'] - $page['curr_page']) && $page['page_count'] > $num)
        {
            $page['last_link'] = url("{$url_format}&page=" . $page['page_count']);
            if (($page['page_count'] - $page['curr_page']) - ($to - $page['curr_page']) != 1)
            {
                $page['last_suspen'] = '..';
            }
        }
  
        $page['prev_link'] = $page['curr_page'] > $from ? url("{$url_format}&page=" . ($page['curr_page'] - 1)) : "#";
        $page['next_link'] = $page['curr_page'] < $to ? url("{$url_format}&page=" . ($page['curr_page'] + 1)) : "#";
    }

    /**
     *    ��ȡ��ѯ����
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_query_conditions($query_item){
        $query = array();
        foreach ($query_item as $options)
        {
            if (is_string($options))
            {
                $field = $options;
                $options['field'] = $field;
                $options['name']  = $field;
            }
            !isset($options['equal']) && $options['equal'] = '=';
            !isset($options['assoc']) && $options['assoc'] = 'AND';
            !isset($options['type'])  && $options['type']  = 'string';
            !isset($options['name'])  && $options['name']  = $options['field'];
            !isset($options['handler']) && $options['handler'] = 'trim';
            if (isset($_GET[$options['name']]))
            {
                $input = $_GET[$options['name']];
                $handler = $options['handler'];
                $value = ($input == '' ? $input : $handler($input));
                if ($value === '' || $value === false)  //��δ���룬δѡ�񣬻��߾���$handler����ʧ�ܾ�����
                {
                    continue;
                }
                strtoupper($options['equal']) == 'LIKE' && $value = "%{$value}%";
                if ($options['type'] != 'numeric')
                {
                    $value = "'{$value}'";      //���ϵ����ţ���ȫ��һ
                }
                else
                {
                    $value = floatval($value);  //��ȫ���������ת���ɸ�����
                }
                $str .= " {$options['assoc']} {$options['field']} {$options['equal']} {$value}";
                $query[$options['name']] = $input;
            }
        }
        $this->assign('query', stripslashes_deep($query));
        return $str;
    }

    /**
     *    ʹ�ñ༭��
     *
     *    @author    Garbin
     *    @param     array $params
     *    @return    string
     */
    function _build_editor($params = array())
    {
        $name = isset($params['name']) ?  $params['name'] : null;
        $theme = isset($params['theme']) ?  $params['theme'] : 'normal';
        $ext_js = isset($params['ext_js']) ? $params['ext_js'] : true;
        $content_css = isset($params['content_css']) ? 'content_css:"' . $params['content_css'] . '",' : null;
        $if_media = false;
        $visit = $this->visitor->get('manage_store');
        $store_id = isset($visit) ? intval($visit) : 0;
        $privs = $this->visitor->get('privs');
        if (!empty($privs))
        {
            if ($privs == 'all')
            {
                $if_media = true;
            }
            else
            {
                $privs_array = explode(',', $privs);
                if (in_array('article|all', $privs_array))
                {
                    $if_media = true;
                }
            }
        }
        if (!empty($store_id) && !$if_media)
        {
            $store_mod =& m('store');
            $store = $store_mod->get_info($store_id);
            $sgrade_mod =& m('sgrade') ;
            $sgrade = $sgrade_mod->get_info($store['sgrade']);
            $functions = explode(',', $sgrade['functions']);
            if (in_array('editor_multimedia', $functions))
            {
                $if_media = true;
            }
        }

        $include_js = $ext_js ? '<script type="text/javascript" src="{lib file="tiny_mce/tiny_mce.js"}"></script>' : '';

        /* ָ���ĸ�(Щ)textarea��Ҫ�༭�� */
        if ($name === null)
        {
            $mode = 'mode:"textareas",';
        }
        else
        {
            $mode = 'mode:"exact",elements:"' . $name . '",';
        }

        /* ָ��ʹ���������� */
        $themes = array(
            'normal'    =>  'plugins:"inlinepopups,preview,fullscreen,paste'.($if_media ? ',media' : '' ).'",
            theme:"advanced",
            theme_advanced_buttons1:"code,fullscreen'.($content_css ? ',preview' : '' ).',removeformat,|,bold,italic,underline,strikethrough,|," +
                "formatselect,fontsizeselect,|,forecolor,backcolor",
            theme_advanced_buttons2:"bullist,numlist,|,outdent,indent,blockquote,|,justifyleft,justifycenter," +
                "justifyright,justifyfull,|,link,unlink,charmap,image,|,pastetext,pasteword,|,undo,redo,|,media",
            theme_advanced_buttons3 : "",',
            'simple'    =>  'theme:"simple",',
        );
        switch ($theme)
        {
            case 'simple':
                $theme_config = $themes['simple'];
            break;
            case 'normal':
                $theme_config = $themes['normal'];
            break;
            default:
                $theme_config = $themes['normal'];
            break;
        }
        /* ���ý������� */
        $_lang = substr(LANG, 0, 2);
        switch ($_lang)
        {
            case 'sc':
                $lang = 'zh_cn';
            break;
            case 'tc':
                $lang = 'zh';
            break;
            case 'en':
                $lang = 'en';
            break;
            default:
                $lang = 'zh_cn';
            break;
        }

        /* ��� */
        $str = <<<EOT
$include_js
<script type="text/javascript">
    tinyMCE.init({
        {$mode}
        {$theme_config}
        {$content_css}
        language:"{$lang}",
        convert_urls : false,
        relative_urls : false,
        remove_script_host : false,
        theme_advanced_toolbar_location:"top",
        theme_advanced_toolbar_align:"left"
});
</script>
EOT;

        return $this->_view->fetch('str:' . $str);;
    }

    /**
     *    ʹ��swfupload
     *
     *    @author    Hyber
     *    @param     array $params
     *    @return    string
     */
    function _build_upload($params = array())
    {
        $belong = isset($params['belong']) ? $params['belong'] : 0; //�ϴ��ļ�����ģ��
        $item_id = isset($params['item_id']) ? $params['item_id']: 0; //����ģ�͵�ID
        $file_size_limit = isset($params['file_size_limit']) ? $params['file_size_limit']: '2 MB'; //Ĭ�����2M
        $button_text = isset($params['button_text']) ? Lang::get($params['button_text']) : Lang::get('bat_upload'); //�ϴ���ť�ı�
        $image_file_type = isset($params['image_file_type']) ? $params['image_file_type'] : IMAGE_FILE_TYPE;
        $upload_url = isset($params['upload_url']) ? $params['upload_url'] : 'index.php?app=swfupload';
        $button_id = isset($params['button_id']) ? $params['button_id'] : 'spanButtonPlaceholder';
        $progress_id = isset($params['progress_id']) ? $params['progress_id'] : 'divFileProgressContainer';
        $if_multirow = isset($params['if_multirow']) ? $params['if_multirow'] : 0;
        $define = isset($params['obj']) ? 'var ' . $params['obj'] . ';' : '';
        $assign = isset($params['obj']) ? $params['obj'] . ' = ' : '';
        $ext_js = isset($params['ext_js']) ? $params['ext_js'] : true;
        $ext_css = isset($params['ext_css']) ? $params['ext_css'] : true;

        $include_js = $ext_js ? '<script type="text/javascript" charset="utf-8" src="{lib file="swfupload/swfupload.js"}"></script>
<script type="text/javascript" charset="utf-8" src="{lib file="swfupload/js/handlers.js"}"></script>' : '';
        $include_css = $ext_css ? '<link type="text/css" rel="stylesheet" href="{lib file="swfupload/css/default.css"}"/>' : '';
        /* �������� */
        $file_types = '';
        $image_file_type = explode('|', $image_file_type);
        foreach ($image_file_type as $type)
        {
            $file_types .=  '*.' . $type . ';';
        }
        $file_types = trim($file_types, ';');
        $str = <<<EOT

{$include_js}
{$include_css}
<script type="text/javascript">
{$define}
$(function(){
    {$assign}new SWFUpload({
        upload_url: "{$upload_url}",
        flash_url: "{lib file="swfupload/swfupload.swf"}",
        post_params: {
            "PL_ID": "{$_COOKIE['PL_ID']}",
            "HTTP_USER_AGENT":"{$_SERVER['HTTP_USER_AGENT']}",
            'belong': {$belong},
            'item_id': {$item_id},
            'ajax': 1
        },
        file_size_limit: "{$file_size_limit}",
        file_types: "{$file_types}",
        custom_settings: {
            upload_target: "{$progress_id}",
            if_multirow: {$if_multirow}
        },

        // Button Settings
        button_image_url: "{lib file="swfupload/images/SmallSpyGlassWithTransperancy_17x18.png"}",
        button_width: 86,
        button_height: 18,
        button_text: '<span class="button">{$button_text}</span>',
        button_text_style: '.button { font-family: Helvetica, Arial, sans-serif; font-size: 12pt; font-weight: bold; color: #3F3D3E; } .buttonSmall { font-size: 10pt; }',
        button_text_top_padding: 0,
        button_text_left_padding: 18,
        button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
        button_cursor: SWFUpload.CURSOR.HAND,

        // The event handler functions are defined in handlers.js
        file_queue_error_handler: fileQueueError,
        file_dialog_complete_handler: fileDialogComplete,
        upload_progress_handler: uploadProgress,
        upload_error_handler: uploadError,
        upload_success_handler: uploadSuccess,
        upload_complete_handler: uploadComplete,
        button_placeholder_id: "{$button_id}",
        file_queued_handler : fileQueued
    });
});
</script>
EOT;
        return $this->_view->fetch('str:' . $str);
    }

    /**
     *    �����ʼ�
     *
     *    @author    Garbin
     *    @param     mixed  $to
     *    @param     string $subject
     *    @param     string $message
     *    @param     int    $priority
     *    @return    void
     */
    function _mailto($to, $subject, $message, $priority = MAIL_PRIORITY_LOW)
    {
        /* �����ʼ����У���֪ͨ��Ҫ���� */
        $model_mailqueue =& m('mailqueue');
        $mails = array();
        $to_emails = is_array($to) ? $to : array($to);
        foreach ($to_emails as $_to)
        {
            $mails[] = array(
                'mail_to'       => $_to,
                'mail_encoding' => CHARSET,
                'mail_subject'  => $subject,
                'mail_body'     => $message,
                'priority'      => $priority,
                'add_time'      => gmtime(),
            );
        }

        $model_mailqueue->add($mails);

        /* Ĭ�ϲ����첽�����ʼ����������Խ����Ӧ���������� */
        $this->_sendmail();
    }

    /**
     *    �����ʼ�
     *
     *    @author    Garbin
     *    @param     bool $is_sync
     *    @return    void
     */
    function _sendmail($is_sync = false)
    {
        if (!$is_sync)
        {
            /* �����첽��ʽ�����ʼ�����ģ��������ϴﵽĿ�� */
            $_SESSION['ASYNC_SENDMAIL'] = true;

            return true;
        }
        else
        {
            /* ͬ�������ʼ������첽���͵�����ȥ�� */
            unset($_SESSION['ASYNC_SENDMAIL']);
            $model_mailqueue =& m('mailqueue');

            return $model_mailqueue->send(5);
        }
    }

    /**
     *     ��ȡ�첽�����ʼ�����
     *
     *    @author    Garbin
     *    @return    string
     */
    function _async_sendmail()
    {
        $script = '';
        if (isset($_SESSION['ASYNC_SENDMAIL']) && $_SESSION['ASYNC_SENDMAIL'])
        {
            /* ��Ҫ�첽���� */
            $async_sendmail = SITE_URL . '/index.php?app=sendmail';
            $script = '<script type="text/javascript">sendmail("' . $async_sendmail . '");</script>';
        }

        return $script;
    }
    function _get_new_message()
    {
        $user_id = $this->visitor->get('user_id');
        if(empty($user_id))
        {
            return '';
        }
        $ms =& ms();
        return $ms->pm->check_new($user_id);
    }

    /**
     *    �ƻ������ػ�����
     *
     *    @author    Garbin
     *    @return    void
     */
    function _run_cron()
    {

        register_shutdown_function(create_function('', '
            /*if (ob_get_level() > 0)
            {
                ob_end_flush();         //���
            }*/
            if (!is_file(ROOT_PATH . "/data/tasks.inc.php"))
            {
                $default_tasks = array(
                    "cleanup" =>
                        array (
                            "cycle" => "custom",
                            "interval" => 3600,     //ÿһ��Сʱִ��һ������
                        ),
                );
                file_put_contents(ROOT_PATH . "/data/tasks.inc.php", "<?php\r\n\r\nreturn " . var_export($default_tasks, true) . ";\r\n\r\n");
            }
            import("cron.lib");
            $cron = new Crond(array(
                "task_list" => ROOT_PATH . "/data/tasks.inc.php",
                "task_path" => ROOT_PATH . "/includes/tasks",
                "lock_file" => ROOT_PATH . "/data/crond.lock"
            ));                     //�ƻ�����ʵ��
            $cron->execute();       //ִ��
        '));
    }

    /**
     * ����Feed
     *
     * @author Garbin
     * @param
     * @return void
     **/
    function send_feed($event, $data)
    {
        $ms = &ms();
        if (!$ms->feed->feed_enabled())
        {
            return;
        }

        $feed_config = $this->visitor->get('feed_config');
        $feed_config = empty($feed_config) ? Conf::get('default_feed_config') : unserialize($feed_config);
        if (!$feed_config[$event])
        {
            return;
        }

        $ms->feed->add($event, $data);
    }
    
	//���ɷѻ��Ա�������� �Ź�Ա����
    function ad_manager_rebate($manager_id, $amount ,$type=0,$order_id=0) //manager_id ֱ���Ƽ����Ź�Աid
    {
    	if ($amount>0)
    	{
	    	$_customer_manager_mod = & m('customermanager');
	    	$_manager_arr = $this->_get_customer_manager($manager_id);
	
	    	foreach ($_manager_arr['ret_grant'] as $k => $v)
	    	{
	    		if ($v['ratio']>0)
	    		{
		    		$grant = format_fanli_money($amount * $v['ratio']);
		    		if ($grant)
		    		{
			    		//����Ӧ�û���, ����Ӧ���ֽ�
			    		$cash = $grant['cash'];
			    		$credit = $grant['credit'];
			    		
			    		changeMemberCreditOrMoney($v['manager'] , $credit , ADD_CREDIT);
			    		changeMemberCreditOrMoney($v['manager'] , $cash , ADD_MONEY);
			    		
			    		$_customer_manager_mod->db->query("update pa_customer_manager set 
			    				  gains_total = gains_total + "
			    				 . $grant['money'] ." where user_id = " . $v['manager'] );
			    	   //��д�����¼
			    		$param = array(
			    			'user_id' => $v['manager'],
			    			'user_money' => $cash,
			    			'user_credit' => $credit,
			    			'change_time' => gmtime(),
			    		    'order_id' => $order_id,
			    		);
			    		if ($type==0)
			    		{
			    			$param['change_desc'] = "�Ź�Ա({$v['manager_name']})��ȡ���ѣ�{$amount}, ��÷ֳɱ���".($v['ratio']*100)."%,��{$grant['money']}";
			    			$param['change_type'] = 50;
			    		}else
			    		{
			    			$param['change_desc'] = "�Ź�Ա({$v['manager_name']})�����Ź��������{$amount}, ��÷ֳɱ���".($v['ratio']*100)."%,��{$grant['money']}";
			    			$param['change_type'] = 52;
			    		}
			    		
			    		add_account_log($param);
		    		}
	    		}
	    	}
	    	
	    	foreach ($_manager_arr['customer'] as $_k => $_v)
	    	{
	    		$_customer_manager_mod->db->query("update pa_customer_manager set 
	    			outstanding_achievement_total = outstanding_achievement_total + "
	    				 . $amount . " where user_id = " . $_v);
	    	}
	    	
    		//������ȡ����
	    	$channel_money = format_fanli_money($amount * 0.05) ;
	    	if ($channel_money)
	    	{
		    	//����Ӧ�û���, ����Ӧ���ֽ�
		    	$cash = $channel_money['cash'];
		    	$credit = $channel_money['credit'];
		    	changeMemberCreditOrMoney(CHANNEL_ID , $credit , ADD_CREDIT);
		    	changeMemberCreditOrMoney(CHANNEL_ID , $cash , ADD_MONEY);
		    	
		    	//��д�����¼
		    	$data = array(
		    		'user_id' => CHANNEL_ID,
		    		'user_money' => $cash,
		    		'user_credit' => $credit,
		    		'change_time' => gmtime(),
		    	    'order_id' => $order_id,
		    	);
		    	if ($type==0)
		    	{
		    		$data['change_desc'] = "�Ź�Ա({$v['manager_name']})��ȡ���ѣ�{$amount}, ������÷ֳɱ���5%,��{$channel_money['money']}";
		    		$data['change_type'] = 54;
		    	}else
		    	{
		    		$data['change_desc'] = "�Ź�Ա({$v['manager_name']})�����Ź��������{$amount}, ������÷ֳɱ���5%,��{$channel_money['money']}";
		    		$data['change_type'] = 55;
		    	}
		    	
		    	add_account_log($data);
	    	}
    	}
    }
    
    //���̽ɷ�,�Ź�Ա����
    function manager_rebate($manager_id, $amount, $store_id) //manager_id ֱ���Ƽ����Ź�Աid, $store_id, �ɷѵ��̵�id
    {
    	if ($amount>0)
    	{
	    	$_customer_manager_mod = & m('customermanager');
	    	$_store_mod = & m('store');
	    	$_manager_arr = $this->_get_customer_manager($manager_id);
	    	$_store_info = $_store_mod->get($store_id);
	    	foreach ($_manager_arr['ret_grant'] as $k => $v)
	    	{
	    		if ($v['ratio']>0)
	    		{
		    		$grant = format_fanli_money($amount * $v['ratio']);
		    		if ($grant)
		    		{
			    		//����Ӧ�û���, ����Ӧ���ֽ�
			    		$cash = $grant['cash'];
			    		$credit = $grant['credit'];
			    		changeMemberCreditOrMoney($v['manager'] , $credit , ADD_CREDIT);
			    		changeMemberCreditOrMoney($v['manager'] , $cash , ADD_MONEY);
			    		
			    		$_customer_manager_mod->db->query("update pa_customer_manager  set 
			    				  gains_total = gains_total + "
			    				 . $grant['money'] ." where user_id = " . $v['manager'] );
			    		//��д�����¼
			    		$param = array(
			    			'user_id' => $v['manager'],
			    			'user_money' => $cash,
			    			'user_credit' => $credit,
			    			'change_time' => gmtime(),
			    			'change_desc' => "�Ź�Ա({$v['manager_name']})�Ƽ��ĵ���(ID:{$_store_info['store_id']},������:{$_store_info['store_name']})�ɷѣ�{$amount}, ��÷ֳɱ���".($v['ratio']*100)."%,��{$grant['money']}",
			    			'change_type' => 51,
			    		);
			    		add_account_log($param);
		    		}
	    		}
	    	}
	    	
	    	foreach ($_manager_arr['customer'] as $_k => $_v)
	    	{
	    		$_customer_manager_mod->db->query("update pa_customer_manager set 
	    			outstanding_achievement_total = outstanding_achievement_total + "
	    				 . $amount . " where user_id = " . $_v);
	    	}
	    	
	    	//������ȡ����
	    	$channel_money = format_fanli_money($amount * 0.05) ;
	    	if ($channel_money)
	    	{
		    	//����Ӧ�û���, ����Ӧ���ֽ�
		    	$cash = $channel_money['cash'];
		    	$credit = $channel_money['credit'];
		    	changeMemberCreditOrMoney(CHANNEL_ID , $credit , ADD_CREDIT);
		    	changeMemberCreditOrMoney(CHANNEL_ID , $cash , ADD_MONEY);
		    	
		    	//��д�����¼
		    	$data = array(
		    		'user_id' => CHANNEL_ID,
		    		'user_money' => $cash,
		    		'user_credit' => $credit,
		    		'change_time' => gmtime(),
		    	    'change_desc' => "�Ź�Ա({$v['manager_name']})�Ƽ��ĵ���(ID:{$_store_info['store_id']},������:{$_store_info['store_name']})�ɷѣ�{$amount}, ������÷ֳɱ���5%,��{$channel_money['money']}",
			        'change_type' => 56,
		    	);
		    	
		    	add_account_log($data);
	    	}
    	}
    }
    
	//��Ա����---�����Ź�Ա��÷���
    function mb_manager_rebate($manager_id, $credit,$order_id = 0 ,$amount) //manager_id ֱ���Ƽ����Ź�Աid
    {
    	if ($credit>0)
    	{
	    	$grant = format_fanli_money($credit * 0.5); //�û��������û��ֵ�50%
	    	if ($grant)
	    	{
	    	//����Ӧ�û���, ����Ӧ���ֽ�
	    	$cash = $grant['cash'];
			$credit = $grant['credit'];
	    	
	    	changeMemberCreditOrMoney($manager_id , $credit , ADD_CREDIT);
	    	changeMemberCreditOrMoney($manager_id , $cash , ADD_MONEY);
	    	
	       //��д�����¼
	    	$param = array(
	    		'user_id' => $manager_id,
	    		'user_money' => $cash,
	    		'user_credit' => $credit,
	    		'change_time' => gmtime(),
	    		'change_desc' => "��Ա����Ź�Ա����õķ�������{$grant['money']}",
	    		'change_type' => 53,
	    		'order_id' => $order_id,
	    	);
	    	add_account_log($param);
	    	//�����Ź�Ա����ҵ���Լ�������
	    	$_customer_manager_mod = & m('customermanager');
	    	$_customer_manager_mod->db->query("update pa_customer_manager set 
		    				  gains_total = gains_total + "
		    				 . $grant['money'] ." ,outstanding_achievement_total = outstanding_achievement_total + "
	    				 . $amount." where user_id = " . $manager_id );
	    	}
    	}
    }
    
	//��Ա����---������÷���
    function mb_channel_rebate($channel_id, $credit ,$order_id = 0 )
    {
    	if ($credit>0)
    	{
	    	$channel_money =  format_fanli_money($credit * 0.5); //�û��������û��ֵ�50%
	    	if ($channel_money)
	    	{
		    	//����Ӧ�û���, ����Ӧ���ֽ�
		    	$cash = $channel_money['cash'];
		    	$credit = $channel_money['credit'];
		    	
		    	changeMemberCreditOrMoney($channel_id , $credit , ADD_CREDIT);
		    	changeMemberCreditOrMoney($channel_id , $cash , ADD_MONEY);
		    	
		       //��д�����¼
		    	$param = array(
		    		'user_id' => $channel_id,
		    		'user_money' => $cash,
		    		'user_credit' => $credit,
		    		'change_time' => gmtime(),
		    		'change_desc' => "��Ա�����������õķ�������{$channel_money['money']}",
		    		'change_type' => 57,
		    		'order_id' => $order_id,
		    	);
		    	add_account_log($param);
	    	}
    	}
    }
    
	//��Ա����---���̻�÷���
    function mb_store_rebate($store_id, $credit,$order_id )
    {
    	if ($credit>0)
    	{
	    	$balance =  format_fanli_money($credit * 0.5); //�û��������û��ֵ�50%
	    	if ($balance)
	    	{
		    	//����Ӧ�û���, ����Ӧ���ֽ�
		    	$cash   = $balance['cash'];
		    	$credit = $balance['credit'];
		    	
		    	changeMemberCreditOrMoney($store_id , $credit , ADD_CREDIT);
		    	changeMemberCreditOrMoney($store_id , $cash , ADD_MONEY);
		    	//��д�����¼
		    	$param = array(
		    		'user_id' => $store_id,
		    		'user_money' => $cash,
		    		'user_credit' => $credit,
		    		'change_time' => gmtime(),
		    		'change_desc' => "��Ա������̻�õķ�������{$balance['money']}",
		    		'change_type' => 58,
		    		'order_id' => $order_id,
		    	);
		    	add_account_log($param);
	    	}
    	}
    }
    
    //��ȡ��Ҫ�������Ź�Ա
    function _get_customer_manager($manager_id, $level_id = -1, $ratio = 0)
    {
    	$_customer_manager_mod = & m('customermanager');
    	$sql = "select * from pa_customer_manager cm left join 
    		pa_customer_level cl on cm.customer_level = cl.level_id 
    		where cm.user_id = " . $manager_id;
    	
    	$info = $_customer_manager_mod->getRow($sql);
    	
    	if ($level_id == -1) define('MANAGE_NAME',$info['user_name']); 
    	$r = $info['benefit_ratio'] - $ratio;
    	$level_id = $info['parent_level_id'];
    	if ($r>0)
    	{
			$this->manager_arr['customer'][] = $info['user_id'];
	    	$this->manager_arr['ret_grant'][] = array(
	    		'manager' => $info['user_id'],
	    	    'manager_name' => MANAGE_NAME,
				'ratio' => $r);
	    	$ratio = $info['benefit_ratio'];
    	}

	    if ($info['parent_level_id'] != 0 && $info['parent_id'] != 0)
    	{
	    	$this->_get_customer_manager($info['parent_id'], $level_id, $ratio);
    	}
    	return $this->manager_arr;
    }

}

/**
 *    �����߻����࣬�����˵�ǰ�����û��Ĳ���
 *
 *    @author    Garbin
 *    @return    void
 */
class BaseVisitor extends Object
{
    var $has_login = false;
    var $info      = null;
    var $privilege = null;
    var $_info_key = '';
    function __construct()
    {
        $this->BaseVisitor();
    }
    function BaseVisitor()
    {
        if (!empty($_SESSION[$this->_info_key]['user_id']))
        {
            $this->info         = $_SESSION[$this->_info_key];
            $this->has_login    = true;
        }
        else
        {
            $this->info         = array(
                'user_id'   => 0,
                'user_name' => Lang::get('guest')
            );
            $this->has_login    = false;
        }
    }
    function assign($user_info)
    {
        $_SESSION[$this->_info_key]   =   $user_info;
    }

    /**
     *    ��ȡ��ǰ��¼�û�����ϸ��Ϣ
     *
     *    @author    Garbin
     *    @return    array      �û�����ϸ��Ϣ
     */
    function get_detail()
    {
        /* δ��¼��������ϸ��Ϣ */
        if (!$this->has_login)
        {
            return array();
        }

        /* ȡ����ϸ��Ϣ */
        static $detail = null;

        if ($detail === null)
        {
            $detail = $this->_get_detail();
        }

        return $detail;
    }

    /**
     *    ��ȡ�û���ϸ��Ϣ
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_detail()
    {
        $model_member =& m('member');

        /* ��ȡ��ǰ�û�����ϸ��Ϣ������Ȩ�� */
        $member_info = $model_member->findAll(array(
            'conditions'    => "member.user_id = '{$this->info['user_id']}'",
            'join'          => 'has_store',                 //�������ҿ����Ƿ��е���
            'fields'        => 'email, password, real_name, logins, ugrade, portrait, store_id, state, sgrade , feed_config',
            'include'       => array(                       //�ҳ����и��û�����ĵ���
                'manage_store'  =>  array(
                    'fields'    =>  'user_priv.privs, store.store_name',
                ),
            ),
        ));
        $detail = current($member_info);

        /* ���ӵ�е��̣���Ĭ�Ϲ���ĵ���Ϊ�Լ��ĵ��̣�������Ҫ�û�����ָ�� */
        if ($detail['store_id'] && $detail['state'] != STORE_APPLYING) // �ų������еĵ���
        {
            $detail['manage_store'] = $detail['has_store'] = $detail['store_id'];
        }

        return $detail;
    }

    /**
     *    ��ȡ��ǰ�û���ָ����Ϣ
     *
     *    @author    Garbin
     *    @param     string $key  ָ���û���Ϣ
     *    @return    string  ���ֵ���ַ����Ļ�
     *               array   ���������Ļ�
     */
    function get($key = null)
    {
        $info = null;

        if (empty($key))
        {
            /* δָ��key���򷵻ص�ǰ�û���������Ϣ��������Ϣ����ϸ��Ϣ */
            $info = array_merge((array)$this->info, (array)$this->get_detail());
        }
        else
        {
            /* ָ����key���򷵻�ָ������Ϣ */
            if (isset($this->info[$key]))
            {
                /* ���Ȳ��һ������� */
                $info = $this->info[$key];
            }
            else
            {
                /* ������������û�У����ѯ��ϸ���� */
                $detail = $this->get_detail();
                $info = isset($detail[$key]) ? $detail[$key] : null;
            }
        }

        return $info;
    }

    /**
     *    �ǳ�
     *
     *    @author    Garbin
     *    @return    void
     */
    function logout()
    {
        unset($_SESSION[$this->_info_key]);
    }
    function i_can($event, $privileges = array())
    {
        $fun_name = 'check_' . $event;

        return $this->$fun_name($privileges);
    }

    function check_do_action($privileges)
    {
        $mp = APP . '|' . ACT;

        if ($privileges == 'all')
        {
            /* ӵ������Ȩ�� */
            return true;
        }
        else
        {
            /* �鿴��ǰ�����Ƿ��ڰ������У�����ڣ��������������� */
            $privs = explode(',', $privileges);
            if (in_array(APP . '|all', $privs) || in_array($mp, $privs))
            {
                return true;
            }

            return false;
        }
    }

}
?>
