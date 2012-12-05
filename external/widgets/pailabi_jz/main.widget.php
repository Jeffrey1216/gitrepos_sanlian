<?php
	class Pailabi_jzWidget extends BaseWidget
	{
			public $_name = 'pailabi_jz';
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
        				$gcategory_mod = &m('gcategory');
        				$pailabi_jz = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
        				$pailabi_jz_data = $pailabi_jz['widget_data'];
        				$data = unserialize($pailabi_jz_data);
        				$data['gcategory'] = $gcategory_mod->getAll("select cate_name,cate_id from pa_gcategory where mall_type = 1 and parent_id = 0 limit 7");
						//var_dump($data);
						//图片循环
						foreach($data['images'] as $k => $image)
						{
							$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
						}
						//var_dump($data['images']);
						//商品循环
						$goods_ids = $data['goods'];
          				//var_dump($goods_ids);
          				foreach($goods_ids as $k => $id)
          				{
            				$goods_info = $goods_mod->get(array('conditions'=> "goods_id={$id['goods_id']}",'fields'=>'goods_name,price,cprice,default_image,yimage_url,mimage_url,
            																					smimage_url,dimage_url,simage_url,credit'));
            				$goods_info['default_image'] = IMAGE_URL.$goods_info['default_image'];
            				$goods_info['yimage_url'] = IMAGE_URL.$goods_info['yimage_url'];
            				$goods_info['mimage_url'] = IMAGE_URL.$goods_info['mimage_url'];
            				$goods_info['smimage_url'] = IMAGE_URL.$goods_info['smimage_url'];
            				$goods_info['dimage_url'] = IMAGE_URL.$goods_info['dimage_url'];
            				$goods_info['simage_url'] = IMAGE_URL.$goods_info['simage_url'];
            				$goods_infos[] = $goods_info;
        				}
        				$data['goods'] = $goods_infos;
            			for($i = 0 ; $i < 12 ; $i +=3)
            			{
            				if ($i == 9) {
            					$data['goods_left'] = array(
            						0 => $data['goods'][$i],
	            					1 => $data['goods'][$i + 1],
	            					2 => $data['goods'][$i + 2]
            					);
            				} else {
	            				$data['goods_all'][] = array(
	            					0 => $data['goods'][$i],
	            					1 => $data['goods'][$i + 1],
	            					2 => $data['goods'][$i + 2]
	            				);
            				}
            			}	
            			$data['goods_r'][] = $data['goods'][9];
            			$data['goods_r'][] = $data['goods'][10];
            			$data['goods_r'][] = $data['goods'][11];
            			//var_dump($data['goods_r']);
            			
            			$cache_server->set($key,$data,3600);	
					}
						
					return $data;	
				}	
		}
	
?>