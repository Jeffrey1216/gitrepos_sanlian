<?php
define('PAGE_NUM', 20);
//供应商管理
class SupplyApp extends BackendApp
{
	public $_supply_mod;
	public $_region_mod;
	public $_gcategory_mod;
	public $_supply_goods_mod;
    function __construct()
    {
        $this->SupplyApp();
    }

    function SupplyApp()
    {
        parent::__construct();
        $this->_supply_mod = & m('supply');
        $this->_region_mod = & m('region');
        $this->_gcategory_mod = &m('gcategory');
        $this->_supply_goods_mod = &m('supplygoods');
    }
    
    public function index()
    {
    	$_uname = empty($_GET['username']) ? false : trim($_GET['username']);
    	$_company_name = empty($_GET['company_name']) ? false : trim($_GET['company_name']);
    	$page = $this->_get_page(PAGE_NUM);
    	$conditions = "1 = 1 and su.type = 1";
        $conditions .= $this->_get_query_conditions(array(array(
                'field' => 'user_name',      
                'equal' => 'LIKE',
        		'name'  => 'user_name',
            ),array(
                'field' => 'supply_name',
                'equal' => 'LIKE',
                'name'  => 'supply_name',
	        ),array(
                'field' => 'parties',
                'equal' => 'LIKE',
                'name'  => 'parties',
	        ),array(
                'field' => 'parties_type',
                'equal' => '=',
                'type'  => 'numeric',
	        ),array(
                'field' => 'contract_number',
                'equal' => '=',
                'type'  => 'numeric',
	        ),array(
                'field' => 'parties_level',
                'equal' => '=',
                'type'  => 'numeric',
	        ),array(
                'field' => 'parties_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'parties_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),           
       		));
       	$gcategory = $this->_gcategory_mod->getAll('select cate_id,cate_name from pa_gcategory where parent_id = 0');
       	$this->assign('cate_id',$_GET['parties_type']);
       	$this->assign('parties_type',$gcategory);
    	foreach($gcategory as $k=>$v)
    	{
    			$cate[$v['cate_id']] = $v['cate_name'];
    	}
    	$_supply_list = $this->_supply_mod->getAll("select * from pa_supply su where " . $conditions . " ORDER BY su.add_time DESC limit " . $page['limit'] );
    	foreach($_supply_list as $k=>$v)
    	{
    		$_supply_list[$k]['parties_type'] = $cate[$v['parties_type']];
    	}
    	$page['item_count'] = $this->_supply_mod->getOne("select count(su.supply_id) from pa_supply su where " . $conditions);
    	$this->_format_page($page);
    	$this->assign('page_info', $page);
    	$this->assign('supply_list', $_supply_list);
    	$this->display('supply.index.html');
    }
    //添加
    public function add()
    {
    	if (!IS_POST)
    	{
    		$this->assign('site_url', site_url());
			$this->assign('regions', $this->_region_mod->get_options(0));
			$gcategory = $this->_gcategory_mod->getAll('select cate_id,cate_name from pa_gcategory where parent_id = 0');
			$this->assign('gcategory',$gcategory);
			/* 导入jQuery的表单验证插件 */
			$this->import_resource(array('script' => 'mlselection.js,jquery.plugins/jquery.validate.js'));
    		$this->display('supply.edit.html');
    	} else {
    		$time = time();
    		/**
			 *  上传图片处理
			 *  图片地址 /data/files/mall/supply/$time/	
			 *  命名 $time + 图片类型 + 图片后缀
			 **/
			//列表显示图
			$data = array();
    		if(!empty($_FILES['supply_logo']['name'])) {
				$logo_end =  strrchr(trim($_FILES['supply_logo']['name']),'.');
				if($_FILES['supply_logo']['tmp_name'] != '') {
					$logo = '/data/files/mall/supply/logo/'.$time.'/'.$time.'logo'.$logo_end;
					if(!file_exists(ROOT_PATH.'/data/files/mall/supply/logo/'.$time.'/')) {
						mkdir(ROOT_PATH.'/data/files/mall/supply/logo/'.$time.'/',0777,true);
					}
	        		move_uploaded_file(trim($_FILES['supply_logo']['tmp_name']),ROOT_PATH.'/'.$logo);
	        		$data['supply_logo'] = $logo;
				}
			}
			
    		if(!empty($_FILES['companypic']['name'])) {
				$companypic_end =  strrchr(trim($_FILES['companypic']['name']),'.');
				if($_FILES['companypic']['tmp_name'] != '') {
					$companypic = '/data/files/mall/supply/companypic/'.$time.'/'.$time.'companypic'.$companypic_end;
					if(!file_exists(ROOT_PATH.'/data/files/mall/supply/companypic/'.$time.'/')) {
						mkdir(ROOT_PATH.'/data/files/mall/supply/companypic/'.$time.'/',0777,true);
					}
	        		move_uploaded_file(trim($_FILES['companypic']['tmp_name']),ROOT_PATH.'/'.$companypic);
	        		$data['companypic'] = $companypic;
				}
			}
    		$data['user_name'] = trim($_POST['user_name']);
    		$data['password'] = intval($_POST['password']);
    		$data['supply_name'] = trim($_POST['supply_name']);
    		$data['linkman'] = trim($_POST['linkman']);
    		$data['gender'] = intval($_POST['gender']);
    		$data['mobile'] = intval($_POST['mobile']);
    		$data['telphone'] = $_POST['telphone'];
    		$data['address'] = trim($_POST['address']);
    		$data['zipcode'] = intval($_POST['zipcode']);
    		$data['sgrade'] = intval($_POST['sgrade']);
    		$data['domain'] = trim($_POST['domain']);
    		$data['state'] = intval($_POST['state']);
    		$data['add_time'] = time();
    		$data['supply_type'] = $_POST['supply_type'];
    		$data['description'] = trim($_POST['description']);
    		$data['plan'] = trim($_POST['plan']);
    		$data['summary'] = trim($_POST['summary']);
    		$data['companynum'] = intval($_POST['companynum']);
    		$data['im_qq'] = $_POST['im_qq'];
    		$data['im_ww'] = $_POST['im_ww'];
    		$data['im_msn'] = $_POST['im_msn'];
    		$data['category'] = trim($_POST['company_name']);
    		$data['region_id'] = $_POST[''];
    		$data['region_name'] = $_POST['region_name'];
    		$data['company_info'] = $_POST['company_info'];
    		$data['scale'] = $_POST['scale'];
    		$data['business_practice'] = $_POST['business_practice'];
    		$data['marketing_channel'] = $_POST['marketing_channel'];
    		$data['annual_sales'] = $_POST['annual_sales'];
    		$data['need_solved_problems'] = $_POST['need_solved_problems'];
    		$data['user_id'] = $_POST['user_id'];
    		$data['type'] = 1;;
    		$data['parties'] = trim($_POST['parties']);
    		$data['parties_type'] = $_POST['parties_type'];
    		$data['parties_time'] = strtotime($_POST['parties_time']);
    		$data['parties_level'] = $_POST['parties_level'];
    		if(strlen($_POST['contract_number']) == 12)
    		{
    			$data['contract_number'] = $_POST['contract_number'];
    		}else{
    			$this->show_warning('合同编号应为12位');
    			return;
    		}
    		if($data['parties_type'] == '' || $data['parties_type'] == null)
    		{
    			$this->show_warning('请选择类型');
    			return;
    		}
    		if(strlen($data['user_name']) <3 || strlen($data['user_name']) > 25)
    		{
    			$this->show_warning('请正确填写账号!');
    			return;
    		}
    		if(strlen($data['password']) < 6 || strlen($data['password']) > 18)
    		{
    			$this->show_warning('请正确填写密码!');
    			return;
    		}	
    		if(strlen($data['supply_name']) < 10 )
    		{
    			$this->show_warning('请正确填写供应商公司名称!');
    			return;
    		}
    		$_supply_id = $this->_supply_mod->add($data);
    		$this->show_message('添加成功',
                '返回',    'index.php?app=supply'       
                );
    	}
    }
    //编辑
    public function edit()
    {
    	$_supply_id = empty($_GET['supply_id']) ? 0 : intval($_GET['supply_id']);
		if (0 == $_supply_id)
		{
			$this->show_warning("未知的供应商!");
			return;
		}
		$_supply_info = $this->_supply_mod->getRow("select * from pa_supply su where su.supply_id = " . $_supply_id);	
    	if (!IS_POST)
    	{
	    	
			$this->assign('site_url', site_url());
			$gcategory = $this->_gcategory_mod->getAll('select cate_id,cate_name from pa_gcategory where parent_id = 0');
	    	foreach($gcategory as $k=>$v)
	    	{
	    			$cate[$v['cate_id']] = $v['cate_name'];
	    	}
	    	$_supply_info['parties_type'] = $cate[$_supply_info['parties_type']];
			$this->assign('gcategory',$gcategory);
			$this->assign('regions', $this->_region_mod->get_options(0));
			/* 导入jQuery的表单验证插件 */
			$this->import_resource(array('script' => 'mlselection.js,jquery.plugins/jquery.validate.js'));
			$this->assign('supply_info', $_supply_info);
	    	$this->display('supply.edit.html');
    	} else {
    		$data = array();  		
    		$time = time();
    	if(!empty($_FILES['supply_logo']['name'])) {
				$logo_end =  strrchr(trim($_FILES['supply_logo']['name']),'.');
				if($_FILES['supply_logo']['tmp_name'] != '') {
					$logo = '/data/files/mall/supply/logo/'.$time.'/'.$time.'logo'.$logo_end;
					if(!file_exists(ROOT_PATH.'/data/files/mall/supply/logo/'.$time.'/')) {
						mkdir(ROOT_PATH.'/data/files/mall/supply/logo/'.$time.'/',0777,true);
					}
	        		move_uploaded_file(trim($_FILES['supply_logo']['tmp_name']),ROOT_PATH.'/'.$logo);
	        		$data['supply_logo'] = $logo;
				}
			}
			
    		if(!empty($_FILES['companypic']['name'])) {
				$companypic_end =  strrchr(trim($_FILES['companypic']['name']),'.');
				if($_FILES['companypic']['tmp_name'] != '') {
					$companypic = '/data/files/mall/supply/companypic/'.$time.'/'.$time.'companypic'.$companypic_end;
					if(!file_exists(ROOT_PATH.'/data/files/mall/supply/companypic/'.$time.'/')) {
						mkdir(ROOT_PATH.'/data/files/mall/supply/companypic/'.$time.'/',0777,true);
					}
	        		move_uploaded_file(trim($_FILES['companypic']['tmp_name']),ROOT_PATH.'/'.$companypic);
	        		$data['companypic'] = $companypic;
				}
			}
    		$data['user_name'] = trim($_POST['user_name']);
    		$data['password'] = intval($_POST['password']);
    		$data['supply_name'] = trim($_POST['supply_name']);
    		$data['linkman'] = trim($_POST['linkman']);
    		$data['gender'] = intval($_POST['gender']);
    		$data['mobile'] = intval($_POST['mobile']);
    		$data['telphone'] = $_POST['telphone'];
    		$data['address'] = trim($_POST['address']);
    		$data['zipcode'] = intval($_POST['zipcode']);
    		$data['sgrade'] = intval($_POST['sgrade']);
    		$data['domain'] = trim($_POST['domain']);
    		$data['state'] = intval($_POST['state']);
    		$data['supply_type'] = $_POST['supply_type'];
    		$data['description'] = trim($_POST['description']);
    		$data['plan'] = trim($_POST['plan']);
    		$data['summary'] = trim($_POST['summary']);
    		$data['companynum'] = intval($_POST['companynum']);
    		$data['im_qq'] = $_POST['im_qq'];
    		$data['im_ww'] = $_POST['im_ww'];
    		$data['im_msn'] = $_POST['im_msn'];
    		$data['category'] = trim($_POST['company_name']);
    		$data['region_id'] = $_POST[''];
    		$data['region_name'] = $_POST['region_name'];
    		$data['company_info'] = $_POST['company_info'];
    		$data['scale'] = $_POST['scale'];
    		$data['business_practice'] = $_POST['business_practice'];
    		$data['marketing_channel'] = $_POST['marketing_channel'];
    		$data['annual_sales'] = $_POST['annual_sales'];
    		$data['need_solved_problems'] = $_POST['need_solved_problems'];
    		$data['user_id'] = $_POST['user_id'];
    		$data['parties'] = trim($_POST['parties']);
    		$data['parties_type'] = intval($_POST['parties_type']);
    		$data['parties_time'] =strtotime($_POST['parties_time']);
    		$data['parties_level'] = $_POST['parties_level'];
    		if(strlen($_POST['contract_number']) == 12)
    		{
    			$data['contract_number'] = $_POST['contract_number'];
    		}else{
    			$this->show_warning('合同编号应为12位');
    			return;
    		}
	    	if(strlen($data['user_name']) <3 || strlen($data['user_name']) > 25)
	    		{
	    			$this->show_warning('请正确填写账号!');
	    			return;
	    		}
	    		if(strlen($data['password']) < 6 || strlen($data['password']) > 18)
	    		{
	    			$this->show_warning('请正确填写密码!');
	    			return;
	    		}	
	    		if(strlen($data['supply_name']) < 10 )
	    		{
	    			$this->show_warning('请正确填写供应商公司名称!');
	    			return;
	    		}
    		$this->_supply_mod->edit($_supply_id,$data);
    		$this->show_message('编辑成功',
                '继续编辑', 'index.php?app=supply');
    	}
    }
    function goods_list()
    {
    	$supply_id= empty($_GET['supply_id']) ? 0 : $_GET['supply_id'];
    	if($supply_id == 0)
    	{
    		$this->show_message("请选择供应商");
    		return ;
    	}
    	$goods_model = &m('goods');
    	$page = $this->get_pages();
    	$pagelimit = $page['limit'];
    	//初始化条件
    	$conditions = "";
   		if(isset($_GET['goods_name']))
 		{
    		$conditions.= " AND goods_name like "."'%".$_GET['goods_name']."%'";	
    	}
    	$goods_info_count = $goods_model->getOne('select count(*) as c from pa_goods g left join pa_supply_goods sg on g.goods_id = sg.goods_id where sg.supply_id='.$supply_id.$conditions);
    	//分页条件
    	$conditions.= ' limit '.$page['limit'];    	
		$goods_info = $goods_model->getAll('select * from pa_goods g left join pa_supply_goods sg on g.goods_id = sg.goods_id where sg.supply_id='.$supply_id.$conditions);
   		//用循环给图片定向到图片同步服务器
        foreach($goods_info as $k => $v) {
            $goods_info[$k]['img'] = IMAGE_URL.$v['dimage_url'];
        }
        //var_dump($goods_info);
    	$page['item_count'] = $goods_info_count; 
    	$this->_format_page($page);
    	$this->assign('goods_info',$goods_info);
    	$this->assign('supply_id',$supply_id);
    	$this->assign('page_info', $page);
    	$this->display("supply_goodslist.html");
    }
    function goods_edit()
    {
        $goods_id = empty($_GET['goods_id']) ? 0 : intval($_GET['goods_id']);
        $supply_id= empty($_GET['supply_id']) ? 0 : $_GET['supply_id'];
    	$goods_model = &m('goods');
    	if(!IS_POST){
	    	$goods_one = $goods_model->getRow("select * from pa_goods where goods_id=".$goods_id);
	    	$this->assign('goods_one',$goods_one);
	    	$this->assign('goods_id',$goods_id);
	    	$this->assign('supply_id',$supply_id);
    		$this->display('goods_edit.html');
    	}else {
    		$goods_id = $_POST['goods_id'];
    		$commodity_code = $_POST['commodity_code'];
    		$goods_name = $_POST['goods_name'];
    		$goods_up =$goods_model->edit($goods_id, array(
    			'commodity_code' => $commodity_code,
    			'goods_name' => $goods_name
    		));
    		$supply_id= empty($_POST['supply_id']) ? 0 : $_POST['supply_id'];
    		if($goods_up){
    			$this->show_message("更新成功",'back_list','index.php?app=supply&act=goods_list&supply_id='.$supply_id);
    			return ;
    		}else {
    			$this->show_warning("更新失败",'back_list','index.php?app=supply&act=goods_list&supply_id='.$supply_id);
    			return ;
    		}
    	}

    }
}
