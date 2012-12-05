<?php
define('PR_INDEX', 'true');       // 促销专区标识
define('NUM_PER_PAGE', 15);        // 每页显示数量
define('PR_PRICE', 2);			// 促销专区，标识为零的数
/* 促销专区 */
class Promotion_indexApp extends MallbaseApp
{
	var $_promotion_mod;
	var $_goods_mod;
    function __construct()
    {
        $this->Promotion_indexApp();
    }
    function Promotion_indexApp()
    {
        parent::__construct();
        $this->_promotion_mod =&m('promotion');
        $this->_goods_mod =&m('goods');
    }
    function index()
    {
    	$this->assign('index',1);
    	//头文件搜索
    	$pr_index = $this->assign('pr_index',PR_INDEX);
        // 查询参数
        $param = $this->_get_query_param();
        
        $goods = $this->_get_pr_goodsinfo($param);
        $this->assign('goods',$goods);
        $this->assign('gcate',$this->_get_pr_gcategory());
        $count = $this->_get_commend_count();
		$this->assign('commend_count',$count);

    	$this->display('promotion.index.html');
    }
    /* 公司推荐*/
	function autotrophy()
    {
    	$this->assign('index',1);
		$this->assign('type','commend');
    	//头文件搜索
    	$pr_index = $this->assign('pr_index',PR_INDEX);
        // 查询参数
        $param = $this->_get_query_param();
        $goods = $this->_get_recommend_goods($param);
        $this->assign('goods',$goods);
        $this->assign('gcate',$this->_get_pr_gcategory());

    	$this->display('commend_goods.index.html');
    }
    /*获取公司推荐商品*/
    private function _get_recommend_goods($param){		
		$page = $this->_get_page(20);
		//初始化查询
		$conditions = " where sg.stock>0 and g.if_show = 1 AND g.closed = 0 AND g.status = 1 and sg.store_id= 2 AND g.`autotrophy` =1 ";
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
        $count = $this->_get_commend_count();
		$this->assign('commend_count',$count);
		$page['item_count'] = $count;
		$goods_sql = $sql.$sql2.$conditions.$orderby.' limit '.$page['limit'];
		$goods_list =  $this->_goods_mod->getAll($goods_sql);
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
	private function _get_commend_count(){
		$conditions = " where sg.stock>0 and g.if_show = 1 AND g.closed = 0 AND g.status = 1 and sg.store_id= 2 AND g.`autotrophy` =1 ";
		$sql2=" from pa_goods g left join pa_store_goods sg on g.goods_id = sg.goods_id";
		$count = $this->_goods_mod->getOne("select count(*)".$sql2.$conditions);	
        return $count;
	}
    /**
     * 取得查询参数（有值才返回）
     *
     * @return  array(
     *              'keyword'   => array('aa', 'bb'),
     *              'cate_id'   => 2,
     *              'layer'     => 2, // 分类层级
     *          )
     */
    function _get_query_param()
    {
        static $res = null;
        //商品分类条件
    	$cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
    	$this->assign('cate_id',$cate_id);    	
    	//免邮条件
    	$pr_art = empty($_GET['pr_art']) ? 0 : intval($_GET['pr_art']);
    	//搜索关键字
    	$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    	//价格区间条件
    	$sprice = empty($_GET['sprice'])? 0:intval($_GET['sprice']);
    	$eprice = empty($_GET['eprice'])? 0:intval($_GET['eprice']);
    	//排序方式
    	$order = intval($_GET['order']);
    	
    	
    	//原条件,后完善后可删除代码
    	$search_arr = parse_url($_SERVER['REQUEST_URI']);
    	$search_arr = explode('&', $search_arr['query']);

    	$uri_arr = array();
    	$arr = array();
    	foreach ($search_arr as $k => $v)
    	{
    		$uri_arr = explode('=', $v);
    		if($uri_arr[0] != 'order')
    		{
    			$arr[$uri_arr[0]] = urldecode($uri_arr[1]);
    		}	
    	}
    	$search_index = 'index.php?' . http_build_query($arr);
    	//原条件,后完善后可删除代码
    	
    	if ($res === null)
    	{
	    	if ('' != $keyword)
	        {
	          	$res['keyword'] = $keyword;
	          	$search_index .= '&keyword=' . $keyword;
	        }
	    	
    		if (0 != $pr_art)
	        {
	          	$res['pr_art'] = $pr_art;
	          	$search_index .= '&pr_art=' . $pr_art;
	          	$this->assign('art',$pr_art);
	        }
	        
	    	if (0 != $cate_id)
	    	{
	    		$res['cate_id'] = $cate_id;
	    		$gcategory_mod  =& bm('gcategory');
                $res['layer']   = $gcategory_mod->get_layer($cate_id, true);
                if(!$res['layer'])
                {
                	$this->show_warning("你所选择的分类不存在"); 
                	return ;
                }
                $search_index .= '&keyword=' . urlencode($keyword);
	    	} 
    		
	    	//新加条件导航功能
	    	$cate_url = $price_url = $order_url = 'index.php?app='.$_GET["app"];
	    	//关键字暂只加在排序上
	    	if($res['keyword']){
	    		$order_url .= '&keyword='. urlencode($keyword);
	    		
	    	}
	    	if($cate_id>0){
	    		$price_url .= '&cate_id='.$cate_id;
	    		$order_url .= '&cate_id='.$cate_id;
	    	}
	    	if($sprice>0){
	    		$cate_url .= '&sprice=' . $sprice ;	
	    		$order_url .= '&sprice=' . $sprice ;
	    		$res['sprice'] = $sprice;    		
	    	}
	    	if($eprice>0){
	    		$cate_url .=  '&eprice='. $eprice;	
	    		$order_url .= '&eprice='. $eprice;
	    		$res['eprice'] = $eprice;
	    		if($sprice>$eprice){
	    			$res['sprice'] = $eprice;
	    			$res['eprice'] = $sprice;	    			
	    		}	    		
	    	}
	    	if($order>0){
	    		$cate_url .= '&order='.$order;
	    		$price_url .= '&order='.$order;
	    	}
	    	
	    	$this->assign('sprice',$sprice);
	    	$this->assign('eprice',$eprice);
	    	
	    	$this->assign('cate_url',$cate_url);
	    	$this->assign('price_url',$price_url);
	    	$this->assign('order_url',$order_url);
    	
	    		switch ($order)
	    		{
	    			case 1 :
	    				$res['order'] = " ORDER BY p.pr_addtime DESC "; 
	    				break;
	    		    case 10 :
	    				$res['order'] = " ORDER BY p.pr_addtime ASC "; 
	    				break;
	    			case 2 :
	    				$res['order'] = " ORDER BY p.virtual_log DESC "; 
	    				break;
	    		    case 20 :
	    				$res['order'] = " ORDER BY p.virtual_log ASC "; 
	    				break;
	    			case 3 :
	    				$res['order'] = " ORDER BY p.pr_price DESC "; 
	    				break;
	    		    case 30 :
	    				$res['order'] = " ORDER BY p.pr_price ASC "; 
	    				break;
	    			default:
	    				$res['order'] = " order by p.pr_sort asc,p.virtual_log DESC,p.promotion_id DESC";
	    				break;
	    		}
	    		$this->assign('order',$order);
    	}
    	$this->assign('search_index',$search_index);
        return $res;
    }
    /**
     * 取得促销专区商品
     *
     * @return  goods_list;
     */
    function _get_pr_goodsinfo($param)
    {
    	//初始化条件
    	$conditions =' where p.pr_status = 10 AND p.pr_stock>0';
    	$page = $this->_get_page(NUM_PER_PAGE);
    	if($param['cate_id'])
    	{
			$conditions .= " and g.cate_id_".$param['layer']." = ".$param['cate_id'];
    	}
       	if($param['keyword'])
    	{
			$conditions .= " and(g.goods_name like '%".$param['keyword']."%' or p.pr_name like '%".$param['keyword']."%')";
    	}
    	if($param['pr_art'])
    	{
			$conditions .= " and p.pr_art = ".$param['pr_art'];
    	}
      	if (is_int($param['sprice']))
      	{
         $conditions .= " and p.pr_price>" . $param['sprice'];
        }
    	if(is_int($param['eprice']))
    	{
           $conditions .= " and p.pr_price<=" . $param['eprice'];
        }    
        if($param['order'] != '')
    	{
			$orders = $param['order'];
    	}
    	$sql = "select p.*,g.goods_name,gs.price as oldprice,g.mimage_url from pa_promotion p 
				left join pa_promotion_store_goods ps on ps.promotion_id=p.promotion_id 
				left join pa_store_goods sg on sg.gs_id=ps.gs_id 
				left join pa_goods_spec gs on gs.spec_id =sg.spec_id
				LEFT JOIN pa_goods g on g.goods_id = sg.goods_id
				LEFT join pa_gcategory gy on  g.cate_id = gy.cate_id";
    	$goods_list = $this->_promotion_mod->getAll($sql.$conditions." GROUP BY sg.store_id,g.goods_id ".$orders." limit ".$page['limit']);
    	foreach ($goods_list as $key =>$goods)
    	{
            $goods_list[$key]['class'] =$key%3+1;
            if($goods['oldprice']==0){
                $goods_list[$key]['zhe'] = 10;
                }
            else{
                $goods_list[$key]['zhe'] =number_format($goods['pr_price']/$goods['oldprice']*10,1);
                }
            $goods_list[$key]['pr_price1'] = (string)floor($goods['pr_price']);
            $goods_list[$key]['pr_price2'] = substr((string)$goods['pr_price'],strpos((string)$goods['pr_price'],"."),3);
    	}
    	$count = $this->_promotion_mod->getAll("select p.promotion_id from pa_promotion p 
				left join pa_promotion_store_goods ps on ps.promotion_id=p.promotion_id 
				left join pa_store_goods sg on sg.gs_id=ps.gs_id 
				LEFT JOIN pa_goods g on g.goods_id = sg.goods_id
				LEFT join pa_gcategory gy on  g.cate_id = gy.cate_id 
				LEFT join pa_goods_statistics gst on gst.goods_id=g.goods_id ".$conditions." GROUP BY sg.store_id,g.goods_id ");
    	$page['item_count'] = count($count);
		$this->_format_page($page);
        $this->assign('page_info', $page);
    	return $goods_list;
    }
    /**
     * 取得促销专区全部分类
     *
     * @return  gcategory;
     */
    function _get_pr_gcategory()
    {
    	$cache_server =& cache_server();
        $key = md5('promotion_index_category');
        $gcate_list = $cache_server->get($key);

        if ($gcate_list === false)
        {
            //初始化条件
	    	$conditions =' where p.pr_status = 10 AND p.pr_stock>0';     	
	    	$sql = "SELECT tabb.cate_id_2,tabb.cate_name,count(tabb.cate_id_2) as num FROM(
	    	select g.cate_id_2,gy.cate_name from pa_promotion p
	    	left join pa_promotion_store_goods ps on ps.promotion_id=p.promotion_id
	    	left join pa_store_goods sg on sg.gs_id=ps.gs_id
	    	LEFT JOIN pa_goods g on g.goods_id = sg.goods_id
	    	LEFT join pa_gcategory gy on g.cate_id_2 = gy.cate_id";
	    	$gcate_list = $this->_promotion_mod->getAll($sql.$conditions." GROUP BY sg.store_id,g.goods_id order by cate_id_2) tabb group by cate_id_2");
            $cache_server->set($key, $gcate_list, 900);
        }
		return $gcate_list;
    }

}
?>