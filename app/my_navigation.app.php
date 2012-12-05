<?php

/**
 *    �������������
 *
 *    @author    Garbin
 *    @usage    none
 */
class My_navigationApp extends StoreadminbaseApp
{
    var $_uploadedfile_mod;

    function __construct()
    {
        $this->My_navigationApp();
    }

    function My_navigationApp()
    {
        parent::__construct();
        $this->_uploadedfile_mod = &m('uploadedfile');
    }

    function index()
    {
        $conditions = $this->_get_query_conditions(array(array(
                'field' => 'title',         //�������ֶ�title
                'equal' => 'LIKE',          //�ȼ۹�ϵ,������LIKE, =, <, >, <>
            ),
        ));

        /* ȡ���б����� */
        $model_article =& m('article');
        $page   =   $this->_get_page(10);    //��ȡ��ҳ��Ϣ
        $articles     = $model_article->find(array(
            'conditions'    => 'store_id = ' . $this->visitor->get('manage_store') . $conditions . ' AND cate_id=' . STORE_NAV,
            'order'         => 'sort_order, article_id ASC',
            'limit'         => $page['limit'],  //��ȡ��ǰҳ������
            'count'         => true
        ));
        $page['item_count'] = $model_article->getCount();   //��ȡͳ�Ƶ�����
        $this->assign('navigations', $articles);

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('my_navigation'), 'index.php?app=my_navigation',
                         LANG::get('navigation_list'));

        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('my_navigation');

        /* ��ǰ�����Ӳ˵� */
        $this->_curmenu('navigation_list');
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'mlselection.js',
                    'attr' =>'',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                 array(
                    'path' => 'utils.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
                ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->_format_page($page);

        $this->assign('filtered', $conditions? 1 : 0); //�Ƿ��в�ѯ����
        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_navigation'));
        header("Content-Type:text/html;charset=" . CHARSET);
        $this->display('storeadmin.mynavigation.index.html');
    }

    /**
     *    ��ӵ�ַ
     *
     *    @author    Garbin
     *    @return    void
     */
    function add()
    {
        if (!IS_POST)
        {
            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                             LANG::get('my_navigation'), 'index.php?app=my_navigation',
                             LANG::get('add_navigation'));

            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('my_navigation');

            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('add_navigation');

            /* ����ģ��δ����ĸ��� */
            $files_belong_article = $this->_uploadedfile_mod->find(array(
                'conditions' => 'store_id = ' . $this->visitor->get('manage_store') . ' AND belong = ' . BELONG_ARTICLE . ' AND item_id = 0',
                'fields' => 'this.file_id, this.file_name, this.file_path',
            ));

            //�ϴ�ͼƬ�Ǵ���iframe�Ĳ���
            $this->assign("id", 0);
            $this->assign("belong", BELONG_ARTICLE);

            extract($this->_get_theme());
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'nav_content',
                'ext_js' => false,
                'content_css' => SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}" . '/shop.css', // for preview
            )));
            
           /* �༭��ͼƬ�����ϴ��� */
            $this->assign('editor_upload', $this->_build_upload(array(
                'obj' => 'EDITOR_SWFU',
                'belong' => BELONG_ARTICLE,
                'item_id' => 0,
                'button_text' => Lang::get('bat_upload'),
                'button_id' => 'editor_upload_button',
                'progress_id' => 'editor_upload_progress',
                'upload_url' => 'index.php?app=swfupload',
                'if_multirow' => 1,
                'ext_js' => false,
                'ext_css' => false,
            )));
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->_assign_form();
            $this->assign('files_belong_article', $files_belong_article);
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->display('my_navigation.form.html');
        }
        else
        {
            $data = array(
                'store_id'      => $this->visitor->get('manage_store'),
                'title'         => $_POST['title'],
                'if_show'       => $_POST['if_show'],
                'sort_order'    => $_POST['sort_order'],
                'content'       => $_POST['nav_content'],
                'cate_id'       => STORE_NAV,
                'add_time'      =>   gmtime(),
            );
            $model_article =& m('article');
            if (!($article_id = $model_article->add($data)))
            {
                $this->pop_warning($model_article->get_error());

                return;
            }
            else
            {
                /* ������� */
                $this->_clear_cache();
            }

            /* ������� */
            if (isset($_POST['file_id']))
            {
                foreach ($_POST['file_id'] as $file_id)
                {
                    $this->_uploadedfile_mod->edit($file_id, array('item_id' => $article_id));
                }
            }

            $this->pop_warning('ok');
        }
    }
    function edit()
    {
        $nav_id = empty($_GET['nav_id']) ? 0 : intval($_GET['nav_id']);
        if (!$nav_id)
        {
            echo Lang::get('no_such_navigation');

            return;
        }
        if (!IS_POST)
        {
            $model_article =& m('article');
            $find_data     = $model_article->find("article_id = {$nav_id} AND store_id=" . $this->visitor->get('manage_store'));
            if (empty($find_data))
            {
                echo Lang::get('no_such_navigation');

                return;
            }
            $navigation = current($find_data);

            /* ��ǰ�ĸ��� */
            $files_belong_article = $this->_uploadedfile_mod->find(array(
                'fields' => 'this.file_id, this.file_name, this.file_path',
                'conditions' => 'store_id = ' . $this->visitor->get('manage_store') . ' AND belong = ' . BELONG_ARTICLE . ' AND item_id=' . $nav_id,
            ));

            //�ϴ�ͼƬ�Ǵ���iframe�Ĳ���
            $this->assign("id", $nav_id);
            $this->assign("belong", BELONG_ARTICLE);

            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                             LANG::get('my_navigation'), 'index.php?app=my_navigation',
                             LANG::get('edit_navigation'));

            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('my_navigation');

            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('edit_navigation');
            /*$this->import_resource(array(
                 'script' => 'jquery.plugins/jquery.validate.js,change_upload.js'
            ));*/
            $this->_assign_form();
            $this->assign('files_belong_article', $files_belong_article);
            
            extract($this->_get_theme());
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'nav_content',
                'ext_js' => false,
                'content_css' => SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}" . '/shop.css', // for preview
            )));
            
           /* �༭��ͼƬ�����ϴ��� */
            $this->assign('editor_upload', $this->_build_upload(array(
                'obj' => 'EDITOR_SWFU',
                'belong' => BELONG_ARTICLE,
                'item_id' => 0,
                'button_text' => Lang::get('bat_upload'),
                'button_id' => 'editor_upload_button',
                'progress_id' => 'editor_upload_progress',
                'upload_url' => 'index.php?app=swfupload',
                'if_multirow' => 1,
                'ext_js' => false,
                'ext_css' => false,
            )));
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('navigation', $navigation);
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->display('my_navigation.form.html');
        }
        else
        {
            $data = array(
                'title'         => $_POST['title'],
                'if_show'       => $_POST['if_show'],
                'content'       => $_POST['nav_content'],
                'sort_order'    => $_POST['sort_order'],
            );

            $model_article =& m('article');
            $rows = $model_article->edit("article_id = {$nav_id} AND store_id=" . $this->visitor->get('user_id'), $data);
            if ($model_article->has_error())
            {
                //$this->show_warning($model_article->get_error());
                $this->pop_warning($model_article->get_error());
                return;
            }
            /* ������� */
            $rows && $this->_clear_cache();
            $this->pop_warning('ok', 'my_navigation_edit');
        }
    }
    function drop()
    {
        $nav_id = isset($_GET['nav_id']) ? trim($_GET['nav_id']) : 0;
        if (!$nav_id)
        {
            $this->show_storeadmin_warning('no_such_navigation');

            return;
        }
        $ids = explode(',', $nav_id);//��ȡһ������array(1, 2, 3)������
        $model_article  =& m('article');
        $drop_count = $model_article->drop("store_id = " . $this->visitor->get('manage_store') . " AND article_id " . db_create_in($ids));
        if (!$drop_count)
        {
            /* û�п�ɾ������ */
            $this->show_storeadmin_warning('no_such_navigation');

            return;
        }

        if ($model_article->has_error())    //������
        {
            $this->show_storeadmin_warning($model_article->get_error());

            return;
        }
        else
        {
            /* ������� */
            $this->_clear_cache();
        }

        $this->show_storeadmin_message('drop_navigation_successed');
    }

    /**
     *    �����˵�
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name'  => 'navigation_list',
                'url'   => 'index.php?app=my_navigation',
            ),
        );
        return $menus;
    }

    function _assign_form()
    {
        /* ��ʾ������ */
        $yes_or_no = array(
            1 => Lang::get('yes'),
            0 => Lang::get('no'),
        );
        /*����ʼֵ*/
        $navigation = array(
            'if_show'       => '1',
            'sort_order'    => '255',
        );
        $this->assign('navigation' , $navigation);
        $this->assign('yes_or_no', $yes_or_no);
    }

        /* �첽ɾ������ */
    function drop_uploadedfile()
    {
        $file_id = isset($_GET['file_id']) ? intval($_GET['file_id']) : 0;
        $file = $this->_uploadedfile_mod->get($file_id);
        if ($file_id && $file['store_id'] == $this->visitor->get('manage_store') && $this->_uploadedfile_mod->drop($file_id))
        {
            $this->json_result('drop_ok');
            return;
        }
        else
        {
            $this->json_error('drop_error');
            return;
        }
    }
    
    /* ������� */
    function _clear_cache()
    {        
        $cache_server =& cache_server();
        $cache_server->delete('function_get_store_data_' . $this->visitor->get('manage_store'));
    }

}

?>