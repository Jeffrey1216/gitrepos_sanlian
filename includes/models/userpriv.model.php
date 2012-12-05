<?php

/**
 *    �����̳�Ȩ��
 *
 *    @author    Garbin
 *    @usage    none
 */
class UserprivModel extends BaseModel
{
    var $table = 'user_priv';
    var $prikey= 'user_id';
    var $_name = 'userpriv';
    var $_relation = array(
        'mall_be_manage' => array(
            'model'     => 'member',
            'type'      => BELONGS_TO,
            'reverse'   => 'manage_mall',
        )
    );
    /*
     * �ж��Ƿ��ǹ���Ա
     */
    function check_admin($user_id)
    {
        $conditions = "user_id in (" . $user_id . ")";
        $user_id && $conditions .= " AND store_id = '0'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }

    /*
     * �ж��Ƿ��ǳ�ʼ����Ա
     */
    function check_system_manager($user_id)
    {
        $conditions = "user_id in (" . $user_id . ")";
        $user_id && $conditions .= " AND store_id = '0'";
        $res = $this->find(array('conditions' => $conditions,
            'fields' => 'privs'));
        foreach ($res as $key => $val)
        {
            if ($val['privs'] == 'all')
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
        /*
     * ȡ�ù���ԱID
     */
     function get_admin_id()
    {
        $conditions = ' AND store_id = 0';
        //��������
        $sort  = 'user_id';
        $order = 'asc';
        $user_id = $this->find(array(
            'conditions' => '1=1' . $conditions,
            'limit' => $limit,
            'order' => "$sort $order",
            'count' => true,
        ));
        return $user_id;
     }
}
?>