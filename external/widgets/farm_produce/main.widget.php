<?php

/**
 * ÂÖ²¥Í¼Æ¬¹Ò¼þ
 *
 * @return  array   $image_list
 */
class Farm_produceWidget extends BaseWidget
{
    var $_name = 'farm_produce';
    function _get_data()
    {
    	$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $widget_mod = &m('widget');
            $farm_produce = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $farm_produce_data = $farm_produce['widget_data'];
            $data = unserialize($farm_produce_data); 
            $cache_server->set($key, $data, 3600);         
        } 
        return $data;
    }
}

?>