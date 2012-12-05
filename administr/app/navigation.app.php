<?php

/**
 *    ҳ�浼��������
 *
 *    @author    Hyber
 *    @usage    none
 */
class NavigationApp extends BackendApp
{
    var $_navi_mod;

    function __construct()
    {
        $this->NavigationApp();
    }

    function NavigationApp()
    {
        parent::BackendApp();

        $this->_navi_mod =& m('navigation');
    }

    /**
     *    ҳ�浼������
     *
     *    @author    Hyber
     *    @return    void
     */
    function index()
    {
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'title',         //�������ֶ�title
                'equal' => 'LIKE',          //�ȼ۹�ϵ,������LIKE, =, <, >, <>
                'assoc' => 'AND',           //��ϵ����,������AND, OR
                'name'  => 'title',         //GET��ֵ�ķ��ʼ���
                'type'  => 'string',        //GET��ֵ������
            ),
            array(
                'field' => 'type',
            ),
        ));
        $page   =   $this->_get_page(10);   //��ȡ��ҳ��Ϣ
        $navigations=$this->_navi_mod->find(array(
        'conditions'  => '1=1' . $conditions,
        'limit'   =>$page['limit'],
        'order'   => 'type ASC,sort_order ASC',
        'count'   => true   //����ͳ��
        ));
        $page['item_count']=$this->_navi_mod->getCount();   //��ȡͳ������
        $open_new = array(
           '0' => Lang::get('no'),
           '1' => Lang::get('yes'),
        );
        $types = array(
            'header' => Lang::get('header'),
            'middle' => Lang::get('middle'),
            'footer' => Lang::get('footer'),
        );
        foreach ($navigations as $key => $navigation){
            $navigations[$key]['open_new'] = $open_new[$navigation['open_new']];
            $navigations[$key]['type'] = $types[$navigation['type']];
        }
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //�Ƿ��в�ѯ����
        $this->_assign_form();
        $this->assign('page_info', $page);   //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->import_resource(array('script' => 'inline_edit.js'));
        $this->assign('navigations', $navigations);
        $this->display('navigation.index.html');
    }
     /**
     *    ����ҳ�浼��
     *
     *    @author    Hyber
     *    @return    void
     */
    function add()
    {
        if (!IS_POST)
        {
            /* ��ʾ������ */
            $navigation = array('type' => 'header', 'sort_order' => 255, 'link' => 'http://','icotype' => '0');
            $this->_assign_form();
            $this->import_resource(array('script' => 'mlselection.js,jquery.plugins/jquery.validate.js'));
       		$this->assign('gcategory_options', $this->_get_gcategory_options()); //��Ʒ������
            $this->assign('acategory_options', $this->_get_acategory_options()); //���·�����
            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js,mlselection.js'));
            $this->assign('navigation', $navigation);
            $this->display('navigation.form.html');
        }
        else
        {
            $data = array();
            /* ����������������Ʒ�����·���ʱ����cate_idƴ������ */
            $_POST['gcategory_cate_id'] && $_POST['link'] = 'index.php?app=search&cate_id='. $_POST['gcategory_cate_id'];
            $_POST['acategory_cate_id'] && $_POST['link'] = 'index.php?app=article&cate_id='. $_POST['acategory_cate_id'];
			$icotype=empty($_POST['icotype']) ? 0 : intval($_POST['icotype']);
            $data['title']      =   $_POST['title'];
            $data['type']      =   $_POST['type'];
            $data['link']      =   $_POST['link'];
            $data['open_new']      =   $_POST['open_new'];
            $data['sort_order'] =   $_POST['sort_order'];
            $data['icotype'] = $icotype;

            if (!$nav_id = $this->_navi_mod->add($data))  //��ȡnav_id
            {
                $this->show_warning($this->_navi_mod->get_error());

                return;
            }

            $this->_clear_cache();
            $this->show_message('add_navigation_successed',
                'back_list',    'index.php?app=navigation',
                'continue_add', 'index.php?app=navigation&amp;act=add'
            );
        }
    }
     /**
     *    �༭��ƷƷ��
     *
     *    @author    Hyber
     *    @return    void
     */
    function edit()
    {
        $nav_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$nav_id)
        {
            $this->show_warning('no_such_navigation');
            return;
        }
         if (!IS_POST)
        {
            $find_data     = $this->_navi_mod->find($nav_id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_navigation');

                return;
            }
            $navigation    =   current($find_data);
            //$navigation['link'] = !preg_match("/^http(s)?:\/\//i", $navigation['link']) ? SITE_URL . '/' . $navigation['link'] : $navigation['link'];
            $this->_assign_form();
            $this->assign('gcategory_options', $this->_get_gcategory_options()); //��Ʒ������
            $this->assign('acategory_options', $this->_get_acategory_options()); //���·�����
            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js,mlselection.js'));
            $this->assign('navigation', $navigation);
            $this->display('navigation.form.html');
        }
        else
        {
            $data = array();
            /* ����������������Ʒ�����·���ʱ����cate_idƴ������ */
            $_POST['gcategory_cate_id'] && $_POST['link'] = 'index.php?app=search&cate_id='. $_POST['gcategory_cate_id'];
            $_POST['acategory_cate_id'] && $_POST['link'] = 'index.php?app=article&cate_id='. $_POST['acategory_cate_id'];
			$icotype=empty($_POST['icotype']) ? 0 : intval($_POST['icotype']); 	
            $data['title']      =   $_POST['title'];
            $data['type']      =   $_POST['type'];
            $data['link']      =   $_POST['link'];
            $data['open_new']      =   $_POST['open_new'];
            $data['sort_order'] =   $_POST['sort_order'];
            $data['icotype'] = $icotype;          
            $rows=$this->_navi_mod->edit($nav_id, $data);
            if ($this->_navi_mod->has_error())
            {
                $this->show_warning($this->_navi_mod->get_error());

                return;
            }

            $this->_clear_cache();
            $this->show_message('edit_navigation_successed',
                'back_list',        'index.php?app=navigation',
                'edit_again',    'index.php?app=navigation&amp;act=edit&amp;id=' . $nav_id);
        }
    }

     //�첽�޸�����
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('title', 'sort_order')))
       {
           $data[$column] = $value;
           $this->_navi_mod->edit($id, $data);
           if(!$this->_navi_mod->has_error())
           {
               $this->_clear_cache();
               echo ecm_json_encode(true);
           }
       }
       else
       {
           return ;
       }
       return ;
   }

    function drop()
    {
        $nav_ids = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$nav_ids)
        {
            $this->show_warning('no_such_navigation');

            return;
        }
        $nav_ids=explode(',',$nav_ids);
        if (!$this->_navi_mod->drop($nav_ids))    //ɾ��
        {
            $this->show_warning($this->_navi_mod->get_error());

            return;
        }

        $this->_clear_cache();
        $this->show_message('drop_navigation_successed');
    }

    /* �������� */
    function update_order()
    {
        if (empty($_GET['id']))
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        $ids = explode(',', $_GET['id']);
        $sort_orders = explode(',', $_GET['sort_order']);
        foreach ($ids as $key => $id)
        {
            $this->_navi_mod->edit($id, array('sort_order' => $sort_orders[$key]));
        }

        $this->show_message('update_order_ok');
    }

            /* ���첢������ */
    function &_tree($acategories)
    {
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($acategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree;
    }
        /* ȡ���������·������� */
    function _get_acategory_options()
    {
        $mod_acategory = &m('acategory');
        $acategorys = $mod_acategory->get_list();

        /* ȥ��ϵͳ�������·��� */
        $system_cate_id = $mod_acategory->get_ACC(ACC_SYSTEM);
        unset($acategorys[$system_cate_id]);

        $tree =& $this->_tree($acategorys);
        return $tree->getOptions();
    }
        /* ȡ���̳ǵ���Ʒ�������� */
    function _get_gcategory_options($parent_id = 0)
    {
        $mod_gcategory = &bm('gcategory');
        $gcategories = $mod_gcategory->get_list($parent_id, true);
        foreach ($gcategories as $gcategory)
        {
            $res[$gcategory['cate_id']] = $gcategory['cate_name'];
        }
        return $res;
    }

    /* ����ֵ */
    function _assign_form()
    {
        $type = array(
            'header' => Lang::get('header'),
            'middle' => Lang::get('middle'),
            'footer' => Lang::get('footer'),
        );
        $open_new = array(
           '0' => Lang::get('no'),
           '1' => Lang::get('yes'),
        );
        $this->assign('type', $type);
        $this->assign('open_new', $open_new);
    }
}

?>
