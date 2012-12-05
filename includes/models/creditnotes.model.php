<?php
class CreditnotesModel extends BaseModel {
	
	var $table  = 'credit_notes';
    var $prikey = 'id';
    var $alias  = 'cn';
    var $_name  = 'creditnotes';
    
    
	function get_notes_list($params = array(), $desc = false, $no_picture = true)
    {
        extract($this->_initFindParams($params));

        $goods_mod = & m('creditnotes');

        $fields = "cn.id,cn.credit_change,cn.order_id,cn.uid,cn.notes,cn.income_expense,cn.operate_time,cn.order_type";
        $tables = " {$this->table} cn ";

        /* ����(WHERE) */
        $conditions = $this->_getConditions($conditions, true);

        /* ����(ORDER BY) */
        if ($order)
        {
            $order = ' ORDER BY ' . $this->getRealFields($order) . ', s.sort_order ';
        }

        /* ��ҳ(LIMIT) */
        $limit && $limit = ' LIMIT ' . $limit;
        if ($count)
        {
            $this->_updateLastQueryCount("SELECT COUNT(*) as c FROM {$tables}{$conditions}");
        }

        /* ������SQL */
        $this->temp = $tables . $conditions;
        $sql = "SELECT {$fields} FROM {$tables}{$conditions}{$order}{$limit}";

        $notes_list = $index_key ? $this->db->getAllWithIndex($sql, $index_key) : $this->db->getAll($sql);

        return $notes_list;
    }
	
}

?>