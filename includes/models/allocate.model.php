<?php
class AllocateModel extends BaseModel
{
    var $table  = 'allocate';
    var $prikey = 'allid';
    var $_name  = 'allocate';
    var $_relation  = array(
    	// һ����Ʒ���ֻ������һ����Ʒ
        'belongs_to_order' => array(
            'model'         => 'order',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'order_id',
            'reverse'       => 'has_allocate',
        ),
        'belongs_to_orderextm' => array(
            'model'         => 'orderextm',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'order_id',
            'reverse'       => 'has_allocate',
        ),
        //һ���� ���ɶ������
       /* 'has_orderextm'    =>array(
        'model'          =>'order_extm',
        'type'          =>HAS_MANY,
        'foreign_key'   =>'order_id',
        'dependent'   =>true
        )*/
      );
}
?>