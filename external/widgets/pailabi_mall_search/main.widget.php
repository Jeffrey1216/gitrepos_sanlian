<?php

class Pailabi_mall_searchWidget extends BaseWidget
{
    var $_name = 'pailabi_mall_search';

    function _get_data()
    {
    	$goods_infos = array();
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        if ($data === false)
        {
            $gcategory_mod = & m('gcategory');
            $data['gcategory'] = $gcategory_mod->getAll('select * from pa_gcategory where parent_id = 0 and mall_type = 1 and store_id = 0 and is_index = 1');     
           foreach ($data['gcategory'] as $k => $v)
            {
            	$cate_id=$v['cate_id'];
            	$sql='select * from pa_gcategory where store_id = 0 and is_index= 1 and parent_id ='."$cate_id";
            	$data['gcategory'][$k][] = $gcategory_mod->getAll($sql);	
            }	
            $cache_server->set($key, $data, 3600);
        }
     
        return $data;
    }


}

?>