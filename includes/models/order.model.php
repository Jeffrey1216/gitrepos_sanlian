<?php

/* 订单 order */
class OrderModel extends BaseModel
{
    var $table  = 'order';
    var $alias  = 'order_alias';
    var $prikey = 'order_id';
    var $_name  = 'order';
    var $_relation  = array(
        // 一个订单有一个实物商品订单扩展
        'has_orderextm' => array(
            'model'         => 'orderextm',
            'type'          => HAS_ONE,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        //一个订单可以投诉一次
        'has_complain' => array(
        	'model'			=> 'complain',
        	'type'			=> HAS_ONE,
        	'foreign_key'   => 'order_id',
        	'dependent'		=> true
        ),
        /*-------------自加-------------*/
        'has_allocate' => array(
            'model'         => 'allocate',
            'type'          => HAS_ONE,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        /*---------------------------*/
        // 一个订单有多个订单商品
        'has_ordergoods' => array(
            'model'         => 'ordergoods',
            'type'          => HAS_MANY,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        // 一个订单有多个订单日志
        'has_orderlog' => array(
            'model'         => 'orderlog',
            'type'          => HAS_MANY,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        'belongs_to_store'  => array(
            'type'          => BELONGS_TO,
            'reverse'       => 'has_order',
            'model'         => 'store',
        ),
        'belongs_to_user'  => array(
            'type'          => BELONGS_TO,
            'reverse'       => 'has_order',
            'model'         => 'member',
        ),
        'has_creditnotes'	=> array(
        	'model'			=>	'creditnotes',
        	'type'			=>  HAS_ONE,
        	'foreign_key'	=>	'order_id',
        	'dependent'		=>  true
        )
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
        $model_ordergoods =& m('ordergoods');
        $order_goods = $model_ordergoods->find("order_id={$order_id}");
        if (empty($order_goods))
        {
            $this->_error('goods_empty');

            return false;
        }

        $storegoods_mod = & m('storegoods');
        $promotion_mod = &m('promotion');

        /* 依次改变库存 */
        foreach ($order_goods as $rec_id => $goods)
        {

			if($goods['pr_id'] == 0)
			{
           		$storegoods_mod->edit($goods['gs_id'], "stock=stock {$action} {$goods['quantity']}");
           		if($action == '-')
           		{
           			$storegoods_mod->edit($goods['gs_id'], "selllog=selllog + {$goods['quantity']}");	
           		}
			}else {
				$promotion_mod->edit($goods['pr_id'],"pr_stock=pr_stock {$action} {$goods['quantity']}");
			    if($action == '-')
           		{
           			$promotion_mod->edit($goods['pr_id'],"pr_selllog=pr_selllog + {$goods['quantity']}");
           			$promotion_mod->edit($goods['pr_id'],"virtual_log=virtual_log + {$goods['quantity']}");	
           		}
			}
                     
        }
		
        /* 操作成功 */
        return true;
    }
}

?>
