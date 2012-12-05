<?php

/* ��Ʒ��� goodsspec */
class PailagoodsModel extends BaseModel
{
    var $table  = 'paila_goods';
    var $prikey = 'gs_id';
    var $alias  = 'pgs';
    var $_name  = 'pailagoods';


	/**
     * ȡ��������Ʒ�б�(����������Ʒ)
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
}

?>