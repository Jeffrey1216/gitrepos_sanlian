<?php

/**
 * �ֲ�ͼƬ�Ҽ�
 *
 * @return  array   $image_list
 */
class Pr_specialWidget extends BaseWidget
{
    var $_name = 'pr_special';

    function _get_data()
    {
        $goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $widget_mod = &m("widget");
            $pr_special = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $pr_special_data = $pr_special['widget_data'];
            $data = unserialize($pr_special_data);
        	//��ѭ����ͼƬ����ͼƬͬ��������
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }

}

?>