<?php
define('PR_CACHE_TTL', 3600);       //设置本页缓存数据的缓存时间 单位:秒 默认1小时
define('VIEW_PAGE_NUM', 20);		//商品每页显示个数
define('PR_GOODS_NUM', 4);			//店铺首页推荐商品显示个数

class Store_leagueApp extends MallbaseApp
{
	var $_promotion_mod;
	var $store_info;
	function __construct()
	{
		$this->Store_leagueApp();
	}
 	public function Store_leagueApp()
    {
    	parent::__construct();
    	$this->_promotion_mod =&m('promotion');
    }
	function index()
	{
		$this->assign('index', 6);
		$store_mod = &m('store');
		$store_goods_mod = & m('storegoods');
		$page_num = 12;
		$page = $this->_get_page($page_num);
		$conditions = " sg.goods_id<>'' AND s.store_id <> ".STORE_ID." GROUP BY s.store_id";
		$store_all = $store_mod->getAll("select s.store_id from pa_store s LEFT join pa_store_goods  sg on sg.store_id=s.store_id where " .$conditions);
		$count = count($store_all);
		$page['item_count'] = $count;
		$store_info = $store_mod->getAll("select s.store_id,s.store_name,s.store_logo from pa_store s LEFT join pa_store_goods  sg on sg.store_id=s.store_id where " . $conditions . " order by s.store_id desc  limit " . $page['limit']);
		foreach($store_info as $k => $v){
			if($v['store_logo'] == "")
			{
				$v['store_logo'] = "themes/mall/default/styles/default/images/160logo.jpg";
			}
			$store_info[$k]['store_logo'] = SITE_URL."/".$v['store_logo'];
		}
		$store_arr = array_chunk($store_info, 2);
		$this->assign('page_info', $page);
		$this->assign('store_info', $store_info);
		$this->assign('store_arr', $store_arr);
		$this->assign('sindex','true');
		$this->display("store_league_index.html");	
	}
	/*显示派啦店商品--分类版*/
	function view()
	{
		$store_goods_mod = &m('storegoods');
		$store_mod = &m('store');
		$goods_mod = &m('goods');
		$gcategory_mod = &m('gcategory');
		
		//店铺参数
		$store_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if($store_id==0){
			$this->index();
			return;
		}
		
		//店铺信息显示
		$this->store_info = $store_mod->getRow("select * from pa_store where store_id = " .$store_id);		
		if($this->store_info===FALSE){  	 //未找到店铺时显示
			$this->index();		     //暂显示店铺列表,以后考虑友好提示页
			return;
		}

		$this->store_info['store_logo'] = IMAGE_URL.$this->store_info['store_logo'];
		$this->assign('store_id',$store_id);
		$this->assign('store',$this->store_info);		
		//$this->assign('pr_in','-1');
		$this->assign('store_id',$store_id);
		$param["store_id"]=$store_id;
		
		$use = empty($_GET['use']) ? 0 : intval($_GET['use']);  //完善后考虑去除
		$param['use']=$use;
		//商品分类条件
    	$cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
    	$param["cate_id"]=$cate_id;
    	//促销分类
    	$pr_cate_id = empty($_GET['pr_cate_id']) ? 0 : trim(strtolower($_GET['pr_cate_id']));
    	if($pr_cate_id!='all'){$pr_cate_id=intval($pr_cate_id);}    		
    	$this->assign('pr_cate_id',$pr_cate_id); 
    	$param["pr_cate_id"]=$pr_cate_id;

    	//搜索关键字
    	$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    	if($keyword=='请输入关键字'){$keyword='';}
    	$param["keyword"]=$keyword;
    	
    	//价格区间条件
    	$sprice = empty($_GET['sprice'])? 0:intval($_GET['sprice']);
    	$eprice = empty($_GET['eprice'])? 0:intval($_GET['eprice']);
    	$param["sprice"]=$sprice;
    	$param["eprice"]=$eprice;
    	
    	//排序方式
    	$opt = (!isset($_GET['opt'])) ? 1 : intval($_GET['opt']);
    	$param['opt']=$opt;
	
		//读取本店所有分类和统计
		$cates = $this->_get_gcategory($store_id);
		$this->assign('cates',$cates);
		
		//读取本店促销分类和统计
		$prcates = $this->_get_pr_gcategory($store_id);
		$this->assign('pr_cates',$prcates);
		
		//店里无产品时显示未开店状态		
		if(empty($cates))
		{
			$this->show_warning("该店铺暂时没有任何商品，处于未开业状态");
			return ;
		}
		
		//所需要连接导航定义
		$index_url = 'index.php?app='.$_GET["app"] .'&act=view&id='.$store_id;
		$this->assign('index_url',$index_url);
    	$prcate_url = $cate_url = $price_url = $order_url = 'index.php?app='.$_GET["app"].'&act=view&id='.$store_id;
    
    	//关键字暂只加在排序上
    	if($keyword){
    		$order_url .= '&keyword='. urlencode($keyword);    		
    	}
    	if($cate_id>0){
    		$price_url .= '&cate_id='.$cate_id;
    		$order_url .= '&cate_id='.$cate_id;
    	}
		if($pr_cate_id){
    		$price_url .= '&pr_cate_id='.$pr_cate_id;
    		$order_url .= '&pr_cate_id='.$pr_cate_id;
    	}
    	if($sprice>$eprice && $eprice!=0){$price=$sprice;$sprice=$eprice;$eprice=$price;}
    	if($sprice>0){
    		$cate_url .= '&sprice=' . $sprice ;	
    		$order_url .= '&sprice=' . $sprice ;
    		if($pr_cate_id!=0){$prcate_url .= '&sprice='.$sprice ;}    		
    	}
    	if($eprice>0){
    		$cate_url .=  '&eprice='. $eprice;	
    		$order_url .= '&eprice='. $eprice;	
    		if($pr_cate_id!=0){$prcate_url .= '&eprice='.$eprice ;}    			
    	}
    	if($pr_cate_id!=0){
    		$price_url .= '&pr_cate_id='.$pr_cate_id;    		
    	}
    	if($opt>0){
    		$cate_url .= '&opt='.$opt;
    		$price_url .= '&opt='.$opt;
    	}
    	
    	$this->assign('sprice',$sprice);
    	$this->assign('eprice',$eprice);    	
    	$this->assign('cate_url',$cate_url);
    	$this->assign('price_url',$price_url);
    	$this->assign('order_url',$order_url);
    	$this->assign('prcate_url',$prcate_url);    
	
		//URL处理
		$search_arr = parse_url($_SERVER['REQUEST_URI']);
    	$search_arr = explode('&', $search_arr['query']);
    	$uri_arr = array();
    	$arr = $arr1 = $arr2 = $arr3 = array();
    	foreach ($search_arr as $k => $v)
    	{
    		$uri_arr = explode('=', $v);
    		if ($uri_arr[0] != 'page' && $uri_arr[0] != 'use' && $uri_arr[0] != 'goods_name' && $uri_arr[0] != pr_cate_id)
    		{
    			$arr2[$uri_arr[0]] = urldecode($uri_arr[1]);
    		}
    	}   	

		//促销商品显示
		if($pr_cate_id)
		{ 
			$pr_goods = $this->_get_pr_goodsinfo($param);	
			$this->assign('view','view2');
			$this->assign('pr_goods',$pr_goods);
			$this->assign('pr_cate_id',$pr_cate_id);
			$this->display('store_league_view.html');	
		}
		else {
			//普通分类显示
			$store_goods_info = $this->_get_cate_goodsinfo($param);
			$search_index = 'index.php?' . http_build_query($arr2);
	    	$this->assign('search_index',$search_index);   	
			
			//首页显示推荐商品
			/*if(PR_GOODS_NUM>0 && $page['curr_page']==1 && $cate_id==0 && $opt==0 && $keyword==""){
				$this->assign('pr_goods',$this->_get_hot_prgoods($store_id,$cate_id));
				$this->assign('showhot','1');		
			}*/
			$this->assign('view','view');
			$this->assign('cate_id',$cate_id);
			$this->assign('store_goods_info',$store_goods_info);
			$this->display("store_league_view.html");	
			}
	}	
	
    /**
     * 取得获取分类商品
     * @author wscsky
     * @return  goods_list;
     */
	function _get_cate_goodsinfo($param){		
		$page = $this->_get_page(VIEW_PAGE_NUM);
		//初始化查询
		$conditions = " where sg.stock>0 and g.if_show = 1 AND g.closed = 0 AND g.status = 1 and sg.store_id=".$param['store_id'];
		$sql="select g.goods_id,g.goods_name,g.mimage_url,g.price,g.credit, sg.gs_id, sg.stock ,sg.stock,sg.selllog";
		$sql2=" from pa_goods g left join pa_store_goods sg on g.goods_id = sg.goods_id";
		
		//关键字
		if($param['keyword'])
		{
			import('chineseSpell.class');
			$cs = new ChineseSpell();		
			$str = "LIKE '%" . urldecode($param['keyword']) . "%'";
            $spellStr = "LIKE '%" . $cs->getFullSpell(urldecode($param['keyword'])) . "%'"; 
            //$letterStr = "LIKE '%" . $cs->getFirstLetter(trim($_GET['keyword'])) . "%'";
            $conditions .= " AND (goods_name {$str} OR brand {$str} OR cate_name {$str} OR full_spell {$spellStr})";
		}

		//分类条件
		if ($param['cate_id']> 0)
		{
			$conditions .= " AND g.cate_id_2 = " . $param['cate_id'];
		}
	    //价格条件
		if ($param['sprice']>0)
      	{
         	$conditions .= " and g.price>" . $param['sprice'];
        }
    	if($param['eprice']>0)
    	{
           	$conditions .= " and g.price<=" . $param['eprice'];
        }
        
        //排序条件
		switch($param['opt'])
			{
			case 1:
				$orderby=" order by g.add_time desc ";
				break;
			case 2:
				$orderby=" order by g.price desc ";
				break;
			case 3:
				$orderby=" order by g.price Asc ";
				break;
			default:
				$orderby=" order by sg.selllog desc ";
				$param['opt']=0;
				break;
		}
		$goods_mod = &m("goods");
		$count = $goods_mod->getOne("select count(*)".$sql2.$conditions);
		$page['item_count'] = $count;
		
		$goods_list = $goods_mod->getAll($sql.$sql2.$conditions.$orderby.' limit '.$page['limit']);
		foreach($goods_list as $k => $v){
			$goods_list[$k]['mimage_url'] = IMAGE_URL.$v['mimage_url'];
			$goods_list[$k]['price1'] = (string)floor($v['price']);
			$goods_list[$k]['price2'] = substr((string)$v['price'],strpos((string)$v['price'],"."),3);
			$goods_list[$k]['credit1'] = (string)floor($v['credit']);
			$goods_list[$k]['credit2'] = substr((string)$v['credit'],strpos((string)$v['credit'],"."),3);
			$goods_list[$k]['class'] = $k%4+1;
		}
    	$this->assign('opt',$param['opt']);	
    	$this->_format_page($page);
        $this->assign('page_info', $page);
        return $goods_list;
	}
    /**
     * 取得促销专区商品
     * @author wscsky
     * @return  goods_list;
     */
    function _get_pr_goodsinfo($param)
    {   
    	$page = $this->_get_page(VIEW_PAGE_NUM);
    	//初始化条件
    	$conditions =' where p.pr_stock> 0 and p.pr_status = 10 and p.pr_stock<>0 AND sg.store_id='.$param['store_id'];
    	$page = $this->_get_page(VIEW_PAGE_NUM);
    	//推荐分类限制
   		if($param['pr_cate_id']=='all'){
    		$param['cate_id']=0;   		
    	}else{
    		$param['cate_id']=intval($param['pr_cate_id']);
    		if($param['cate_id']>0){
    			$gcategory_mod  =& bm('gcategory');
    			$param['layer']   = $gcategory_mod->get_layer($param['cate_id'], true);
    			$conditions .= " and g.cate_id_".$param['layer']." = ".$param['cate_id'];
    		}
    	}
    	//关键字
		if($param['keyword'])
		{
			import('chineseSpell.class');
			$cs = new ChineseSpell();		
			$str = "LIKE '%" . urldecode($param['keyword']) . "%'";
            $spellStr = "LIKE '%" . $cs->getFullSpell(urldecode($param['keyword'])) . "%'"; 
            //$letterStr = "LIKE '%" . $cs->getFirstLetter(trim($_GET['keyword'])) . "%'";
            $conditions .= " AND (goods_name {$str} OR brand {$str} OR cate_name {$str} OR full_spell {$spellStr})";
		}
		
    	if($param['sprice']>0)
    	{
			$conditions .= " and p.pr_price > ".$param['sprice'];
    	}
    	if($param['eprice']>0){
    		$conditions .= " and p.pr_price <= ".$param['eprice'];	
    	}
    	switch($param['opt'])
			{
			case 1:
				$orderby=" order by g.add_time desc ";
				break;
			case 2:
				$orderby=" order by p.pr_price desc ";
				break;
			case 3:
				$orderby=" order by p.pr_price Asc ";
				break;
			default:
				$orderby=" order by p.pr_selllog desc ";
				$param['opt']=0;
				break;
		}
		
    	$count = $this->_promotion_mod->getOne("select count(0) FROM pa_promotion p 
				LEFT JOIN pa_promotion_store_goods ps on ps.promotion_id=p.promotion_id 
				LEFT JOIN pa_store_goods sg on sg.gs_id=ps.gs_id 
				LEFT JOIN pa_goods g on g.goods_id = sg.goods_id ".$conditions);
    	$page['item_count'] = $count;
    			
    	$sql = "select p.pr_price,p.pr_stock,p.pr_selllog,p.promotion_id,p.pr_credit,g.goods_id,g.goods_name,g.cate_id,g.mimage_url
    			FROM pa_promotion p 
				LEFT JOIN pa_promotion_store_goods ps on ps.promotion_id=p.promotion_id 
				LEFT JOIN pa_store_goods sg on sg.gs_id=ps.gs_id 
				LEFT JOIN pa_goods g on g.goods_id = sg.goods_id 
				";

    	$goods_list = $this->_promotion_mod->getAll($sql.$conditions.$orderby." limit ".$page['limit']);
    	foreach ($goods_list as $key =>$goods)
    	{
            $goods_list[$key]['mimage_url'] = IMAGE_URL.$goods['mimage_url'];
			$goods_list[$key]['price1'] = (string)floor($goods['pr_price']);
			$goods_list[$key]['price2'] = substr((string)$goods['pr_price'],strpos((string)$goods['pr_price'],"."),3);
			$goods_list[$key]['pr_credit1'] = (string)floor($goods['pr_credit']);
			$goods_list[$key]['pr_credit2'] = substr((string)$goods['pr_credit'],strpos((string)$goods['pr_credit'],"."),3);
			$goods_list[$key]['class'] = $k%4+1;
            $goods_list[$key]['pr_discount'] = $goods['pr_discount']*10;
    	}
    	$this->assign('opt',$param['opt']);	
    	$this->_format_page($page);
        $this->assign('page_info', $page);
    	return $goods_list;
    }
    /**
     * 读取店铺热门商品
     * @author wscsky 
     * @return goods_list 
     **/
    function _get_hot_prgoods($store_id,$cate_id=0,$orders=' g.add_time desc ')
    	{
    	//初始化条件
    	$conditions =' where p.pr_status = 10 and p.pr_stock<>0 AND sg.store_id='.$store_id;
    	if($cate_id>0)
    	{	
    		$gcategory_mod  =& bm('gcategory');
	        $layer = $gcategory_mod->get_layer($cate_id, true);	            
			$conditions .= " and g.cate_id_".$layer." = ".$cate_id;
    	}
    	$sql = "select select p.pr_price,p.pr_stock,p.pr_selllog,p.promotion_id,p.pr_credit,g.goods_id,g.goods_name,g.cate_id,g.mimage_url
    			from pa_promotion p 
				left join pa_promotion_store_goods ps on ps.promotion_id=p.promotion_id 
				left join pa_store_goods sg on sg.gs_id=ps.gs_id 
				LEFT JOIN pa_goods g on g.goods_id = sg.goods_id 
				LEFT JOIN pa_store s on s.store_id = sg.store_id";
    	
    	$goods_list = $this->_promotion_mod->getAll($sql.$conditions.' order by '.$orders." limit 0,".PR_GOODS_NUM);
    	foreach ($goods_list as $key =>$goods)
    	{
            $goods_list[$key]['mimage_url'] = IMAGE_URL.$goods['mimage_url'];
            $goods_list[$key]['price1'] = (string)floor($goods['pr_price']);
			$goods_list[$key]['price2'] = substr((string)$goods['pr_price'],strpos((string)$goods['pr_price'],"."),3);
			$goods_list[$key]['pr_credit1'] =(string)floor($goods['pr_credit']);
			$goods_list[$key]['pr_credit2'] = substr((string)$goods['pr_credit'],strpos((string)$goods['pr_credit'],"."),3);
			$goods_list[$key]['class'] = $key%4+1;	
            $goods_list[$key]['pr_discount'] = $goods['pr_discount']*10;
    	}
    	return $goods_list;

    }
	 /**
     * 读取本店的所有分类及商品数
     * @author wscsky
     * @return gcate_list;
     */
    function _get_gcategory($store_id)
    {
    	$cache_server = &cache_server();
    	$gcate_list = $cache_server->get('all_cate_of_store_'.$store_id);
    	if($gcate_list === false){    	
    		//初始化条件
    		$conditions =' where sg.stock>0 AND g.if_show = 1 AND g.closed = 0 AND g.status = 1 and sg.store_id = '.$store_id;
    		//查询设定
    		$sql = "select gc.cate_id,gc.cate_name from pa_goods g
    					left join pa_gcategory gc on g.cate_id_2 = gc.cate_id
    					left join pa_store_goods sg on g.goods_id = sg.goods_id ";
    		$sql .= $conditions;
            
    		$sql = 'select cate_id,cate_name,count(cate_id) as num from('.$sql.') as tmptb group by cate_id HAVING num>0 order by num desc';
            
            $gcate_list = $this->_promotion_mod->getAll($sql);
    		$cache_server->set('all_cate_of_store_'.$store_id,$gcate_list,PR_CACHE_TTL);
    	}
    	$goodstotal=0;
		foreach ($gcate_list as $k=>$v){
			$goodstotal+=$v['num'];
		}
		$this->assign('total',$goodstotal);
    	return $gcate_list;
    }
     /**
     * 读取本店的促销分类及商品数
     * @author wscsky
     * @return gprcate_list;
     **/
    function _get_pr_gcategory($store_id)
    {
    	$cache_server = &cache_server();
    	$gprcate_list = $cache_server->get('pr_cate_of_store_'.$store_id);
    	if($gprcate_list === false){    	
    		//初始化条件
    		$conditions =' where p.pr_stock>0 AND p.pr_status = 10 and p.pr_stock<>0 AND sg.store_id='.$store_id;
    		//查询设定
    		$sql = "select g.cate_id_2,gy.cate_name from pa_promotion p 
				left join pa_promotion_store_goods ps on ps.promotion_id=p.promotion_id 
				left join pa_store_goods sg on sg.gs_id=ps.gs_id 
				LEFT JOIN pa_goods g on g.goods_id = sg.goods_id 
				LEFT join pa_gcategory gy on g.cate_id_2 = gy.cate_id";
    		$sql .=$conditions;
    		$sql = 'select cate_id_2,cate_name,count(cate_id_2) as num from('.$sql.') as tmptb group by cate_id_2 HAVING num>0 order by num desc';
    		$gprcate_list = $this->_promotion_mod->getAll($sql);
    		$cache_server->set('pr_cate_of_store_'.$store_id,$gprcate_list,PR_CACHE_TTL);
    	}
    	$goodstotal=0;
		foreach ($gprcate_list as $k=>$v){
			$goodstotal+=$v['num'];
		}
		$this->assign('prtotal',$goodstotal);
    	return $gprcate_list;
    }
}
?>