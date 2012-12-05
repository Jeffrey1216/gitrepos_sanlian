<?php

/**
 * api����������
 */
class ApiApp extends ECBaseApp
{
    function _init_visitor()
    {
        $this->visitor =& env('visitor', new ApiVisitor());
    }

    /**
     * ִ�е�½����
     * �������Ҫ�� frontend.base.php �е� _do_login ����һ��
     */
    function _do_login($user_id)
    {
        $mod_user =& m('member');

        $user_info = $mod_user->get(array(
            'conditions'    => "user_id = '{$user_id}'",
            'join'          => 'has_store',
            'fields'        => 'user_id, user_name, reg_time, last_login, last_ip, store_id',
        ));
        /* ����ID */
        $my_store = empty($user_info['store_id']) ? 0 : $user_info['store_id'];

        /* ��֤������������ */
        unset($user_info['store_id']);

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
    }

    /**
     * ִ���˳�����
     */
    function _do_logout()
    {
        $this->visitor->logout();
    }
}

/**
 *    api������
 */
class ApiVisitor extends BaseVisitor
{
    var $_info_key = 'user_info';
}

?>