<?php

/**
 * ÂÖ²¥Í¼Æ¬¹Ò¼þ
 *
 * @return  array   $image_list
 */
class CommendationWidget extends BaseWidget
{
    var $_name = 'commendation';
    function _get_data()
    {
    	$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if ($data === false)
        {
          
        	$gcategory_mod = &m('gcategory');
        	$goods_mod = &m('goods');
            $widget_mod = &m('widget');
            $preview_new = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $preview_new_data = $preview_new['widget_data'];
            $data = unserialize($preview_new_data); 
 			$data['gcategory'] = $gcategory_mod->getAll("select cate_id,cate_name from pa_gcategory where parent_id in (select cate_id from pa_gcategory where parent_id = 0 ) and is_index = 1 AND mall_type =0 AND store_id = 0 AND if_show = 1 limit 10");
 			foreach($data['images'] as $images)
         	{
         		$data['images'][$k]['img'] = IMAGE_URL.$images['img'];
         	}
        	foreach($data['goods'] as $k => $id)
            {
  
            	$goods_info = $goods_mod->get(array('conditions'=> "store_goods.gs_id={$id['gs_id']}",'join'=> 'belongs_to_store,g.goods,on g.goods_id = store_goods.goods_id','fields'=>'goods_name,price,default_image,yimage_url,mimage_url,smimage_url,dimage_url,simage_url,credit,store_goods.gs_id'));
            				$goods_infos[] = $goods_info;
            }
           
           	$data['goods'] = $goods_infos;
         
            $cache_server->set($key, $data, 3600);
        	
        }
        return $data;
    }
}

?>