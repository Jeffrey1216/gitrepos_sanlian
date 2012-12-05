<?php
/*
 *  订单分派控制器
 *  
 *  @author 张壮
 * */
class AllocateApp extends BackendApp
{
	var $_allocate_mod;
	var $_order_mod;
	var $_order_extm_mod;
	var $_store_mod;
	var $_region_mod;
	function __construct()
	{
		$this->AllocateApp();
	}
	function AllocateApp()
	{
		parent::BackendApp();
		$this->_order_mod=& m('order');
		$this->_order_extm_mod=& m('orderextm');
		$this->_allocate_mod =& m('allocate');
		$this->_store_mod =& m('store');
		$this->_region_mod =& m('region');
	}
	/*显示*/
/*	function index(){
		$page = $this->_get_page(10);//获取分页
		//更新排序
		if (isset($_GET['sort']) && isset($_GET['order']))
		{
			$sort=strtolower(trim($_GET['sort']));
			$order=strtolower(trim($_GET['order']));
			if (!in_array($order, array('asc','desc')))
			{
			$sort='add_time';
			$order='desc';
			}
		}
		else
		{
			$sort='add_time';
			$order='desc';
		}
        //拼装sql，统计总数
        $count_sql = "select count(*) from pa_order o,pa_order_extm e where o.order_id=e.order_id and o.seller_id=3 ";
        $page['item_count'] = $this->_order_mod->getOne($count_sql); //获取统计的数据
        $this->_format_page($page);//格式化分页信息
        //拼装sql，查询每页显示数据
        $sql="select o.order_id,o.goods_amount, o.order_sn, o.add_time,o.assign_store_id, e.region_name from pa_order o,pa_order_extm e where o.order_id=e.order_id and o.seller_id=3 order by $sort $order limit $page[limit]";
        $aa=$this->_order_mod->getAll($sql);
        //dump($page);
        $this->assign('allocate',$aa);
        //$this->assign('orders',$page['item_count']);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
		$this->display('allocate.index.html');
	}*/
	function index() {
	    $order_info = $this->_order_mod->find();
	    $this->assign("order" , $order_info); // 分类 
	    $assign_store_id=$_GET['assign_store_id'];
	    $store_id = $_GET['store_id'];
	    //var_dump($assign_store_id);
	    //$field = 'seller_name';
	    //array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
	    $conditions=$this->_get_query_conditions(array(
	        array(
	        	'filed' => 'assign_store_id',
	            'name' => 'assign_store_id',
                'equal' => '=',
                'type'  => 'numeric',
	        ),
	    	array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),
	    ));
	    //var_dump($conditions);
	    $model_order =& m('order');
	    $model_allocate = & m('allocate');
        $page   =   $this->_get_page(10);    //获取分页信息
		if (isset($_GET['sort']) && isset($_GET['order']))
	        {
	            $sort  = strtolower(trim($_GET['sort']));
	            $order = strtolower(trim($_GET['order']));
	            if (!in_array($order,array('asc','desc')))
	            {
	             $sort  = 'add_time';
	             $order = 'desc';
	            }
	        }
	    else
	        {
	            $sort  = 'add_time';
	            $order = 'desc';
	        }
	    $orders = $model_order->find(array(
            'conditions'    => 'seller_id=3' . $conditions, // . o.seller_id == 3,
	        //'conditions'  => 'store_id=0' . $conditions,
	        'join'    => 'has_orderextm',
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        )); //找出所有商城的合作伙伴
        //var_dump($assign_store_id);
         /*$this->assign('assign_store_list', array(
            1 => Lang::get('yes'),
            0 => Lang::get('no'),
        ));*/
        //var_dump($orders);
        $page['item_count'] = $model_order->getCount();   //获取统计的数据
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('orders', $orders);
        //var_dump($orders);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display("allocate.index.html");
    }
/*	function index(){
		$allocate_info = $this->_allocate_mod->find();
	    $this->assign("allocate" , $allocate_info); // 分类 
	    $conditions = ' 1 = 1';
	    $page_num = 1;
	    $assign_store_id = empty($_GET['assign_store_id']) ? 0 : intval($_GET['assign_store_id']);
    	$add_time = empty($_GET['add_time']) ? 0 : intval($_GET['add_time']);
		switch($assign_store_id) {
			case 1: $conditions .= " AND assign_store_id = '0' "; break;
			case 2: $conditions .= " AND assign_store_id != '0' "; break;
			//case 0: $this->assign('assign_store_id',0); ;break;
			default : $this->show_warning("搜索条件出错! 位置 question type!");
		}
		if($add_time != 0) {
			$conditions .= " AND class_id = " . $add_time;	
			$this->assign("add_time" , $add_time);
		}
		$page = $this->_get_page($page_num);
    	$page['item_count'] = $this->getQuestion($conditions,$page,1);
    	$list = $this->getQuestion($conditions,$page);
        $this->_format_page($page);
        $this->assign('list',$list);
    	//获取分页显示条数

        //将分页信息传递给视图，用于形成分页条
        $this->assign('page_info', $page);
        //var_dump($a);
        $this->display("allocate.index.html");
	}
	public function getQuestion($conditions , $page , $is_count = 0) {
		if($is_count == 0) { // 不统计数目,也就是直接返回记录
			$list = $this->_allocate_mod->find(array(
				'conditions' => 'seller_id=3' .$conditions,
				'limit' => $page['limit'],
				'join'	=> 'has_orderextm',
				'order'	=> 'order_id'
			));
			return $list;
		} else {
			$count = $this->_allocate_mod->getOne("select count(*) from pa_order o left join pa_order_extms e on o.class_id = e.class_id  where ".$conditions);
			return $count;
		}
	}*/
	function view(){
		//获取查看订单信息的id
		if (isset($_GET['order_id'])){
			$order_id=$_GET['order_id'];
			//var_dump($order_id);
			$viewSQL="select a.order_sn,a.status,a.assign_store_id,a.add_time,b.goods_name,b.price,b.quantity,c.region_name from pa_order a,pa_order_goods b,pa_order_extm c where a.order_id=b.order_id and b.order_id=c.order_id and b.order_id=c.order_id and a.order_id='$order_id'";
			$view=$this->_order_mod->getAll($viewSQL);
			
			$this->assign('view',$view);
			//var_dump($view);
		}
		$this->display("allocate.view.html");
	}
/*
 *   分派管理
 * 
 *  @author 张壮
 * 
 * */
	function add()
	{
		if(!IS_POST) {
			
			//获取订单
			$order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
			if($order_id == 0) {
				$this->show_warning("unsettled_required");
				return;
			}
			$store_info = $this->getStoreInfo($order_id);
			//var_dump($store_info);
			if(is_array($store_info)) {
				$this->assign("store_info",$store_info);
			} else {
				$this->assign("store_info",0);
			}
			//省市县三级联动
			$this->assign('site_url', site_url());
			$this->assign('order_id',$order_id);
			$this->assign('regions', $this->_region_mod->get_options(0));
			/* 导入jQuery的表单验证插件 */
			$this->import_resource(array('script' => 'mlselection.js,jquery.plugins/jquery.validate.js'));
			$this->display('allocate.form.html');
		} else { //提交定单后
			header('location:index.php?app=allocate');
		}
	}

    //店铺查询
    function searchStore(){
    	$region_id = empty($_GET['region_id']) ? 0 : intval($_GET['region_id']);
    	$order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
    	if($order_id == 0) {
			$this->json_error('未提供定单信息！');
			return;
		}
		$store_info = $this->getStoreInfo($order_id , $region_id);
   	 	if(is_array($store_info)) {
			$this->json_result(array(
           		'store_info'  =>  $store_info,                      //返回店铺信息
			));
		} else if($store_info == -1) {
			$this->json_error('未发现有需求商品的店铺！');
			return;
		} else {
			$this->json_error('未搜索到店铺！');
			return;
		}
		
  	}
  	//订单信息
  	function getStoreInfo($order_id,$region_id = 0) {
  		//定单商品信息
		$order_info = $this->_order_mod->getAll("select o.order_id,o.order_sn,o.status,o.assign_store_id,o.add_time,o.goods_amount,oe.region_id,oe.region_name,o.order_sn,og.goods_id,og.goods_name,og.price,og.spec_id,og.quantity from pa_order o left join pa_order_extm oe on o.order_id=oe.order_id left join pa_order_goods og on o.order_id=og.order_id where o.order_id={$order_id}");
		//处理定单中商品信息
		//var_dump($order_info);
		$this->assign('orderinfo',$order_info);
		$region_id = $region_id != 0 ? $region_id : intval($order_info[0]['region_id']);
		/**
		 *	自动获取附近商户
		 */
		//var_dump($region_id);
		//定单商品信息数组
		$goods_info = array();
		$store_info = array(); //外部数组，确定数组作用域
		while($region_id != 0) {
			//查找店铺
			$searchRegionSql = "select * from pa_store where store_id <> 3 and  region_id={$region_id}";
			$store_info = $this->_store_mod->getAll($searchRegionSql);
			if(!empty($store_info)) {
				break;
			} else {
				$region_info = $this->_region_mod->getRow("select * from pa_region where region_id={$region_id}");
				$region_id = $region_info['parent_id']; 
			}
		}
		if(!empty($store_info)) {
			$true_store_info = array(); // 有商品的店铺
			
			foreach($store_info as $k => $v) {
				$is_true = true;
				$store_id = $v['store_id'];
				foreach($order_info as $_k => $_v) {
					$searchOrderGoodsSQL = 'select * from pa_store s left join pa_store_goods pg on s.store_id=pg.store_id WHERE pg.goods_id= '.$_v['goods_id'].' AND pg.spec_id= ' . $_v['spec_id'] . ' and pg.stock >= ' . $_v['quantity'] . ' and s.store_id = ' . $store_id;
					$goods = $this->_store_mod->getRow($searchOrderGoodsSQL);
					if(empty($goods)) {
						$is_true = false;
						break;
					}
				}
				if($is_true) {
					$true_store_info[] = $v;
					$true_store_info[] = $v;
				}
			}
			if(!empty($true_store_info)) {//有自动搜索到店铺，并且店铺内有定单中的商品
				//$this->assign("true_store_info",$true_store_info);
				return $true_store_info;
			} else { //自动搜索到店铺， 但店铺没有商品
				return -1;
			}
		} else {
			//自动搜索没有搜索到
			return -2;
		}
  	}
  		/*搜索功能*/
/*	function search()
	{
		
		$statue=$_POST['stu'];
		$time1=$_POST['add_time_from'];
		$time2=$_POST['add_time_to'];
		//var_dump($statue);exit();
		//检查搜索条件是否赋值
		if($statue==2 and !empty($time1) and !empty($time2))
		{
			 $sql="select o.add_time,o.order_sn,e.region_name,o.assign_store_id from pa_order o,pa_order_extm e  where
		     o.seller_id=3 and FROM_UNIXTIME(o.add_time) BETWEEN  '$time1'  AND  '$time2' and e.order_id=o.order_id";
	         $arr=$this->_order_mod->getall($sql);
	         $this->assign('allocate',$arr);
		}
			//判断分配状态，$statue为0为未分派状态,不为0则是分派状态
		elseif($statue==0)
		{
		     $sql="select o.add_time,o.order_sn,e.region_name,o.assign_store_id from pa_order o,pa_order_extm e  where
		     o.seller_id=3 and o.assign_store_id=0 or FROM_UNIXTIME(o.add_time) BETWEEN  '$time1'  AND  '$time2' and e.order_id=o.order_id";
	         $arr=$this->_order_mod->getall($sql);
	         var_dump($arr);
	         $this->assign('allocate',$arr);
         //如果搜索不到值，责提示商品不存在
         if(!$arr)
         {
         	$this->show_warning("您搜索的商品不存在 ! 请重新搜索!");exit();
         }
         }
         //如果分配，责执行else
		else 
		{
		  $sql="select o.add_time,o.order_sn,e.region_name,o.assign_store_id from pa_order o,pa_order_extm e  where
	      o.seller_id=3 and e.order_id=o.order_id";
          $arr=$this->_order_mod->getall($sql);         
	      $this->assign('allocate',$arr);	
		 }
		
	
	 $this->display("allocate.index.html");
	}*/
  	/*function search(){
  		$order_info = $this->_order_mod->find();
  		$this->assign("order_infos" , $order_info); // 分类 
  		$conditions = ' 1 = 1';
	    $page_num = 3;
	    $assign_store_id=empty($_GET['assign_store_id']) ? 2 : intval($_GET['assign_store_id']);
	    $add_time=empty($_GET['add_time']) ? '' : trim($_GET['add_time']);
	    switch ($assign_store_id){
	    	case 0: $conditions .= " AND assign_store_id = 0 ". ' . 未分派.';break;
	    	case 1: $conditions .= " AND assign_store_id = 1 ". ' . 已分派.';break;
	    	case 2: $this->assign('assign_store_id',3);break;
			default : $this->show_warning("搜索条件出错! 位置 assign_store_id!");
	    }
  	    if($add_time != '') {
			$conditions .= " AND add_time = " . $add_time;
			$this->assign("stem" , $add_time);
		}
		$page = $this->_get_page($page_num);
    	$page['item_count'] = $this->getQuestion($conditions,$page,1);
    	$list = $this->getQuestion($conditions,$page);
        $this->_format_page($page);
        $this->assign('list',$list);
    	//获取分页显示条数

        //将分页信息传递给视图，用于形成分页条
        $this->assign('page_info', $page);
	    $this->display("allocate.index.html");
  	}*/

	/*public function getAllocate($conditions , $page , $is_count = 0) {
		if($is_count == 0) { // 不统计数目,也就是直接返回记录
			$list = $this->_order_mod->find(array(
				'conditions' => $conditions,
				'limit' => $page['limit'],
				//'join'	=> 'belongs_to_questionclasses',
				'sort'	=> 'add_time'
			));
			return $list;
		} else {
			$count = $this->_order_mod->getOne("select count(*) from pa_order o where" . $conditions);
			return $count;
		}
	}*/
  	
}
?>