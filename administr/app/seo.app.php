
<?php
class SeoApp extends BackendApp
{
	var $_goods_mod;
    var $_gcategory_mod;
    
    function __construct()
    {
        $this->GoodsApp();
    }
    function GoodsApp()
    {
        parent::BackendApp();
		import('chineseSpell.class');
        $this->_goods_mod =& bm('goods');
        $this->_gcategory_mod =& bm('gcategory');
    }
    /* 商品列表 */
    function index()
    {
         $conditions = '1 =1 ';
    	 $conditions .= $this->_get_query_conditions(array(
            array(
                'field' => 'goods_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'brand',
                'equal' => 'like',
            ),
            array(
                'field' => 'closed',
                'type'  => 'int',
            ),
        ));
        // 分类
        $cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        if ($cate_id > 0)
        { 
            $cate_ids = $this->_gcategory_mod->get_descendant_ids($cate_id);
            $conditions .= " AND g.cate_id" . db_create_in($cate_ids);
        }
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'goods_id';
             $order = 'desc';
            }
        }
        else
        {
            $sort  = 'goods_id';
            $order = 'desc';
        }
        $page = $this->_get_page(20);
        $page['item_count'] = $this->_goods_mod->getOne('select count(*) from (select count(*) from pa_goods g left join pa_store_goods sg on g.goods_id = sg.goods_id where g.status = 1 and g.closed = 0 and g.if_show = 1 group by g.goods_id ) aa');
	
        $goods_list = $this->_goods_mod->getAll('SELECT g.goods_id,g.goods_name,g.brand,g.cate_name,g.smimage_url,sg.gs_id FROM pa_goods g left join pa_store_goods sg on g.goods_id = sg.goods_id  WHERE ' .$conditions.' and g.status = 1 and g.closed = 0 and g.if_show = 1 group by g.goods_id  limit '.$page['limit']);
        foreach ($goods_list as $key => $goods)
        {
            $goods_list[$key]['cate_name'] = $this->_goods_mod->format_cate_name($goods['cate_name']);
        }
        $this->assign('goods_list', $goods_list);
        $this->_format_page($page);
        $this->assign('page_info', $page);
        // 第一级分类
        $this->assign('gcategories', $this->_gcategory_mod->get_all_options(0));
        $this->import_resource(array('script' => 'mlselection.js,inline_edit.js'));
        $this->assign('imgurl', IMAGE_URL);
        $this->display('goods_seo.html');
    }

    /**
     * 取得商品信息
     */
    function _get_goods_info($id = 0)
    {
        if ($id > 0)
        {
            $goods_info = $this->_goods_mod->get_goods_info($id);
        }
        return $goods_info;
    }
    /* 编辑商品 */
    function edit_goods()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            if(!$id || !($goods = $this->_get_goods_info($id)))
            {
                $this->show_warning('no_such_goods');
                return;
            }
            $this->assign('goods',$this->_get_goods_info($id));	
            $spec_mod=&m('goodsspec');
            $spec_data=$spec_mod->find('goods_id='.$id);
            $this->assign('specs',$spec_data);
            $this->display('goods_seo.form.html');
        }else{
        	$data = array();
        	$data['seo_title'] = $_REQUEST['seo_goods_id'];
	    	$data['seo_title'] = $_POST['seo_title'];
	    	$data['seo_keywords'] = $_POST['seo_keywords'];
	    	$data['seo_description'] = $_POST['seo_description'];
	    	$goods_mod=&m('goods');
	    	$goods_mod->edit($id,$data);				
    		$this->show_message('edit_ok',
    			'back_list', 'index.php?app=seo',
    			'edit_again', "index.php?app=seo&amp;act=edit_goods&amp;id=".$id);
        }
    }  
}

?>
