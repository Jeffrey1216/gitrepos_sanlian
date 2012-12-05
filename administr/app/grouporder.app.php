<?php

/**
 *    合作伙伴控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class GrouporderApp extends BackendApp
{
	var $_group_order_mod;
	var $_group_log_mod;
	function __construct(){
		$this->GrouporderApp();
	}
 	function GrouporderApp(){
    	parent::__construct();
    	$this->_group_order_mod=& m('grouporder');
    	$this->_group_log_mod=& m('grouporderlog');
    	
    }
    /**
     *    管理
     *
     *    @author    贺瑾璞
     */
    function index()
    {
    	date_default_timezone_set("Asia/Shanghai");
    	$page_num = 10;
        $page = $this->_get_page($page_num);
        $order_sn=empty($_GET['order_sn']) ? '' : intval($_GET['order_sn']);
        $buyer_name=empty($_GET['buyer_name']) ? '' : trim($_GET['buyer_name']);
        $title=empty($_GET['title']) ? '' : trim($_GET['title']);
        $add_time=empty($_GET['add_time']) ? '' : trim($_GET['add_time']);
       // $add_time_to=empty($_GET['add_time_to']) ? '' : trim($_GET['add_time_to']);
        $pay_time=empty($_GET['pay_time']) ? '' : trim($_GET['pay_time']);
        //$pay_time_to=empty($_GET['pay_time_to']) ? '' : trim($_GET['pay_time_to']);
        $status=empty($_GET['status']) ? 11 : intval($_GET['status']);
        $conditions = " 1 = 1 ";
        if(!$order_sn == ''){
        	$conditions .= " AND o.order_sn like '%".$order_sn."%' ";
        	$this->assign("order_sn",$order_sn);
        }
    	if(!$buyer_name == ''){
        	$conditions .= " AND o.buyer_name like '%".$buyer_name."%' ";
        	$this->assign("buyer_name",$buyer_name);
        }
    	if(!$title == ''){
        	$conditions .= " AND p.title like '%".$title."%' ";
        	$this->assign("title",$title);
        }
        if(!$add_time == ''){
        	$conditions .= " AND o.add_time like '%".$add_time."%' ";
        	$this->assign("add_time",$add_time);
        }
    	if(!$pay_time == ''){
        	$conditions .= " AND o.pay_time like '%".$pay_time."%' ";
        	$this->assign("pay_time",$pay_time);
        }

     	switch($status) {
        	case 11: 
        		$conditions .= " AND o.status = 11";
        		$this->assign('sta',11);
        		break;
        	case 20: 
        		$conditions .= " AND o.status = 20";
        		$this->assign('sta',20);
        		break;
        	case 30: 
        		$conditions .= " AND o.status = 30";
        		$this->assign('sta',30);
        		break;
        	case 40: 
        		$conditions .= " AND o.status = 40";
        		$this->assign('sta',40);
        		break;
        	case 50: 
        		$conditions .= " AND o.status = 50";
        		$this->assign('sta',50);
        		break;
        	case 60: 
        		$conditions .= " AND o.status = 60";
        		$this->assign('sta',60);
        		break;
        	case -1: 
        		$conditions .= " AND o.status = 0";
        		$this->assign('sta',-1);
        		break;
        	default : 
        		$this->show_warning("程序出错！ "); 
        		return;
        }
   		 if (isset($_GET['sort']) && isset($_GET['order']))
			        {
			            $sort  = strtolower(trim($_GET['sort']));
			            $order = strtolower(trim($_GET['order']));
			            if (!in_array($order,array('asc','desc')))
			            {
			             $sort  = 'o.add_time';
			             $order = 'desc';
			            }
			        }
			        else
			        {
			            $sort  = 'o.add_time';
			            $order = 'desc';
			        }
        $count=$this->_group_order_mod->getOne("select count(*) from pa_group_order o left join pa_group_project p on o.project_id=p.id left join pa_group_category c on p.category_id=c.id where  " . $conditions );
        $page['item_count'] = $count;
        $order_info=$this->_group_order_mod->getAll("select *,o.add_time,o.status from pa_group_order o left join pa_group_project p on o.project_id=p.id left join pa_group_category c on p.category_id=c.id where " . $conditions . " order  by " . $sort . ' ' . $order . " limit " . $page['limit']);
        $this->_format_page($page);
	    $this->assign('order_info',$order_info);
	    $this->assign('page_info', $page);    
        $this->display('grouporder.list.html');
    }
    /*订单详情*/
    function view(){
    	date_default_timezone_set("Asia/Shanghai");
    	$order_id=empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
    	$status=$this->_group_order_mod->getRow("select o.status from pa_group_order o left join pa_group_project p on o.project_id = p.id where o.order_id = " . $order_id);
    	$a=$status['status'];
    	$this->assign('status',$a);
    	$group_order_mod=& m('grouporder');
    	if($order_id!=0){
    		$group_info=$this->_group_order_mod->getRow("select *,o.order_id,o.buyer_name,o.seller_name,o.add_time,o.ship_time,l.log_time from pa_group_order o left join pa_group_project p on o.project_id = p.id left join pa_group_order_extm e  on o.order_id = e.order_id left join pa_group_order_log l on o.order_id=l.order_id where o.order_id = " . $order_id);
		     $group_info['ship_time'] = date('Y-m-d H:i',$group_info['ship_time']);
    		 $this->assign("groinfo",$group_info);
    	}
   		 
    	$this->display("grouporder.form.html");
    }
    /*发货*/
	function delivery(){
		$order_id=empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
		$status=$this->_group_order_mod->getRow("select o.status from pa_group_order o left join pa_group_project p on o.project_id = p.id where o.order_id = " . $order_id);
		$group_order_mod=& m('grouporder');
		if(!IS_POST){
			if($order_id!=0){
	    		$group_info=$this->_group_order_mod->getRow("select * from pa_group_order o left join pa_group_project p on o.project_id = p.id where o.order_id = " . $order_id);
	    		$this->assign("groinfo",$group_info);
	    		$this->display("grouporder.delv.html");
	    	}
		} else {
			if($status['status']==20){
    			$data=array();
    			$data['status']= 30;
    			$data['ship_no']=trim($_POST['ship_no']);
    			$data['ship_reason']= trim($_POST['ship_reason']);
    			$data['ship_query']=trim($_POST['ship_query']);
    			$data['ship_time']=time();
    			$data['Invoice_no']=trim($_POST['Invoice_no']);
    			$data['ship_name']=trim($_POST['ship_name']);
    			$group_order_mod->edit($order_id,$data);
    			$this->index();
    		}
		}
	}
	/*修改状态*/
	function audit(){
		$order_id=empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
		$user_name=$this->visitor->get('user_name');
		$status=$this->_group_order_mod->getRow("select o.status from pa_group_order o left join pa_group_project p on o.project_id = p.id where o.order_id = " . $order_id);
		$group_order_mod=& m('grouporder');
		$group_log_mod=& m('grouporderlog');
		if(!IS_POST){
    		if($order_id!=0){
    			$this->assign('order_id',$order_id);
    	        $this->display('grouporder.batch.html');
    		}
    	}
    	else{
    		$data1=array();
    		$data1['status']=intval($_POST['status']);
    		$data1['refund_cause']=trim($_POST['refund_cause']);
    		$group_order_mod->edit($order_id,$data1);
    		$data2=array();
    		$data2['order_id']=$order_id;
    		$data2['operator']=$user_name;
    		$data2['order_status']=$status['status'];
    		$data2['changed_status']=intval($_POST['status']);
    		$data2['remark']=trim($_POST['refund_cause']);
    		$data2['log_time']=time();
    		$group_log_mod->add($data2);
    		$this->index();
    	}
	}
	
	/* 定单打印 */
	public function orderprint() {
		date_default_timezone_set("Asia/Shanghai");
    	$order_id=empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
    	$status=$this->_group_order_mod->getRow("select o.status from pa_group_order o left join pa_group_project p on o.project_id = p.id where o.order_id = " . $order_id);
    	$a=$status['status'];
    	$this->assign('status',$a);
    	$group_order_mod=& m('grouporder');
    	if($order_id!=0){
    		$group_info=$this->_group_order_mod->getRow("select *,o.add_time,o.buyer_name,o.seller_name,o.order_id,o.ship_time,l.log_time from pa_group_order o left join pa_group_project p on o.project_id = p.id left join pa_group_order_extm e  on o.order_id = e.order_id left join pa_group_order_log l on o.order_id=l.order_id where o.order_id = " . $order_id);
		     $group_info['ship_time'] = date('Y-m-d H:i',$group_info['ship_time']);
    		 $this->assign("groinfo",$group_info);
    	}
		$this->display('group_order.detaillist.html');
	}
}
?>
