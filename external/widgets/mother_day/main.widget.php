<?php
	class Mother_dayWidget extends BaseWidget
		{
			public $_name = 'mother_day';
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
        				$mother_day = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
        				$mother_day_data = $mother_day['widget_data'];
        				$data = unserialize($mother_day_data);
        				
						
						//用循环给图片定向到图片同步服务器
			            $goods_ids = $data['goods'];
			            foreach($goods_ids as $k => $id) {
			            	$goods_info = $goods_mod->get(array('conditions'=> "goods_id={$id['goods_id']}",'fields'=>'goods_name,cprice,price,default_image,yimage_url,mimage_url,smimage_url,dimage_url,simage_url,credit'));
			            	$goods_info['default_image'] = IMAGE_URL.$goods_info['default_image'];
			            	$goods_info['yimage_url'] = IMAGE_URL.$goods_info['yimage_url'];
			            	$goods_info['mimage_url'] = IMAGE_URL.$goods_info['mimage_url'];
			            	$goods_info['smimage_url'] = IMAGE_URL.$goods_info['smimage_url'];
			            	$goods_info['dimage_url'] = IMAGE_URL.$goods_info['dimage_url'];
			            	$goods_info['simage_url'] = IMAGE_URL.$goods_info['simage_url'];
			            	$goods_infos[] = $goods_info;
			            	$goods_infos[$k]['subjoin_img'] = IMAGE_URL.$id['subjoin_img'];
			            	$goods_infos[$k]['rebate'] = $goods_info['cprice'] == 0 ? 0 : number_format($goods_info['price']/$goods_info['cprice'], 2) * 10;
			            	$goods_infos[$k]['save'] = $goods_info['cprice'] - $goods_info['price'];
			            }
			            $goods_num = count($goods_infos);
			            for ($i = 0, $j = 0; $i < $goods_num; $i += 3, $j++)
			            {
			            	$goods_list[$j][] = $goods_infos[$i];
			            	$goods_list[$j][] = $goods_infos[$i+1];
			            	$goods_list[$j][] = $goods_infos[$i+2];
			            }
			
			            $data['goods'] = $goods_list;
					}
						$cache_server->set($key,$data,3600);	
						return $data;	
				}	
		}
	
?>