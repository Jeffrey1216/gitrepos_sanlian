<?php
class Paila_cosmetic_goodsWidget extends BaseWidget {
	
	public $_name = 'paila_cosmetic_goods';
	
	
	public function _get_data() {
		$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        if ($data === false)
        {
            $widget_mod = &m("widget");
            $goods_mod = & m("goods");
            $paila_new_goods_info = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $paila_new_goods_data = $paila_new_goods_info['widget_data'];
            $data = unserialize($paila_new_goods_data);
            //图片
        	//用循环给图片定向到图片同步服务器
            foreach($data['images'] as $k => $image) {
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
            }
            $data['top'] = $data['images'][0];
            foreach($data['images'] as $k => $v) {
            	if($k == 0) {
            		continue;
            	}
            	$data['right'][] = $v;
            }
        	
            $goods_ids = $data['goods'];
            foreach($goods_ids as $k => $id) {
            	$goods_info = $goods_mod->get(array('conditions'=> "goods_id={$id['goods_id']} and area_type='pailamall'",'fields'=>'goods_name,price,default_image,yimage_url,mimage_url,smimage_url,dimage_url,simage_url,credit'));
            	$goods_infos[$k]['goods_info'] = $goods_mod->get(array('conditions'=> "goods_id={$id['goods_id']}",'fields'=>'goods_name,cprice,price,default_image,yimage_url,mimage_url,smimage_url,dimage_url,simage_url,credit'));
            	$goods_infos[$k]['goods_info']['credit'] = intval($goods_infos[$k]['goods_info']['price']);
            	$goods_infos[$k]['goods_info']['default_image'] = IMAGE_URL.$goods_infos[$k]['goods_info']['default_image'];
            	$goods_infos[$k]['goods_info']['yimage_url'] = IMAGE_URL.$goods_infos[$k]['goods_info']['yimage_url'];
            	$goods_infos[$k]['goods_info']['mimage_url'] = IMAGE_URL.$goods_infos[$k]['goods_info']['mimage_url'];
            	$goods_infos[$k]['goods_info']['smimage_url'] = IMAGE_URL.$goods_infos[$k]['goods_info']['smimage_url'];
            	$goods_infos[$k]['goods_info']['dimage_url'] = IMAGE_URL.$goods_infos[$k]['goods_info']['dimage_url'];
            	$goods_infos[$k]['goods_info']['simage_url'] = IMAGE_URL.$goods_infos[$k]['goods_info']['simage_url'];
            	$goods_infos[$k]['subjoin_img'] = $id['subjoin_img'];
            }
            $data['goods'] = $goods_infos;
            
            $cache_server->set($key, $data, 3600);
        }
        return $data;
	}

}
