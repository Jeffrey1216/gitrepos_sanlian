<?php

class CouponsnModel extends BaseModel
{
    var $table  = 'coupon_sn';
    var $prikey = 'coupon_sn';
    var $_name  = 'couponsn';
    var $_relation  = array(
        // һ���Ż�ȯ���ֻ������һ���Ż�ȯ
        'belongs_to_coupon' => array(
            'model'         => 'coupon',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'coupon_id',
            'reverse'       => 'has_couponsn',
        ),
        // �û����Ż�ȯ�Ƕ�Զ�Ĺ�ϵ   
        'bind_user' => array(
            'model'         => 'member',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'user_coupon',
            'foreign_key'   => 'coupon_sn',
            'reverse'       => 'bind_couponsn',
        ),
    );
}

?>
