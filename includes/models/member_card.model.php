<?php

/* */
class Member_cardModel extends BaseModel
{
    var $table  = 'member_card';
    var $prikey = 'id';
    var $_name  = 'member_card';

     /* 关系列表 */
    var $_relation  = array(
        // 一个收货地址只能属于一个会员
        'belongs_to_member' => array(
            'model'             => 'member',
            'type'              => BELONGS_TO,
            'foreign_key'       => 'user_id',
            'reverse'           => 'has_card',
        )
    );
   /**
     *    获取用户的银行卡信息
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