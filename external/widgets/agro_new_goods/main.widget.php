<?php
class Agro_new_goodsWidget extends BaseWidget {
	
	public $_name = 'agro_new_goods';
	
	public function _get_data() {
		$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $widget_mod = & m("widget");
            $gcategory_mod = &m('gcategory');
            $goods_mod = & m("goods");
            $hot_goods_info = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $hot_goods_data = $hot_goods_info['widget_data'];
            $gcategory = $gcategory_mod->getAll("select cate_id,cate_name from pa_gcategory where parent_id in (select cate_id from pa_gcategory where parent_id = 0 and mall_type= 1) and if_show = 1 and store_id = 0 ");
            $data = unserialize($hot_goods_data);
            $goods_ids = $data['goods'];
            foreach($goods_ids as $k => $id) {
            	$goods_info = $goods_mod->get(array('conditions'=> "store_goods.gs_id={$id['gs_id']}",'join'=> 'belongs_to_store,g.goods,on g.goods_id = store_goods.goods_id','fields'=>'goods_name,price,default_image,yimage_url,mimage_url,smimage_url,dimage_url,simage_url,credit,store_goods.gs_id'));
            	$goods_infos[] = $goods_info;
            }
            $data['goods'] = $goods_infos;
            $data['gcategorys'] = $gcategory;
            $cache_server->set($key, $data, 3600);
        }
        return $data;
	}	
}
?>