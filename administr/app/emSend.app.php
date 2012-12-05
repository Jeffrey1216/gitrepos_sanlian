<?php
class EmSendApp extends BackendApp {
	public function __construct() {
		$this->EmSendApp();
	}
	public function EmSendApp() {
		parent::BackendApp();
		$this->_member_mod=& m('member');
	}
	public function index() {
		$page_num=50;
		//var_dump($_GET);
		$page=$this->_get_page($page_num);	
		$condt=$_GET['condt'];
		//var_dump($condt);
		$user_name=empty($_GET['user_name']) ? '' : trim($_GET['user_name']);
		$mobile=empty($_GET['mobile']) ? '' : trim($_GET['mobile']);
		$real_name=empty($_GET['real_name']) ? '' :trim($_GET['real_name']);
		$store_id=empty($_GET['store_id']) ? 0 :intval($_GET['store_id']);
		$member=empty($_GET['member']) ? '' :trim($_GET['member']);
		$sorder=$_GET['sorder'];
		$conditions = " 1=1";
		$this->assign('condt',$condt);
		$this->assign('sorder',$sorder);
		if(!IS_POST) {
		switch($condt){
			case 0:
				if(!$user_name == ''){
					$conditions .= " AND m.user_name like '%".$user_name."%' ";
		        	$this->assign('user_name',$user_name);
				}
				break;
			case 1:
				if(!$real_name == ''){
					$conditions .= " AND m.real_name like '%".$real_name."%' ";
					$this->assign('real_name',$real_name);
				}
				break;
			case 2:
				if(!$mobile == ''){
					$conditions .= " AND m.mobile like '%".$mobile."%' ";
					$this->assign('mobile',$mobile);
				}
				break;
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
		        	$member_info = $this->_member_mod->getAll("select *,m.is_bind_channel,s.is_bind_channel as bind from pa_member m left join pa_store s on m.user_id = s.store_id  where " . $conditions . " order by " . $sort . ' ' . $order . " limit " . $page['limit']);
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
	        $this->assign('page_info', $page);          
		    $this->display("emSend.index.html");
	}else {
			$_member_mod = & m('member');
			$content = empty($_POST['content']) ? '' : trim($_POST['content']);
			$subject = empty($_POST['subject']) ? '' : trim($_POST['subject']);
			$content = str_ireplace('\\', '', $content);
			$ids = $_POST['uid'];
			$id_str = '';
			if(is_array($ids)) {
				foreach($ids as $id) {
					$id_str .= $id . ",";
				}
				$id_str = substr($id_str,0,-1);
				$users = $_member_mod->getAll('select m.email from pa_member m where m.user_id in(' . $id_str .')');
				$mailer =& get_mailer();
				foreach ($users as $k=>$v){
					$result=$mailer->send($v['email'],$subject,$content,CHARSET,1);
				}
				$this->show_message('邮件发送完成，成功' . $result['success'] . '封，失败' . $result['fail'] . '封');
			} else {
				$this->show_warning('Error! ');
			}
		}
	}
/*	public function send($emails,$subject,$content) {
    	import('mailer.lib');   //导入邮件发送类
    	$sms = MailQueue::MailQueue();
        $success_num = 0;
        $fail_num = 0;
        foreach($emails as $email) {
        	if($email['email'] != '') {
        		$state = $sms->SendSms($email['email'],$subject,$content);
        		if($state->g_SubmitResult->State == 0) {
        			$success_num++;
        		} else {
        			$fail_num++;
        		}
        	} else {
        		$fail_num++;
        	}
        }
        $result = array('success' => $success_num , 'fail' => $fail_num);
        return $result;
    }*/
}
/*$mailer =& get_mailer();
            $mailer->send($email,addslashes($mail['subject']),addslashes($mail['message']), CHARSET, 1);*/
?>