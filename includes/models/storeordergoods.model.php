<?php

/* ������Ʒ ordergoods */
class StoreordergoodsModel extends BaseModel
{
    var $table  = 'store_order_goods';
    var $prikey = 'rec_id';
    var $_name  = 'storeordergoods';
    var $_relation = array(
        // һ��������Ʒֻ������һ������
        'belongs_to_storeorder' => array(
            'model'         => 'storeorder',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'order_id',
            'reverse'       => 'has_storeordergoods',
        ),
    );
}

?>