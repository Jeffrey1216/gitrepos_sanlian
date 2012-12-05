<?php

/* ���� order */
class QuickorderModel extends BaseModel
{
    var $table  = 'quick_order';
    var $alias  = 'quick_order';
    var $prikey = 'order_id';
    var $_name  = 'quickorder';
    
    var $_relation  = array(
    // һ�������ж��������Ʒ
        'has_quickordergoods' => array(
            'model'         => 'quickordergoods',
            'type'          => HAS_MANY,
            'foreign_key'   => 'order_id',
            'dependent'     => true
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
        $model_ordergoods =& m('ordergoods');
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
