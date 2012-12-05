<?php

/**
 *    ���̻�Ա���������
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
     *    ��Ա�б�
     *
     *    @author    lihuoliang
     *    @return    $memberlist
     */
    function member_list()
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
        $this->display('storeadmin.mymember.list.html');
    }

    /**
     *    ���̻�Աע��
     *
     *    @author    lihuoliang
     *    @return    void
     */
    function register()
    {
    	$store_id = intval($this->visitor->get('manage_store'));
        if (!IS_POST)
        {
        	//����ű���֤��js
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
	             $this->show_storeadmin_warning('�������벻��Ϊ��');
            }else{
            	if (count($trpwd)!=6)
            	{
            		$this->show_storeadmin_warning('��������ֻ��Ϊ6λ����');
            	}
            	if ($trpwd!=$trpwd2)
            	{
            		$this->show_storeadmin_warning('���ν������벻һ��');
            	}
            }
            $ms =& ms(); //�����û�����
            $data['mobile'] = $mobile;
            $data['invite_id']    = $store_id;
            $mobilearea = &  m('mobile');  //ʵ�����ֻ������
            $data['mobilearea'] = $mobilearea->get_areaname_by_mobile($mobile);
            $data['trader_password'] = $ms->user->getMd5TraderPassword($trpwd);
            $user_id = $ms->user->register($user_name, $password, $email,$data);

            if (!$user_id)
            {
                $this->show_storeadmin_warning($ms->user->get_error());

                return;
            }else
            {
            	
            	//�����Աע��ɹ�---���Ͷ���Ϣ
            	
        		import('class.smswebservice');    //������ŷ�����
        		$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
        		$smscontent = str_replace('{password}',$password,Lang::get('smscontent.register_success'));

        		$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ�����֤�����
        		//���ŷ��ͳɹ�
        		if ($result == 0) 
        		{
        			//ִ�ж�����־д�����
        			$smsdata['mobile'] = $mobile;
        			$smsdata['smscontent'] = $smscontent;
        			$smsdata['type'] = 'register_success'; //ע��ɹ�����
        			$smsdata['sendtime'] = gmtime();
        			$smslog =&  m('smslog'); 
        			$smslog->add($smsdata);
        		}
            	$this->show_storeadmin_message('��ϲ�û�'.$mobile.'���ɹ�ע��Ϊ�����Ա');
            }
        }
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
    /* �������� */
    public function channelmsg() {
    	$channel_user_mod = & m('channeluser');
    	$member_mod = & m('member');
    	$store_mod = & m('store');
  		$channel_fee_mod = & m('channelfee');
  		$user_id = $this->visitor->get('user_id');
    	if($this->checkIsChannel()) { // �Ѱ�����
    		/* �����˻���Ϣ */
	    	$info = $this->get($user_id,false,false,true);
	    	
	    	$pms = $this->get_list($user_id, 100, 'newpm'); 
	    	$channelfee =&m('channelfee');    //���˷��ò�ѯ
	     	$fee = $channelfee->get(" level=" . $info['level'] . " and area_id = " . $info['area_id'] ."");
	     	//var_dump($pms['count']);exit;
	    	$this->assign('count', $pms['count']); 		    	   
	        $this->assign('ip', $_SERVER["REMOTE_ADDR"]);   
	        $this->assign('user', $info);  
	        $this->assign('fee', $fee); 
	        /* �������� */
	        
    		$this->display("storeadmin.channelmsg.index.html");
    	} else { //δ������
    		if(!IS_POST) {//δ�ύ��
    			$username = $this->visitor->info['user_name'];
    			$this->assign('username',$username);
    			$this->display("storeadmin.channel_bind.form.html");
    		} else {
    			$uname = empty($_POST['uname']) ? '' : trim($_POST['uname']);
    			$password = empty($_POST['password']) ? '' : md5(trim($_POST['password']));
    			$sn = empty($_POST['sn']) ? '' : trim($_POST['sn']);
    			$channel_user_info = $channel_user_mod->get(array('conditions' => ' channel_name="'.$uname.'" AND password="'.$password.'" AND sn="'.$sn.'"'));
    			if(!$channel_user_info) {
    				$this->show_storeadmin_warning("δ�ҵ�������Ϣ, ����������Ϣ���ԣ�");
    			} else {
    				if($channel_user_info['level'] != 3) {
    					$this->show_storeadmin_warning("�󶨵����������������˵�����������,�����ڵ�ǰλ�ð�!");
    					return;
    				}
    				$channel_fee_info = $channel_fee_mod->get(array('conditions' => " level=" . $channel_user_info['level'] . " AND area_id=" . $channel_user_info['area_id']));
    				if(!$channel_fee_info) {
    					$this->show_storeadmin_warning("������Ϣ�������! ������,���޷����,����ϵ�ͷ�!");
    					return;
    				}
    				$store_info = $store_mod->get($user_id);
    				$user_info = $member_mod->get($user_id);
    				if($store_info['is_bind_channel'] == 1) {
	    				$this->show_warning("�Ѱ�����,�����ظ���!");
	    				return;
	    			}
    				if($user_info['is_bind_channel'] == 1) {
	    				$this->show_warning("�Ѱ�����,�����ظ���!");
	    				return;
	    			}
    				//д��channle_user �û���Ϣ
    				$channel_user_mod->edit($channel_user_info['channel_id'],array('sid' => $this->visitor->info['store_id']));
    				$store_mod->edit($this->visitor->info['store_id'],array('channel_id' => $channel_user_info['channel_id'] , 'is_bind_channel' => 1));
    				$member_mod->edit($this->visitor->get('user_id'),array('channel_id' => $channel_user_info['channel_id'] , 'is_bind_channel' => 1));
    				
    				$this->show_storeadmin_message('�ɹ�����������������');
    			}
    		}
    	}
    }
    /**
     *  �鿴�ǵ�ǰ�����Ƿ������
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
        	/* ȡ����Ʒ�б� */
        
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
                $messages[$key]['new'] = (($message['from_id'] == $user_id && $message['new'] == 2)||($message['to_id'] == $user_id && $message['new'] == 1 )) ? 1 : 0; //�ж��Ƿ�������Ϣ
                $subject = $this->removecode($messages[$key]['content']);
                $messages[$key]['content'] = htmlspecialchars($subject);
                $message['from_id'] == MSG_SYSTEM && $messages[$key]['user_name'] = Lang::get('system_message'); //�ж��Ƿ���ϵͳ��Ϣ
            }
        }*/
        return array(
            'count' => $model_message->getCount(),
            'data' => $messages
        );
    }
}

?>