<?php
/*
*许愿墙后台控制器
* &@author 贺瑾璞
* */

class VowwallApp extends BackendApp {
	var $_vow_mod;
	var $_message_mod;
	public function __construct() {
		$this->VowwallApp();
	}
	
	public function VowwallApp() {
		parent::BackendApp();
		$this->_member_mod=& m('member');
		$this->_vow_mod=& m('vowwall');
		$this->_message_mod=& m('message');
	}
	function index(){
		date_default_timezone_set("Asia/Shanghai");
    	$page_num = 20;
        $page = $this->_get_page($page_num);
        $vow_id = empty($_GET['vow_id']) ? 0 : intval($_GET['vow_id']);
        $user_name=empty($_GET['user_name']) ? '' : trim($_GET['user_name']);
        $status = empty($_GET['status']) ? 0 : intval($_GET['status']);
		$opt=$_GET['opt'];
		$this->assign('opt',$opt);
		$conditions = " 1 = 1 ";
		switch($opt){
			case 0:
				if($vow_id != 0) {
		        	$conditions .= " AND v.vow_id = " . $vow_id;
		        	$this->assign('vow_id',$vow_id);
        		}
        	break;
			case 1:
				if(!$user_name == ''){
					$conditions .= " AND m.user_name like '%".$user_name."%' ";
			        $this->assign('user_name',$user_name);
				}
			break;
			default :
				$this->show_warning("查找程序出错！");
				break;
		}
		if (isset($_GET['sort']) && isset($_GET['order']))
			        {
			            $sort  = strtolower(trim($_GET['sort']));
			            $order = strtolower(trim($_GET['order']));
			            if (!in_array($order,array('asc','desc')))
			            {
			             $sort  = 'vow_id';
			             $order = 'desc';
			            }
			        }
			        else
			        {
			            $sort  = 'vow_id';
			            $order = 'desc';
			        }
		if(!IS_POST){
         $count = $this->_vow_mod->getOne("select count(*) from pa_vow_wall v left join pa_member m on v.user_id = m.user_id where " . $conditions);
	     $page['item_count'] = $count;
	     $vowinfo = $this->_vow_mod->getAll("select * from pa_vow_wall v left join pa_member m on v.user_id = m.user_id where " . $conditions . " order by " . $sort . ' ' . $order . " limit " . $page['limit']);
	     $this->_format_page($page);
	     foreach($vowinfo as $k => $v) {
	     	$vowinfo[$k]['add_time'] = date('Y-m-d H:i',$v['add_time']);
	     }
	     $this->assign('page_info', $page);
        $this->assign('vowinfo',$vowinfo);
		$this->display("vowwall.index.html");
		}else {
        	$vid = empty($_POST['vid']) ? 0 : $_POST['vid'];
        	$this->assign("vid",$vid);
        	$this->display("vowwall.form.html");
        }
    }
    /*批量删除*/
    function drop(){
    	$vid=empty($_POST['vid']) ? 0 : $_POST['vid'];
		if($vid == 0){
			$this->show_warning("没有选中任何愿望");
		}else{
			
			foreach ($vid as $k=>$v){
				$this->_vow_mod->drop($v);
			}
		}
		header("Location:index.php?app=vowwall&act=index");
    }
    /*审核*/
	function audit(){
		$conditions = " 1 = 1 ";
		$vid=empty($_POST['vid']) ? 0 : $_POST['vid'];
        $status = empty($_POST['status']) ? 0 : intval($_POST['status']);
        if($vid == 0) {
        	$this->show_warning("没有选中任何商品！");
        } else{      		
        foreach($vid as $k => $v) {
        			$this->_vow_mod->edit($v," status = " . $status . "");
        		} 
        header("Location:index.php?app=vowwall&act=index");  
        }
    }
    /*修改*/
	function edit(){
    	$vow_id = isset($_GET['vow_id']) ? intval($_GET['vow_id']) : 0;
    	$vow_mod=& m(vowwall);
    	if(!IS_POST){
    		if($vow_id != 0){
    			//查询商品信息
    			$vowinfo = $this->_vow_mod->getRow("select * from pa_vow_wall v left join pa_member m on v.user_id = m.user_id where vow_id = " . $vow_id);
    			$this->assign("vowinfo",$vowinfo);
    	        $this->display('vowwall.edit.html');
    		}
    	}
    	else{
    	if (isset($_POST['Submit'])){
    		$data=array();
    		$data['content']= trim($_POST['content']);
    		$vow_mod->edit($vow_id,$data);
    		$this->index();
    	}
    	}
    }
}

?>
