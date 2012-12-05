<?php

/**
 * ÂÖ²¥Í¼Æ¬¹Ò¼þ
 *
 * @return  array   $image_list
 */
class Promotion_fectureWidget extends BaseWidget
{
    var $_name = 'promotion_fecture';
    function _get_data()
    {
    	$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $widget_mod = &m('widget');
            $promotion_fecture = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $promotion_fecture_data = $promotion_fecture['widget_data'];
            $data = unserialize($promotion_fecture_data); 
	        foreach($data['images'] as $k=>$v)
	        {
	        	$data[$k]['images'] = IMAGE_URL.$v['images'];
	        }
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }
}

?>