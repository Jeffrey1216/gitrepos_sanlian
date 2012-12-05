<?php

/**
 *    专题活动控制器
 *
 *    @author   lihuoliang
 *    @date     2011/11/10
 *    @usage    none
 */

class TopicsApp extends MallbaseApp
{
	var $_activity_mod;

    function __construct()
    {
        $this->Topics();
    }

    function Topics()
    {
        parent::__construct();
        $this->_activity_mod = &m('activity');                      //实例化专题活动表
        $this->_activity_awardcount_mod = &m('activityawardcount'); //实例化活动抽奖统计表
        $this->_activity_awardinfo_mod = &m('activityawardinfo');	//实例化获奖信息表
        $this->_activity_awardnum_mod = &m('activityawardnum');		//实例化会员抽奖机会表
        $this->_activity_awardprize_mod = &m('activityawardprize'); //实例化专题活动奖品表
        $this->_pay_order_mod = &m('payorder');						//实例化网上支付订单表
    }
    
    /* 大派送活动 */
    function greatsale()
    {	
    	
    	$this->_activity_mod->edit(PAISONG,'act_viewnum = act_viewnum + 1'); //更新活动统计数据
    	$activity = $this->_activity_mod->get(PAISONG); 					 //获取大派送活动的统计数据
    	
	    //用户已经登入
		if($this->visitor->has_login)
		{
			$data['uid']      = $this->visitor->get('user_id');  //会员id
			$data['mobile']   = $this->visitor->get('mobile');   //手机号
			$data['username'] = $this->visitor->get('user_name');//用户名
			$data['act_id']  = PAISONG;	 //活动id
			$data['num'] = 1;  		 		 //抽奖机会
			//判断是否写入了用户此次活动的抽奖记录
			$rs = $this->_activity_awardnum_mod->get('uid='.$data['uid'].' AND act_id='.$data['act_id']);
			if(!$rs)
			{
				$this->_activity_awardnum_mod->add($data); //写入数据
				//登入活动赠送一次抽奖机会
				$infos['uid'] 	   = $data['uid'];
            	$infos['username'] = $data['username'];
            	$infos['mobile']   = $data['mobile'];
            	$infos['num'] = 1;
            	$infos['action'] = 'login';
            	$infos['type'] = 'get';
            	$infos['add_time'] = time();
            	$infos['act_id'] = PAISONG;
            	$infos['remark'] = '首次登入活动页面获得一次大派送活动抽奖机会';
        		$this->_activity_awardcount_mod->add($infos);
				$awardnum = 1;
			}else{
				$awardnum = $rs['num']; 	     //剩余抽奖次数
			}
			//设置邀请码
			$invitecode = base64_encode($data['uid']);
			$this->assign('invitecode',$invitecode);
		} 					 
		
		//查询获奖动态----最新30条
		$awardinfo = $this->_activity_awardinfo_mod->find(array(
						            'conditions'    => 'i.act_id='.PAISONG,
									'fields'         => 'p.prize_name,i.username,i.add_time',
						            'order'         => 'i.add_time DESC',
									'join'          => 'belongs_to_awardprize',
						            'limit'         => '10',
						        ));
						        
		//模拟添加一些虚假数据
		$newarr = array(31=>array('prize_name'=>'铭珠坊珠宝','username'=>'zhuzhu2','add_time'=>'1322611359'),
						32=>array('prize_name'=>'世纪科技丝幕花洒','username'=>'hsjxa','add_time'=>'1322611359'),
						33=>array('prize_name'=>'A派营养膳食','username'=>'唯我独尊','add_time'=>'1322611359'),
						34=>array('prize_name'=>'价值2元派啦币','username'=>'小星星','add_time'=>'1322611359'),
						35=>array('prize_name'=>'价值2元派啦币','username'=>'mcys','add_time'=>'1322611359')); 
		$awardinfo += $newarr;
    	$this->assign('activity',$activity);
    	$this->assign('awardnum',$awardnum);
    	$this->assign('awardinfo',$awardinfo);
    	$this->display('topics_paisong.html');
    }
    /* 验证信息 */
    function checkinfo()
    {	
    	$activity  = $this->_activity_mod->get(PAISONG);
    	$starttime = $activity['act_starttime'];
    	$endtime   = $activity['act_endtime'];
    	$currenttime = time();
    	
	    //如果当前时间小于活动开始时间---表示活动未开始
		if ($currenttime<$starttime) 
		{
			echo 'rs=unstart';
			return;
		}
		//如果当前时间大于活动结束时间---表示活动已结束
		if ($currenttime>$endtime) 
		{
			echo 'rs=end';
			return;
		}
		if($this->visitor->has_login)
		{
			//判断当前这期活动时间内用户还有几次抽奖机会
			$rs = $this->_activity_awardnum_mod->get('uid='.$this->visitor->get('user_id').' AND act_id='.PAISONG);
		
			$lastnum = $rs['num'];
			if($lastnum==0)
			{
				echo 'rs=nochance';	  //抽奖机会为0
				return;
			}else
			{
				echo 'rs=getaward';	  //开始抽奖
				return;
			}
		}else
		{
			echo 'rs=unlogin';	  //未登入网站
			return;
		}
    }
     /* 获取奖品 */
    function getaward()
    {	
    	if (!$this->visitor->has_login)
    	{
    		show_message('你还没有登入网站，无权进行此操作！');
    	}
		$prizearr = $this->_activity_awardprize_mod->find('act_id='.PAISONG.' AND lastnum!=0'); //取出剩余的奖品数以及奖品的对应概率
		$rs = $this->_activity_awardnum_mod->get('uid='.$this->visitor->get('user_id').' AND act_id='.PAISONG);
		$lastnum = $rs['num'];
		if ($lastnum) 
		{
			if ($prizearr) 
			{
				//重新排列数组
				foreach ($prizearr as $v)
				{
					$newprizearr[] = $v;
				}
				//计算获奖概率
				$key  = mt_rand(0,count($newprizearr)-1);  //取出奖品中的随机奖品类型key
				$jpid = $newprizearr[$key]['prize_id'];	   //奖品id
				$jpname = $newprizearr[$key]['prize_name'];//奖品名称
				$lucker = $newprizearr[$key]['winproba'];  //奖品概率
				$arrluck = explode('.',$lucker);
				$totalnum = (intval($arrluck[1])/$lucker); //总数量
				
				if ($totalnum<1) $totalnum = 1;
				$one = mt_rand(0,intval($totalnum)); 		 //取出获奖的那个一个随机数
				$rs = $this->lucker(intval($arrluck[1]),$one,intval($totalnum)); //获取奖品
				if ($rs) 
				{
					//更新奖品数量
					$this->_activity_awardprize_mod->edit('prize_id='.$jpid,'lastnum=lastnum-1');
					//写入获奖数据
					$data['uid']      = $this->visitor->get('user_id');  //会员id
					$data['mobile']   = $this->visitor->get('mobile');   //手机号
					$data['username'] = $this->visitor->get('user_name');//用户名
					$data['prize_id'] = $jpid;	     //奖品id
					$data['act_id']   = PAISONG;	 //活动id
					$data['add_time'] = time();  	 //获奖时间
					$data['used']    = 0;				          //状态：未领取/未使用
					
					$rs = $this->_activity_awardinfo_mod->add($data);      //写入数据
					if ($rs)
					{
						$infoarr = array(1=>8,2=>12,3=>1,4=>5,5=>14,6=>array(2,10),7=>array(6,16),8=>array(4,9,13)); //奖品id对应转轮上的位置
						//获取转轮停止的位置
						if (is_array($infoarr[$jpid]))
						{
							$stopnum = $infoarr[$jpid][array_rand($infoarr[$jpid])];
						}else
						{
							$stopnum = $infoarr[$jpid];
						}
						//赠送派啦积分
						if ($jpid==7||$jpid==8)
						{
							if ($jpid == 7)
							{
								$credit = 10;
							}else
							{
								$credit = 5;
							}
						}
						$this->sendsms($jpname,$jpid); //发送获奖短信
					}else
					{
						$stopnum = $this->getstopnum();
					}
				}else{
					$stopnum = $this->getstopnum();
				}
			}else
			{
				$stopnum = $this->getstopnum();
			}
			//减少用户一次抽奖机会
			$this->_activity_awardnum_mod->edit('uid='.$this->visitor->get('user_id').' AND act_id='.PAISONG,'num=num-1');
			//增加一次活动抽奖统计
			$this->_activity_mod->edit('act_id='.PAISONG,'act_num=act_num+1');
			//统计抽奖流水
			$infos['uid'] 	   = $this->visitor->get('user_id');
            $infos['username'] = $this->visitor->get('user_name');
            $infos['mobile'] = $this->visitor->get('mobile');
            $infos['num'] = 1;
            $infos['action'] = 'login';
            $infos['type'] = 'use';
            $infos['add_time'] = time();
            $infos['act_id'] = PAISONG;
            $infos['remark'] = '大派送抽奖使用一次抽奖机会';
        	$this->_activity_awardcount_mod->add($infos);
			
			echo 'stopnum='.$stopnum.'&lastnum='.$lastnum;
		}else
		{
			show_message('你没有抽奖机会，无权进行此操作！');
		}
    }
    /* 获取机会 */
    function getchance()
    {
    	if (!$this->visitor->has_login)
    	{
    		show_message('你还没有登入网站，无权进行此操作！');
    	}
    	if (!IS_POST)
        {
	    	//设置邀请码
			$invitecode = base64_encode($this->visitor->get('user_id'));
			$award = $this->_activity_awardnum_mod->get('uid='.$this->visitor->get('user_id').' AND act_id='.PAISONG);
			$member = &m('member');
			$info = $member->get($this->visitor->get('user_id'));
			$this->assign('invitecode',$invitecode);
			$this->assign('award',$award);
			$this->assign('info',$info);
	    	$this->display('topics_paisong_getchance.html');
        }else 
        {
        	$chargemoney = intval($_POST['chargemoney']);
        	
        	if ($chargemoney)
        	{
        		$rs = $this->_pay_order_mod->get('uid='.$this->visitor->get('user_id')." AND ordertype='paisong' AND pay_state='unpay'");
        		if (!$rs)
        		{
	        		$data['ordersn'] = $this->create_sn();
					$data['uid']      = $this->visitor->get('user_id');  //会员id
					$data['mobile']   = $this->visitor->get('mobile');   //手机号
					$data['username'] = $this->visitor->get('user_name');//用户名
					$data['ordertype'] = 'paisong';
					$data['service']    = 'abcpay';
					$data['money']   = $chargemoney;
					$data['ordertime'] = time();
					$data['ip'] = real_ip();
					$data['act_id'] = PAISONG;
					$this->_pay_order_mod->add($data);
					$rs['ordersn'] = $data['ordersn'];
        		}else
        		{
        			$ordersn = $this->create_sn();
        			$this->_pay_order_mod->edit('pay_id='.$rs['pay_id'],'money='.$chargemoney.', ordersn='.$ordersn);
        			$rs['ordersn'] = $ordersn;
        		}
        		$PaymentURL = 'http://59.54.54.69:8080/axis/abcpay.jsp';
        		$subject    = '欢乐大派送充值';
        		//$notifyurl  = site_url().'/index.php?app=topics&act=paynotify';
        		$notifyurl  = 'http://59.54.54.69:8080/axis/AbcPayResult.jsp';
				$tOrderDesc = 'paila100.com';  	  //订单描述
				$tOrderDate = date('Y/m/d');  //订单日期	
				$tOrderTime = date('H:i:s');  //订单时间
        		echo <<<SEARCH
<form method="post" action="$PaymentURL" name="payform">
	<input type='hidden' name='OrderNo' value='{$rs['ordersn']}'>
	<input type='hidden' name='OrderDesc' value='{$tOrderDesc}'>
	<input type='hidden' name='OrderDate' value='{$tOrderDate}'>
	<input type='hidden' name='OrderTime' value='{$tOrderTime}'>
	<input type='hidden' name='OrderAmount' value='{$chargemoney}'>
	<input type='hidden' name='OrderURL' value='http://www.paila100.com/order/abcpay/order.php?action=queryorder'>
	<input type='hidden' name='ProductType' value='1'>
	<input type='hidden' name='PaymentType' value='1'>
	<input type='hidden' name='NotifyType' value='1'>
	<input type='hidden' name='ResultNotifyURL' value='{$notifyurl}'>
	<input type='hidden' name='MerchantRemarks' value='$tOrderDesc'>
	<input type='hidden' name='PaymentLinkType' value='1'>
</form>
<script>
 document.payform.submit()
</script>
SEARCH;
        	}else 
        	{
        		show_message('充值金额有误，请重新输入!');
        	}
        }
    }
    /* 支付通知地址 */
    function paynotify()
    {
    	$sign = $_GET['sign'];
    	import('AES');    //导入短信发送类
    	$aes = new AES(true);
    	$key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";// 128位密钥
		$keys = $aes->makeKey($key);
		//解密后的签名字符串
		$cpt = $aes->decryptString($sign, $keys);
		//劈开解密后签名字符串
		@list($ordersn,$money, $verify) = explode('&', $cpt, 3);
		if ($verify=='true') 
		{
			$rs = $this->_pay_order_mod->get("ordersn='$ordersn'");
			if($rs)
			{
				if ($rs['pay_state']=='unpay')
				{	if ($rs['money']!=$money)
					{
						show_message('签名验证失败-支付金额错误');
					}
					//更新订单为已支付
					$time = time();
					$this->_pay_order_mod->edit('ordersn='.$ordersn," pay_state='pay' , paytime=".$time);
	        		//赠送抽奖机会
	        		$this->_activity_awardnum_mod->edit('uid='.$rs['uid'].' AND act_id='.PAISONG,'num=num+'.intval($money));
					//登入活动赠送一次抽奖机会
					$infos['uid'] 	   = $rs['uid'];
	            	$infos['username'] = $rs['username'];
	            	$infos['mobile']   = $rs['mobile'];
	            	$infos['num'] = intval($money);
	            	$infos['action'] = 'charge';
	            	$infos['type'] = 'get';
	            	$infos['add_time'] = time();
	            	$infos['act_id'] = PAISONG;
	            	$infos['remark'] = '使用K宝充值赠送抽奖机会';
				}
        
	            $member = &m('member');
				$info = $member->get($rs['uid']);
				$rs = $this->_activity_awardnum_mod->get('uid='.$rs['uid'].' AND act_id='.PAISONG);
				$this->assign('rs',$rs);
				$this->assign('chargenum',intval($money));
				$this->assign('info',$info);
	        	$this->_activity_awardcount_mod->add($infos);
	    		$this->display('topics_paisong_paysucc.html');
			}else
			{
				show_message('你无权访问该页面');
			}
		}else 
		{
			show_message('签名验证失败');
		}
    }
	/* 获取不中奖时转轮停止的位置*/
	private function getstopnum()
	{
		$rand = rand(1,4);
		if ($rand==1) 
		{
			$stopnum = 3;
		}elseif($rand==2)
		{
			$stopnum = 7;
		}elseif($rand==3)
		{
			$stopnum = 11;
		}else{
			$stopnum = 15;
		}
		return $stopnum;
	}
	/* 生成一个随机编码 */
	private function create_sn() 
	{
		/* 选择一个随机的方案 */
	    mt_srand((double) microtime() * 1000000);
	    return  date('Ymd') . str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
	}
	/* 计算抽奖概率 */
	private function lucker($dot,$one,$total) 
	{
		
		$dot = intval($dot);
		$dot = max($dot,0);
		$dot = min($dot,$total);
		
		$total = range(0,$total);
		shuffle($total); //按新的键名排列数组
		$range = array();
		for($i=0; $i<$dot; $i++) {
			$range[] = $total[$i];
		}
		return in_array($one,$range);
	}
	/* 发送获奖通知短信 */
	private function sendsms($jpname,$jpid)
	{
		$arrnames = array(1=>'特等奖',2=>'一等奖',3=>'一等奖',4=>'二等奖',5=>'三等奖',6=>'四等奖',7=>'五等奖',8=>'六等奖'); //奖品id对应奖项的等级
		if ($jpid==7||$jpid==8)
		{
			$smscontent = '尊敬的用户：'.'恭喜你获得'.$arrnames[$jpid].'('.$jpname.')，系统已经直接充值到您的派啦账户，请注意查收【派啦网】';
		}else 
		{
			$smscontent = '尊敬的用户：'.'恭喜你获得'.$arrnames[$jpid].'('.$jpname.')，请尽快完善您的个人资料以便我们邮寄，我们会在活动结束后统一配送【派啦网】';
		}

        import('class.smswebservice');    //导入短信发送类
        $sms = SmsWebservice::instance(); //实例化短信接口类
        
        $result= $sms->SendSms2($this->visitor->get('mobile'),$smscontent); //执行发送短信验证码操作
        //短信发送成功
        if ($result == 0) 
        {
        	$smslog =&  m('smslog'); 
        	//执行短信日志写入操作
        	$smsdata['mobile'] = $this->visitor->get('mobile');
        	$smsdata['smscontent'] = $smscontent;
        	$smsdata['type'] = 'award_notify'; //中奖通知短信
        	$smsdata['sendtime'] = time();
        	
        	$smslog->add($smsdata);
        }
	}
}

?>