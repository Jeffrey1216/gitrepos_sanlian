<?php

/* 商品统计 goodsstatistics */
class GoodsstatisticsModel extends BaseModel
{
    var $table  = 'goods_statistics';
    var $prikey = 'goods_id';
    var $_name  = 'goodsstatistics';

    var $_relation  = array(
        // 一个商品统计只能属于一个商品
        'belongs_to_goods' => array(
            'model'         => 'goods',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_goodsstatistics',
        ),
        // 一个商品只能属于一个店铺
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