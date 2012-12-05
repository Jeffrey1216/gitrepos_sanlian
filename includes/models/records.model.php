<?php

/* Í¶Æ±¼ÇÂ¼±í records */
class RecordsModel extends BaseModel
{
    var $table  = 'records';
    var $prikey = 'r_id';
    var $alias  = 'r';
    var $_name  = 'records';
    var $_relation  = array(
        'belongs_to_contents'  => array(
            'type'          => BELONGS_TO,
            'reverse'       => 'has_contents',
            'model'         => 'contents',
        ),
       );
}
?>