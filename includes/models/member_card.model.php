<?php

/* */
class Member_cardModel extends BaseModel
{
    var $table  = 'member_card';
    var $prikey = 'id';
    var $_name  = 'member_card';

     /* ��ϵ�б� */
    var $_relation  = array(
        // һ���ջ���ַֻ������һ����Ա
        'belongs_to_member' => array(
            'model'             => 'member',
            'type'              => BELONGS_TO,
            'foreign_key'       => 'user_id',
            'reverse'           => 'has_card',
        )
    );
   /**
     *    ��ȡ�û������п���Ϣ
     *
     *    @author    xiaoyu
     *    @param     int $user_id
     *    @return    Array
     */
    function get_all($user_id=0)
    {
		$sql = "select * from {$this->table} where UID = {$user_id}";
		return $this->db->getAll($sql);    	
    }

}

   

?>