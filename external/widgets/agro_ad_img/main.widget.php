<?php

/**
 * นใธๆอผฦฌนาผþ
 *
 * @return  array   $image_list
 */
class Agro_ad_imgWidget extends BaseWidget
{
    var $_name = 'agro_ad_img';

    function _get_data()
    {
        $goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $widget_mod = &m("widget");
            $paila_move_ad = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $paila_move_ad_data = $paila_move_ad['widget_data'];
            $data = unserialize($paila_move_ad_data);      
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }

}

?>