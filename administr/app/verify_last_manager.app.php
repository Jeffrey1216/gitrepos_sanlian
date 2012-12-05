<?php
define('PAGE_NUM',20);
/* 渠道商控制器 */
class Verify_last_managerApp extends BackendApp
{
	private static $recomManager_info = array();
	var $algebra;
	private static $rate_arr = array();
	var $_member_mod;
	function __construct(){
		$this->Verify_last_managerApp();
	}
 	function Verify_last_managerApp(){
    	parent::__construct();
    	$this->assign('finance','true');
    	$this->_member_mod = & m('member');
    	
    }   	 
 	function index() //审核列表
    {
    	$customerAsk_mod = & m('customerask');
    	$page = $this->_get_page(PAGE_NUM);
    	$page['item_count'] = $customerAsk_mod->getOne('select count(*) from pa_customer_ask');
    	
    	$customerAsk_list = $customerAsk_mod->getAll('select * from pa_customer_ask limit ' . $page['limit']);
    	
    	$this->_format_page($page);
    	
    	$this->assign('customerAsk_list', $customerAsk_list);    	
    	$this->assign('page_info', $page);
    	$this->display('channel.manager.html');
    }
	public function manager_check() //管理
    {
    	//获取所有团购点信息
    	$customerManager_mod = & m('customermanager');
    	$page = $this->_get_page(PAGE_NUM);
		$condt=$_GET['condt'];
		$level= empty($_GET['level']) ? 0 : intval($_GET['level']);
		$user_name=empty($_GET['user_name']) ? '' : trim($_GET['user_name']);
		$real_name=empty($_GET['real_name']) ? '' :trim($_GET['real_name']);
		$mobile=empty($_GET['mobile']) ? '' : trim($_GET['mobile']);
		$cuser_name=empty($_GET['cuser_name']) ? '' : trim($_GET['cuser_name']);
		$algebra = empty($_GET['algebra']) ? 0 : intval($_GET['algebra']);
		$conditions = " 1=1";
		$this->assign('condt',$condt);
		switch($condt){
			//会员名
			case 0:
				if(!$user_name == ''){
					$conditions .= " AND cm.user_name like '%".$user_name."%' ";
			        $this->assign('user_name',$user_name);
				}
				break;
			//真实姓名
			case 1:
				if(!$real_name == ''){
					$conditions .= " AND cm.real_name like '%".$real_name."%' ";
					$this->assign('real_name',$real_name);
				}
				break;
			case 2:
				if(!$mobile == ''){
					$conditions .= " AND mr.mobile like '%".$mobile."%' ";
					$this->assign('mobile',$mobile);
				}
				break;
			case 3:
				if(!$cuser_name == ''){
					$conditions .= " AND cm1.user_name like '%".$cuser_name."%' ";
					$this->assign('cuser_name',$cuser_name);
				}
				break;
			case 4:
				if(!$algebra == 0){
					$conditions .= " AND cm.algebra = ".$algebra;
					$this->assign('algebra',$algebra);
				}
				break;
			}
	    switch($level) {
	        	case 0: 
	        		$conditions .= " AND 1 = 1";
	        		$this->assign('level',0);
	        		break;
	        	case 1: 
	        		$conditions .= " AND cl.level_id = 1";
	        		$this->assign('level',1);
	        		break;
	        	case 2: 
	        		$conditions .= " AND cl.level_id = 2";
	        		$this->assign('level',2);
	        		break;
	        	case 3: 
	        		$conditions .= " AND cl.level_id = 3";
	        		$this->assign('level',3);
	        		break;
	        	case 4: 
	        		$conditions .= " AND cl.level_id = 4";
	        		$this->assign('level',4);
	        		break;
	        	default : 
	        		$this->show_warning("程序出错！ "); 
	        		return;
	        }
    	//排序
    	if (isset($_GET['sort']) && isset($_GET['order']))
			        {
			            $sort  = strtolower(trim($_GET['sort']));
			            $order = strtolower(trim($_GET['order']));
			            if (!in_array($order,array('asc','desc')))
			            {
			             $sort  = 'mr.reg_time';
			             $order = 'desc';
			            }
			        }
			        else
			        {
			            $sort  = 'mr.reg_time';
			            $order = 'desc';
			        }
		$page['item_count'] = $customerManager_mod->getOne("select count(*) from pa_customer_manager cm left join 
    		pa_customer_level cl on cm.customer_level = cl.level_id left join pa_member mr on mr.user_id = cm.user_id left join pa_customer_manager cm1 on cm.parent_id=cm1.user_id where " .$conditions);
    	$customerManager_list = $customerManager_mod->getAll("select cm.*,cl.*,cm1.user_name as cuser_name,cm1.user_id as cuser_id,mr.mobile from pa_customer_manager cm left join 
    		pa_customer_level cl on cm.customer_level = cl.level_id left join pa_member mr on mr.user_id = cm.user_id left join pa_customer_manager cm1 on cm.parent_id=cm1.user_id where " .$conditions . 
    		" order by " . $sort . ' ' . $order  . " limit " . $page['limit']);
    	
    	$this->_format_page($page);
    	$this->assign('customerManager_list', $customerManager_list);

    	$this->assign('page_info', $page);
    	$this->display('channel.manager_check.html');
    }
	//团购员 , 详情查询
	public function manager_detail()
	{
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if (0 == $id)
		{
			$this->show_warning('查询失败, 没有这个团购员!');
			return;
		}
		$customerManager_mod = & m('customermanager');
		$customerLevel_mod = & m('customerlevel');
		if (!IS_POST)
		{
			$customerManager_info = $customerManager_mod->getRow('select * from pa_customer_manager cm left join 
	    		pa_customer_level cl on cm.customer_level = cl.level_id where cm.user_id = ' . $id);			
			$customerLevel_info = $customerLevel_mod->find();
			$this->assign('levels', $customerLevel_info);
			$this->assign('customerManager_info', $customerManager_info);			
			$this->display('channel.managerdetail.html'); //详情页,  
		}
		else
		{
			$level_id = empty($_POST['customer_level']) ? 0 : intval($_POST['customer_level']);
			if ($level_id != 0)
			{
				$customerManager_mod->edit($id, array('customer_level' => $level_id));		
			}			
			$this->show_message('编辑成功', '继续编辑', 'index.php?app=verify_last_manager&act=manager_detail&id=' . $id);
		}
	}
    function manager_underling()
    {
    	$user_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	$page = $this->_get_page();   //获取分页信息
		
		$customermanager_mod = &m('customermanager');
  		$unintvitgroup = $customermanager_mod->getAll('select * from pa_customer_manager cm left join pa_customer_level cl on cl.level_id=cm.customer_level where parent_id='.$user_id.' limit '.$page['limit']);
  		$item_count = $customermanager_mod->getRow('select count(*) as count from pa_customer_manager where parent_id='.$user_id);
  		$page['item_count'] = $item_count['count'];
  		$this->assign('ungroup',$unintvitgroup);
  		$this->_format_page($page);
        $this->assign('page_info', $page);
        $this->display('ch_underling.html');
    }
	function _upload_files($style)
   	 {
        import('uploader.lib');
        $data      = array();
        /* acticle_logo */
        $file = $_FILES[$style];
      
        if ($file['error'] == UPLOAD_ERR_OK && $file !='')
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->allowed_size(1024000); // 1M
            $uploader->addFile($file);
            if ($uploader->file_info() === false)
            {
                $this->show_warning($uploader->get_error());
                return false;
            }
            $uploader->root_dir(ROOT_PATH);
        	$filename  = $uploader->random_filename();
            $fileurl = $uploader->save('data/files/partner/'.$style, $filename);
        }
        return $fileurl;
   	 }   	 
    public function verifyManager() //审核团购员
    {
    	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	$customerAsk_mod = & m('customerask');
    	$customerManager_mod = & m('customermanager');
    	$customer_info = $customerAsk_mod->getRow('select ca.*,cm.tel_phone as mobile from pa_customer_ask ca left join pa_customer_level cl on 
    	ca.need_level = cl.level_id left join pa_customer_manager cm on cm.user_id = ca.recom_user_id  where ca.id = ' . $id);
    	if ($id == 0)
    	{
    		$this->show_warning('程序错误...');
    		return ;
    	}
        if (!$customer_info)
    	{
    		$this->show_warning('程序错误...');
    		return ;
    	}
    	if($customer_info['vstatus'] != 1)
    	{
    		$this->show_warning('程序错误...');
    		return ;
    	}
    	if (!IS_POST)
    	{
    		$this->assign('info', $customer_info);
    		$this->_get_member_count($customer_info['user_id']);
    		$membercard_mod = & m('member_card');
    		$member_info = $membercard_mod->get_all($customer_info['user_id']);
			$this->assign('member_info',$member_info);
    		$this->display('channel.verifymanager.html');
    	} else {
    		if (trim($_POST['submit']) == '1')
    		{
    			$algebra = $this->get_algebra($customer_info);
    			$data = array(
    				'user_id' => $customer_info['user_id'],
	    			'user_name' => $customer_info['user_name'],
	    			'outstanding_achievement_total' => 0,
    				'gains_total' => 0,
	    			'parent_id' => $customer_info['recom_user_id'],
	    			'customer_level' => $customer_info['need_level'],
	    			'real_name' => $customer_info['real_name'],
	    			'sex' => $customer_info['sex'],
	    			'identity_num' => $customer_info['identity_num'],
	    			'identity_card' => $customer_info['identity_card'],
	    			'tel_phone' => $customer_info['tel_phone'],
	    			'email' => $customer_info['email'],
	    			'region_id' => $customer_info['region_id'],
	    			'region_name' => $customer_info['region_name'],
	    			'address' => $customer_info['address'],
    				'reg_time' => gmtime(),
    				'algebra'  => $algebra,
    				'card_number' => $customer_info['card_number'],
    				'bank_name' => $customer_info['bank_name'],
    				);
    			$customerManager_mod->add($data);
    			/* 连接用户系统 */
		        $ms =& ms();
		        $pmsg_id = $ms->pm->send(MSG_SYSTEM,$customer_info['user_id'], '申请团购员通过', "尊敬的用户".$customer_info['real_name']."您提交的团购员申请已通过，详情请咨询客服：400-166-1616");
    		
    			$user_info = $this->_member_mod->get($customer_info['user_id']);
    			if(!$user_info['invite_id'])
    			{
    				$data = array(
    					'invite_id' => $customer_info['recom_user_id'],
    				);
    				$this->_member_mod->edit($customer_info['user_id'],$data);
    			}else {
    				$cusManager= $customerManager_mod->get($user_info['invite_id']);
    				if(!$cusManager)
    				{
    					 $data = array(
    						'invite_id' => $customer_info['recom_user_id'],
    					 );
    					 $this->_member_mod->edit($customer_info['user_id'],$data);
    				}
    			}
    			$this->clearManagerAsk($id);
    			$this->show_message('审核成功',
    								'继续审核','index.php?app=verify_last_manager');
    		} else {
    			$this->clearManagerAsk($id);
    			/* 连接用户系统 */
		        $ms =& ms();
		        $pmsg_id = $ms->pm->send(MSG_SYSTEM,$customer_info['user_id'], '申请团购员未通过', "尊敬的用户".$customer_info['real_name']."您提交的团购员申请未通过，详情请咨询客服：400-166-1616");
    			$this->show_message('审核成功',
    								'继续审核','index.php?app=verify_last_manager');
    		}
    	}
    }
    //获取推荐人数组,   推荐人的级别为数组的 key , 获取收益率为 val
    private function getRecomManager($id, $key)
    {
    	$customerManager_mod = & m('customermanager');
    	self::$recomManager_info[$key] = $customerManager_mod->getRow('select cm.user_id, cm.parent_id, 
    	cl.recom_yidle_level_' . $key . ' as recom_yidle, cm.gains_total, cm.gains_now, 
    	cm.outstanding_achievement_total from pa_customer_manager cm left join pa_customer_level cl on 
    	cm.customer_level = cl.level_id where cm.user_id = ' . $id);
    	
    	if (self::$recomManager_info[$key]['parent_id'] != 0 && $key <= 5)
    	{
    		$id = self::$recomManager_info[$key]['parent_id'];
    		$key = $key + 1;		
    		$this->getRecomManager($id, $key);
    	}
    }
    
    private function clearManagerAsk($id)
    {
    	$customerAsk_mod = & m('customerask');
    	$customerAsk_mod->drop($id);
    } 
    function detail()
    {
    	$user_mod = & m('member');
    	$page = $this->_get_page(20);
    	$user_info = $user_mod->getAll('SELECT * from pa_account_log al where al.user_id=0 and al.change_type in(54,55,56,57) order by al.change_time desc limit '.$page['limit']);
		$change_type = get_change_type();
		foreach ($user_info as $k=>$v)
		{
			$user_info[$k]['change_type'] = $change_type[$v['change_type']];
			$user_info[$k]['change_time'] = $v['change_time'];
		}
    	$user_count = $user_mod->getRow('SELECT count(*) as count from pa_account_log al where al.user_id=0 and al.change_type in(54,55,56,57)');
        $page['item_count'] = $user_count['count'];        
        $this->_format_page($page);
        $this->assign('page_info', $page);
    	$this->assign('users',$user_info);
    	$this->display('channel.detail.html');
    } 
    //获取团购员代数
    function get_algebra($user)
    {
    	if($user['recom_user_id'] == 0)
    	{
    		return 1;  		
    	}else {
    		$user_id = intval($user['recom_user_id']);
    		$algeb = $this->_get_last($user_id);
    		return $algeb;	
    	}
    }
    private function _get_last($user_id,$algebra = 2){
		$customerManager_mod = & m('customermanager');
		$cust_info = $customerManager_mod->get($user_id); //查找上一代
		$u_id = intval($cust_info['parent_id']);			
		$this->algebra = $algebra;
		if ($u_id > 0)
		{
			$algebra++;	
			$this->algebra = $algebra;		
			$this->_get_last($u_id,$this->algebra);
		}
		return $this->algebra;
    } 	
	public function _get_member_count($user_id)
	{
		$all_amount = $this->_member_mod->getRow("SELECT SUM(goods_amount) as all_amount from pa_order where status=40 and buyer_id=".$user_id);
		$this->assign('amount',$all_amount);
		$this->assign('achievement',ACHIEVEMENT);
	}
}
?>
