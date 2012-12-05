<?php
	class Paila_prefecture_lbWidget extends BaseWidget
	{
		public $_name = 'paila_prefecture_lb';
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
        		$paila_prefecture_lb = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
        		$paila_prefecture_lb_data = $paila_prefecture_lb['widget_data'];
        		$data = unserialize($paila_prefecture_lb_data);
				//Í¼Æ¬Ñ­»·
				foreach($data['images'] as $k => $image)
					{
						$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
					}
				$cache_server->set($key,$data,3600);	
				return $data;	
			}	
		}
	}	
?>
