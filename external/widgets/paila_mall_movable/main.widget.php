<?php
	class Paila_mall_movableWidget extends BaseWidget
		{
			public $_name = 'paila_mall_movable';
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
        					$paila_mall_day = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
        					$paila_mall_day_data = $paila_mall_day['widget_data'];
        					$data = unserialize($paila_mall_day_data);
							//var_dump($data);
							//图片循环
//							foreach($data['images'] as $k => $image)
//								{
//									$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
//								}
							//商品循环
							foreach($data['goods'] as $k => $id)
								{
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
								 $data['goods_o'][] = $data['goods'][0];
								 $data['goods_o'][]=  $data['goods'][1];
								 $data['goods_o'][] = $data['goods'][2];
								 
								 $data['goods_t'][]= $data['goods'][3];
								 $data['goods_t'][]= $data['goods'][4];
								 $data['goods_t'][] =$data['goods'][5];
								 
								 $data['goods_s'][]= $data['goods'][6];
								 $data['goods_s'][]= $data['goods'][7];
								 $data['goods_s'][]= $data['goods'][8];
								 
								 
								 
								 //var_dump($data);
						
						}
						$cache_server->set($key,$data,3600);	
						return $data;	
				}	
		}
	
?>