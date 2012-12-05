<?php

/**
 * �ֲ�ͼƬ�Ҽ�
 *
 * @return  array   $image_list
 */
class Brand_shop_moveWidget extends BaseWidget
{
    var $_name = 'brand_shop_move';

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        if($data === false)
        {
            $widget_mod = &m("widget");
            $brand_shop_info = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $brand_shop_data = $brand_shop_info['widget_data'];
            $data = unserialize($brand_shop_data);
        	//��ѭ����ͼƬ����ͼƬͬ��������
            foreach($data['images'] as $k => $image) {
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
            }
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }

    function parse_config($input)
    {
        //return $input;
    }

    
}

?>