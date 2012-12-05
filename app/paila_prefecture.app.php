<?php
	class Paila_prefectureApp extends MallbaseApp
	{
		function index()
		{
			$goods_mod = &m('goods');
			$gcategory_mod = &m('gcategory');
			$goods_statistics_mod = &m('goodsstatistics');
			$pala_goods_mod = &m('pailagoods');
			//分页数
			$page_num = 20;
			$page = $this->_get_page($page_num);
			//全部分类及个数	
			$gcategory = $gcategory_mod->getAll('select cate_id,cate_name from pa_gcategory where parent_id = 0 and mall_type = 2 and store_id = 0 and if_show = 1');;
			foreach($gcategory as $k=>$v)
			{
				$num= $v['cate_id'];
				$gcategory[$k]['ids'] = $gcategory_mod->getAll("select count(*) from pa_goods g left join pa_gcategory gc on g.cate_id = gc.cate_id  where g.cate_id in (select gc1.cate_id from 
																pa_gcategory gc1 where g.if_show = 1 AND g.closed = 0 AND g.status = 1 and g.discount<=0.35 and g.discount>0 and gc1.parent_id in (select cate_id from pa_gcategory where mall_type=2 and parent_id ='$num'))");						
				$gcategory[$k]['nums'] = $gcategory[$k]['ids'][0]['count(*)'];	
			}		
			//URL处理
			$search_arr = parse_url($_SERVER['REQUEST_URI']);
    		$search_arr = explode('&', $search_arr['query']);
    		$uri_arr = array();
    		$arr = $arr1 = $arr2 = $arr3 = array();
			foreach ($search_arr as $k => $v)
	    	{
	    		$uri_arr = explode('=', $v);
	    		if ($uri_arr[0] != 'page' && $uri_arr[0] != 'use' && $uri_arr[0] != 'goods_name')
	    		{
	    			$arr2[$uri_arr[0]] = urldecode($uri_arr[1]);
	    		}
	    	}
	    	//类别搜索
	    	$conditions .= " g.if_show = 1 AND g.closed = 0 AND g.status = 1";
	    	$type = empty($_GET['type']) ? 0 : intval($_GET['type']);    	
	    	$this->assign('type',1);
	    	//积分价格查询
	    		$credit = empty($_GET['credit']) ? 0 : trim($_GET['credit']);
	    		$this->assign('credit',$credit);
	    		if('' !=$credit)
	    		{
	    			$pri_arr = explode('-',$credit);
	    			if(!$pri_arr[1])
	    			{
	    				$res['credit'] = array(
	    					'min' => $pri_arr[0],
	    					'max' => $pri_arr[1],
	    				);
	    			}else{
	    				if($pri_arr[0] < $pri_arr[1])
	    				{
	    					$res['credit'] = array(
	    						'min' => $pri_arr[0],
	    						'max' => $pri_arr[1],
	    					);
	    				}
	    			}
	    			$pmin = $pri_arr[0];
	    			$pmax = $pri_arr[1];
	    			$this->assign('pmin',$pmin);
	    			$this->assign('pmax',$pmax);
	    			if(!$pmax)
	    			{
	    				$conditons .= ' AND g.credit > ' .$pmin;
	    			}else{
	    				if($pmin < $pmax)
	    				{
	    					$conditions .= ' AND g.credit > ' . $pmin . ' AND g.credit <= ' . $pmax;
	    				}
	    			}
	    		}
			//排序
			$type = empty($_GET['type']) ? 0 : intval($_GET['type']);
			$cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET[cate_id]);
			$this->assign('cate_id',$cate_id);
			switch($type)
			{
				case 0:
					$conditions .=" and g.discount<=0.35 and g.discount>0";
					$this->assign('type',0);
					break;
				case 1:		
					$conditions .=" and g.discount<=0.35 and g.discount>0 and g.cate_id in (select gc1.cate_id from pa_gcategory gc1 where gc1.parent_id in (select cate_id from pa_gcategory where parent_id =$cate_id and mall_type = 2))";
					$this->assign('type',1);
					break;
			}
			$opt = empty($_GET['opt']) ? 0 : intval($_GET['opt']);
			$count = $goods_mod->getOne("select count(*) from pa_goods g where " .$conditions);
			$page['item_count'] = $count;
			switch($opt)
			{
				case 0:
					$goods_info = $goods_mod->getAll("select g.goods_name,g.discount,g.cprice,g.price,g.credit,g.mimage_url,g.add_time,g.price,s.sales,gs.stock,SUM(gs.stock),g.goods_id as gid from pa_goods g left join pa_goods_statistics s on g.goods_id = s.goods_id left join pa_goods_spec gs on g.goods_id = gs.goods_id where " . $conditions . " group by gs.goods_id order by s.sales desc limit " . $page['limit']);
					$this->assign('opt',0);
					break;
				case 1:	
					$goods_info = $goods_mod->getAll("select g.goods_name,g.discount,g.cprice,g.price,g.credit,g.mimage_url,g.add_time,g.price,s.sales,gs.stock,SUM(gs.stock),g.goods_id as gid from pa_goods g left join pa_goods_statistics s on g.goods_id = s.goods_id left join pa_goods_spec gs on g.goods_id = gs.goods_id where " . $conditions . " group by gs.goods_id order by g.add_time desc limit " . $page['limit']);
					$this->assign('opt',1);
					break;
				case 2:
					$goods_info = $goods_mod->getAll("select g.goods_name,g.discount,g.cprice,g.price,g.credit,g.mimage_url,g.add_time,g.price,s.sales,gs.stock,SUM(gs.stock),g.goods_id as gid from pa_goods g left join pa_goods_statistics s on g.goods_id = s.goods_id left join pa_goods_spec gs on g.goods_id = gs.goods_id where " . $conditions . " group by gs.goods_id order by g.price desc limit " . $page['limit']);
					$this->assign('opt',2);
					break;
				case 3:
					$goods_info = $goods_mod->getAll("select g.goods_name,g.discount,g.cprice,g.price,g.credit,g.mimage_url,g.add_time,g.price,s.sales,gs.stock,SUM(gs.stock),g.goods_id as gid from pa_goods g left join pa_goods_statistics s on g.goods_id = s.goods_id left join pa_goods_spec gs on g.goods_id = gs.goods_id where " . $conditions . " group by gs.goods_id order by g.price asc limit " .$page['limit']);
					$this->assign('opt',3);
					break;
			}
			foreach($goods_info as $k => $v)
			{
				$goods_info[$k]['mimage_url'] = IMAGE_URL.$v['mimage_url'];
			}
			$this->assign('index', 1);
			$search_index = 'index.php?' . http_build_query($arr2);
			$this->assign('search_index',$search_index);
			$this->_format_page($page);
			$this->assign('page_info',$page);
			$this->assign('goods_info',$goods_info);
			$this->assign('gcategory',$gcategory);
			$this->display("paila_prefecture.index.html");		
		}
	}
?>