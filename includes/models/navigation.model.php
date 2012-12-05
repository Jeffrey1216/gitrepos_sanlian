<?php

/* ���� navigation */
class NavigationModel extends BaseModel
{
    var $table  = 'navigation';
    var $prikey = 'nav_id';
    var $_name  = 'navigation';

     /* ��ӱ༭ʱ�Զ���֤ */
    var $_autov = array(
        'title' => array(
            'required'  => true,    //����
            'min'       => 1,       //���1���ַ�
            'max'       => 100,     //�100���ַ�
            'filter'    => 'trim',
        ),
        'link'  => array(
            'required'  => true,    //����
            'min'       => 1,       //���1���ַ�
            'max'       => 255,     //�255���ַ�
            'filter'    => 'trim',
        ),
        'sort_order'    => array(
            'filter'    => 'trim,intval',//����
            'max'       => 3,     //�3���ַ�
        ),
        'open_new'      => array(
            'filter'    => 'intval',
        ),
        'type'      => array(
            'required'    => true,
        )
    );

    var $_relation  = array(
    );
}

?>