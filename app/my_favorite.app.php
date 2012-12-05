<?php

/**
 *    �ҵ��ղؿ�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class My_favoriteApp extends MemberbaseApp
{
    /**
     *    �ղ��б�
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        $type = empty($_GET['type'])    ? 'goods' : trim($_GET['type']);
        /* ��ǰ�û�������Ϣ*/
        $this->_get_user_info();
        if ($type == 'goods')
        {
            $this->_list_collect_goods();
        }
        elseif ($type == 'store')
        {
            /* �ղص��� */
            $this->_list_collect_store();
        }
    }

    /**
     *    �ղ���Ŀ
     *
     *    @author    Garbin
     *    @return    void
     */
    function add()
    {
        $type = empty($_GET['type'])    ? 'goods' : trim($_GET['type']);
        $item_id = empty($_GET['item_id'])  ? 0 : intval($_GET['item_id']);
        $keyword = empty($_GET['keyword'])  ? '' : trim($_GET['keyword']);
        /* ��ǰ�û�������Ϣ*/
        $this->_get_user_info();
        if (!$item_id)
        {
            $this->show_warning('no_such_collect_item');

            return;
        }

        if ($type == 'goods')
        {
            $this->_add_collect_goods($item_id, $keyword);
        }
        elseif ($type == 'store')
        {
            $this->_add_collect_store($item_id, $keyword);
        }
    }
    /**
     *    ɾ���ղص���Ŀ
     *
     *    @author    Garbin
     *    @return    void
     */
    function drop()
    {
        $type = empty($_GET['type'])    ? 'goods' : trim($_GET['type']);
        $item_id = empty($_GET['item_id'])  ? 0 : trim($_GET['item_id']);
        if (!$item_id)
        {
            $this->show_warning('no_such_collect_item');

            return;
        }
        if ($type == 'goods')
        {
            $this->_drop_collect_goods($item_id);
        }
        elseif ($type == 'store')
        {
            $this->_drop_collect_store($item_id);
        }
    }

    /**
     *    �б��ղص���Ʒ
     *
     *    @author    Garbin
     *    @return    void
     */
    function _list_collect_goods()
    {
        $conditions = $this->_get_query_conditions(array(array(
                'field' => 'goods_name',         //�������ֶ�title
                'equal' => 'LIKE',          //�ȼ۹�ϵ,������LIKE, =, <, >, <>
            ),
        ));
        $model_goods =& m('goods');
        $page   =   $this->_get_page();    //��ȡ��ҳ��Ϣ
        $collect_goods = $model_goods->getAll("select g.*,s.store_name,s.store_id,collect.item_id,collect.add_time,gs.price,gs.spec_id from pa_goods g left join pa_store_goods
        									 sg on g.goods_id = sg.goods_id left join pa_store s on sg.store_id = s.store_id left join pa_goods_spec gs on g.default_spec = gs.spec_id
        									 left join pa_collect collect on sg.gs_id = collect.item_id and collect.type='goods' where {$conditions} collect.user_id = {$this->visitor->get('user_id')} 
        									 ORDER BY collect.add_time DESC LIMIT 0,10");
        //        $collect_goods = $model_goods->find(array(
//            'join'  => 'be_collect,belongs_to_store,has_default_spec,',
//            'fields'=> 'this.*,store.store_name,store.store_id,collect.add_time,goodsspec.price,goodsspec.spec_id',//store.store_name,store.store_id,
//            'conditions' => 'collect.user_id = ' . $this->visitor->get('user_id') . $conditions,
//            'count' => true,
//            'order' => 'collect.add_time DESC',
//            'limit' => $page['limit'],
//        ));
//        
        foreach ($collect_goods as $key => $goods)
        {
        	$collect_goods[$key]['default_image'] = IMAGE_URL.$goods['default_image'];
          //  empty($goods['default_image']) && $collect_goods[$key]['default_image'] = Conf::get('default_goods_image');
        }
        $page['item_count'] = $model_goods->getCount();   //��ȡͳ�Ƶ�����
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //�Ƿ��в�ѯ����
        $this->assign('collect_goods', $collect_goods);
        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                            LANG::get('my_favorite'), 'index.php?app=my_favorite',
                            LANG::get('collect_goods'));

        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));

        //��ǰ�û����Ĳ˵���
        $this->_curitem('my_favorite');

        $this->_curmenu('collect_goods');
        /* ��ǰ�û�������Ϣ*/
        $this->_get_user_info();
        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('collect_goods'));
        $this->display('my_favorite.goods.index.html');
    }

    /**
     *    �б��ղصĵ���
     *
     *    @author    Garbin
     *    @return    void
     */
    function _list_collect_store()
    {
        $conditions = $this->_get_query_conditions(array(array(
                'field' => 'store_name',         //�������ֶ�title
                'equal' => 'LIKE',          //�ȼ۹�ϵ,������LIKE, =, <, >, <>
            ),
        ));
        $model_store =& m('store');
        $page   =   $this->_get_page();    //��ȡ��ҳ��Ϣ
        $page['item_count'] = $model_store->getCount();   //��ȡͳ�Ƶ�����
        $collect_store = $model_store->getAll("select s.*,member.user_name,collect.add_time from pa_store s left join pa_collect collect on s.store_id = 
        										collect.item_id and collect.type='store' left join pa_member member on s.store_id = member.user_id where 
        										{$conditions} collect.user_id = {$this->visitor->get('user_id')} ORDER BY collect.add_time limit 0,10");
//        $collect_store = $model_store->find(array(
//            'join'  => 'be_collect,belongs_to_user',
//            'fields'=> 'this.*,member.user_name,collect.add_time',
//            'conditions' => 'collect.user_id = ' . $this->visitor->get('user_id') . $conditions,
//            'count' => true,
//            'order' => 'collect.add_time DESC',
//            'limit' => $page['limit'],
//        ));
        $this->_format_page($page);
        $step = intval(Conf::get('upgrade_required'));
        $step < 1 && $step = 5;
        foreach ($collect_store as $key => $store)
        {
        	$collect_store[$key]['store_logo'] = IMAGE_URL.$store['store_logo'];
            empty($store['store_logo']) && $collect_store[$key]['store_logo'] = Conf::get('default_store_logo');
            $collect_store[$key]['credit_image'] = $this->_view->res_base . '/images/' . $model_store->compute_credit($store['credit_value'], $step);
        }
        $this->assign('collect_store', $collect_store);

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                        LANG::get('my_favorite'), 'index.php?app=my_favorite',
                        LANG::get('collect_store'));

        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        //��ǰ�û����Ĳ˵���
        $this->_curitem('my_favorite');

        $this->_curmenu('collect_store');
        /* ��ǰ�û�������Ϣ*/
        $this->_get_user_info();
        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->assign('filtered', $conditions? 1 : 0); //�Ƿ��в�ѯ����
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('collect_store'));
        $this->display('my_favorite.store.index.html');
    }

    /**
     *    ɾ���ղص���Ʒ
     *
     *    @author    Garbin
     *    @param     int $item_id
     *    @return    void
     */
    function _drop_collect_goods($item_id)
    {
        $ids = explode(',', $item_id);

        /* ������ҡ�����ƷIDΪ$ids���ղع�ϵ */
        $model_user =& m('member');
        $model_user->unlinkRelation('collect_goods', $this->visitor->get('user_id'), $ids);
        if ($model_user->has_error())
        {
            $this->show_warning($model_user->get_error());

            return;
        }
        $this->show_message('drop_collect_goods_successed');
    }

    /**
     *    ɾ���ղصĵ���
     *
     *    @author    Garbin
     *    @param     int $item_id
     *    @return    void
     */
    function _drop_collect_store($item_id)
    {
        $ids = explode(',', $item_id);

        /* ������ҡ������IDΪ$ids���ղع�ϵ */
        $model_user =& m('member');
        $model_user->unlinkRelation('collect_store', $this->visitor->get('user_id'), $ids);
        if ($model_user->has_error())
        {
            $this->show_warning($model_user->get_error());

            return;
        }
        $this->show_message('drop_collect_store_successed');
    }

    /**
     *    �ղ���Ʒ
     *
     *    @author    Garbin
     *    @param     int    $goods_id
     *    @param     string $keyword
     *    @return    void
     */
    function _add_collect_goods($gs_id, $keyword)
    {
        /* ��֤Ҫ�ղص���Ʒ�Ƿ���� */
        $model_goods =& m('goods');

        $goods_info  = $model_goods->getRow("select * from pa_store_goods sg left join pa_goods g on g.goods_id=sg.goods_id where sg.gs_id=".$gs_id);
        if (empty($goods_info))
        {
            /* ��Ʒ������ */
            $this->json_error('no_such_goods');
            return;
        }
        $model_user =& m('member');
        $model_user->createRelation('collect_goods', $this->visitor->get('user_id'), array(
            $gs_id   =>  array(
                'keyword'   =>  $keyword,
                'add_time'  =>  gmtime(),
            )
        ));

        /* ���±��ղش��� */
        $model_goods->update_collect_count($gs_id);

        $goods_image = $goods_info['default_image'] ? $goods_info['default_image'] : Conf::get('default_goods_image');
        $goods_url  = SITE_URL . '/' . url('app=goods&id=' . $gs_id	);
        $this->send_feed('goods_collected', array(
            'user_id'   => $this->visitor->get('user_id'),
            'user_name'   => $this->visitor->get('user_name'),
            'goods_url'   => $goods_url,
            'goods_name'   => $goods_info['goods_name'],
            'images'    => array(array(
                'url' => SITE_URL . '/' . $goods_image,
                'link' => $goods_url,
            )),
        ));

        /* �ղسɹ� */
        $this->json_result('', 'collect_goods_ok');
    }

    /**
     *    �ղص���
     *
     *    @author    Garbin
     *    @param     int    $store_id
     *    @param     string $keyword
     *    @return    void
     */
    function _add_collect_store($store_id, $keyword)
    {
        /* ��֤Ҫ�ղصĵ����Ƿ���� */
        $model_store =& m('store');
        $store_info  = $model_store->get($store_id);
        if (empty($store_info))
        {
            /* ���̲����� */
            return;
        }
        $model_user =& m('member');
        $model_user->createRelation('collect_store', $this->visitor->get('user_id'), array(
            $store_id   =>  array(
                'keyword'   =>  $keyword,
                'add_time'  =>  gmtime(),
            )
        ));
        $this->send_feed('store_collected', array(
            'user_id'   => $this->visitor->get('user_id'),
            'user_name'   => $this->visitor->get('user_name'),
            'store_url'   => SITE_URL . '/' . url('app=store&id=' . $store_id),
            'store_name'   => $store_info['store_name'],
        ));

        /* �ղسɹ� */
        $this->json_result('', 'collect_store_ok');
    }

    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name'  => 'collect_goods',
                'url'   => 'index.php?app=my_favorite',
            ),
            array(
                'name'  => 'collect_store',
                'url'   => 'index.php?app=my_favorite&amp;type=store',
            ),
        );
        return $menus;
    }
}

?>
