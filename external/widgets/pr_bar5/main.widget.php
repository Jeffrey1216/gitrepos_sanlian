<?php

/**
 * �ֲ�ͼƬ�Ҽ�
 *
 * @return  array   $image_list
 */
class Pr_bar5Widget extends BaseWidget
{
    var $_name = 'pr_bar5';

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
            //ͼƬ
            //��ѭ����ͼƬ����ͼƬͬ��������
//            foreach($data['images'] as $k => $image) {
//            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
//            }
            $cache_server->set($key, $data, 3600);
        }
        //var_dump($data);
        return $data;
    }

}

?>