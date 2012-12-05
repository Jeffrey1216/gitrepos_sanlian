<?php
define('PAGE_NUM',20);
/* �����̿����� */
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
    
	//���������
    function index()
    {
    	$channelrecommend =&m('channelrecommend');    //�Ƽ��̻�
     	 
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
    
    //���
   function verify()
    {
    	if (!IS_POST)
        {
			$channelrecommend =&m('channelrecommend');    //�Ƽ��̻�
	        $info = $channelrecommend->getRow('select *,cr.id as rid from pa_channel_recommend cr left join 
	        	pa_channel_level cl on cr.level = cl.id where cr.id='.$_GET['id']);
	        //��������Ѳ�ѯ
	        $channelfee =&m('channelfee');    //���˷��ò�ѯ
	     	$fee = $channelfee->get(" level=$info[level] and area_id=$info[area_id]");
	     	if($info[recommend]){
		     	//�Ƽ�����Ϣ
		     	$channeluser =&m('channeluser');    
			    $recominfo = $channeluser->get("channel_id=$info[recommend]");
			    //�Ƽ���������
			    //$recomfee = $channelfee->get("level=$recominfo[level] and area_id=$recominfo[area_id]");
			    //�Ƽ��������˺Ų�ѯ
	    		//$channelbank =&m('channelbank');    //�����˺�
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
        	if($_POST['agree']){//���ͨ��
        		//������������Ϣ��ѯ
        		$channelrecommend =&m('channelrecommend');    //�Ƽ��̻�
	        	$info = $channelrecommend->get($id);
	        	if (!$info)
	        	{
	        		$this->show_warning("δ����Ƽ��̻�!");
	        	} 
	        	
        		$channeluser =&m('channeluser');    //������
		        $username = $channeluser->get("channel_name='$info[username]'");
		        $usermobile = $channeluser->get("mobile='$info[mobile]'");
		        if($info['level']==1 || $info['level']==2){
		        	$userarea = $channeluser->get("level='$info[level]' and area_id=$info[area_id]");
		        }
		        if($username){
		        	$this->show_warning('���ʺ����ѱ�ע�ᣬ���������룡');
		        }elseif($usermobile){
		        	$this->show_warning('���ֻ����ѱ�ע�ᣬ���������룡');
		        }elseif($userarea){
		        	if($info['level']==1){
		        		$this->show_warning('�õ�����Ӫ�����Ѵ��ڣ����������룡');
		        	}elseif($info['level']==2){
		        		$this->show_warning('�õ������������Ѵ��ڣ����������룡');
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
		            
		            //��ֵ��¼
		            $channelcharge =&  m('channelcharge');    //�����̽ɷѱ�
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
		            
		            if($info[recommend]){//�Ƽ��̻�����
						//�Ƽ�����Ϣ
						$channeluser =&m('channeluser');    
						$recominfo = $channeluser->get("channel_id=$info[recommend]");
						$recom_rate = $this->getRecomRate($info['area_id'],$info['level'],$recominfo['area_id'],$recominfo['level']);//��ȡ�Ƽ���������
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
			        	//������Ƽ���
			            $channelrecomincome =&m('channelrecomincome');    //�Ƽ������
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
		            $this->show_message('������ͨ����ˣ�','����','index.php?app=channel');
		        }
        	}elseif($_POST['reject']){//��˲�ͨ��
        		$reason   		= trim($_POST['reason']);
	            if (!$reason)
	            {
	                $this->show_warning('����д�ܾ����ɣ�');
	                return;
	            }
	            $channelrecommend =&m('channelrecommend');    //�Ƽ��̻�
	            
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
	            $this->show_message('�����������ѱ��ܾ���','����','index.php?app=channel');
        	}
        }
    }
    
    //����������
    function add()
    {
    	if (!IS_POST)
        {
        	$channelarea =&m('region');    //�����б�
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
            
            $channeluser =&m('channeluser');    //������
        	if (!$username)
            {
                $this->show_warning('����д��¼�û�����');
                return;
            }else{
            	$info = $channeluser->get("channel_name='$username'");
            	if($info){
            		$this->show_warning('���û����ѱ�ע�ᣡ');
                	return;
            	}
            }
            if (strlen($password) < 6 || strlen($password) > 20)
            {
                $this->show_warning('���볤�Ȳ�����������6~20λ���룡');
                return;
            }
        	if ($password != $repassword)
            {
                $this->show_warning('�����������벻һ�£�');
                return;
            }
        	if (!is_mobile($mobile))               //�ֻ��˻�
            {
                $this->show_warning('��������ȷ���ֻ����룡');
                return;
            }else{
            	$info = $channeluser->get("mobile='$mobile'");
            	if($info){
            		$this->show_warning('���ֻ����ѱ�ע�ᣡ');
                	return;
            	}
            }
            if (!is_email($email))                    //�����ʼ�
            {
                $this->show_warning('��������ȷ�������ַ��');
				return;
            }
        	if (!$name)
            {
                $this->show_warning('����д��ʵ������');
                return;
            }
        	if (!$identity)
            {
                $this->show_warning('����д���֤���룡');
                return;
            }
        	if ($level!=4 && !$companynum)
            {
                $this->show_warning('����дӪҵִ��/��֯��������֤���룡');
                return;
            }
        	if (($level==1 && !$area_id2) || ($level!=1 && !$area_id3))
            {
                $this->show_warning('��ѡ����������');
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
        	//ͬ������ھ�Ӫ���Ļ���������ж�
	        if($level==1 || $level==2){
	        	$cuserarea = $channeluser->get("level='$level' and area_id='$area_id'");
	        }
        	if($cuserarea){
	        	if($level==1){
	        		$this->show_warning('�õ�����Ӫ�����Ѵ��ڣ����������룡');
	        		return;
	        	}elseif($level==2){
	        		$this->show_warning('�õ������������Ѵ��ڣ����������룡');
	        		return;
	        	}
	        }
	        //��ѯ���
	        $channelfee =&m('channelfee');    //�����б�
	        $fee = $channelfee->get("area_id='$area_id' and level='$level'");
	        if(!$fee){
	        	$this->show_warning('�õ�����δ��ͨ��������������ѻ��������룡');
	        	return;
	        }
        
      		if ($_FILES['identitypic']['name'])
            {
                $identitypic  =  $this->_upload_files('identitypic');
                if(!$identitypic){
                	$this->show_warning('�ϴ�ʧ�ܣ������ԣ�');
                	return;
                }
            }else{
            	$this->show_warning('���ϴ����֤��ӡ����');
                return;
            }
        	if ($_FILES['companypic']['name'])
            {
                $companypic  =  $this->_upload_files('companypic');
            	if(!$companypic){
                	$this->show_warning('�ϴ�ʧ�ܣ������ԣ�');
                	return;
                }
            }elseif($level!=4){
            	$this->show_warning('���ϴ�Ӫҵִ��/��֯��������֤��ӡ����');
                return;
            }
            
            $user_id = $this->visitor->get('user_id');
            $channelrecommend =&  m('channelrecommend');    //�Ƽ��̻�
            
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
            $this->show_message('������Ϣ¼��ɹ���','����','index.php?app=channel');
        }
    }
    
	//�������б�
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
		
    	$channeluser =&m('channeluser');    //������
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
    
	//��������ϸ
    function info()
    {
    	$channeluser =&m('channeluser');    //������
    	$info = $channeluser->get("channel_id=".$_GET['id']);         
        $this->assign('info', $info);
        $this->display('channel.info.html');
    }
    
    //���Ӫ������
    function fee()
    {
    	if (!IS_POST)
        {
        	$channelarea =&m('region');    //�����б�
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
            
            $channelfee =&m('channelfee');    //������
        	
        	if (($level==1 && !$area_id2) || ($level!=1 && !$area_id3))
            {
                $this->show_warning('��ѡ����������');
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
                $this->show_warning('����д������ѣ�');
                return;
            }
        	if (!$return_rate)
            {
            	if($level != 4 || $level != 5) {
	                $this->show_warning('����д��Ա���ѷ��������ʣ�');
	                return;
            	}
            }
        	if (!$recom_rate)
            {
                $this->show_warning('����д�Ƽ������������ʣ�');
                return;
            }
      		$userfee = $channelfee->get("level='$level' and area_id='$area_id'");
            if($userfee){
            	$this->show_warning('����ǰ���õļ�¼�Ѵ��ڣ������ٴ���ӣ�');
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
            $this->show_message('����¼��ɹ���');
        }
    }
    
	//��Ӫ�����б�
    function feelist()
    {
    	$channelfee =&m('channelfee');    //������
     	  
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
    
    //�޸�Ӫ������
    function editfee()
    {
    	$channelfee =&m('channelfee');    //�����̷��ò�ѯ
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
                $this->show_warning('����д������ѣ�');
                return;
            }
        	if (!$return_rate)
            {
                $this->show_warning('����д��Ա���ѷ��������ʣ�');
                return;
            }
        	if (!$recom_rate)
            {
                $this->show_warning('����д�Ƽ������������ʣ�');
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
            $this->show_message('�����޸ĳɹ���','����','index.php?app=channel&act=feelist');
        }
    }
    
	//ɾ����Ӫ����
    function delfee()
    {
    	$channelfee =&m('channelfee');    //������
     	  
        $channelfee->drop($_GET['id']);
    	if ($channelfee->has_error())
        {
            $this->show_warning($channelfee->get_error());
			return;
        }
        $this->show_message('ɾ�����ݳɹ���');
    }
    
    //�̻��������
	function income()
    {
		$querystr = ' where 1=1 ';
		if($_GET['name']){
			$querystr.=" and c.name='".trim($_GET['name'])."'";
		}
		if($_GET['mobile']){
			$querystr.=" and c.mobile='".trim($_GET['mobile'])."'";
		}
		
    	$channelrecomincome =&m('channelrecomincome');    //�Ƽ������ѯ
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
    
    //�̻��Ƽ��������
	function incomeset()
    {
    	$channelrecomincome =&m('channelrecomincome');    //�Ƽ��̻�
    	if(!IS_POST){
    		$user = $channelrecomincome->get("id=$_GET[id]");
    		//�����˺Ų�ѯ
    		$channelbank =&m('channelbank');    //�����˺�
    		$bank = $channelbank->get("channel_id=".$user['recommend']);
    		$this->assign('user', $user);
    		$this->assign('bank', $bank);
    		$this->display('channel.incomeset.html');
    	}else{
    		if (!trim($_POST['bank']))
            {
                $this->show_warning('����д�������ƣ�������ʽ������ע������');
                return;
            }
    		if (!trim($_POST['bankuser']))
            {
                $this->show_warning('����д���п�������������ʽ��������д�տ�����������');
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
	        $this->show_message('�������Ƽ������ѽ��㣡','����','index.php?app=channel&act=income');
    	}
    }
    
    //�̻��ɷѹ���
	function charge()
    {
		$querystr = '';
		if($_GET['name']){
			$querystr.=" and c.name='".trim($_GET['name'])."'";
		}
		if($_GET['mobile']){
			$querystr.=" and c.mobile='".trim($_GET['mobile'])."'";
		}
		
    	$channelcharge =&  m('channelcharge');    //�Ƽ��ɷѲ�ѯ
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
    
   //����Ϣ
    function sendmsg()
    {
    if (!IS_POST)
        {
        	$channeluser =&m('channeluser');    //������
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
            
            $channelmsg =&m('channelmessage');    //��������Ϣ
        	
        	if (!$title)
            {
                $this->show_warning('����д��Ϣ���⣡');
                return;
            }
        	if (!$content)
            {
                $this->show_warning('����д��Ϣ���ݣ�');
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
            $this->show_message('��Ϣ���ͳɹ���','����','index.php?app=channel&act=channellist');
        }
    }
    
    //ajax��ȡ������������Ϣ
	function selectarea()
    {
    	header("Content-Type:text/html;charset=gbk"); 
    	$channelarea =&m('region');    //�����б�
        $area = $channelarea->find(array(
            'fields'        =>'this.region_id,region_name',
            'conditions'    =>' parent_id='.$_POST['parent_id'],
            'order'         => 'region_id ASC',
        ));
        foreach($area as $value){
        	$areaSel.='<option value="'.$value['region_id'].'">'.$value['region_name'].'</option>';
        }
        if($areaSel){
        	echo '<select id="area_id'.$_POST['level'].'" name="area_id'.$_POST['level'].'" onchange="selectarea('.($_POST['level']+1).', this);"><option value="">��ѡ��...</option>'.$areaSel.'</select>';
        }
    }
    
	//ajax��ѯ���
	function checkfee()
    {
    	header("Content-Type:text/html;charset=gbk"); 
    	$channelfee =&m('channelfee');    //�����б�
        $fee = $channelfee->get(' area_id='.$_POST['area_id'].' and level='.$_POST['level']);
        if($fee['fee']){
        	echo $fee['fee'];
        }else{
        	echo '�ݲ�֧�ֵ�ǰ����';
        }
    }
    
    //������һ������ID���������̵���id
    function findarea($ids)
    {
    	$channelarea =&m('region');    //�����б�
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
		/* ѡ��һ������ķ��� */
	    mt_srand((double) microtime() * 1000000);
	    return  date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
	}
   	 function create_ordersn() {
		/* ѡ��һ������ķ��� */
	    mt_srand((double) microtime() * 1000000);
	    return  date('Ymd') . str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
	}
	
	//�����Ƽ�������
	function getRecomRate($area,$level,$recom_area,$recom_level){
		$channelfee = & m('channelfee');    //��ѯ�����̷��ñ�
		$channelUser_mod = & m('channeluser');
		$channelLevel_mod = & m('channellevel');
		$channelLevel_info = $channelLevel_mod->get("id = " . $level );
		$recomChannelLevel_info = $channelLevel_mod->get("id = " . $recom_level);
		//ֱ���Ƽ��˷���
		if ($this->isIdenticalArea($area, $level, $recom_area, $recom_level)) //��ͬ����
		{
			$recom_info = $channelfee->get(" level = " . $recom_level . " and area_id = " . $recom_area);
			$recom_rate = $recom_info['recom_rate'];
			$channelUser_info = $channelUser_mod->get(" level = " . $recom_level . " and area_id = " . $recom_area);
			$this->getParentArea($area, $channelLevel_info['parent_level'], $recom_rate, $channelUser_info['channel_id'], $recomChannelLevel_info['hierarchy'], true);
		} else { //����ͬ����
			$recom_info = $channelfee->get(" level = " . $level . " and area_id = " . $area);
			$recom_rate = $recom_info['recom_rate'];
			$channelUser_info = $channelUser_mod->get(" level = " . $recom_level . " and area_id = " . $recom_area);
			$this->getParentArea($area, $channelLevel_info['parent_level'], $recom_rate, $channelUser_info['channel_id'], $recomChannelLevel_info['hierarchy'], false);
		}
		//ֱ���Ƽ��˵�channel_id

		return array(0=>$recom_rate,1=>self::$rate_arr);
	}
	
	public function getParentArea($area, $level, $rate, $channel_id, $recom_hierarchy, $flag)  //����Ƽ�����
	{
		//����Ѱ��ֱ���ϼ�
		$channelLevel_mod = & m('channellevel');
		$channelUser_mod = & m('channeluser');
		$region_mod = & m('region');
		$channelFee_mod = & m('channelfee');
		$channelLevel_info = $channelLevel_mod->get("id = " . $level );
		
		$area_id = 0; //����Ҫ��ȡ�����ĵ���ID
		//��ֱ���ϼ��ĵ���
		$area_info = $region_mod->get($area);
		if ($area_info['level'] > $channelLevel_info['area_level']) //�ϼ��������������ڱ������ϼ����� 
		{
			//Ѱ������
			$region_info = $region_mod->getRow("select * from pa_region where region_id = (select parent_id from pa_region where region_id = " . $area . ")");
			$area_id = $region_info['region_id'];
		} else { //�ϼ��������ڵĵ����ͱ������ڵ�������ͬ
			$area_id = $area;
		}
		//����fee , ��ȡӦ�÷���
		$fee_info = $channelFee_mod->get(" area_id = " . $area_id . " AND level = " . $level); //�ϼ�������Ϣ
		
		$channelUser_info = $channelUser_mod->get(" area_id = " . $area_id . " AND level = " . $level); //�鿴�Ƿ����⵱ǰ�ȼ�������,����оͶ��巵������
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
		
		if ($channelLevel_info['parent_level'] == 0) { //����ϼ���parent_idΪ0,  ��ʾ����Ϊ���ϼ�.  ����Ҫ���Ϸ���
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
			$this->show_warning("���ĵ�����д����ȷ.��ѡ�� ��/�� ��.");
			return false;
		} 
		if ($region_info['level'] == 2) //������м�ֱ�ӷ��� 
		{
			return $region_info['region_id'];
		} 
		//�������ݹ����
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
	
	//�����̷��������ѯ�Ƽ�������
	function checkfee_arr($fee,$area,$level){
		foreach ($fee as $v){
			if($v['area_id']==$area && $v['level']==$level){
				return $v['recom_rate'];exit;
			}
		}
	}
	
	//�ж��Ƿ���ڼ���Ƽ���
	function check_recom($area,$level){
		$channeluser =&m('channeluser');    //������
		$cuserarea = $channeluser->get("level='$level' and area_id='$area' and exp_time>".time());
		if($cuserarea){
			return $cuserarea['channel_id'];
		}		
	}
	
	//�ͻ��������ֹ���
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
	//�ͻ������������
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
			//��Ա��
			case 0:
				if(!$user_name == ''){
					$conditions .= " AND cm.user_name like '%".$user_name."%' ";
			        $this->assign('user_name',$user_name);
				}
				break;
			//��ʵ����
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
        		$this->show_warning("�ȼ������������ "); 
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
        		$this->show_warning("������������������� "); 
        		return;
		}
		//����
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
	public function manager_check() //����
    {
    	//��ȡ�����Ź�����Ϣ
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
			//��Ա��
			case 0:
				if(!$user_name == ''){
					$conditions .= " AND cm.user_name like '%".$user_name."%' ";
			        $this->assign('user_name',$user_name);
				}
				break;
			//��ʵ����
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
	        		$this->show_warning("������� "); 
	        		return;
	        }
    	//����
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
    
 	public function manager() //����б�
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
   	 
    public function verifyManager() //����Ź�Ա
    {
    	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	$customerAsk_mod = & m('customerask');
    	$customer_info = $customerAsk_mod->getRow('select ca.*,cm.tel_phone as mobile from pa_customer_ask ca left join pa_customer_level cl on 
    	ca.need_level = cl.level_id left join pa_customer_manager cm on cm.user_id = ca.recom_user_id  where ca.id = ' . $id);	
    	if ($id == 0)
    	{
    		$this->show_warning('�������...');
    	}
       	if (!$customer_info)
    	{
    		$this->show_warning('�������...');
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
    			//�жϱ����Ƿ�Ϊ�ͻ�����
    			$customerManager_mod = & m('customermanager');
    			$customerManager_info = $customerManager_mod->get($customer_info['user_id']);
    			$data = array(
					'vstatus' => 1,	
    			);
    			$customerAsk_mod->edit($id,$data); 			
    			$this->show_message('��˳ɹ�',
    								'�������','index.php?app=channel&act=manager');
    		} else {
    			$this->clearManagerAsk($id);
    			/* �����û�ϵͳ */
		        $ms =& ms();
		        $pmsg_id = $ms->pm->send(MSG_SYSTEM,$customer_info['user_id'], '�����Ź�Աʧ��', "�𾴵��û�".$customer_info['real_name']."���ύ���Ź�Ա����δͨ�������������ĵ���ˣ���������ѯ�ͷ���400-166-1616");
    			$this->show_message('��˳ɹ�',
    								'�������','index.php?app=channel&act=manager');
    		}
    	}
    }
    //��ȡ�Ƽ�������,   �Ƽ��˵ļ���Ϊ����� key , ��ȡ������Ϊ val
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
    	$page = $this->_get_page();   //��ȡ��ҳ��Ϣ
		
		$customermanager_mod = &m('customermanager');
  		$unintvitgroup = $customermanager_mod->getAll('select * from pa_customer_manager cm left join pa_customer_level cl on cl.level_id=cm.customer_level where parent_id='.$user_id.' limit '.$page['limit']);
  		$item_count = $customermanager_mod->getRow('select count(*) as count from pa_customer_manager where parent_id='.$user_id);
  		$page['item_count'] = $item_count['count'];
  		$this->assign('ungroup',$unintvitgroup);
  		$this->_format_page($page);
        $this->assign('page_info', $page);
        $this->display('ch_underling.html');
    }
    //ת�˹���
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
     *    ���̽�����������
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return    void
     */
    function store_order()
    {
        $search_options = array(
            's.store_name'   => '��������',
            'so.payment_name'   => Lang::get('payment_name'),
            'so.order_sn'   => Lang::get('order_sn'),
        );
        /* Ĭ���������ֶ��ǵ����� */
        $field = 's.store_name';
        $status=empty($_GET['status']) ? 11 : intval($_GET['status']);
        $payment_id=empty($_GET['payment_id']) ? '' : trim($_GET['payment_id']);

        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = '1=1';
        $conditions .= $this->_get_query_conditions(array(array(
                'field' => $field,       //���û���,������,֧����ʽ���ƽ�������
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
        $page   =   $this->_get_page(20);    //��ȡ��ҳ��Ϣ
        //��������
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

        //ͳ������
       	$page['item_count'] = $this->_store_order_mod->getOne('select count(*) from pa_store_order so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id
       															 where '."$conditions");
        $this->_format_page($page);
        $this->assign('filtered', $conditions != '1=1'? 1 : 0); //�Ƿ��в�ѯ����
        $this->assign('order_status_list', array(
            ORDER_PENDING => Lang::get('������'),
            ORDER_ACCEPTED => Lang::get('������'),
            ORDER_SHIPPED => Lang::get('�ѷ���'),
            ORDER_FINISHED => Lang::get('���׳ɹ�'),
            ORDER_REFUND => Lang::get('�˿���'),
            ORDER_REFUND_FINISH => Lang::get('�˿����'),
            ORDER_CANCELED => Lang::get('����ȡ��'),
        ));
        $this->assign('op_status_list', array(
            0 => Lang::get('δ����'),
            1 => Lang::get('�����Ѹ�����������'),
            2 => Lang::get('���������ȷ�϶����۸�'),
            3 => Lang::get('������ȷ���տ���Ϣ'),
            4 => Lang::get('������ȷ�Ϸ���'),
        ));
        $this->assign('store_type',array(
       				'0' => 'ֱӪ��',
       				'1' => '���˵�',
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
        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->assign('orders', $store_order_info);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        Lang::load(lang_file('admin/store_order'));
        $this->assign('app',APP);
        $this->display('store_order.index.html');
    }
    //��ʾ���̽�����������
	function view()
    {
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        /* ��ȡ������Ϣ */
        $order_info = $this->_store_order_mod->get(array(
            'conditions'    => $order_id,
            'join'          => 'has_storeorderextm',
            'include'       => array(
                'has_storeordergoods',   //ȡ��������Ʒ
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
    //������˶�����Ϣ---ȷ������Ԥ�����
    function audit_store_order()
    {
    	$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    	/* ��ȡ������Ϣ */
        $order_info = $this->_store_order_mod->get($order_id);
       
        if (!$order_info)
        {
            $this->show_warning('no_such_order');
            return;
        }
        
        $yufu_money = floatval(trim($_POST['yufu_amount']));
        
        if ($yufu_money > 0 && $yufu_money <= $order_info['order_amount'])
        {
	        $data['pay_amount']  = $yufu_money; //ʵ�����
	        $data['arrears_amount']  = $order_info['order_amount'] - $data['pay_amount']; //ʵ�����
        }elseif($yufu_money == 0) 
        {
        	$data['pay_amount']  = $order_info['order_amount'];
        }else
        {
        	$this->show_warning('����Ԥ������������');
            return;
        }
        
        $data['op_status'] = 2;
        
		$this->_store_order_mod->edit($order_id,$data);
		$this->show_message('��˶�����Ϣ�ɹ���',"�����б�",'index.php?app=channel&act=store_order');
    }
	public function _get_member_count($user_id)
	{
		$all_amount = $this->_member_mod->getRow("SELECT SUM(goods_amount) as all_amount from pa_order where status in (20,30,40) and buyer_id=".$user_id);
		$this->assign('amount',$all_amount);
		$this->assign('achievement',ACHIEVEMENT);
	}
}

?>
