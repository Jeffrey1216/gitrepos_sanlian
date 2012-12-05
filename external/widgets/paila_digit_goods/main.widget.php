<?php
class Paila_digit_goodsWidget extends BaseWidget {
	
	public $_name = 'paila_digit_goods';
	
	
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
            $goods_statistics_mod = & m("goodsstatistics");
            //取出销量最大的10件商品
            $sql = 'SELECT *,goods_statistics.goods_id FROM pa_goods_statistics goods_statistics LEFT JOIN pa_goods g ON goods_statistics.goods_id = g.goods_id left join pa_gcategory gcategory on g.cate_id=gcategory.cate_id WHERE g.area_type="delivery" and gcategory.parent_id=(SELECT p.cate_id FROM pa_gcategory p where p.cate_name="女装/女士精品") ORDER BY goods_statistics.sales DESC LIMIT 10';
            $paila_goods_infos = $goods_statistics_mod->getAll($sql);
            //var_dump($paila_goods_infos);
            
            $paila_new_goods_info = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $paila_new_goods_data = $paila_new_goods_info['widget_data'];
            $data = unserialize($paila_new_goods_data);
            //图片
       	 	//用循环给图片定向到图片同步服务器
            foreach($data['images'] as $k => $image) {
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];
            }
            $goods_ids = $data['goods'];
            foreach($goods_ids as $k => $id) {
            	$goods_infos[$k]['goods_info'] = $goods_mod->get(array('conditions'=> "goods_id={$id['goods_id']}",'fields'=>'goods_name,cprice,price,default_image,yimage_url,mimage_url,smimage_url,dimage_url,simage_url,credit,area_type'));
            	$goods_infos[$k]['goods_info']['credit'] = intval($goods_infos[$k]['goods_info']['price'])/2;
            	$goods_infos[$k]['goods_info']['default_image'] = IMAGE_URL.$goods_infos[$k]['goods_info']['default_image'];
            	$goods_infos[$k]['goods_info']['yimage_url'] = IMAGE_URL.$goods_infos[$k]['goods_info']['yimage_url'];
            	$goods_infos[$k]['goods_info']['mimage_url'] = IMAGE_URL.$goods_infos[$k]['goods_info']['mimage_url'];
            	$goods_infos[$k]['goods_info']['smimage_url'] = IMAGE_URL.$goods_infos[$k]['goods_info']['smimage_url'];
            	$goods_infos[$k]['goods_info']['dimage_url'] = IMAGE_URL.$goods_infos[$k]['goods_info']['dimage_url'];
            	$goods_infos[$k]['goods_info']['simage_url'] = IMAGE_URL.$goods_infos[$k]['goods_info']['simage_url'];
            	$goods_infos[$k]['subjoin_img'] = $id['subjoin_img'];
            }
            $data['sales_volume'] = $paila_goods_infos; //销量排行
            $data['goods'] = $goods_infos;
            $cache_server->set($key, $data, 3600);
        }
        return $data;
	}

}