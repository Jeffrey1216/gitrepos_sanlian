<?php
	class Paila_mall_lbWidget extends BaseWidget
	{
		public $_name = 'paila_mall_lb';
		public function _get_data()
		{
			$goods_infos = array();
			$cache_server = & cache_server();
			$key = $this->_get_cache_id();
			$data = $cache_server->get($key);
			$data = false;
			if($data === false)
			{
				$widget_mod = &m('widget');
        		$goods_mod = &m('goods');
        		$paila_mall_lb = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
        		$paila_mall_lb_data = $paila_mall_lb['widget_data'];
        		$data = unserialize($paila_mall_lb_data);
				//var_dump($data);
				$data['image_l'][]= $data['images'][0];
				$data['image_r'][] = $data['images'][1];
				$data['image_r'][]= $data['images'][2];
				$data['image_r'][]= $data['images'][3];
				$data['image_r'][]= $data['images'][4];
				$data['image_r'][]= $data['images'][5];
				//อผฦฌัญปท
				foreach($data['image_l'] as $k => $image)
					{
						$data['image_l'][$k]['img'] = IMAGE_URL.$image['img'];
					}
				foreach($data['image_r'] as $k => $image)
					{
						$data['image_r'][$k]['img'] = IMAGE_URL.$image['img'];
					}	
			}
				$cache_server->set($key,$data,3600);	
				return $data;	
		}	
	}	
?>