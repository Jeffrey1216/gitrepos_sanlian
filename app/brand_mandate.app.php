<?php
class Brand_mandateApp extends MallbaseApp
{	
	var $_supply_mod;
	var $_commodities_exhibition_mod;
	var $_member_mod;
 	public function __construct()
    {
        $this->Brand_mandateApp();
    }

    public function Brand_mandateApp()
    {
    	parent::__construct();
    	$this->_supply_mod=& m('supply');
    	$this->_commodities_exhibition_mod=& m('commoditiesexhibition'); 
    	$this->_member_mod=& m('member');
    }
	public function index()
	{
		 $supply_num=9;
		 $com_num=8;
		 $user_id=$this->visitor->get('user_id');
		 $conditions = " 1 = 1 ";
         $supply_info = $this->_supply_mod->getAll("select * from  pa_supply s where " . $conditions . " order by s.supply_id desc " . " limit 0,9");
		 foreach($supply_info as $k => $v) {
            	$supply_info[$k]['supply_logo'] = IMAGE_URL.$v['supply_logo'];
            }
         $com_info = $this->_commodities_exhibition_mod->getAll("select * from pa_commodities_exhibition c left join pa_supply s on  c.supply_id = s.supply_id where " . $conditions . " order by c.id  desc " . " limit " . $com_num);
         foreach($com_info as $_k => $_v){
         	$com_info[$_k]['goods_image'] = IMAGE_URL.$_v['goods_image'];
         	$com_info[$_k]['supply_logo'] = IMAGE_URL.$_v['supply_logo'];
         }
         //$supply_arr = array_chunk($supply_info, 3);
         $com_arr=array_chunk($com_info, 4);
         //$this->assign("supply_arr",$supply_arr);
         $this->assign("com_arr",$com_arr);
         $this->assign("supply_info",$supply_info);
         $this->assign('a',1);
		 $this->display("brand_index.html");
	}
	public function form()
	{
		$num = 24;
		$page = $this->_get_page($num);
		$conditions = " 1 = 1";
		$count =  $this->_supply_mod->getOne("select count(*) from  pa_supply s where " . $conditions );
	    $page['item_count'] = $count;
		$supply_info = $this->_supply_mod->getAll("select * from  pa_supply s where " . $conditions . " order by s.supply_id desc " . " limit " . $page['limit']);
		foreach($supply_info as $_k => $_v){
			$supply_info[$_k]['supply_logo'] = IMAGE_URL.$_v['supply_logo'];
		}
		$this->_format_page($page);
		$this->assign('page_info', $page);
		$this->assign("supply_arr",$supply_info);
		$this->assign('b',2);
		$this->display("brand_show.html");
	}
	
	function login()
    {
        if ($this->visitor->has_login)
        {
            $this->json_error("hasLogin");

            return;
        }
        
        /* 判断是否开启了登入的验证码，如果开启了判断验证码是否正确 */
        if (Conf::get('captcha_status.login') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
        {
            $this->json_error('captcha_failed');

            return;
        }

        $user_name = trim($_POST['user_name']);
        $password  = trim($_POST['password']);
		
        $ms =& ms();
        $user_id = $ms->user->auth($user_name, $password);
        //var_dump($user_id);
        if (!$user_id)
        {
            /* 未通过验证，提示错误信息 */
            //$this->json_error($ms->user->get_error());
			$this->json_error("LoginFalid");
            return;
        }
        else
        {
            /* 通过验证，执行登陆操作 */
            $this->_do_login($user_id);

            /* 同步登陆外部系统 */
            $synlogin = $ms->user->synlogin($user_id);
        }
		//Lang::get('login_successed') . $synlogin,
       	$this->json_result("LoginSuccess");
    }
    

    //加盟需求表
	public function formlist()
	{
		$supply_info_mod = & m('supplyinfo');
		$user_id=$this->visitor->get('user_id');
		if(!$this->visitor->has_login)
		{
			header("Location:index.php?app=member&act=login");
			return;
		}
		
		$user_name=$this->visitor->get('user_name');

		if(!IS_POST)
		{
			$this->display("brand_jiameng.html");
			
		}else{
			$id=$supply_info_mod->getRow("select * from pa_supply_info where user_id = " .$user_id);
			$supply_name = trim($_POST['supply_name']);
			$linkman = trim($_POST['linkman']);
			$mobile = trim($_POST['mobile']);
			//$linkman = trim($_POST['linkman']);
			$company_info = trim($_POST['company_info']);
			$address = trim($_POST['address']);
			if($id)
			{
				$this->show_warning("您已经提交过了加盟信息！请等待我们的联系，谢谢！");
				return;
			}
			if(!$supply_name)
			{
				$this->show_warning("您尚未添加客户名称，请返回重新添加");
				return;
			}
			if(!$linkman)
			{
				$this->show_warning("您尚未添加联系人，请返回重新添加");
				return;
			}
			if(!$mobile)
			{
				$this->show_warning("您尚未添加联系电话，请返回重新添加");
				return;
			}
			if(!$company_info)
			{
				$this->show_warning("您尚未添加公司简介，请返回重新添加");
				return;
			}
			if(!$address)
			{
				$this->show_warning("您尚未添加公司地址，请返回重新添加");
				return;
			}
			//文件上传
			$uploadDir = 'data/files/upload/';
    		//$uploadFile = md5($user_name) . strrchr($_FILES['upfile']['name'],'.'););
    		$uploadFile = md5($user_name . substr($_FILES['upfile']['name'],0,strrpos($_FILES['upfile']['name'],'.'))) .strrchr($_FILES['upfile']['name'],'.');
    		if (!file_exists($uploadDir))
    		{
    			mkdir($uploadDir,0777,true);
    		}
    		move_uploaded_file(trim($_FILES['upfile']['tmp_name']), $uploadDir . $uploadFile);
    		
    		//LOGO上传
    		$logoDir = $uploadDir . 'logo/';
    		//$uploadFile = md5($user_name) . strrchr($_FILES['upfile']['name'],'.'););
    		$logoFile = md5($user_name . substr($_FILES['supply_logo']['name'],0,strrpos($_FILES['supply_logo']['name'],'.'))) .strrchr($_FILES['supply_logo']['name'],'.');
    		if (!file_exists($logoDir))
    		{
    			mkdir($logoDir,0777,true);
    		}
    		move_uploaded_file(trim($_FILES['supply_logo']['tmp_name']), $logoDir . $logoFile);
    		
    		
    		//相关资料上传
    		$infoDir = $uploadDir . 'info/';
			if (!file_exists($infoDir))
    		{
    			mkdir($infoDir,0777,true);
    		}
    		$name = $_FILES['info']['name'];
    		$info_tmp_name = $_FILES['info']['tmp_name'];
    		$length=count($name);
    		$arr = array();
    		for($i=0;$i < $length;$i++)
    		{
    			//echo $i;
    			$arr[] =  $infoDir . $i . strrchr($name[$i],'.');
    			move_uploaded_file(trim($info_tmp_name[$i]), $infoDir . $i . strrchr($name[$i],'.'));
    		}

    		//合作客户需求表单添加
			$data=array();
			$data['supply_name']=$supply_name;
			$data['user_id']=$user_id;
			$data['user_name']=$user_name;
			$data['supply_type']=$_POST['supply_type'];
			$data['linkman']=$linkman;
			$data['mobile']=$mobile;
			$data['add_time']=time();
			$data['company_info']=$company_info;
			$data['address']=$address;
			$data['scale']=$_POST['scale'];
			$data['business_practice']=$_POST['business_practice'];
			$data['marketing_channel']=$_POST['marketing_channel'];
			$data['annual_sales']=$_POST['annual_sales'];
			$data['info']= serialize($arr);
			$data['channel']=$_POST['channel'];
			$data['brand_demand']=$_POST['brand_demand'];
			$data['company_demand']=$_POST['company_demand'];
			$data['cooperation_demand']=$_POST['cooperation_demand'];
			$data['data_url']=$uploadDir . $uploadFile;
			$data['supply_logo']=$logoDir . $logoFile;
			$supply = $supply_info_mod->add($data);
			if (!$supply)
	    	{
	    		$this->show_warning("提交失败，请重新提交！");
	    		return;
	    	}else{
	    		$this->show_warning("提交成功！");
	    		return;
	    	}
		}
	}
	
	//案例详细页
	public function view()
	{
		//echo 123;
		$supply_mod = & m('supply');
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		$supply_info = $supply_mod->getRow("select * from pa_supply s  where s.supply_id = " .$id);
		//var_dump($supply_info);
		$supply_info['supply_logo'] = IMAGE_URL.$supply_info['supply_logo'];
		$supply_goods = $this->_commodities_exhibition_mod->getAll("select  * from pa_commodities_exhibition c where c.supply_id = " .$id);
		foreach($supply_goods as $k => $v){
			$supply_goods[$k]['goods_image'] = IMAGE_URL.$v['goods_image'];
		}
		$supply_arr = array_chunk($supply_goods, 3);
		$this->assign('supply_info',$supply_info);
		$this->assign('supply',$supply_goods);
		$this->assign('supply_goods',$supply_arr);
		$this->display("brand.view.html");
	}
}

?>
