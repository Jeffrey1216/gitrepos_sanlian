<?php

/* 促销模型promotion */
class PromotionstoregoodsModel extends BaseModel
{
    var $table  = 'promotion_store_goods';
    var $prikey = 'id';
    var $_name  = 'promotion_store_goods';

    /**
     * 取得促销商品列表信息
     *
     * @return array
     */
    function get_promotioninfo($params,$goods_name)
    {
    	//初始化变量
        $goods_spec_mod    =& m('goodsspec');
        $store_goods_mod = &m('storegoods');
        $goods_mod = &m('goods');
        $promotion_mod = &m('promotion');
        $store_mod = & m('store');
			
        $fields = "p.*,sg.gs_id,g.goods_id,g.goods_name,g.spec_name_1,g.brand,g.description,g.spec_name_2,gc.spec_id,gc.price,gc.credit,gc.spec_1,gc.spec_2,sg.store_id,s.store_name,g.cate_id,g.yimage_url,g.mimage_url,g.dimage_url
    			,g.simage_url,g.smimage_url,g.default_image";
        $tables = "{$this->table} pg " .
                "LEFT JOIN {$promotion_mod->table} p ON p.promotion_id = pg.promotion_id " .
        		"LEFT JOIN {$store_goods_mod->table} sg ON pg.gs_id = sg.gs_id " .
                "LEFT JOIN {$goods_spec_mod->table} gc ON gc.spec_id = sg.spec_id " .
                "LEFT JOIN {$goods_mod->table} g ON g.goods_id = sg.goods_id " . 
        		"LEFT JOIN {$store_mod->table} s ON s.store_id = sg.store_id " . 
        /* 条件(WHERE) */
        $conditions = " ";
        $conditions = " where 1=1 ";
        if($goods_name)
        {
        	$conditions .= " and g.goods_name like '%".$goods_name."%'";
        }
        if($status)
        {
        	$conditions .= " and p.pr_status =".$status;
        }
        if(isset($params['status']))
        {
        	$conditions .= " and pr_status = ".$params['status'];
        }

        /* 分页(LIMIT) */
        $limit = "";
        if(isset($params['page'])){
        	$limit = " limit ".$params['page']['limit'];
        }
		/* 排序 */
		$orderBy = " ORDER BY p.pr_sort ";

        /* 完整的SQL */
        $this->temp = $tables . $conditions;
        $sql = "SELECT {$fields} FROM {$tables}{$conditions}{$orderBy}{$limit}";
        $item_count = $this->db->getRow("SELECT COUNT(*) as count FROM {$tables}{$conditions}");
        $promotion = $this->db->getAll($sql);
        $promotioninfo['goods'] = $promotion;
        $promotioninfo['item_count'] = $item_count['count'];
        return $promotioninfo;
    }
    /**
     * 取得促销单个商品列表信息
     *
     * @return array
     */
    function get_promotion($promotion_id)
    {
        $goods_spec_mod    =& m('goodsspec');
        $store_goods_mod = &m('storegoods');
        $goods_mod = &m('goods');
        $promotion_mod = &m('promotion');
        $store_mod = & m('store');

       $fields = "p.*,sg.gs_id,g.goods_id,g.goods_name,sg.stock,g.spec_name_1,g.brand,g.description,g.spec_name_2,gc.spec_id,gc.price,gc.credit,gc.spec_1,gc.spec_2,sg.store_id,s.store_name,g.cate_id,g.yimage_url,g.mimage_url,g.dimage_url
    			,g.simage_url,g.smimage_url,g.default_image";
       $tables = "{$this->table} pg " .
                "LEFT JOIN {$promotion_mod->table} p ON p.promotion_id = pg.promotion_id " .
        		"LEFT JOIN {$store_goods_mod->table} sg ON pg.gs_id = sg.gs_id " .
                "LEFT JOIN {$goods_spec_mod->table} gc ON gc.spec_id = sg.spec_id " .
                "LEFT JOIN {$goods_mod->table} g ON g.goods_id = sg.goods_id " . 
        		"LEFT JOIN {$store_mod->table} s ON s.store_id = sg.store_id " . 
        /* 条件(WHERE) */
        $conditions =" ";
        if($promotion_id)
        {
        	$conditions .=  " where pg.promotion_id=".$promotion_id;
        }

        $sql = "SELECT ".$fields." FROM ".$tables.$conditions;

        $promotioninfo = $this->db->getRow($sql);

        return $promotioninfo;
    }
    /**
     * 取得本店促销商品列表信息
     *
     * @return array
     */
    function get_promotion_list($page,$store_id)
    {
    	//初始化变量
        $goods_spec_mod    =& m('goodsspec');
        $store_goods_mod = &m('storegoods');
        $goods_mod = &m('goods');
        $promotion_mod = &m('promotion');
        $store_mod = & m('store');
			
        $fields = "p.*,sg.gs_id,g.goods_id,g.goods_name,g.spec_name_1,g.brand,g.description,g.spec_name_2,gc.spec_id,gc.price,gc.credit,gc.spec_1,gc.spec_2,sg.store_id,s.store_name,g.cate_id,g.yimage_url,g.mimage_url,g.dimage_url
    			,g.simage_url,g.smimage_url,g.default_image";
        $tables = "{$this->table} pg " .
                "LEFT JOIN {$promotion_mod->table} p ON p.promotion_id = pg.promotion_id " .
        		"LEFT JOIN {$store_goods_mod->table} sg ON pg.gs_id = sg.gs_id " .
                "LEFT JOIN {$goods_spec_mod->table} gc ON gc.spec_id = sg.spec_id " .
                "LEFT JOIN {$goods_mod->table} g ON g.goods_id = sg.goods_id " . 
        		"LEFT JOIN {$store_mod->table} s ON s.store_id = sg.store_id " . 
        /* 条件(WHERE) */
        $conditions = " ";
        
        /* 分页(LIMIT) */
        $limit = "";
        if(isset($page)){
        	$limit = " limit ".$page['limit'];
        }
        if(isset($store_id))
        {
        	$conditions .= " where s.store_id=".$store_id;
        }
        /* 完整的SQL */
        $this->temp = $tables . $conditions;
        
        $sql = "SELECT {$fields} FROM {$tables}{$conditions}{$limit}";
        $item_count = $this->db->getRow("SELECT COUNT(*) as count FROM {$tables}{$conditions}");
        $promotion = $this->db->getAll($sql);
        $promotioninfo['goods'] = $promotion;
        $promotioninfo['item_count'] = $item_count['count'];
        return $promotioninfo;
    }
    /**
     * 取得本店促销商品列表信息
     *
     * @return array
     */
    function get_promotion_all_list($page,$store_id,$param ="")
    {
    	//初始化变量
        $goods_spec_mod    =& m('goodsspec');
        $store_goods_mod = &m('storegoods');
        $goods_mod = &m('goods');
        $promotion_mod = &m('promotion');
        $store_mod = & m('store');
			
        $fields = "p.*,sg.gs_id,g.goods_id,g.goods_name,g.spec_name_1,g.brand,g.description,g.spec_name_2,gc.spec_id,gc.price,gc.credit,gc.spec_1,gc.spec_2,sg.store_id,s.store_name,g.cate_id,g.yimage_url,g.mimage_url,g.dimage_url
    			,g.simage_url,g.smimage_url,g.default_image";
        $tables = "{$this->table} pg " .
                "LEFT JOIN {$promotion_mod->table} p ON p.promotion_id = pg.promotion_id " .
        		"LEFT JOIN {$store_goods_mod->table} sg ON pg.gs_id = sg.gs_id " .
                "LEFT JOIN {$goods_spec_mod->table} gc ON gc.spec_id = sg.spec_id " .
                "LEFT JOIN {$goods_mod->table} g ON g.goods_id = sg.goods_id " . 
        		"LEFT JOIN {$store_mod->table} s ON s.store_id = sg.store_id " . 
        /* 条件(WHERE) */
        $conditions = " ";
        
        $conditions = ' where 1=1';
        /* 分页(LIMIT) */
        $limit = "";
        if(isset($page)){
        	$limit = " limit ".$page['limit'];
        }
        if(isset($store_id))
        {
        	$conditions .= " and s.store_id=".$store_id;
        }
        if(!empty($param['keywords']))
        {
        	$conditions .= " AND g.goods_name like '%".$param['keywords']."%'";
        }
        /* 完整的SQL */
        $this->temp = $tables . $conditions;
        
        $sql = "SELECT {$fields} FROM {$tables}{$conditions}{$limit}";
        $item_count = $this->db->getRow("SELECT COUNT(*) as count FROM {$tables}{$conditions}");
        $promotion = $this->db->getAll($sql);
        $promotioninfo['goods'] = $promotion;
        $promotioninfo['item_count'] = $item_count['count'];
        return $promotioninfo;
    }
}
?>