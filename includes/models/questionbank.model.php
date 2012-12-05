<?php

/* 题目分类 */
class QuestionbankModel extends BaseModel
{
    var $table  = 'question_bank';
    var $prikey = 'question_id';
    var $_name  = 'questionbank';

    var $_relation  = array(
        // 一个商品规格只能属于一个商品
        'belongs_to_questionclasses' => array(
            'model'         => 'questionclasses',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'class_id',
            'reverse'       => 'has_questionbank',
        ),
    );
}

?>
