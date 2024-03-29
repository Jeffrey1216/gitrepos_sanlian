<?php
	class Pailabi_spWidget extends BaseWidget
	{
		public $_name = 'pailabi_sp';
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
        		$pailabi_sp = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
        		$pailabi_sp_data = $pailabi_sp['widget_data'];
        		$data = unserialize($pailabi_sp_data);
        		$data['gcategory'] = $gcategory_mod->getAll("select cate_name,cate_id from pa_gcategory where mall_type = 1 and parent_id = 0 limit 7");
				//ͼƬѭ��
				foreach($data['images'] as $k => $image)
				{
					$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
				}		
				//��Ʒѭ��
				$goods_ids = $data['goods'];
          		foreach($goods_ids as $k => $id)
          		{
            		$goods_info = $goods_mod->get(array('conditions'=> "goods_id={$id['goods_id']}",'fields'=>'goods_name,price,default_image,yimage_url,mimage_url,
            																					smimage_url,dimage_url,simage_url,credit,price,cprice'));
            		$goods_info['default_image'] = IMAGE_URL.$goods_info['default_image'];
            		$goods_info['yimage_url'] = IMAGE_URL.$goods_info['yimage_url'];
            		$goods_info['mimage_url'] = IMAGE_URL.$goods_info['mimage_url'];
            		$goods_info['smimage_url'] = IMAGE_URL.$goods_info['smimage_url'];
            		$goods_info['dimage_url'] = IMAGE_URL.$goods_info['dimage_url'];
            		$goods_info['simage_url'] = IMAGE_URL.$goods_info['simage_url'];
            		$goods_infos[] = $goods_info;
        		}
            	$data['goods'] = $goods_infos;
				$data['goods_u'][] = $data['goods'][0];
				$data['goods_u'][] = $data['goods'][1];
				$data['goods_u'][] = $data['goods'][2];
				$data['goods_u'][] = $data['goods'][3];
				$data['goods_d'][] = $data['goods'][4];
				$data['goods_d'][] = $data['goods'][5];
				$data['goods_d'][] = $data['goods'][6];
			}
				$cache_server->set($key,$data,3600);	
				return $data;	
		}	
	}	
?>