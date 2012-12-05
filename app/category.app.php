<?php

/*���������*/
class CategoryApp extends MallbaseApp
{
    /* ��Ʒ���� */
    function index()
    {
        /* ȡ�õ��� */
        $this->assign('navs', $this->_get_navs());

        /* ȡ����Ʒ���� */
        $gcategorys = $this->_list_gcategory();

        /* ��ǰλ�� */
        $_curlocal=array(
            array(
                'text'  => Lang::get('index'),
                'url'   => 'index.php',
            ),
            array(
                'text'  => Lang::get('gcategory'),
                'url'   => '',
            ),
        );
        $gcategorys_arr = array_chunk($gcategorys,ceil(count($gcategorys)/2));
        $this->assign('_curlocal',$_curlocal);
        $this->assign('gcategorys', $gcategorys_arr);
        

        $this->_config_seo('title', Lang::get('goods_category') . ' - '. Conf::get('site_title'));
        $this->display('category.goods.html');
    }

        /* ���̷��� */
    function store()
    {
        /* ȡ�õ��� */
        $this->assign('navs', $this->_get_navs());
        /* ȡ����Ʒ���� */
        $scategorys = $this->_list_scategory();
        /* ȡ�����µ��� */
        $new_stores = $this->_new_stores(5);
        /* ȡ���Ƽ����� */
        $recommended_stores = $this->_recommended_stores(5);
        /* ��ǰλ�� */
        $_curlocal=array(
            array(
                'text'  => Lang::get('index'),
                'url'   => 'index.php',
            ),
            array(
                'text'  => Lang::get('scategory'),
                'url'   => '',
            ),
        );
        $this->assign('_curlocal',$_curlocal);
        $this->assign('new_stores', $new_stores);
        $this->assign('recommended_stores', $recommended_stores);
        $scategorys_arr = array_chunk($scategorys,ceil(count($scategorys)/2));
        $this->assign('scategorys', $scategorys_arr);

        $this->_config_seo('title', Lang::get('store_category') . ' - '. Conf::get('site_title'));
        $this->display('category.store.html');
    }

        /* ȡ����Ʒ���� */
    function _list_gcategory()
    {
        $cache_server =& cache_server();
        $key = 'page_goods_category';
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $gcategory_mod =& bm('gcategory', array('_store_id' => 0));
            $gcategories = $gcategory_mod->get_list(-1,true);
    
            import('tree.lib');
            $tree = new Tree();
            $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
            $data = $tree->getArrayList(0);

            $cache_server->set($key, $data, 3600);
        }

        return $data;
    }

            /* ȡ�õ��̷��� */
    function _list_scategory()
    {
        $scategory_mod =& m('scategory');
        $scategories = $scategory_mod->get_list(-1,true);

        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getArrayList(0);
    }

            /* ȡ�����µ��� */
    function _new_stores($num)
    {
        $store_mod =& m('store');
        $goods_mod =& m('goods');
        $stores = $store_mod->find(array(
            'conditions' => 'state = 1',
            'order' => 'add_time DESC',
            'join'  => 'belongs_to_user',
            'limit' => '0,' . $num,
        ));
        foreach ($stores as $key => $store){
            empty($store['store_logo']) && $stores[$key]['store_logo'] = Conf::get('default_store_logo');
            $stores[$key]['goods_count'] = $goods_mod->get_count_of_store($store['store_id']);
        }

        return $stores;
    }

          /* ȡ���Ƽ����� */
    function _recommended_stores($num)
    {
        $store_mod =& m('store');
        $goods_mod =& m('goods');
        $stores = $store_mod->find(array(
            'conditions'    => 'recommended=1 AND state = 1',
            'order'         => 'sort_order',
            'join'          => 'belongs_to_user',
            'limit'         => '0,' . $num,
        ));
        foreach ($stores as $key => $store){
            empty($store['store_logo']) && $stores[$key]['store_logo'] = Conf::get('default_store_logo');
            $stores[$key]['goods_count'] = $goods_mod->get_count_of_store($store['store_id']);
        }
        return $stores;
    }
}

?>