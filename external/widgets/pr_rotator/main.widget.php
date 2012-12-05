
<?php

/**
 * �ֲ�ͼƬ�Ҽ�
 *
 * @return  array   $image_list
 */
class Pr_rotatorWidget extends BaseWidget
{
    var $_name = 'pr_rotator';

    function _get_data()
    {
        $goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $widget_mod = &m("widget");
            $pr_rotator = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $pr_rotator_data = $pr_rotator['widget_data'];
            $data = unserialize($pr_rotator_data);
            //ͼƬ
        	//��ѭ����ͼƬ����ͼƬͬ��������
//            foreach($data['images'] as $k => $image) 
//            {          	
//            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];	
//            }
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }

}

?>