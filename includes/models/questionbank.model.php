<?php

/* ��Ŀ���� */
class QuestionbankModel extends BaseModel
{
    var $table  = 'question_bank';
    var $prikey = 'question_id';
    var $_name  = 'questionbank';

    var $_relation  = array(
        // һ����Ʒ���ֻ������һ����Ʒ
        'belongs_to_questionclasses' => array(
            'model'         => 'questionclasses',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'class_id',
            'reverse'       => 'has_questionbank',
        ),
    );
}

?>
