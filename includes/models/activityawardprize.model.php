<?php

/* 专题活动奖品表 activity_awardprize */
class ActivityawardprizeModel extends BaseModel
{
    var $table  = 'activity_awardprize';
    var $prikey = 'prize_id';
    var $alias  = 'p';
    var $_name  = 'activityawardprize';
    var $_relation  = array(
        // 一个奖品可以归属与多个用户
        'has_awardprize' => array(
            'model'         => 'activityawardinfo',
            'type'          => HAS_MANY,
            'foreign_key'   => 'prize_id',
            'dependent' => true
        ),
    );
}
?>