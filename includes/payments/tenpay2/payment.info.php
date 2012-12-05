<?php

return array(
    'code'      => 'tenpay2',
    'name'      => Lang::get('tenpay'),
    'desc'      => Lang::get('tenpay_desc'),
    'is_online' => '1',
    'author'    => 'PaiLa TEAM',
    'website'   => 'http://www.tenpay.com',
    'version'   => '1.0',
    'currency'  => Lang::get('tenpay_currency'),
    'config'    => array(
        'tenpay_account'   => array(        //�˺�
            'text'  => Lang::get('tenpay_account'),
            'desc'  => Lang::get('tenpay_account_desc'),
            'type'  => 'text',
        ),    
        'tenpay_key'       => array(        //��Կ
        'text'  => Lang::get('tenpay_key'),
            'type'  => 'text',
        ),
        'magic_string' => array(            //����ǩ��
            'text' => Lang::get('magic_string'),
            'type' => 'text',
        ),
    ),
);

?>