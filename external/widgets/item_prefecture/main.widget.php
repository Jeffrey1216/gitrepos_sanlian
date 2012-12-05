<?php

/**
 * ÂÖ²¥Í¼Æ¬¹Ò¼þ
 *
 * @return  array   $image_list
 */
class Item_prefectureWidget extends BaseWidget
{
    var $_name = 'item_prefecture';
    function _get_data()
    {
    	$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        if ($data === false)
        {
            $widget_mod = &m('widget');
            $goods_mod = &m('goods');
            $item_prefecture = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $item_prefecture_data = $item_prefecture['widget_data'];
            $data = unserialize($item_prefecture_data);
      		foreach($data['goods'] as $k => $id)
            {
  
            	$goods_info = $goods_mod->get(array('conditions'=> "store_goods.gs_id={$id['gs_id']}",'join'=> 'belongs_to_store,g.goods,on g.goods_id = store_goods.goods_id','fields'=>'goods_name,price,default_image,yimage_url,mimage_url,smimage_url,dimage_url,simage_url,credit,store_goods.gs_id'));            	
            	$goods_info['default_image'] = IMAGE_URL.$goods_info['default_image'];
            				$goods_info['yimage_url'] = IMAGE_URL.$goods_info['yimage_url'];
            				$goods_info['mimage_url'] = IMAGE_URL.$goods_info['mimage_url'];
            				$goods_info['smimage_url'] = IMAGE_URL.$goods_info['smimage_url'];
            				$goods_info['dimage_url'] = IMAGE_URL.$goods_info['dimage_url'];
            				$goods_info['simage_url'] = IMAGE_URL.$goods_info['simage_url'];
            				$goods_infos[] = $goods_info;
            }
            $data['goods_info'] = $goods_infos;
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }
}

?>