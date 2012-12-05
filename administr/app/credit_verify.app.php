<?php
	class Credit_verifyApp extends BackendApp
	{
		var $credit_verify_mod;
		var $credit_notes_mod;	
		var $member_mod;
		public function __construct()
		{
			$this->Credit_verifyApp();
		}
		
		public function Credit_verifyApp()
		{
			parent::__construct();
			$this->credit_verify_mod = &m('creditverify');
			$this->credit_notes_mod = &m('creditnotes');
			$this->member_mod = &m('member');
		}
		function index()
		{
			$verify = empty($_GET['verify']) ? '' : intval($_GET['verify']);
			$type = get_change_type();
			$page_num = 20;
			$page = $this->_get_page($page_num);
			$condt = $_GET['condt'];
			$this->assign('condt',$condt);
			$conditions = "1=1";
			switch($verify)
				{
					case 0:
						$conditions .= " AND cv.verify = 0";
						$this->assign('verify',0);
						break;
					case 1:
						$conditions .= " AND cv.verify = 1";
						$this->assign('verify',1);
						break;
					case 2:
						$conditions .= " AND cv.verify = 2";
						$this->assign('verify',2);
						break;
					default : 
						$this->show_warning("程序出错!");
						return;
				}
				switch($condt)
				{
					//会员名
					case 0:
						$user_name = empty($_GET['names']) ? '' : trim($_GET['names']);
						if(!$user_name =='')
						{
							$conditions .= " AND m.user_name like '%".$user_name."%'";
							$this->assign('user_name',$user_name);
						}
						break;
					case 1: 
						$phone_mob = empty($_GET['names']) ? '' : trim($_GET['names']);
						if(!$phone_mob == '')
						{
							$conditions .=" AND m.phone_mob like '%".$phone_mob."%'";
							$this->assign('phone_mob',$phone_mob);
						}
				}
			$count = $this->credit_verify_mod->getOne("select count(*) from pa_credit_verify cv left join pa_member m on cv.user_id = m.user_id  where ".$conditions);
			$page['item_count'] = $count;	
			$credit_verify = $this->credit_verify_mod->getAll("select cv.id,cv.user_id,m.user_name,m.phone_mob,cv.credit,cv.notes,cv.add_time,cv.verify,cv.operator from pa_credit_verify cv left join pa_member m on cv.user_id = m.user_id where ". $conditions ."  order by cv.add_time desc limit ".$page['limit']);
			$this->_format_page($page);
			$this->assign('page_info',$page);
			foreach ($credit_verify as $k=>$v)
			{
				$credit_verify[$k]['notes'] = $type[$v['notes']];
			}
			$this->assign('credit_verify',$credit_verify);
			$this->display("credit_verify.index.html");
		}
		function verify()
		{	
			$type = get_change_type();
			$user_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
			$credit_verify = $this->credit_verify_mod->getRow('select cv.id,cv.user_id,m.user_name,m.phone_mob,cv.credit,cv.notes,cv.add_time,cv.verify,cv.operator from pa_credit_verify cv left join pa_member m on cv.user_id = m.user_id where cv.id ='.$user_id);
			if(!IS_POST)
			{		
				$credit_verify['notes'] = $type[$credit_verify['notes']];
				$this->assign('verify',$credit_verify);
				$this->display('credit_verify.form.html');
			}else{				
				$data = array();
				$data['verify'] = intval($_POST['pass']);
				$data['reason'] = trim($_POST['drop_reason']);
				$data['verify_name'] = $this->visitor->get('user_name');
				$data['user_id'] = $credit_verify['user_id'];
				if($data['verify'] == '')
				{
					$this->show_warning('请仔细审核!');
					return ;
				}				
				if($data['verify'] == 1)
				{
					if($credit_verify['notes'] == 9)
					{
						changeMemberCreditOrMoney($credit_verify['user_id'],$credit_verify['credit'],SUBTRACK_CREDIT);
						//更新操作记录日志
						$param = array(
					        	'user_id' => $credit_verify['user_id'],
					        	'user_credit' =>"-".$credit_verify['credit'] ,
								'change_time' => time(),
					            'change_desc' => "系统扣除积分:".$credit_verify['credit'],
					            'change_type'	=>	$credit_verify['notes'],
					        );
						add_account_log($param);
					}else
					{
						changeMemberCreditOrMoney($credit_verify['user_id'],$credit_verify['credit'],ADD_CREDIT);
						//更新操作记录日志
						$param = array(
					        	'user_id' => $credit_verify['user_id'],
					        	'user_credit' =>$credit_verify['credit'] ,
								'change_time' => time(),
					            'change_desc' => "系统赠送积分:".$credit_verify['credit'],
					            'change_type'	=>	$credit_verify['notes'],
					        );
						add_account_log($param);
					}
				}
				if($data['verify'] == 2)
				{
					if($data['reason'] == '' || $data['reason'] == null)
					{
						$this->show_warning('请填写未通过原因!');
						return;
					}				
				}		
				if(!$user_id = $this->credit_verify_mod->edit($credit_verify['id'],$data))
				{
					$this->show_warning($this->credit_verify_mod->get_error());
					return;
				}else{					
					$this->show_message('审核成功',
			 		'继续审核',	'index.php?app=credit_verify');
					}
				}
			}			
	}
?>