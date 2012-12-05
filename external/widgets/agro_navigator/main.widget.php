<?php
class Agro_navigatorWidget extends BaseWidget {
	
	public $_name = 'agro_navigator';
	
	public function _get_data() {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $gcategory_mod =& bm('gcategory', array());
            $gcategories = $gcategory_mod->get_list(-1,true,1);
            import('tree.lib');
            $tree = new Tree();
			$tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name','nav_img', 'is_index','mall_type');
           	$cates = $gcategory_mod->getRow('select * from pa_gcategory where mall_type=1');
           	$data = $tree->getArrayList($cates['cate_id']);
        	//修改$data  加入推荐和活动
            $brand_mod = & m('brand');
            $specialSubject_mod = & m('specialsubject');
            foreach($data as $k => $v) {
            	$data[$k]['specialsubject_info'] = $specialSubject_mod->find(array('conditions' => 'cate_id='.$v['id']));
            	foreach ($v['children'] as $_k => $_v)
            	{
            		if($_v['is_index'] == 1)
            		{
            			$data[$k]['class'][] = $_v;
            		}
            		foreach ($_v['children'] as $key => $val)
            		{
            			if($val['is_index'] == 1)
            			{
            				$data[$k]['class'][] = $val;
            			}
            			foreach ($val['children'] as $_key => $_value)
            			{
            				$data[$k]['class'][] = $_value;
            			}
            		}
            	}
            }
            $cache_server->set($key, $data, 3600);
        }
        return $data;
	}

}
?>