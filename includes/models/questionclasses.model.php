<?php

/* ��Ŀ���� */
class QuestionclassesModel extends BaseModel
{
    var $table  = 'question_classes';
    var $prikey = 'class_id';
    var $_name  = 'questionclasses';

    var $_relation = array(
    	// һ����Ʒ��Ӧ������
        'has_questionbank' => array(
            'model'         => 'questionbank',
            'type'          => HAS_MANY,
            'foreign_key'   => 'class_id',
            'dependent'     => true
        ),
    ); 
}

?>
