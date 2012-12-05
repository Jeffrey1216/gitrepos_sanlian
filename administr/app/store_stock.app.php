<?php
	class Store_stockApp extends BackendApp
	{
		var $store_mod;
		var $goods_mod;
		var $store_goods_mod;
		var $store_order_goods_mod;
		function __construct()
		{
			$this->Store_stockApp();
		}
		function Store_stockApp()
		{
			parent::__construct();
			$this->store_mod = &m('store');
			$this->goods_mod = &m('goods');
			$this->store_goods_mod = &m('storegoods');
			$this->store_order_goods_mod = &m('storeordergoods');
		}
		//管理
		function index()
		{
			$search_options = array(
            'store_name'   => Lang::get('store_name'),
            'owner_name'   => Lang::get('owner_name'),
            'tel'   => Lang::get('tel'),
        	);
	        /* 默认搜索的字段是店铺名 */
	        $field = 'seller_name';       
	        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
	        $conditions = '1=1';
	        $conditions .= $this->_get_query_conditions(array(array(
	                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
	                'equal' => 'LIKE',
	                'name'  => 'search_name',
	            ),array(
	                'field' => 'store_type',
	                'equal' => '=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'state',
	                'equal' => '=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'add_time',
	                'name'  => 'add_time_from',
	                'equal' => '>=',
	                'handler'=> 'gmstr2time',
	            ),array(
	                'field' => 'end_time',
	                'name'  => 'add_time_to',
	                'equal' => '<=',
	                'handler'   => 'gmstr2time_end',
	            ),array(
	                'field' => 'order_amount',
	                'name'  => 'order_amount_from',
	                'equal' => '>=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'order_amount',
	                'name'  => 'order_amount_to',
	                'equal' => '<=',
	                'type'  => 'numeric',
	            ),
	        ));	
	        $page = $this->_get_page(20);
	        $page['item_count'] = $this->store_mod->getOne('select count(*) from pa_store where '.$conditions);
	        $store = $this->store_mod->find(array(
	        	'conditions' => $conditions,
	        	'limit'		 => $page['limit'],
	        	'count'		 => true,
	        ));	       
	        $this->_format_page($page);
	        $this->assign('store_type', array(
          				'0' => '直营店',
	        			'1' => '加盟店',
       					));    
       		$this->assign('state',array(
       					'0' => '未审核',
       					'1' => '已开启',
       					'2' => '已关闭',
       					));		
	        $this->assign('search_options', $search_options);
	        $this->assign('page_info',$page);
	        $this->assign('store',$store);
			$this->display('store_stock.index.html');
		}
		//店铺所进商品
		function store_goods()
		{
			$store_id = intval($_GET['id']);
			if($store_id === '')
			{
				$this->show_warning('此店铺不存在!');
				return;
			}
			$search_options = array(
            'sog.goods_name'   => Lang::get('goods_name'),
            'g.brand'   => Lang::get('brand_name'),
			'g.goods_number' => Lang::get('goods_number'),
			'g.unit'		   => Lang::get('unit'),
        	);
	        /* 默认搜索的字段是店铺名 */
	        $field = 'seller_name';       
	        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
	        $conditions = '1=1';
	        $conditions .= $this->_get_query_conditions(array(array(
	                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
	                'equal' => 'LIKE',
	                'name'  => 'search_name',
	            ),array(
	                'field' => 'add_time',
	                'name'  => 'add_time_from',
	                'equal' => '>=',
	                'handler'=> 'gmstr2time',
	            ),array(
	                'field' => 'end_time',
	                'name'  => 'add_time_to',
	                'equal' => '<=',
	                'handler'   => 'gmstr2time_end',
	            ),array(
	                'field' => 'order_amount',
	                'name'  => 'order_amount_from',
	                'equal' => '>=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'order_amount',
	                'name'  => 'order_amount_to',
	                'equal' => '<=',
	                'type'  => 'numeric',
	            ),
	        ));	
	        $page = $this->_get_page(20);	
	        $page['item_count'] = $this->store_goods_mod->getOne('select count(*) from pa_store_order_goods sog left join pa_store_goods sg on sog.goods_id = sg.goods_id left join pa_store 
	        											s on sg.store_id = s.store_id left join pa_goods g on sog.goods_id = g.goods_id where s.store_id ='.$store_id." AND $conditions " );
	        $goods = $this->store_order_goods_mod->getAll('select sog.goods_name,g.brand,g.goods_number,sog.specification,g.unit,g.zprice,sog.quantity from 
	        											pa_store_order_goods sog left join pa_store_goods sg on sog.goods_id = sg.goods_id left join pa_store 
	        											s on sg.store_id = s.store_id left join pa_goods g on sog.goods_id = g.goods_id where s.store_id ='.$store_id." AND $conditions ". "limit " . $page['limit']);
	        $this->assign('goods',$goods);
	        var_dump($conditions);
	        $this->_format_page($page);
	        $this->assign('search_options', $search_options);
	        $this->assign('page_info',$page);
			$this->display('store_stock.goods.html');
		}
	}
?>