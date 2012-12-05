<?php

/* ���� store */
class SupplyModel extends BaseModel
{
    var $table  = 'supply';
    var $prikey = 'supply_id';
    var $alias  = 'su';
    var $_name  = 'supply';

    var $_relation = array(
        // һ����Ӧ���ж����Ʒ����   ����
        'has_gcategory' => array(
            'model'         => 'gcategory',
            'type'          => HAS_MANY,
            'foreign_key' => 'supply_id',
            'dependent' => true
        ),
        // һ����Ӧ���ж����Ʒ
        'has_goods' => array(
            'model'         => 'goods',
            'type'          => HAS_MANY,
            'foreign_key'   => 'supply_id',
            'dependent' => true
        ),
        // һ����Ӧ���ж�����̶���
        'has_storeorder' => array(
            'model'         => 'storeorder',
            'type'          => HAS_MANY,
            'foreign_key'   => 'seller_id',
            'dependent' => true
        ),
        'has_commoncart'    => array(
            'type'          => HAS_MANY,
            'model'         => 'commoncart',
            'foreign_key'   => 'seller_id',
        ),
        // ��Ӧ�̺ͷ����Ƕ�Զ�Ĺ�ϵ   ����
        'has_scategory' => array(
            'model'         => 'scategory',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'category_store',
            'foreign_key'   => 'supply_id',
            'reverse'       => 'belongs_to_supply',
        ),
         //һ����Ӧ�̶�Ӧ����ϴ��ļ� 
        'has_uploadedfile' => array(
            'model'             => 'uploadedfile',
            'type'              => HAS_MANY,
            'foreign_key'       => 'supply_id',
            'dependent'         => true
        ),
        //��Ӧ�̺��Ź����һ�Զ��ϵ
        'has_groupbuy' => array(
            'model' => 'groupbuy',
            'type' => HAS_MANY,
            'foreign_key' => 'supply_id',
            'dependent'   => true, // ����
        ),
    );

    var $_autov = array(
        'supply_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
    );

    /*
     * �ж������Ƿ�Ψһ
     */
    function unique($supply_name, $supply_id = 0)
    {
        $conditions = "supply_name = '" . $supply_name . "'";
        $supply_id && $conditions .= " AND supply_id <> '" . $supply_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }

    /**
     * ȡ����Ϣ
     */
    function get_info($supply_id)
    {
        $info = $this->get(array(
            'conditions' => $supply_id,
            'join'       => 'belongs_to_user',
            'fields'     => 'this.*,member.user_name, member.email',
        ));
        if (!empty($info['certification']))
        {
            $info['certifications'] = explode(',', $info['certification']);
        }
        return $info;
    }

    /**
     * ��ȡ���������˵�����
     *
     */
    function list_regions()
    {
        $data = array();
        $sql = "SELECT region_id, region_name, count(*) as count FROM {$this->table} WHERE region_id > 0 GROUP BY region_id ORDER BY count DESC LIMIT 50";
        $res = $this->db->query($sql);
        while ($row = $this->db->fetchRow($res))
        {
            $data[$row['region_id']] = $row['region_name'];
        }
        return $data;
    }



    /**
     * ȡ�ù�Ӧ��������Ϣ��������������Ʒ�����ϴ��ռ��С����Ӧ�̹���ʱ��ȵ�
     */
    function get_settings($supply_id)
    {
        return $this->get(array(
            'conditions' => $supply_id,
            'fields' => 'sgrade.*',
            'join' => 'belongs_to_sgrade',
        ));
    }

    /**
     * ��������ֵ����ͼ��
     *
     * @param   int     $credit_value   ����ֵ
     * @param   int     $step           ��͵ȼ�������������ֵ
     * @return  string  ͼƬ�ļ���
     */
    function compute_credit($credit_value, $step = 5)
    {
        $level_1 = $step * 5;
        $level_2 = $level_1 * 6;
        $level_3 = $level_2 * 6;
        $level_4 = $level_3 * 6;
        $level_5 = $level_4 * 6;
        if ($credit_value < $level_1)
        {
            return 'heart_' . (floor($credit_value / $step) + 1) . '.gif';
        }
        elseif ($credit_value < $level_2)
        {
            return 'diamond_' . (floor(($credit_value - $level_1) / $level_1) + 1) . '.gif';
        }
        elseif ($credit_value < $level_3)
        {
            return 'crown_' . (floor(($credit_value - $level_2) / $level_2) + 1) . '.gif';
        }
//        elseif ($credit_value < $level_4)
//        {
//            return (floor(($credit_value - $level_3) / $level_3) + 1) . 'level4' . '.gif';
//        }
//        elseif ($credit_value < $level_5)
//        {
//            return (floor(($credit_value - $level_4) / $level_4) + 1) . 'level5' . '.gif';
//        }
        else
        {
            return 'level_end.gif';
        }
    }

    /**
     *    �����������Ƿ����
     *
     *    @author    Garbin
     *    @param     string $subdomain  Ҫע��Ķ�������
     *    @param     string $reserved   ϵͳ����������
     *    @param     string $length     ϵͳ���Ƶ�ע�᳤��
     *    @return    bool
     */
    function check_domain($subdomain, $reserved, $length)
    {
        if (!$subdomain)
        {
            return true;
        }
        if (!preg_match("/^[a-z0-9]+$/i", $subdomain))
        {
            $this->_error('domain_format_error');

            return false;
        }

        /* ����Ƿ��Ǳ������� */
        if ($reserved)
        {
            if (in_array($subdomain, explode(',', $reserved)))
            {
                $this->_error('reserved_domain');

                return false;
            }
        }

        /* ��鳤���Ƿ�Ϸ� */
        if ($length)
        {
            list($min, $max) = explode('-', $length);
            if (strlen($subdomain) < $min || strlen($subdomain) > $max)
            {
                $this->_error('domain_length_error', $length);

                return false;
            }
        }

        /* ���Ψһ�� */
        if ($this->get("domain='{$subdomain}'"))
        {
            $this->_error('domain_exists');

            return false;
        }

        return true;
    }

    function clear_cache($supply_id)
    {
        $cache_server =& cache_server();
        $keys = array('function_get_store_data_' . $supply_id);
        foreach ($keys as $key)
        {
            $cache_server->delete($key);
        }
    }

    function edit($conditions, $edit_data)
    {
        $store_list = $this->find(array(
            'fields'     => 'supply_id',
            'conditions' => $conditions,
        ));
        foreach ($store_list as $store)
        {
            // �������
            $this->clear_cache($store['supply_id']);
        }

        return parent::edit($conditions, $edit_data);
    }

    function drop($conditions, $fields = '')
    {
        /* ������� */
        $store_list = $this->find(array(
            'fields'     => 'supply_id',
            'conditions' => $conditions,
        ));
        foreach ($store_list as $store)
        {
            $this->clear_cache($store['supply_id']);
        }

        return parent::drop($conditions, $fields);
    }

    /* ȡ�ñ���������Ʒ���� */
    function get_sgcategory_options($supply_id)
    {
        $mod =& bm('gcategory', array('_supply_id' => $supply_id));
        $gcategories = $mod->get_list();
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getOptions();
    }
}

?>
