<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Move_ad_jqWidget extends BaseWidget
{
    var $_name = 'move_ad_jq';

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
            //图片
        	//用循环给图片定向到图片同步服务器
            foreach($data['images'] as $k => $image) {
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
            }
            
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }

    function parse_config($input)
    {
    
    }
    function get_config_datasrc() {
    	//var_dump($this->options);
    }

    function _upload_image($num)
    {
        /*import('uploader.lib');

        $images = array();
        for ($i = 0; $i < $num; $i++)
        {
            $file = array();
            foreach ($_FILES['ad_image_file'] as $key => $value)
            {
                $file[$key] = $value[$i];
            }

            if ($file['error'] == UPLOAD_ERR_OK)
            {
                $uploader = new Uploader();
                $uploader->allowed_type(IMAGE_FILE_TYPE);
                $uploader->addFile($file);
                $uploader->root_dir(ROOT_PATH);
                $images[$i] = $uploader->save('data/files/mall/template', $uploader->random_filename());
            }
        }

        return $images;*/
    }
}

?>