<?php

!defined('ROOT_PATH') && exit('Forbidden');

/**
 *    ��Ʒ���ͻ���
 *
 *    @author    Garbin
 *    @usage    none
 */
class BaseGoods extends Object
{
    var $_is_material;  // �Ƿ�ʵ����Ʒ��֧���ӿڿ�����Ҫ�õ�
    var $_name;         // ��Ʒ���͵�����
    var $_order_type;   // ��Ӧ�Ķ�������

    function __construct($params)
    {
        $this->BaseGoods($params);
    }
    function BaseGoods($params)
    {
        if (!empty($params))
        {
            foreach ($params as $key => $value)
            {
                $this->$key = $value;
            }
        }
    }

    /**
     *    ��ȡ��Ӧ��������ʵ��
     *
     *    @author    Garbin
     *    @param     array $params
     *    @return    void
     */
    function get_order_type()
    {
        return $this->_order_type;
    }

    /**
     *    ��ȡ��������
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function get_name()
    {
        return $this->_name;
    }

    /**
     *    �Ƿ���ʵ����Ʒ
     *
     *    @author    Garbin
     *    @return    void
     */
    function is_material()
    {
        return $this->_is_material;
    }
}


?>