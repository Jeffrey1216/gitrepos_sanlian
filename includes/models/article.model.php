<?php

/* ���� article */
class ArticleModel extends BaseModel
{
    var $table  = 'article';
    var $prikey = 'article_id';
    var $_name  = 'article';

    /* ��ӱ༭ʱ�Զ���֤ */
    var $_autov = array(
        'title' => array(
            'required'  => true,    //����
            'min'       => 1,       //���1���ַ�
            'max'       => 100,     //�100���ַ�
            'filter'    => 'trim',
        ),
        'sort_order'  => array(
            'filter'    => 'intval',
        ),
        'cate_id'  => array(
            'min'       => 1,
            'required'  => true,    //����
        ),
        'link'  => array(
            'filter'    => 'trim',
            'max'       => 255,     //�100���ַ�
        ),
    );

    var $_relation = array(
        // һƪ����ֻ������һ������
        'belongs_to_store' => array(
            'model'             => 'store',
            'type'              => BELONGS_TO,
            'foreign_key'       => 'store_id',
            'reverse'           => 'has_article',
        ),
        // һƪ����ֻ������һ�����·���
        'belongs_to_acategory' => array(
            'model'             => 'acategory',
            'type'              => BELONGS_TO,
            'foreign_key'       => 'cate_id',
            'reverse'           => 'has_article',
        ),
         //һ�����¶�Ӧ����ϴ��ļ�
        'has_uploadedfile' => array(
            'model'             => 'uploadedfile',
            'type'              => HAS_MANY,
            'foreign_key' => 'item_id',
            'ext_limit' => array('belong' => BELONG_ARTICLE),
            'dependent' => true
        ),
    );

}

?>