<?php

/**
 * �ֲ�ͼƬ�Ҽ�
 *
 * @return  array   $image_list
 */
class Home_adWidget extends BaseWidget
{
    var $_name = 'home_ad';

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
            $home_ad= $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $home_ad_data = $home_ad['widget_data'];
            $data = unserialize($home_ad_data);
           // var_dump($data);
            //ͼƬ
            //��ѭ����ͼƬ����ͼƬͬ��������
           foreach($data['images'] as $k => $image) {
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
            }
			//var_dump($data);
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }

}

?>