<?php

/**
 *    �û��������ӽӿڻ�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class BasePassport extends Object
{
    var $_name = '';
    var $user = null;
    var $pm = null;
    var $friend = null;
    var $feed = null;
    function __construct()
    {
        $this->BasePassport();
    }
    function BasePassport()
    {
        $user_class_name = ucfirst($this->_name) . 'PassportUser';
        $pm_class_name = ucfirst($this->_name) . 'PassportPM';
        $friend_class_name = ucfirst($this->_name) . 'PassportFriend';
        $feed_class_name = ucfirst($this->_name) . 'PassportFeed';
        $this->user     = new $user_class_name();
        $this->pm       = new $pm_class_name();
        $this->friend   = new $friend_class_name();
        $this->feed     = new $feed_class_name();
    }
    function tag_get($tag)
    {
        return array();
    }
}
/**
 *    �û��ӿڻ�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class BasePassportUser extends Object
{
    /**
     *    ע���û�
     *
     *    @author    Garbin
     *    @param     string $user_name  ��ע����û���
     *    @param     string $password   ����
     *    @param     string $email      �����ʼ�
     *    @return    int        �û�ID
     */
    function register($user_name, $password, $email)
    {
        return true;
    }

    /**
     *    �޸��û���Ϣ
     *
     *    @author    Garbin
     *    @param     int    $user_id    �û�ID
     *    @param     string $old_password  ԭʼ����
     *    @param     array  $items      Ҫ�޸ĵ���Ŀ
     *    @param     bool   $force      ǿ���޸�
     *    @return    bool
     */
    function edit($user_id, $old_password, $items, $force = false)
    {
        return true;
    }

    /**
     *    ɾ���û�
     *
     *    @author    Garbin
     *    @param     int $user_id       �û�ID
     *    @return    bool
     */
    function drop($user_id)
    {
        return true;
    }

    /**
     *    ��ȡ�û���Ϣ���û���ʱ����������û����ı���һ��
     *
     *    @author    Garbin
     *    @param     int $flag  �û�ID string $flag �û���
     *    @param     bool $is_name  �Ƿ����û���
     *    @return    array
     */
    function get($flag, $is_name = false)
    {
        # ���뷵�ر�׼������
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
        #TODO
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
        return '';
    }

    /**
     *    ͬ���˳�
     *
     *    @author    Garbin
     *    @return    string
     */
    function synlogout()
    {
        return '';
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
        #TODO
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
        #TODO
    }

    /**
     *    ����ͷ��
     *
     *    @author    Garbin
     *    @param     int $user_id
     *    @return    string
     */
    function set_avatar($user_id)
    {
        #TODO
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
        #TODO
    }

    function _local_add($data)
    {
        $model_member =& m('member');
        $user_id = $model_member->add($data);
        if (!$user_id)
        {
            $this->_errors = $model_member->get_error();
            return 0;
        }
        return $user_id;
    }

    function _local_edit($user_id, $data)
    {
        $model_member =& m('member');
        $model_member->edit($user_id, $data);

        return true;
    }
    function _local_drop($user_id)
    {
        $model_member =& m('member');
        $drop_nums = $model_member->drop($user_id);
        if ($model_member->has_error())
        {
            $this->_errors = $model_member->get_error();

            return 0;
        }

        return $drop_nums;
    }
    function _local_get($conditions)
    {
        $model_member =& m('member');

        return $model_member->get($conditions);
    }
    function _local_sync($user_id, $user_name, $email)
    {
        /* ���ر���ͬ�� */
        $local_info = $this->_local_get($user_id);
        if (empty($local_info))
        {
            /* �п������û������ж�����û�У���ʱҪ������� */
            $data = array(
                'user_id'   => $user_id,
                'user_name' => $user_name,
                'password'  => md5(time() . rand(100000, 999999)),
                'email'     => $email,
                'reg_time'  => gmtime()
            );
            $this->_local_add($data);
        }
    }
}

/**
 *    ����Ϣ�ӿڻ�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class BasePassportPM extends Object
{
    /**
     *    ���Ͷ���Ϣ
     *
     *    @author    Garbin
     *    @param     int $sender
     *    @param     int $recipient
     *    @param     string $subject
     *    @param     string $message
     *    @param     int $replyto
     *    @return    bool
     */
    function send($sender, $recipient, $subject, $message, $replyto = 0)
    {

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

    }

    /**
     *    ��ȡ��Ϣ�б�
     *
     *    @author    Garbin
     *    @param     int    $user_id
     *    @param     string $limit
     *    @return    array:��Ϣ�б�
     */
    function get_list($user_id, $limit = '0, 10', $folder = 'inbox')
    {
        #TODO
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
        #TODO
    }

    /**
     *    ɾ������Ϣ
     *
     *    @author    Garbin
     *    @param     int        $user_id ����Ϣӵ����
     *    @param     array      $pm_ids  ��ɾ���Ķ���Ϣ
     *    @return    false:ʧ��   true:�ɹ�
     */
    function drop($user_id, $pm_ids)
    {
        #TODO
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
        #TODO
    }
}

/**
 *    ���ѽӿڻ�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class BasePassportFriend extends Object
{
    /**
     *    ����һ������
     *
     *    @author    Garbin
     *    @param     int $user_id       ����ӵ����
     *    @param     int $friend_id     ����
     *    @return    false:ʧ�� true:�ɹ�
     */
    function add($user_id, $friend_id)
    {
        #TODO
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
        #TODO
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
        #TODO
    }

    /**
     *    ��ȡ�����б�
     *
     *    @author    Garbin
     *    @param     int $user_id       ����ӵ����
     *    @return    array  �����б�
     */
    function get_list($user_id, $limit = '0, 10')
    {
        #TODO
    }
}

/**
 *    �¼��ӿڻ�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class BasePassportFeed extends Object
{
    /**
     *    ����¼�
     *
     *    @author    Garbin
     *    @param     array $feed    �¼�
     *    @return    false:ʧ��   true:�ɹ�
     */
    function add($feed)
    {
        #TODO
    }

    /**
     *    ��ȡ�¼�
     *
     *    @author    Garbin
     *    @param     int $limit     ����
     *    @return    array
     */
    function get($limit)
    {
        #TODO
    }

    /**
     * Feed�Ƿ�����
     *
     * @author Garbin
     * @return bool
     **/
    function feed_enabled()
    {
        return false;
    }
}

function limit_page_info($limit)
{
    list($start, $size) = explode(',', $limit);
    $start = intval($start);
    $size  = intval($size);
    $page = $start / $size + 1;

    return array($page, $size);
}
?>
