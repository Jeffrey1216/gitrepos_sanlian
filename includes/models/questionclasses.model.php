<?php

/* 题目分类 */
class QuestionclassesModel extends BaseModel
{
    var $table  = 'question_classes';
    var $prikey = 'class_id';
    var $_name  = 'questionclasses';

    var $_relation = array(
    	// 一个商品对应多个规格
        'has_questionbank' => array(
            'model'         => 'questionbank',
            'type'          => HAS_MANY,
            'foreign_key'   => 'class_id',
            'dependent'     => true
        ),
    ); 
}

?>
