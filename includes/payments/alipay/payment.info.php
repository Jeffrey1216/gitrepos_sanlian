<?php

return array(
    'code'      => 'alipay',
    'name'      => Lang::get('alipay'),
    'desc'      => Lang::get('alipay_desc'),
    'is_online' => '1',
    'author'    => 'PaiLa TEAM',
    'website'   => 'http://www.alipay.com',
    'version'   => '1.0',
    'currency'  => Lang::get('alipay_currency'),
    'config'    => array(
        'alipay_account'   => array(        //�˺�
            'text'  => Lang::get('alipay_account'),
            'desc'  => Lang::get('alipay_account_desc'),
            'type'  => 'text',
        ),
        'alipay_key'       => array(        //��Կ
            'text'  => Lang::get('alipay_key'),
            'desc'  => Lang::get('alipay_key_desc'),
            'type'  => 'text',
        ),
        'alipay_partner'   => array(        //���������ID
            'text'  => Lang::get('alipay_partner'),
            'type'  => 'text',
        ),
        'alipay_service'  => array(         //��������
            'text'      => Lang::get('alipay_service'),
            'desc'  => Lang::get('alipay_service_desc'),
            'type'      => 'select',
            'items'     => array(
                'trade_create_by_buyer'   => Lang::get('trade_create_by_buyer'),
                'create_partner_trade_by_buyer'   => Lang::get('create_partner_trade_by_buyer'),
                'create_direct_pay_by_user'   => Lang::get('create_direct_pay_by_user'),
            ),
        ),
    ),
);

?>