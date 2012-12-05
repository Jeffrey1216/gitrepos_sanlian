<?php 
/*物流配置器*/

define('MAX_LAYER', 2);

/* 物流配置控制器 */
class DistributionApp extends BackendApp
{	 
    var $_distribution_mod;
    var $_region_mod;
	var $_shipping_mod;
    function __construct()
    {
        $this->DistributionApp();
    }

    function DistributionApp()
    {
        parent::BackendApp();

        $this->_distribution_mod =& m('distribution');
        $this->_region_mod=& m('region');
        $this->_shipping_mod=& m('shipping');
    }
	/*管理*/
    function index(){
    	$_distribution_mod=&m('physicaldistribution');
    	$distribution=$_distribution_mod->getAll("select * from pa_physical_distribution left join pa_shipping on pa_physical_distribution.pid = pa_shipping.shipping_id");
    	//var_dump($distribution);
    	foreach($distribution as $k => $v){
    		 $arr = unserialize($v['charge_info']);
    		 //var_dump($arr);
    		 $distribution[$k]['prise_info'] = $arr['start'];		
    	}
    	$this->assign('name',array(1,2,3));
    	$this->assign('s_name',array('顺风','顺水','顺你妹'));
    	
    	//var_dump($distribution);
    	$this->assign('data',$distribution);
    	$this->display('distribution.show.html'); 
    }

	/*新曾*/
  function add() {
    	//省市县三级联动
		$this->assign('site_url', site_url());
		//$this->assign('order_id',$order_id);
		$this->assign('regions', $this->_region_mod->get_options(0));
		$this->assign('regionss', $this->_region_mod->get_options(0));
		//var_dump($this->_region_mod->get_options(0));
		/* 导入jQuery的表单验证插件 */
		$this->assign('kuaidi',array(1,2,3));
		$this->assign('sel_name',array('顺风','顺水','顺你妹'));
		$this->import_resource(array('script' => 'mlselection.js,jquery.plugins/jquery.validate.js'));
	    $this->display('distribution.form.html');
	   /*添加*/ 
	   if (isset($_POST['submit']))
	   		{
	   		$weight_min=$_POST['min_weight'];
	   		$weight_max=$_POST['max_weight'];
	   		$price=$_POST['price'];
	   		for($i=0; $i<count($weight_min); $i++){			 
	   			 $sum["info".$i]['weight_min']=$weight_min[$i];
                 $sum["info".$i]['weight_max']=$weight_max[$i];
                 $sum["info".$i]['price']=$price[$i];	   				 
	   		}
	   		 	 $sum['start']['weight_start']=$_POST['weight_start'];
	   			 $sum['start']['price']=$_POST['price_start'];
				//	var_dump($sun);
	   			 $a=serialize($sum);     				
	   	    $data=array();
	    	$data['p_id']		       =$_POST['name1'];
	    	$data['pname']			   =$_POST['sel_name'];    	
     		$data['incept_id']		   =$_POST['region_id'];	
	    	$data['incept_name']       =$_POST['region_name'];
     		$data['finished_id']	   =$_POST['region_ids'];
	    	$data['finished_name']	   =$_POST['region_names'];
	    	$data['charge_info']	   =$a;	
	    	$id=$this->_distribution_mod->add($data);
	    	$this->show_message('添加成功',
	    	'back_list',	'index.php?app=distribution',
	    	'continue_add',	'index.php?app=distribution&act=add'
	    	);
	   	 }    	   	 
    }   
    /*编辑*/
   function edit(){
   		//省市县三级联动
		$this->assign('site_url', site_url());
		//$this->assign('order_id',$order_id);
		$this->assign('regions', $this->_region_mod->get_options(0));
		$this->assign('regionss', $this->_region_mod->get_options(0));
		//var_dump($this->_region_mod->get_options(0));
		/* 导入jQuery的表单验证插件 */
		$this->import_resource(array('script' => 'mlselection.js,jquery.plugins/jquery.validate.js'));    	
		$id=isset($_GET['id']) ? 0 : intval($_GET['id']) ;
		$name=isset($_POST['name']) ? '' : trim($_POST['name']);
		if($name=''){
			$this->show_warning('编辑失败');
		}
		//var_dump($name);
		$_distribution_mod=&m('physicaldistribution');
		$distribution=$_distribution_mod->getAll("select * from pa_physical_distribution left join pa_shipping on pa_physical_distribution.pid = pa_shipping.shipping_id");
		foreach($distribution as $k => $v){
    		 $arr = unserialize($v['charge_info']);
    		 //var_dump($arr);
    		 $distribution[$k]['prise_info'] = $arr['start'];		
    	}
    	//var_dump($distribution);
    	$this->assign('data',$distribution);
		$this->display('distribution.edit.html');
		   	
   
    		/*显示信息*/
     /*		$id=isset($_GET['id']) ? intval($_GET['id']) : 0;
    		if(!$id){
    			$this->show_warning('编辑失败');
    			return;
    		}
    		if(!IS_POSTT){
    			$find_data =$this->_distribution_mod->find($id);
    			if(empty($find_data)){
    				$this->show_warning('编辑失败');
    				return;
    			}
    		$distribution=current($find_data);
    		$this->assign('distributions',$distribution);   		
    		$this->display('distribution.edit.html');
    		}	
			else{
   				$data=array('pname'		       =>$_POST['pname'],
	    					'level1_incept_id'  =>$_POST['level1_incept_id'],
     						'level2_incept_id'  =>$_POST['level2_incept_id'],
     						'level3_incept_id'  =>$_POST['level3_incept_id'],
     						'level4_incept_id'  =>$_POST['level4_incept_id'],
	    					'incept_name'       =>$_POST['incept_name'],
	    					'level1_finished_id'=>$_POST['level1_finished_id'],
     						'level2_finished_id'=>$_POST['level2_finished_id'],
     						'level3_finished_id'=>$_POST['level3_finished_id'],
     						'level4_finished_id'=>$_POST['level4_finished_id'],
	    					'finished_name'	   =>$_POST['finished_name'],
	    					'charge_info'	   =>$_POST['charge_info'],);
    		
    				$rows=$this->_distribution_mod->edit($id,$data);
    				$this->index();
			}*/
    		 		
    }	
   		/*删除*/
	   function drop(){
    	
    		$id=isset($_GET['id']) ? trim($_GET['id']): '' ;
    	
    	if(!$id){
    		$this->show_warning('删除失败');
    		return;
    	}
    	$id=explode(',',trim($_GET['id']));
    	if(!$this->_distribution_mod->drop($id)){
    		$this->show_warning($this->_distribution_mod->get_error());
    		return;
    	}else{
    		$this->show_message('删除成功');
    	}
    }    
}
?>