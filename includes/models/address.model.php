<?php

/* �ջ���ַ address */
class AddressModel extends BaseModel
{
    var $table  = 'address';
    var $prikey = 'addr_id';
    var $_name  = 'address';

    /* ���Զ���֤ */
    var $_autov = array(
        'user_id'   => array(
            'required'  => true,
        ),
        'consignee' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'address'   => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'region_id' => array(
            'required'  => true,
            'filter'    => 'intval',
        ),
        'region_name'   => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'phone_tel' => array(
            'reg'   => '/^[0-9\+(\s]{3,}[0-9\-)\s]{2,}[0-9]$/',      //�绰��������6λ
        ),
        'phone_mob' => array(
            'reg'   => '/\d{6}/',      //����6λ������
        ),
    );

    /* ��ϵ�б� */
    var $_relation  = array(
        // һ���ջ���ַֻ������һ����Ա
        'belongs_to_member' => array(
            'model'             => 'member',
            'type'              => BELONGS_TO,
            'foreign_key'       => 'user_id',
            'reverse'           => 'has_address',
        ),
    );
}

?>