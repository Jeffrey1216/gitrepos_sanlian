<?php

/* 通用购物车 common_cart */
class CommoncartModel extends BaseModel
{
    var $table  = 'common_cart';
    var $prikey = 'cart_id';
    var $_name  = 'common_cart';

    var $_relation = array(
    	'belongs_to_supply'  => array(
            'type'      =>  BELONGS_TO,
            'model'     =>  'supply',
            'reverse'   =>  'has_commoncart',
        ),
        'belongs_to_goodsspec'  => array(
            'type'      =>  BELONGS_TO,
            'model'     =>  'goodsspec',
            'foreign_key' => 'spec_id',
            'reverse'   =>  'has_common_cart',
        ),
    );

    /**
     *    获取商品种类数
     *
     *    @author    Garbin
     *    @return    void
     */
    function get_kinds($sess_id, $user_id = 0)
    {
        $where_user_id = $user_id ? " AND user_id={$user_id}" : '';
        $kinds = $this->db->getOne("SELECT COUNT(DISTINCT goods_id) as c FROM {$this->table} WHERE session_id='{$sess_id}'{$where_user_id}");

        return $kinds;
    }
}

?>