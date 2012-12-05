<?php
class StoregoodsModel extends BaseModel{
	var $table  = 'store_goods';
    var $prikey = 'gs_id';
    var $_name  = 'storegoods';
    
/**
     * 取得派啦商品列表
     *
     * @param array $params     这个参数跟find函数的参数相同
     * @param int   $scate_ids  店铺商品分类id
     * @param bool  $desc       是否查描述
     * @param bool  $no_picture 没有图片时是否使用no_picture作为默认图片
     * @return array
     */
    function get_list($params = array(), $scate_ids = array(), $desc = false, $no_picture = true)
    {
        is_int($scate_ids) && $scate_ids > 0 && $scate_ids = array($scate_ids);

        extract($this->_initFindParams($params));

        $g_mod = & m('goods');
        $gs_mod    =& m('goodsspec');
        $gstat_mod =& m('goodsstatistics');
        $cg_table  = DB_PREFIX . 'category_goods';

        $fields = "g.goods_id, g.type, g.goods_name, g.cate_id, g.cate_name, g.brand, g.spec_qty, g.spec_name_1, g.spec_name_2, g.if_show, g.closed, g.add_time, g.recommended, g.default_image, " .
        		"g.zprice, g.credit, g.status, g.reason,g.goods_number,g.yimage_url,g.mimage_url, ".
        		"g.smimage_url, g.dimage_url, g.simage_url,".
                "gs.spec_id, gs.spec_1, gs.spec_2 , gs.zprice, gs.color_rgb, gs.price, sg.stock, sg.gs_id," .
                "gst.views, gst.sales, gst.comments";
        $desc && $fields .= ", g.description";
        $tables = "{$this->table} sg " .
        		"LEFT JOIN {$g_mod->table} g ON sg.goods_id = g.goods_id " .
                "LEFT JOIN {$gs_mod->table} gs ON sg.spec_id = gs.spec_id " .
                "LEFT JOIN {$gstat_mod->table} gst ON g.goods_id = gst.goods_id ";

        /* 条件(WHERE) */
        $conditions = $this->_getConditions($conditions, true);
        if ($scate_ids)
        {
            $sql = "SELECT DISTINCT goods_id FROM {$cg_table} WHERE cate_id " . db_create_in($scate_ids);
            $goods_ids = $gs_mod->getCol($sql);
            $conditions .= " AND g.goods_id " . db_create_in($goods_ids);
        }

        /* 排序(ORDER BY) */
        if ($order)
        {
            $order = ' ORDER BY ' . $this->getRealFields($order) . ', s.sort_order ';
        }

        /* 分页(LIMIT) */
        $limit && $limit = ' LIMIT ' . $limit;
        if ($count)
        {
            $this->_updateLastQueryCount("SELECT COUNT(*) as c FROM {$tables}{$conditions}");
        }

        /* 完整的SQL */
        $this->temp = $tables . $conditions;
        $sql = "SELECT {$fields} FROM {$tables}{$conditions}{$order}{$limit}";
        $goods_list = $index_key ? $this->db->getAllWithIndex($sql, $index_key) : $this->db->getAll($sql);

        // 用no_picture替换商品图片
        if ($no_picture)
        {
            foreach ($goods_list as $key => $goods)
            {
                $goods['default_image'] || $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
            }
        }

        return $goods_list;
    }
    
    //获取自营礼包(店铺id取后台设置的id)
    function _get_autotrophy($page)
    {
    	$model_setting = &af('settings');
    	$own_store = $model_setting->getOne('own_store');
    	
    	
    	$count = $this->getOne("select count(*) from pa_store_goods sg left join  
    		pa_goods g on sg.goods_id = g.goods_id where g.if_show = 1 
    		AND g.closed = 0 AND g.`status`=1 AND sg.store_id = {$own_store} 
    		and g.autotrophy = 1");
    	$goods_list = $this->getAll("select * from pa_store_goods sg left join  
    		pa_goods g on sg.goods_id = g.goods_id where g.if_show = 1 
    		AND g.closed = 0 AND g.`status`=1 AND sg.store_id = {$own_store} 
    		and g.autotrophy = 1 limit " . $page['limit']);
    	return array(
    		'goods_list' => $goods_list,
    		'count' => $count
    	);
    }
    
}
?>