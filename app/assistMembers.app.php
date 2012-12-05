<?php
class assistMembersApp extends MemberbaseApp
{
	function __construct()
    {
        $this->MemberApp();
    }
    
    function MemberApp()
    {
        parent::__construct();
        $ms =& ms();
        $this->_feed_enabled = $ms->feed->feed_enabled();
        $this->assign('feed_enabled', $this->_feed_enabled);
        $this->_member_mod = & m("member");
    }
    
    /**
     * 	  获取所有会员信息
     * 	 @author bottle
     */
    public function index()
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
        $this->display('storeadmin.assistmembers.list.html');
    }
    
	/**
     *    设置支付密码
     *
     *    @author    Hyber
     *    @usage    none
     */
    function set_trader_password() 
    {
    	$user_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	$member_mod = & m('member');
    	if (0 == $user_id)
    	{
    		$this->show_storeadmin_warning('未知的用户!');
    		return;
    	}
    	if (!IS_POST)
    	{
    		//用户信息
    		$info = $member_mod->get($user_id);
    		if (!$info)
    		{
    			$this->show_storeadmin_warning('未知的用户信息!');
    			return;
    		}
    		//导入脚本验证的js
        	$this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));  
    		$this->assign('info', $info);
    		
    		$this->display("storeadmin.member.paypassword.html");
    	}
		else 
		{
			$verify = empty($_POST['smsverify']) ? false : trim($_POST['smsverify']);
			$new_password = empty($_POST['new_password']) ? false : trim($_POST['new_password']);
			$re_passowrd = empty($_POST['re_password']) ? false : trim($_POST['re_password']);
			if (!$verify)
			{
				$this->show_storeadmin_warning('你没有输入手机验证码!');
				return;
			}
			if (!$new_password)
			{
				$this->show_storeadmin_warning('请输入新的交易密码!');
				return;
			}
			if ($new_password != $re_passowrd)
			{
				$this->show_storeadmin_warning('两次输入的密码不一致!');
				return;
			}
			if ($verify != $_SESSION['smsverifydata']['verify'])
			{
				$this->show_storeadmin_warning('手机验证码输入不正确!');
				return;
			}
			
			//写入信息
			$userObj = & ms();
			$userObj->user->updateTraderAuth($user_id, $new_password, $re_passowrd);
			$this->show_storeadmin_message('修改支付密码成功!.');
		}
    	
    }
    
	function send_sms_verify()
    {
    	//获取手机号码
        $mobile = empty($_POST['mobile']) ? null : trim($_POST['mobile']);

        if (!$mobile)
        {
            echo 1;  //手机号码为空
        }else
        {
        	if (is_mobile($mobile))
        	{
        		$smslog =&  m('smslog'); 
	        	$todaysmscount = $smslog->get_today_smscount($mobile); //当天短信的发送总量
	        	
				if ($todaysmscount>=5)
				{
					echo 6;
					return;
        		}
        		$ms =& ms();    //连接用户系统
        		$info = $ms->user->get($mobile,false,true);//判断手机号码是否存在
        		if (!$info)
        		{
        			echo 3;
        			return;
        		}
        		else
        		{
        			//由于虚拟主机中php运行环境暂时未配置开启soap扩展，所以暂时不使用webservice方式
        			import('class.smswebservice');    //导入短信发送类
        			$sms = SmsWebservice::instance(); //实例化短信接口类
        			$verify = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);//验证码
        			$verifytype = $_GET['verifytype']?$_GET['verifytype']:'retrieve_trade_password'; //短信验证码类型
        			if ($verifytype=='modifymobile')
        			{
        				$smscontent = str_replace('{verify}',$verify,Lang::get('smscontent.modifymobile_verify'));
        			}else 
        			{
        				$smscontent = str_replace('{verify}',$verify,'尊敬的客户，你找回支付密码的验证码为 {verify}，快乐购物、快捷支付尽在www.paila100.com【派啦网】');
        			}
					//echo "OK";
        			$result= $sms->SendSms2($mobile,$smscontent); //执行发送短信验证码操作
        			//短信发送成功
        			if ($result == 0) 
        			{
        				//将验证码写入SESSION
        				$time = time();
        				$_SESSION['smsverifydata']['mobile'] =  $mobile;
        				$_SESSION['smsverifydata']['verify'] =  $verify;
        				$_SESSION['smsverifydata']['dateline'] =  $time;
        				//执行短信日志写入操作
        				$smsdata['mobile'] = $mobile;
        				$smsdata['smscontent'] = $smscontent;
        				$smsdata['type'] = $verifytype; //注册验证短信
        				$smsdata['sendtime'] = $time;
        				
        				$smslog->add($smsdata);
        				echo 4;
        			}else
        			{
	        			echo 5; //短信发送失败
        			}
        		}
        	}else 
        	{
        		echo 2; //非正确的手机号码
        	}
        }
        return;
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
        
        if ($_GET['mobile'])
        {
        	$conditions .= " mobile = '" . trim($_GET['mobile']) . "'";
        }else{
        	$conditions = " 1 = 2 ";
        }
        return $conditions;
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
        	/* 取得会员列表 */
        
	        $member_list = $this->_member_mod->find(array(
	            'conditions' => $conditions,
	            'limit' => $page->firstRow.','.$page->listRows,
	        ));
	        return $member_list;
        } 
    }
}