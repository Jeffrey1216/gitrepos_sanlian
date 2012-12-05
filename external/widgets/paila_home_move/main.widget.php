<?php

/**
 * �ֲ�ͼƬ�Ҽ�
 *
 * @return  array   $image_list
 */
class Paila_home_moveWidget extends BaseWidget
{
    var $_name = 'paila_home_move';

    function _get_data()
    {
        $goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $widget_mod = &m("widget");
            $paila_home_move = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $paila_home_move_data = $paila_home_move['widget_data'];
            $data = unserialize($paila_home_move_data);
        	//��ѭ����ͼƬ����ͼƬͬ��������
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }

}

?>