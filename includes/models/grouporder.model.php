<?php
/*订单表*/
class GrouporderModel extends BaseModel
{
	var $table	=	'group_order';
	var $prikey	=	'order_id';
	var $_name	=	'grouporder';
	
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
        $model_project =& m('groupproject');
        $project_list = $model_project->getRow("select gp.id,go.quantity,gp.max_quantity from pa_group_project gp left join pa_group_order go on gp.id = go.project_id where go.order_id = " . $order_id);
        if (empty($project_list))
        {
            $this->_error('goods_empty');

            return false;
        }


        /* 依次改变库存 */
        if(intval($project_list['max_quantity']) > 0) {
        	$model_project->edit($project_list['id'], " max_quantity=max_quantity {$action} {$project_list['quantity']}");
        }


        /* 操作成功 */
        return true;
    }

}


?>