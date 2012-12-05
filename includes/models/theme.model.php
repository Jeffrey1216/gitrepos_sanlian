<?php

/* 投票主题表 theme */
class ThemeModel extends BaseModel
{
    var $table  = 'theme';
    var $prikey = 'th_id';
    var $_name  = 'theme';
    var $alias  = 'th';
    var $_relation  = array(
        // 一个主题可以有多个内容
        'has_contents' => array(
            'model'         => 'contents',
            'type'          => HAS_MANY,
            'foreign_key'   => 'th_id',
            'dependent' => true
        ),
    );
}
?>