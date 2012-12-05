<?php
	define('ZERO', 0);
	define('ONE',1);
	class RefundsApp extends BackendApp
	{
		var $_order_mod;
		function  __construct()
		{
			$this->RefundsApp();
		}
		function RefundsApp()
		{
			parent::__construct();
			$this->_order_mod = &m('order');
		}
		//����
		function index()
		{
			//����
			$conditions = '1=1 and evaluation_status != 1 and (status = 50 or status = 60)';
			$conditions .= $this->_get_query_conditions(array(array(
					'field' => 'order_sn',
					'equal' => '=',
					'name' => 'order_sn',
				),array(
					'field' => 'type',
					'equal' => '=',
					'name'  => 'order_type',	
				),array(
					'field' =>'status',
					'equal' =>'=',
					'name' => 'type',
				),array(
					'field' =>'op_status',
					'equal' =>'=',
					'name' => 'verify_type',
				),array(
					'field' => 'buyer_name',
					'equal' => 'LIKE',
					'name'  => 'buyer_name',
				),array(
					'field' => 'refund_time',
					'name'  => 'refund_time_from',
					'equal' => '>=',
					'handler' => 'gmstr2time',
				),array(
					'field' => 'refund_time',
					'name'  => 'refund_time_to',
					'equal' => '<=',
					'handler' => 'gmstr2time',
				),
			));
			$page = $this->_get_page(20);
			$order_info = $this->_order_mod->find(array(
				'fields' =>'this.*',
				'conditions' => $conditions,
				'count'	=> true,
				'order_by' => ' desc refund_name',
				'limit' => $page['limit'],
			));
			$page['item_count'] = $this->_order_mod->getCount();
			$this->assign('order_type',array(
					ZERO =>Lang::get('���϶���'),
					ONE => Lang::get('���¶���'),
			));
			$this->assign('type',array(
					'50' =>Lang::get('�˿���'),
					'60' =>Lang::get('�˿����'),
			));
			$this->assign('verify_type',array(
					'0' => 'δ����',
					'1' => '�����',
					'2' => 'δͨ��',
			));
			$this->_format_page($page);
			$this->assign('page_info',$page);
			$this->assign('order_info',$order_info);
			$this->display('refunds.index.html');
		}
		//�������
		function verify()
		{
			$id = empty($_GET['id']) ? '' : intval($_GET['id']);
			$type = empty($_GET['type']) ? '' : intval($_GET['type']);
			if(!$id)
			{
				$this->show_warning('�ö���������!');
				return;
			}
			$order_info = $this->_order_mod->getRow('select o.order_sn,o.type,o.seller_name,o.op_status,o.buyer_name,o.pay_time,o.refund_time,o.status,o.refund_cause,m.mobile from pa_order o 
													left join pa_member m on o.buyer_id = m.user_id where o.order_id ='.$id);
			$this->assign('order_info',$order_info);
			if(!IS_POST)
			{
				$order_info['op_status'] = $data[$order_info['op_status']];
				$this->assign('type',$type);
				$this->display('refunds.verify.html');
			}else{
				$status = empty($_POST['pass']) ? '' : intval($_POST['pass']);
				$reason = empty($_POST['reason']) ? '' : trim($_POST['reason']);
				$data = array();
				$data['op_status'] = $status;
				if($data['op_status'] == 1)
				{
					if(!$this->_order_mod->edit($id,$data))
					{
						$this->show_warning($this->get_error());
						return;
					}else{
						$this->show_message('�������ͨ������ȴ��������!',
							'����', 'index.php?app=refunds'
						);				
					}
				}else{
					if($reason == '' || $reason == null)
					{
						$this->show_warning('����дδͨ��ԭ��!');
						return;
					}else{
						$data['refund_cause'] = $reason;
						if(!$this->_order_mod->edit($id,$data))
						{
							$this->show_warning($this->get_error());
							return;
						}else{//����δͨ����������Ϣ�����û�
							$smslog =&  m('smslog'); 
					      	import('class.smswebservice');    //������ŷ�����
					   		$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
						    $smscontent = "�𾴵Ļ�Ա������Ҫ���˻��Ķ�����".$order_info['order_sn']."δͨ������רԱ����ˣ�ԭ��".$reason.",�� ���Ե�¼��www.paila100.com����ͷ���ѯ�򲦴����ǵ����ߣ�13888888";
						    $mobile = $order_info['mobile'];
						    $verifytype = "system";	
					      	$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ������Ѳ���
					      	//���ŷ��ͳɹ�
					        if ($result == 0) 
					        {
					        	$time = time();
					        	//ִ�ж�����־д�����
					        	$smsdata['mobile'] = $mobile;
					        	$smsdata['smscontent'] = $smscontent;
					        	$smsdata['type'] = $verifytype; //��������
					        	$smsdata['sendtime'] = $time;
					       		$smslog->add($smsdata);
					       	}
							$this->show_message('�������δͨ��!',
								'����', 'index.php?app=refunds'
							);				
						}
					}
				}			
			}	
		}
	}
?>