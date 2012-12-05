<?php
/*������*/
class GrouporderModel extends BaseModel
{
	var $table	=	'group_order';
	var $prikey	=	'order_id';
	var $_name	=	'grouporder';
	
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
        $model_project =& m('groupproject');
        $project_list = $model_project->getRow("select gp.id,go.quantity,gp.max_quantity from pa_group_project gp left join pa_group_order go on gp.id = go.project_id where go.order_id = " . $order_id);
        if (empty($project_list))
        {
            $this->_error('goods_empty');

            return false;
        }


        /* ���θı��� */
        if(intval($project_list['max_quantity']) > 0) {
        	$model_project->edit($project_list['id'], " max_quantity=max_quantity {$action} {$project_list['quantity']}");
        }


        /* �����ɹ� */
        return true;
    }

}


?>