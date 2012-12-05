<?php
class Paila_navigatorWidget extends BaseWidget {
	
	public $_name = 'paila_navigator';
	
	
	public function _get_data() {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
        if ($data === false)
        {
            $gcategory_mod =& bm('gcategory', array());
            $gcategories = $gcategory_mod->get_list(-1,true,2);
			
            import('tree.lib');
            $tree = new Tree();
            $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name','nav_img');
            $data = $tree->getArrayList(0);
            
        	//修改$data  加入推荐和活动
            $brand_mod = & m('brand');
            $specialSubject_mod = & m('specialsubject');
            foreach($data as $k => $v) {
            	$data[$k]['brand_info'] = $brand_mod->find(array('conditions' => 'cate_id='.$v['id'].' and store_id='.PAILAMALL));
            	$data[$k]['specialsubject_info'] = $specialSubject_mod->find(array('conditions' => 'cate_id='.$v['id']));
            }

            $cache_server->set($key, $data, 3600);
        }
        return $data;
	}

}