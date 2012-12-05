<?php 
/*����������*/

define('MAX_LAYER', 2);

/* �������ÿ����� */
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
	/*����*/
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
    	$this->assign('s_name',array('˳��','˳ˮ','˳����'));
    	
    	//var_dump($distribution);
    	$this->assign('data',$distribution);
    	$this->display('distribution.show.html'); 
    }

	/*����*/
  function add() {
    	//ʡ������������
		$this->assign('site_url', site_url());
		//$this->assign('order_id',$order_id);
		$this->assign('regions', $this->_region_mod->get_options(0));
		$this->assign('regionss', $this->_region_mod->get_options(0));
		//var_dump($this->_region_mod->get_options(0));
		/* ����jQuery�ı���֤��� */
		$this->assign('kuaidi',array(1,2,3));
		$this->assign('sel_name',array('˳��','˳ˮ','˳����'));
		$this->import_resource(array('script' => 'mlselection.js,jquery.plugins/jquery.validate.js'));
	    $this->display('distribution.form.html');
	   /*���*/ 
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
	    	$this->show_message('��ӳɹ�',
	    	'back_list',	'index.php?app=distribution',
	    	'continue_add',	'index.php?app=distribution&act=add'
	    	);
	   	 }    	   	 
    }   
    /*�༭*/
   function edit(){
   		//ʡ������������
		$this->assign('site_url', site_url());
		//$this->assign('order_id',$order_id);
		$this->assign('regions', $this->_region_mod->get_options(0));
		$this->assign('regionss', $this->_region_mod->get_options(0));
		//var_dump($this->_region_mod->get_options(0));
		/* ����jQuery�ı���֤��� */
		$this->import_resource(array('script' => 'mlselection.js,jquery.plugins/jquery.validate.js'));    	
		$id=isset($_GET['id']) ? 0 : intval($_GET['id']) ;
		$name=isset($_POST['name']) ? '' : trim($_POST['name']);
		if($name=''){
			$this->show_warning('�༭ʧ��');
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
		   	
   
    		/*��ʾ��Ϣ*/
     /*		$id=isset($_GET['id']) ? intval($_GET['id']) : 0;
    		if(!$id){
    			$this->show_warning('�༭ʧ��');
    			return;
    		}
    		if(!IS_POSTT){
    			$find_data =$this->_distribution_mod->find($id);
    			if(empty($find_data)){
    				$this->show_warning('�༭ʧ��');
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
   		/*ɾ��*/
	   function drop(){
    	
    		$id=isset($_GET['id']) ? trim($_GET['id']): '' ;
    	
    	if(!$id){
    		$this->show_warning('ɾ��ʧ��');
    		return;
    	}
    	$id=explode(',',trim($_GET['id']));
    	if(!$this->_distribution_mod->drop($id)){
    		$this->show_warning($this->_distribution_mod->get_error());
    		return;
    	}else{
    		$this->show_message('ɾ���ɹ�');
    	}
    }    
}
?>