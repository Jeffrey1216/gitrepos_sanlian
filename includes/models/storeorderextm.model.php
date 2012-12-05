<?php

/**
 *    订单扩展信息表
 *
 *    @author    Garbin
 *    @usage    none
 */
class StoreorderextmModel extends BaseModel
{
    var $table  =   'store_order_extm';
    var $prikey =   'order_id';
    var $_name  =   'storeorderextm';
    var $_relation = array(
        'belongs_to_storeorder'  => array(
            'type'          => BELONGS_TO,
            'reverse'       => 'has_storeorderextm',
            'model'         => 'storeorder',
            'foreign_key'   => 'order_id',
        ),
    );
}

?>