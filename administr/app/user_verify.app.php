<?php
	class User_verifyApp extends BackendApp
	{
		var $_credit_verify_mod;
		var $_user_mod;
		function __construct()
		{
			$this->User_verifyApp();
		}
		function User_verifyApp()
		{
			parent::__construct();
			$this->_credit_verify_mod = &m('creditverify');
			$this->_user_mod = &m('member');		
		}
		//����
		function index()
		{
			//����
			$search_options = array(
            'm.user_name'   => Lang::get('��Ա��'),
            'm.real_name'   => Lang::get('��ʵ����'),
            'm.mobile'   => Lang::get('�ֻ�'),
       		);
	        /* Ĭ���������ֶ��ǵ����� */
	        $field = 'seller_name';
	        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
			$conditions = '1=1';
        	$conditions .= $this->_get_query_conditions(array(array(
                'field' => $field,       
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'cv.add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'cv.add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'cv.money',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'cv.money',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ), array(
                'field' => 'cv.credit',
                'name'  => 'order_pl_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'cv.credit',
                'name'  => 'order_pl_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),          
       		));
       		$verify = empty($_GET['verify']) ? '' : intval($_GET['verify']);
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
					$this->show_warning("�������!");
					return;
			}
			$type = get_change_type();
			$page = $this->_get_page(20);
			$page['item_count'] = $this->_credit_verify_mod->getOne('select count(*) from pa_credit_verify cv left join pa_member m on cv.user_id = m.user_id where '.$conditions);
			$user_verify = $this->_credit_verify_mod->getAll('select cv.id,m.user_name,m.real_name,m.mobile,cv.credit,cv.money,cv.notes,cv.add_time,cv.operator,cv.verify from 
															pa_credit_verify cv left join pa_member m on cv.user_id = m.user_id where '.$conditions.' ORDER BY cv.add_time desc limit '.$page['limit']);		
			foreach($user_verify as $k => $v)
			{
				$user_verify[$k]['notes'] = $type[$v['notes']];
			}	
			$this->_format_page($page);
			$this->assign('search_options',$search_options);
			$this->assign('page_info',$page);
			$this->assign('verify',$user_verify);
			$this->display('user_verify.index.html');
		}
		//��Ա�˻��䶯���
		function verify()
		{
			$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
			$type = get_change_type();
			if(!$id)
			{
				$this->show_warning('�˻�Ա������!');
				return;
			}
			$verify = $this->_credit_verify_mod->getRow('select cv.id,m.user_name,m.real_name,cv.user_id,cv.credit,cv.money,cv.notes,cv.add_time,cv.operator,cv.verify,cv.remark,cv.reason,cv.verify_name from 
															pa_credit_verify cv left join pa_member m on cv.user_id = m.user_id where cv.id='.$id);
			if(!IS_POST)
			{			
				$verify['notes'] = $type[$verify['notes']];
				$this->assign('verify',$verify);
				$this->display('user_verify.verify.html');
			}else{
				$user_id = $this->visitor->get('user_id');
				$user_info = $this->_user_mod->get($user_id);
				if(!empty($user_info['real_name']))
				{
					$verify_name = $user_info['real_name'];
				}else {
					$verify_name = $user_info['user_name'];
				}
				$data = array();
				$data['user_id'] = $verify['user_id'];
				$data['verify'] = intval($_POST['status']);
				$data['verify_name'] = $verify_name;
				if($data['verify'] == '')
				{
					$this->show_warning('����ϸ���!');
					return;
				}			
				if($data['verify'] == 1)
				{
					if($verify['notes'] == 8)
					{
						$status = ADD_MONEY;
						$money_amount = $verify['money'];
						$desc = "ϵͳ�������:".$verify['money']."Ԫ";
					}elseif($verify['notes'] == 9)
					{
						$status = SUBTRACK_MONEY;
						$money_amount = $verify['money'];
						$desc = "ϵͳ�۳����:".$verify['money']."Ԫ";
					}elseif($verify['notes'] == 16){
					   $status = ADD_MONEY;
					   $money_amount = $verify['money'];
					   $desc = "���̳�ֵ���:".$verify['money']."Ԫ";
                       
					}elseif($verify['notes'] == 17){
					   $status = ADD_MONEY;
					   $money_amount = $verify['money'];
					   $desc = "�Ź�Ա��ֵ���:".$verify['money']."Ԫ";
                       
					}elseif($verify['notes'] == 14)
					{
						$status = ADD_CREDIT;
						$credit_amount = $verify['credit'];
						$desc = "ϵͳ���ͻ���:".$verify['credit']."PL";
					}elseif($verify['notes'] == 15)
					{
						$status = SUBTRACK_CREDIT;
						$credit_amount = $verify['credit'];
						$desc = "ϵͳ�۳�����:".$verify['credit']."PL";						
					}
					if($verify['notes'] == 8 || $verify['notes'] == 9 || $verify['notes'] == 16 || $verify['notes'] == 17)
					{	
						changeMemberCreditOrMoney($verify['user_id'],$money_amount,$status);
					}else{
						changeMemberCreditOrMoney($verify['user_id'],$credit_amount,$status);
					}
					if($verify['notes'] == 9)
					{
						//���²�����¼��־
						$param = array(
					        	'user_id' => $verify['user_id'],
					        	'user_money' =>"-".$money_amount ,
								'user_credit' =>$credit_amount,
								'change_time' => time(),
					            'change_desc' => $verify['remark'],
					            'change_type'	=>	$verify['notes'],
					        );
						add_account_log($param);
					}elseif($verify['notes'] == 15)
					{
						$param = array(
					        	'user_id' => $verify['user_id'],
					        	'user_money' =>$money_amount ,
								'user_credit' =>"-".$credit_amount,
								'change_time' => time(),
					            'change_desc' => $verify['remark'],
					            'change_type'	=>	$verify['notes'],
					        );
						add_account_log($param);
					}else{
						//���²�����¼��־
						$param = array(
					        	'user_id' => $verify['user_id'],
					        	'user_money' =>$money_amount ,
								'user_credit' =>$credit_amount,
								'change_time' => time(),
					            'change_desc' => $verify['remark'],
					            'change_type'	=>	$verify['notes'],
					        );
						add_account_log($param);
					}
				}else{
					if($data['verify'] == 2)
					{
						$data['reason'] = trim($_POST['reason']);
						if(trim($_POST['reason']) == '')
						{
							$this->show_warning('����дδͨ��ԭ��!');
							return;
						}
					}
				}
				if(!$user_id = $this->_credit_verify_mod->edit($verify['id'],$data))
				{
					$this->show_warning($this->_credit_verify_mod->get_error());
					return;
				}else{
					$this->show_message('��˳ɹ�',
			 		'�������',	'index.php?app=user_verify');
				}
			}
		}
		function finance_print()
		{
			$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
			$type = get_change_type();
			if(!$id)
			{
				$this->show_warning('�˻�Ա������!');
				return;
			}
			$verify = $this->_credit_verify_mod->getRow('select cv.id,m.user_name,m.real_name,cv.user_id,cv.credit,cv.money,cv.notes,cv.add_time,cv.operator,cv.verify,cv.remark,cv.reason,cv.verify_name from 
															pa_credit_verify cv left join pa_member m on cv.user_id = m.user_id where cv.id='.$id);	
			$verify['notes'] = $type[$verify['notes']];
			$this->assign('verify',$verify);
			$this->display('finance_print.html');
		}
	}
?>