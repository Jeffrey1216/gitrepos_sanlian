<?php
class CreditCartModel extends BaseModel
{
	 var $table  = 'credit_cart';
     var $prikey = 'id';
     var $_name  = 'creditcart';
	/**
     *    获取商品种类数
     *
     *    @author    Garbin
     *    @return    void
     */
    function get_credit_kinds($sess_id, $user_id = 0)
    {
        $where_user_id = $user_id ? " AND user_id={$user_id}" : '';
        $credit_kinds = $this->db->getOne("SELECT COUNT(DISTINCT id) as c FROM {$this->table} WHERE session_id='{$sess_id}'{$where_user_id}");

        return $credit_kinds;
    }
}
?>