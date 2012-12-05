<?php

/* ������־ orderlog */
class StoreorderlogModel extends BaseModel
{
    var $table  = 'store_order_log';
    var $prikey = 'log_id';
    var $_name  = 'storeorderlog';
    var $_relation  = array(
        // һ��������־ֻ������һ������
        'belongs_to_storeorder' => array(
            'model'         => 'storeorder',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'order_id',
            'reverse'       => 'has_storeorderlog',
        ),
    );
}

?>