<?php
define('PAGE_NUM',20);
/* 渠道商控制器 */
class ChannelApp extends BackendApp
{
	private static $recomManager_info = array();
	var $algebra;
	private static $rate_arr = array();
	var $_store_order_mod;
	var $_store_order_log_mod;
	var $_store_order_extm_mod;
	var $_member_mod;
	function __construct(){
		$this->ChannelApp();
	}
 	function ChannelApp(){
    	parent::__construct();
    	$this->_store_order_mod=& m('storeorder');
    	$this->_store_order_log_mod=& m('storeorderlog');
    	$this->_store_order_extm_mod=& m('storeorderextm');
    	$this->_member_mod =& m('member');
    }
    
	//渠道商审核
    function index()
    {
    	$channelrecommend =&m('channelrecommend');    //推荐商户
     	 
        $page = $this->_get_page(30);
        $page['item_count'] = $channelrecommend->getOne("select count(*) from pa_channel_recommend cr left join 
        pa_channel_level cl on cr.level = cl.id where cr.status = 0");
        
        $users = $channelrecommend->getAll("select *,cr.id as rid from pa_channel_recommend cr left join 
        pa_channel_level cl on cr.level = cl.id where cr.status = 0 order by createtime DESC limit " . $page['limit']);
        $this->assign('users', $users);
        $this->_format_pages($page);
        $this->assign('page_info', $page);
        $this->display('channel.index.html');
    }
    
    //审核
   function verify()
    {
    	if (!IS_POST)
        {
			$channelrecommend =&m('channelrecommend');    //推荐商户
	        $info = $channelrecommend->getRow('select *,cr.id as rid from pa_channel_recommend cr left join 
	        	pa_channel_level cl on cr.level = cl.id where cr.id='.$_GET['id']);
	        //渠道商年费查询
	        $channelfee =&m('channelfee');    //加盟费用查询
	     	$fee = $channelfee->get(" level=$info[level] and area_id=$info[area_id]");
	     	if($info[recommend]){
		     	//推荐人信息
		     	$channeluser =&m('channeluser');    
			    $recominfo = $channeluser->get("channel_id=$info[recommend]");
			    //推荐人收益率
			    //$recomfee = $channelfee->get("level=$recominfo[level] and area_id=$recominfo[area_id]");
			    //推荐人银行账号查询
	    		//$channelbank =&m('channelbank');    //银行账号
	    		//$bank = $channelbank->get("channel_id=".$info['recommend']);
	    		//$this->assign('bank', $bank);
			    $this->assign('recominfo', $recominfo);
			    //$this->assign('recomfee', $recomfee);
	     	}
	     	$this->assign('fee', $fee['fee']);
	     	$this->assign('fee1', number_format($fee['fee'],2));
	     	$this->assign('fee2', number_format($fee['fee']*2,2));
	     	$this->assign('fee3', number_format($fee['fee']*3,2));
	        
	        $this->assign('info', $info);
	        $this->display('channel.verify.html');
        }
        else
        {
        	$id       = $_POST['id'];
        	if($_POST['agree']){//审核通过
        		//加盟渠道商信息查询
        		$channelrecommend =&m('channelrecommend');    //推荐商户
	        	$info = $channelrecommend->get($id);
	        	if (!$info)
	        	{
	        		$this->show_warning("未获得推荐商户!");
	        	} 
	        	
        		$channeluser =&m('channeluser');    //渠道商
		        $username = $channeluser->get("channel_name='$info[username]'");
		        $usermobile = $channeluser->get("mobile='$info[mobile]'");
		        if($info['level']==1 || $info['level']==2){
		        	$userarea = $channeluser->get("level='$info[level]' and area_id=$info[area_id]");
		        }
		        if($username){
		        	$this->show_warning('该帐号名已被注册，请重新申请！');
		        }elseif($usermobile){
		        	$this->show_warning('该手机号已被注册，请重新申请！');
		        }elseif($userarea){
		        	if($info['level']==1){
		        		$this->show_warning('该地区经营中心已存在，请重新申请！');
		        	}elseif($info['level']==2){
		        		$this->show_warning('该地区服务中心已存在，请重新申请！');
		        	}
		        }else{
		        	$data = array(
		              'channel_name' =>  $info['username'],
		              'password' =>  $info['password'],
		              'mobile'      =>   $info['mobile'],
		              'email'     =>   $info['email'],
		              'name'   =>   $info['name'],
		              'gender' =>   $info['gender'],  
		              'company'      =>   $info['company'],      
		              'address'   =>   $info['address'],
		              'identity'     =>   $info['identity'],
		              'companynum'=>   $info['companynum'],
			            'level'=>   $info['level'],
			            'area_id'=>   $info['area_id'],
			            'area_name'=>   $info['area_name'],
			            'identitypic'=>   $info['identitypic'],
		            	'companypic'=>   $info['companypic'],
			            'recommend'=>   $info['recommend'],
			        	'year'=>   $_POST['year'],
			        	'price'=>   $_POST['money'],
		        		'sn'=>  'PL'.$info['level'].'_'.$this->create_sn(),
		        		'exp_time'=>   (time()+$_POST['year']*365*24*60*60+$_POST['day']*24*60*60),
		              'reg_time'  => time(),
		            );
		
		            $cid = $channeluser->add($data);
		            if (!$cid)
		            {
		                $this->show_warning($this->get_error());
		                return;
		            }
		            $channelrecommend->edit($id ,array('status'=>1));
		            
		            //充值记录
		            $channelcharge =&  m('channelcharge');    //渠道商缴费表
            		$chargedata = array(
			              'channel_id' =>  $cid,
			              'order_sn' =>  $this->create_ordersn(),
			              'level'      =>  $info['level'],
			              'year'     =>   $_POST['year'],
            			  'exday'     =>   $_POST['day'],
			              'money'   =>   $_POST['year']*$_POST['money'],
			              'paymethod' => 'offline',
            			  'status' => 1,    
			              'createtime'  => time(),
		            );
					$channelcharge->add($chargedata);
		            
		            if($info[recommend]){//推荐商户收益
						//推荐人信息
						$channeluser =&m('channeluser');    
						$recominfo = $channeluser->get("channel_id=$info[recommend]");
						$recom_rate = $this->getRecomRate($info['area_id'],$info['level'],$recominfo['area_id'],$recominfo['level']);//获取推荐人收益率
			            $incomedata=array(
		            		  'channel_id' =>  $cid,
				              'channel_name' =>  $info['name'],
				              'email'      =>   $info['email'],
				              'level'     =>   $info['level'],
				              'recommend'   =>   $info['recommend'],
				              'money' =>   $_POST['year']*$_POST['money'],  
				              'income'      =>   $_POST['year']*$_POST['money']*($recom_rate[0]/100),      
				              'recomtime'   =>   $info['createtime']
		            	);
			        	//结算给推荐人
			            $channelrecomincome =&m('channelrecomincome');    //推荐收益表
			            $channelrecomincome->add($incomedata);
						if($recom_rate[1]){
							foreach($recom_rate[1] as $k=>$v){
								$recom_income=array(
									  'channel_id' =>  $cid,
									  'channel_name' =>  $info['name'],
									  'email'      =>   $info['email'],
									  'level'     =>   $info['level'],
									  'recommend'   =>   $k,
									  'money' =>   $_POST['year']*$_POST['money'],  
									  'income'      =>   $_POST['year']*$_POST['money']*($v/100),      
									  'recomtime'   =>   $info['createtime'],
									  'type'		=> 1
								);
								$channelrecomincome->add($recom_income);
							}
						}
		            }
		            $this->show_message('渠道商通过审核！','返回','index.php?app=channel');
		        }
        	}elseif($_POST['reject']){//审核不通过
        		$reason   		= trim($_POST['reason']);
	            if (!$reason)
	            {
	                $this->show_warning('请填写拒绝理由！');
	                return;
	            }
	            $channelrecommend =&m('channelrecommend');    //推荐商户
	            
	            $data = array(
	              'reason' =>  $reason,
	              'status' =>  2,
	            );
	
	            $channelrecommend->edit($id ,$data);
	        	if ($channelrecommend->has_error())
	            {
	                $this->show_warning($channelrecommend->get_error());
					return;
	            }
	            $this->show_message('渠道商申请已被拒绝！','返回','index.php?app=channel');
        	}
        }
    }
    
    //新增渠道商
    function add()
    {
    	if (!IS_POST)
        {
        	$channelarea =&m('region');    //地区列表
        	$area = $channelarea->find(array(
	            'fields'        =>'this.region_id,region_name',
	            'conditions'    =>' parent_id=0 ',
	            'order'         => 'region_id ASC',
	        ));
	        $channellevel_mod = & m('channellevel');
	        $channellevel_list = $channellevel_mod->find();
	        $this->assign('channellevel_list', $channellevel_list);
	        $this->assign('area', $area);
            $this->display('channel.add.html');
        }
        else
        {
        	$username       = trim($_POST['username']);
        	$password       = $_POST['password'];
        	$repassword     = $_POST['repassword'];
            $mobile   		= trim($_POST['mobile']);
            $email         	= trim($_POST['email']);
            $name   		= trim($_POST['name']);
            $gender     	= $_POST['gender'];
            $company     	= trim($_POST['company']);
            $address   		= trim($_POST['address']);
            $identity     	= trim($_POST['identity']);
            $companynum     = trim($_POST['companynum']);
            $level       	= $_POST['level'];
            $area_id2  		= $_POST['area_id2'];
            $area_id3  		= $_POST['area_id3'];
            $area_id4 		= $_POST['area_id4'];
            $area_name   	= $_POST['area_name'];
            
            $channeluser =&m('channeluser');    //渠道商
        	if (!$username)
            {
                $this->show_warning('请填写登录用户名！');
                return;
            }else{
            	$info = $channeluser->get("channel_name='$username'");
            	if($info){
            		$this->show_warning('该用户名已被注册！');
                	return;
            	}
            }
            if (strlen($password) < 6 || strlen($password) > 20)
            {
                $this->show_warning('密码长度不符，请输入6~20位密码！');
                return;
            }
        	if ($password != $repassword)
            {
                $this->show_warning('两次密码输入不一致！');
                return;
            }
        	if (!is_mobile($mobile))               //手机账户
            {
                $this->show_warning('请输入正确的手机号码！');
                return;
            }else{
            	$info = $channeluser->get("mobile='$mobile'");
            	if($info){
            		$this->show_warning('该手机号已被注册！');
                	return;
            	}
            }
            if (!is_email($email))                    //电子邮件
            {
                $this->show_warning('请输入正确的邮箱地址！');
				return;
            }
        	if (!$name)
            {
                $this->show_warning('请填写真实姓名！');
                return;
            }
        	if (!$identity)
            {
                $this->show_warning('请填写身份证号码！');
                return;
            }
        	if ($level!=4 && !$companynum)
            {
                $this->show_warning('请填写营业执照/组织机构代码证号码！');
                return;
            }
        	if (($level==1 && !$area_id2) || ($level!=1 && !$area_id3))
            {
                $this->show_warning('请选择所属区域！');
                return;
            }else{
            	if($level==1){
            		$area_id = $area_id2;
            	}else if($level == 2){
            		$area_id = $area_id3;
            	} else {
            		$area_id = $area_id4;
            	}
            }
        	//同区域存在经营中心或服务中心判断
	        if($level==1 || $level==2){
	        	$cuserarea = $channeluser->get("level='$level' and area_id='$area_id'");
	        }
        	if($cuserarea){
	        	if($level==1){
	        		$this->show_warning('该地区经营中心已存在，请重新申请！');
	        		return;
	        	}elseif($level==2){
	        		$this->show_warning('该地区服务中心已存在，请重新申请！');
	        		return;
	        	}
	        }
	        //查询年费
	        $channelfee =&m('channelfee');    //地区列表
	        $fee = $channelfee->get("area_id='$area_id' and level='$level'");
	        if(!$fee){
	        	$this->show_warning('该地区暂未开通服务，请先设置年费或重新申请！');
	        	return;
	        }
        
      		if ($_FILES['identitypic']['name'])
            {
                $identitypic  =  $this->_upload_files('identitypic');
                if(!$identitypic){
                	$this->show_warning('上传失败，请重试！');
                	return;
                }
            }else{
            	$this->show_warning('请上传身份证复印件！');
                return;
            }
        	if ($_FILES['companypic']['name'])
            {
                $companypic  =  $this->_upload_files('companypic');
            	if(!$companypic){
                	$this->show_warning('上传失败，请重试！');
                	return;
                }
            }elseif($level!=4){
            	$this->show_warning('请上传营业执照/组织机构代码证复印件！');
                return;
            }
            
            $user_id = $this->visitor->get('user_id');
            $channelrecommend =&  m('channelrecommend');    //推荐商户
            
            $data = array(
              'username' =>  $username,
              'password' =>  md5($password),
              'mobile'      =>   $mobile,
              'email'     =>   $email,
              'name'   =>   $name,
              'gender' =>   $gender,  
              'company'      =>   $company,      
              'address'   =>   $address,
              'identity'     =>   $identity,
              'companynum'=>   $companynum,
	            'level'=>   $level,
	            'area_id'=>   $area_id,
	            'area_name'=>   $area_name,
	            'identitypic'=>   $identitypic,
            	'companypic'=>   $companypic,
              'createtime'  => time(),
            );

            $id = $channelrecommend->add($data);
            if (!$id)
            {
                $this->show_warning($this->get_error());
                return;
            }
            $this->show_message('渠道信息录入成功！','返回','index.php?app=channel');
        }
    }
    
	//渠道商列表
    function channellist()
    {
		$querystr = '1 = 1';
		if($_GET['mobile']){
			$querystr.=" and mobile='".trim($_GET['mobile'])."'";
		}
		if($_GET['name']){
			$querystr.= " and name='".trim($_GET['name'])."'";
		}
		if($_GET['level']){
			$querystr.= " and level='".$_GET['level']."'";
		}
		
    	$channeluser =&m('channeluser');    //渠道商
     	$channellevel_mod = & m('channellevel');
	    $channellevel_list = $channellevel_mod->find();
	    $this->assign('channellevel_list', $channellevel_list);
        $page = $this->_get_page(30);
        $page['item_count'] = $channeluser->getOne("select count(*) from pa_channel_user cu left join 
        	pa_channel_level cl on cu.level = cl.id where " . $querystr);
        $users = $channeluser->getAll("select * from pa_channel_user cu left join 
        	pa_channel_level cl on cu.level = cl.id where " . $querystr . " order by cu.channel_id limit " . $page['limit']);
        $this->assign('users', $users);
        
        $this->_format_pages($page);
        $this->assign('page_info', $page);
        $this->display('channel.channellist.html');
    }
    
	//渠道商明细
    function info()
    {
    	$channeluser =&m('channeluser');    //渠道商
    	$info = $channeluser->get("channel_id=".$_GET['id']);         
        $this->assign('info', $info);
        $this->display('channel.info.html');
    }
    
    //添加营运数据
    function fee()
    {
    	if (!IS_POST)
        {
        	$channelarea =&m('region');    //地区列表
        	$channellevel_mod = & m('channellevel');
        	$area = $channelarea->find(array(
	            'fields'        =>'this.region_id,region_name',
	            'conditions'    =>' parent_id=0 ',
	            'order'         => 'region_id ASC',
	        ));
	        $channellevel_list = $channellevel_mod->find();
	        $this->assign('channellevel_list', $channellevel_list);
	        $this->assign('area', $area);
            $this->display('channel.fee.html');
        }
        else
        {
        	$level       	= $_POST['level'];
            $area_id2  		= $_POST['area_id2'];
            $area_id3  		= $_POST['area_id3'];
            $area_id4 		= $_POST['area_id4'];	
            $area_name   	= $_POST['area_name'];
            $fee   			= $_POST['fee'];
            $return_rate   	= $_POST['return_rate'];
            $recom_rate   	= $_POST['recom_rate'];
            $grant_credit 	= $_POST['grant_credit'];
            
            $channelfee =&m('channelfee');    //渠道商
        	
        	if (($level==1 && !$area_id2) || ($level!=1 && !$area_id3))
            {
                $this->show_warning('请选择所属区域！');
                return;
            }else{
            	if($level==1){
            		$area_id = $area_id2;
            	}else if ($level == 2) {
            		$area_id = $area_id3;
            	} else {
            		$area_id = !($area_id4) ? $area_id3 : $area_id4;
            	}
            }
        	if (!$fee)
            {
                $this->show_warning('请填写加盟年费！');
                return;
            }
        	if (!$return_rate)
            {
            	if($level != 4 || $level != 5) {
	                $this->show_warning('请填写会员消费返利收益率！');
	                return;
            	}
            }
        	if (!$recom_rate)
            {
                $this->show_warning('请填写推荐渠道商收益率！');
                return;
            }
      		$userfee = $channelfee->get("level='$level' and area_id='$area_id'");
            if($userfee){
            	$this->show_warning('您当前设置的记录已存在，不能再次添加！');
                return;
            }
            $data = array(
              	'level'=>   $level,
	            'area_id'=>   $area_id,
	            'area_name'=>   $area_name,
            	'fee'=>   $fee,
	            'return_rate'=>   $return_rate,
            	'recom_rate'=>   $recom_rate,
            	'grant_credit' => $grant_credit,
            );

            $id = $channelfee->add($data);
            if (!$id)
            {
                $this->show_warning($this->get_error());
                return;
            }
            $this->show_message('数据录入成功！');
        }
    }
    
	//运营数据列表
    function feelist()
    {
    	$channelfee =&m('channelfee');    //渠道商
     	  
        $page = $this->_get_page(30);
        $page['item_count'] = $channelfee->getOne("select count(*) from pa_channel_fee cf left join 
        	pa_channel_level cl on cf.level = cl.id");
        $users = $channelfee->getAll("select *,cf.id as fid from pa_channel_fee cf left join pa_channel_level cl on 
        	cf.level = cl.id order by cf.area_name asc, cf.area_id asc limit " . $page['limit']);
        
        $this->assign('users', $users);
        $this->_format_pages($page);
        $this->assign('page_info', $page);
        $this->display('channel.feelist.html');
    }
    
    //修改营运数据
    function editfee()
    {
    	$channelfee =&m('channelfee');    //渠道商费用查询
    	if (!IS_POST)
        {
        	$user = $channelfee->get('id='.$_GET['id']);
	        $this->assign('user', $user);
            $this->display('channel.editfee.html');
        }
        else
        {
        	$id   			= $_POST['id'];
        	$fee   			= $_POST['fee'];
            $return_rate   	= $_POST['return_rate'];
            $recom_rate   	= $_POST['recom_rate'];  
            $grant_credit 	= $_POST['grant_credit'];        
        	
        	if (!$fee)
            {
                $this->show_warning('请填写加盟年费！');
                return;
            }
        	if (!$return_rate)
            {
                $this->show_warning('请填写会员消费返利收益率！');
                return;
            }
        	if (!$recom_rate)
            {
                $this->show_warning('请填写推荐渠道商收益率！');
                return;
            }
      		
            $data = array(
              	'fee'=>   $fee,
	            'return_rate'=>   $return_rate,
            	'recom_rate'=>   $recom_rate,
            	'grant_credit' => $grant_credit,
            );

            $channelfee->edit($id,$data);
	        if ($channelfee->has_error())
	        {
	            $this->show_warning($channelfee->get_error());
				return;
	        }
            $this->show_message('数据修改成功！','返回','index.php?app=channel&act=feelist');
        }
    }
    
	//删除运营数据
    function delfee()
    {
    	$channelfee =&m('channelfee');    //渠道商
     	  
        $channelfee->drop($_GET['id']);
    	if ($channelfee->has_error())
        {
            $this->show_warning($channelfee->get_error());
			return;
        }
        $this->show_message('删除数据成功！');
    }
    
    //商户收益管理
	function income()
    {
		$querystr = ' where 1=1 ';
		if($_GET['name']){
			$querystr.=" and c.name='".trim($_GET['name'])."'";
		}
		if($_GET['mobile']){
			$querystr.=" and c.mobile='".trim($_GET['mobile'])."'";
		}
		
    	$channelrecomincome =&m('channelrecomincome');    //推荐收益查询
     	$page = $this->_get_page(30);
        $sql = "select r.id,r.channel_name,r.income,r.recomtime,r.status,r.type,r.closetime,cl.level_name,c.name,c.gender,c.mobile,c.level,c.area_name from pa_channel_recomincome r 
     			left join pa_channel_user c on c.channel_id=r.recommend left join pa_channel_level cl on c.level = cl.id $querystr
     			order by r.id desc limit ".$page['limit'];
     	$users = $channelrecomincome->getAll($sql);
        
     	$sqlc = "select count(r.id) from pa_channel_recomincome r 
     			left join pa_channel_user c on c.channel_id=r.recommend left join pa_channel_level cl on c.level = cl.id $querystr";
     	$page['item_count'] = $channelrecomincome->getOne($sqlc);
        
        $this->assign('users', $users);
        $this->_format_pages($page);
        $this->assign('page_info', $page);
        
        $this->display('channel.income.html');
    }
    
    //商户推荐收益结算
	function incomeset()
    {
    	$channelrecomincome =&m('channelrecomincome');    //推荐商户
    	if(!IS_POST){
    		$user = $channelrecomincome->get("id=$_GET[id]");
    		//银行账号查询
    		$channelbank =&m('channelbank');    //银行账号
    		$bank = $channelbank->get("channel_id=".$user['recommend']);
    		$this->assign('user', $user);
    		$this->assign('bank', $bank);
    		$this->display('channel.incomeset.html');
    	}else{
    		if (!trim($_POST['bank']))
            {
                $this->show_warning('请填写银行名称（其他方式结算请注明）！');
                return;
            }
    		if (!trim($_POST['bankuser']))
            {
                $this->show_warning('请填写银行开户名（其他方式结算请填写收款人姓名）！');
                return;
            }
    		$data = array(
    			'status' =>  1,
    			'type' =>  $_POST['type'],
	    		'bank' =>  trim($_POST['bank']),
	    		'bankcard' =>  trim($_POST['bankcard']),
	    		'bankuser' =>  trim($_POST['bankuser']),
	    		'chargecode' =>  trim($_POST['chargecode']),
	        	'closetime' =>  time(),
	        );
			$channelrecomincome->edit($_POST['id'] ,$data);
	        if ($channelrecomincome->has_error())
	        {
		        $this->show_warning($channelrecomincome->get_error());
				return;
	        }
	        $this->show_message('渠道商推荐收益已结算！','返回','index.php?app=channel&act=income');
    	}
    }
    
    //商户缴费管理
	function charge()
    {
		$querystr = '';
		if($_GET['name']){
			$querystr.=" and c.name='".trim($_GET['name'])."'";
		}
		if($_GET['mobile']){
			$querystr.=" and c.mobile='".trim($_GET['mobile'])."'";
		}
		
    	$channelcharge =&  m('channelcharge');    //推荐缴费查询
     	$page = $this->_get_page(30);
        $sql = "select r.order_sn,r.year,r.exday,r.money,r.paymethod,r.createtime,cl.level_name,c.name,c.gender,c.mobile,c.level,c.area_name from pa_channel_charge r 
     			left join pa_channel_user c on c.channel_id=r.channel_id left join pa_channel_level cl on c.level = cl.id 
     			where r.status=1 $querystr order by r.id desc limit ".$page['limit'];
     	$users = $channelcharge->getAll($sql);
        
     	$sqlc = "select count(r.id) from pa_channel_charge r 
     			 left join pa_channel_user c on c.channel_id=r.channel_id left join pa_channel_level cl on c.level = cl.id 
     			 where r.status=1 $querystr";
     	$page['item_count'] = $channelcharge->getOne($sqlc);
        
        $this->assign('users', $users);
        $this->_format_pages($page);
        $this->assign('page_info', $page);
        
        $this->display('channel.charge.html');
    }
    
   //发消息
    function sendmsg()
    {
    if (!IS_POST)
        {
        	$channeluser =&m('channeluser');    //渠道商
        	$info = $channeluser->get($_GET['id']);
        	$this->assign('id', $_GET['id']);
        	$this->assign('name', $info['name']);
        	$this->display('channel.sendmsg.html');
        }
        else
        {
        	$title       	= $_POST['title'];
            $content  		= $_POST['content'];
            $id  		= $_POST['id'];
            
            $channelmsg =&m('channelmessage');    //渠道商消息
        	
        	if (!$title)
            {
                $this->show_warning('请填写消息主题！');
                return;
            }
        	if (!$content)
            {
                $this->show_warning('请填写消息内容！');
                return;
            }
            $data = array(
              	'title'=>   $title,
	            'content'=>   $content,
	            'to_id'=>   $id,
            	'createtime'=>   time(),
            );

            $id = $channelmsg->add($data);
            if (!$id)
            {
                $this->show_warning($this->get_error());
                return;
            }
            $this->show_message('消息发送成功！','返回','index.php?app=channel&act=channellist');
        }
    }
    
    //ajax获取渠道商区域信息
	function selectarea()
    {
    	header("Content-Type:text/html;charset=gbk"); 
    	$channelarea =&m('region');    //地区列表
        $area = $channelarea->find(array(
            'fields'        =>'this.region_id,region_name',
            'conditions'    =>' parent_id='.$_POST['parent_id'],
            'order'         => 'region_id ASC',
        ));
        foreach($area as $value){
        	$areaSel.='<option value="'.$value['region_id'].'">'.$value['region_name'].'</option>';
        }
        if($areaSel){
        	echo '<select id="area_id'.$_POST['level'].'" name="area_id'.$_POST['level'].'" onchange="selectarea('.($_POST['level']+1).', this);"><option value="">请选择...</option>'.$areaSel.'</select>';
        }
    }
    
	//ajax查询年费
	function checkfee()
    {
    	header("Content-Type:text/html;charset=gbk"); 
    	$channelfee =&m('channelfee');    //地区列表
        $fee = $channelfee->get(' area_id='.$_POST['area_id'].' and level='.$_POST['level']);
        if($fee['fee']){
        	echo $fee['fee'];
        }else{
        	echo '暂不支持当前设置';
        }
    }
    
    //根据上一级区域ID查找渠道商地区id
    function findarea($ids)
    {
    	$channelarea =&m('region');    //地区列表
        $areas = $channelarea->find(array(
            'fields'        =>'this.region_id',
            'conditions'    =>' parent_id IN ('.$ids.') ',
            'order'         => 'region_id ASC',
        ));
        $dot = '';
        foreach($areas as $v){
        	$area .= $dot.$v['region_id'];
        	$dot = ',';
        }
        return $area;
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
   	 
   	 function create_sn() {
		/* 选择一个随机的方案 */
	    mt_srand((double) microtime() * 1000000);
	    return  date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
	}
   	 function create_ordersn() {
		/* 选择一个随机的方案 */
	    mt_srand((double) microtime() * 1000000);
	    return  date('Ymd') . str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
	}
	
	//计算推荐人收益
	function getRecomRate($area,$level,$recom_area,$recom_level){
		$channelfee = & m('channelfee');    //查询渠道商费用表
		$channelUser_mod = & m('channeluser');
		$channelLevel_mod = & m('channellevel');
		$channelLevel_info = $channelLevel_mod->get("id = " . $level );
		$recomChannelLevel_info = $channelLevel_mod->get("id = " . $recom_level);
		//直接推荐人返利
		if ($this->isIdenticalArea($area, $level, $recom_area, $recom_level)) //在同地区
		{
			$recom_info = $channelfee->get(" level = " . $recom_level . " and area_id = " . $recom_area);
			$recom_rate = $recom_info['recom_rate'];
			$channelUser_info = $channelUser_mod->get(" level = " . $recom_level . " and area_id = " . $recom_area);
			$this->getParentArea($area, $channelLevel_info['parent_level'], $recom_rate, $channelUser_info['channel_id'], $recomChannelLevel_info['hierarchy'], true);
		} else { //不在同地区
			$recom_info = $channelfee->get(" level = " . $level . " and area_id = " . $area);
			$recom_rate = $recom_info['recom_rate'];
			$channelUser_info = $channelUser_mod->get(" level = " . $recom_level . " and area_id = " . $recom_area);
			$this->getParentArea($area, $channelLevel_info['parent_level'], $recom_rate, $channelUser_info['channel_id'], $recomChannelLevel_info['hierarchy'], false);
		}
		//直接推荐人的channel_id

		return array(0=>$recom_rate,1=>self::$rate_arr);
	}
	
	public function getParentArea($area, $level, $rate, $channel_id, $recom_hierarchy, $flag)  //间接推荐返利
	{
		//首先寻找直接上级
		$channelLevel_mod = & m('channellevel');
		$channelUser_mod = & m('channeluser');
		$region_mod = & m('region');
		$channelFee_mod = & m('channelfee');
		$channelLevel_info = $channelLevel_mod->get("id = " . $level );
		
		$area_id = 0; //定义要获取返利的地区ID
		//找直接上级的地区
		$area_info = $region_mod->get($area);
		if ($area_info['level'] > $channelLevel_info['area_level']) //上级渠道所属区域在本级的上级区域 
		{
			//寻找区域
			$region_info = $region_mod->getRow("select * from pa_region where region_id = (select parent_id from pa_region where region_id = " . $area . ")");
			$area_id = $region_info['region_id'];
		} else { //上级区域所在的地区和本级所在的区域相同
			$area_id = $area;
		}
		//查找fee , 获取应得返利
		$fee_info = $channelFee_mod->get(" area_id = " . $area_id . " AND level = " . $level); //上级返利信息
		
		$channelUser_info = $channelUser_mod->get(" area_id = " . $area_id . " AND level = " . $level); //查看是否在这当前等级渠道商,如果有就定稿返利数组
		if ($flag)
		{
			if ($channelUser_info && $channelUser_info['channel_id'] != $channel_id && $channelLevel_info['hierarchy'] < $recom_hierarchy) {
				self::$rate_arr[$channelUser_info['channel_id']] = $fee_info['recom_rate'] - $rate;
			}
		} else {
			if ($channelUser_info && $channelUser_info['channel_id'] != $channel_id) {
				self::$rate_arr[$channelUser_info['channel_id']] = $fee_info['recom_rate'] - $rate;
			}
		}
		$rate = $fee_info['recom_rate'];
		
		if ($channelLevel_info['parent_level'] == 0) { //如果上级的parent_id为0,  表示本级为最上级.  不需要往上返利
			return false;
		}
		$this->getParentArea($area_id, $channelLevel_info['parent_level'], $rate, $channel_id, $recom_hierarchy, $flag);
	}
	
	public function getBelongArea($area)
	{
		$region_mod = & m("region");
		$region_info = $region_mod->get($area);
		if ($region_info['level'] == 1)
		{
			$this->show_warning("您的地区填写不正确.请选择 地/市 级.");
			return false;
		} 
		if ($region_info['level'] == 2) //如果是市级直接返回 
		{
			return $region_info['region_id'];
		} 
		//下面做递归操作
		return $this->getBelongArea($region_info['parent_id']);
	}
	
	public function isIdenticalArea($area,$level,$recom_area,$recom_level)
	{
		if (($this->getBelongArea($area)) == ($this->getBelongArea($recom_area)))
		{
			return true;
		} else {
			return false;
		}
		
	}
	
	//渠道商费用数组查询推荐收益率
	function checkfee_arr($fee,$area,$level){
		foreach ($fee as $v){
			if($v['area_id']==$area && $v['level']==$level){
				return $v['recom_rate'];exit;
			}
		}
	}
	
	//判断是否存在间接推荐人
	function check_recom($area,$level){
		$channeluser =&m('channeluser');    //渠道商
		$cuserarea = $channeluser->get("level='$level' and area_id='$area' and exp_time>".time());
		if($cuserarea){
			return $cuserarea['channel_id'];
		}		
	}
	
	//客户经理提现管理
	function managerIncome()
	{
		$page = $this->_get_page(PAGE_NUM);
		$customerWithdrawAsk_mod = & m('customerwithdrawask');
		$page['item_count'] = $customerWithdrawAsk_mod->getOne("select count(*) from pa_customer_withdraw_ask cw 
			left join pa_customer_manager cm on cw.user_id = cm.user_id left join pa_withdraw_type wt on 
			cw.draw_type = wt.id");
		
		$customerWithdrawAsk_list = $customerWithdrawAsk_mod->getAll("select *,wt.draw_type as draw_type_content from pa_customer_withdraw_ask cw 
			left join pa_customer_manager cm on cw.user_id = cm.user_id left join pa_withdraw_type wt on 
			cw.draw_type = wt.id limit " . $page['limit']);
		$this->_format_page($page);
		$this->assign('page_info', $page);
		$this->assign('customerWithdrawAsk_list', $customerWithdrawAsk_list);
		
		$this->display('channel.managerIncome.html');
	}
	//客户经理收益管理
	function managerCash()
	{
		$customerGains_mod = & m('customergains');
		$page = $this->_get_page(PAGE_NUM);
		$condt=$_GET['condt'];
		$level=empty($_GET['level']) ? 0 : intval($_GET['level']);
		$gains_type=empty($_GET['gains_type']) ? 0 : intval($_GET['gains_type']);
		$user_name=empty($_GET['user_name']) ? '' : trim($_GET['user_name']);
		$real_name=empty($_GET['real_name']) ? '' :trim($_GET['real_name']);
		$conditions = " 1=1";
		$this->assign('condt',$condt);
		switch ($condt){
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
		}
		switch ($level){
			case 0:
				$conditions .= " AND 1 = 1 ";
				$this->assign('level',0);
				break;
			case 1:
				$conditions .= " AND cl.level_id = 1 ";
				$this->assign('level',1);
				break;
			case 2:
				$conditions .= " AND cl.level_id = 2 ";
				$this->assign('level',2);
				break;
			case 3:
				$conditions .= " AND cl.level_id = 3 ";
				$this->assign('level',3);
				break;
			default : 
        		$this->show_warning("等级搜索程序出错！ "); 
        		return;
		}
		switch ($gains_type){
			case 0:
				$conditions .= " AND 1 = 1 ";
				$this->assign('gains_type',0);
				break;
			case 1:
				$conditions .= " AND cg.gains_type = 1 ";
				$this->assign('gains_type',1);
				break;
			case 2:
				$conditions .= " AND cg.gains_type = 2 ";
				$this->assign('gains_type',2);
				break;
			case 3:
				$conditions .= " AND cg.gains_type = 3 ";
				$this->assign('gains_type',3);
				break;
			case -1: 
        		$conditions .= " AND cg.gains_type = 0";
        		$this->assign('gains_type',-1);
        		break;
			default : 
        		$this->show_warning("收益类型搜索程序出错！ "); 
        		return;
		}
		//排序
    	if (isset($_GET['sort']) && isset($_GET['order']))
			        {
			            $sort  = strtolower(trim($_GET['sort']));
			            $order = strtolower(trim($_GET['order']));
			            if (!in_array($order,array('asc','desc')))
			            {
			             $sort  = 'gains_time';
			             $order = 'desc';
			            }
			        }
			        else
			        {
			            $sort  = 'gains_time';
			            $order = 'desc';
			        }
		$page['item_count'] = $customerGains_mod->getOne('select count(*) from pa_customer_gains cg left join 
			pa_customer_manager cm on cg.user_id = cm.user_id left join pa_customer_level cl on 
			cm.customer_level = cl.level_id where ' .$conditions);
		$customerGains_list = $customerGains_mod->getAll('select * from pa_customer_gains cg left join 
			pa_customer_manager cm on cg.user_id = cm.user_id left join pa_customer_level cl on 
			cm.customer_level = cl.level_id where ' . $conditions . 
			" order by " . $sort . ' ' . $order  . ' limit ' . $page['limit']);
		$this->_format_page($page);
		$this->assign('page_info', $page);
		$this->assign('customerGains_list', $customerGains_list);
		$this->display('channel.managerCash.html');
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
		$mobile=empty($_GET['mobile']) ? '' :trim($_GET['mobile']);
		$cuser_name=empty($_GET['cuser_name']) ? '' :trim($_GET['cuser_name']);
		$algebra = empty($_GET['algebra']) ? 0 :intval($_GET['algebra']);
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
    
 	public function manager() //审核列表
    {
    	$customerAsk_mod = & m('customerask');
    	$page = $this->_get_page(PAGE_NUM);
    	$page['item_count'] = $customerAsk_mod->getOne('select count(*) from pa_customer_ask');
    	
    	$customerAsk_list = $customerAsk_mod->getAll('select * from pa_customer_ask limit ' . $page['limit']);
    	
    	$this->_format_page($page);
    	
    	$this->assign('customerAsk_list', $customerAsk_list);
    	
    	$this->assign('page_info', $page);
    	$this->assign('chan','true');
    	$this->display('channel.manager.html');
    }
   	 
    public function verifyManager() //审核团购员
    {
    	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	$customerAsk_mod = & m('customerask');
    	$customer_info = $customerAsk_mod->getRow('select ca.*,cm.tel_phone as mobile from pa_customer_ask ca left join pa_customer_level cl on 
    	ca.need_level = cl.level_id left join pa_customer_manager cm on cm.user_id = ca.recom_user_id  where ca.id = ' . $id);	
    	if ($id == 0)
    	{
    		$this->show_warning('程序错误...');
    	}
       	if (!$customer_info)
    	{
    		$this->show_warning('程序错误...');
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
    			//判断本人是否为客户经理
    			$customerManager_mod = & m('customermanager');
    			$customerManager_info = $customerManager_mod->get($customer_info['user_id']);
    			$data = array(
					'vstatus' => 1,	
    			);
    			$customerAsk_mod->edit($id,$data); 			
    			$this->show_message('审核成功',
    								'继续审核','index.php?app=channel&act=manager');
    		} else {
    			$this->clearManagerAsk($id);
    			/* 连接用户系统 */
		        $ms =& ms();
		        $pmsg_id = $ms->pm->send(MSG_SYSTEM,$customer_info['user_id'], '申请团购员失败', "尊敬的用户".$customer_info['real_name']."您提交的团购员申请未通过渠道运作中心的审核，详情请咨询客服：400-166-1616");
    			$this->show_message('审核成功',
    								'继续审核','index.php?app=channel&act=manager');
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
    //转账管理
    function transfer_accounts()
    {
    	$user_name = empty($_GET['user_name']) ? 'all' : trim($_GET['user_name']);
    	$type = empty($_GET['type']) ? 'all' : trim($_GET['type']);
		$conditions = " where 1=1 ";
    	if($user_name != 'all')
		{
			$conditions .= " and m.user_name like '%".$user_name."%'";
		}
		if($type != 'all')
		{
			$conditions .= " and al.change_type in (".$type.")";
		}else {
			$conditions .= " and al.change_type in (42,43)";
		}
    	$accountlog_mod = &m ('accountlog');
    	$page = $this->_get_page(10);
    	$tr_account = $accountlog_mod->getAll('select * from pa_account_log al left join pa_member m on m.user_id=al.user_id '.$conditions.' limit '.$page['limit']);
    	$user_count = $accountlog_mod->getRow('SELECT count(*) as count from pa_account_log al left join pa_member m on m.user_id=al.user_id'.$conditions);
    	$change_type = get_change_type();
    	foreach ($tr_account as $k=>$v)
		{
			$tr_account[$k]['change_type'] = $change_type[$v['change_type']];
			$tr_account[$k]['change_time'] = $v['change_time'];
			$tr_account[$k]['user_money'] = abs($v['user_money']);
			$tr_account[$k]['user_credit'] = abs($v['user_credit']);
		}
    	$page['item_count'] = $user_count['count'];        
        $this->_format_page($page);
		$this->assign('page_info', $page);
    	$this->assign('users',$tr_account);
    	$this->display('tr_account.html');
    }	
    
    /**
     *    店铺进货订单管理
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return    void
     */
    function store_order()
    {
        $search_options = array(
            's.store_name'   => '店铺名称',
            'so.payment_name'   => Lang::get('payment_name'),
            'so.order_sn'   => Lang::get('order_sn'),
        );
        /* 默认搜索的字段是店铺名 */
        $field = 's.store_name';
        $status=empty($_GET['status']) ? 11 : intval($_GET['status']);
        $payment_id=empty($_GET['payment_id']) ? '' : trim($_GET['payment_id']);

        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = '1=1';
        $conditions .= $this->_get_query_conditions(array(array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'store_type',
                'equal' => '=',
                'type'  => 'numeric',
	        ),array(
                'field' => 'status',
                'equal' => '=',
                'type'  => 'numeric',
            ),array(
                'field' => 'op_status',
                'equal' => '=',
                'type'  => 'numeric',
            ),array(
                'field' => 'so.add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'so.add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'so.order_amount',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'so.order_amount',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
    	if(!$payment_id == '') {
        	$conditions .= " AND s.payment_id = " . $payment_id;
        	$this->assign('payment_id',$payment_id);
        }
        $model_order =& m('storeorder');
        $page   =   $this->_get_page(20);    //获取分页信息
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'add_time';
             $order = 'desc';
            }
        }
        else
        {
            $sort  = 'add_time';
            $order = 'desc';
        }

        $store_order_info = $this->_store_order_mod->getAll('select so.order_id,so.order_sn,so.goods_amount,s.store_type,so.pay_message,so.order_amount,s.store_name,so.payment_name,so.add_time,soe.shipping_fee,so.status,so.op_status from pa_store_order
       												 so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id where
       												  '."$conditions".'  ORDER BY so.add_time DESC limit '.$page['limit']) ;

        //统计总数
       	$page['item_count'] = $this->_store_order_mod->getOne('select count(*) from pa_store_order so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id
       															 where '."$conditions");
        $this->_format_page($page);
        $this->assign('filtered', $conditions != '1=1'? 1 : 0); //是否有查询条件
        $this->assign('order_status_list', array(
            ORDER_PENDING => Lang::get('待付款'),
            ORDER_ACCEPTED => Lang::get('待发货'),
            ORDER_SHIPPED => Lang::get('已发货'),
            ORDER_FINISHED => Lang::get('交易成功'),
            ORDER_REFUND => Lang::get('退款中'),
            ORDER_REFUND_FINISH => Lang::get('退款完成'),
            ORDER_CANCELED => Lang::get('交易取消'),
        ));
        $this->assign('op_status_list', array(
            0 => Lang::get('未操作'),
            1 => Lang::get('物流已更改物流费用'),
            2 => Lang::get('店面管理已确认订单价格'),
            3 => Lang::get('财务已确认收款信息'),
            4 => Lang::get('物流已确认发货'),
        ));
        $this->assign('store_type',array(
       				'0' => '直营店',
       				'1' => '加盟店',
       		));
       	foreach ($store_order_info as $_key => $_val)
	     {
	       $all_amount['order_amount'] += $_val['order_amount'];
	       $all_amount['goods_amount'] += $_val['goods_amount'];
	       $all_amount['shipping_fee'] += $_val['shipping_fee'];
	       $all_amount['pay_amount'] += $_val['pay_amount'];
	       $all_amount['arrears_amount'] += $_val['arrears_amount'];
	     }
		$this->assign('all_amount',$all_amount);
			
        $this->assign('search_options', $search_options);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('orders', $store_order_info);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        Lang::load(lang_file('admin/store_order'));
        $this->assign('app',APP);
        $this->display('store_order.index.html');
    }
    //显示店铺进货订单详情
	function view()
    {
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        /* 获取订单信息 */
        $order_info = $this->_store_order_mod->get(array(
            'conditions'    => $order_id,
            'join'          => 'has_storeorderextm',
            'include'       => array(
                'has_storeordergoods',   //取出订单商品
            ),
        ));
       
        if (!$order_info)
        {
            $this->show_warning('no_such_order');
            return;
        }
        
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_store_order_detail($order_id, $order_info);
        Lang::load(lang_file('admin/store_order'));
        
        $this->assign('app',APP);
        $this->assign('order',$order_info);
        $this->assign('image_url',IMAGE_URL);
        $this->assign('order_detail',$order_detail['data']);
        $this->display('store_order.view.html');
    }
    //渠道审核订单信息---确定订单预付金额
    function audit_store_order()
    {
    	$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    	/* 获取订单信息 */
        $order_info = $this->_store_order_mod->get($order_id);
       
        if (!$order_info)
        {
            $this->show_warning('no_such_order');
            return;
        }
        
        $yufu_money = floatval(trim($_POST['yufu_amount']));
        
        if ($yufu_money > 0 && $yufu_money <= $order_info['order_amount'])
        {
	        $data['pay_amount']  = $yufu_money; //实付金额
	        $data['arrears_amount']  = $order_info['order_amount'] - $data['pay_amount']; //实付金额
        }elseif($yufu_money == 0) 
        {
        	$data['pay_amount']  = $order_info['order_amount'];
        }else
        {
        	$this->show_warning('订单预付金额输入错误！');
            return;
        }
        
        $data['op_status'] = 2;
        
		$this->_store_order_mod->edit($order_id,$data);
		$this->show_message('审核订单信息成功！',"返回列表",'index.php?app=channel&act=store_order');
    }
	public function _get_member_count($user_id)
	{
		$all_amount = $this->_member_mod->getRow("SELECT SUM(goods_amount) as all_amount from pa_order where status in (20,30,40) and buyer_id=".$user_id);
		$this->assign('amount',$all_amount);
		$this->assign('achievement',ACHIEVEMENT);
	}
}

?>
