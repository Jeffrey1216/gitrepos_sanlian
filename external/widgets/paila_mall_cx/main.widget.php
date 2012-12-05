<?php

/**
 * ÂÖ²¥Í¼Æ¬¹Ò¼þ
 *
 * @return  array   $image_list
 */
class Paila_mall_cxWidget extends BaseWidget
{
    var $_name = 'paila_mall_cx';
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
            $paila_mall_cx = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $paila_mall_cx_data = $paila_mall_cx['widget_data'];
            $data = unserialize($paila_mall_cx_data); 
           // var_dump($data);
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }
}

?>