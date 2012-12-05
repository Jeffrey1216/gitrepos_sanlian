<?php

	class Paila_mall_adWidget extends BaseWidget
		{
			public $_name = 'paila_mall_ad';
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
        					$paila_mall_ad = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
        					$paila_mall_ad_data = $paila_mall_ad['widget_data'];
        					$data = unserialize($paila_mall_ad_data);
        					//อผฦฌัญปท
        					foreach($data['images'] as $k => $image)
        						{
        							$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
        						}		
						}
						$cache_server->set($key,$data,3600);	
						return $data;	
				}	
		}
	
?>