<?php

/* ר����Ʒ�� activity_awardprize */
class ActivityawardprizeModel extends BaseModel
{
    var $table  = 'activity_awardprize';
    var $prikey = 'prize_id';
    var $alias  = 'p';
    var $_name  = 'activityawardprize';
    var $_relation  = array(
        // һ����Ʒ���Թ��������û�
        'has_awardprize' => array(
            'model'         => 'activityawardinfo',
            'type'          => HAS_MANY,
            'foreign_key'   => 'prize_id',
            'dependent' => true
        ),
    );
}
?>