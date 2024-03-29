<?php
class Paila_greenWidget extends BaseWidget {
	
	public $_name = 'paila_green';
	
	
	public function _get_data() {
		$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        if ($data === false)
        {
        	$widget_mod = &m('widget');
        	$goods_mod = &m('goods');
        	$gcategory_mod = &m('gcategory');
        	$paila_green = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
        	$paila_green_data = $paila_green['widget_data'];
        	$data = unserialize($paila_green_data);
        	$data['gcategory'] = $gcategory_mod->getAll("select cate_name,cate_id from pa_gcategory where mall_type = 3 and parent_id = 0 limit 7");
        	//var_dump($data);
			//ͼƬѭ��
			foreach($data['images'] as $k => $image)
				{
					$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
				}
				$data['image1'][] = $data['images'][0];
				$data['image1'][] = $data['images'][1];
				$data['image1'][]= $data['images'][2];
				$data['image2'][] = $data['images'][3];
				$data['image2'][] = $data['images'][4];
				$data['image2'][] = $data['images'][5];
				$data['image3'][] = $data['images'][6];
				$data['image3'][] = $data['images'][7];
				$data['image3'][] = $data['images'][8];
				//var_dump($data['images']);
      		 //��Ʒ
          	 $goods_ids = $data['goods'];
            //var_dump($goods_ids);
            foreach($goods_ids as $k => $id) {
            	$goods_info = $goods_mod->get(array('conditions'=> "goods_id={$id['goods_id']}",'fields'=>'goods_name,price,default_image,yimage_url,mimage_url,smimage_url,dimage_url,simage_url,credit'));
            	$goods_info['default_image'] = IMAGE_URL.$goods_info['default_image'];
            	$goods_info['yimage_url'] = IMAGE_URL.$goods_info['yimage_url'];
            	$goods_info['mimage_url'] = IMAGE_URL.$goods_info['mimage_url'];
            	$goods_info['smimage_url'] = IMAGE_URL.$goods_info['smimage_url'];
            	$goods_info['dimage_url'] = IMAGE_URL.$goods_info['dimage_url'];
            	$goods_info['simage_url'] = IMAGE_URL.$goods_info['simage_url'];
            	$goods_infos[] = $goods_info;
            }
            	$data['goods'] = $goods_infos;
        		//var_dump($data['goods']);
            	$cache_server->set($key, $data, 3600);
        }
        //var_dump($data);
        return $data;
	}

}