<?php

/* ���ͷ�ʽ shipping */
class ShippingModel extends BaseModel
{
    var $table  = 'shipping';
    var $prikey = 'shipping_id';
    var $_name  = 'shipping';
    var $_autov = array(
        'shipping_name' =>  array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'first_price'   =>  array(
            'required'  => true,
            'filter'    => 'floatval',
        ),
        'step_price'    =>  array(
            'filter'    => 'floatval'
        ),
        'cod_regions'   =>  array(
            'filter'    => 'trim',
        ),
        'enabled'       =>  array(
            'filter'    => 'intval',
        ),
        'sort_order'    =>  array(
            'filter'    => 'intval'
        ),
    );

    var $_relation  =   array(
        // һ�����ͷ�ʽֻ������һ������
        'belongs_to_store' => array(
            'model'         => 'store',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'store_id',
            'reverse'       => 'has_shipping',
        ),
    );
}

?>