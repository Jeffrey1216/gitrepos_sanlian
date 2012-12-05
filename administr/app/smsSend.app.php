<?php
class SmsSendApp extends BackendApp
{
	var $_member_mod;
	
	public function __construct() 
	{
		$this->SmsSendApp();
	}
	
	public function SmsSendApp() 
	{
		parent::BackendApp();
		$this->_member_mod=& m('member');
	}
	public function index() 
	{
		show_message('�����̻���̨���ж���Ⱥ��!');
		return;
		/*
		//�����ҳ��ʾ��Ŀ
		$page_num=50;
		//��ȡ��ҳ��ʾ��Ŀ
		$page=$this->_get_page($page_num);	
		$condt=$_GET['condt'];
		//var_dump($condt);
		$user_name=empty($_GET['user_name']) ? '' : trim($_GET['user_name']);
		$mobile=empty($_GET['mobile']) ? '' : trim($_GET['mobile']);
		$real_name=empty($_GET['real_name']) ? '' :trim($_GET['real_name']);
		$store_id=empty($_GET['store_id']) ? 0 :intval($_GET['store_id']);
		$member=empty($_GET['member']) ? '' :trim($_GET['member']);
		$sorder=$_GET['sorder'];
		//����
		$conditions = " 1=1";
		$this->assign('condt',$condt);
		$this->assign('sorder',$sorder);
		if(!IS_POST) {
			switch($condt){
				//��Ա��
				case 0:
					if(!$user_name == ''){
						$conditions .= " AND m.user_name like '%".$user_name."%' ";
			        	$this->assign('user_name',$user_name);
					}
					break;
				//��ʵ����
				case 1:
					if(!$real_name == ''){
						$conditions .= " AND m.real_name like '%".$real_name."%' ";
						$this->assign('real_name',$real_name);
					}
					break;
				//�ֻ�����
				case 2:
					if(!$mobile == ''){
						$conditions .= " AND m.mobile like '%".$mobile."%' ";
						$this->assign('mobile',$mobile);
					}
					break;
				//��Ա����
				case 3:
					if($member == 'shops'){
							$conditions .= " AND s.store_id <> ''";
							$this->assign('member_value',$member);
					}else if($member== 'channel'){
							$conditions .= " AND m.is_bind_channel = 1";
							$this->assign('member_value',$member);
					} else if($member == 'user') {
						$this->assign('member_value',$member);
					}
					break;
			}
	        $count = $this->_member_mod->getOne("select count(*) from pa_member m left join pa_store s on m.user_id = s.store_id  where " . $conditions  );
	        $page['item_count'] = $count;
	        //����
			switch ($sorder){
	        	case 0:
				        if (isset($_GET['sort']) && isset($_GET['order']))
			        {
			            $sort  = strtolower(trim($_GET['sort']));
			            $order = strtolower(trim($_GET['order']));
			            if (!in_array($order,array('asc','desc')))
			            {
			             $sort  = 'logins';
			             $order = 'desc';
			            }
			        }
			        else
			        {
			            $sort  = 'logins';
			            $order = 'desc';
			        }
		        	$member_info = $this->_member_mod->getAll("select *,s.is_bind_channel as bind from pa_member m left join pa_store s on m.user_id = s.store_id  where " . $conditions . " order by " . $sort . ' ' . $order . " limit " . $page['limit']);
		        	$this->assign('member_info',$member_info);
		        	break;
	        	case 1:
	        		if (isset($_GET['sort']) && isset($_GET['order']))
			        {
			            $sort  = strtolower(trim($_GET['sort']));
			            $order = strtolower(trim($_GET['order']));
			            if (!in_array($order,array('asc','desc')))
			            {
			             $sort  = 'reg_time';
			             $order = 'desc';
			            }
			        }
			        else
			        {
			            $sort  = 'reg_time';
			            $order = 'desc';
			        }
		        	$member_info = $this->_member_mod->getAll("select *,m.is_bind_channel,s.is_bind_channel as bind from pa_member m left join pa_store s on m.user_id = s.store_id  where " . $conditions . " order  by " . $sort . ' ' . $order . " limit " . $page['limit']);
	
		        	$this->assign('member_info',$member_info);
		        	break;
	        }
	        $this->_format_page($page);
	        //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
	        $this->assign('page_info', $page);          
		    $this->display("smsSend.index.html");
		} else {
			
			$_member_mod = & m('member');
			$contnet = empty($_POST['content']) ? '' : trim($_POST['content']);
			$ids = $_POST['uid'];
			$id_str = '';
		
			if(is_array($ids)) {
				foreach($ids as $id) {
					$id_str .= $id . ",";
				}
				$id_str = substr($id_str,0,-1);
				$users = $_member_mod->getAll('select m.mobile,m.user_id from pa_member m where m.user_id in(' . $id_str .')');
				$mobiles = array();		
				foreach($users as $k => $v) {
					$mobiles[] = $v['mobile'];
				}
				$result = $this->send($mobiles,$contnet);
				
				if($result->g_SubmitResult->State == 0) {
					$this->show_message('����Ⱥ���ɹ�!');
				} else {
					$this->show_message('����Ⱥ��ʧ��!');
				}
					
			} else {
				$this->show_warning('Error! δѡȡ�û�!');
			}
		}
		*/
	}

	public function send($mobiles,$content) {
    	import('class.smswebservice');    //������ŷ�����
        $sms = SmsWebservice::instance(); //ʵ�������Žӿ���
        $content = str_ireplace('\\', '', $content);
        $stat = $sms->SmsFsend($mobiles,$content);
        foreach($mobiles as $mobile) {      	
        			$param = array(
        				'user_id' => $mobile['user_id'],
        				'store_id' => 0,
        				'mobile' => $mobile,
        				'smscontent' => $content,
        				'type' => 'system',
        				'sendtime' => time(),
        			); 			
        			$sms->log($param);
        }      
        return $stat;
    }
}