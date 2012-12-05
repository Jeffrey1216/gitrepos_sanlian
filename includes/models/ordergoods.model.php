<?php

/* ������Ʒ ordergoods */
class OrdergoodsModel extends BaseModel
{
    var $table  = 'order_goods';
    var $prikey = 'rec_id';
    var $_name  = 'ordergoods';
    var $_relation = array(
        // һ��������Ʒֻ������һ������
        'belongs_to_order' => array(
            'model'         => 'order',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'order_id',
            'reverse'       => 'has_ordergoods',
        ),
    );
}

?>