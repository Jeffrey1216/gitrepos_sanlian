<?php
/*客户经理信息表*/
class CustomermanagerModel extends BaseModel
{
	var $table	=	'customer_manager';
	var $prikey	=	'user_id';
	var $_name	=	'customermanager';
	
	
    /**
     * 取得所以团购员列表
     * @param array $params     这个参数跟find函数的参数相同
     * @param int   $parent_id      父级团购员id
     * @author xiaoyu
     * @return array
     */
	function customer_all_info($parent_id = -1, $shown = false)
	{
		$conditions = "1 = 1";
        $parent_id >= 0 && $conditions .= " AND parent_id = '$parent_id'";
        $page = $shown['page'];
        if($shown['user_name'])
        {
        	$conditions .= $shown['user_name'];
        }
	    if($shown['algebra'])
        {
        	$conditions .= $shown['algebra'];
        }
		if($shown['lv'])
        {
        	$conditions .= $shown['lv'];
        }
        $num = count(
        	$this->find(array(
            'conditions' => $conditions,
        	))
        );
        $cust_info['info'] = $this->find(array(
					            'conditions' => $conditions,
					        	'limit'		 => $page['limit'],
					        )); 
		$cust_info['num'] = $num;
        return $cust_info;
	}
}

?>