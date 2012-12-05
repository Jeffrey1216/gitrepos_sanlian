<?php

/**
 *    店铺会员管理控制器
 *
 *    @author   lihuoliang
 *    @date     2011/09/03
 *    @usage    none
 */
class My_memberApp extends StoreadminbaseApp
{
    var $_member_mod;

    function __construct()
    {
        $this->My_memberAppApp();
    }

    function My_memberAppApp()
    {
        parent::__construct();
        $this->_member_mod = &m('member');
    }

    /**
     *    会员列表
     *
     *    @author    lihuoliang
     *    @return    $memberlist
     */
    function member_list()
    {
    	//获取搜索条件
        $conditions = $this->_get_conditions();
        import('Page.class');
        $count = $this->_get_members($conditions,false,true); //总条数
        $listRows= 10;        //每页显示条数
        $page=new Page($count,$listRows); //初始化对象
        //获取会员信息
        $member_list = $this->_get_members($conditions,$page);
	
        $this->assign('member_list', $member_list);
        
		$page->setConfig('header', '条记录');
        $p=$page->show();
        $this->assign('page',$p);
        $this->display('storeadmin.mymember.list.html');
    }

    /**
     *    店铺会员注册
     *
     *    @author    lihuoliang
     *    @return    void
     */
    function register()
    {
    	$store_id = intval($this->visitor->get('manage_store'));
        if (!IS_POST)
        {
        	//导入脚本验证的js
        	$this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));          
        	$this->display('storeadmin.mymember.register.html');
        }
        else
        {
        	$user_name = trim($_POST['user_name']);
            $password  = substr($_POST['mobile'],-6,6);
            $email     = trim($_POST['email']);
            $mobile     = trim($_POST['mobile']);
            $smsverify     = trim($_POST['sms_verify']);
            $trpwd = trim($_POST['trpwd']);
            $trpwd2 = trim($_POST['trpwd2']);
            
            if ($user_name)
            {
	            $user_name_len = strlen($user_name);
	            if ($user_name_len < 3 || $user_name_len > 25)
	            {
	                $this->show_storeadmin_warning('user_name_length_error');
	
	                return;
	            }
            }else 
            {
            	$user_name = $mobile;
            }
            
            if ($email)
            {
	            if (!is_email($email))
	            {
	                $this->show_storeadmin_warning('email_error');
	
	                return;
	            }
            }else
            {
            	$email = $mobile.'@qq.com';
            }
        	
        	if (!is_mobile($mobile))
            {
                $this->show_storeadmin_warning('mobile_error');

                return;
            }
            if ($_SESSION['smsverifydata']['verify'] != $smsverify || $_SESSION['smsverifydata']['mobile'] != $mobile)
            {
            	$this->show_storeadmin_warning('smsverify_error');

                return;
            }
            $time = time() - $_SESSION['smsverifydata']['dateline'];
        	if ($time>3600)
            {
            	$this->show_storeadmin_warning('smsverify_invalid');

                return;
            }
        	if (!$trpwd||!$trpwd2)
            {
	             $this->show_storeadmin_warning('交易密码不能为空');
            }else{
            	if (count($trpwd)!=6)
            	{
            		$this->show_storeadmin_warning('交易密码只能为6位数字');
            	}
            	if ($trpwd!=$trpwd2)
            	{
            		$this->show_storeadmin_warning('两次交易密码不一致');
            	}
            }
            $ms =& ms(); //连接用户中心
            $data['mobile'] = $mobile;
            $data['invite_id']    = $store_id;
            $mobilearea = &  m('mobile');  //实例化手机区域表
            $data['mobilearea'] = $mobilearea->get_areaname_by_mobile($mobile);
            $data['trader_password'] = $ms->user->getMd5TraderPassword($trpwd);
            $user_id = $ms->user->register($user_name, $password, $email,$data);

            if (!$user_id)
            {
                $this->show_storeadmin_warning($ms->user->get_error());

                return;
            }else
            {
            	
            	//本店会员注册成功---发送短消息
            	
        		import('class.smswebservice');    //导入短信发送类
        		$sms = SmsWebservice::instance(); //实例化短信接口类
        		$smscontent = str_replace('{password}',$password,Lang::get('smscontent.register_success'));

        		$result= $sms->SendSms2($mobile,$smscontent); //执行发送短信验证码操作
        		//短信发送成功
        		if ($result == 0) 
        		{
        			//执行短信日志写入操作
        			$smsdata['mobile'] = $mobile;
        			$smsdata['smscontent'] = $smscontent;
        			$smsdata['type'] = 'register_success'; //注册成功短信
        			$smsdata['sendtime'] = gmtime();
        			$smslog =&  m('smslog'); 
        			$smslog->add($smsdata);
        		}
            	$this->show_storeadmin_message('恭喜用户'.$mobile.'，成功注册为本店会员');
            }
        }
    }
    
    
    /**
     *    获取搜索条件
     *
     *    @author    lihuoliang
     *    @return    void
     */
    function _get_conditions()
    {
        /* 搜索条件 */
    	$sid = intval($this->visitor->get('manage_store'));
        $conditions = "invite_id=$sid";
        if (trim($_GET['user_name']))
        {
            $conditions .= " AND user_name LIKE '%" . trim($_GET['user_name']) . "%'";
        }
        if ($_GET['mobile'])
        {
        	$conditions .= " AND mobile LIKE '%" . trim($_GET['mobile']) . "%'";
        }
        if ($_GET['areaname'])
        {
        	$conditions .= " AND mobilearea LIKE '%" . trim($_GET['areaname']) . "%'";
        }
        if ($_GET['starttime'])
        {
        	$conditions .= " AND reg_time >=" . strtotime($_GET['starttime']);
        }
        if ($_GET['endtime'])
        {
        	$conditions .= " AND reg_time <=" . strtotime($_GET['endtime'].' 23:59:59');
        }
        return $conditions;
    }
    /* 渠道操作 */
    public function channelmsg() {
    	$channel_user_mod = & m('channeluser');
    	$member_mod = & m('member');
    	$store_mod = & m('store');
  		$channel_fee_mod = & m('channelfee');
  		$user_id = $this->visitor->get('user_id');
    	if($this->checkIsChannel()) { // 已绑定渠道
    		/* 个人账户信息 */
	    	$info = $this->get($user_id,false,false,true);
	    	
	    	$pms = $this->get_list($user_id, 100, 'newpm'); 
	    	$channelfee =&m('channelfee');    //加盟费用查询
	     	$fee = $channelfee->get(" level=" . $info['level'] . " and area_id = " . $info['area_id'] ."");
	     	//var_dump($pms['count']);exit;
	    	$this->assign('count', $pms['count']); 		    	   
	        $this->assign('ip', $_SERVER["REMOTE_ADDR"]);   
	        $this->assign('user', $info);  
	        $this->assign('fee', $fee); 
	        /* 个人资料 */
	        
    		$this->display("storeadmin.channelmsg.index.html");
    	} else { //未绑定渠道
    		if(!IS_POST) {//未提交表单
    			$username = $this->visitor->info['user_name'];
    			$this->assign('username',$username);
    			$this->display("storeadmin.channel_bind.form.html");
    		} else {
    			$uname = empty($_POST['uname']) ? '' : trim($_POST['uname']);
    			$password = empty($_POST['password']) ? '' : md5(trim($_POST['password']));
    			$sn = empty($_POST['sn']) ? '' : trim($_POST['sn']);
    			$channel_user_info = $channel_user_mod->get(array('conditions' => ' channel_name="'.$uname.'" AND password="'.$password.'" AND sn="'.$sn.'"'));
    			if(!$channel_user_info) {
    				$this->show_storeadmin_warning("未找到渠道信息, 请检查输入信息再试！");
    			} else {
    				if($channel_user_info['level'] != 3) {
    					$this->show_storeadmin_warning("绑定的渠道不是派拉加盟店类型渠道商,不可在当前位置绑定!");
    					return;
    				}
    				$channel_fee_info = $channel_fee_mod->get(array('conditions' => " level=" . $channel_user_info['level'] . " AND area_id=" . $channel_user_info['area_id']));
    				if(!$channel_fee_info) {
    					$this->show_storeadmin_warning("渠道信息处理出错! 请重试,如无法解决,请联系客服!");
    					return;
    				}
    				$store_info = $store_mod->get($user_id);
    				$user_info = $member_mod->get($user_id);
    				if($store_info['is_bind_channel'] == 1) {
	    				$this->show_warning("已绑定渠道,不能重复绑定!");
	    				return;
	    			}
    				if($user_info['is_bind_channel'] == 1) {
	    				$this->show_warning("已绑定渠道,不能重复绑定!");
	    				return;
	    			}
    				//写入channle_user 用户信息
    				$channel_user_mod->edit($channel_user_info['channel_id'],array('sid' => $this->visitor->info['store_id']));
    				$store_mod->edit($this->visitor->info['store_id'],array('channel_id' => $channel_user_info['channel_id'] , 'is_bind_channel' => 1));
    				$member_mod->edit($this->visitor->get('user_id'),array('channel_id' => $channel_user_info['channel_id'] , 'is_bind_channel' => 1));
    				
    				$this->show_storeadmin_message('成功绑定派啦店铺渠道商');
    			}
    		}
    	}
    }
    /**
     *  查看是当前商铺是否绑定渠道
     */
    public function checkIsChannel() {
    	$store_id = $this->visitor->info['store_id'];
    	$store_mod = & m("store");
    	$channel_mod = & m("channeluser");
    	$store_info = $store_mod->get(array('conditions' => 'store_id='.$store_id));
    	if($store_info['is_bind_channel'] == 1) {
	    	if($store_info['channel_id']) {
				$channel_id = $store_info['channel_id'];
				$channel_info = $channel_mod->get($channel_id);
				if($channel_info['sid'] == $store_id) {
					return true;
				} else {
					return false;
				}
	    	} else {
	    		return false;
	    	}
    	} else {
    		return false;
    	}
    }
    
    /**
     *    根据条件获取会员列表
     *
     *    @author    lihuoliang
     *    @return    void
     */
	function _get_members($conditions,$page,$count=false)
    {

        if ($count)
        {
        	$this->_member_mod->find(array(
	            'conditions' => $conditions,
	            'count' => $count,
	        ));
	        return $this->_member_mod->getCount();
        }else 
        {
        	/* 取得商品列表 */
        
	        $member_list = $this->_member_mod->find(array(
	            'conditions' => $conditions,
	            'limit' => $page->firstRow.','.$page->listRows,
	        ));
	        return $member_list;
        } 
    }
	function get($flag, $is_name = false,$is_mobile = false,$is_sid = false)
    {
        if ($is_name) {
            $conditions = "channel_name='{$flag}'";
        }elseif ($is_mobile) {
        	$conditions = "mobile='{$flag}'";
        } elseif ($is_sid) {
        	$conditions = "sid='{$flag}'";
        } else {
            $conditions = intval($flag);
        }
        return $this->_local_get($conditions);
    }
	function _local_get($conditions)
    {
        $supply_user =& m('channeluser');
        return $supply_user->get($conditions);
    }
	function get_list($user_id, $page, $folder = 'privatepm')
    {
        $limit = $page['limit'];
        $condition = '';
       // var_dump($folder);
        switch ($folder)
        {
            case 'privatepm':
                $condition = '((from_id = ' . $user_id . ' AND status IN (2,3)) OR (to_id = ' . $user_id . ' AND status IN (1,3)) AND from_id > 0)';
            break;
            case 'systempm':
                $condition = 'from_id = ' . MSG_SYSTEM . ' AND to_id = ' . $user_id;
            break;
            case 'announcepm':
                $condition = 'from_id = 0 AND to_id = 0';
            break;
            default:
                $condition = ' to_id = ' . $user_id . ' and status=0';
            break;
        }
        $model_message =& m('channelmessage');
        $messages = $model_message->find(array(
            'fields'        =>'this.*',
            'conditions'    =>  $condition,
            'count'         => true,
            'limit'         => $limit,
            'order'         => 'msg_id desc',
        ));
        /*$subject = '';
    
        if (!empty($messages))
        {
            foreach ($messages as $key => $message)
            {
                $messages[$key]['new'] = (($message['from_id'] == $user_id && $message['new'] == 2)||($message['to_id'] == $user_id && $message['new'] == 1 )) ? 1 : 0; //判断是否是新消息
                $subject = $this->removecode($messages[$key]['content']);
                $messages[$key]['content'] = htmlspecialchars($subject);
                $message['from_id'] == MSG_SYSTEM && $messages[$key]['user_name'] = Lang::get('system_message'); //判断是否是系统消息
            }
        }*/
        return array(
            'count' => $model_message->getCount(),
            'data' => $messages
        );
    }
}

?>