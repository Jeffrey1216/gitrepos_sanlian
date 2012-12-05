<?php
/*
*派啦币交易专区后台控制器
* &@author 贺瑾璞
* */
class CreditApp extends BackendApp {
	
	var $_credit_goods_mod;
	var $_credit_order_mod;
	public function __construct() {
		$this->CreditApp();
	}
	
	public function CreditApp() {
		parent::__construct();
		$credit_goods_mod = & m('creditgoods');
		//$credit_order_mod = & m('creditorder');
	}
	//订单
	function index(){
		date_default_timezone_set("Asia/Shanghai");
		$credit_order_mod = & m('creditorder');
		$page_num = 10;
		$page=$this->_get_page($page_num);
		//会员名搜索
		$buyer_name=empty($_GET['buyer_name']) ? '' : trim($_GET['buyer_name']);
		$seller_name=empty($_GET['seller_name']) ? '' : trim($_GET['seller_name']);
		$opt=$_GET['opt'];
		//状态搜索条件
		$status=empty($_GET['status']) ? 0 : intval($_GET['status']);
		//下单时间条件
		$add_time_from=empty($_GET['add_time_from']) ? 0 : strtotime($_GET['add_time_from']);//讲获取的date时间转换成时间戳的形式
		$add_time_to=empty($_GET['add_time_to']) ? 0 : strtotime($_GET['add_time_to']);
		$time=time ();//取得系统当前时间的时间戳
		$conditions = " 1 = 1 ";
		//会员名
		switch($opt){
			case 0:
				if(!buyer_name == '') {
		        	$conditions .= " AND o.buyer_name like '%".$buyer_name."%' ";
		        	$this->assign('buyer_name',$buyer_name);
        		}
        	break;
			case 1:
				if(!$seller_name == ''){
					$conditions .= " AND o.seller_name like '%".$seller_name."%' ";
			        $this->assign('seller_name',$seller_name);
				}
			break;
			default :
				$this->show_warning("查找程序出错！");
				break;
		}
		//订单状态
		switch ($status){
			case 0:
				$conditions .= " AND 1 = 1 ";
				$this->assign('status',0);
				break;
			case 11:
				$conditions .= " AND o.status = " .$status;
				$this->assign('status',11);
				break;
			case 20:
				$conditions .= " AND o.status = " .$status;
				$this->assign('status',20);
				break;
			case 40:
				$conditions .= " AND o.status = " .$status;
				$this->assign('status',40);
				break;
			default :
				$this->show_warning("订单查找程序出错！");
				break;
		}
		//下单时间
		if($add_time_from!=0 && $add_time_to !=0 && $add_time_to > $add_time_from){
			$conditions .= " AND o.add_time >= " .$add_time_from . " AND o.add_time <= " .$add_time_to;;
		}else if($add_time_to == 0){
			$conditions .= " AND o.add_time >= " .$add_time_from . " AND o.add_time <= " .$time;
		}
		$credit_count = $credit_order_mod->getOne("select count(*) from pa_credit_order o where " . $conditions);
		$page['item_count'] = $credit_count;
		$credit_order = $credit_order_mod->getAll("select * from pa_credit_order o where " . $conditions . " order by o.add_time desc limit " . $page['limit']);
		$this->_format_page($page);
		$this->assign('page_info', $page);
		$this->assign('credit_order',$credit_order);
		$this->display("credit.index.html");
	}
	//查看详情
	function view()
	{
		date_default_timezone_set("Asia/Shanghai");
		$credit_order_mod = & m('creditorder');
		$id=empty($_GET['id']) ? 0 : intval($_GET['id']);
		$credit_view = $credit_order_mod->getRow("select * from pa_credit_order o where o.id = " .$id);
		$this->assign('credit_view',$credit_view);
		$this->display("credit.form.html");
	}
	function credit()
	{
		date_default_timezone_set("Asia/Shanghai");
		$credit_goods_mod = & m('creditgoods');
		$page_num = 10;//每页显示数量
    	$page = $this->_get_page($page_num);
    	//获取条件
		$cycle=$_GET['cycle'];
		$cycle_info=$cycle * 24 * 60 * 60;
		$opt=$_GET['opt'];
		$type=empty($_GET['type']) ? 0 : intval($_GET['type']);
		$credit_num=empty($_GET['credit_num']) ?  0 : intval($_GET['credit_num']);
		$user_name=empty($_GET['user_name']) ? '' : trim($_GET['user_name']);
		$conditions = " 1 = 1 ";
		//PL数目
		if($credit_num != 0){
			$conditions .= " AND g.credit_num = " .$credit_num;
			$this->assign('credit_num',$credit_num);
		}
		//发布者
		if($user_name != ''){
			$conditions .= " AND g.user_name like '%".$user_name."%' ";
			$this->assign('user_name',$user_name);
		}
		//周期
		switch($cycle)
		{
			case 0:
				$conditions .= " AND 1 = 1 ";
				$this->assign('cycle',0);
				break;
			case 3:
				$conditions .= " AND g.cycle = " .$cycle_info;
				$this->assign('cycle',3);
				break;
			case 7:
				$conditions .= " AND g.cycle = " .$cycle_info;
				$this->assign('cycle',7);
				break;
			case 10:
				$conditions .= " AND g.cycle = " .$cycle_info;
				$this->assign('cycle',10);
				break;
			case 15:
				$conditions .= " AND g.cycle = " .$cycle_info;
				$this->assign('cycle',15);
				break;
			default :
				$this->show_warning("周期查找程序出错！");
				break;
		}
		//状态
		switch($type)
		{
			case 0:
				$conditions .= " AND 1 = 1 ";
				$this->assign('type',0);
				break;
			case 1:
				$conditions .= " AND g.type = 1";
				$this->assign('type',1);
				break;
			case 2:
				$conditions .= " AND g.type = 0";
				$this->assign('type',2);
				break;
			case 3:
				$conditions .= " AND g.type = 3";
				$this->assign('type',3);
				break;
			case 4:
				$conditions .= " AND g.type = 4";
				$this->assign('type',4);
				break;
			default :
				$this->show_warning("状态查找程序出错！");
				break;
		}
		$count = $credit_goods_mod->getOne("select count(*) from pa_credit_goods g where " . $conditions);
		$page['item_count'] = $count;
		$credit_goods_info=$credit_goods_mod->getAll("select * from pa_credit_goods g where " .$conditions . " order by g.time desc limit " . $page['limit']);
		foreach ($credit_goods_info as $k => $v){
    		$credit_goods_info[$k]['cycle']=$v['cycle']/60/60/24;
    		$credit_goods_info[$k]['time']=date('Y-m-d H:i',$v['time']);
    	}
		$this->_format_page($page);
		$this->assign('page_info', $page);
		$this->assign('credit_goods_info',$credit_goods_info);
		$this->display("credit.goods.html");
	}
}
?>