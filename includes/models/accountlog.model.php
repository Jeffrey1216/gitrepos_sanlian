<?php

/* �˻���־�� accountlog */
class AccountlogModel extends BaseModel
{
    var $table  = 'account_log';
    var $prikey = 'log_id';
    var $_name  = 'accountlog';
    
	function get_account_list($params = array(), $desc = false, $no_picture = true)
    {
        extract($this->_initFindParams($params));

        $goods_mod = & m('accountlog');

        $fields = "log_id,user_id,user_money,frozen_money,user_credit,frozen_credit,change_time,change_desc,change_type,order_id,verify_id";
        $tables = " {$this->table} account_log ";

        /* ����(WHERE) */
        $conditions = $this->_getConditions($conditions, true);

        /* ����(ORDER BY) */
        if ($order)
        {
            $order = ' ORDER BY account_log.change_time DESC ';
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
    /* ������ʾ ������� һ��*/
    	function get_account_orders($params = array(), $desc = false, $no_picture = true)
    {
        extract($this->_initFindParams($params));

        $fields = "log_id,user_id,user_money,frozen_money,user_credit,frozen_credit,change_time,change_desc,change_type,account_log.order_id,order_sn,verify_id";
        $tables = " {$this->table} account_log ";
	$left_table = " LEFT JOIN pa_order o ON account_log.order_id = o.order_id ";

        /* ����(WHERE) */
        $conditions = $this->_getConditions($conditions, true);

        /* ����(ORDER BY) */
        if ($order)
        {
            $order = ' ORDER BY account_log.change_time DESC ';
        }

        /* ��ҳ(LIMIT) */
        $limit && $limit = ' LIMIT ' . $limit;
        if ($count)
        {
            $this->_updateLastQueryCount("SELECT COUNT(*) as c FROM {$tables}{$conditions}");
        }

        /* ������SQL */
        $this->temp = $tables . $conditions;
        $sql = "SELECT {$fields} FROM {$tables}{$left_table}{$conditions}{$order}{$limit}";
        $notes_list = $index_key ? $this->db->getAllWithIndex($sql, $index_key) : $this->db->getAll($sql);

        return $notes_list;
    }
}
?>