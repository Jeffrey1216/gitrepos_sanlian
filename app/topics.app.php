<?php

/**
 *    ר��������
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
        $this->_activity_mod = &m('activity');                      //ʵ����ר����
        $this->_activity_awardcount_mod = &m('activityawardcount'); //ʵ������齱ͳ�Ʊ�
        $this->_activity_awardinfo_mod = &m('activityawardinfo');	//ʵ��������Ϣ��
        $this->_activity_awardnum_mod = &m('activityawardnum');		//ʵ������Ա�齱�����
        $this->_activity_awardprize_mod = &m('activityawardprize'); //ʵ����ר����Ʒ��
        $this->_pay_order_mod = &m('payorder');						//ʵ��������֧��������
    }
    
    /* �����ͻ */
    function greatsale()
    {	
    	
    	$this->_activity_mod->edit(PAISONG,'act_viewnum = act_viewnum + 1'); //���»ͳ������
    	$activity = $this->_activity_mod->get(PAISONG); 					 //��ȡ�����ͻ��ͳ������
    	
	    //�û��Ѿ�����
		if($this->visitor->has_login)
		{
			$data['uid']      = $this->visitor->get('user_id');  //��Աid
			$data['mobile']   = $this->visitor->get('mobile');   //�ֻ���
			$data['username'] = $this->visitor->get('user_name');//�û���
			$data['act_id']  = PAISONG;	 //�id
			$data['num'] = 1;  		 		 //�齱����
			//�ж��Ƿ�д�����û��˴λ�ĳ齱��¼
			$rs = $this->_activity_awardnum_mod->get('uid='.$data['uid'].' AND act_id='.$data['act_id']);
			if(!$rs)
			{
				$this->_activity_awardnum_mod->add($data); //д������
				//��������һ�γ齱����
				$infos['uid'] 	   = $data['uid'];
            	$infos['username'] = $data['username'];
            	$infos['mobile']   = $data['mobile'];
            	$infos['num'] = 1;
            	$infos['action'] = 'login';
            	$infos['type'] = 'get';
            	$infos['add_time'] = time();
            	$infos['act_id'] = PAISONG;
            	$infos['remark'] = '�״ε���ҳ����һ�δ����ͻ�齱����';
        		$this->_activity_awardcount_mod->add($infos);
				$awardnum = 1;
			}else{
				$awardnum = $rs['num']; 	     //ʣ��齱����
			}
			//����������
			$invitecode = base64_encode($data['uid']);
			$this->assign('invitecode',$invitecode);
		} 					 
		
		//��ѯ�񽱶�̬----����30��
		$awardinfo = $this->_activity_awardinfo_mod->find(array(
						            'conditions'    => 'i.act_id='.PAISONG,
									'fields'         => 'p.prize_name,i.username,i.add_time',
						            'order'         => 'i.add_time DESC',
									'join'          => 'belongs_to_awardprize',
						            'limit'         => '10',
						        ));
						        
		//ģ�����һЩ�������
		$newarr = array(31=>array('prize_name'=>'���鷻�鱦','username'=>'zhuzhu2','add_time'=>'1322611359'),
						32=>array('prize_name'=>'���ͿƼ�˿Ļ����','username'=>'hsjxa','add_time'=>'1322611359'),
						33=>array('prize_name'=>'A��Ӫ����ʳ','username'=>'Ψ�Ҷ���','add_time'=>'1322611359'),
						34=>array('prize_name'=>'��ֵ2Ԫ������','username'=>'С����','add_time'=>'1322611359'),
						35=>array('prize_name'=>'��ֵ2Ԫ������','username'=>'mcys','add_time'=>'1322611359')); 
		$awardinfo += $newarr;
    	$this->assign('activity',$activity);
    	$this->assign('awardnum',$awardnum);
    	$this->assign('awardinfo',$awardinfo);
    	$this->display('topics_paisong.html');
    }
    /* ��֤��Ϣ */
    function checkinfo()
    {	
    	$activity  = $this->_activity_mod->get(PAISONG);
    	$starttime = $activity['act_starttime'];
    	$endtime   = $activity['act_endtime'];
    	$currenttime = time();
    	
	    //�����ǰʱ��С�ڻ��ʼʱ��---��ʾ�δ��ʼ
		if ($currenttime<$starttime) 
		{
			echo 'rs=unstart';
			return;
		}
		//�����ǰʱ����ڻ����ʱ��---��ʾ��ѽ���
		if ($currenttime>$endtime) 
		{
			echo 'rs=end';
			return;
		}
		if($this->visitor->has_login)
		{
			//�жϵ�ǰ���ڻʱ�����û����м��γ齱����
			$rs = $this->_activity_awardnum_mod->get('uid='.$this->visitor->get('user_id').' AND act_id='.PAISONG);
		
			$lastnum = $rs['num'];
			if($lastnum==0)
			{
				echo 'rs=nochance';	  //�齱����Ϊ0
				return;
			}else
			{
				echo 'rs=getaward';	  //��ʼ�齱
				return;
			}
		}else
		{
			echo 'rs=unlogin';	  //δ������վ
			return;
		}
    }
     /* ��ȡ��Ʒ */
    function getaward()
    {	
    	if (!$this->visitor->has_login)
    	{
    		show_message('�㻹û�е�����վ����Ȩ���д˲�����');
    	}
		$prizearr = $this->_activity_awardprize_mod->find('act_id='.PAISONG.' AND lastnum!=0'); //ȡ��ʣ��Ľ�Ʒ���Լ���Ʒ�Ķ�Ӧ����
		$rs = $this->_activity_awardnum_mod->get('uid='.$this->visitor->get('user_id').' AND act_id='.PAISONG);
		$lastnum = $rs['num'];
		if ($lastnum) 
		{
			if ($prizearr) 
			{
				//������������
				foreach ($prizearr as $v)
				{
					$newprizearr[] = $v;
				}
				//����񽱸���
				$key  = mt_rand(0,count($newprizearr)-1);  //ȡ����Ʒ�е������Ʒ����key
				$jpid = $newprizearr[$key]['prize_id'];	   //��Ʒid
				$jpname = $newprizearr[$key]['prize_name'];//��Ʒ����
				$lucker = $newprizearr[$key]['winproba'];  //��Ʒ����
				$arrluck = explode('.',$lucker);
				$totalnum = (intval($arrluck[1])/$lucker); //������
				
				if ($totalnum<1) $totalnum = 1;
				$one = mt_rand(0,intval($totalnum)); 		 //ȡ���񽱵��Ǹ�һ�������
				$rs = $this->lucker(intval($arrluck[1]),$one,intval($totalnum)); //��ȡ��Ʒ
				if ($rs) 
				{
					//���½�Ʒ����
					$this->_activity_awardprize_mod->edit('prize_id='.$jpid,'lastnum=lastnum-1');
					//д�������
					$data['uid']      = $this->visitor->get('user_id');  //��Աid
					$data['mobile']   = $this->visitor->get('mobile');   //�ֻ���
					$data['username'] = $this->visitor->get('user_name');//�û���
					$data['prize_id'] = $jpid;	     //��Ʒid
					$data['act_id']   = PAISONG;	 //�id
					$data['add_time'] = time();  	 //��ʱ��
					$data['used']    = 0;				          //״̬��δ��ȡ/δʹ��
					
					$rs = $this->_activity_awardinfo_mod->add($data);      //д������
					if ($rs)
					{
						$infoarr = array(1=>8,2=>12,3=>1,4=>5,5=>14,6=>array(2,10),7=>array(6,16),8=>array(4,9,13)); //��Ʒid��Ӧת���ϵ�λ��
						//��ȡת��ֹͣ��λ��
						if (is_array($infoarr[$jpid]))
						{
							$stopnum = $infoarr[$jpid][array_rand($infoarr[$jpid])];
						}else
						{
							$stopnum = $infoarr[$jpid];
						}
						//������������
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
						$this->sendsms($jpname,$jpid); //���ͻ񽱶���
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
			//�����û�һ�γ齱����
			$this->_activity_awardnum_mod->edit('uid='.$this->visitor->get('user_id').' AND act_id='.PAISONG,'num=num-1');
			//����һ�λ�齱ͳ��
			$this->_activity_mod->edit('act_id='.PAISONG,'act_num=act_num+1');
			//ͳ�Ƴ齱��ˮ
			$infos['uid'] 	   = $this->visitor->get('user_id');
            $infos['username'] = $this->visitor->get('user_name');
            $infos['mobile'] = $this->visitor->get('mobile');
            $infos['num'] = 1;
            $infos['action'] = 'login';
            $infos['type'] = 'use';
            $infos['add_time'] = time();
            $infos['act_id'] = PAISONG;
            $infos['remark'] = '�����ͳ齱ʹ��һ�γ齱����';
        	$this->_activity_awardcount_mod->add($infos);
			
			echo 'stopnum='.$stopnum.'&lastnum='.$lastnum;
		}else
		{
			show_message('��û�г齱���ᣬ��Ȩ���д˲�����');
		}
    }
    /* ��ȡ���� */
    function getchance()
    {
    	if (!$this->visitor->has_login)
    	{
    		show_message('�㻹û�е�����վ����Ȩ���д˲�����');
    	}
    	if (!IS_POST)
        {
	    	//����������
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
					$data['uid']      = $this->visitor->get('user_id');  //��Աid
					$data['mobile']   = $this->visitor->get('mobile');   //�ֻ���
					$data['username'] = $this->visitor->get('user_name');//�û���
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
        		$subject    = '���ִ����ͳ�ֵ';
        		//$notifyurl  = site_url().'/index.php?app=topics&act=paynotify';
        		$notifyurl  = 'http://59.54.54.69:8080/axis/AbcPayResult.jsp';
				$tOrderDesc = 'paila100.com';  	  //��������
				$tOrderDate = date('Y/m/d');  //��������	
				$tOrderTime = date('H:i:s');  //����ʱ��
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
        		show_message('��ֵ�����������������!');
        	}
        }
    }
    /* ֧��֪ͨ��ַ */
    function paynotify()
    {
    	$sign = $_GET['sign'];
    	import('AES');    //������ŷ�����
    	$aes = new AES(true);
    	$key = "UJAGUATYTUWOZENIHCGYCCEQEBXCXMMPDIOVFCFAYTAWSTBPIBZYEDVCBYFVJZFIWFMHZCAYNPNAVEZTTVBVQAWMISXLEJJXZYTPDYGTMHKBINHUONOPMVGTZJZYJQVJ";// 128λ��Կ
		$keys = $aes->makeKey($key);
		//���ܺ��ǩ���ַ���
		$cpt = $aes->decryptString($sign, $keys);
		//�������ܺ�ǩ���ַ���
		@list($ordersn,$money, $verify) = explode('&', $cpt, 3);
		if ($verify=='true') 
		{
			$rs = $this->_pay_order_mod->get("ordersn='$ordersn'");
			if($rs)
			{
				if ($rs['pay_state']=='unpay')
				{	if ($rs['money']!=$money)
					{
						show_message('ǩ����֤ʧ��-֧��������');
					}
					//���¶���Ϊ��֧��
					$time = time();
					$this->_pay_order_mod->edit('ordersn='.$ordersn," pay_state='pay' , paytime=".$time);
	        		//���ͳ齱����
	        		$this->_activity_awardnum_mod->edit('uid='.$rs['uid'].' AND act_id='.PAISONG,'num=num+'.intval($money));
					//��������һ�γ齱����
					$infos['uid'] 	   = $rs['uid'];
	            	$infos['username'] = $rs['username'];
	            	$infos['mobile']   = $rs['mobile'];
	            	$infos['num'] = intval($money);
	            	$infos['action'] = 'charge';
	            	$infos['type'] = 'get';
	            	$infos['add_time'] = time();
	            	$infos['act_id'] = PAISONG;
	            	$infos['remark'] = 'ʹ��K����ֵ���ͳ齱����';
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
				show_message('����Ȩ���ʸ�ҳ��');
			}
		}else 
		{
			show_message('ǩ����֤ʧ��');
		}
    }
	/* ��ȡ���н�ʱת��ֹͣ��λ��*/
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
	/* ����һ��������� */
	private function create_sn() 
	{
		/* ѡ��һ������ķ��� */
	    mt_srand((double) microtime() * 1000000);
	    return  date('Ymd') . str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
	}
	/* ����齱���� */
	private function lucker($dot,$one,$total) 
	{
		
		$dot = intval($dot);
		$dot = max($dot,0);
		$dot = min($dot,$total);
		
		$total = range(0,$total);
		shuffle($total); //���µļ�����������
		$range = array();
		for($i=0; $i<$dot; $i++) {
			$range[] = $total[$i];
		}
		return in_array($one,$range);
	}
	/* ���ͻ�֪ͨ���� */
	private function sendsms($jpname,$jpid)
	{
		$arrnames = array(1=>'�صȽ�',2=>'һ�Ƚ�',3=>'һ�Ƚ�',4=>'���Ƚ�',5=>'���Ƚ�',6=>'�ĵȽ�',7=>'��Ƚ�',8=>'���Ƚ�'); //��Ʒid��Ӧ����ĵȼ�
		if ($jpid==7||$jpid==8)
		{
			$smscontent = '�𾴵��û���'.'��ϲ����'.$arrnames[$jpid].'('.$jpname.')��ϵͳ�Ѿ�ֱ�ӳ�ֵ�����������˻�����ע����ա���������';
		}else 
		{
			$smscontent = '�𾴵��û���'.'��ϲ����'.$arrnames[$jpid].'('.$jpname.')���뾡���������ĸ��������Ա������ʼģ����ǻ��ڻ������ͳһ���͡���������';
		}

        import('class.smswebservice');    //������ŷ�����
        $sms = SmsWebservice::instance(); //ʵ�������Žӿ���
        
        $result= $sms->SendSms2($this->visitor->get('mobile'),$smscontent); //ִ�з��Ͷ�����֤�����
        //���ŷ��ͳɹ�
        if ($result == 0) 
        {
        	$smslog =&  m('smslog'); 
        	//ִ�ж�����־д�����
        	$smsdata['mobile'] = $this->visitor->get('mobile');
        	$smsdata['smscontent'] = $smscontent;
        	$smsdata['type'] = 'award_notify'; //�н�֪ͨ����
        	$smsdata['sendtime'] = time();
        	
        	$smslog->add($smsdata);
        }
	}
}

?>