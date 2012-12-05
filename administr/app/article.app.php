<?php

/**
 *    ���¹��������
 *
 *    @author    Hyber
 *    @usage    none
 */
class ArticleApp extends BackendApp
{
    var $_article_mod;
    var $_uploadedfile_mod;

    function __construct()
    {
        $this->ArticleApp();
    }

    function ArticleApp()
    {
        parent::BackendApp();

        $this->_article_mod =& m('article');
        $this->_uploadedfile_mod = &m('uploadedfile');
    }

    /**
     *    ��������
     *
     *    @author    Hyber
     *    @return    void
     */
    function index()
    {
        /* ����cate_id */
        $cate_id = !empty($_GET['cate_id'])? intval($_GET['cate_id']) : 0;
        if ($cate_id > 0) //ȡ�ø÷��༰�ӷ���cate_id
        {
            $acategory_mod = & m('acategory');
            $cate_ids = $acategory_mod->get_descendant($cate_id);
            if (!$cate_ids)
            {
                $this->show_warning('no_this_acategory');
                return;
            }
        }
        $conditions='';
        !empty($cate_ids)&& $conditions = ' AND article.cate_id ' . db_create_in($cate_ids);
        $conditions .= $this->_get_query_conditions(array(
            array(
                'field' => 'title',         //�������ֶ�title
                'equal' => 'LIKE',          //�ȼ۹�ϵ,������LIKE, =, <, >, <>
                'assoc' => 'AND',           //��ϵ����,������AND, OR
                'name'  => 'title',         //GET��ֵ�ķ��ʼ���
                'type'  => 'string',        //GET��ֵ������
            ),
        ));
        $page   =   $this->_get_page(10);   //��ȡ��ҳ��Ϣ
        $articles=$this->_article_mod->find(array(
        'fields'   => 'article.*,acategory.cate_name',
        'conditions'  => 'store_id=0' . $conditions,
        'limit'   => $page['limit'],
        'join'    => 'belongs_to_acategory',
        'order'   => 'article.sort_order ASC,article.add_time DESC', //����ӱ���
        'count'   => true   //����ͳ��
        ));    //�ҳ����е�����
        $page['item_count']=$this->_article_mod->getCount();   //��ȡͳ������
        $if_show = array(
            0 => Lang::get('no'),
            1 => Lang::get('yes'),
        );
        foreach ($articles as $key =>$article){
            $articles[$key]['if_show']  = $if_show[$article['if_show']]; //�Ƿ���ʾ
        }
        $this->_format_page($page);
        $this->import_resource(array('script' => 'inline_edit.js'));
        $this->assign('filtered', $conditions? 1 : 0); //�Ƿ��в�ѯ����
        $this->assign('parents', $this->_get_options()); //������
        $this->assign('page_info', $page);   //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->assign('articles', $articles);
        $this->display('article.index.html');
    }
     /**
     *    ��������
     *
     *    @author    Hyber
     *    @return    void
     */
    function add()
    {
        if (!IS_POST)
        {
            /* ��ʾ������ */
            $cate_id = isset ($_GET['cate_id']) ? intval($_GET['cate_id']) : 0;//������ĳ����������������
            $article = array('cate_id' => $cate_id, 'sort_order' => 255, 'link' => '', 'if_show' => 1);

            /* ����ģ��δ����ĸ��� */
            $files_belong_article = $this->_uploadedfile_mod->find(array(
                'conditions' => 'store_id = 0 AND belong = ' . BELONG_ARTICLE . ' AND item_id = 0',
                'fields' => 'this.file_id, this.file_name, this.file_path',
                'order' => 'add_time DESC'
            ));

            $this->assign("id", 0);
            $this->assign('belong', BELONG_ARTICLE);

            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js,change_upload.js'));
            $this->assign('article', $article);
            $this->assign('files_belong_article', $files_belong_article);
            $this->assign('parents', $this->_get_options()); //������
            
            $template_name = $this->_get_template_name();
            $style_name    = $this->_get_style_name();
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'content',
                'content_css' => SITE_URL . "/themes/mall/{$template_name}/styles/{$style_name}/css/ecmall.css"
            )));
            
            $this->assign('build_upload', $this->_build_upload(array('belong' => BELONG_ARTICLE, 'item_id' => 0))); // ����swfupload�ϴ����
            $this->display('article.form.html');
        }
        else
        {
        	$dat=$this->_upload_files();
            $data = array();
            $data['title']      =   $_POST['title'];
            $data['cate_id']    =   $_POST['cate_id'];
            $data['link']       =   $_POST['link'] == 'http://' ? '' : $_POST['link'];
            $data['if_show']    =   $_POST['if_show'];
            $data['sort_order'] =   $_POST['sort_order'];
            $data['content'] =   $_POST['content'];
            $data['image0']        =   $dat['dir'];
            $data['add_time']   =   gmtime();

            if (!$article_id = $this->_article_mod->add($data))  //��ȡarticle_id
            {
                $this->show_warning($this->_article_mod->get_error());

                return;
            }

            /* ������� */
            if (isset($_POST['file_id']))
            {
                foreach ($_POST['file_id'] as $file_id)
                {
                    $this->_uploadedfile_mod->edit($file_id, array('item_id' => $article_id));
                }
            }
            $this->show_message('add_article_successed',
                'back_list',    'index.php?app=article',
                'continue_add', 'index.php?app=article&amp;act=add'
            );
        }
    }
     /**
     *    �༭����
     *
     *    @author    Hyber
     *    @return    void
     */
    function edit()
    {
        $article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$article_id)
        {
            $this->show_warning('no_such_article');
            return;
        }
         if (!IS_POST)
        {
            /* ��ǰ���µĸ��� */
            $files_belong_article = $this->_uploadedfile_mod->find(array(
                'conditions' => 'store_id = 0 AND belong = ' . BELONG_ARTICLE . ' AND item_id=' . $article_id,
                'fields' => 'this.file_id, this.file_name, this.file_path',
                'order' => 'add_time DESC'
            ));

            $find_data     = $this->_article_mod->find($article_id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_article');

                return;
            }
            $article    =   current($find_data);
            $article['link'] = $article['link'] ? $article['link'] : '';
            $this->assign("id", $article_id);
            $this->assign("belong", BELONG_ARTICLE);
            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js,change_upload.js'));
            $this->assign('parents', $this->_get_options());
            $this->assign('files_belong_article', $files_belong_article);
            $this->assign('article', $article);
            
            $template_name = $this->_get_template_name();
            $style_name    = $this->_get_style_name();
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'content',
                'content_css' => SITE_URL . "/themes/mall/{$template_name}/styles/{$style_name}/css/ecmall.css"
            )));
            
            $this->assign('build_upload', $this->_build_upload(array('belong' => BELONG_ARTICLE, 'item_id' => $article_id))); // ����swfupload�ϴ����
            $this->display('article.form.html');
        }
        else
        {
        	$dat=$this->_upload_files();
            $data = array();
            
            $data['title']          =   $_POST['title'];
            if (!empty($_POST['cate_id']))
            {
                $data['cate_id']        =   $_POST['cate_id'];
            }
            $data['link']           =   $_POST['link'] == 'http://' ? '' : $_POST['link'];
            $data['if_show']        =   $_POST['if_show'];
            $data['sort_order']     =   $_POST['sort_order'];
            $data['content']        =   $_POST['content'];
           $data['image0']        =   $dat['dir'];  
     
            $rows=$this->_article_mod->edit($article_id, $data);
            if ($this->_article_mod->has_error())
            {
                $this->show_warning($this->_article_mod->get_error());

                return;
            }

            $this->show_message('edit_article_successed',
                'back_list',        'index.php?app=article',
                'edit_again',    'index.php?app=article&amp;act=edit&amp;id=' . $article_id);
        }
    }
function _upload_files()
    {
    	/*
    	//ԭ����
        import('uploader.lib');
        $data      = array();
         acticle_logo 
        $file = $_FILES['file'];
        if ($file['error'] == UPLOAD_ERR_OK && $file !='')
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->allowed_size(SIZE_STORE_LOGO); // 20KB
            $uploader->addFile($file);
            if ($uploader->file_info() === false)
            {
                $this->show_warning($uploader->get_error());
                return false;
            }
            $uploader->root_dir(ROOT_PATH);
            $data['image0'] = $uploader->save('data/files/articles', $_FILES["name"]);
        }
        return $data;*/
    	
    	import('uploader.lib');
    	$daimg = array();
    	$file = $_FILES['file'];
    	$Filedir = 'data/files/articles/';
    	$filena = date('Ymdhis'); 
    	$FileName = $filena.strrchr($_FILES['file']['name'],'.');
    	if ($file['error'] == UPLOAD_ERR_OK && $file !='')
    	{
    		$uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->allowed_size(SIZE_STORE_PARTNER); // 100KB
            $uploader->addFile($file);
            if ($uploader->file_info() === false)
            {
            	$this->show_warning($uploader->get_error());
            	return false;
            }
            $uploader->root_dir(ROOT_PATH);
            $uploader->save('data/files/articles',$filena);
			$daimg['dir']=$Filedir.$FileName;
    	}
    	return $daimg;
    }

    //�첽�޸�����
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('if_show', 'sort_order')))
       {
           $data[$column] = $value;
           $this->_article_mod->edit($id, $data);
           if(!$this->_article_mod->has_error())
           {
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
        $article_ids = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$article_ids)
        {
            $this->show_warning('no_such_article');

            return;
        }
        $article_ids=explode(',', $article_ids);
        $message = 'drop_ok';
        foreach ($article_ids as $key=>$article_id){
            $article=$this->_article_mod->find(intval($article_id));
            $article=current($article);
            if($article['code']!=null)
            {
                unset($article_ids[$key]);  //�в�����ϵͳ���� ���˵�
                $message = 'drop_ok_system_article';
            }
            else
            {

            }
        }
        if (!$article_ids)
        {
            $message = 'system_article'; //ȫ��ϵͳ����
            $this->show_warning($message);

            return;
        }
        if (!$this->_article_mod->drop($article_ids))    //ɾ��
        {
            $this->show_warning($this->_article_mod->get_error());

            return;
        }

        $this->show_message($message);
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
            $this->_article_mod->edit($id, array('sort_order' => $sort_orders[$key]));
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
        /* ȡ�ÿ�����Ϊ�ϼ������·������� */
    function _get_options()
    {
        $mod_acategory = &m('acategory');
        $acategorys = $mod_acategory->get_list();
        $tree =& $this->_tree($acategorys);
        return $tree->getOptions();
    }

    /* �첽ɾ������ */
    function drop_uploadedfile()
    {
        $file_id = isset($_GET['file_id']) ? intval($_GET['file_id']) : 0;
        if ($file_id && $this->_uploadedfile_mod->drop($file_id))
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
}

?>