<?php

class GoodsimageModel extends BaseModel
{
    var $table  = 'goods_image';
    var $prikey = 'image_id';
    var $_name  = 'goodsimage';
    var $_relation = array(
        // һ����ƷͼƬֻ������һ����Ʒ
        'belongs_to_goods' => array(
            'model'         => 'goods',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_goodsimage',
        ),
        // һ����ƷͼƬ��Ӧһ��ͼƬ�ļ�
        'has_uploadedfile' => array(
            'model'         => 'uploadedfile',
            'type'          => HAS_ONE,
            'foreign_key'   => 'file_id',
            'refer_key'     => 'file_id',
            'dependent'     => true
        ),
    );
}
?>