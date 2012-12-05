<?php
/**********
 * 品牌托管后台管理
 * &fash
 * *******/
class BrandmandateApp extends BackendApp
{
	public $_get_supply_info_mod;
	public $_get_supply_mod;
 	function __construct()
	    {
	        $this->BrandMandateApp();
	    }
    function BrandMandateApp()
    {
        parent::__construct();
        $this->_get_supply_info_mod=&m('supply');
        $this-> _get_supply_mod=&m('supply');
    }
	
	function index()
	{	
		$supply = $this->_get_supply_info_mod->getAll('select * from pa_supply where state=1');
		$this->assign("supply",$supply);
		$this->display('brand_mandate.html');
	}
	/****详细审核页***/	
	function verify()
	{
		$id = empty($_GET['supply_id']) ? 0 : intval($_GET['supply_id']);
		if ($id == 0)
		{
			$this->show_warning("没有需要审核项！");
			return;
		}
		if (!IS_POST)
		{
			$verify = $this->_get_supply_info_mod->getRow('select * from pa_supply_info where supply_id=' . $id);
			$this->assign('verify',$verify);
			$this->display('brand_mverify.html');
		} else {
			$rad = empty($_POST["rad"]) ? 0 : intval($_POST['rad']);
			$refuse = empty($_POST['refuse']) ? null :trim($_POST['refuse']);
			if($rad != 0 && $rad == 2){
				if(isset($_POST['refuse']))
				{			
					$verify = $this->_get_supply_info_mod->getRow('select state,supply_type,business_practice,scale,user_name,supply_logo,marketing_channel,annual_sales,supply_name,linkman,mobile,address,zipcode,company_info,user_id,summary,plan from pa_supply_info where supply_id=' . $id);
					$data = array();
					$data = $verify;
					$data['state'] = $rad;
					$state = array(
						state => $rad
					);
					//var_dump($data);
							
					$this->_get_supply_mod->add($data);	
					$this->_get_supply_info_mod->edit($id,$state);
					$this->show_message('审核成功', '继续审核', 'index.php?app=brandmandate');
				}
				return 0;
			}
			if(!empty($refuse) && $rad == 3)
			{
				$data=array();
	    		$data['state'] = $rad;
	    		$data['refuse'] = $refuse;
				$this->_get_supply_info_mod->edit($id,$data);
				$this->show_message('审核成功', '继续审核', 'index.php?app=brandmandate');	
				return 0;	
			}
		}
	}
	function manage()
	{
		$supply_mod = &m('supply');
		$supply = $supply_mod->getAll('select * from pa_supply');
		$this->assign("supply",$supply);
		$this->display('brand_manage.html');
		
	}
	
	//商品管理
	function brand_index()
	{
		$com_ex_info_mod = & m('commoditiesexhibition');
		$page_num = 10;
        $page = $this->_get_page($page_num);
        $count = $com_ex_info_mod->getOne("select count(*) from pa_commodities_exhibition");
        $page['item_count'] = $count;
		$goods_info  = $com_ex_info_mod->getAll("select * from pa_commodities_exhibition  limit " . $page['limit']);
		$this->_format_page($page);
		$this->assign('page_info', $page);
		$this->assign('goods_info',$goods_info);
		$this->display("brand_goods_index.html");
	}
	
	//商品添加
	function brand_goods()
	{
		$goods_mod = & m('goods');
		$com_ex_info_mod = & m('commoditiesexhibition');
		$supply_mod = & m('supply');
		
		if(!IS_POST)
		{
			$this->display("brand_goods.html");
		}else{
			$goods_id = $_POST['goods_id'];
			$supply_id = intval($_POST['supply_id']);
			if (isset($_POST['Submit'])){
				if(!$goods_id)
				{
					$this->show_warning('您尚未输出要添加的商品ID');
					return;
				}
				if(!$supply_id)
				{
					$this->show_warning('您尚未输入要添加的供应商的ID');
					return;
				}
				//$goods_info = $goods_mod->getAll("select *,s.supply_id,g.supply_id from pa_goods g left join pa_supply s on g.supply_id = s.supply_id where g.goods_id in (" . $goods_id . ")");
				$goods_info = $goods_mod->getAll("select * from pa_goods g where g.goods_id in (" . $goods_id . ")");
				$supply_name = $supply_mod->getRow("select supply_name from pa_supply s where s.supply_id = " .$supply_id);
				foreach($goods_info as $k => $v)
				{
					$data=array(
						'goods_name'=>$v['goods_name'],
						'goods_spec'=>$v['spec_qty'],
						'sprice'=>$v['sprice'],
						'price'=>$v['price'],
						'goods_image'=>$v['mimage_url'],
						//'goods_image'=>$v['smimage_url'],
						'supply_id'=>$supply_id,
						'credit'=>$v['credit'],
						'supply_name'=>$supply_name['supply_name'],
					);
					$com_ex_info = $com_ex_info_mod->add($data);
				}
				header("Location:index.php?app=brandmandate&act=brand_index"); 
			}
		}
	}
	
	//商品删除
	function brand_drop()
	{
		$commodities_exhibition_mod = & m('commoditiesexhibition');
		$id=$_GET['id'];
		$commodities_exhibition_mod->drop($id);
		header("Location:index.php?app=brandmandate&act=brand_index");
		
	}
	
	//客户需求修改
	function brand_edit()
	{
		$supply_mod = &m('supply');
		$supply_id=$_GET['id'];
		$supply_info = $supply_mod->getRow("select * from pa_supply where supply_id = " .$supply_id);
		$user_name = $supply_info['user_name'];
		$supply_logo = $supply_info['supply_logo'];
		$this->assign('supply',$supply_info);
		if(!IS_POST)
		{
			$this->display("brand_edit.html");
		}else{
			$up_path=ROOT_PATH . '/'.'data/files/upload';
			if(!file_exists($up_path)){
				mkdir($up_path);
				if(!file_exists($up_path.'/logo')){
					mkdir($up_path.'/logo');
				}
			}
			$supply_path=ROOT_PATH.'/data/files/mall/supply';
			if(!file_exists($supply_path)){
				mkdir($supply_path);
			}
			if(!file_exists($supply_path.'/logo')){
				mkdir($supply_path.'/logo');
			}
			$aa= dirname(ROOT_PATH . '/' .$supply_logo);
			if(!file_exists($aa)){
				mkdir($aa);
			}
    		move_uploaded_file(trim($_FILES['supply_logo']['tmp_name']), ROOT_PATH . '/' .$supply_logo);
    		$data=array();
    		$data['supply_name']=$_POST['supply_name'];
    		$data['supply_type']=$_POST['supply_type'];
    		$data['linkman']=$_POST['linkman'];
    		$data['mobile']=$_POST['mobile'];
    		$data['company_info']=$_POST['company_info'];
    		$data['address']=$_POST['address'];
    		$data['scale']=$_POST['scale'];
    		$data['business_practice']=$_POST['business_practice'];
    		$data['marketing_channel']=$_POST['marketing_channel'];
    		$data['annual_sales']=$_POST['annual_sales'];
			$supply_mod->edit($supply_id,$data);
			header("Location:index.php?app=brandmandate&act=manage");
		}
		
	}
	
	//未审核供应商的修改
	function brand_edit_no()
	{
		$supply_info_mod = & m('supplyinfo');
		$supply_id = intval($_GET['supply_id']);
		$supply_info = $supply_info_mod->getRow("select * from pa_supply_info where supply_id = " .$supply_id);
		$supply_logo = $supply_info['supply_logo'];
		$data_url = $supply_info['data_url'];
		$info = $supply_info['info'];
		$info = unserialize($info);
		foreach($info as $k => $v)
		{
			$supply_info['info_array'][$k]['img_url'] = IMAGE_URL.$v;
			$supply_info['info_array'][$k]['name'] = "info" . $k;
		}

		if(!IS_POST)
		{
			$this->assign('supply',$supply_info);
			$this->display("brand_edit_no.html");
		}else{
			$infoDir = 'data/files/upload/info/';
			$arr=array();
			for ($i = 0; $i < count($_FILES['info']['type']); $i++)
			{
				$a=strrchr($_FILES['info']['name'][$i],'.');
				if ($_FILES['info']['type'][$i])
				{
					$arr[]= $infoDir . $i . strrchr($_FILES['info']['name'][$i],'.');
					$p_path= ROOT_PATH . '/' . $infoDir . $i . strrchr($_FILES['info']['name'][$i],'.');
					$supply_path=ROOT_PATH.'/data/files/mall/supply';
					if(!file_exists($supply_path)){
						mkdir($supply_path);
						if(!file_exists($supply_path.'/logo')){
							mkdir($supply_path);
						}
					}
					move_uploaded_file(trim($_FILES['info']['tmp_name'][$i]),$p_path);
				}else{
					$arr[]=$info[$i];
					move_uploaded_file(trim($_FILES['info']['tmp_name'][$i]), ROOT_PATH . '/' . $info[$i]);
				}
			}
			//文件上传
			move_uploaded_file(trim($_FILES['upfile']['tmp_name']),  ROOT_PATH . '/'  .$data_url);
			//logo上传
			move_uploaded_file(trim($_FILES['supply_logo']['tmp_name']), ROOT_PATH  . '/' .$supply_logo);

    		
			$data=array();
			$data['supply_name']=trim($_POST['supply_name']);
			$data['supply_type']=intval($_POST['supply_type']);
			$data['linkman']=trim($_POST['linkman']);
			$data['mobile']=trim($_POST['mobile']);
			$data['company_info']=$_POST['company_info'];
			$data['address']=$_POST['address'];
			$data['scale']=$_POST['scale'];
			$data['business_practice']=$_POST['business_practice'];
			$data['marketing_channel']=$_POST['marketing_channel'];
			$data['annual_sales']=$_POST['annual_sales'];
			$data['channel']=$_POST['channel'];
			$data['brand_demand']=$_POST['brand_demand'];
			$data['company_demand']=$_POST['company_demand'];
			$data['cooperation_demand']=$_POST['cooperation_demand'];
			$data['info']=serialize($arr);
			//var_dump($data['info']);
			//die();
			$supply_info_mod->edit($supply_id,$data);
			header("Location:index.php?app=brandmandate");
		}
	}
}

?>