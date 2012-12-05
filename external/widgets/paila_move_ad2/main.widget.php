<?php
class Paila_move_ad2Widget extends BaseWidget {
	
	public $_name = 'paila_move_ad2';
	
	
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
            $ad = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $paila_ad_data = $ad['widget_data'];
            $data = unserialize($paila_ad_data);
        	//用循环给图片定向到图片同步服务器
            foreach($data['images'] as $k => $image) {
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
            }
            
            $cache_server->set($key, $data, 3600);
            //var_dump($goods_infos);
        }
        return $data;
	}

}