<?php

/* 订单商品 ordergoods */
class StoreordergoodsModel extends BaseModel
{
    var $table  = 'store_order_goods';
    var $prikey = 'rec_id';
    var $_name  = 'storeordergoods';
    var $_relation = array(
        // 一个订单商品只能属于一个订单
        'belongs_to_storeorder' => array(
            'model'         => 'storeorder',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'order_id',
            'reverse'       => 'has_storeordergoods',
        ),
    );
}

?>