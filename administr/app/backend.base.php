<?php

/**
 *    Desc
 *
 *    @author    Garbin
 *    @usage    none
 */
class BackendApp extends ECBaseApp
{
    function __construct()
    {
        $this->BackendApp();
    }
    function BackendApp()
    {
        Lang::load(lang_file('admin/common'));
        Lang::load(lang_file('admin/' . APP));
        Lang::load(lang_file('common'));
        Lang::load(lang_file('my_goods'));
        parent::__construct();
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
            if (Conf::get('captcha_status.backend'))
            {
                $this->assign('captcha', 1);
            }
            $this->display('login.html');
        }
        else
        {
            if (Conf::get('captcha_status.backend') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
            {
                $this->show_warning('captcha_faild');

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

            /* ͨ����֤��ִ�е�½���� */
            if (!$this->_do_login($user_id))
            {
                return;
            }

            $this->show_message('login_successed',
                'go_to_admin', 'index.php');
        }
    }

    function logout()
    {
        parent::logout();
        $this->show_message('logout_successed',
            'go_to_admin', 'index.php');
    }

    /**
     * ִ�е�½����
     *
     * @param int $user_id
     * @return bool
     */
    function _do_login($user_id)
    {
        $mod_user =& m('member');
        $user_info = $mod_user->get(array(
            'conditions' => $user_id,
            'join'       => 'manage_mall',
            'fields'     => 'this.user_id, user_name, reg_time, last_login, last_ip, privs'
        ));

        if (!$user_info['privs'])
        {
            $this->show_warning('not_admin');

            return false;
        }

        /* ������� */
        $this->visitor->assign(array(
            'user_id'       => $user_info['user_id'],
            'user_name'     => $user_info['user_name'],
            'reg_time'      => $user_info['reg_time'],
            'last_login'    => $user_info['last_login'],
            'last_ip'       => $user_info['last_ip'],
        ));
        

        /* ���µ�¼��Ϣ */
        $time = gmtime();
        $ip   = real_ip();
        $mod_user->edit($user_id, "last_login = '{$time}', last_ip='{$ip}', logins = logins + 1");

        return true;
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
        $lang = Lang::fetch(lang_file('admin/jslang'));
        parent::jslang($lang);
    }

    /**
     *    ��̨����ҪȨ����֤����
     *
     *    @author    Garbin
     *    @return    void
     */
    function _run_action()
    {
        /* ���ж��Ƿ��¼ */
        if (!$this->visitor->has_login)
        {
            $this->login();

            return;
        }

        /* ��¼���ж��Ƿ���Ȩ�� */
        if (!$this->visitor->i_can('do_action', $this->visitor->get('privs')))
        {
            $this->show_warning('no_permission');

            return;
        }

        /* ���� */
        parent::_run_action();
    }

    function _config_view()
    {
        parent::_config_view();
        $this->_view->template_dir  = APP_ROOT . '/templates';
        $this->_view->compile_dir   = ROOT_PATH . '/temp/compiled/admin';
        $this->_view->res_base      = SITE_URL . '/administr/templates';
        $this->_view->lib_base      = SITE_URL . '/includes/libraries/javascript';
        $this->_view->wid_base 		= ROOT_PATH.'/data/widgetImage';
    }
    
    /**
     *   ��ȡ�̳ǵ�ǰģ������
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
     *    ��ȡ�̳ǵ�ǰ�������
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
    
    function _init_visitor()
    {
        $this->visitor =& env('visitor', new AdminVisitor());
    }

    /* ������� */
    function _clear_cache()
    {
        $cache_server =& cache_server();
        $cache_server->clear();
    }
}

/**
 *    ��̨������
 *
 *    @author    Garbin
 *    @usage    none
 */
class AdminVisitor extends BaseVisitor
{
    var $_info_key = 'admin_info';
    /**
     *    ��ȡ�û���ϸ��Ϣ
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_detail()
    {
        $model_member =& m('member');
        $detail = $model_member->get(array(
            'conditions'    => "member.user_id = '{$this->info['user_id']}'",
            'join'          => 'manage_mall',                 //�������ҿ����Ƿ��е���
        ));
        unset($detail['user_id'], $detail['user_name'], $detail['reg_time'], $detail['last_login'], $detail['last_ip']);

        return $detail;
    }
}

/* ʵ����Ϣ������ӿ� */
class MessageBase extends BackendApp {};

/* ʵ��ģ�������ӿ� */
class BaseModule  extends BackendApp {};

/* ��Ϣ������ */
require(ROOT_PATH . '/core/controller/message.base.php');

?>
