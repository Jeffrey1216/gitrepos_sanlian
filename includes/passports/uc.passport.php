<?php

include (ROOT_PATH . '/uc_client/client.php');

/**
 *    UCenter���ӽӿ�
 *
 *    @author    Garbin
 *    @usage    none
 */
class UcPassport extends BasePassport
{
    var $_name = 'uc';
    function tag_get($tag)
    {
        $cache_server = &cache_server();
        $cache_key    = 'uc_app_list';
        $uc_app_list  = $cache_server->get($cache_key);
        if ($uc_app_list === false)
        {
            $uc_app_list = outer_call('uc_app_ls');
            $cache_server->set($cache_key, $uc_app_list, 86400);
        }
        $nums = array();
        $related_info = array('count' => 0);
        foreach ($uc_app_list as $app_id => $app_info)
        {
            $nums[$app_id] = 10;
            $related_info['list'][$app_id] = array(
                'app_name' => $app_info['name'],
                'app_type' => $app_info['type'],
                'app_url'  => $app_info['url'],
                'data'     => array(),
            );
        }
        $data_list = outer_call('uc_tag_get', array($tag, $nums));
        if ($data_list)
        {
            foreach ($data_list as $_data_app_id => $data)
            {
                foreach ($data['data'] as $value)
                {
                    $data_key = array_keys($value);
                    array_walk($data_key, create_function('&$item, $key', '$item=\'{\' . $item . \'}\';'));
                    $item = str_replace($data_key, $value, $uc_app_list[$_data_app_id]['tagtemplates']['template']);
                    $related_info['count']++;
                    $related_info['list'][$_data_app_id]['data'][] = $item;
                }
            }
        }

        return $related_info;
    }    
}

/**
 *    UCenter���û�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class UcPassportUser extends BasePassportUser
{

    /* ע�� */
    function register($user_name, $password, $email, $local_data = array())
    {
        /* ��UCenterע�� */
        $user_id = outer_call('uc_user_register', array($user_name, $password, $email));
        if ($user_id < 0)
        {
            switch ($user_id)
            {
                case -1:
                    $this->_error('invalid_user_name');
                    break;
                case -2:
                    $this->_error('blocked_user_name');
                    break;
                case -3:
                    $this->_error('user_exists');
                    break;
                case -4:
                    $this->_error('email_error');
                    break;
                case -5:
                    $this->_error('blocked_email');
                    break;
                case -6:
                    $this->_error('email_exists');
                    break;
            }

            return false;
        }

        /* ͬ�������� */
        $local_data['user_name']    = $user_name;
        $local_data['password']     = md5(time() . rand(100000, 999999));
        $local_data['email']        = $email;
        $local_data['reg_time']     = gmtime();
        $local_data['user_id']      = $user_id;

        /* ��ӵ��û�ϵͳ */
        $this->_local_add($local_data);

        return $user_id;
    }

    /* �༭�û����� */
    function edit($user_id, $old_password, $items, $force = false)
    {
        $new_pwd = $new_email = '';
        if (isset($items['password']))
        {
            $new_pwd  = $items['password'];
        }
        if (isset($items['email']))
        {
            $new_email = $items['email'];
        }
        $info = $this->get($user_id);
        if (empty($info))
        {
            $this->_error('no_such_user');

            return false;
        }

        /* �ȵ�UCenter�޸� */
        $result = outer_call('uc_user_edit', array($info['user_name'], $old_password, $new_pwd, $new_email, $force));
        if ($result != 1)
        {
            switch ($result)
            {
                case 0:
                case -7:
                    return true;
                    break;
                case -1:
                    $this->_error('auth_failed');

                    return false;
                    break;
                case -4:
                    $this->_error('email_error');

                    return false;
                    break;
                case -5:
                    $this->_error('blocked_email');

                    return false;
                    break;
                case -6:
                    $this->_error('email_exists');

                    return false;
                    break;
                case -8:
                    $this->_error('user_protected');

                    return false;
                    break;
                default:
                    $this->_error('unknow_error');

                    return false;
                    break;
            }
        }

        /* �ɹ���༭�������� */
        $local_data = array();
        if ($new_pwd)
        {
            $local_data['password'] = md5(time() .  rand(100000, 999999));
        }
        if ($new_email)
        {
            $local_data['email']    = $new_email;
        }

        //�༭��������
        $this->_local_edit($user_id, $local_data);

        return true;
    }

    /* ɾ���û� */
    function drop($user_id)
    {
        if (empty($user_id))
        {
            $this->_error('no_such_user');

            return false;
        }

        /* �ȵ�UCenter��ɾ�� */
        /*$result = outer_call('uc_user_delete', array($user_id));
        outer_call('uc_user_deleteavatar', array($user_id));
        if (!$result)
        {
            $this->_error('uc_drop_user_failed');

            return false;
        }*/

        /* ��ɾ�����ص� */
        return $this->_local_drop($user_id);
    }

    /* ��ȡ�û���Ϣ */
    function get($flag, $is_name = false)
    {
        /* ��UCenterȡ�û� */
        $user_info = outer_call('uc_get_user', array($flag, !$is_name));
        if (empty($user_info))
        {
            $this->_error('no_such_user');

            return false;
        }
        list($user_id, $user_name, $email) = $user_info;

        /* ͬ�������� */
        $this->_local_sync($user_id, $user_name, $email);

        return array(
                'user_id'   =>  $user_id,
                'user_name' =>  $user_name,
                'email'     =>  $email,
                'portrait'  =>  portrait($user_id, '')
                );
    }

    /**
     *    ��֤�û���¼
     *
     *    @author    Garbin
     *    @param     $string $user_name
     *    @param     $string $password
     *    @return    int    �û�ID
     */
    function auth($user_name, $password)
    {
        register_shutdown_function('restore_error_handler'); // �ָ�PHPϵͳĬ�ϵĴ�����
        $result = outer_call('uc_user_login', array($user_name, $password));
        if ($result[0] < 0)
        {
            switch ($result[0])
            {
                case -1:
                    $this->_error('no_such_user');
                    break;
                case -2:
                    $this->_error('password_error');
                    break;
                case -3:
                    $this->_error('answer_error');
                    break;
                default:
                    $this->_error('unknow_error');
                    break;
            }

            return false;
        }

        /* ͬ�������� */
        $this->_local_sync($result[0], $result[1], $result[3]);

        /* �����û�ID */
        return $result[0];
    }

    /**
     *    ͬ����¼
     *
     *    @author    Garbin
     *    @param     int $user_id
     *    @return    string
     */
    function synlogin($user_id)
    {
        return outer_call('uc_user_synlogin', array($user_id));
    }

    /**
     *    ͬ���˳�
     *
     *    @author    Garbin
     *    @return    string
     */
    function synlogout()
    {
        return outer_call('uc_user_synlogout');
    }

    /**
     *    �������ʼ��Ƿ�Ψһ
     *
     *    @author    Garbin
     *    @param     string $email
     *    @return    bool
     */
    function check_email($email)
    {
        $result = outer_call('uc_user_checkemail', array($email));
        if ($result < 0)
        {
            switch ($result)
            {
                case -4:
                    $this->_error('email_error');
                    break;
                case -5:
                    $this->_error('blocked_email');
                    break;
                case -6:
                    $this->_error('email_exists');
                    break;
                default:
                    $this->_error('unknow_error');
                    break;
            }

            return false;
        }

        return true;
    }

    /**
     *    ����û����Ƿ�Ψһ
     *
     *    @author    Garbin
     *    @param     string $user_name
     *    @return    bool
     */
    function check_username($user_name)
    {
        $result = outer_call('uc_user_checkname', array($user_name));
        if ($result < 0)
        {
            switch ($result)
            {
                case -1:
                    $this->_error('invalid_user_name');
                    break;
                case -2:
                    $this->_error('blocked_user_name');
                    break;
                case -3:
                    $this->_error('user_exists');
                    break;
                default:
                    $this->_error('unknow_error');
                    break;
            }
            return false;
        }

        return true;
    }

    /**
     *    ����ͷ��
     *
     *    @author    Garbin
     *    @param     int $user_id
     *    @return    string
     */
    function set_avatar($user_id = 0)
    {
        return outer_call('uc_avatar', array($user_id));
    }

    /**
     *    ɾ��ͷ��
     *
     *    @author    Garbin
     *    @param     int $user_id
     *    @return    bool
     */
    function drop_avatar($user_id)
    {
        return outer_call('uc_user_deleteavatar', array($user_id));
    }
}

/**
 *    �����û����ĵĶ��Ų���
 *
 *    @author    Garbin
 *    @usage    none
 */
class UcPassportPM extends BasePassportPM
{
    var $show_announce;
    function __construct()
    {
        $this->UcPassportPM();
    }
    function UcPassportPM()
    {
        $this->show_announce = true;
        Lang::load(ROOT_PATH . '/includes/passports/' . MEMBER_TYPE . '/' . LANG . '/common.lang.php');
        if (file_exists(ROOT_PATH . '/data/msg.lang.php'))
        {
            Lang::load(ROOT_PATH . '/data/msg.lang.php');
        }
    }
    /**
     * ���Ͷ���Ϣ
     *
     * @param int $sender   ������
     * @param array $recipient  ������
     * @param string $subject   ����Ϣ����
     * @param string $message   ����Ϣ����
     * @param int $replyto      0�������µĶ���Ϣ ����0���ظ�����Ϣ
     * @return ���� 0:���ͳɹ������һ����Ϣ ID
                0:����ʧ��
                -1:������24Сʱ��������Ͷ���Ϣ��Ŀ
                -2:���������η��Ͷ���Ϣ��̼��
                -3:���ܸ��Ǻ����������Ͷ���Ϣ
                -4:Ŀǰ������ʹ�÷��Ͷ���Ϣ���ܣ�ע������պ�ſ���ʹ�÷�����Ϣ���ƣ�
     */
    function send($sender, $recipient, $subject, $message, $replyto = 0)
    { 
        $recipient = is_array($recipient) ? implode(',', $recipient) : $recipient;
        return  outer_call('uc_pm_send',array($sender, $recipient, '',$message, 1, 0, 0));
    }
    /**
     * ȡ�����ض���Ա�����лỰ
     *
     * @param int $user_id  ӵ����
     * @param int $pm_id
     * @return array ��������ͻظ��Ķ���Ϣ��UCû������ͻظ������֣�ֻ����һ����Ϊ���⣬��������Ϊ�ظ���
     */
    function get($user_id, $pm_id, $full = false)
    {

        $message = outer_call('uc_pm_viewnode', array($user_id, 0, $pm_id));
        if (empty($message))
        {
            return array();
        }
        $uid = ($user_id == $message['msgfromid']) ? $message['msgtoid'] : $message['msgfromid'];
        if ($message['msgfromid'] == MSG_SYSTEM)
        {
            $uid = 0;
        }
        $rs = outer_call('uc_pm_view', array($user_id, '', $uid, 5));
        $new = 0;
        if (empty($uid))
        {
            $rs = array($message);   
        }
        $result = array();
        foreach ($rs as $value)
        {
            $result[$value['pmid']]['from_id'] = $value['msgfromid'];
            $result[$value['pmid']]['to_id'] = $value['msgtoid'];
            $result[$value['pmid']]['new'] = $value['new'];
            $result[$value['pmid']]['add_time'] = $value['dateline'];
            $tmp = '';
            $result[$value['pmid']]['content'] = $value['message'];
            $result[$value['pmid']]['msg_id'] = $value['pmid'];
            if (empty($new) && $value['new'])
            {
                $new = 1;
            }
        }  
        $topic['new'] = $new;
        $topic = current(array_slice($result, 0, 1));
        
        $replies = array_slice($result, 1);
        return array('topic' => $topic, 'replies' => $replies);
    }
    /**
     * ȡ��δ������Ϣ��˽�˶��š�ϵͳ���š��������
     *
     * @param int $user_id
     * @param array $page ����limit,curr_page,pageper
     * @param string $folder
     * @return array  ���� data count ������
     */
    function get_list($user_id, $page, $folder = 'newpm')
    {
        $result = array();
        $rs = array();
        
        switch ($folder)
        {
            case 'newpm':
                $rs = outer_call('uc_pm_list', array($user_id, $page['curr_page'], $page['pageper'], 'inbox', 'newpm', 200));
                break;
            case 'privatepm':
                $rs = outer_call('uc_pm_list', array($user_id, $page['curr_page'], $page['pageper'], 'inbox', 'privatepm', 200));
                break;
            case 'systempm':
                $rs = outer_call('uc_pm_list', array($user_id, $page['curr_page'], $page['pageper'], 'inbox', 'systempm', 200));
                break;
            case 'announcepm':
                $rs = outer_call('uc_pm_list', array($user_id, $page['curr_page'], $page['pageper'], 'inbox', 'announcepm', 200));
                break;
        }
        $new = 0;
        $tmp = '';
        !isset($rs['data']) && $rs['data'] = array();
        // ��ȡ�������ݸ�ʽ���ɱ��ص�����
        foreach ($rs['data'] as $value)
        {
            $result[$value['pmid']]['from_id'] = $value['msgfromid'];
            $result[$value['pmid']]['to_id'] = $value['msgtoid'];
            $result[$value['pmid']]['new'] = $value['new'];
            $result[$value['pmid']]['last_update'] = $value['dateline'];
            $result[$value['pmid']]['msg_id'] = $value['pmid'];    

            $result[$value['pmid']]['content'] = $value['subject'];
        }
        return array('data' => $result, 'count' => $rs['count']);
    }

    /**
     *    ����Ƿ��ж���Ϣ
     *
     *    @param     int $user_id
     *    @return    int ����Ϣ������
     * 
     * */
    function check_new($user_id)
    {
        $rs = outer_call('uc_pm_checknew', array($user_id, 4));
        return $rs['newpm'];
    }

    /**
     *    ɾ������Ϣ
     *
     *    @author    Garbin
     *    @param     int        $user_id ����Ϣӵ����
     *    @param     array      $pm_ids  ��ɾ���Ķ���Ϣ
     *    @param     string     $foloder    ��ѡֵ:inbox,outbox
     *    @return    int        ɾ���Ķ���Ϣ����
     */
    function drop($user_id, $pm_ids, $folder = 'inbox', $relate = false)
    { 
        $pm_ids = is_array($pm_ids) ? $pm_ids : array($pm_ids);
        if ($relate)
        {
            $pm_id = $pm_ids[0];
            $message = outer_call('uc_pm_viewnode', array($user_id, 0, $pm_id));
            $uid = ($user_id == $message['msgfromid']) ? $message['msgtoid'] : $message['msgfromid'];
            return outer_call('uc_pm_deleteuser',array($user_id, array($uid)));
        }
        return outer_call('uc_pm_delete', array($user_id, $folder, $pm_ids));
    }

    /**
     *    ����Ķ�״̬
     *
     *    @author    Garbin
     *    @param     int   $user_id   ����Ϣӵ����
     *    @param     array $pm_ids    ����ǵĶ���ϢID����
     *    @param     int   $status    ��ǳɵ�״̬��0Ϊ�Ѷ���1Ϊδ��
     *    @return    false:���ʧ��  true:��ǳɹ�
     */
    function mark($user_id, $pm_ids, $status = 0)
    {

        $uids = array();
        
        foreach ($pm_ids as $id)
        {
            $message = outer_call('uc_pm_viewnode', array($user_id, 0, $id));
            if ($message['msgfromid'] == 0 && $message['msgtoid'] != 0)
            {
                $uids = array();
                break;
            }
            if ($user_id == $message['msgtoid'])
            {
                $uids[] = $message['msgfromid'];
            }
            else 
            {
                $uids[] = $message['msgtoid'];
            }
        }
        return outer_call('uc_pm_readstatus', array($user_id, $uids, $pm_ids));
    }
    
    /**
     *  ����Ϣ��ʾ����
     *  @return string
     * 
     */
    function msg_filter($message)
    {
        return str_replace('&amp;', '&', $message); // ��ֹURL�е�&���ظ�ת��;
    }
}

/**
 *    �����û����ĵĺ��Ѳ���
 *
 *    @author    Garbin
 *    @usage    none
 */
class UcPassportFriend extends BasePassportFriend
{
    /**
     *    ����һ������
     *
     *    @author    Garbin
     *    @param     int $user_id       ����ӵ����
     *    @param     array $friend_ids    ����
     *    @return    false:ʧ�� true:�ɹ�
     */
    function add($user_id, $friend_ids)
    {
        $model_member =& m('member');
        $user_data = array();
        foreach ($friend_ids as $friend_id)
        {
            if ($friend_id == $user_id)
            {
                $this->_error('cannot_add_myself');

                return false;
            }
            $user_data[$friend_id] = array(
                    'add_time'  => gmtime()
                    );
        }

        return $model_member->createRelation('has_friend', $user_id ,$user_data);
    }

    /**
     *    ɾ��һ������
     *
     *    @author    Garbin
     *    @param     int $user_id       ����ӵ����
     *    @param     array $friend_id     ����
     *    @return    false:ʧ��   true:�ɹ�
     */
    function drop($user_id, $friend_ids)
    {
        $model_member =& m('member');

        return $model_member->unlinkRelation('has_friend', $user_id ,$friend_ids);
    }

    /**
     *    ��ȡ��������
     *
     *    @author    Garbin
     *    @param     int $user_id       ����ӵ����
     *    @return    int    ��������
     */
    function get_count($user_id)
    {
        $model_member =& m('member');

        return count($model_member->getRelatedData('has_friend', array($user_id)));
    }

    /**
     *    ��ȡ�����б�
     *
     *    @author    Garbin
     *    @param     int $user_id       ����ӵ����
     *    @param     string $limit      ����
     *    @return    array  �����б�
     */
    function get_list($user_id, $limit = '0, 10')
    {
        $model_member =& m('member');
        $friends = $model_member->getRelatedData('has_friend', array($user_id), array(
                    'limit' => $limit,
                    'order' => 'add_time DESC',
                    ));
        if (empty($friends))
        {
            $friends = array();
        }
        else
        {
            foreach ($friends as $_k => $f)
            {
                $friends[$_k]['portrait'] = portrait($f['user_id'], $f['portrait']);
            }
        }

        return $friends;
    }
}

/**
 *    UCenter���¼�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class UcPassportFeed extends BasePassportFeed
{
    /**
     *    ����¼�
     *
     *    @author    Garbin
     *    @param     array $feed    �¼�
     *    @return    false:ʧ��   true:�ɹ�
     */
    function add($event, $data)
    {
        $feed_info = $this->_get_feed_info($event, $data);
        return outer_call('uc_feed_add', array($feed_info['icon'], $feed_info['user_id'], $feed_info['user_name'], $feed_info['title']['template'], $feed_info['title']['data'], $feed_info['body']['template'], $feed_info['body']['data'], $feed_info['body_general'], $feed_info['target_ids'], $feed_info['images']));
    }

    /**
     * ͨ���¼������ݻ�ȡfeed��ϸ����
     *
     * @author Garbin
     * @param
     * @return void
     **/
    function _get_feed_info($event, $data)
    {
        $mall_name = '<a href="' . SITE_URL . '">' . Conf::get('site_name') . '</a>';
        switch ($event)
        {
            case 'order_created':
                $feed = array(
                    'icon'  => 'goods',
                    'user_id'  => $data['user_id'],
                    'user_name'  => $data['user_name'],
                    'title'  => array(
                        'template'  => Lang::get('feed_order_created.title'),
                        'data'      => array(
                            'store'    => '<a href="' . $data['store_url'] . '">' . $data['seller_name'] . '</a>',
                            ),
                        ),
                    'body'  => array(
                        'template'  => Lang::get('feed_order_created.body'),
                        ),
                    'images' => $data['images'],
                );
                break;
            case 'store_created':
                $feed = array(
                    'icon'  => 'profile',
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                    'title' => array(
                        'template' => Lang::get('feed_store_created.title'),
                        'data' => array(
                            'mall_name' => $mall_name,
                            'store' => '<a href="' . $data['store_url'] . '">' . $data['seller_name'] . '</a>',

                        ),
                    ),
                    'body'  => array(
                        'template'  => Lang::get('feed_store_created.body'),
                        'data' => array(),
                    ),
                );
                break;
            case 'goods_created':
                $feed = array(
                    'icon' => 'goods',
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                    'title' => array(
                        'template' => Lang::get('feed_goods_created.title'),
                        'data' => array(
                            'goods' => '<a href="' . $data['goods_url'] . '">' . $data['goods_name'] . '</a>'
                        ),
                    ),
                    'body' => array(
                        'template' => Lang::get('feed_goods_created.body'),
                        'data' => array(),
                    ),
                    'images' => $data['images']
                );
                break;
            case 'groupbuy_created':
                $feed = array(
                    'icon' => 'goods',
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                    'title' => array(
                        'template' => Lang::get('feed_groupbuy_created.title'),
                        'data' => array(
                            'groupbuy' => '<a href="' . $data['groupbuy_url'] . '">' . $data['groupbuy_name'] . '</a>'
                        ),
                    ),
                    'body' => array(
                        'template' => Lang::get('feed_groupbuy_created.body'),
                        'data' => array(
                            'groupbuy_message' => $data['message']
                        ),
                    ),
                    'images' => $data['images']
                );
                break;
            case 'goods_collected':
                $feed = array(
                    'icon' => 'goods',
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                    'title' => array(
                        'template' => Lang::get('feed_goods_collected.title'),
                        'data' => array(
                            'goods' => '<a href="' . $data['goods_url'] . '">' . $data['goods_name'] . '</a>'
                        ),
                    ),
                    'body' => array(
                        'template' => Lang::get('feed_goods_collected.body'),
                        'data' => array(),
                    ),
                    'images' => $data['images']
                );
                break;
            case 'store_collected':
                $feed = array(
                    'icon' => 'goods',
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                    'title' => array(
                        'template' => Lang::get('feed_store_collected.title'),
                        'data' => array(
                            'store' => '<a href="' . $data['store_url'] . '">' . $data['store_name'] . '</a>'
                        ),
                    ),
                    'body' => array(
                        'template' => Lang::get('feed_store_collected.body'),
                        'data' => array(),
                    ),
                    'images' => $data['images']
                );
                break;
            case 'goods_evaluated':
                $feed = array(
                    'icon' => 'goods',
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                    'title' => array(
                        'template' => Lang::get('feed_goods_evaluated.title'),
                        'data' => array(
                            'goods' => '<a href="' . $data['goods_url'] . '">' . $data['goods_name'] . '</a>',
                            'evaluation' => $data['evaluation'],
                        ),
                    ),
                    'body' => array(
                        'template' => Lang::get('feed_goods_evaluated.body'),
                        'data' => array(
                            'comment' => $data['comment'],
                        ),
                    ),
                    'images' => $data['images']
                );
                break;
            case 'groupbuy_joined':
                $feed = array(
                    'icon' => 'goods',
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                    'title' => array(
                        'template' => Lang::get('feed_groupbuy_joined.title'),
                        'data' => array(
                            'groupbuy' => '<a href="' . $data['groupbuy_url'] . '">' . $data['groupbuy_name'] . '</a>'
                        ),
                    ),
                    'body' => array(
                        'template' => Lang::get('feed_groupbuy_joined.body'),
                        'data' => array(),
                    ),
                    'images' => $data['images']
                );
                break;
        }

        return $feed;
    }

    /**
     *    ��ȡ�¼�
     *
     *    @author    Garbin
     *    @param     int $limit     ����
     *    @return    array
     */
    function get($limit) {}

    /**
     * �ж�feed�Ƿ�����
     *
     * @author Garbin
     * @return bool
     **/
    function feed_enabled()
    {
        $feed_enabled = null;
        if ($feed_enabled === null)
        {
            $cache_server =& cache_server();
            $cache_key = 'feed_enabled';
            $feed_enabled = $cache_server->get($cache_key);
            if ($feed_enabled === false)
            {
                $feed_enabled = 0;
                $app_list = outer_call('uc_app_ls');
                if ($app_list)
                {
                    foreach ($app_list as $app)
                    {
                        if ($app['type'] == 'UCHOME')
                        {
                            $feed_enabled = $app;
                        }
                    }
                }
                $cache_server->set($cache_key, $feed_enabled, 86400);
            }
        }

        return $feed_enabled;
    }
}

?>
