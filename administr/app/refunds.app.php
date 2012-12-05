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
		//管理
		function index()
		{
			//搜索
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
					ZERO =>Lang::get('线上订单'),
					ONE => Lang::get('线下订单'),
			));
			$this->assign('type',array(
					'50' =>Lang::get('退款中'),
					'60' =>Lang::get('退款完成'),
			));
			$this->assign('verify_type',array(
					'0' => '未操作',
					'1' => '已审核',
					'2' => '未通过',
			));
			$this->_format_page($page);
			$this->assign('page_info',$page);
			$this->assign('order_info',$order_info);
			$this->display('refunds.index.html');
		}
		//物流审核
		function verify()
		{
			$id = empty($_GET['id']) ? '' : intval($_GET['id']);
			$type = empty($_GET['type']) ? '' : intval($_GET['type']);
			if(!$id)
			{
				$this->show_warning('该订单不存在!');
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
						$this->show_message('物流审核通过，请等待财务审核!',
							'返回', 'index.php?app=refunds'
						);				
					}
				}else{
					if($reason == '' || $reason == null)
					{
						$this->show_warning('请填写未通过原因!');
						return;
					}else{
						$data['refund_cause'] = $reason;
						if(!$this->_order_mod->edit($id,$data))
						{
							$this->show_warning($this->get_error());
							return;
						}else{//物流未通过，发送信息告诉用户
							$smslog =&  m('smslog'); 
					      	import('class.smswebservice');    //导入短信发送类
					   		$sms = SmsWebservice::instance(); //实例化短信接口类
						    $smscontent = "尊敬的会员，您所要求退货的订单：".$order_info['order_sn']."未通过物流专员的审核，原因：".$reason.",您 可以登录【www.paila100.com】与客服咨询或拨打我们的热线：13888888";
						    $mobile = $order_info['mobile'];
						    $verifytype = "system";	
					      	$result= $sms->SendSms2($mobile,$smscontent); //执行发送短信提醒操作
					      	//短信发送成功
					        if ($result == 0) 
					        {
					        	$time = time();
					        	//执行短信日志写入操作
					        	$smsdata['mobile'] = $mobile;
					        	$smsdata['smscontent'] = $smscontent;
					        	$smsdata['type'] = $verifytype; //短信提醒
					        	$smsdata['sendtime'] = $time;
					       		$smslog->add($smsdata);
					       	}
							$this->show_message('物流审核未通过!',
								'返回', 'index.php?app=refunds'
							);				
						}
					}
				}			
			}	
		}
	}
?>