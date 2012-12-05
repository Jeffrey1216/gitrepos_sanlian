<?php
/**
 * 找回密码控制器
 * @author cheng
 */
class Find_passwordApp extends MallbaseApp
{
    var $_password_mod;
    function __construct()
    {
        $this->Find_passwordApp();
    }

    function Find_passwordApp()
    {
        parent::FrontendApp();
        $this->_password_mod = &m("member");
    }

    /**
     * 显示文本框及处理提交的用户信息
     *
     */
    function index()
    {
       if(!IS_POST)
       {
           $this->import_resource('jquery.plugins/jquery.validate.js');
           $this->display("find_password.html");
       }
       else
       {
       		if ($_POST['smsverify'] != $_SESSION['smsverifydata']['verify'])
       		{
       			$this->show_warning("您输入的短信验证码有误，请重新操作！");
       			return false;
       		}
       		//var_dump($_POST);
       		$mobile = empty($_POST['mobile']) ? 0 : $_POST['mobile'];
       		$username = empty($_POST['username']) ? "" : $_POST['username'];
       		$modle_member = &m('member');
       		$member_info = $modle_member->getRow("select * from pa_member where mobile = '".$mobile. "' and user_name='".$username."'");		
       		if(empty($member_info))
       		{
       			$this->show_warning("您输入的手机号和您的账号不匹配！请重新操作！");
       			return;
       		}
       		 if (empty($_POST['new_password']) || empty($_POST['re_password']))
            {
                $this->show_warning("unsettled_required");
                return ;
            }
            if (trim($_POST['new_password']) != trim($_POST['re_password']))
            {
                $this->show_warning("password_not_equal");
                return ;
            }
            $password = trim($_POST['new_password']);
            $passlen = strlen($password);
            if ($passlen < 6 || $passlen > 20)
            {
                $this->show_warning('password_length_error');

                return;
            }
			
            $id = $member_info['user_id'];
            $word = $this->_rand();
            $md5word = md5($word);

            $ms =& ms();        //连接用户系统
            $ms->user->edit($id, '', array('password' => $password), true); //强制修改
            if ($ms->user->has_error())
            {
                $this->show_warning($ms->user->get_error());

                return;
            }
            $ret = $this->_password_mod->edit($id, array('activation' => $md5word));

            $this->show_message("edit_success",
                'login_in', 'index.php?app=member&act=login',
                'back_index', 'index.php');
            return ;
            /* //老修改密码
           $addr = $_SERVER['HTTP_REFERER'];
           if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['captcha']))
           {
               $this->show_warning("unsettled_required",
                   'go_back', $addr);
               return ;
           }
           //暂时去除找回密码需要校验码功能
//           if (base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
//           {
//               $this->show_warning("captcha_faild",
//                   'go_back', $addr);
//               return ;
//           }
           $username = trim($_POST['username']);
           $email = trim($_POST['email']);

           /* 简单验证是否是该用户 
           $ms =& ms();     //连接用户系统
           $info = $ms->user->get($username, true);
           if (empty($info) || $info['email'] != $email)
           {
               $this->show_warning('not_exist',
                   'go_back', $addr);

               return;
           }

            $word = $this->_rand();
            $md5word = md5($word);
            $res = $this->_password_mod->get($info['user_id']);
            if (empty($res))
            {
                $info['activation'] = $md5word;
                $this->_password_mod->add($info);
            }
            else
            {
                $this->_password_mod->edit($info['user_id'], array('activation' => "{$md5word}"));
            }
            $mail = get_mail('touser_find_password', array('user' => $info, 'word' => $word));
            $this->_mailto($email, addslashes($mail['subject']), addslashes($mail['message']));
            $this->show_message("sendmail_success",
                    'back_index', 'index.php');

            return;
            */
       }
    }

    /**
     * 显示设置密码及处理提交的新密码信息
     *
     */
    function set_password()
    {
        if (!IS_POST)
        {
            if (!isset($_GET['id']) || !isset($_GET['activation']) || empty($_GET['activation']))
            {
                $this->show_warning("request_error",
                    'back_index', 'index.php');
                return ;
            }
            $id = intval(trim($_GET['id']));
            $activation = trim($_GET['activation']);
            $res = $this->_password_mod->get_info($id);
            if (md5($activation) != $res['activation'])
            {
                $this->show_warning("invalid_link",
                    'back_index', 'index.php');
                return ;
            }
            $this->import_resource('jquery.plugins/jquery.validate.js');
            $this->display("set_password.html");
        }
        else
        {
            if (empty($_POST['new_password']) || empty($_POST['confirm_password']))
            {
                $this->show_warning("unsettled_required");
                return ;
            }
            if (trim($_POST['new_password']) != trim($_POST['confirm_password']))
            {
                $this->show_warning("password_not_equal");
                return ;
            }
            $password = trim($_POST['new_password']);
            $passlen = strlen($password);
            if ($passlen < 6 || $passlen > 20)
            {
                $this->show_warning('password_length_error');

                return;
            }

            $id = intval($_GET['id']);
            $word = $this->_rand();
            $md5word = md5($word);

            $ms =& ms();        //连接用户系统
            $ms->user->edit($id, '', array('password' => $password), true); //强制修改
            if ($ms->user->has_error())
            {
                $this->show_warning($ms->user->get_error());

                return;
            }
            $ret = $this->_password_mod->edit($id, array('activation' => $md5word));

            $this->show_message("edit_success",
                'login_in', 'index.php?app=member&act=login',
                'back_index', 'index.php');
            return ;
        }

    }

    /**
     * 构造15位的随机字符串
     *
     * @return string | 生成的字符串
     */
    function _rand()
    {
        $word = $this->generate_code();
        return $word;
    }

    function generate_code($len = 15)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0, $count = strlen($chars); $i < $count; $i++)
        {
            $arr[$i] = $chars[$i];
        }

        mt_srand((double) microtime() * 1000000);
        shuffle($arr);
        $code = substr(implode('', $arr), 5, $len);
        return $code;
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
        		if ($info === false)
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
        				$smscontent = str_replace('{verify}',$verify,'尊敬的客户，你找回登录密码的验证码为 {verify}，快乐购物、快捷支付尽在www.paila100.com【派啦网】');
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
}

?>
