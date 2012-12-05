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
     * 	  ��ȡ���л�Ա��Ϣ
     * 	 @author bottle
     */
    public function index()
    {
    	//��ȡ��������
        $conditions = $this->_get_conditions();

        import('Page.class');
        $count = $this->_get_members($conditions,false,true); //������
        $listRows= 10;        //ÿҳ��ʾ����
        $page=new Page($count,$listRows); //��ʼ������
        //��ȡ��Ա��Ϣ
        $member_list = $this->_get_members($conditions,$page);
	
        $this->assign('member_list', $member_list);
        
		$page->setConfig('header', '����¼');
        $p=$page->show();
        $this->assign('page',$p);
        $this->display('storeadmin.assistmembers.list.html');
    }
    
	/**
     *    ����֧������
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
    		$this->show_storeadmin_warning('δ֪���û�!');
    		return;
    	}
    	if (!IS_POST)
    	{
    		//�û���Ϣ
    		$info = $member_mod->get($user_id);
    		if (!$info)
    		{
    			$this->show_storeadmin_warning('δ֪���û���Ϣ!');
    			return;
    		}
    		//����ű���֤��js
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
				$this->show_storeadmin_warning('��û�������ֻ���֤��!');
				return;
			}
			if (!$new_password)
			{
				$this->show_storeadmin_warning('�������µĽ�������!');
				return;
			}
			if ($new_password != $re_passowrd)
			{
				$this->show_storeadmin_warning('������������벻һ��!');
				return;
			}
			if ($verify != $_SESSION['smsverifydata']['verify'])
			{
				$this->show_storeadmin_warning('�ֻ���֤�����벻��ȷ!');
				return;
			}
			
			//д����Ϣ
			$userObj = & ms();
			$userObj->user->updateTraderAuth($user_id, $new_password, $re_passowrd);
			$this->show_storeadmin_message('�޸�֧������ɹ�!.');
		}
    	
    }
    
	function send_sms_verify()
    {
    	//��ȡ�ֻ�����
        $mobile = empty($_POST['mobile']) ? null : trim($_POST['mobile']);

        if (!$mobile)
        {
            echo 1;  //�ֻ�����Ϊ��
        }else
        {
        	if (is_mobile($mobile))
        	{
        		$smslog =&  m('smslog'); 
	        	$todaysmscount = $smslog->get_today_smscount($mobile); //������ŵķ�������
	        	
				if ($todaysmscount>=5)
				{
					echo 6;
					return;
        		}
        		$ms =& ms();    //�����û�ϵͳ
        		$info = $ms->user->get($mobile,false,true);//�ж��ֻ������Ƿ����
        		if (!$info)
        		{
        			echo 3;
        			return;
        		}
        		else
        		{
        			//��������������php���л�����ʱδ���ÿ���soap��չ��������ʱ��ʹ��webservice��ʽ
        			import('class.smswebservice');    //������ŷ�����
        			$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
        			$verify = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);//��֤��
        			$verifytype = $_GET['verifytype']?$_GET['verifytype']:'retrieve_trade_password'; //������֤������
        			if ($verifytype=='modifymobile')
        			{
        				$smscontent = str_replace('{verify}',$verify,Lang::get('smscontent.modifymobile_verify'));
        			}else 
        			{
        				$smscontent = str_replace('{verify}',$verify,'�𾴵Ŀͻ������һ�֧���������֤��Ϊ {verify}�����ֹ�����֧������www.paila100.com����������');
        			}
					//echo "OK";
        			$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ�����֤�����
        			//���ŷ��ͳɹ�
        			if ($result == 0) 
        			{
        				//����֤��д��SESSION
        				$time = time();
        				$_SESSION['smsverifydata']['mobile'] =  $mobile;
        				$_SESSION['smsverifydata']['verify'] =  $verify;
        				$_SESSION['smsverifydata']['dateline'] =  $time;
        				//ִ�ж�����־д�����
        				$smsdata['mobile'] = $mobile;
        				$smsdata['smscontent'] = $smscontent;
        				$smsdata['type'] = $verifytype; //ע����֤����
        				$smsdata['sendtime'] = $time;
        				
        				$smslog->add($smsdata);
        				echo 4;
        			}else
        			{
	        			echo 5; //���ŷ���ʧ��
        			}
        		}
        	}else 
        	{
        		echo 2; //����ȷ���ֻ�����
        	}
        }
        return;
    }
	/**
     *    ��ȡ��������
     *
     *    @author    lihuoliang
     *    @return    void
     */
    function _get_conditions()
    {
        /* �������� */
        
        if ($_GET['mobile'])
        {
        	$conditions .= " mobile = '" . trim($_GET['mobile']) . "'";
        }else{
        	$conditions = " 1 = 2 ";
        }
        return $conditions;
    }
	/**
     *    ����������ȡ��Ա�б�
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
        	/* ȡ�û�Ա�б� */
        
	        $member_list = $this->_member_mod->find(array(
	            'conditions' => $conditions,
	            'limit' => $page->firstRow.','.$page->listRows,
	        ));
	        return $member_list;
        } 
    }
}