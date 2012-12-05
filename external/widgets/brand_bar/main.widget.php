<?php

/**
 * 品牌托管轮播图
 *
 * @return  array   $image_list
 */
class Brand_barWidget extends BaseWidget
{
    var $_name = 'brand_bar';

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
            $paila_move_ad = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $paila_move_ad_data = $paila_move_ad['widget_data'];
            $data = unserialize($paila_move_ad_data);
            //图片
        	//用循环给图片定向到图片同步服务器
        	//var_dump($data['images']);
            foreach($data['images'] as $k => $image) {
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
            }
           //var_dump($data['images'][$k]);
            $cache_server->set($key, $data, 3600);
        }
        return $data;
        //var_dump($data);
    }

}

?>
