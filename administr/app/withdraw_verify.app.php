<?php
	class Withdraw_verifyApp extends BackendApp
	{
		var $customer_withdraw_ask_mod;
		var $member_mod;
		public function __construct()
		{
			$this->Withdraw_verifyApp();
		}
		public function Withdraw_verifyApp()
		{
			parent::__construct();
			$this->customer_withdraw_ask_mod = &m('customerwithdrawask');
			$this->member_mod =&m('member');
		}
		function index()
		{
			$status = empty($_GET['status']) ? '' : intval($_GET['status']);
			//条件查旬
			$conditions = "1=1";
			$conditions .= $this->_get_query_conditions(array(
				array(
					'field'	=> $_GET['search_name'],
					'name'	=> 'search_value',
					'equal'	=> 'like',
				),
			));
			switch($status)
			{
				case 0:
					$conditions .= " AND cws.status = 1";
					$this->assign('status',1);
					break;
				case 1:
					$conditions .= " AND cws.status = 2";
					$this->assign('status',2);
					break;
				case 2:
					$conditions .= " AND cws.status = 3";
					$this->assign('status',3);
					break;
				
			}
			$type=get_change_type();
			$page = $this->_get_page(20);
			$page['item_count'] = $this->customer_withdraw_ask_mod->getOne('select count(*) from pa_customer_withdraw_ask cws left join pa_member m on cws.user_id = m.user_id where '.$conditions);
 			
			$member = $this->customer_withdraw_ask_mod->getAll("select cws.id,m.user_id,m.user_name,m.mobile,m.money,m.frozen_money,cws.withdraw_amount,cws.draw_name,cws.draw_type,cws.draw_bank,cws.operator_time,
																cws.draw_accounts,cws.status,cws.reason,cws.operator,cws.withdraw_time from pa_customer_withdraw_ask cws left join 
																pa_member m on cws.user_id = m.user_id where $conditions ORDER BY cws.withdraw_time DESC limit ".$page['limit']); 			
			foreach($member as $k=>$v)
 			{
 				//所剩余额
 				$member[$k]['money'] = floatval($member[$k]['money'] - $member[$k]['frozen_money']);
 				$member[$k]['draw_type'] = $type[$v['draw_type']];
 				$member[$k]['withdraw_time'] = $v['withdraw_time'];
 				$member[$k]['operator_time'] = $v['operator_time'];
 			}
 			$this->assign('member',$member);
 			$this->assign('search_name',array(
				'm.user_name' => LANG::get('user_name'),
				'cws.draw_bank'	=> LANG::get('draw_bank'),
				'cws.draw_name'	=> LANG::get('draw_name'),
				'm.mobile' => LANG::get('mobile'),		
			));	
 			$this->_format_page($page);
 			$this->assign('page_info', $page);
			$this->display('withdraw_verify_index.html');
		}
		function verify()
		{			
			//类型
			$type=get_change_type();	
			$id = empty($_GET['id']) ? 0 : intval($_GET['id']);			
			$member = $this->customer_withdraw_ask_mod->getRow("select cws.id,m.user_id,m.user_name,m.mobile,m.money,m.frozen_money,cws.withdraw_amount,cws.draw_name,cws.draw_type,cws.draw_bank,cws.operator_time,
																cws.draw_accounts,cws.status,cws.reason,cws.operator,cws.withdraw_time from pa_customer_withdraw_ask cws left join 
																pa_member m on cws.user_id = m.user_id where cws.id={$id}");
			$member['type'] = $type[$member['draw_type']];
			$member['operator_time'] = $member['operator_time'];
			if(!IS_POST)
			{					
				$this->assign('member',$member);
				$this->display('withdraw_verify.form.html');
			}else{	
				$data = array();
				$data['status'] = $_POST['pass'];
				$data['operator'] = $this->visitor->get('user_name');
				$data['operator_time'] = time();
				$data['reason'] = $_POST['drop_reason'];	
				if($data['status'] == 2)
				{
					//会员余额更新
		    		changeMemberCreditOrMoney($member['user_id'],$member['withdraw_amount'],CANCLE_FROZEN_MONEY);
					//更新操作记录日志
					$param = array(
				        	'user_id' => $member['user_id'],
				        	'frozen_money' =>'-'.$member['withdraw_amount'] ,
							'change_time' => time(),
				            'change_desc' => "会员提现成功，系统取消冻结余额:".$member['withdraw_amount'],
				            'change_type'	=>	$member['draw_type'],
				        );
					add_account_log($param);
					//会员余额更新
		    		changeMemberCreditOrMoney($member['user_id'],$member['withdraw_amount'],SUBTRACK_MONEY);
					//更新操作记录日志
					$param = array(
				        	'user_id' => $member['user_id'],
				        	'user_money' =>"-".$member['withdraw_amount'] ,
							'change_time' => time(),
				            'change_desc' => "会员提现成功，系统扣除用户余额:".$member['withdraw_amount'],
				            'change_type'	=>	$member['draw_type'],
				        );
					add_account_log($param);
				}
				if($data['status'] == 3)
				{
					if($data['reason'] == '' || $data['reason'] == null)
					{
						$this->show_warning('请填写未通过原因!');
						return;
					}
					//会员余额更新
		    		changeMemberCreditOrMoney($member['user_id'],$member['withdraw_amount'],CANCLE_FROZEN_MONEY);
					//更新操作记录日志
					$param = array(
				        	'user_id' => $member['user_id'],
				        	'frozen_money' =>'-'.$member['withdraw_amount'] ,
							'change_time' => time(),
				            'change_desc' => "会员提现申请失败，系统取消冻结余额:".$member['withdraw_amount'],
				            'change_type'	=>	$member['draw_type'],
				        );
					add_account_log($param);
				}
				if(!$this->customer_withdraw_ask_mod->edit($id,$data))
				{
					$this->show_warning($this->customer_withdraw_ask_mod->get_error(''));
					return;
				}else{					
					$this->show_message('审核成功',
			 		'继续审核',	'index.php?app=withdraw_verify');
					}	
			}		
		}
	}
?>