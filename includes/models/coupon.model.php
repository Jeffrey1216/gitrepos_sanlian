<?php

class CouponModel extends BaseModel
{
    var $table  = 'coupon';
    var $prikey = 'coupon_id';
    var $_name  = 'coupon';
    var $_relation  = array(
        // һ���Ż�ȯ�ж���Ż�ȯ���
        'has_couponsn' => array(
            'model'         => 'couponsn',
            'type'          => HAS_MANY,
            'foreign_key'   => 'coupon_id',
            'dependent'     => true
        ),
        // һ���Ż�ȯֻ������һ������
        'belong_to_store' => array(
            'model'         => 'store',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'store_id',
            'reverse'       => 'has_coupon',    
        ),
    );
}

?>
