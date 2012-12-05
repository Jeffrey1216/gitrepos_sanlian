<?php

/* 促销模型promotion */
class PromotionModel extends BaseModel
{
    var $table  = 'promotion';
    var $prikey = 'promotion_id';
    var $_name  = 'promotion';
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
        $promotion_sotore_goods_mod = &m('promotionstoregoods');
        $store_mod = & m('store');
			
        $fields = "p.*,sg.gs_id,g.goods_id,g.spec_qty,g.goods_name,g.spec_name_1,g.brand,g.description,g.seo_title,g.seo_description,seo_keywords,g.spec_name_2,gc.spec_id,gc.price,gc.credit,gc.spec_1,gc.spec_2,gc.logistics_num,sg.store_id,s.store_name,g.cate_id,g.yimage_url,g.mimage_url,g.dimage_url
    			,g.simage_url,g.smimage_url,g.default_image";
        $tables = "{$promotion_sotore_goods_mod->table} pg " .
                "LEFT JOIN {$this->table} p ON p.promotion_id = pg.promotion_id " .
        		"LEFT JOIN {$store_goods_mod->table} sg ON pg.gs_id = sg.gs_id " .
                "LEFT JOIN {$goods_spec_mod->table} gc ON gc.spec_id = sg.spec_id " .
                "LEFT JOIN {$goods_mod->table} g ON g.goods_id = sg.goods_id " . 
        		"LEFT JOIN {$store_mod->table} s ON s.store_id = sg.store_id " . 
        /* 条件(WHERE) */
        $conditions =" ";
        if($promotion_id)
        {
        	$conditions .=  " where pr_status = 10 and pg.promotion_id=".$promotion_id;
        }

        $sql = "SELECT ".$fields." FROM ".$tables.$conditions;

        $promotioninfo = $this->db->getRow($sql);
	$promotioninfo['goods_id'] = trim($promotioninfo['goods_id']);
	if (empty($promotioninfo['goods_id'])) {
		show_message("Invalid parameter!");
		die;
	}
		/* 商品图片 */
        $image_mod =& m('goodsimage');
        $promotioninfo['_images'] = array_values($image_mod->find(array(
        'conditions' => "goods_id = ".$promotioninfo['goods_id'],
        'order' => 'sort_order',
        )));
        return $promotioninfo;
    }
    /**
     * 通过店铺ID和商品ID取得促销商品列表信息
     *
     * @return array
     */
    function get_all_promotion($goods_id,$store_id)
    {
        $goods_spec_mod    =& m('goodsspec');
        $store_goods_mod = &m('storegoods');
        $goods_mod = &m('goods');
        $promotion_sotore_goods_mod = &m('promotionstoregoods');
        $store_mod = & m('store');
			
        $fields = "p.*,sg.gs_id,g.goods_id,g.goods_name,g.spec_name_1,g.brand,g.description,g.spec_name_2,gc.spec_id,gc.price,gc.credit,gc.spec_1,gc.spec_2,gc.logistics_num,sg.store_id,s.store_name,g.cate_id,g.yimage_url,g.mimage_url,g.dimage_url
    			,g.simage_url,g.smimage_url,g.default_image";
        $tables = "{$promotion_sotore_goods_mod->table} pg " .
                "LEFT JOIN {$this->table} p ON p.promotion_id = pg.promotion_id " .
        		"LEFT JOIN {$store_goods_mod->table} sg ON pg.gs_id = sg.gs_id " .
                "LEFT JOIN {$goods_spec_mod->table} gc ON gc.spec_id = sg.spec_id " .
                "LEFT JOIN {$goods_mod->table} g ON g.goods_id = sg.goods_id " . 
        		"LEFT JOIN {$store_mod->table} s ON s.store_id = sg.store_id " . 
        /* 条件(WHERE) */
        $conditions =" ";
        if($goods_id)
        {
        	$conditions .=  " where pr_status = 10 and g.goods_id=".$goods_id;
        }
        if($goods_id)
        {
        	$conditions .=  " and sg.store_id=".$store_id;
        }

        $sql = "SELECT ".$fields." FROM ".$tables.$conditions;

        $promotioninfo = $this->db->getAll($sql);
        
        return $promotioninfo;
    }
}
?>