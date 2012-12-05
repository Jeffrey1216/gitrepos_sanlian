<?php

/* ���� order */
class StoreorderModel extends BaseModel
{
    var $table  = 'store_order';
    var $alias  = 'store_order_alias';
    var $prikey = 'order_id';
    var $_name  = 'store_order';
    var $_relation  = array(
        // һ��������һ��ʵ����Ʒ������չ
        'has_storeorderextm' => array(
            'model'         => 'storeorderextm',
            'type'          => HAS_ONE,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        // һ�������ж��������Ʒ
        'has_storeordergoods' => array(
            'model'         => 'storeordergoods',
            'type'          => HAS_MANY,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        // һ�������ж��������־
        'has_storeorderlog' => array(
            'model'         => 'storeorderlog',
            'type'          => HAS_MANY,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        'belongs_to_user'  => array(
            'type'          => BELONGS_TO,
            'reverse'       => 'has_storeorder',
            'model'         => 'member',
        ),
        //������̶�����Ӧһ����Ӧ��
        'belongs_to_supply'  => array(
            'type'          => BELONGS_TO,
            'reverse'       => 'has_storeorder',
            'model'         => 'supply',
        ),
    );

    /**
     *    �޸Ķ�������Ʒ�Ŀ�棬�����Ǽ���Ҳ�����Ǽӻ�
     *
     *    @author    Garbin
     *    @param     string $action     [+:�ӻأ� -:����]
     *    @param     int    $order_id   ����ID
     *    @return    bool
     */
    function change_stock($action, $order_id)
    {
        if (!in_array($action, array('+', '-')))
        {
            $this->_error('undefined_action');

            return false;
        }
        if (!$order_id)
        {
            $this->_error('no_such_order');

            return false;
        }

        /* ��ȡ������Ʒ�б� */
        $model_ordergoods =& m('storeordergoods');
        $order_goods = $model_ordergoods->find("order_id={$order_id}");
        if (empty($order_goods))
        {
            $this->_error('goods_empty');

            return false;
        }

        $model_goodsspec =& m('goodsspec');
        $model_goods =& m('goods');

        /* ���θı��� */
        foreach ($order_goods as $rec_id => $goods)
        {
            $model_goodsspec->edit($goods['spec_id'], "stock=stock {$action} {$goods['quantity']}");
            $model_goods->clear_cache($goods['goods_id']);
        }

        /* �����ɹ� */
        return true;
    }
}

?>
