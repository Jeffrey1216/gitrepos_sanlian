<?php

/* ��Ա member */
class MemberModel extends BaseModel
{
    var $table  = 'member';
    var $prikey = 'user_id';
    var $_name  = 'member';

    /* ������ģ��֮��Ĺ�ϵ */
    var $_relation = array(
        // һ����Աӵ��һ�����̣�id��ͬ
        'has_store' => array(
            'model'       => 'store',       //ģ�͵�����
            'type'        => HAS_ONE,       //��ϵ����
            'foreign_key' => 'store_id',    //�����
            'dependent'   => true           //����
        ),
        'manage_mall'   =>  array(
            'model'       => 'userpriv',
            'type'        => HAS_ONE,
            'foreign_key' => 'user_id',
            'ext_limit'   => array('store_id' => 0),
            'dependent'   => true
        ),
        // һ����Աӵ�ж���ջ���ַ
        'has_address' => array(
            'model'       => 'address',
            'type'        => HAS_MANY,
            'foreign_key' => 'user_id',
            'dependent'   => true
        ),
	// һ����Աӵ�ж�������˺�
        'has_card' => array(
            'model'       => 'member_card',
            'type'        => HAS_MANY,
            'foreign_key' => 'UID',
            'dependent'   => true
        ),
        // һ���û��ж������
        'has_order' => array(
            'model'         => 'order',
            'type'          => HAS_MANY,
            'foreign_key'   => 'buyer_id',
            'dependent' => true
        ),
        // һ���û��ж�����̶���
        'has_storeorder' => array(
            'model'         => 'storeorder',
            'type'          => HAS_MANY,
            'foreign_key'   => 'buyer_id',
            'dependent' => true
        ),
         // һ���û��ж����յ��Ķ���
        'has_received_message' => array(
            'model'         => 'message',
            'type'          => HAS_MANY,
            'foreign_key'   => 'to_id',
            'dependent' => true
        ),
        // һ���û��ж������ͳ�ȥ�Ķ���
        'has_sent_message' => array(
            'model'         => 'message',
            'type'          => HAS_MANY,
            'foreign_key'   => 'from_id',
            'dependent' => true
        ),
        // ��Ա����Ʒ�Ƕ�Զ�Ĺ�ϵ����Ա�ղ���Ʒ��
        'collect_goods' => array(
            'model'        => 'goods',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'collect',    //�м������
            'foreign_key'  => 'user_id',
            'ext_limit'    => array('type' => 'goods'),
            'reverse'      => 'be_collect', //�����ϵ����
        ),
        // ��Ա�͵����Ƕ�Զ�Ĺ�ϵ����Ա�ղص��̣�
        'collect_store' => array(
            'model'        => 'store',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'collect',
            'foreign_key'  => 'user_id',
            'ext_limit'    => array('type' => 'store'),
            'reverse'      => 'be_collect',
        ),
        // ��Ա�͵����Ƕ�Զ�Ĺ�ϵ����Աӵ�е���Ȩ�ޣ�
        'manage_store' => array(
            'model'        => 'store',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'user_priv',
            'foreign_key'  => 'user_id',
            'reverse'      => 'be_manage',
        ),
        // ��Ա�ͺ����Ƕ�Զ�Ĺ�ϵ����Աӵ�ж�����ѣ�
        'has_friend' => array(
            'model'        => 'member',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'friend',
            'foreign_key'  => 'owner_id',
            'reverse'      => 'be_friend',
        ),
        // �����Ƕ�Զ�Ĺ�ϵ����Աӵ�ж�����ѣ�
        'be_friend' => array(
            'model'        => 'member',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'friend',
            'foreign_key'  => 'friend_id',
            'reverse'      => 'has_friend',
        ),
        //�û�����Ʒ��ѯ��һ�Զ�Ĺ�ϵ��һ����Աӵ�ж����Ʒ��ѯ
        'user_question' => array(
            'model' => 'goodsqa',
            'type' => HAS_MANY,
            'foreign_key' => 'user_id',
        ),
        //��Ա���Ż�ȯ����Ƕ�Զ�Ĺ�ϵ
        'bind_couponsn' => array(
            'model'        => 'couponsn',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'user_coupon',
            'foreign_key'  => 'user_id',
            'reverse'      => 'bind_user',
        ),
        // ��Ա���Ź���Ƕ�Զ�Ĺ�ϵ����Ա�ղ���Ʒ��
        'join_groupbuy' => array(
            'model'        => 'groupbuy',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'groupbuy_log',    //�м������
            'foreign_key'  => 'user_id',
            'reverse'      => 'be_join', //�����ϵ����
        ),
        // һ����Ա����һ���Ź�
        'start_groupbuy' => array(
            'model'         => 'groupbuy',
            'type'          => HAS_ONE,
            'foreign_key'   => 'store_id',
            'dependent'   => true
        ),
        // һ����Ա�ж������ּ�¼
        'has_creditnotes' => array(
        	'model'			=> 'creditnotes',
        	'type'			=> HAS_MANY,
        	'foreign_key'	=> 'uid',
        	'dependent'		=> true
        )
    );

    var $_autov = array(
        'user_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'password' => array(
            'required' => true,
            'filter'   => 'trim',
            'min'      => 6,
        ),
    );

    /*
     * �ж������Ƿ�Ψһ
     */
    function unique($user_name, $user_id = 0)
    {
        $conditions = "user_name = '" . $user_name . "'";
        $user_id && $conditions .= " AND user_id <> '" . $user_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }

    function drop($conditions, $fields = 'portrait')
    {
        if ($droped_rows = parent::drop($conditions, $fields))
        {
            restore_error_handler();
            $droped_data = $this->getDroppedData();
            foreach ($droped_data as $row)
            {
                $row['portrait'] && @unlink(ROOT_PATH . '/' . $row['portrait']);
            }
            reset_error_handler();
        }
        return $droped_rows;
    }
}

?>