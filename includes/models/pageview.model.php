<?php

/* ҳ����� pageview */
class PageviewModel extends BaseModel
{
    var $table  = 'pageview';
    var $prikey = 'rec_id';//
    var $_name  = 'pageview';

    var $_relation  =   array(
        // һ��ҳ����ʼ�¼ֻ������һ������
        'belongs_to_store' => array(
            'model'         => 'store',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'store_id',
            'reverse'       => 'has_pageview',
        ),
    );
}

?>