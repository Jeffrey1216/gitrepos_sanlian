<?php

/**
 * �ֲ�ͼƬ�Ҽ�
 *
 * @return  array   $image_list
 */
class Three_image_adsWidget extends BaseWidget
{
    var $_name = 'three_image_ads';

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
            //ͼƬ
        	//��ѭ����ͼƬ����ͼƬͬ��������
            foreach($data['images'] as $k => $image) {
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
            }
            
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }
}

?>