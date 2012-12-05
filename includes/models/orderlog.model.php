<?php

/* ������־ orderlog */
class OrderlogModel extends BaseModel
{
    var $table  = 'order_log';
    var $prikey = 'log_id';
    var $_name  = 'orderlog';
    var $_relation  = array(
        // һ��������־ֻ������һ������
        'belongs_to_order' => array(
            'model'         => 'order',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'order_id',
            'reverse'       => 'has_orderlog',
        ),
    );
}

?>