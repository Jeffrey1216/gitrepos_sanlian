<?php

/* ��Ʒ����ģ�� */
class GoodsModel extends BaseModel
{
    var $table  = 'goods';
    var $prikey = 'goods_id';
    var $alias  = 'g';
    var $_name  = 'goods';
    var $temp; // ��ʱ����
    var $_relation = array(
        // һ����Ʒ��Ӧһ����Ʒͳ�Ƽ�¼
        'has_goodsstatistics' => array(
            'model'         => 'goodsstatistics',
            'type'          => HAS_ONE,
            'foreign_key'   => 'goods_id',
            'dependent'     => true
        ),
        // һ����Ʒ��Ӧ������
        'has_goodsspec' => array(
            'model'         => 'goodsspec',
            'type'          => HAS_MANY,
            'foreign_key'   => 'goods_id',
            'dependent'     => true
        ),
        // һ����Ʒ��Ӧ����ļ�
        'has_uploadedfile' => array(
            'model'         => 'uploadedfile',
            'type'          => HAS_MANY,
            'foreign_key'   => 'item_id',
            'ext_limit'     => array('belong' => BELONG_GOODS),
            'dependent'     => true
        ),
        // һ����Ʒ��Ӧһ��Ĭ�Ϲ��
        'has_default_spec' => array(
            'model'         => 'goodsspec',
            'type'          => HAS_ONE,
            'refer_key'     => 'default_spec',
            'foreign_key'   => 'spec_id',
        ),
        // һ����Ʒ��Ӧ�������
        'has_goodsattr' => array(
            'model'         => 'goodsattr',
            'type'          => HAS_MANY,
            'foreign_key'   => 'goods_id',
            'dependent'     => true
        ),
        // һ����Ʒ��Ӧ���ͼƬ
        'has_goodsimage' => array(
            'model'         => 'goodsimage',
            'type'          => HAS_MANY,
            'foreign_key'   => 'goods_id',
            'dependent'     => true
        ),
        // һ����Ʒֻ������һ������
        'belongs_to_store' => array(
            'model'         => 'store',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'store_id',
            'reverse'       => 'has_goods',
        ),
        // ��Ʒ�ͷ����Ƕ�Զ�Ĺ�ϵ
        'belongs_to_gcategory' => array(
            'model'         => 'gcategory',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'category_goods',
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_goods',
        ),
        // ��Ʒ�ͻ�Ա�Ƕ�Զ�Ĺ�ϵ����Ա�ղ���Ʒ��
        'be_collect' => array(
            'model'         => 'member',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'collect',
            'foreign_key'   => 'item_id',
            'ext_limit'     => array('type' => 'goods'),
            'reverse'       => 'collect_goods',
        ),
        // ��Ʒ���Ƽ������Ƕ�Զ�Ĺ�ϵ todo
        'be_recommend' => array(
            'model'         => 'recommend',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'recommended_goods',
            'foreign_key'   => 'goods_id',
            'reverse'       => 'recommend_goods',
        ),
        //��Ʒ����Ʒ��ѯ��һ�Զ��ϵ
        'be_questioned' => array(
            'model' => 'goodsqa',
            'type' => HAS_MANY,
            'foreign_key' => 'item_id',
            'ext_limit' => array('type' => 'goods'),
            'dependent'   => true, // ����
        ),
            //��Ʒ���Ź����һ�Զ��ϵ
        'has_groupbuy' => array(
            'model' => 'groupbuy',
            'type' => HAS_MANY,
            'foreign_key' => 'goods_id',
            'dependent'   => true, // ����
        ),
    );

    var $_autov = array(
        'goods_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
    );

    /**
     * ȡ����Ʒ�б�
     *
     * @param array $params     ���������find�����Ĳ�����ͬ
     * @param int   $scate_ids  ������Ʒ����id
     * @param bool  $desc       �Ƿ������
     * @param bool  $no_picture û��ͼƬʱ�Ƿ�ʹ��no_picture��ΪĬ��ͼƬ
     * @return array
     */
    function get_list($params = array(), $scate_ids = array(), $desc = false, $no_picture = true)
    {
        is_int($scate_ids) && $scate_ids > 0 && $scate_ids = array($scate_ids);

        extract($this->_initFindParams($params));

        $gs_mod    =& m('goodsspec');
        $store_mod =& m('store');
        $gstat_mod =& m('goodsstatistics');
        $cg_table  = DB_PREFIX . 'category_goods';

        $fields = "g.goods_id, g.store_id, g.type, g.goods_name, g.cate_id, g.cate_name, g.brand, g.spec_qty, g.spec_name_1, g.spec_name_2, g.if_show, g.closed, g.add_time, g.recommended, g.default_image, " .
        		"g.cprice, g.sprice, g.zprice, g.credit, g.area_type, g.status, g.reason,g.weight,g.simage_url, ".
                "gs.spec_id, gs.spec_1, gs.spec_2, gs.color_rgb, gs.price, gs.stock, " .
                "s.store_name, s.region_id, s.region_name, s.credit_value, s.sgrade, " .
                "gst.views, gst.sales, gst.comments";
        $desc && $fields .= ", g.description";
        $tables = "{$this->table} g " .
                "LEFT JOIN {$gs_mod->table} gs ON g.default_spec = gs.spec_id " .
                "LEFT JOIN {$store_mod->table} s ON g.store_id = s.store_id " .
                "LEFT JOIN {$gstat_mod->table} gst ON g.goods_id = gst.goods_id ";

        /* ����(WHERE) */
        $conditions = $this->_getConditions($conditions, true);
        $conditions .= " AND gs.spec_id IS NOT NULL AND s.store_id IS NOT NULL ";
        if ($scate_ids)
        {
            $sql = "SELECT DISTINCT goods_id FROM {$cg_table} WHERE cate_id " . db_create_in($scate_ids);
            $goods_ids = $gs_mod->getCol($sql);
            $conditions .= " AND g.goods_id " . db_create_in($goods_ids);
        }

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

        $goods_list = $index_key ? $this->db->getAllWithIndex($sql, $index_key) : $this->db->getAll($sql);

        // ��no_picture�滻��ƷͼƬ
        if ($no_picture)
        {
            foreach ($goods_list as $key => $goods)
            {
                $goods['default_image'] || $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
            }
        }

        return $goods_list;
    }
    
    /**
     * ȡ��������Ʒ�б�
     *
     * @param array $params     ���������find�����Ĳ�����ͬ
     * @param int   $scate_ids  ������Ʒ����id
     * @param bool  $desc       �Ƿ������
     * @param bool  $no_picture û��ͼƬʱ�Ƿ�ʹ��no_picture��ΪĬ��ͼƬ
     * @return array
     */
    function get_pailalist($params = array(), $scate_ids = array(), $desc = false, $no_picture = true)
    {
        is_int($scate_ids) && $scate_ids > 0 && $scate_ids = array($scate_ids);

        extract($this->_initFindParams($params));

        $gs_mod    =& m('goodsspec');
        $gstat_mod =& m('goodsstatistics');
        $cg_table  = DB_PREFIX . 'category_goods';

        $fields = "g.goods_id, g.store_id, g.type, g.goods_name, g.cate_id, g.cate_name, g.brand, g.spec_qty, g.spec_name_1, g.spec_name_2, g.if_show, g.closed, g.add_time, g.recommended, g.default_image, " .
        		"g.cprice, g.sprice, g.zprice, g.credit, g.area_type, g.status, g.reason,g.goods_number,g.yimage_url,g.mimage_url, ".
        		"g.smimage_url, g.dimage_url, g.simage_url, g.supply_id, g.weight,g.rate,".
                "gs.spec_id, gs.spec_1, gs.spec_2, gs.color_rgb, gs.price, gs.stock, " .
                "gst.views, gst.sales, gst.comments";
        $desc && $fields .= ", g.description";
        $tables = "{$this->table} g " .
                "LEFT JOIN {$gs_mod->table} gs ON g.default_spec = gs.spec_id " .
                "LEFT JOIN {$gstat_mod->table} gst ON g.goods_id = gst.goods_id ";

        /* ����(WHERE) */
        $conditions = $this->_getConditions($conditions, true);
        if ($scate_ids)
        {
            $sql = "SELECT DISTINCT goods_id FROM {$cg_table} WHERE cate_id " . db_create_in($scate_ids);
            $goods_ids = $gs_mod->getCol($sql);
            $conditions .= " AND g.goods_id " . db_create_in($goods_ids);
        }

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

        $goods_list = $index_key ? $this->db->getAllWithIndex($sql, $index_key) : $this->db->getAll($sql);

        // ��no_picture�滻��ƷͼƬ
        if ($no_picture)
        {
            foreach ($goods_list as $key => $goods)
            {
                $goods['default_image'] || $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
            }
        }

        return $goods_list;
    }

    /**
     * ȡ����Ʒ��Ϣ
     *
     * @param int $id ��Ʒid
     * @return array
     */
    function get_info($id)
    {
        $goods = $this->get(array(
            'conditions' => "goods_id = '$id'",
            'join'       => 'belongs_to_store',
            'fields'     => 'this.*, store.state'
        ));
        if ($goods)
        {
            /* ��Ʒ��� */
            $spec_mod =& m('goodsspec');
            $specs = $spec_mod->find(array(
                'conditions' => "goods_id = '$id'",
                'order' => 'spec_id',
            ));
            $goods['_specs'][] = $specs[$goods['default_spec']];
            unset($specs[$goods['default_spec']]);
            $goods['_specs'] = array_merge($goods['_specs'], array_values($specs));
            /* ��ƷͼƬ */
            $image_mod =& m('goodsimage');
            $goods['_images'] = array_values($image_mod->find(array(
                'conditions' => "goods_id = '$id'",
                'order' => 'sort_order',
            )));

            /* ���̷��� */
            $goods['_scates'] = array_values($this->getRelatedData('belongs_to_gcategory', $id, array(
                'fields' => 'category_goods.cate_id',
            )));

            /* ͳ����� */
            $stat_mod =& m('goodsstatistics');
            $goods = array_merge($goods, $stat_mod->get_info($id));
        }

        return $goods;
    }
	/**
     * �����̳�����ȡ����Ʒ��Ϣ
     *
     * @param int $id ��Ʒid
     * @param string $area_type �����̳�
     * @return array
     */
    function get_info_by_area_type($id,$area_type)
    {
        $goods = $this->get(array(
            'conditions' => "goods_id = '$id' and area_type='$area_type'",
            'join'       => 'belongs_to_store',
            'fields'     => 'this.*, store.state'
        ));
        if ($goods)
        {
            /* ��Ʒ��� */
            $spec_mod =& m('goodsspec');
            $specs = $spec_mod->find(array(
                'conditions' => "goods_id = '$id'",
                'order' => 'spec_id',
            ));
            $goods['_specs'][] = $specs[$goods['default_spec']];
            unset($specs[$goods['default_spec']]);
            $goods['_specs'] = array_merge($goods['_specs'], array_values($specs));
            /* ��ƷͼƬ */
            $image_mod =& m('goodsimage');
            $goods['_images'] = array_values($image_mod->find(array(
                'conditions' => "goods_id = '$id'",
                'order' => 'sort_order',
            )));

            /* ���̷��� */
            $goods['_scates'] = array_values($this->getRelatedData('belongs_to_gcategory', $id, array(
                'fields' => 'category_goods.cate_id',
            )));

            /* ͳ����� */
            $stat_mod =& m('goodsstatistics');
            $goods = array_merge($goods, $stat_mod->get_info($id));
        }

        return $goods;
    }

    /**
     * ȡ�õ�����Ʒ����
     *
     * @param int $store_id
     */
    function get_count_of_store($store_id)
    {
        static $data = array();
        if (!isset($data[$store_id]))
        {
            $cache_server =& cache_server();
            $data = $cache_server->get('goods_count_of_store');
            if($data === false)
            {
                $sql = "SELECT store_id, COUNT(*) AS goods_count FROM {$this->table} WHERE if_show = 1 AND closed = 0 GROUP BY store_id";
                $data = array();
                $res = $this->db->query($sql);
                while ($row = $this->db->fetchRow($res))
                {
                    $data[$row['store_id']] = $row['goods_count'];
                }
                $cache_server->set('goods_count_of_store', $data, 3600);
            }
        }
        return isset($data[$store_id]) ? $data[$store_id] : 0;
    }

    /**
     * ��ʽ����������
     *
     * @param string $cate_name ��tab�������Ķ༶��������
     * @return ��tab���ɻ��з������ҷּ�����
     */
    function format_cate_name($cate_name)
    {
        $arr = explode("\t", $cate_name);
        if (count($arr) > 1)
        {
            for ($i = 0; $i < count($arr); $i++)
            {
                $arr[$i] = str_repeat("&nbsp;", $i * 4) . htmlspecialchars($arr[$i]);
            }
            $cate_name = join("\n", $arr);
        }

        return $cate_name;
    }

    /**
     *    ���±��ղش���
     *
     *    @author    Garbin
     *    @param     int $goods_id
     *    @return    void
     */
    function update_collect_count($goods_id)
    {
        $count = $this->db->getOne("SELECT COUNT(*) AS collect_count FROM {$this->_prefix}collect WHERE item_id={$goods_id} AND type='goods'");
        $model_goodsstatistics =& m('goodsstatistics');
        $model_goodsstatistics->edit($goods_id, array('collects' => $count));
    }

    /**
     * ɾ����Ʒ������ݣ�������ƷͼƬ����Ʒ����ͼ��Ҫ��ɾ����Ʒ֮ǰ����
     *
     * @param   string  $goods_ids  ��Ʒid���ö��Ÿ���
     */
    function drop_data($goods_ids)
    {
        $image_mod =& m('goodsimage');
        $images = $image_mod->find(array(
            'conditions' => 'goods_id' . db_create_in($goods_ids),
            'fields' => 'image_url, thumbnail',
        ));

        foreach ($images as $image)
        {
            if (!empty($image['image_url']) && trim($image['image_url']) && substr($image['image_url'], 0, 4) != 'http' && file_exists(ROOT_PATH . '/' . $image['image_url']))
            {
                _at(unlink, ROOT_PATH . '/' . $image['image_url']);
            }
            if (!empty($image['thumbnail']) && trim($image['thumbnail']) && substr($image['thumbnail'], 0, 4) != 'http' && file_exists(ROOT_PATH . '/' . $image['thumbnail']))
            {
                _at(unlink, ROOT_PATH . '/' . $image['thumbnail']);
            }
        	if (!empty($image['yimage_url']) && trim($image['yimage_url']) && substr($image['yimage_url'], 0, 4) != 'http' && file_exists(ROOT_PATH . '/' . $image['yimage_url']))
            {
                _at(unlink, ROOT_PATH . '/' . $image['yimage_url']);
            }
        	if (!empty($image['mimage_url']) && trim($image['mimage_url']) && substr($image['mimage_url'], 0, 4) != 'http' && file_exists(ROOT_PATH . '/' . $image['mimage_url']))
            {
                _at(unlink, ROOT_PATH . '/' . $image['mimage_url']);
            }
        	if (!empty($image['smimage_url']) && trim($image['smimage_url']) && substr($image['smimage_url'], 0, 4) != 'http' && file_exists(ROOT_PATH . '/' . $image['smimage_url']))
            {
                _at(unlink, ROOT_PATH . '/' . $image['smimage_url']);
            }
        	if (!empty($image['dimage_url']) && trim($image['dimage_url']) && substr($image['dimage_url'], 0, 4) != 'http' && file_exists(ROOT_PATH . '/' . $image['dimage_url']))
            {
                _at(unlink, ROOT_PATH . '/' . $image['dimage_url']);
            }
        	if (!empty($image['simage_url']) && trim($image['simage_url']) && substr($image['simage_url'], 0, 4) != 'http' && file_exists(ROOT_PATH . '/' . $image['simage_url']))
            {
                _at(unlink, ROOT_PATH . '/' . $image['simage_url']);
            }
        }
    }

    /* ������� */
    function clear_cache($goods_id)
    {
        $cache_server =& cache_server();
        $keys = array('page_of_goods_' . $goods_id);
        foreach ($keys as $key)
        {
            $cache_server->delete($key);
        }
    }

    function edit($conditions, $edit_data)
    {
        /* ������� */
        $goods_list = $this->find(array(
            'fields'     => 'goods_id',
            'conditions' => $conditions,
        ));
        foreach ($goods_list as $goods)
        {
            $this->clear_cache($goods['goods_id']);
        }

        // ����cate_idȡ��cate_id_1��cate_id_4
        if (is_array($edit_data) && isset($edit_data['cate_id']))
        {
            $edit_data = array_merge($edit_data, $this->_get_cate_ids($edit_data['cate_id']));
        }

        return parent::edit($conditions, $edit_data);
    }

    function drop($conditions, $fields = '')
    {
        /* ������� */
        $goods_list = $this->find(array(
            'fields'     => 'goods_id',
            'conditions' => $conditions,
        ));
        foreach ($goods_list as $goods)
        {
            $this->clear_cache($goods['goods_id']);
        }
        /* ���������Ʒ������ */
        $cache_server =& cache_server();
        $cache_server->delete('goods_count_of_store');

        return parent::drop($conditions, $fields);
    }

    /**
     * ȡ��ĳ�����ǰ4������id��������Ʒ��Ϊ�������ݣ������ѯ��ͳ�ƣ�
     *
     * @param   int     $cate_id    ����id
     * @return  array(
     *              'cate_id_1' => 1,
     *              'cate_id_2' => 2,
     *              'cate_id_3' => 3,
     *              'cate_id_4' => 4,
     *          )
     */
    function _get_cate_ids($cate_id)
    {
        $res = array(
            'cate_id_1' => 0,
            'cate_id_2' => 0,
            'cate_id_3' => 0,
            'cate_id_4' => 0,
        );

        if ($cate_id > 0)
        {
            $gcategory_mod =& bm('gcategory');
            $ancestor = $gcategory_mod->get_ancestor($cate_id);
            for ($i = 1; $i <= 4; $i++)
            {
                $res['cate_id_' . $i] = isset($ancestor[$i - 1]) ? $ancestor[$i - 1]['cate_id'] : 0;
            }
        }

        return $res;
    }
}

/* ��Ʒҵ��ģ�� business model */
class GoodsBModel extends GoodsModel
{
    var $_store_id = 0;

    /*
     * �ж������Ƿ�Ψһ
     */
    function unique($goods_name, $goods_id = 0)
    {
        return true;
    }

    /* ���ǻ��෽�� */
    function add($data, $compatible = false)
    {
        // store_id
        $data['store_id'] = $this->_store_id;

        // ����cate_idȡ��cate_id_1��cate_id_4
        if (!empty($data['cate_id']))
        {
            $data = array_merge($data, $this->_get_cate_ids($data['cate_id']));
        }

        $id = parent::add($data, $compatible);
        $stat_mod =& m('goodsstatistics');
        $stat_mod->add(array(
            'goods_id' => $id
        ));
        
        /* ���������Ʒ������ */
        $cache_server =& cache_server();
        $cache_server->delete('goods_count_of_store');

        return $id;
    }

    /* ���ǻ��෽�� */
    function _getConditions($conditions, $if_add_alias = false)
    {
        $alias = '';
        if ($if_add_alias)
        {
            $alias = $this->alias . '.';
        }
        $res = parent::_getConditions($conditions, $if_add_alias);
        return $res ? $res . " AND {$alias}store_id = '{$this->_store_id}'" : " WHERE {$alias}store_id = '{$this->_store_id}'";
    }

    /* ���˵����Ǳ������Ʒid */
    function get_filtered_ids($ids)
    {
        $sql = "SELECT goods_id FROM {$this->table} WHERE store_id = '{$this->_store_id}' AND goods_id " . db_create_in($ids);

        return $this->db->getCol($sql);
    }

    /* ȡ����Ʒ�� */
    function get_count()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE store_id = '{$this->_store_id}'";

        return $this->db->getOne($sql);
    }
}

?>