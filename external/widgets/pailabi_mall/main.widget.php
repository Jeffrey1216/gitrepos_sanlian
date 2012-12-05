<?php
class Pailabi_mallWidget extends BaseWidget {
	
	public $_name = 'pailabi_mall';
	
	
	public function _get_data() {
		$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        if ($data === false)
        {
        	$widget_mod = &m('widget');
        	$goods_mod = &m('goods');
        	$pailabi_mall = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
        	$pailabi_mall_data = $pailabi_mall['widget_data'];
        	$data = unserialize($pailabi_mall_data);	
        	//var_dump($data);      	
        	foreach($data['images'] as $k => $image)
        		{
        			$data['images'][$k]['img'] = IMAGE_URL.$image['img'];	
        		}	
        	$data['image_lb'][] = $data['images'][0];
        	$data['image_lb'][] = $data['images'][1];
        	$data['image_lb'][] = $data['images'][2];
        	$data['image_lb'][] = $data['images'][3];
        	$data['image_lb'][] = $data['images'][4];
        	$data['image_l'][] = $data['images'][5];
				//var_dump($data);
        }	
            	$cache_server->set($key, $data, 3600);
        
        return $data;
	}

}
?>