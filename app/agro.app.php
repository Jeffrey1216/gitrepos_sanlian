<?php

/*分类控制器*/
class agroApp extends MallbaseApp
{
	var $_article_mod;
	
    function index()
    {
    	$tit="选择分类";
        $this->assign('index', 2); // 标识当前页面是首页，用于设置导航状态
        $this->assign('icp_number', Conf::get('icp_number'));
        /* 热门搜素 */
        $this->assign('hot_keywords', $this->_get_hot_keywords());
        /* 尾部 */
        $this->assign('infor' , $this->_get_information());
        /* 分类的搜索*/
        $this->assign('goods_sort',$this->_get_stor());
        $this->_config_seo(array(
            'title' => Lang::get('mall_index') . ' - ' . Conf::get('site_title'),
        ));
        $this->assign('region',$this->_get_region());
        /*获取农业专区搜索联动*/
        $this->assign('title',$tit);
        $this->assign('agcate',$this->_get_gcategory());
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('index', 'is_index');
        $this->assign('page_keywords', Conf::get('site_keywords'));
        $this->display('agroIndex.html');
    }

    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }
    
    function _get_navigation()
    {
    	$_gcategroy_mod = & m('gcategory');
    	$navigation = $_gcategroy_mod->getAll('select cate_name,cate_id from  pa_gcategory where parent_id=0 AND mall_type=3');
    	//var_dump($navigation);
    	return  $navigation;
    }
    function _get_information()
    {
    	$goods_mod = & m("goods");
    	$_article_mod = &m('article');
		$information = $_article_mod->getAll("SELECT * from pa_article a LEFT JOIN pa_acategory b ON a.cate_id=b.cate_id WHERE a.cate_id=32  ORDER BY add_time DESC LIMIT 0,2");
		$commit = $goods_mod->getAll("SELECT a.comment,b.goods_id,b.simage_url,c.comments,d.buyer_name,sg.gs_id from pa_order_goods a left join pa_goods b on a.goods_id=b.goods_id left join pa_goods_statistics c on c.goods_id=b.goods_id left join pa_order d on d.order_id=a.order_id left join pa_store_goods sg on sg.goods_id=a.goods_id  where seller_id=".STORE_ID." GROUP BY goods_id ORDER BY c.comments DESC LIMIT 0,4");		
		foreach ($commit as $key=>$val)
		{
			$commit[$key]['simage_url'] = IMAGE_URL.$val['simage_url'];
           	$commit[$key]['comment'] = empty($val['comment']) ? '卖家挺热情的，发货速度还不错' : $val['comment'];
		}
		$infor['commit'] = $commit;
		foreach ($information as $v){
			$st=strip_tags(htmlspecialchars_decode($v['content'], ENT_QUOTES));
			$v['content']=str_replace("&nbsp;", "", $st);
			$cont[]=$v;
		}
		$infor['mation'] = $cont;
		return  $infor;
    }
	function _get_region(){
    	$region_mod=&m ('region');
    	$region=$region_mod->getAll("SELECT * from pa_region WHERE level=1");
    	//var_dump($region);
    	return $region;	
	}
	function _get_stor(){
		$scategory_mod =& m('scategory');
        $stor=$scategory_mod->getAll("SELECT * from pa_gcategory where parent_id=0 AND mall_type=1");
        return $stor;
	}
    /*获取所有商品的一级分类*/
    function _get_gcategory(){
    	$gcategory_mod =& m('gcategory');
    	$gcate = $gcategory_mod->getAll("SELECT * from pa_gcategory where parent_id in (select cate_id from pa_gcategory where parent_id=0 and mall_type=1)");
		return $gcate;
    }
	/* 取得提醒信息 */
    function lmzq()
    {
    	$this->display('agroIndex.html');
    }
}

?>