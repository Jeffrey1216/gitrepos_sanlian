<?php
	class Credit_viewApp extends BackendApp
	{
		var $member_mod;
		var $credit_verify_mod;
		var $account_log_mod;
		
		public function __construct()
		{
			$this->Credit_viewApp();
		}
		
		public function Credit_viewApp()
		{
			parent::__construct();
			$this->member_mod = &m('member');
			$this->credit_notes_mod = &m('creditnotes');
			$this->account_log_mod = &m('accountlog');
		}
		function index()
		{
			$type = get_change_type();
			$member_mod = &m('member');
			//分页条数
			$page_num = 20;
			$page = $this->_get_page($page_num);
			$condt = $_GET['condt'];						
			$this->assign("condt",$condt);
			$conditions = "1=1";
			if(!IS_POST)
			{
				switch($condt)
				{
					//会员名
					case 0:
						$user_name = empty($_GET['mem']) ? '' : trim($_GET['mem']);
						if(!$user_name == '')
						{
							$conditions .= " AND m.user_name like '%".$user_name."%' ";
							$this->assign('user_name',$user_name);
						}
						break;
					//真实姓名
					case 1:
						$email = empty($_GET['mem']) ? '' : trim($_GET['mem']);	
						if(!$email == '')
						{
							$conditions .= " AND m.email like '%".$email."%' ";
							$this->assign('email',$email);
						}					
				}	
			}
			$page['item_count'] = $this->account_log_mod->getOne("select count(*) from pa_account_log al left join pa_member m on al.user_id = m.user_id left join pa_credit_verify cv on al.verify_id = cv.id where " .$conditions);
			$credit_info  = $this->account_log_mod->getAll("select al.*,m.user_name,m.email from pa_account_log al left join pa_member m on al.user_id = m.user_id left join pa_credit_verify cv on al.verify_id = cv.id where ".$conditions. " order by al.change_time desc limit " .$page['limit']);
			
			foreach($credit_info as $k=>$v)
			{
				$credit_info[$k]['change_type'] = $type[$v['change_type']];
			}
			$this->_format_page($page);
			$this->assign('page_info',$page);
			$this->assign("credit_info",$credit_info);
			$this->display("credit_view.index.html");
		}
	}
?>