<?php
class Paila_mapWidget extends BaseWidget
{
    var $_name = 'paila_map';

    function _get_data()
    {
        $goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        
        if ($data === false)
        {
	            $widget_mod = &m("widget");
	            $paila_map = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
	            $paila_map_data = $paila_map['widget_data'];
	  			//var_dump($paila_map_data);
	            $data = unserialize($paila_map_data);
				//var_dump($data);
            //图片
            //用循环给图片定向到图片同步服务器
	            foreach($data['images'] as $k => $image) {
	            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
            }
            $cache_server->set($key, $data, 3600);
           // var_dump($data);
        }
        return $data;
    }

}

?>