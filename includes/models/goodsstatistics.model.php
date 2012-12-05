<?php

/* ��Ʒͳ�� goodsstatistics */
class GoodsstatisticsModel extends BaseModel
{
    var $table  = 'goods_statistics';
    var $prikey = 'goods_id';
    var $_name  = 'goodsstatistics';

    var $_relation  = array(
        // һ����Ʒͳ��ֻ������һ����Ʒ
        'belongs_to_goods' => array(
            'model'         => 'goods',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_goodsstatistics',
        ),
        // һ����Ʒֻ������һ������
        'belongs_to_store' => array(
            'model'         => 'goods',
            'type'          => HAS_AND_BELONGS_TO_MANY,
        	'middle_table'  => 'store_goods',
            'foreign_key'   => 'goods_id',
            'reverse'       => 'belongs_to_goods',
        ),
    );
}

?>