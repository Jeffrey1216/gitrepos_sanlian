<?php

/* ��Ʒ��ѯ */
class GoodsQaModel extends BaseModel
{
    var $table  = 'goods_qa';
    var $prikey = 'ques_id';
    var $_name  = 'goodsqa';

    /* ������ģ��֮��Ĺ�ϵ */
    var $_relation = array(
        // һ����ѯ����һ����Ʒ
        'belongs_to_goods' => array(
            'model'       => 'goods',       //ģ�͵�����
            'type'        => BELONGS_TO,       //��ϵ����
            'foreign_key' => 'goods_id',    //�����
            'refer_key'     => 'item_id',
            'reverse' => 'be_questioned',
        ),
        //һ����ѯ����һ���Ź�
        'belong_to_groupbuy' => array(
            'model' => 'groupbuy',
            'type' => BELONGS_TO,
            'foreign_key' => 'group_id',
            'refer_key' => 'item_id',
            'reverse' => 'has_consulting',
        ),
          //һ����ѯ����һ����Ա
        'belongs_to_user' => array(
            'model' => 'member',
            'type' => BELONGS_TO,
            'foreign_key' => 'user_id',
            'reverse' => 'user_question',
        ),
          //һ����ѯ����һ������
        'belongs_to_store' => array(
            'model' => 'store',
            'type' =>BELONGS_TO,
            'foreign_key'   => 'store_id',
            'dependent' =>false,
            'reverse' => 'has_question'
        ),
    );
    var $_autov = array(
        'question_content' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'item_id' => array(
            'required' => true,
            'filter'   => 'trim',
            'type'    => 'int',
        ),
           'store_id' => array(
            'required' => true,
            'filter'    => 'trim',
            'type'    => 'int',
        ),
    );
}
?>