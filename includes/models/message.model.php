<?php

define('CHECK_PM_INTEVAL', 600); // �������Ϣ��ʱ��������λ���룩

/* ����Ϣ message */
class MessageModel extends BaseModel
{
    var $table  = 'message';
    var $prikey = 'msg_id';
    var $_name  = 'message';

    /* ������ģ��֮��Ĺ�ϵ */
    var $_relation = array(
        // һ���յ��Ķ�������һ���û�
        'received_belongs_to_member' => array(
            'model'             => 'member',
            'type'              => BELONGS_TO,
            'foreign_key'       => 'to_id',
            'reverse'           => 'has_received_message',
        ),
        // һ������ȥ�Ķ�������һ���û�
        'sent_belongs_to_member' => array(
            'model'             => 'member',
            'type'              => BELONGS_TO,
            'foreign_key'       => 'from_id',
            'reverse'           => 'has_sent_message',
        ),
        // һ�������ж����ظ�����
        'has_reply' => array(
            'model'         => 'message',
            'type'          => HAS_MANY,
            'foreign_key'   => 'parent_id',
            'dependent' => true
        ),
    );

     /* ��ӱ༭ʱ�Զ���֤ */
    var $_autov = array(
    /*    'from_id' => array(
            'required'  => true,
            'type'      => 'int',
            'filter'    => 'trim',
        ),*/
        'to_id' => array(
            'required'  => true,
            'type'      => 'int',
            'filter'    => 'trim',
        ),
        'content' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
    );

    /**
     * ���Ͷ���Ϣ
     *
     * @author Hyber
     * @param int $from_id
     * @param mixed $to_id  ���͸���Щuser_id  �����Ƕ��ŷָ� ����������
     * @param string $title ���ű���
     * @param string $content ��������
     * @param int $parent_id ����ǻظ�����Ҫ����msg_id
     * @return mixed
     */
    function send($from_id, $to_id, $title='', $content, $parent_id=0)
    {
        $to_ids = is_array($to_id) ? $to_id : explode(',', $to_id);
        foreach ($to_ids as $k => $to_id)
        {
            if ($from_id == $to_id)
            {
                $this->_error('cannot_sent_to_myself');
                return false; //���ܷ����Լ�
            }
            $data[$k] = array(
                'from_id'   => $from_id,
                'to_id'     => $to_id,
                'title'     => $title,
                'content'   => $content,
                'parent_id' => $parent_id,
                'add_time'  => gmtime(),
            );
            if ($parent_id>0) //�ظ�
            {
                if ($k==0)//ִֻ��һ��
                {
                    $message = $this->get_info($parent_id);
                    $edit_data =array(
                        'last_update'   => gmtime(), //�޸��������ʱ��
                        'status'        => 3, //����˫��δɾ��
                    );
                    $edit_data['new'] = $from_id == $message['from_id'] ? 1 : 2; //����ظ��Լ����͵�����ʱ
                    //unset($this->_autov['title']['required']); //�������Ϊ��
                }
            }else //������
            {
                $data[$k]['new']         = 1; //�ռ�������Ϣ
                $data[$k]['status']      = 3; //˫��δɾ��
                $data[$k]['last_update'] = gmtime(); //����ʱ��
            }
        }//dump($data);
        $msg_ids = $this->add($data);
        $edit_data && $msg_ids && $this->edit($parent_id, $edit_data);
        return $msg_ids;
    }

    /**
     * ɾ������Ϣ
     *
     * @author Hyber
     * @param mix $msg_id �����Ƕ��ŷָ� ����������
     * @param integer $user_id ��ǰ�û�
     * @return integer
     */
    function msg_drop($msg_id, $user_id)
    {
        $msg_ids = is_array($msg_id) ? $msg_id : explode(',', $msg_id);
        if (!$msg_ids)
        {
            $this->_error('no_such_message');
            return false;
        }
        if (!$user_id)
        {
            $this->_error('no_such_user');
            return false;
        }
        foreach ($msg_ids as $msg_id)
        {
            $message = $this->get_info($msg_id);
            if ($message['from_id'] == MSG_SYSTEM && $message['to_id'] == $user_id)
            {
                $drop_ids[] = $msg_id; // ɾ��ϵͳ�����Լ�����Ϣ
            }
            elseif ($user_id==$message['to_id']) //�ռ���
            {
                if ($message['status']==2 || $message['status']==3)
                {
                    $this->edit($msg_id, array('status' => 2));
                }else
                {
                    $drop_ids[] = $msg_id; //��¼��Ҫɾ����¼��msg_id
                }
            }
            elseif ($user_id==$message['from_id']) //������
            {
                if ($message['status']==1 || $message['status']==3)
                {
                    $this->edit($msg_id, array('status' => 1));
                }else
                {
                    $drop_ids[] = $msg_id; //��¼��Ҫɾ����¼��msg_id
                }
            }
            else
            {
                $this->_error('no_drop_permission');
                return false;//û��ɾ��Ȩ��
            }
        }
        if ($drop_ids)
        {
            return $this->drop($drop_ids);
        }
        else
        {
            return count($msg_ids);
        }
    }

    function check_new($user_id)
    {
        if (!$user_id)
        {
            $this->_error('no_such_user');
            return false;
        }

        $cache_server =& cache_server();
        $key = 'new_pm_of_user_' . $user_id;
        $new = $cache_server->get($key);
        if ($new === false)
        {
            $new = array();

            /* ͳ���ռ�������Ϣ */
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE to_id = '$user_id' AND parent_id = 0 AND status IN(1,3) AND new = 1";
            $new['inbox'] = $this->getOne($sql);
    
            /* ͳ�Ʒ���������Ϣ */
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE from_id = '$user_id' AND parent_id = 0 AND status IN(2,3) AND new = 2";
            $new['outbox'] = $this->getOne($sql);
    
            /* ͳ��ȫ������Ϣ */
            $new['total'] = $new['inbox'] + $new['outbox'];

            $cache_server->set($key, $new, CHECK_PM_INTEVAL);
        }
        
        return $new;
    }
}

?>