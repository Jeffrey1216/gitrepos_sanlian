<?php

/* ���� order */
class OrderModel extends BaseModel
{
    var $table  = 'order';
    var $alias  = 'order_alias';
    var $prikey = 'order_id';
    var $_name  = 'order';
    var $_relation  = array(
        // һ��������һ��ʵ����Ʒ������չ
        'has_orderextm' => array(
            'model'         => 'orderextm',
            'type'          => HAS_ONE,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        //һ����������Ͷ��һ��
        'has_complain' => array(
        	'model'			=> 'complain',
        	'type'			=> HAS_ONE,
        	'foreign_key'   => 'order_id',
        	'dependent'		=> true
        ),
        /*-------------�Լ�-------------*/
        'has_allocate' => array(
            'model'         => 'allocate',
            'type'          => HAS_ONE,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        /*---------------------------*/
        // һ�������ж��������Ʒ
        'has_ordergoods' => array(
            'model'         => 'ordergoods',
            'type'          => HAS_MANY,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        // һ�������ж��������־
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

        $storegoods_mod = & m('storegoods');
        $promotion_mod = &m('promotion');

        /* ���θı��� */
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
		
        /* �����ɹ� */
        return true;
    }
}

?>
