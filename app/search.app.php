<?php

/* 定义like语句转换为in语句的条件 */
define('MAX_ID_NUM_OF_IN', 10000); // IN语句的最大ID数
define('MAX_HIT_RATE', 0.05);      // 最大命中率（满足条件的记录数除以总记录数）
define('MAX_STAT_PRICE', 10000);   // 最大统计价格
define('PRICE_INTERVAL_NUM', 5);   // 价格区间个数
define('MIN_STAT_STEP', 50);       // 价格区间最小间隔
define('NUM_PER_PAGE', 32);        // 每页显示数量
define('ENABLE_SEARCH_CACHE', true); // 启用商品搜索缓存
define('SEARCH_CACHE_TTL', 3600);  // 商品搜索缓存时间

class SearchApp extends MallbaseApp
{
	var $_gcategory_mod;
	var $_goods_mod;
    function __construct()
    {
        $this->SearchApp();
    }
    function SearchApp()
    {
        parent::__construct();
    	$this->_gcategory_mod = & m('gcategory');
    	$this->_goods_mod = & m('goods');
    }
	
    /* 搜索商品 */
    function index()
    {
        // 查询参数
        $param = $this->_get_query_param();
        //按品牌, 地区分类,并统计商品数量
        $stats = $this->_get_group_by_info($param, ENABLE_SEARCH_CACHE);   
        $this->assign('stats',$stats);
        if (empty($param))
        {
            header('Location: index.php?app=category&act=index');
            exit;
        }
        if (isset($param['cate_id']) && $param['layer'] === false)
        {
            $this->show_warning('no_such_category');
            return;
        }
        /* 筛选条件 */
        $this->assign('filters', $this->_get_filter($param));
		$mll_type=$param['mall_type'];
        /* 排序 */
        $orders = $this->_get_orders();   
        $this->assign('orders', $orders);
        /* 分页信息 */
        $page = $this->_get_page(NUM_PER_PAGE);
		/* 统计商品数量*/
        $count_goods = $this->_get_goods_num($param, ENABLE_SEARCH_CACHE);
        $page['item_count'] = $count_goods['total_count'];          
        $conditions = $this->_get_goods_conditions($param);
        $goods_mod  =& m('goods');
        $goods_list = $goods_mod->get_list(array(
            'conditions' => $conditions,
            'order'      => isset($_GET['order']) && isset($orders[$_GET['order']]) ? $_GET['order'] : '',
            'limit'      => $page['limit'],
        ));
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $order_goods_mod = & m("ordergoods"); //取得物品定单模型
        $order_mod = & m("order"); //取得定单模型
		$recommend_goods_info = array();
        $recommends = array(); //存放页面上的9个推荐商品
        foreach ($goods_list as $key => $goods)
        {
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $store_mod =& m('store');
            //图片加上图片服务器URL
            $goods_list[$key]['default_image'] = IMAGE_URL.$goods['default_image'];
            $goods_list[$key]['smimage_url'] = IMAGE_URL.$goods['smimage_url'];
            $goods_list[$key]['dimage_url'] = IMAGE_URL.$goods['dimage_url'];
            $goods_list[$key]['yimage_url'] = IMAGE_URL.$goods['yimage_url'];
            $goods_list[$key]['mimage_url'] = IMAGE_URL.$goods['mimage_url'];
            $goods_list[$key]['simage_url'] = IMAGE_URL.$goods['simage_url'];
            $goods_list[$key]['credit_image'] = $this->_view->res_base . '/images/' . $store_mod->compute_credit($goods['credit_value'], $step);
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
            $goods_list[$key]['grade_name'] = $sgrades[$goods['sgrade']];
            
            //当前商品评论为
            $comment_goods = $order_goods_mod->get(array('conditions' => 'goods_id='.$goods['goods_id']));
            if(!$comment_goods) {
            	$goods_list[$key]['comment'] = '';
            	$goods_list[$key]['memName'] = '';
            } else {
            	$goods_list[$key]['comment'] = empty($comment_goods['comment']) ? '卖家挺热情的，发货速度还不错' : $comment_goods['comment'];
            	$order_info = $order_mod->get(array('conditions' => 'order_id='.$comment_goods['order_id']));
            	$goods_list[$key]['memName'] = $order_info['buyer_name'];	
            }
            if($goods['is_best'] == 1) {
            	$recommend_goods_info[] = $goods; 
            }
        }
        $ids  = ecm_getcookie('goodsBrowseHistory');
        if(!empty($ids)){
         	$this->_get_history($num = 9);
        }
       	//调用随机方案
        $recommend_infos = $this->best_rand($recommend_goods_info);
        /* 处理获取到的数据 */
		$recommend_info = array_chunk($recommend_infos,3);
        $this->assign('recommend_infos',$recommend_info);
        $this->assign('goods_list', $goods_list);   
        /* 商品展示方式 */
        $display_mode = ecm_getcookie('goodsDisplayMode');
        if (empty($display_mode) || !in_array($display_mode, array('list', 'squares')))
        {
            $display_mode = 'squares'; // 默认格子方式
        }
        $this->assign('display_mode', $display_mode);

        /* 取得导航 */
        $this->assign('navs', $this->_get_navs());
        
        /* 当前位置 */
        $cate_id = isset($param['cate_id']) ? $param['cate_id'] : 0;
        
        $this->_curlocal($this->_get_goods_curlocal($cate_id));
        $gcategory_info = $this->_gcategory_mod->get(array('conditions' => 'cate_id='.$cate_id, 'fields' => 'cate_id,cate_name,mall_type'));
        $mall_ty=$gcategory_info["mall_type"];
        $this->assign('gcategory_info',$gcategory_info);
        //获取当前子分类
		if($gcategory_info){;
        $goods_sort = $this->_gcategory_mod->getAll('select cate_id,cate_name,parent_id,mall_type from pa_gcategory where  parent_id ='.$gcategory_info['cate_id']);
			
       	foreach ($goods_sort as $k => $v)
     	 {
            $cate_id=$v['cate_id'];
            $sql='select * from pa_gcategory where  parent_id ='."$cate_id";	
            $goods_sort[$k][]= $this->_gcategory_mod->getAll($sql);	
         }
		}
        $this->assign('goods_sort',$goods_sort);
        /* 评论块 */
        //定义评论数组
        $comments_info = array();
        //定义计数器
        $no = 0;
        foreach($goods_list as $k => $v) {
        	if(isset($goods_list[$k]['comment']) && $goods_list[$k]['comment'] != '' && $no < 6) {
        		$comments_info[] = $goods_list[$k];
        		$no++;
        	}
        }
        $this->assign("IMAGE_URL",IMAGE_URL);
        $this->assign('comments_info',$comments_info);
        /* 配置seo信息 */
        $this->_config_seo($this->_get_seo_info('goods', $cate_id));
        $this->assign('sales_info',$this->_get_agohot());
		//判断是否跳转农业专区
        if($mall_ty == 1 or $mll_type == 1){
        	$this->assign('cate_id',$param['cate_id']);
        	$this->display('agr_channel.html');		
        }else {
        	$this->display('pl_channel.html');
        }
       
    }
    
	private function getFirstCategory($cate_id)
    {
    	$gcategory_info = $this->_gcategory_mod->get($cate_id);
    	if (0 == $gcategory_info['parent_id'])
    	{
    		return $cate_id;
    	} else {
    		return $this->getFirstCategory($gcategory_info['parent_id']);
    	}
    	
    }
    
    /* 搜索店铺 */
    function store()
    {
        /* 取得导航 */
        $this->assign('navs', $this->_get_navs());

        /* 取得该分类及子分类cate_id */
        $cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        $cate_ids=array();
        $condition_id='';
        if ($cate_id > 0)
        {
            $scategory_mod =& m('scategory');
            $cate_ids = $scategory_mod->get_descendant($cate_id);
        }

        /* 店铺分类检索条件 */
        $condition_id=implode(',',$cate_ids);
        $condition_id && $condition_id = ' AND cate_id IN(' . $condition_id . ')';

        /* 其他检索条件 */
        $conditions = $this->_get_query_conditions(array(
            array( //店铺名称
                'field' => 'store_name',
                'equal' => 'LIKE',
                'assoc' => 'AND',
                'name'  => 'keyword',
                'type'  => 'string',
            ),
            array( //地区名称
                'field' => 'region_name',
                'equal' => 'LIKE',
                'assoc' => 'AND',
                'name'  => 'region_name',
                'type'  => 'string',
            ),
            array( //地区id
                'field' => 'region_id',
                'equal' => '=',
                'assoc' => 'AND',
                'name'  => 'region_id',
                'type'  => 'string',
            ),
            array( //商家用户名
                'field' => 'user_name',
                'equal' => 'LIKE',
                'assoc' => 'AND',
                'name'  => 'user_name',
                'type'  => 'string',
            ),
        ));

        $model_store =& m('store');
        $regions = $model_store->list_regions();
        $page   =   $this->_get_page(10);   //获取分页信息
        $stores = $model_store->find(array(
            'conditions'  => 'state = ' . STORE_OPEN . $condition_id . $conditions,
            'limit'   =>$page['limit'],
            'order'   => empty($_GET['order']) || !in_array($_GET['order'], array('credit_value desc')) ? 'sort_order' : $_GET['order'],
            'join'    => 'belongs_to_user,has_scategory',

            'count'   => true   //允许统计
        ));

        $model_goods = &m('goods');

        foreach ($stores as $key => $store)
        {
            //店铺logo
            empty($store['store_logo']) && $stores[$key]['store_logo'] = Conf::get('default_store_logo');

            //商品数量
            $stores[$key]['goods_count'] = $model_goods->get_count_of_store($store['store_id']);

            //等级图片
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $stores[$key]['credit_image'] = $this->_view->res_base . '/images/' . $model_store->compute_credit($store['credit_value'], $step);

        }
        $page['item_count']=$model_store->getCount();   //获取统计数据
        $this->_format_page($page);
        /* 当前位置 */
        $this->_curlocal($this->_get_store_curlocal($cate_id));
        $scategorys = $this->_list_scategory();
        $this->assign('stores', $stores);
        $this->assign('regions', $regions);
        $this->assign('cate_id', $cate_id);
        $this->assign('scategorys', $scategorys);
        $this->assign('page_info', $page);
        /* 配置seo信息 */
        $this->_config_seo($this->_get_seo_info('store', $cate_id));
        $this->display('search.store.html');
    }

    function groupbuy()
    {
        empty($_GET['state']) &&  $_GET['state'] = 'on';
        $conditions = '1=1';

        // 排序
        $orders = array(
            'group_id desc'          => Lang::get('select_pls'),
            'views desc'     => Lang::get('views'),
        );

        if ($_GET['state'] == 'on')
        {
            $orders['end_time asc'] = Lang::get('lefttime');
            $conditions .= ' AND gb.state ='. GROUP_ON .' AND gb.end_time>' . gmtime();
        }
        elseif ($_GET['state'] == 'end')
        {
            $conditions .= ' AND (gb.state=' . GROUP_ON . ' OR gb.state=' . GROUP_END . ') AND gb.end_time<=' . gmtime();
        }
        else
        {
            $conditions .= $this->_get_query_conditions(array(
                array(      //按团购状态搜索
                    'field' => 'gb.state',
                    'name'  => 'state',
                    'handler' => 'groupbuy_state_translator',
                )
            ));
        }
        $conditions .= $this->_get_query_conditions(array(
            array( //活动名称
                'field' => 'group_name',
                'equal' => 'LIKE',
                'assoc' => 'AND',
                'name'  => 'keyword',
                'type'  => 'string',
            ),
        ));
        $page = $this->_get_page(NUM_PER_PAGE);   //获取分页信息
        $groupbuy_mod = &m('groupbuy');
        $groupbuy_list = $groupbuy_mod->find(array(
            'conditions'    => $conditions,
            'fields'        => 'gb.group_name,gb.spec_price,gb.min_quantity,gb.store_id,gb.state,gb.end_time,g.default_image,default_spec,s.store_name',
            'join'          => 'belong_store, belong_goods',
            'limit'         => $page['limit'],
            'count'         => true,   //允许统计
            'order'         => isset($_GET['order']) && isset($orders[$_GET['order']]) ? $_GET['order'] : 'group_id desc',
        ));
        if ($ids = array_keys($groupbuy_list))
        {
            $quantity = $groupbuy_mod->get_join_quantity($ids);
        }
        foreach ($groupbuy_list as $key => $groupbuy)
        {
            $groupbuy_list[$key]['quantity'] = empty($quantity[$key]['quantity']) ? 0 : $quantity[$key]['quantity'];
            $groupbuy['default_image'] || $groupbuy_list[$key]['default_image'] = Conf::get('default_goods_image');
            $groupbuy['spec_price'] = unserialize($groupbuy['spec_price']);
            $groupbuy_list[$key]['group_price'] = $groupbuy['spec_price'][$groupbuy['default_spec']]['price'];
            $groupbuy['state'] == GROUP_ON && $groupbuy_list[$key]['lefttime'] = lefttime($groupbuy['end_time']);
        }
        $this->assign('state', array(
             'on' => Lang::get('group_on'),
             'end' => Lang::get('group_end'),
             'finished' => Lang::get('group_finished'),
             'canceled' => Lang::get('group_canceled'))
        );
        $this->assign('orders', $orders);
        // 当前位置
        $this->_curlocal(array(array('text' => Lang::get('groupbuy'))));
        $this->_config_seo('title', Lang::get('groupbuy') . ' - ' . Conf::get('site_title'));
        $page['item_count'] = $groupbuy_mod->getCount();   //获取统计数据
        $this->_format_page($page);
        $this->assign('nav_groupbuy', 1); // 标识当前页面是团购列表，用于设置导航状态
        $this->assign('page_info', $page);
        $this->assign('groupbuy_list',$groupbuy_list);
        $this->assign('recommended_groupbuy', $this->_recommended_groupbuy(2));
        $this->assign('last_join_groupbuy', $this->_last_join_groupbuy(2));
        $this->display('search.groupbuy.html');
    }

    // 推荐团购活动
    function _recommended_groupbuy($_num)
    {
        $model_groupbuy =& m('groupbuy');
        $data = $model_groupbuy->find(array(
            'join'          => 'belong_goods',
            'conditions'    => 'gb.recommended=1 AND gb.state=' . GROUP_ON . ' AND gb.end_time>' . gmtime(),
            'fields'        => 'group_id, goods.default_image, group_name, end_time, spec_price',
            'order'         => 'group_id DESC',
            'limit'         => $_num,
        ));
        foreach ($data as $gb_id => $gb_info)
        {
            $price = current(unserialize($gb_info['spec_price']));
            empty($gb_info['default_image']) && $data[$gb_id]['default_image'] = Conf::get('default_goods_image');
            $data[$gb_id]['lefttime']   = lefttime($gb_info['end_time']);
            $data[$gb_id]['price']      = $price['price'];
        }
        return $data;
    }

    // 最新参加的团购
    function _last_join_groupbuy($_num)
    {
        $model_groupbuy =& m('groupbuy');
        $data = $model_groupbuy->find(array(
            'join' => 'be_join,belong_goods',
            'fields' => 'gb.group_id,gb.group_name,gb.group_id,groupbuy_log.add_time,gb.spec_price,goods.default_image',
            'conditions' => 'groupbuy_log.user_id > 0',
            'order' => 'groupbuy_log.add_time DESC',
            'limit' => $_num,
        ));
        foreach ($data as $gb_id => $gb_info)
        {
            $price = current(unserialize($gb_info['spec_price']));
            empty($gb_info['default_image']) && $data[$gb_id]['default_image'] = Conf::get('default_goods_image');
            $data[$gb_id]['price']      = $price['price'];
        }
        return $data;
    }

                /* 取得店铺分类 */
    function _list_scategory()
    {
        $scategory_mod =& m('scategory');
        $scategories = $scategory_mod->get_list(-1,true);

        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getArrayList(0);
    }

    function _get_goods_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $gcategory_mod =& bm('gcategory');
            $parents = $gcategory_mod->get_ancestor($cate_id, true);
        }

        $curlocal = array(
            array('text' => LANG::get('all_categories'), 'url' => "index.php?app=search"),
        );
        foreach ($parents as $category)
        {
            $curlocal[] = array('text' => $category['cate_name'], 'url' => "index.php?app=search&cate_id=" . $category['cate_id']);
        }
        unset($curlocal[count($curlocal) - 1]['url']);

        return $curlocal;
    }

    function _get_store_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $scategory_mod =& m('scategory');
            $scategory_mod->get_parents($parents, $cate_id);
        }

        $curlocal = array(
            array('text' => LANG::get('all_categories'), 'url' => url('app=category&act=store')),
        );
        foreach ($parents as $category)
        {
            $curlocal[] = array('text' => $category['cate_name'], 'url' => url('app=search&act=store&cate_id=' . $category['cate_id']));
        }
        unset($curlocal[count($curlocal) - 1]['url']);
        return $curlocal;
    }
	
    /**
     * 取得查询参数（有值才返回）
     *
     * @return  array(
     *              'keyword'   => array('aa', 'bb'),
     *              'cate_id'   => 2,
     *              'layer'     => 2, // 分类层级
     *              'brand'     => 'ibm',
     *              'region_id' => 23,
     *              'price'     => array('min' => 10, 'max' => 100),
     *          )
     */
    function _get_query_param()
    {
        static $res = null;
        $mall_type = empty($_GET['mall_type']) ? 0 : intval($_GET['mall_type']);
    	$cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
    	$brand = empty($_GET['brand']) ? '' : trim($_GET['brand']);
    	$credit = empty($_GET['credit']) ? '' : trim($_GET['credit']);
    	$price = empty($_GET['price']) ? '' : trim($_GET['price']);
    	$region_id = empty($_GET['region_id']) ? 0 : intval($_GET['region_id']);
    	$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    	$search_arr = parse_url($_SERVER['REQUEST_URI']);
    	$search_arr = explode('&', $search_arr['query']);
    	$uri_arr = array();
    	$arr = array();
    	foreach ($search_arr as $k => $v)
    	{
    		$uri_arr = explode('=', $v);
    		$arr[$uri_arr[0]] = urldecode($uri_arr[1]);
    	}
    	$search_index = 'index.php?' . http_build_query($arr);

    	if ($res === null)
    	{
    		if (0 != $mall_type)
    		{
    			$res['mall_type'] = $mall_type;
    		}
    		
	    	if ('' != $keyword)
	        {
	          	$res['keyword'] = $keyword;
	          	$search_index .= '&keyword=' . $keyword;
	        }
	    	
	    	if (0 != $cate_id)
	    	{
	    		$res['cate_id'] = $cate_id;
	    		$gcategory_mod  =& bm('gcategory');
                $res['layer']   = $gcategory_mod->get_layer($cate_id, true);
                $search_index .= '&cate_id=' . $cate_id;
	    	} 
	    	
	    	if ('' != $brand)
	    	{
	    		$res['brand'] = $brand;
	    		$search_index .= '&brand=' . $brand;
	    	}
	    	
	    	if ('' != $credit)
	    	{
				$cre_arr = explode('-', $credit);
				if (!$cre_arr[1])
				{
					$res['credit'] = array(
						'min' => $cre_arr[0],
						'max' => NULL,
					);
				} else {
					if ($cre_arr[0] < $cre_arr[1]) {
						$res['credit'] = array(
							'min' => $cre_arr[0],
							'max' => $cre_arr[1],
						);
					}
				}
	    		$search_index .= '&credit=' . $credit;
	    	}
	    	
	    	if ('' != $price)
	    	{
	    		$pri_arr = explode('-', $price);
				if (!$pri_arr[1])
				{
					$res['price'] = array(
						'min' => $pri_arr[0],
						'max' => NULL,
					);
				} else {
					if ($pri_arr[0] < $pri_arr[1]) {
						$res['price'] = array(
							'min' => $pri_arr[0],
							'max' => $pri_arr[1],
						);
					}
				}
				$search_index .= '&price=' . $price;
	    	}
	    	
	    	if (0 != $region_id)
	    	{
	    		$res['region_id'] = $region_id;
	    		$search_index .= '&region_id=' . $region_id;
	    	}
    	}
    	$this->assign('search_index', $search_index);
        return $res;
       
    }

    /**
     * 取得过滤条件
     */
    function _get_filter($param)
    {
        static $filters = null;
        if ($filters === null)
        {
            $filters = array();
            if (isset($param['keyword']))
            {
            	if(is_array($param['keyword']))
            	{
                	$keyword = join(' ', $param['keyword']);
            	}else {
            		$keyword = $param['keyword'];
            	}
                $filters['keyword'] = array('key' => 'keyword', 'name' => LANG::get('keyword'), 'value' => $keyword);
            }
            isset($param['brand']) && $filters['brand'] = array('key' => 'brand', 'name' => LANG::get('brand'), 'value' => $param['brand']);
            if (isset($param['price']))
            {
                $min = $param['price']['min'];
                $max = $param['price']['max'];
                if ($min <= 0)
                {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => LANG::get('le') . ' ' . price_format($max));
                }
                elseif ($max <= 0)
                {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => LANG::get('ge') . ' ' . price_format($min));
                }
                else
                {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => price_format($min) . ' - ' . price_format($max));
                }
            }
            if (isset($param['credit']))
            {
                $min = $param['credit']['min'];
                $max = $param['credit']['max'];
                if ($min <= 0)
                {
                    $filters['credit'] = array('key' => 'credit', 'name' => LANG::get('credit'), 'value' => LANG::get('le') . ' ' . price_format($max));
                }
                elseif ($max <= 0)
                {
                    $filters['credit'] = array('key' => 'credit', 'name' => LANG::get('credit'), 'value' => LANG::get('ge') . ' ' . price_format($min));
                }
                else
                {
                    $filters['credit'] = array('key' => 'credit', 'name' => LANG::get('credit'), 'value' => price_format($min) . ' - ' . price_format($max));
                }
            }
        }
            
        return $filters;
    }

    /**
     * 取得查询条件语句//yoyo
     *
     * @param   array   $param  查询参数（参加函数_get_query_param的返回值说明）
     * @return  string  where语句
     */
    function _get_goods_conditions($param)//查询条件
    {
    	$mall_type = $param['mall_type'];
    	$cate_id = $param['cate_id'];
    	$brand = $param['brand'];
    	$credit = $param['credit'];
    	$price = $param['price'];
    	
    	$conditions = ' g.if_show = 1 AND g.closed = 0 AND s.state = 1 AND g.status = 1 AND sg.store_id ='.STORE_ID;
    	
    	if (isset($mall_type))
    	{
    		$conditions .= " AND gc.mall_type = " .$mall_type . ' '; 
    	}
    	
    	if (isset($param['keyword']))
        {
            $conditions .= $this->_get_conditions_by_keyword(urldecode($param['keyword']), ENABLE_SEARCH_CACHE);
        }
    	
    	if (0 != $cate_id)
    	{
    		$conditions .= " AND g.cate_id_{$param['layer']} = '" . $param['cate_id'] . "'";
    	}  	
    	
    	if ('' != $brand)
    	{
    		$conditions .= ' AND g.brand = "' . $brand . '"';
    	}
    	
    	if (!empty($credit))
    	{
			if (!$credit['max'])
			{
				$conditions .= ' AND g.credit > ' . $credit['min'];
			} else {
				if ($credit['min'] < $credit['max']) {
					$conditions .= ' AND g.credit > ' . $credit['min'] . ' AND g.credit <= ' . $credit['max'];
				}
			}
    		
    	}
    	
    	if (!empty($price))
    	{
			if (!$price['max'])
			{
				$conditions .= ' AND g.price > ' . $price['min'];
			} else {
				if ($price['min'] < $price['max']) {
					$conditions .= ' AND g.price > ' . $price['min'] . ' AND g.price <= ' . $price['max'];
				}
			}
    	}
    	return $conditions;
    }

    /**
     * 取得查询条件语句//xiaoyu
     *
     * @param   array   $param  查询参数（参加函数_get_query_param的返回值说明）
     * @return  string  where语句
     */
    function _get_screen_conditions($param) {//查询条件

		$mall_type = $param['mall_type'];
		$cate_id = $param['cate_id'];
		$param['layer'] = trim($param['layer']);
		
		$conditions = ' g.if_show = 1 AND g.closed = 0 AND s.state = 1 AND g.status = 1 ';

		if (isset($mall_type)) {
			$conditions .= " AND gc.mall_type = " . $mall_type . ' ';
		}
		if (0 != $cate_id && !empty($param['layer'])) {
			$conditions .= " AND g.cate_id_{$param['layer']} = '" . $param['cate_id'] . "'";
		}
		return $conditions;
	}
    /**
     * 根据查询条件取得分组统计信息
     *
     * @param   array   $param  查询参数（参加函数_get_query_param的返回值说明）
     * @param   bool    $cached 是否缓存
     * @return  array(
     *              'total_count' => 10,
     *              'by_category' => array(id => array('cate_id' => 1, 'cate_name' => 'haha', 'count' => 10))
     *              'by_brand'    => array(array('brand' => brand, 'count' => count))
     *              'by_region'   => array(array('region_id' => region_id, 'region_name' => region_name, 'count' => count))
     *              'by_price'    => array(array('min' => 10, 'max' => 50, 'count' => 10))
     *          )
     */
    function _get_group_by_info($param, $cached)
    {
    	$data = false;
        if ($cached)
        {
            $cache_server =& cache_server();
            $key = 'group_by_info_' . var_export($param, true);
            $data = $cache_server->get($key);
        }
		$data = false;
        if ($data === false)
        {
            $data = array(
                'total_count' => 0,
                //'by_category' => array(),
                'by_brand'    => array(),
                'by_price'    => array(),
            	'by_credit'   => array(),
            );
			
            $goods_mod =& m('goods');
            $store_mod =& m('store');
            $store_goods_mod = &m('storegoods');
            $gcategory_mod = & m('gcategory');
            $table = " {$goods_mod->table} g LEFT JOIN {$store_goods_mod->table} sg ON sg.goods_id = g.goods_id " .
           		" LEFT JOIN {$store_mod->table} s ON sg.store_id = s.store_id " .
            	" LEFT JOIN {$gcategory_mod->table} gc on g.cate_id = gc.cate_id " ;
            $conditions = $this->_get_screen_conditions($param);//Anan	
            $sql = "SELECT COUNT(*) FROM {$table} WHERE" . $conditions;;
            $total_count = $goods_mod->getOne($sql);

            if ($total_count > 0)
            {
                $data['total_count'] = $total_count;
                /* 按分类统计 */
                $cate_id = isset($param['cate_id']) ? $param['cate_id'] : 0;
                $sql = "";
                if ($cate_id > 0)
                {
                    $layer = $param['layer'];
                    if ($layer < 4)
                    {
                        $sql = "SELECT g.cate_id_" . ($layer + 1) . " AS id, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.cate_id_" . ($layer + 1) . " > 0 GROUP BY g.cate_id_" . ($layer + 1) . " ORDER BY count DESC";
                    }
                }
                else
                {
                    $sql = "SELECT g.cate_id_1 AS id, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.cate_id_1 > 0 GROUP BY g.cate_id_1 ORDER BY count DESC";
                }
				
                if ($sql)
                {
                    $category_mod =& bm('gcategory');
                    $children = $category_mod->get_children($cate_id, true);
                    $res = $goods_mod->db->query($sql);
                    while ($row = $goods_mod->db->fetchRow($res))
                    {
                        $data['by_category'][$row['id']] = array(
                            'cate_id'   => $row['id'],
                            'cate_name' => $children[$row['id']]['cate_name'],
                            'count'     => $row['count']
                        );
                    }
                }
				
                /* 按品牌统计 */
                $sql = "SELECT g.brand, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.brand > '' GROUP BY g.brand ORDER BY count DESC";
                $by_brands = $goods_mod->db->getAllWithIndex($sql, 'brand');
                
                /* 滤去未通过商城审核的品牌 */
                if ($by_brands)
                {
                    $m_brand = &m('brand');
                    $brand_conditions = db_create_in(addslashes_deep(array_keys($by_brands)), 'brand_name');
                    $brands_verified = $m_brand->getCol("SELECT brand_name FROM {$m_brand->table} WHERE " . $brand_conditions . ' AND if_show=1');
                    foreach ($by_brands as $k => $v)
                    {
                        if (!in_array($k, $brands_verified))
                        {
                            unset($by_brands[$k]);
                        }
                    }
                }
                $data['by_brand'] = $by_brands;
                

                /* 按价格统计 */
                
                if ($total_count > 0)//ww
                {
                        $data['by_price'][0] = array(
                            'min'   => 0,
                            'max'   => 100,
                        );
                        $data['by_price'][1] = array(
                            'min'   => 100,
                            'max'   => 200,
                        );
                        $data['by_price'][2] = array(
                            'min'   => 200,
                            'max'   => 500,
                        );
                        $data['by_price'][3] = array(
                            'min'   => 500,
                            'max'   => 800,
                        );
                        $data['by_price'][4] = array(
                            'min'   => 800,
                        );
                }
                
                //按积分统计
                
            	if ($total_count > 0)//ww
                {

                        $data['by_credit'][0] = array(
                            'min'   => 0,
                            'max'   => 5,
                        );
                        $data['by_credit'][1] = array(
                            'min'   => 5,
                            'max'   => 10,
                        );
                        $data['by_credit'][2] = array(
                            'min'   => 10,
                            'max'   => 20,
                        );
                        $data['by_credit'][3] = array(
                            'min'   => 20,
                            'max'   => 30,
                        );
                        $data['by_credit'][4] = array(
                            'min'   => 30,
                            'max'   => 50,
                        );
                        $data['by_credit'][5] = array(
                            'min'   => 50,
                        	'max'	=> 100,
                        );
                        $data['by_credit'][5] = array(
                            'min'   => 100,
                        );
                }
            }
			
            if ($cached)
            {
                $cache_server->set($key, $data, SEARCH_CACHE_TTL);
            }
        }
 		return $data;
    }

    /**
     * 根据关键词取得查询条件（可能是like，也可能是in）
     *
     * @param   array       $keyword    关键词
     * @param   bool        $cached     是否缓存
     * @return  string      " AND (0)"
     *                      " AND (goods_name LIKE '%a%' AND goods_name LIKE '%b%')"
     *                      " AND (goods_id IN (1,2,3))"
     */
    function _get_conditions_by_keyword($keyword, $cached)
    {
        $conditions = false;
		if(is_array($keyword)){	
	        if ($cached)
	        {
	            $cache_server =& cache_server();
	            $key1 = 'query_conditions_of_keyword_' . join("\t", $keyword);
	            $conditions = $cache_server->get($key1);
	        	
	        }
		}else {
			
			if ($cached)
		        {
		            $cache_server =& cache_server();
		            $key1 = 'query_conditions_of_keyword_' .  $keyword;
		            $conditions = $cache_server->get($key1);
		        }
		}
        if ($conditions === false)
        {
        	
            /* 组成查询条件 */
            $conditions = array();
	            if(is_array($keyword)){
	            foreach ($keyword as $word)
	            {
	                $conditions[] = "g.goods_name LIKE '%{$word}%'";
	            }
	            $conditions = join(' AND ', $conditions);
	             
            }else{
            	$conditions = "g.goods_name LIKE '%{$keyword}%'";
            		
            }
            /* 取得满足条件的商品数 */
            $goods_mod =& m('goods');
            	
            $sql = "SELECT COUNT(*) FROM {$goods_mod->table} g WHERE " . $conditions;
            $current_count = $goods_mod->getOne($sql);
           
            if ($current_count > 0)
            {
                if ($current_count < MAX_ID_NUM_OF_IN)
                {
                    /* 取得商品表记录总数 */
                    $cache_server =& cache_server();
                    $key2 = 'record_count_of_goods';
                    $total_count = $cache_server->get($key2);
                    if ($total_count === false)
                    {
                        $sql = "SELECT COUNT(*) FROM {$goods_mod->table}";
                        $total_count = $goods_mod->getOne($sql);
                        $cache_server->set($key2, $total_count, SEARCH_CACHE_TTL);
                    }

                    /* 不满足条件，返回like */
                    if (($current_count / $total_count) < MAX_HIT_RATE)
                    {
                        /* 取得满足条件的商品id */
                        $sql = "SELECT goods_id FROM {$goods_mod->table} g WHERE " . $conditions;
                        $ids = $goods_mod->getCol($sql);
                        $conditions = 'g.goods_id' . db_create_in($ids);
                    }
                }
            }
            else
            {
                /* 没有满足条件的记录，返回0 */
                $conditions = "0";
            }

            if ($cached)
            {
                $cache_server->set($key1, $conditions, SEARCH_CACHE_TTL);
            }
        }
        return ' AND (' . $conditions . ')';
    }

    /* 商品排序方式 */
    function _get_orders()
    {
        return array(
            ''                  => Lang::get('select_pls'),
            'sales desc'        => Lang::get('sales_desc'),
            'credit_value desc' => Lang::get('credit_value_desc'),
            'price asc'         => Lang::get('price_asc'),
            'price desc'        => Lang::get('price_desc'),
            'views desc'        => Lang::get('views_desc'),
            'add_time desc'     => Lang::get('add_time_desc'),
        );
    }
    
    function _get_seo_info($type, $cate_id)
    {
        $seo_info = array(
            'title'       => '',
            'keywords'    => '',
            'description' => ''
        );
        $parents = array(); // 所有父级分类包括本身
        switch ($type)
        {
            case 'goods':                
                if ($cate_id)
                {
                    $gcategory_mod =& bm('gcategory');
                    $parents = $gcategory_mod->get_ancestor($cate_id, true);
                    $parents = array_reverse($parents);
                }
                $filters = $this->_get_filter($this->_get_query_param());
                foreach ($filters as $k => $v)
                {
                    $seo_info['keywords'] .= $v['value']  . ',';
                }
                break;
            case 'store':
                if ($cate_id)
                {
                    $scategory_mod =& m('scategory');
                    $scategory_mod->get_parents($parents, $cate_id);
                    $parents = array_reverse($parents);
                }
        }
        
        foreach ($parents as $key => $cate)
        {
            $seo_info['title'] .= $cate['cate_name'] . ' - ';
            $seo_info['keywords'] .= $cate['cate_name']  . ',';
            if ($cate_id == $cate['cate_id'])
            {
                $seo_info['description'] = $cate['cate_name'] . ' ';
            }
        }
        $seo_info['title'] .= Lang::get('searched_'. $type) . ' - ' .Conf::get('site_title');
        $seo_info['keywords'] .= Conf::get('site_title');
        $seo_info['description'] .= Conf::get('site_title');
        return $seo_info;
    }

	//清空历史记录
	    public function truncateHistory() {
	    	if(isset($_GET['id'])) {
	    		ecm_setcookie('goodsBrowseHistory', null);
	    		header("Location:index.php?app=goods&id=".$_GET['id']);
	    	} else {
	    		return ;
	    	}
	    	
	    }
	    /* 取得浏览历史 */
	    function _get_history($num = 9)
	    {
	        $goods_list = array();
	        $goods_ids  = ecm_getcookie('goodsBrowseHistory');
	        if ($goods_ids)
	        {
	        	$goods_mod = & m('goods');
	            $rows[] = $goods_mod->find(array(
	                'conditions' => 'store_goods.gs_id in ('.$goods_ids.')',
	                'fields'     => 'g.goods_name,g.default_image,g.price,g.simage_url,store_goods.gs_id',
	            	'join'		 =>	'belongs_to_storegoods,g.goods',
	            ));
	         foreach ($rows[0] as $key1=>$val1)
	         {
	         	$row[$val1['gs_id']] = $val1;
	         }
	         $rows=$row;
	         $goods_ids  = explode(',', $goods_ids);
	         foreach ($goods_ids as $goods_id)
	            {
	                if (isset($rows[$goods_id]))
	                {
	                    empty($rows[$goods_id]['default_image']) && $rows[$goods_id]['default_image'] = Conf::get('default_goods_image');
	                    $goods_list[] = $rows[$goods_id];
	                }
	            }
	        }
	        if (count($goods_ids) > $num)
	        {
	            unset($goods_ids[0]);
	        }
	        ecm_setcookie('goodsBrowseHistory', join(',', array_unique($goods_ids)));
	        foreach($goods_list as $k => $v) {
	        	$goods_list[$k]['simage_url'] = IMAGE_URL . $v['simage_url'];
	        	//break;
	        }
	        $goods_ls = array_reverse($goods_list);
	        $this->assign('list', $goods_ls);
	        //return $goods_list;
	    }
	    /*通过JSON方式取出二级分类的信息*/
	    function edit(){
	    	$scategory_mod =& m('scategory');
	    	$cate_id = empty($_GET['cat']) ? 0 : intval($_GET['cat']);
	    	
	    	if (0 == $cate_id)
	    	{
	    		$this->json_error("Error");
	    		return;
	    	}
	    	
	    	$sql="select * from pa_gcategory where parent_id=" . $cate_id;	
	    	//var_dump($sql);
	    	$tegory=$scategory_mod->getAll($sql);	    	
	    	$data= $this->json_result($tegory);
	    	return $data;	    	
	    }
	    function _get_navigation()
	    {
	    	$_gcategroy_mod = & m('gcategory');
	    	$navigation = $_gcategroy_mod->getAll('select cate_name,cate_id from  pa_gcategory where parent_id=0 AND mall_type=3');
	    	//var_dump($navigation);
	    	return  $navigation;
	    }
	   	//销售排行
	    function _get_agohot()
	    {	    	
	    	 $goods_mod = &m('goods'); 
	         $sales_goods = $goods_mod->getAll('SELECT g.goods_id, g.goods_name , g.simage_url,store_goods.gs_id,g.goods_id FROM pa_store_goods store_goods LEFT JOIN pa_goods g ON g.goods_id = store_goods.goods_id LEFT JOIN pa_goods_statistics goods_statistics ON g.goods_id=goods_statistics.goods_id WHERE if_show = 1 AND store_goods.store_id = '.STORE_ID.' AND closed = 0 GROUP BY g.goods_id  ORDER BY sales LIMIT 10');
	    	 foreach($sales_goods as $k1 => $v1){
	         	$sales_goods[$k1]['simage_url'] = IMAGE_URL.$v1['simage_url'];
	         }
			return $sales_goods;
	    }
	    function _get_goods_num($param, $cached)
	    {
	    	$data = false;
	        if ($cached)
	        {
	            $cache_server =& cache_server();
	            $key = 'group_by_info_' . var_export($param, true);
	            $data = $cache_server->get($key);
	        }
			$data = false;
	        if ($data === false)
	        {
	            $data = array(
	                'total_count' => 0,
	                'by_brand'    => array(),
	                'by_price'    => array(),
	            	'by_credit'   => array(),
	            );
				
	            $goods_mod =& m('goods');
	            $store_mod =& m('store');
	            $store_goods_mod = &m('storegoods');
	            $gcategory_mod = & m('gcategory');
	            $table = " {$goods_mod->table} g LEFT JOIN {$store_goods_mod->table} sg ON sg.goods_id = g.goods_id " .
	           		" LEFT JOIN {$store_mod->table} s ON sg.store_id = s.store_id " .
	            	" LEFT JOIN {$gcategory_mod->table} gc on g.cate_id = gc.cate_id " ;
	            $conditions = $this->_get_goods_conditions($param);//Anan
	            $sql = "SELECT COUNT(*) FROM {$table} WHERE" . $conditions." group by g.goods_id ";
	            $total_count = $goods_mod->getAll($sql);
		        $count = count($total_count);
	            $data['total_count'] = $count;			
	            if ($cached)
	            {
	                $cache_server->set($key, $data, SEARCH_CACHE_TTL);
	            }
	        }
	 		return $data;
	    }
	    /***
	     * 推荐商品随机方案
	     * @author xiaoyu 
	     ****/
	    function best_rand($data)
	    {
	    	//如果当前推荐商品数目大于9个, 就随机取出9个
	        if(count($data) >= 9) {
	            $keys = array_rand($data,9);
	        	if($keys && $keys !== null) {
		        	foreach($keys as $key) {
		        		$recommends[] = $data[$key];
		        	}    
        		}
	        } else {
	            $recommends = $data;
				$sql = "select gs.gs_id,g.goods_name,g.dimage_url,g.price,g.credit from pa_store_goods gs left join pa_goods g on gs.goods_id=g.goods_id where g.status =1 and gs.store_id = ".STORE_ID." GROUP BY g.goods_id limit 0,50";
	            $rand_all_goods = $this->_goods_mod->getAll($sql);
	            //补齐9款推荐商品
	            $best_NUM = 9; //初始化变量
	            $count = count($data);
	            $rand_num = abs($best_NUM - $count);
	            $best_info = array_rand($rand_all_goods,$rand_num);
	            if($rand_num != 1)
	            {
	            	if($best_info && $best_info !== null){
			            foreach($best_info as $_k) {
			        		$recommends[] = $rand_all_goods[$_k];
			        	} 
	            	}
	            }else {
	            	$recommends[] = $rand_all_goods[$best_info];
	            }
	        }
			return $recommends;
	    }
}

?>
