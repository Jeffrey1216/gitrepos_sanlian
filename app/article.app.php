<?php

class ArticleApp extends MallbaseApp
{

    var $_article_mod;
    var $_acategory_mod;
    var $_ACC; //ϵͳ����cate_id����
    var $_cate_ids; //��ǰ���༰�������cate_id
    function __construct()
    {
        $this->ArticleApp();
    }
    function ArticleApp()
    {
        parent::__construct();
        $this->_article_mod = &m('article');
        $this->_acategory_mod = &m('acategory');
        /* ���ϵͳ����cate_id���� */
        $this->_ACC = $this->_acategory_mod->get_ACC();
    }
    function index()
    {
        /* ȡ�õ��� */
        $this->assign('navs', $this->_get_navs());
    	 /* ����cate_id */
        $cate_id_now = !empty($_GET['cate_id'])? intval($_GET['cate_id']) : $this->_ACC[ACC_ABOUT]; //���cate_idΪ����Ĭ����ʾ��������
        $cate_id = $this->_ACC[ACC_ABOUT];
        $parent_id = empty($_GET['parent_id']) ? 0 : intval($_GET['parent_id']);
        $article_id = empty($_GET['article_id']) ? 0 : intval($_GET['article_id']); 
        isset($_GET['code']) && isset($this->_ACC[trim($_GET['code'])]) && $cate_id_now = $this->_ACC[trim($_GET['code'])]; //�����code
        /* ȡ�õ�ǰ���༰�������cate_id */
        $cate_ids = array();
        if ($cate_id_now > 0) //�ų�ϵͳ���÷���
        {
            $cate_ids = $this->_acategory_mod->get_descendant($cate_id_now);
            if (!$cate_ids)
            {
                $this->show_warning('no_such_acategory');
                return;
            }
        }
        else
        {
            $this->show_warning('no_such_acategory');
            return;
        }
        $this->_cate_ids = $cate_ids;
        /* ��ǰλ�� */
        $curlocal = $this->_get_article_curlocal($cate_id_now);

        unset($curlocal[count($curlocal)-1]['url']);
        $this->_curlocal($curlocal);
        /* ���·��� */
        $acategories = $this->_get_acategory($cate_id);	
        /* �������µ��ӷ���  */
        foreach($acategories as $k => $acategory) {
        	$acategories[$k]['child'] = $this->_acategory_mod->find(array('conditions' => 'parent_id='.$acategory['cate_id']));
        	$conditions = ' AND cate_id='.$acategory['cate_id']; 
        	$acategories[$k]['article'] = $this->_article_mod->find(array(
													            'conditions'  => 'if_show=1 AND store_id=0 AND code = ""' . $conditions,
													            'count'   => true   //����ͳ��
													        )); //�ҳ����з�������������
        }
    	$conditions = '';
    	if(IS_POST) {
	    	$title = $_POST['keyword'];
	    	$article_mod = & m("article");
	    	if($title == '') { //���û�йؼ���
	    		
	    	} else {
	    		$conditions = " and title like '%".$title."%'";
	    	}
	    	$all = $this->_search_article('all',$conditions);
        
       		 $articles = $all['articles'];
    	} else {
        
	        /* �����µ��������� */
	        $all = $this->_get_article('all');
	        
	        $articles = $all['articles'];
    	}
    	/**
    	 * ������ϸ��Ϣ
    	 * **/
    	if($article_id != 0)
    	{
    		$article_info = $this->_article_mod->get($article_id);
    		 /* ��һƪ��һƪ */
            $pre_article = $this->_article_mod->get('article_id<' . $article_id . ' AND code = "" AND if_show=1  AND store_id=0 ORDER BY article_id DESC limit 1');
            $pre_article && $pre_article['target'] = $pre_article['link'] ? '_blank' : '_self';
            $next_article = $this->_article_mod->get('article_id>' . $article_id . ' AND code = "" AND if_show=1  AND store_id=0 ORDER BY article_id ASC limit 1');
            $next_article && $next_article['target'] = $next_article['link'] ? '_blank' : '_self';
    		$this->assign('article_info',$article_info);
    		$this->assign('pre_article', $pre_article);
        	$this->assign('next_article', $next_article);
        	$curlocal = $this->_get_article_curlocal($parent_id);	
    	}
    	$this->_curlocal($curlocal);
    	if(is_array($curlocal))
    	{
	    	foreach ($curlocal as $k=>$v)
	        {
	        	$cul = $v;
	        }	
    	}
        $page = $all['page'];
        /* ������ */
        $new = $this->_get_article('new',$conditions);
        $new_articles = $new['articles'];
        // ҳ�����
        $category = $this->_acategory_mod->get_info($cate_id);
        $this->_config_seo('title', $category['cate_name'] . ' - ' . Conf::get('site_title'));
        $this->assign('parent_id',$parent_id);
        $this->assign('cate_id_now',$cate_id_now);
        $this->assign('articles', $articles);
        $this->assign('new_articles', $new_articles);
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('cul',$cul);
        $this->assign('cate_id',$cate_id_now);
        $this->assign('acategories', $acategories);
        $this->display('article.index.html');
    }
    //�̳ǰ���
    public function help() {
    	 /* ����cate_id */
        $cate_id_now = !empty($_GET['cate_id'])? intval($_GET['cate_id']) : $this->_ACC[ACC_HELP]; //���cate_idΪ����Ĭ����ʾ��������
        $cate_id = $this->_ACC[ACC_HELP];
        $parent_id = empty($_GET['parent_id']) ? 0 : intval($_GET['parent_id']);
        $article_id = empty($_GET['article_id']) ? 0 : intval($_GET['article_id']); 
        isset($_GET['code']) && isset($this->_ACC[trim($_GET['code'])]) && $cate_id_now = $this->_ACC[trim($_GET['code'])]; //�����code
        /* ȡ�õ�ǰ���༰�������cate_id */
        $cate_ids = array();
        if ($cate_id_now > 0) //�ų�ϵͳ���÷���
        {
            $cate_ids = $this->_acategory_mod->get_descendant($cate_id_now);
            if (!$cate_ids)
            {
                $this->show_warning('no_such_acategory');
                return;
            }
        }
        else
        {
            $this->show_warning('no_such_acategory');
            return;
        }
        $this->_cate_ids = $cate_ids;
        /* ��ǰλ�� */
        $curlocal = $this->_get_article_curlocal($cate_id_now);
        unset($curlocal[count($curlocal)-1]['url']);
        $this->_curlocal($curlocal);
        /* ���·��� */
        $acategories = $this->_get_acategory($cate_id);	
        
        /* �������µ��ӷ���  */
        foreach($acategories as $k => $acategory) {
        	$acategories[$k]['child'] = $this->_acategory_mod->find(array('conditions' => 'parent_id='.$acategory['cate_id']));
        	$conditions = ' AND cate_id='.$acategory['cate_id']; 
        	$acategories[$k]['article'] = $this->_article_mod->find(array(
													            'conditions'  => 'if_show=1 AND store_id=0 AND code = ""' . $conditions,
													            'count'   => true   //����ͳ��
													        )); //�ҳ����з�������������
        }
    	$conditions = '';
    	if(IS_POST) {
	    	$title = $_POST['keyword'];
	    	$article_mod = & m("article");
	    	if($title == '') { //���û�йؼ���
	    		
	    	} else {
	    		$conditions = " and title like '%".$title."%'";
	    	}
	    	$all = $this->_search_article('all',$conditions);
        
       		 $articles = $all['articles'];
    	} else {
        
	        /* �����µ��������� */
	        $all = $this->_get_article('all');
	        
	        $articles = $all['articles'];
    	}
    	/**
    	 * ������ϸ��Ϣ
    	 * **/
    	if($article_id != 0)
    	{
    		$article_info = $this->_article_mod->get($article_id);
    		 /* ��һƪ��һƪ */
            $pre_article = $this->_article_mod->get('article_id<' . $article_id . ' AND code = "" AND if_show=1  AND store_id=0 ORDER BY article_id DESC limit 1');
            $pre_article && $pre_article['target'] = $pre_article['link'] ? '_blank' : '_self';
            $next_article = $this->_article_mod->get('article_id>' . $article_id . ' AND code = "" AND if_show=1  AND store_id=0 ORDER BY article_id ASC limit 1');
            $next_article && $next_article['target'] = $next_article['link'] ? '_blank' : '_self';
    		$this->assign('article_info',$article_info);
    		$this->assign('pre_article', $pre_article);
        	$this->assign('next_article', $next_article);
    	}
        $page = $all['page'];
        /* ������ */
        $new = $this->_get_article('new',$conditions);
        $new_articles = $new['articles'];
        if(IS_POST)
        {
        	$this->assign('dis','1');
        }
        // ҳ�����
        $category = $this->_acategory_mod->get_info($cate_id);
        $this->_config_seo('title', $category['cate_name'] . ' - ' . Conf::get('site_title'));
        $this->assign('parent_id',$parent_id);
        $this->assign('cate_id_now',$cate_id_now);
        $this->assign('articles', $articles);
        $this->assign('new_articles', $new_articles);
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('acategories', $acategories);
        //$this->display('article.help.html');
        $this->display('article.help.index.html');
    }
    
    function help_content()
    {
    	$article_id = empty($_GET['article_id']) ? 0 : intval($_GET['article_id']); 
    	if($article_id != 0)
    	{
    		$article_info = $this->_article_mod->get($article_id);
    		 /* ��һƪ��һƪ */
            $pre_article = $this->_article_mod->get('article_id<' . $article_id . ' AND code = "" AND if_show=1  AND store_id=0 ORDER BY article_id DESC limit 1');
            $pre_article && $pre_article['target'] = $pre_article['link'] ? '_blank' : '_self';
            $next_article = $this->_article_mod->get('article_id>' . $article_id . ' AND code = "" AND if_show=1  AND store_id=0 ORDER BY article_id ASC limit 1');
            $next_article && $next_article['target'] = $next_article['link'] ? '_blank' : '_self';
    		$this->assign('article_info',$article_info);
    		$this->assign('pre_article', $pre_article);
        	$this->assign('next_article', $next_article);
        	
        	$this->display('article_info.html');
    	}
    }
    
    function get_article()
    {
    	$article_id = empty($_GET['article_id']) ? 0 : intval($_GET['article_id']); 
    	$html = file_get_contents(SITE_URL . '/index.php?app=article&act=help_content&article_id='.$article_id);
    	
    	$this->json_result($html);
    }
    
    
    function view()
    {

        $article_id = empty($_GET['article_id']) ? 0 : intval($_GET['article_id']);
        //��ǰ���µ�cate_id
        $cate_id_now = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        $parent_id = empty($_GET['parent_id']) ? 0 : intval($_GET['parent_id']);
        $cate_ids = array();
        if ($article_id>0)
        {
            $article = $this->_article_mod->get('article_id=' . $article_id . ' AND code = "" AND if_show=1 AND store_id=0');
            if (!$article)
            {
                $this->show_warning('no_such_article');
                return;
            }
            if ($article['link']){ //����������ת
                header("HTTP/1.1 301 Moved Permanently");
                header('location:'.$article['link']);
                return;
            }
            /* ��һƪ��һƪ */
            $pre_article = $this->_article_mod->get('article_id<' . $article_id . ' AND code = "" AND if_show=1  AND store_id=0 ORDER BY article_id DESC limit 1');
            $pre_article && $pre_article['target'] = $pre_article['link'] ? '_blank' : '_self';
            $next_article = $this->_article_mod->get('article_id>' . $article_id . ' AND code = "" AND if_show=1  AND store_id=0 ORDER BY article_id ASC limit 1');
            $next_article && $next_article['target'] = $next_article['link'] ? '_blank' : '_self';
            if ($article)
            {
                $cate_id = $article['cate_id'];
                /* ȡ�õ�ǰ���༰�������cate_id */
                $cate_ids = $this->_acategory_mod->get_descendant($cate_id);
            }
            else
            {
                $this->show_warning('no_such_article');
                return;
            }
        }
        else
        {
            $this->show_warning('no_such_article');
            return;
        }

        $this->_cate_ids = $cate_ids;
        /* ��ǰλ�� */
        $curlocal = $this->_get_article_curlocal($cate_id);
        $curlocal[] =array('text' => Lang::get('content'));
        $this->_curlocal($curlocal);
        /* ���·��� */
        $acategories = $this->_get_acategory($this->_ACC[ACC_NOTICE]);
        
        
        /* �������µ��ӷ���  */
        foreach($acategories as $k => $acategory) {
        	$acategories[$k]['child'] = $this->_get_child_acategory($acategory['cate_id']);
        }
        /* ������ */
        $new = $this->_get_article('new');
        $new_articles = $new['articles'];
        $this->assign('cate_id_now',$cate_id_now);
        $this->assign('parent_id',$parent_id);
        $this->assign('article', $article);
        $this->assign('pre_article', $pre_article);
        $this->assign('next_article', $next_article);
        $this->assign('new_articles', $new_articles);
        $this->assign('acategories', $acategories);

        $this->_config_seo('title', $article['title'] . ' - ' . Conf::get('site_title'));
        $this->display('article.view.html');
    }

    function system()
    {
    	//ȡ����ȡ�������¹���
    	$this->show_warning('û���ҵ��������');
        return;
        $code = empty($_GET['code']) ? '' : trim($_GET['code']);
        if (!$code)
        {
            $this->show_warning('no_such_article');
            return;
        }
        $article = $this->_article_mod->get("code='" . $code . "'");
        if (!$article||$article['cate_id']!=3)
        {
            $this->show_warning('no_such_article');
            return;
        }
        if ($article['link']){ //����������ת
                header("HTTP/1.1 301 Moved Permanently");
                header('location:'.$article['link']);
                return;
        }

        /*��ǰλ��*/
        $curlocal[] =array('text' => $article['title']);
        $this->_curlocal($curlocal);
        /*���·���*/
        $acategories = $this->_get_acategory($this->_ACC[ACC_SYSTEM]);
        
        // �������� 
        $helpCategories = $acategories[$this->_ACC[ACC_SYSTEM]];
        
        /* ������ */
        $new = $this->_get_article('new');
        $new_articles = $new['articles'];
        $this->assign('acategories', $acategories);
        $this->assign('new_articles', $new_articles);
        $this->assign('article', $article);
		$this->assign('helpCategories', $helpCategories);
        $this->_config_seo('title', $article['title'] . ' - ' . Conf::get('site_title'));
        $this->display('article.block.html');

    }

    function _get_article_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $acategory_mod = &m('acategory');
            $acategory_mod->get_parents($parents, $cate_id);
        }
        foreach ($parents as $category)
        {
            $curlocal[] = array('text' => $category['cate_name'], 'ACC' => $category['code'], 'url' => 'index.php?app=article&amp;cate_id=' . $category['cate_id']);
        }
        return $curlocal;
    }
    
    function _get_acategory($cate_id)
    {
        $acategories = $this->_acategory_mod->get_list($cate_id);
        if ($acategories){
            unset($acategories[$this->_ACC[ACC_SYSTEM]]);
            return $acategories;
        }
        else
        {
            $parent = $this->_acategory_mod->get($cate_id);
            if (isset($parent['parent_id']))
            {
                return $this->_get_acategory($parent['parent_id']);
            }
        }
    }
    //��ȡ�ӷ��� 
	function _get_child_acategory($cate_id)
    {
        $acategories = $this->_acategory_mod->get_list($cate_id); 
        if ($acategories){
            unset($acategories[$this->_ACC[ACC_SYSTEM]]);
            return $acategories;
        }
        else
        {
            $parent = $this->_acategory_mod->get($cate_id);
            if (isset($parent['parent_id']))
            {
                return null;
            }
        }
    }
    function _get_article($type='')
    {
    	$conditions = '';
        $per = '';
        switch ($type)
        {
            case 'new' : $sort_order = 'add_time DESC,sort_order ASC';
            $per=5;
            break;
            case 'all' : $sort_order = 'sort_order ASC,add_time DESC';
            $per=10;
            break;
        }
        $page = $this->_get_page($per);   //��ȡ��ҳ��Ϣ
        !empty($this->_cate_ids)&& $conditions .= ' AND cate_id ' . db_create_in($this->_cate_ids);
        $articles = $this->_article_mod->find(array(
            'conditions'  => 'if_show=1 AND store_id=0 AND code = ""' . $conditions,
            'limit'   => $page['limit'],
            'order'   => $sort_order,
            'count'   => true   //����ͳ��
        )); //�ҳ����з�������������
        $page['item_count'] = $this->_article_mod->getCount();
        foreach ($articles as $key => $article)
        {
            $articles[$key]['target'] = $article[link] ? '_blank' : '_self';
        }
        return array('page'=>$page, 'articles'=>$articles);
    }
    //������Ʒ
    public function _search_article($type='',$conditions = '') {
    	$per = '';
        switch ($type)
        {
            case 'new' : $sort_order = 'add_time DESC,sort_order ASC';
            $per=5;
            break;
            case 'all' : $sort_order = 'sort_order ASC,add_time DESC';
            $per=10;
            break;
        }
        $page = $this->_get_page($per);   //��ȡ��ҳ��Ϣ
        $articles = $this->_article_mod->find(array(
            'conditions'  => 'if_show=1 AND store_id=0 AND code = ""' . $conditions,
            'limit'   => $page['limit'],
            'order'   => $sort_order,
            'count'   => true   //����ͳ��
        )); //�ҳ����з�������������
        $page['item_count'] = $this->_article_mod->getCount();
        foreach ($articles as $key => $article)
        {
            $articles[$key]['target'] = $article[link] ? '_blank' : '_self';
        }
        return array('page'=>$page, 'articles'=>$articles);
    }
}
	

?>
