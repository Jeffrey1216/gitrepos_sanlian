<?php

/* 商品规格 goodsspec */
class PailagoodsModel extends BaseModel
{
    var $table  = 'paila_goods';
    var $prikey = 'gs_id';
    var $alias  = 'pgs';
    var $_name  = 'pailagoods';


	/**
     * 取得派啦商品列表(派拉商铺商品)
     *
     * @param array $params     这个参数跟find函数的参数相同
     * @param int   $scate_ids  店铺商品分类id
     * @param bool  $desc       是否查描述
     * @param bool  $no_picture 没有图片时是否使用no_picture作为默认图片
     * @return array
     */
    function get_pailalist($params = array(), $scate_ids = array(), $desc = false, $no_picture = true)
    {
        is_int($scate_ids) && $scate_ids > 0 && $scate_ids = array($scate_ids);

        extract($this->_initFindParams($params));

        $goods_mod = & m('goods');
        $gs_mod    =& m('goodsspec');
        $gstat_mod =& m('goodsstatistics');
        $cg_table  = DB_PREFIX . 'category_goods';

        $fields = "pg.gs_id,pg.goods_id,pg.store_id,pg.spec_id,pg.stock,g.goods_id, g.store_id, g.type, g.goods_name, g.cate_id, g.cate_name, g.brand, g.spec_qty, g.spec_name_1, g.spec_name_2, g.if_show, g.closed, g.add_time, g.recommended, g.default_image, " .
        		" g.zprice, g.credit, g.status, g.reason,g.goods_number,g.yimage_url,g.mimage_url, ".
        		"g.smimage_url, g.dimage_url, g.simage_url, ".
                "gs.spec_id, gs.spec_1, gs.spec_2, gs.color_rgb, gs.price, " .
                "gst.views, gst.sales, gst.comments";
        $desc && $fields .= ", g.description";
        $tables = " {$this->table} pg " .
        		"LEFT JOIN {$gs_mod->table} gs ON pg.spec_id = gs.spec_id " .
                "LEFT JOIN  {$goods_mod->table} g ON gs.spec_id  =  g.default_spec " .
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
}

?>