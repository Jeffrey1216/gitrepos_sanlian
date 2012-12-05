<?php

/* ������Ʒ ordergoods */
class QuickordergoodsModel extends BaseModel
{
    var $table  = 'quick_order_goods';
    var $prikey = 'cart_id';
    var $_name  = 'quickordergoods';
    var $_relation = array(
        // һ��������Ʒֻ������һ������
        'belongs_to_quickorder' => array(
            'model'         => 'quickorder',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'order_id',
            'reverse'       => 'has_quickordergoods',
        ),
    );
}

?>