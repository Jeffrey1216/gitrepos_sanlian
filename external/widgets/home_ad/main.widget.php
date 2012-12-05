<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Home_adWidget extends BaseWidget
{
    var $_name = 'home_ad';

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
            $home_ad= $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $home_ad_data = $home_ad['widget_data'];
            $data = unserialize($home_ad_data);
           // var_dump($data);
            //图片
            //用循环给图片定向到图片同步服务器
           foreach($data['images'] as $k => $image) {
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
            }
			//var_dump($data);
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }

}

?>