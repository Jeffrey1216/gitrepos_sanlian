<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Pailabi_jyWidget extends BaseWidget
{
    var $_name = 'pailabi_jy';

    /*function _get_data()
    {
        $goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        if ($data === false)
        {
            $widget_mod = &m("widget");
            $pailabi_jy = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $pailabi_jy_data = $pailabi_jy['widget_data'];
            $data = unserialize($pailabi_jy_data);
            //图片
        	//用循环给图片定向到图片同步服务器
        	//var_dump($data);
            foreach($data['images'] as $k => $image) {
            	
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];   	
            }
            $cache_server->set($key, $data, 3600);
        }	
        	//var_dump($data);
        return $data;
    }*/
    function _get_data()
    {
    	$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        if ($data === false)
        {
        	$widget_mod = &m("widget");
        	$credit_order_mod = & m('creditorder');
        	$credit_goods_mod = & m('creditgoods');
            $pailabi_jy = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
            $pailabi_jy_data = $pailabi_jy['widget_data'];
            $data = unserialize($pailabi_jy_data);
            $credit_order_info = $credit_order_mod->getAll("select * from pa_credit_order o order by o.pay_time desc limit 0,5");
            $credit_goods_info = $credit_goods_mod->getAll("select * from pa_credit_goods g where g.type != 3 AND g.type != 4 order by g.time desc limit 0,5");
            
            //图片
        	//用循环给图片定向到图片同步服务器
            foreach($data['images'] as $k => $image) {
            	
            	$data['images'][$k]['img'] = IMAGE_URL.$image['img'];   	
            }
            $data['orders'] = $credit_order_info;
            $data['goods'] = $credit_goods_info;
            $cache_server->set($key, $data, 3600);
        }	
        return $data;
    }

}
?>
