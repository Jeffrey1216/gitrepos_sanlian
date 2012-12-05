<?php

/**
 * ÂÖ²¥Í¼Æ¬¹Ò¼þ
 *
 * @return  array   $image_list
 */
class Green_hotWidget extends BaseWidget
{
    var $_name = 'green_hot';
    function _get_data()
    {
    	$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if ($data === false)
        {
        	$goods_mod = &m('goods');
        	$gcategory_mod = &m('gcategory');
            $widget_mod = &m('widget');
            $green_hot = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $green_hot_data = $green_hot['widget_data'];
            $data = unserialize($green_hot_data);
            $data['gcategory'] = $gcategory_mod->getAll("select * from pa_gcategory where parent_id in (select cate_id from pa_gcategory where parent_id = 0 AND mall_type =1 AND store_id = 0 AND if_show = 1)");
            $data['sales'] = $goods_mod->getAll("select g.goods_id,g.goods_name,g.price,g.simage_url,sg.gs_id FROM pa_store_goods sg left join  pa_goods g on g.goods_id=sg.goods_id left join pa_goods_statistics s on g.goods_id=s.goods_id left join pa_gcategory a 
            										on g.cate_id=a.cate_id WHERE g.closed=0 and g.status=1 and a.mall_type=1 ORDER BY Sales DESC LIMIT 0,8");
			
            foreach($data['goods'] as $k => $id)
            {
            	$goods_info = $goods_mod->get(array('conditions'=> "store_goods.gs_id={$id['gs_id']}",'join'=> 'belongs_to_store,g.goods,on g.goods_id = store_goods.goods_id','fields'=>'goods_name,price,default_image,yimage_url,mimage_url,smimage_url,dimage_url,simage_url,credit,store_goods.gs_id'));
            				$goods_infos[] = $goods_info;
            }
           	$data['goods'] = $goods_infos;
            $cache_server->set($key, $data, 3600);     
        }
        return $data;
    }
}

?>