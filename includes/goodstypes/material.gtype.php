<?php

/**
 *    ʵ����Ʒ
 *
 *    @author    Garbin
 *    @usage    none
 */
class MaterialGoods extends BaseGoods
{
    function __construct($param)
    {
        $this->MaterialGoods($param);
    }
    function MaterialGoods($param)
    {
        /* ��ʼ�� */
        $param['_is_material']  = true;
        $param['_name']         = 'material';
        $param['_order_type']   = 'normal';

        parent::__construct($param);
    }
}

?>