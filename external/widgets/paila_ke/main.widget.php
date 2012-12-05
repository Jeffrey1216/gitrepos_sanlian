<?php
class Paila_keWidget extends BaseWidget
{
    var $_name = 'paila_ke';

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
            $paila_ke = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $paila_ke_data = $paila_ke['widget_data'];
            $data = unserialize($paila_ke_data);
          	//var_dump($data);
            //图片
            //用循环给图片定向到图片同步服务器
            foreach($data['images'] as $k => $image) {
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
            }
			$data['image1'][] = $data['images'][0];
			$data['image2'][] = $data['images'][1];
            //var_dump($data['images']);
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }

}

?>