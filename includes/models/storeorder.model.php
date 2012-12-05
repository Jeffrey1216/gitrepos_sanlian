<?php

/* 订单 order */
class StoreorderModel extends BaseModel
{
    var $table  = 'store_order';
    var $alias  = 'store_order_alias';
    var $prikey = 'order_id';
    var $_name  = 'store_order';
    var $_relation  = array(
        // 一个订单有一个实物商品订单扩展
        'has_storeorderextm' => array(
            'model'         => 'storeorderextm',
            'type'          => HAS_ONE,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        // 一个订单有多个订单商品
        'has_storeordergoods' => array(
            'model'         => 'storeordergoods',
            'type'          => HAS_MANY,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        // 一个订单有多个订单日志
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
        //多个商铺定单对应一个供应商
        'belongs_to_supply'  => array(
            'type'          => BELONGS_TO,
            'reverse'       => 'has_storeorder',
            'model'         => 'supply',
        ),
    );

    /**
     *    修改订单中商品的库存，可以是减少也可以是加回
     *
     *    @author    Garbin
     *    @param     string $action     [+:加回， -:减少]
     *    @param     int    $order_id   订单ID
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

        /* 获取订单商品列表 */
        $model_ordergoods =& m('storeordergoods');
        $order_goods = $model_ordergoods->find("order_id={$order_id}");
        if (empty($order_goods))
        {
            $this->_error('goods_empty');

            return false;
        }

        $model_goodsspec =& m('goodsspec');
        $model_goods =& m('goods');

        /* 依次改变库存 */
        foreach ($order_goods as $rec_id => $goods)
        {
            $model_goodsspec->edit($goods['spec_id'], "stock=stock {$action} {$goods['quantity']}");
            $model_goods->clear_cache($goods['goods_id']);
        }

        /* 操作成功 */
        return true;
    }
}

?>
