<?php

/* 店铺 store */
class SupplyModel extends BaseModel
{
    var $table  = 'supply';
    var $prikey = 'supply_id';
    var $alias  = 'su';
    var $_name  = 'supply';

    var $_relation = array(
        // 一个供应商有多个商品分类   待定
        'has_gcategory' => array(
            'model'         => 'gcategory',
            'type'          => HAS_MANY,
            'foreign_key' => 'supply_id',
            'dependent' => true
        ),
        // 一个供应商有多个商品
        'has_goods' => array(
            'model'         => 'goods',
            'type'          => HAS_MANY,
            'foreign_key'   => 'supply_id',
            'dependent' => true
        ),
        // 一个供应商有多个商铺订单
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
        // 供应商和分类是多对多的关系   待定
        'has_scategory' => array(
            'model'         => 'scategory',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'category_store',
            'foreign_key'   => 'supply_id',
            'reverse'       => 'belongs_to_supply',
        ),
         //一个供应商对应多个上传文件 
        'has_uploadedfile' => array(
            'model'             => 'uploadedfile',
            'type'              => HAS_MANY,
            'foreign_key'       => 'supply_id',
            'dependent'         => true
        ),
        //供应商和团购活动是一对多关系
        'has_groupbuy' => array(
            'model' => 'groupbuy',
            'type' => HAS_MANY,
            'foreign_key' => 'supply_id',
            'dependent'   => true, // 依赖
        ),
    );

    var $_autov = array(
        'supply_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
    );

    /*
     * 判断名称是否唯一
     */
    function unique($supply_name, $supply_id = 0)
    {
        $conditions = "supply_name = '" . $supply_name . "'";
        $supply_id && $conditions .= " AND supply_id <> '" . $supply_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }

    /**
     * 取得信息
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
     * 获取地区检索菜单数据
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
     * 取得供应商设置信息：包括允许发布商品数，上传空间大小，供应商过期时间等等
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
     * 根据信用值计算图标
     *
     * @param   int     $credit_value   信用值
     * @param   int     $step           最低等级升级所需信用值
     * @return  string  图片文件名
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
     *    检查二级域名是否存在
     *
     *    @author    Garbin
     *    @param     string $subdomain  要注册的二级域名
     *    @param     string $reserved   系统保留的域名
     *    @param     string $length     系统限制的注册长度
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

        /* 检查是否是保留域名 */
        if ($reserved)
        {
            if (in_array($subdomain, explode(',', $reserved)))
            {
                $this->_error('reserved_domain');

                return false;
            }
        }

        /* 检查长度是否合法 */
        if ($length)
        {
            list($min, $max) = explode('-', $length);
            if (strlen($subdomain) < $min || strlen($subdomain) > $max)
            {
                $this->_error('domain_length_error', $length);

                return false;
            }
        }

        /* 检查唯一性 */
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
            // 清除缓存
            $this->clear_cache($store['supply_id']);
        }

        return parent::edit($conditions, $edit_data);
    }

    function drop($conditions, $fields = '')
    {
        /* 清除缓存 */
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

    /* 取得本店所有商品分类 */
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
