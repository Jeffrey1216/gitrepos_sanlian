<?php
	class Paila_mall_dayWidget extends BaseWidget
		{
			public $_name = 'paila_mall_day';
			public function _get_data()
				{
					$goods_infos = array();
					$cache_server = & cache_server();
					$key = $this->_get_cache_id();
					$data = $cache_server->get($key);
					if($data === false)
						{
							$widget_mod = &m('widget');
        					$goods_mod = &m('goods');
        					$paila_mall_day = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
        					$paila_mall_day_data = $paila_mall_day['widget_data'];
        					$data = unserialize($paila_mall_day_data);
							$data['image1'][] = $data['images'][6];
							for($i = 0 ; $i<6 ; $i +=3)
								{
									$data['image2'][] = array(
											0 => $data['images'][$i],
											1 => $data['images'][$i + 1],
									);	
								}	
						}
						$cache_server->set($key,$data,3600);	
						return $data;	
				}	
		}
	
?>