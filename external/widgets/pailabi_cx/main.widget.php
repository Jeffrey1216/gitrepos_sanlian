<?php

class Pailabi_cxWidget extends BaseWidget
{
    var $_name = 'pailabi_cx';

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
            $pailabi_cx = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $pailabi_cx_data = $pailabi_cx['widget_data'];
            $data = unserialize($pailabi_cx_data); 
        	//var_dump($data);
			$cache_server->set($key, $data, 3600);
        }
        return $data;
    }
}

?>