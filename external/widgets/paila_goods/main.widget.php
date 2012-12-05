<?php
class Paila_goodsWidget extends BaseWidget {
	
	public $_name = 'paila_goods';
	
	
	public function _get_data() {
		$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        if ($data === false)
        {
            $widget_mod = &m("widget");
            $goods_mod = & m("goods");
            $hot_goods_info = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $hot_goods_data = $hot_goods_info['widget_data'];
            $data = unserialize($hot_goods_data);
            //图片
        	//用循环给图片定向到图片同步服务器
            foreach($data['images'] as $k => $image) {
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
            }
            $goods_ids = $data['goods'];
            //var_dump($goods_ids);
            foreach($goods_ids as $k => $id) {
            	$goods_info = $goods_mod->get(array('conditions'=> "goods_id={$id['goods_id']}",'fields'=>'goods_name,price,default_image,yimage_url,mimage_url,smimage_url,dimage_url,simage_url,credit'));
            	$goods_info['default_image'] = IMAGE_URL.$goods_info['default_image'];
            	$goods_info['yimage_url'] = IMAGE_URL.$goods_info['yimage_url'];
            	$goods_info['mimage_url'] = IMAGE_URL.$goods_info['mimage_url'];
            	$goods_info['smimage_url'] = IMAGE_URL.$goods_info['smimage_url'];
            	$goods_info['dimage_url'] = IMAGE_URL.$goods_info['dimage_url'];
            	$goods_info['simage_url'] = IMAGE_URL.$goods_info['simage_url'];
            	$goods_infos[] = $goods_info;
            }
            //var_dump($goods_infos);
            $data['goods'] = $goods_infos;
            $cache_server->set($key, $data, 3600);
            //var_dump($goods_infos);
        }
        return $data;
	}

}