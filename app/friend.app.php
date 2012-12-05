<?php

class FriendApp extends MemberbaseApp
{
    /**
     *    �����б�
     *
     *    @author    Hyber
     *    @return    void
     */
    function index()
    {
        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                         LANG::get('friend'),         'index.php?app=friend',
                         LANG::get('friend_list')
                         );

        /* ��ǰ�����Ӳ˵� */
        $this->_curmenu('friend_list');
        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('friend');
        $page = $this->_get_page(10);

        $ms =& ms();
        $friends = $ms->friend->get_list($this->visitor->get('user_id'), $page['limit']);

        $page['item_count'] = $ms->friend->get_count($this->visitor->get('user_id'));   //��ȡͳ�Ƶ�����
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
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->_format_page($page);
        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->assign('friends', $friends);
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('friend'));
    	/* edit by lihuoliang 2011/08/24 (�޸ĺ�̨��ʽ��ȡģ��)*/
        if ($_GET['from']=='storeadmin')
        {
            $this->display('storeadmin.friend.index.html');
        }else
        {
            $this->display('friend.index.html');
        }
    }

    /**
     *    ��Ӻ���
     *
     *    @author    Hyber
     *    @return    void
     */
    function add()
    {
        if (!IS_POST){
            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                             LANG::get('friend'),         'index.php?app=friend',
                             LANG::get('add_friend')
                             );
             header('Content-Type:text/html;charset=' . CHARSET);
            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('add_friend');
            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('friend');
            $this->display('friend.form.html');
        }
        else
        {
            $user_name = str_replace(Lang::get('comma'), ',', $_POST['user_name']); //�滻���ĸ�ʽ�Ķ���
            if (!$user_name)
            {
                $this->pop_warning('input_username');
                return;
            }
            $user_names = explode(',',$user_name); //�����ŷָ���û���ת��������
            $mod_member = &m('member');
            $members = $mod_member->find("user_name " . db_create_in($user_names));
            $friend_ids = array_keys($members);
            if (!$friend_ids)
            {
                $this->pop_warning('no_such_user');
                return;
            }

            $ms =& ms();
            $ms->friend->add($this->visitor->get('user_id'), $friend_ids);
            if ($ms->has_error())
            {
                $this->pop_warning($ms->friend->get_error());

                return;
            }
            $this->pop_warning('ok', APP.'_'.ACT);
            /*$this->show_message('add_friend_successed',
                'back_list',    'index.php?app=friend',
                'continue_add', 'index.php?app=friend&amp;act=add'
            );*/
        }
    }

    /**
     *    ɾ������
     *
     *    @author    Hyber
     *    @return    void
     */
    function drop()
    {
        $user_ids = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
        if (!$user_ids)
        {
            $this->show_warning('no_such_friend');
            return;
        }
        $user_ids = explode(',',$user_ids);

        $ms =& ms();
        $result = $ms->friend->drop($this->visitor->get('user_id'), $user_ids);
        if (!$result)    //ɾ��
        {
        	if ($_GET['from'] == 'storeadmin')
            {
            	$this->show_storeadmin_warning($ms->friend->get_error());
            }else
            {
            	$this->show_warning($ms->friend->get_error());
            }
            return;
        }

        /* ɾ���ɹ����� */
    	if ($_GET['from'] == 'storeadmin')
        {
            $this->show_storeadmin_message('drop_friend_successed');
        }else
        {
            $this->show_message('drop_friend_successed');
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
        return array(
            array(
                'name'  => 'friend_list',
                'url'   => 'index.php?app=friend',
            ),
        );
    }
}
?>
