<?php

/* 获奖信息表 activity_awardinfo */
class ActivityawardinfoModel extends BaseModel
{
    var $table  = 'activity_awardinfo';
    var $prikey = 'id';
    var $alias  = 'i';
    var $_name  = 'activityawardinfo';
    var $_relation  = array(
        'belongs_to_awardprize'  => array(
            'type'          => BELONGS_TO,
            'reverse'       => 'has_awardprize',
            'model'         => 'activityawardprize',
        )
    );
}
?>