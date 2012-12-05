<?php
	class Paila_mall_mzWidget extends BaseWidget
	{
		public $_name = 'paila_mall_mz';
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
        		$paila_mall_sm = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
        		$paila_mall_sm_data = $paila_mall_sm['widget_data'];
        		$data = unserialize($paila_mall_sm_data);
        		$data['gcategory'] = $gcategory_mod->getAll("select cate_name,cate_id from pa_gcategory where mall_type = 2 and parent_id = 0 limit 7");
				foreach($data['images'] as $k => $image)
				{
						$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
				} 
				for($i = 0 ; $i< 5 ; $i +=2)
				{
						$data['imagesGroup'][] = array(
							0 => $data['images'][$i],
							1 => $data['images'][$i + 1]
						);
				}
				$goods_ids = $data['goods'];
          		foreach($goods_ids as $k => $id)
          		{
            		$goods_info = $goods_mod->get(array('conditions'=> "goods_id={$id['goods_id']}",'fields'=>'goods_name,price,default_image,yimage_url,mimage_url,
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
				for($i = 0, $j=0 ,$k = 0; $i < 12,$j<5, $k<3 ;$i += 4,$j += 2,$k++)
				{
					$data['page'][$k]['goods_all'] = array(
						0 => $data['goods'][$i],
						1 => $data['goods'][$i + 1],
						2 => $data['goods'][$i + 2],
						3 => $data['goods'][$i + 3]
					);
					$data['page'][$k]['imagesGroup'] = array(
						0 => $data['images'][$j],
						1 => $data['images'][$j + 1]
					);
				}
					$order = $goods_mod->getAll("SELECT g.default_image,g.goods_id,g.simage_url,g.yimage_url,g.mimage_url,g.smimage_url,g.dimage_url,g.goods_name,g.cprice,
												g.price,g.credit,o.seller_id,o.seller_name,o.add_time,o.buyer_name FROM pa_order_goods og left join pa_order o on
 												o.order_id = og.order_id left join pa_goods g on og.goods_id = g.goods_id WHERE og.goods_id IN (SELECT goods_id FROM pa_goods 
 												WHERE cate_id IN (select gc1.cate_id from pa_gcategory gc1 where gc1.parent_id in (select cate_id from pa_gcategory where parent_id = 858 or parent_id = 752) AND gc1.mall_type=2)) 
												and o.add_time >= 1302027022 ORDER BY o.add_time ");
					foreach($order as $k => $value)
					{
						$order[$k]['default_image'] = IMAGE_URL.$order[$k]['default_image'];
						$order[$k]['yimage_url'] = IMAGE_URL.$order[$k]['yimage_url'];
						$order[$k]['mimage_url'] = IMAGE_URL.$order[$k]['mimage_url'];
						$order[$k]['smimage_url'] = IMAGE_URL.$order[$k]['smimage_url'];
						$order[$k]['dimage_url'] = IMAGE_URL.$order[$k]['dimage_url'];
						$order[$k]['simage_url'] = IMAGE_URL.$order[$k]['simage_url'];	
					}
					$data['goods_gl'][] =$order[0];
					$data['goods_gl'][] =$order[1];
	
					$data['goods_gm'][] =$order[2];
					$data['goods_gm'][] =$order[3];
					
					$data['goods_gr'][] =$order[4];
					$data['goods_gr'][] =$order[5];
					$data['orders'][] = $data['goods_gl'];
					$data['orders'][] = $data['goods_gm'];
					$data['orders'][] = $data['goods_gr'];
				}
						
					$cache_server->set($key,$data,3600);	
					return $data;	
			}	
	}	
?>