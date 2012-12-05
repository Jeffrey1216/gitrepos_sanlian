<?php

class MessageApp extends MemberbaseApp
{
    /**
     *    �¶���
     *
     *    @author    Hyber
     *    @return    void
     */
    function newpm()
    {
        $this->_clear_newpm_cache();

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                         LANG::get('message'),         'index.php?app=message&amp;act=newpm',
                         LANG::get('newpm')
                         );

        /* ��ǰ�����Ӳ˵� */
        $this->_curmenu('newpm');
        /* ��ǰ�û�������Ϣ*/
        $this->_get_user_info();
        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('newpm');
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
        $this->assign('messages', $this->_list_message('newpm', $this->visitor->get('user_id')));
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('newpm'));
    	/* edit by lihuoliang 2011/08/24 (�޸ĺ�̨��ʽ��ȡģ��)*/
        if ($_GET['from']=='storeadmin')
        {
            $this->display('storeadmin.message.box.html');
        }else
        {
            $this->display('message.box.html');
        }
    }

    /**
     *    ������
     *
     *    @author    Hyber
     *    @return    void
     */
    function privatepm()
    {
        $this->_clear_newpm_cache();

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                         LANG::get('message'),         'index.php?app=message&amp;act=newpm',
                         LANG::get('privatepm')
                         );
        /* ��ǰ�����Ӳ˵� */
        $this->_curmenu('privatepm');
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
        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('message');
        $messages = $this->_list_message('privatepm', $this->visitor->get('user_id'));
        $this->assign('messages', $messages);
        $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('privatepm'));
    	/* edit by lihuoliang 2011/08/24 (�޸ĺ�̨��ʽ��ȡģ��)*/
        if ($_GET['from']=='storeadmin')
        {
            $this->display('storeadmin.message.box.html');
        }else
        {
            $this->display('message.box.html');
        }
    }
    
    function systempm()
    {
        $this->_clear_newpm_cache();

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                         LANG::get('message'),         'index.php?app=message&amp;act=newpm',
                         LANG::get('systempm')
                         );
        /* ��ǰ�����Ӳ˵� */
        $this->_curmenu('systempm');
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
        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('message');
        $this->assign('messages', $this->_list_message('systempm', $this->visitor->get('user_id')));
        $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('systempm'));
    	/* edit by lihuoliang 2011/08/24 (�޸ĺ�̨��ʽ��ȡģ��)*/
        if ($_GET['from']=='storeadmin')
        {
            $this->display('storeadmin.message.box.html');
        }else
        {
            $this->display('message.box.html');
        }
    }
    
    function announcepm()
    {
        $this->_clear_newpm_cache();

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                         LANG::get('message'),         'index.php?app=message&amp;act=newpm',
                         LANG::get('announcepm')
                         );
        /* ��ǰ�����Ӳ˵� */
        $this->_curmenu('announcepm');
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
        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('message');
        $this->assign('messages', $this->_list_message('announcepm', $this->visitor->get('user_id')));
        $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('announcepm'));
        $this->display('message.box.html');
    }
    /**
     *    ���Ͷ���Ϣ
     *
     *    @author    Hyber
     *    @return    void
     */
    function send()
    {

        if (!IS_POST){
            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                             LANG::get('message'),         'index.php?app=message&amp;act=newpm',
                             LANG::get('send_message')
                             );
            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('send_message');
            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('my_new_info');
            /* ��ǰ�û�������Ϣ*/
            $this->_get_user_info();
            $to_ids = array(); //��ֹforeach����
            $to_id = trim($_GET['to_id']); //��ȡurl�е�to_id
            $to_id && $to_ids = explode(',',$to_id); //ת��������
            $mod_member = &m('member');
            foreach ($to_ids as $key => $to_id)
            {
                /* ����û����� ����$to_user_name������ */
                $user_name = $mod_member->get_info(intval($to_id));
                $user_name && $to_user_name[] = $user_name['user_name'];
            }
            /* ����û������ڣ���ֵ��$_GET,����ģ���ȡ */
            isset($to_user_name) && $_GET['to_user_name'] = implode(',', $to_user_name);

            header('Content-Type:text/html;charset=' . CHARSET);
            /* ���� */
            $friends = $this->_list_friend();
            $this->assign('friends',        $friends);
            $this->assign('friend_num',    count($friends));

            //����jquery�����
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('send_message'));
	        /* edit by lihuoliang 2011/08/24 (�޸ĺ�̨��ʽ��ȡģ��)*/
	        if ($_GET['from']=='storeadmin')
	        {
	            $this->display('storeadmin.message.send.html');
	        }else
	        {
	            $this->display('message.send.html');
	        }
        }
        else
        {
            $to_user_name = str_replace(Lang::get('comma'), ',', trim($_POST['to_user_name'])); //�滻���ĸ�ʽ�Ķ���
            if (!$to_user_name)
            {
	            if ($_POST['from']=='storeadmin')
		        {
		            $this->show_storeadmin_warning('no_to_user_name'); //û����д�û���
		        }else
		        {
		            $this->show_warning('no_to_user_name'); //û����д�û���
		        }
                return;
            }
            $to_user_names = explode(',', $to_user_name); //�����ŷָ���û���ת��������
            $mod_member = &m('member');
            $members = $mod_member->find('user_name ' . db_create_in($to_user_names));
            $to_ids = array();
            foreach ($members as $_user)
            {
                if (isset($_user['user_id']) && $_user['user_id']!= $this->visitor->get('user_id'))
                {
                    $to_ids[] = $_user['user_id'];
                }
            }
            if (!$to_ids)
            {
            	if ($_POST['from']=='storeadmin')
		        {
		            $this->show_storeadmin_warning('no_user_self'); //û�и��û���
		        }else
		        {
		            $this->show_warning('no_user_self'); //û�и��û���
		        }
                return;
            }

            /* �����û�ϵͳ */
            $ms =& ms();
            $msg_id = $ms->pm->send($this->visitor->get('user_id'), $to_ids, '', $_POST['msg_content']);
            if (!$msg_id)
            {
                //$this->show_warning($ms->pm->get_error());
                $rs = $ms->pm->get_error();
                $msg = current($rs);
            	if ($_POST['from']=='storeadmin')
		        {
		            $this->show_storeadmin_warning($msg['msg'], 'go_back', 'index.php?app=message&act=send&from=storeadmin');
		        }else
		        {
		            $this->show_warning($msg['msg'], 'go_back', 'index.php?app=message&act=send');
		        }
                return;
            }
        	if ($_POST['from']=='storeadmin')
	        {
	            $this->show_storeadmin_message('send_message_successed', 'go_back', 'index.php?app=message&act=privatepm&from=storeadmin');
	        }else
	        {
	            $this->show_message('send_message_successed', 'go_back' , 'index.php?app=message&act=privatepm');
	        }
        }
    }

    /**
     *    �鿴����Ϣ
     *
     *    @author    Hyber
     *    @return    void
     */
    function view()
    {
        $this->_clear_newpm_cache();
        /* ��ǰ�û�������Ϣ*/
        $this->_get_user_info();

        $msg_id = isset($_GET['msg_id']) ? intval($_GET['msg_id']) : 0;
        if (!$msg_id)
        {
        	if ($_GET['from']=='storeadmin')
	        {
	            $this->show_storeadmin_warning('no_such_message');
	        }else
	        {
	            $this->show_warning('no_such_message');
	        }
            return;
        }
        $my_id = $this->visitor->get('user_id');
        $ms =& ms();
        if (!IS_POST)
        {
            $message = $ms->pm->get($this->visitor->get('user_id'), $msg_id, true);
            if (empty($message))
            {
	            if ($_GET['from']=='storeadmin')
		        {
		            $this->show_storeadmin_warning('no_such_message');
		        }else
		        {
		            $this->show_warning('no_such_message');
		        }
                return;
            };
            $new = $message['topic']['new'];
            !empty($new) && $ms->pm->mark($this->visitor->get('user_id'), array($msg_id), 0); //��ʾ�Ѷ�
            
            $box = '';
            
            if ($message['topic']['from_id'] == 0 && $message['topic']['to_id'] == 0 )
            {
                $box = 'announcepm';
            }
            elseif ($message['topic']['from_id'] == MSG_SYSTEM)
            {
                $box = 'systempm';
            }
            elseif ($my_id == $message['topic']['from_id'] || $my_id == $message['topic']['to_id'])
            {
                $box = 'privatepm';
            }
            $ms = &ms();
            if ($message['topic']['from_id'] == 0 && $message['topic']['to_id'] == 0)
            {
                $message['topic']['user_name'] = Lang::get('announce_msg');
                $message['topic']['portrait'] = portrait(0, '');
            }
            elseif ($message['topic']['from_id'] == MSG_SYSTEM)
            {
                $message['topic']['user_name'] = Lang::get('system_msg');
                $message['topic']['portrait'] = portrait(0, '');
            }
            else
            {
                $uid = $message['topic']['from_id'];
                $user_info = $ms->user->get($uid);
                $message['topic']['user_name'] = $user_info['user_name'];
                $portrait = portrait($user_info['user_id'], $user_info['portrait']);
                $message['topic']['portrait'] = $portrait;
            }
            
            $uid = 0;
            $user_info = array();
            
            foreach ($message['replies'] as $key => $value)
            {
                $uid = $value['from_id'];
                $user_info = $ms->user->get($uid);
                $message['replies'][$key]['user_name'] = $user_info['user_name'];
                $portrait = portrait($user_info['user_id'], $user_info['portrait']);
                $message['replies'][$key]['portrait'] = $portrait;
            }
            $this->assign('message', $message['topic']);
            $this->assign('replies', $message['replies']);
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('view_message'));
            $this->assign('box', $box);
            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                             LANG::get('message'),         'index.php?app=message&amp;act=newpm',
                             LANG::get('view_message')
                             );
            /* ��ǰ�����Ӳ˵���������������������Ϣ��������ȷ�� */
            $this->_curmenu('view_message');
            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('message');
        	/* edit by lihuoliang 2011/08/24 (�޸ĺ�̨��ʽ��ȡģ��)*/
	        if ($_GET['from']=='storeadmin')
	        {
	            $this->display('storeadmin.message.view.html');
	        }else
	        {
	            $this->display('message.view.html');
	        }
        }
        else
        {
            $message = $ms->pm->get($this->visitor->get('user_id'), $msg_id);
            $reply_to_id = 0;
            if ($my_id == $message['topic']['to_id'])
            {
                $reply_to_id = $message['topic']['from_id'];
            }
            elseif ($my_id == $message['topic']['from_id'])
            {
                $reply_to_id = $message['topic']['to_id'];
            }

            if (empty($reply_to_id) || $reply_to_id == MSG_SYSTEM)
            {
	            if ($_POST['from']=='storeadmin')
		        {
		            $this->show_storeadmin_warning('cannot_replay_system_message');
		        }else
		        {
		            $this->show_warning('cannot_replay_system_message');
		        }
                return;
            }

            $mod_member = &m('member');
            if (!$mod_member->get_info($reply_to_id))
            {
            	if ($_POST['from']=='storeadmin')
		        {
		            $this->show_storeadmin_warning('no_such_user');
		        }else
		        {
		            $this->show_warning('no_such_user');
		        }
                return;
            }
            if (!$msg_id = $ms->pm->send($this->visitor->get('user_id'), $reply_to_id, '', $_POST['msg_content'] , $msg_id))  //��ȡmsg_id
            {
            	if ($_POST['from']=='storeadmin')
		        {
		            $this->show_storeadmin_warning($ms->pm->get_error());
		        }else
		        {
		            $this->show_warning($ms->pm->get_error());
		        }

                return;
            }
        	if ($_POST['from']=='storeadmin')
	        {
	            $this->show_storeadmin_message('send_message_successed');
	        }else
	        {
	            $this->show_message('send_message_successed');
	        }
        }
    }

    /**
     *    ɾ������Ϣ
     *
     *    @author    Hyber
     *    @return    void
     */
    function drop()
    {
        $msg_ids = isset($_GET['msg_id']) ? trim($_GET['msg_id']) : '';
        if(in_array($_GET['back'],array('newpm','privatepm')))
        {
            $folder = trim($_GET['back']);
        }
        if (!$msg_ids)
        {
        	if ($_GET['from']=='storeadmin')
	        {
	            $this->show_storeadmin_warning('no_such_message');
	        }else
	        {
	            $this->show_warning('no_such_message');
	        }
            return;
        }
        $msg_ids = explode(',',$msg_ids);
        if (!$msg_ids)
        {
        	if ($_GET['from']=='storeadmin')
	        {
	            $this->show_storeadmin_warning('no_such_message');
	        }else
	        {
	            $this->show_warning('no_such_message');
	        }
            return;
        }
        $ms =& ms();
        if (!$ms->pm->drop($this->visitor->get('user_id'), $msg_ids, $folder))    //ɾ��������Ϣ
        {
        	if ($_GET['from']=='storeadmin')
	        {
	            $this->show_storeadmin_warning('drop_error');
	        }else
	        {
	            $this->show_warning('drop_error');
	        }
            return;
        }

        /* ɾ���ɹ����� */
        if (in_array($_GET['back'],array('newpm', 'privatepm')))
        {
        	if ($_GET['from']=='storeadmin')
	        {
	            $this->show_storeadmin_message('drop_message_successed',
                	'back_' . $_GET['back'] ,'index.php?app=message&amp;act=' . $_GET['back'].'&amp;from=storeadmin');
	        }else
	        {
	            $this->show_message('drop_message_successed',
                	'back_' . $_GET['back'] ,'index.php?app=message&amp;act=' . $_GET['back']);
	        }
        }
        else
        {
        	if ($_GET['from']=='storeadmin')
	        {
	            $this->show_storeadmin_message('drop_message_successed');
	        }else
	        {
	            $this->show_message('drop_message_successed');
	        }
        }
    }
    
    /**
     * ɾ����û�Ա�����лỰ��UC�Ķ���Ϣ�������˵Ĺ�ϵ����������ͻظ���
     * 
     * @return void
     *
     */
    function drop_relate()
    {
        $msg_ids = isset($_GET['msg_id']) ? trim($_GET['msg_id']) : '';
        if(in_array($_GET['back'],array('newpm', 'privatepm')))
        {
            $folder = trim($_GET['back']);
        }
        if (!$msg_ids)
        {
        	if ($_GET['from']=='storeadmin')
	        {
	            $this->show_storeadmin_warning('no_such_message');
	        }else
	        {
	            $this->show_warning('no_such_message');
	        }
            return;
        }
        $msg_id = intval($msg_ids);
        if (!$msg_id)
        {
        	if ($_GET['from']=='storeadmin')
	        {
	            $this->show_storeadmin_warning('no_such_message');
	        }else
	        {
	            $this->show_warning('no_such_message');
	        }
            return;
        }
        $ms =& ms();
        if (!$ms->pm->drop($this->visitor->get('user_id'), $msg_id, $folder, true))    //ɾ��
        {
        	if ($_GET['from']=='storeadmin')
	        {
	            $this->show_storeadmin_warning($ms->pm->get_error());
	        }else
	        {
	            $this->show_warning($ms->pm->get_error());
	        }

            return;
        }

        /* ɾ���ɹ����� */
        if (in_array($_GET['back'],array('newpm', 'privatepm')))
        {
        	if ($_GET['from']=='storeadmin')
	        {
	            $this->show_storeadmin_message('drop_message_successed',
                	'back_' . $_GET['back'] ,'index.php?app=message&amp;act=' . $_GET['back'].'&amp;from=storeadmin');
	        }else
	        {
	            $this->show_message('drop_message_successed',
                	'back_' . $_GET['back'] ,'index.php?app=message&amp;act=' . $_GET['back']);
	        }
        }
        else
        {
        	if ($_GET['from']=='storeadmin')
	        {
	            $this->show_storeadmin_message('drop_message_successed');
	        }else
	        {
	            $this->show_message('drop_message_successed');
	        }
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
        $ms =& ms();
        $new = $ms->pm->check_new($this->visitor->get('user_id'));
        $new && $newpm = "(". $new . ")";
        $menus = array(
                array(
                    'name'  => 'newpm',
                    'url'   => 'index.php?app=message&amp;act=newpm',
                    'text'  => Lang::get('newpm') . $newpm,
                ),
                array(
                    'name'  => 'privatepm',
                    'url'   => 'index.php?app=message&amp;act=privatepm',
                    'text'  => Lang::get('privatepm'),
                ),
                array(
                    'name'  => 'systempm',
                    'url'   => 'index.php?app=message&amp;act=systempm',
                    'text'  => Lang::get('systempm'),
                ),
        );
        if ($ms->pm->show_announce)
        {
            $menus[] = array(
                    'name'  => 'announcepm',
                    'url'   => 'index.php?app=message&amp;act=announcepm',
                    'text'  => Lang::get('announcepm'),
                );
        }

        ACT == 'send' && $menus[] = array(
                'name' => 'send_message',
        );

        ACT == 'view' && $menus[] = array(
                'name' => 'view_message',
        );
        return $menus;
    }

    function _list_message($pattern, $user_id)
    {
        /* �����û�ϵͳ */
        $user_id = intval($user_id);
        if (!$user_id){
            $this->show_warning('no_such_user');

            return;
        }
        if (!in_array($pattern, array('newpm', 'privatepm', 'announcepm', 'systempm')))
        {
            $this->show_warning('request_error');
            exit;
        }
        $page = $this->_get_page(10);
        $ms =& ms();
        $pms = $ms->pm->get_list($user_id, $page, $pattern);
        $page['item_count'] = $pms['count'];
        $this->_format_page($page);
        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        
        //����ȡ��������
        $my_id = $this->visitor->get('user_id');
        $ms = &ms();
        //$i_send = 0;
        $messages = $pms['data'];
        foreach ($messages as $key=>$message)
        {
            //$i_send = $message['to_id'] == $my_id ? 0 : 1;
            $user_info = $ms->user->get($message['to_id'] == $my_id ? $message['from_id'] : $message['to_id']);
            //$messages[$key]['i_send'] = $i_send;
            if ($message['from_id'] == 0 && $message['to_id'] == 0)
            {
                $user_info['user_name'] = Lang::get('announce_msg');
                $user_info['user_id'] = 0;
                $user_info['portrait'] = '';
            }
            elseif ($message['from_id'] == MSG_SYSTEM) 
            {
                $user_info['user_name'] = Lang::get('system_msg');
                $user_info['user_id'] = 0;
                $user_info['portrait'] = '';
            }
            $user_info['portrait'] = portrait($user_info['user_id'], $user_info['portrait']);
            $messages[$key]['user_info'] = $user_info;
            //$messages[$key]['i_send'] = $i_send;
        }
        return $messages;
    }
    function _list_friend()
    {
        $friends = array();
        $ms =& ms();
        $friends = $ms->friend->get_list($this->visitor->get('user_id'), '0, 10000');

        return $friends;
    }

    function _clear_newpm_cache()
    {
        /* ����¶���Ϣ���� */
        $cache_server =& cache_server();
        $cache_server->delete('new_pm_of_user_' . $this->visitor->get('user_id'));
    }
}
?>
