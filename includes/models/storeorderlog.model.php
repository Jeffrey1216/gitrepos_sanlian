<?php

/* 订单日志 orderlog */
class StoreorderlogModel extends BaseModel
{
    var $table  = 'store_order_log';
    var $prikey = 'log_id';
    var $_name  = 'storeorderlog';
    var $_relation  = array(
        // 一个订单日志只能属于一个订单
        'belongs_to_storeorder' => array(
            'model'         => 'storeorder',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'order_id',
            'reverse'       => 'has_storeorderlog',
        ),
    );
}

?>