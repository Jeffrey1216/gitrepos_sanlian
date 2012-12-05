<?php

/* 投票内容表 contents */
class ContentsModel extends BaseModel
{
    var $table  = 'contents';
    var $prikey = 'c_id';
    var $alias  = 'c';
    var $_name  = 'contents';
    var $_relation  = array(
        'belongs_to_theme'  => array(
            'type'          => BELONGS_TO,
            'reverse'       => 'has_contents',
    		'foreign_key'   => 'th_id',
            'model'         => 'theme',
        ),
        // 一个内容可以有多个投票用户
        'has_contentsmmm' => array(
            'model'         => 'records',
            'type'          => HAS_MANY,
            'foreign_key'   => 'c_id',
            'dependent' => true
        )
    );
}
?>