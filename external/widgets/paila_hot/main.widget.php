<?php

/**
 * ÂÖ²¥Í¼Æ¬¹Ò¼þ
 *
 * @return  array   $image_list
 */
class Paila_hotWidget extends BaseWidget
{
    var $_name = 'paila_hot';
    function _get_data()
    {
    	$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        if ($data === false)
        {
            $widget_mod = &m('widget');
            $goods_mod = &m('goods');
            $paila_hot = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $paila_hot_data = $paila_hot['widget_data'];
            $data = unserialize($paila_hot_data);
        foreach($data['goods'] as $k => $id)
            {
  
            	$goods_info = $goods_mod->get(array('conditions'=> "store_goods.gs_id={$id['gs_id']}",'join'=> 'belongs_to_store,g.goods,on g.goods_id = store_goods.goods_id','fields'=>'goods_name,price,default_image,yimage_url,mimage_url,smimage_url,dimage_url,simage_url,credit,store_goods.gs_id'));            	
            	$goods_infos[] = $goods_info;
            }
            $data['goods_info'] = $goods_infos;
           	for($i = 0; $i < 16 ; $i += 4)
           	{
           		$data['goods'][] = array(
           			'0'	=> $data['goods_info'][$i],
           			'1' => $data['goods_info'][$i+1],
           			'2'	=> $data['goods_info'][$i+2],
           			'3' => $data['goods_info'][$i+3]		
           		);
           	}
           	$data['goods_1'] = $data['goods'][16];
           	$data['goods_2'] = $data['goods'][17];
           	$data['goods_3'] = $data['goods'][18];
           	$data['goods_4'] = $data['goods'][19];
            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }
}

?>