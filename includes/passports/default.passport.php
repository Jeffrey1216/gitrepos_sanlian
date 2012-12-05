<?php

/**
 *    �����û��������ӽӿ�
 *
 *    @author    Garbin
 *    @usage    none
 */
class DefaultPassport extends BasePassport
{
    var $_name = 'default';
}

/**
 *    �����û����ĵ��û�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class DefaultPassportUser extends BasePassportUser
{

    /* ע�� */
    function register($user_name, $password, $email, $local_data = array())
    {
        if (!$this->check_username($user_name))
        {
            return false;
        }

        if (!$this->check_email($email))
        {
            return false;
        }
    	if ($this->get($local_data['mobile'],false,true))
        {
        	$this->_error('mobile_exists');
            return false;
        }
        $local_data['user_name']    = $user_name;
        $local_data['password']     = md5($password);
        $local_data['email']        = $email;
        $local_data['reg_time']     = gmtime();

        /* ��ӵ��û�ϵͳ */
        $user_id = $this->_local_add($local_data);

        return $user_id;
    }
    /* �༭�û����� */
    function edit($user_id, $old_password, $items, $force = false)
    {
        if (!$force)
        {
            $info = $this->get($user_id);
            if (md5($old_password) != $info['password'])
            {
                $this->_error('auth_failed');

                return false;
            }
        }
        $edit_data = array();
        if (isset($items['password']))
        {
            $edit_data['password']  = md5($items['password']);
        }
        if (isset($items['email']))
        {
            $edit_data['email'] = $items['email'];
        }
    	if (isset($items['mobile']))
        {
            $edit_data['mobile'] = $items['mobile'];
        }
        if (empty($edit_data))
        {
            return false;
        }
        //�༭��������
        $this->_local_edit($user_id, $edit_data);

        return true;
    }

    /* ɾ���û� */
    function drop($user_id)
    {
        return $this->_local_drop($user_id);
    }

    /** 
     *    ��ȡ�û���Ϣ
     *    @edit       �޸ĵ�����֤---�����ֻ��ŵ��뷽ʽ
     *    @editdate   2011/07/22
     *    @edituser   lihuoliang
     */
    function get($flag, $is_name = false,$is_mobile = false, $is_id = false)
    {
        if ($is_name)
        {
            $conditions = "user_name='{$flag}'";
        }elseif ($is_mobile)
        {
        	$conditions = "mobile='{$flag}'";
        } else if ($is_id)
        {
        	$conditions = "user_id = '" . $flag . "'";
        }
        else
        {
            $conditions = intval($flag);
        }
        return $this->_local_get($conditions);
    }

    /**
     *    ��֤�û���¼
     *
     *    @author    Garbin
     *    @param     $string $user_name
     *    @param     $string $password
     *    @return    int    �û�ID
     *    @edit      �����ֻ�������뷽ʽ��֤
     * 	  @editdate  2011/07/22
     * 	  @edituser  lihuoliang
     */
    function auth($user_name, $password)
    {
        $info = $this->get($user_name, true);

        //��ʹ���û����Ҳ�����Ա��Ϣ��ʱ��----ʹ���ֻ�����֤
        if (!$info) 
        {
	        if (is_mobile($user_name)) 
	        {
	        	$info = $this->get($user_name, false,true);
	        }
        }
        if ($info['password'] != md5($password))
        {
            $this->_error('auth_failed');

            return 0;
        }

        return $info['user_id'];
    }
    
    //��֤֧������
    function traderAuth($user_id, $traderPassword)
    {
    	$info = $this->get($user_id, false, false, true);
    	if (!$info) 
        {
	        $this->_error('auth_failed');
	        return false;
        }
        if ($info['trader_password'] != $this->getMd5TraderPassword($traderPassword))
        {
            $this->_error('auth_failed');

            return false;
        }

        return true;
    }
    
    //����֧��������ܴ�
    public function getMd5TraderPassword($password)
    {
    	return md5('sanliantrader' . md5($password));
    }
    
    //�޸�֧������
    function updateTraderAuth($user_id, $traderPassword, $priTraderPassword)
    {
    	$traderPassword = $this->getMd5TraderPassword($traderPassword);
    	$priTraderPassword = $this->getMd5TraderPassword($priTraderPassword);
    	$info = $this->get($user_id, false, false, true);
    	if (!$info)
    	{
    		$this->_error('auth_failed');
    	}
    	
    	if ($traderPassword != $priTraderPassword)
    	{
    		$this->_error('�������벻һ��!');
    		return false;
    	}
    	
    	$this->_local_edit($user_id, array('trader_password' => $traderPassword));
    }
    
    //��֤�û��Ƿ���֧������
    function hasTraderPassword($user_id)
    {
    	$info = $this->get($user_id, false, false, true);
    	if (!$info)
    	{
    		$this->_error('auth_failed');

            return -2;
    	}
    	if (!$info['trader_password'])
    	{
    		return -1;
    	}
    	
    	return true;
    }

    /**
     *    ͬ����¼
     *
     *    @author    Garbin
     *    @param     int $user_id
     *    @return    string
     */
    function synlogin($user_id) {}

    /**
     *    ͬ���˳�
     *
     *    @author    Garbin
     *    @return    string
     */
    function synlogout() {}

    /**
     *    �������ʼ��Ƿ�Ψһ
     *
     *    @author    Garbin
     *    @param     string $email
     *    @return    bool
     */
    function check_email($email)
    {
        /* ��ʱ�޴����� */
        return true;

        $model_member =& m('member');
        $info = $model_member->get("email='{$email}'");
        if (!empty($info))
        {
            $this->_error('email_exists');

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
        $model_member =& m('member');
        $info = $model_member->get("user_name='{$user_name}'");
        if (!empty($info))
        {
            $this->_error('user_exists');

            return false;
        }

        return true;
    }
	function check_user_valid($user_name){
		$customer_ask_model=& m('customerask');		
		$ask_info=$customer_ask_model->get("user_name='{$user_name}'");
		$customer_manager_model=& m('customermanager');
		$manager_info=$customer_manager_model->get("user_name='{$user_name}'");
		if (!empty($ask_info)||!empty($manager_info))
		{
			$this->_error('user_exists');
		
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
        return false;
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
        $model_member =& m('member');
        $info = $model_member->get($user_id);

        if ($info['portrait'])
        {
            return _at('unlink', ROOT_PATH . '/' . $info['portrait']);
        }

        return true;
    }
}

/**
 *    �����û����ĵĶ��Ų���
 *
 *    @author    Garbin
 *    @usage    none
 */
class DefaultPassportPM extends BasePassportPM
{
    var $show_announce;
    function __construct()
    {
        $this->UcPassportPM();
    }
    function UcPassportPM()
    {
        $this->show_announce = false;
        Lang::load(ROOT_PATH . '/includes/passports/' . MEMBER_TYPE . '/' . LANG . '/common.lang.php');
        if (file_exists(ROOT_PATH . '/data/msg.lang.php'))
        {
            Lang::load(ROOT_PATH . '/data/msg.lang.php');
        }
    }
    /**
     *    ���Ͷ���Ϣ
     *
     *    @author    Garbin
     *    @param     int $sender        ������
     *    @param     array $recipient     ������
     *    @param     string $subject    ����
     *    @param     string $message    ����
     *    @param     int $replyto       �ظ�����
     *    @return    false:ʧ��   true:�ɹ�
     */
    function send($sender, $recipient, $subject, $message, $replyto = 0)
    {
        $model_message =& m('message');
        $msg_id = $model_message->send($sender, $recipient, '', $message, $replyto);
        if (!$msg_id)
        {
            $this->_errors = $model_message->get_error();

            return 0;
        }

        return $msg_id;
    }

    /**
     *    ��ȡ����Ϣ����
     *
     *    @author    Garbin
     *    @param     int  $user_id  ӵ����
     *    @param     int  $pm_id    ����Ϣ��ʶ
     *    @param     bool $full     �Ƿ�����ظ� false:������ true����
     *    @return    false:û����Ϣ array:��Ϣ����
     */
    function get($user_id, $pm_id, $full = false)
    {
        $model_message =& m('message');
        $topic = $model_message->get(array(
            'fields'     => 'this.*',
            'conditions' => 'msg_id=' . $pm_id . ' AND parent_id=0 AND ((status IN (1,3) AND to_id = ' . $user_id . ') OR (status IN (2,3) AND from_id = ' . $user_id . '))',
        ));
        if (empty($topic))
        {
            return array();
        }
        if ($topic['from_id'] == MSG_SYSTEM)
        {
            $topic['user_name'] = Lang::get('system_message');
            $topic['system'] = 1;
        }
        $topic['new'] = (($topic['from_id'] == $user_id && $topic['new'] == 2)||($topic['to_id'] == $user_id && $topic['new'] == 1 )) ? 1 : 0;
        $topic['portrait'] = portrait($topic['from_id'], $topic['portrait']);
        if ($full)
        {
            $replies = $model_message->find(array(
                'fields'     => 'this.*',
                'conditions' => 'parent_id=' . $pm_id,
            ));
        }

        return array(
            'topic' => $topic,
            'replies' => $replies
        );
    }

    /**
     *    ��ȡ��Ϣ�б�
     *
     *    @author    Garbin
     *    @param     int    $user_id
     *    @param     string $limit
     *    @param     string $folder ��ѡֵ:inbox, outbox
     *    @return    array:��Ϣ�б�
     */
    function get_list($user_id, $page, $folder = 'privatepm')
    {
        $limit = $page['limit'];
        $condition = '';
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
                $condition = '((new = 1 AND status IN (1,3) AND to_id = ' . $user_id . ') OR (new =2 AND status IN (2,3) AND from_id = ' . $user_id . '))';
            break;
        }
        $model_message =& m('message');
        $messages = $model_message->find(array(
            'fields'        =>'this.*',
            'conditions'    =>  $condition .' AND parent_id=0 ',
            'count'         => true,
            'limit'         => $limit,
            'order'         => 'last_update DESC',
        ));
        $subject = '';
        if (!empty($messages))
        {
            foreach ($messages as $key => $message)
            {
                $messages[$key]['new'] = (($message['from_id'] == $user_id && $message['new'] == 2)||($message['to_id'] == $user_id && $message['new'] == 1 )) ? 1 : 0; //�ж��Ƿ�������Ϣ
                $subject = $this->removecode($messages[$key]['content']);
                $messages[$key]['content'] = htmlspecialchars($subject);
                $message['from_id'] == MSG_SYSTEM && $messages[$key]['user_name'] = Lang::get('system_message'); //�ж��Ƿ���ϵͳ��Ϣ
            }
        }
        return array(
            'count' => $model_message->getCount(),
            'data' => $messages
        );
    }
    
    function removecode($str) {
        $rs = trim(preg_replace(array(
            "/\[(img)=?.*\].*?\[\/(img)\]/siU",
            "/\[\/?(url)=?.*\]/siU",
            "/\r\n/",
            ), '', $str));
        return $rs;
    }


    
    /**
     *    ����Ƿ��ж���Ϣ
     *
     *    @author    Garbin
     *    @param     int $user_id
     *    @return    false:���¶���Ϣ ture:���¶���Ϣ
     */
    function check_new($user_id)
    {
        $model_message =& m('message');
        
        $new = $model_message->check_new($user_id);
        return $new['total'];
    }

    /**
     *    ɾ������Ϣ
     *
     *    @author    Garbin
     *    @param     int        $user_id ����Ϣӵ����
     *    @param     array      $pm_ids  ��ɾ���Ķ���Ϣ
     *    @param     string     $foloder    ��ѡֵ:inbox,outbox
     *    @return    false:ʧ��   true:�ɹ�
     */
    function drop($user_id, $pm_ids)
    {
        $model_message =& m('message');
        if (!$model_message->msg_drop($pm_ids, $user_id))
        {
            $this->_errors = $model_message->get_error();

            return false;
        }

        return true;
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
        
        $model_message =& m('message');
        $model_message->edit($pm_ids, array(
            'new'   => $status,
        ));

        return (!$model_message->has_error());
    }
    
    /**
     *  ����Ϣ����
     *
     *  @return string 
     */
    function msg_filter($message)
    {
        $message = str_replace('&amp;', '&', $message); // ��ֹURL�е�&���ظ�ת��
        $message = htmlspecialchars($message);
        if(strpos($message, '[/url]') !== FALSE)
        {
            $message = preg_replace("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/|www\.)([^\[\"']+?))?\](.+?)\[\/url\]/ies", "\$this->parseurl('\\1', '\\5')", $message);
        }
        if(strpos($message, '[/img]') !== FALSE)
        {
            $message = preg_replace(array(
                "/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies",
                "/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies"
                ), array(
                "\$this->bbcodeurl('\\1', '<img src=\"%s\" border=\"0\" alt=\"\" />')",
                "\$this->bbcodeurl('\\3', '<img width=\"\\1\" height=\"\\2\" src=\"%s\" border=\"0\" alt=\"\" />')"),
                $message);
        }
        return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
    }
         
    function bbcodeurl($url, $tags) 
    {
        if(!preg_match("/<.+?>/s", $url)) 
        {
            if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://'))) 
            {
                $url = 'http://'.$url;
            }
            return str_replace(array('submit', 'logging.php'), array('', ''), sprintf($tags, $url, addslashes($url)));
        } 
        else 
        {
            return '&nbsp;'.$url;
        }
    }
    
    function parseurl($url, $text) 
    {
        if(!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches))
        {
            $url = $matches[0];
            $length = 65;
            if(strlen($url) > $length)
            {
                $text = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
            }
            return '<a href="'.(substr(strtolower($url), 0, 4) == 'www.' ? 'http://'.$url : $url).'" target="_blank">'.$text.'</a>';
        }
        else
        {
            $url = substr($url, 1);
            if(substr(strtolower($url), 0, 4) == 'www.')
            {
                $url = 'http://'.$url;
            }
            return '<a href="'.$url.'" target="_blank">'.$text.'</a>';
        }
    }
}

/**
 *    �����û����ĵĺ��Ѳ���
 *
 *    @author    Garbin
 *    @usage    none
 */
class DefaultPassportFriend extends BasePassportFriend
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
 *    �����û����ĵ��¼�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class DefaultPassportFeed extends BasePassportFeed
{
    /**
     *    ����¼�
     *
     *    @author    Garbin
     *    @param     array $feed    �¼�
     *    @return    false:ʧ��   true:�ɹ�
     */
    function add($feed) {}

    /**
     *    ��ȡ�¼�
     *
     *    @author    Garbin
     *    @param     int $limit     ����
     *    @return    array
     */
    function get($limit) {}
}

?>
