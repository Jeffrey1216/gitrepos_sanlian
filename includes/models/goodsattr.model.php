<?php

/* ��Ʒ���� goodsattr */
class GoodsattrModel extends BaseModel
{
    var $table  = 'goods_attr';
    var $prikey = 'gattr_id';
    var $_name  = 'goodsattr';

    var $_relation  = array(
        // һ����Ʒ����ֻ������һ����Ʒ
        'belongs_to_goods' => array(
            'model'         => 'goods',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_goodsattr',
        ),
    );
}

?>