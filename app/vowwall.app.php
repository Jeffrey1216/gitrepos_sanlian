<?php
/*
 * 许愿墙前台控制器
 * @author 贺瑾璞
 */

class VowwallApp extends MallbaseApp {
	var $_vow_mod;
	public function __construct()
    {
        $this->VowwallApp();
    }

    public function VowwallApp()
    {
    	parent::__construct();
    	$this->_vow_mod=& m('vowwall');
    	
    }
    /*祝福显示*/
	function index(){
		//var_dump($this);
		date_default_timezone_set("Asia/Shanghai");
		$user_id=$this->visitor->get('user_id');
		//var_dump($user_id);
		$this->assign('user_id',$user_id);
		$vow_id = empty($_GET['vow_id']) ? 0 : intval($_GET['vow_id']);
		$user_name=empty($_GET['user_name']) ? '' : trim($_GET['user_name']);
		//$status=empty($_GET['status']) ? 0 : intval($_GET['status']);
		$opt=$_GET['opt'];
		$conditions = " 1 = 1 AND v.status = 1 ";
		$this->assign('opt',$opt);
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
		}
        $vowinfo = $this->_vow_mod->getAll("select * from pa_vow_wall v left join pa_member m on v.user_id = m.user_id where " . $conditions);
        $count = $this->_vow_mod->getOne("select count(*) from pa_vow_wall v left join pa_member m on v.user_id = m.user_id where v.user_id = " .$user_id);
        $this->assign("count",$count);
		foreach($vowinfo as $k => $v) {
	     	$vowinfo[$k]['add_time'] = date('Y-m-d H:i',$v['add_time']);
	     }
        $this->assign("vowinfo",$vowinfo);
		$this->display("wish_wall.html");
	}
	/*发布祝福*/
	function add(){
		$user_id=$this->visitor->get('user_id');
		$user_name=$this->visitor->get('user_name');
		$content=$_POST['content'];
    	if (IS_POST){
    		
    		if(isset($_POST['Submit'])){
    			$data=array();
    			$data['content']=trim($_POST['content']);
    			$data['add_time']=time();
    			$data['user_id']=$user_id;
    			$data['user_name']=$user_name;
    			$data['class']=$_POST['color_name'];
    			$vow = $this->_vow_mod->add($data);
    		}
    		header("Location:index.php?app=vowwall&act=index");
    		}
    }
}
?>