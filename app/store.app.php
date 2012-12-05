<?php

class StoreApp extends StorebaseApp
{
    function index()
    {
        /* ������Ϣ */
        $_GET['act'] = 'index';
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
    	$this->assign("imdir",IMAGE_URL);
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);
        /* ȡ���������� */
        $this->assign('partners', $this->_get_partners($id));    

        /* ȡ���Ƽ���Ʒ */
        $this->assign('recommended_goods', $this->_get_recommended_goods($id));
        $this->assign('new_groupbuy', $this->_get_new_groupbuy($id));

        /* ȡ��������Ʒ */
        $this->assign('new_goods', $this->_get_new_goods($id));
		/*echo "<pre>";
        var_dump($this->_get_new_goods($id));
        echo "</pre>";*/
        
        //��������
		$friend_link_mod = &m('friend_link');
        $fl = $friend_link_mod->find(array(
        							'field' => 'link_id,link_url,show_order,link_name,link_logo,type',
        							'limit' => '8',
        							'conditions' =>'type=1',
        							'order' => 'show_order desc'
        							));
        $this->assign('fl',$fl);
        
        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store', $store['store_name']);

        $this->_config_seo('title', $store['store_name'] . ' - ' . Conf::get('site_title'));
        /* ����seo��Ϣ */
        $this->_config_seo($this->_get_seo_info($store));
        $this->display('store.index.html');
    }

    
    function search()
    {
    	$this->assign("imdir",IMAGE_URL);
    	$temp = 2;
        $_GET['act'] = 'index';
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);
        $gcategory_mod = & m('gcategory');
        $sgcate = $gcategory_mod->getAll('select * from pa_gcategory gy left join pa_category_store cs on cs.cate_id=gy.cate_id where cs.store_id='.$id);
        $this->assign('sgcate',$sgcate);
    	$keyword = $_GET['keyword'];
    	if($keyword)
    	{
    		$temp = 1;
    		$conditions = '1=1';
    		$conditions = "g.goods_name like '%".$keyword."%'"; 	
        /* ������Ϣ */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);
        $goods_mod = &m("goods");
        $data = $goods_mod->getAll("select * from pa_goods g left join pa_store_goods sg on g.goods_id = sg.goods_id where {$conditions} and sg.store_id = {$id}");
        $page = $this->_get_page(16);
        $store = $goods_mod->getAll("select * from pa_goods  g left join pa_store_goods sg on g.goods_id = sg.goods_id where {$conditions} limit ".$page['limit']);
        $page['item_count'] = $goods_mod->getOne("select count(*) from pa_goods  g left join pa_store_goods sg on g.goods_id = sg.goods_id where {$conditions}");       
        $this->_format_page($page);
        $this->assign('temp', $temp); 
        $this->assign('page_info', $page); 
		$this->assign("search_name",$keyword);
		$this->assign("searched_goods",$store);
    	}
    	else
    	{
        /* ������Ϣ */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);
        $goods_mod = &m("goods");
        $data = $goods_mod->getAll("select g.goods_name from pa_goods g left join 
        				pa_store_goods sg on g.goods_id = sg.goods_id where {$conditions} sg.store_id = {$id}");
        $page = $this->_get_page(16);
 		$page['item_count']= $goods_mod->getOne("select count(*) from pa_goods  g left join pa_store_goods sg on g.goods_id = sg.goods_id where sg.store_id=".$id); 		
 		$tmp = $_GET['tmp'];
 		$this->assign("divtmp",$tmp);
 		switch ($tmp)
 		{
 			case 3:
 				$st = $goods_mod->getAll("select * from pa_goods  g left join pa_store_goods sg on g.goods_id = sg.goods_id  where sg.store_id = $id" ." order by g.price desc limit ".$page['limit']);
 				break;
 			case 1:
 				$st = $goods_mod->getAll("select * from pa_goods  g left join pa_store_goods sg on g.goods_id = sg.goods_id left join pa_goods_statistics on pa_goods_statistics.goods_id = g.goods_id where sg.store_id = $id" ." order by pa_goods_statistics.sales  limit ".$page['limit']);
 				break;
 			case 4:
 				$st = $goods_mod->getAll("select * from pa_goods  g left join pa_store_goods sg on g.goods_id = sg.goods_id  where sg.store_id = $id" ." order by g.price ASC limit ".$page['limit']);
 				break;
 			case 2:$st = $goods_mod->getAll("select * from pa_goods  g left join pa_store_goods sg on g.goods_id = sg.goods_id  where sg.store_id = $id" ." order by g.add_time desc limit ".$page['limit']);
 				break;
 			default:$st = $goods_mod->getAll("select * from pa_goods  g left join pa_store_goods sg on g.goods_id = sg.goods_id  where sg.store_id = $id" ." order by g.add_time desc limit ".$page['limit']);
 				break;
 		}
 		$this->_format_page($page);
        $this->assign('temp', $temp); 
        $this->assign('page_info', $page); 
		$this->assign("search_name",$keyword);
		$this->assign("searched_goods",$st);
    	}
		
//        $goods_mod->find(array(
//            'fields'    => 'g.goods_name',
//            'join'      => 'belongs_to_store',
//            'conditions'=> 'store_goods.goods_id = g.goods_id AND g.goods_name like '%".$user_name."%'',
//            'order'     => 'g.goods_id DESC',
//            'limit'     => $page['limit'],
//            'count'     => true));
//        $num = $goods_mod->getCount();
//         $page['item_count'] = $num;
        
//        /* ����������Ʒ */
//        $this->_assign_searched_goods($id);

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store',
            $store['store_name'], 'index.php?app=store&amp;id=' . $store['store_id'],
            LANG::get('goods_list')
        );
        $this->_config_seo('title', Lang::get('goods_list') . ' - ' . $store['store_name']);
        $this->display('store.search.html');
    }

    function groupbuy()
    {
        /* ������Ϣ */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);

        /* �����Ź� */
        empty($_GET['state']) &&  $_GET['state'] = 'on';
        $conditions = '1=1';
        if ($_GET['state'] == 'on')
        {
            $conditions .= ' AND gb.state ='. GROUP_ON .' AND gb.end_time>' . gmtime();
            $search_name = array(
                array(
                    'text'  => Lang::get('group_on')
                ),
                array(
                    'text'  => Lang::get('all_groupbuy'),
                    'url'  => url('app=store&act=groupbuy&state=all&id=' . $id)
                ),
            );
        }
        else if ($_GET['state'] == 'all')
        {
            $conditions .= ' AND gb.state '. db_create_in(array(GROUP_ON,GROUP_END,GROUP_FINISHED));
            $search_name = array(
                array(
                    'text'  => Lang::get('all_groupbuy')
                ),
                array(
                    'text'  => Lang::get('group_on'),
                    'url'  => url('app=store&act=groupbuy&state=on&id=' . $id)
                ),
            );
        }

        $page = $this->_get_page(16);
        $groupbuy_mod = &m('groupbuy');
        $groupbuy_list = $groupbuy_mod->find(array(
            'fields'    => 'goods.default_image, gb.group_name, gb.group_id, gb.spec_price, gb.end_time, gb.state',
            'join'      => 'belong_goods',
            'conditions'=> $conditions . ' AND gb.store_id=' . $id ,
            'order'     => 'group_id DESC',
            'limit'     => $page['limit'],
            'count'     => true
        ));
        $page['item_count'] = $groupbuy_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        if (empty($groupbuy_list))
        {
            $groupbuy_list = array();
        }
        foreach ($groupbuy_list as $key => $_g)
        {
            empty($groupbuy_list[$key]['default_image']) && $groupbuy_list[$key]['default_image'] = Conf::get('default_goods_image');
            $tmp = current(unserialize($_g['spec_price']));
            $groupbuy_list[$key]['price'] = $tmp['price'];
            if ($_g['end_time'] < gmtime())
            {
                $groupbuy_list[$key]['group_state'] = group_state($_g['state']);
            }
            else
            {
                $groupbuy_list[$key]['lefttime'] = lefttime($_g['end_time']);
            }
        }
        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store',
            $store['store_name'], 'index.php?app=store&amp;id=' . $store['store_id'],
            LANG::get('groupbuy_list')
        );

        $this->assign('groupbuy_list', $groupbuy_list);
        $this->assign('search_name', $search_name);
        $this->_config_seo('title', $search_name[0]['text'] . ' - ' . $store['store_name']);
        $this->display('store.groupbuy.html');
    }

    function article()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $article = $this->_get_article($id);
        if (!$article)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->assign('article', $article);

        /* ������Ϣ */
        $this->set_store($article['store_id']);
        $store = $this->get_store_data();
        $this->assign('store', $store);

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store',
            $store['store_name'], 'index.php?app=store&amp;id=' . $store['store_id'],
            $article['title']
        );

        $this->_config_seo('title', $article['title'] . ' - ' . $store['store_name']);
        $this->display('store.article.html');
    }

    /* �������� */
    function credit()
    {
        /* ������Ϣ */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);
        /* ȡ�����۹�����Ʒ */
        if (!empty($_GET['eval']) && in_array($_GET['eval'], array(1,2,3)))
        {
            $conditions = "AND evaluation = '{$_GET['eval']}'";
        }
        else
        {
            $conditions = "";
            $_GET['eval'] = '';
        }
        $page = $this->_get_page(10);
        $order_goods_mod =& m('ordergoods');
        $goods_list = $order_goods_mod->find(array(
            'conditions' => "seller_id = '$id' AND evaluation_status = 1 AND is_valid = 1 " . $conditions,
            'join'       => 'belongs_to_order',
            'fields'     => 'buyer_id, buyer_name, anonymous, evaluation_time, goods_id, goods_name, specification, price, quantity, goods_image, evaluation, comment',
            'order'      => 'evaluation_time desc',
            'limit'      => $page['limit'],
            'count'      => true,
        ));
        $this->assign('goods_list', $goods_list);

        $page['item_count'] = $order_goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);

        /* ��ʱ��ͳ�� */
        $stats = array();
        for ($i = 0; $i <= 3; $i++)
        {
            $stats[$i]['in_a_week']        = 0;
            $stats[$i]['in_a_month']       = 0;
            $stats[$i]['in_six_month']     = 0;
            $stats[$i]['six_month_before'] = 0;
            $stats[$i]['total']            = 0;
        }

        $goods_list = $order_goods_mod->find(array(
            'conditions' => "seller_id = '$id' AND evaluation_status = 1 AND is_valid = 1 ",
            'join'       => 'belongs_to_order',
            'fields'     => 'evaluation_time, evaluation',
        ));
        foreach ($goods_list as $goods)
        {
            $eval = $goods['evaluation'];
            $stats[$eval]['total']++;
            $stats[0]['total']++;

            $days = (gmtime() - $goods['evaluation_time']) / (24 * 3600);
            if ($days <= 7)
            {
                $stats[$eval]['in_a_week']++;
                $stats[0]['in_a_week']++;
            }
            if ($days <= 30)
            {
                $stats[$eval]['in_a_month']++;
                $stats[0]['in_a_month']++;
            }
            if ($days <= 180)
            {
                $stats[$eval]['in_six_month']++;
                $stats[0]['in_six_month']++;
            }
            if ($days > 180)
            {
                $stats[$eval]['six_month_before']++;
                $stats[0]['six_month_before']++;
            }
        }
        $this->assign('stats', $stats);

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store',
            $store['store_name'], 'index.php?app=store&amp;id=' . $store['store_id'],
            LANG::get('credit_evaluation')
        );

        $this->_config_seo('title', Lang::get('credit_evaluation') . ' - ' . $store['store_name']);
        $this->display('store.credit.html');
    }

    /* ȡ���������� */
    function _get_partners($id)
    {
        $partner_mod =& m('partner');
        return $partner_mod->find(array(
            'conditions' => "store_id = '$id'",
            'order' => 'sort_order',
        ));
    }

    /* ȡ���Ƽ���Ʒ */
    //�����Ƽ� 
    function _get_recommended_goods($id, $num = 12)
    {
    	//����ΪƷ���̳�
		//������Ʒ��ӦΪƷ����Ʒ
		//$area_type = 'brandmall';
        $goods_mod =& m('goods');
        $goods_list = $goods_mod->find(array(
        	'join'		 =>'belongs_to_store',
            'conditions' => "store_goods.store_id = {$id}",
            'fields'     => 'goods.goods_name,goods.default_image,goods.price,goods.credit,store_goods.gs_id',
            'limit'      => $num,
        ));
        foreach ($goods_list as $key => $goods)
        {
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
        }
        return $goods_list;
    }

    function _get_new_groupbuy($id, $num = 12)
    {
        $model_groupbuy =& m('groupbuy');
        $groupbuy_list = $model_groupbuy->find(array(
            'fields'    => 'goods.default_image, this.group_name, this.group_id, this.spec_price, this.end_time',
            'join'      => 'belong_goods',
            'conditions'=> $model_groupbuy->getRealFields('this.state=' . GROUP_ON . ' AND this.store_id=' . $id . ' AND end_time>'. gmtime()),
            'order'     => 'group_id DESC',
            'limit'     => $num
        ));
        if (empty($groupbuy_list))
        {
            $groupbuy_list = array();
        }
        foreach ($groupbuy_list as $key => $_g)
        {
            empty($groupbuy_list[$key]['default_image']) && $groupbuy_list[$key]['default_image'] = Conf::get('default_goods_image');
            $tmp = current(unserialize($_g['spec_price']));
            $groupbuy_list[$key]['price'] = $tmp['price'];
            $groupbuy_list[$key]['lefttime'] = lefttime($_g['end_time']);
        }

        return $groupbuy_list;
    }

    /* ȡ��������Ʒ */
    function _get_new_goods($id, $num = 12)
    {
    	//����ΪƷ���̳�
		//������Ʒ��ӦΪƷ����Ʒ
		//$area_type = 'brandmall';
        $goods_mod =& m('goods');
        $goods_list = $goods_mod->find(array(
        	'join'		 =>'belongs_to_store',
            'conditions' => "store_goods.store_id = {$id}",
            'fields'     => 'goods.goods_name,goods.default_image,goods.price,goods.credit,store_goods.gs_id',
            'limit'      => $num,
            'order'      => 'add_time desc',
        ));
        foreach ($goods_list as $key => $goods)
        {
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
        }
        return $goods_list;
    }

    /* �������Ľ�� */
    function _assign_searched_goods($id)
    {
        $goods_mod =& bm('goods', array('_store_id' => $id));
        $search_name = LANG::get('all_goods');

        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'goods_name',
                'name'  => 'keyword',
                'equal' => 'like',
            ),
        ));
        if ($conditions)
        {
            $search_name = sprintf(LANG::get('goods_include'), $_GET['keyword']);
            $sgcate_id   = 0;
        }
        else
        {
            $sgcate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        }

        if ($sgcate_id > 0)
        {
            $gcategory_mod =& bm('gcategory', array('_store_id' => $id));
            $sgcate = $gcategory_mod->get_info($sgcate_id);
            $search_name = $sgcate['cate_name'];

            $sgcate_ids = $gcategory_mod->get_descendant_ids($sgcate_id);
        }
        else
        {
            $sgcate_ids = array();
        }

        /* ����ʽ */
        $orders = array(
            'add_time desc' => LANG::get('add_time_desc'),
            'price asc' => LANG::get('price_asc'),
            'price desc' => LANG::get('price_desc'),
        );
        $this->assign('orders', $orders);

        $page = $this->_get_page(16);
        $goods_list = $goods_mod->get_list(array(
            'conditions' => 'closed = 0 AND if_show = 1' . $conditions,
            'count' => true,
            'order' => empty($_GET['order']) || !isset($orders[$_GET['order']]) ? 'add_time desc' : $_GET['order'],
            'limit' => $page['limit'],
        ), $sgcate_ids);
        foreach ($goods_list as $key => $goods)
        {
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
        }
        $this->assign('searched_goods', $goods_list);

        $page['item_count'] = $goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);

        $this->assign('search_name', $search_name);
    }

    /**
     * ȡ��������Ϣ
     */
    function _get_article($id)
    {
        $article_mod =& m('article');
        return $article_mod->get_info($id);
    }
    
    function _get_seo_info($data)
    {
        $seo_info = $keywords = array();
        $seo_info['title'] = $data['store_name'] . ' - ' . Conf::get('site_title');        
        $keywords = array(
            str_replace("\t", ' ', $data['region_name']),
            $data['store_name'],
        );
        //$seo_info['keywords'] = implode(',', array_merge($keywords, $data['tags']));
        $seo_info['keywords'] = implode(',', $keywords);
        $seo_info['description'] = sub_str(strip_tags($data['description']), 10, true);
        return $seo_info;
    }
}

?>
