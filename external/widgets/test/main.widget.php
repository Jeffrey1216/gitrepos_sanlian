<?php
class TestWidget extends BaseWidget {
	
	public $_name = 'test';
	
	
	public function _get_data() {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        if($data === false)
        {
        	$params = array(
        		'area_type' => 	'delivery',
        		'limit'		=>	'6',
        	);
            $goods_mod =& m('goods');
            $data = $goods_mod->get_list($params);
            $cache_server->set($key, $data, $this->_ttl);
        }
        return $data;
	}

}

?>