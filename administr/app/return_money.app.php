<?php
/**
 *    ������������
 *
 *    @author    Garbin
 *    @usage    none
 */
class Return_moneyApp extends BackendApp
{
	var $_order_mod;
	var $_log_mod;
	function __construct(){
		$this->return_moneyApp();
	}
 	function return_moneyApp(){
    	parent::__construct();
    	$this->_order_mod=& m('order');
    	$this->_log_mod=& m('orderlog');  	
    }
    /**
     *    ����
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    { 	
    	//��������
    	$conditions = "1=1 ";
		$conditions .= $this->_get_query_conditions(array(
			array(
				'field'	=> $_GET['search_name'],
				'name'	=> 'search_value',
				'equal'	=> 'like',
			),
		));
		$status = empty($_GET['status'])? 1 : intval($_GET['status']);
		switch($status)
		{
			case 1:
				$conditions .=' AND status = 50 and op_status = 1';
				$this->assign('status',1);
				break;
			case 2:
				$conditions .=' AND status = 60 and op_status = 3';
				$this->assign('status',2);
				break;
			case 3:
			$conditions .=' AND status = 50 and op_status = 4';
			$this->assign('status',3);
			break;
		}
		$page = $this->_get_page(20);
		$page['item_count'] = $this->_order_mod->getOne('select count(*) from pa_order where '.$conditions);
		$orders=$this->_order_mod->getAll('select * from pa_order where '.$conditions.' limit '.$page['limit']);	
    	$this->assign('search_name',array(
				'seller_name' => LANG::get('��������'),
				'order_sn'	=> LANG::get('������'),
				'buyer_name' => LANG::get('���'),		
    			'payment_name'   => LANG::get('֧����ʽ'),
			));    	
		$this->_format_page($page);
    	$this->assign('orders',$orders);
    	$this->assign('page_info',$page);
        $this->display('return_money.index.html');
    }
    //�˿����
	function operate()
	{
		$order_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		$type = empty($_GET['type']) ? '' : intval($_GET['type']);
		$orders=$this->_order_mod->getRow('select o.order_sn,o.order_id,o.seller_name,o.buyer_name,o.use_money,o.use_credit,o.refund_cause,o.get_credit,o.order_amount,o.payment_name,o.add_time,o.status,o.op_status,m.mobile from pa_order o left join pa_member m on o.buyer_id = m.user_id where o.order_id ='.$order_id);
		if(!IS_POST)
		{	
			$this->assign('orders',$orders);	
			$this->display('return_money.operate.html');
		}else{
			$status = empty($_POST['pass']) ? '' : intval($_POST['pass']);
			$reason = empty($_POST['reason']) ? '' : trim($_POST['reason']);
			$data = array();	
			if($status == 4)
			{
				if($reason == '' || $reason == null)
				{
					$this->show_warning('����дδͨ��ԭ��!');
					return;
				}else
				{
					$data['status'] = 50;
					$data['op_status'] = 4;
					$data['refund_cause'] = $reason;
				}
				if(!$this->_order_mod->edit($orders['order_id'],$data))
				{
					$this->show_warning($this->_order_mod->get_error());
					return;
				}else{					
					//�˿�ɹ�/ʧ�ܷ��Ͷ���֪ͨ:
					$smslog =&  m('smslog'); 
			      	import('class.smswebservice');    //������ŷ�����
			   		$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
				    $smscontent = "�𾴵Ļ�Ա������Ҫ���˻��Ķ�����".$orders['order_sn']."δͨ������רԱ����ˣ�ԭ��".$reason.",�� ���Ե�¼��www.paila100.com����ͷ���ѯ�򲦴����ǵ����ߣ�13888888";
				    $mobile = $orders['mobile'];
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
					$this->show_message('���δͨ��','����',
						'index.php?app=return_money'
					);
				}
			}else{
				$data['status'] = 60;
				$data['refund_cause'] = '';
				$data['op_status'] = 3;
				$this->_order_mod->edit($order_id,$data);
				$money = floatval($orders['order_amount'] - $orders['use_credit']);
				//��Ա���ָ���
				changeMemberCreditOrMoney($orders['buyer_id'],$orders['use_credit'],ADD_CREDIT);
				//���²�����¼��־
				$param = array(
			        	'user_id' => $orders['buyer_id'],
			        	'user_credit' =>$orders['use_credit'] ,
						'change_time' => time(),
			            'change_desc' => "�����˿�ɹ���ϵͳ�����û�����:".$orders['use_credit'],
			            'change_type'	=>33 ,
						'order_id'	=> $orders['order_id'],
			        );
				add_account_log($param);
				changeMemberCreditOrMoney($orders['buyer_id'],$orders['get_credit'],SUBTRACK_CREDIT);
				$param = array(
			        	'user_id' => $orders['buyer_id'],
			        	'user_credit' =>"-".$orders['get_credit'] ,
						'change_time' => time(),
			            'change_desc' => "�����˿�ɹ���ϵͳ�������ͻ���:".$orders['get_credit'],
			            'change_type'	=>33 ,
						'order_id'	=> $orders['order_id'],
			        );
				add_account_log($param);
				//��Ա������
				changeMemberCreditOrMoney($orders['buyer_id'],$money,ADD_MONEY);
				$param = array(
			        	'user_id' => $orders['buyer_id'],
			        	'user_money' =>$money ,
						'change_time' => time(),
			            'change_desc' => "�����˿�ɹ���ϵͳ�����û����:".$money,
			            'change_type'	=>33 ,
						'order_id'	=> $orders['order_id'],
			        );
				add_account_log($param);
				//�˿�ɹ�/ʧ�ܷ��Ͷ���֪ͨ:
				$smslog =&  m('smslog'); 
		      	import('class.smswebservice');    //������ŷ�����
		   		$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
			    $smscontent = "�𾴵Ļ�Ա������Ҫ���˻��Ķ�����".$orders['order_sn']."�˿�ɹ�,�� ���Ե�¼��www.paila100.com����ͷ���ѯ�򲦴����ǵ����ߣ�13888888";
			    $mobile = $orders['mobile'];
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
				$this->show_message('���ͨ��,�˿�ɹ�','����',
					'index.php?app=return_money'
				);
			}
		}	
	}
    /**
     *    �鿴
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function view()
    {
    	date_default_timezone_set("Asia/Shanghai");
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $status=$this->_order_mod->getRow("select o.status from pa_order o where o.order_id = " . $order_id);
    	$a=$status['status'];
    	$this->assign('status',$a);
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* ��ȡ������Ϣ */
        $model_order =& m('order');
        $order_info = $model_order->get(array(
            'conditions'    => $order_id,
            'join'          => 'has_orderextm',
            'include'       => array(
            'has_ordergoods',   //ȡ��������Ʒ
            ),
        ));

        if (!$order_info)
        {
            $this->show_warning('no_such_order');
            return;
        }
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        $order_info['group_id'] = 0;
        if ($order_info['extension'] == 'groupbuy')
        {
            $groupbuy_mod =& m('groupbuy');
            $groupbuy = $groupbuy_mod->get(array(
                'fields' => 'groupbuy.group_id',
                'join' => 'be_join',
                'conditions' => "order_id = {$order_info['order_id']} ",
                )
            );
            $order_info['group_id'] = $groupbuy['group_id'];
        }
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            if (substr($goods['goods_image'], 0, 7) != 'http://')
            {
                $order_detail['data']['goods_list'][$key]['goods_image'] = SITE_URL . '/' . $goods['goods_image'];
            }
        }
        $order1=$this->_order_mod->getRow("select o.refund_time,o.ship_time from pa_order o where o.order_id = " . $order_id);
        $order1['refund_time'] = date('Y-m-d H:i',$order1['refund_time']);
        $order1['ship_time'] = date('Y-m-d H:i',$order1['ship_time']);
        $this->assign("ordera",$order1);
        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        $this->display('order.view.html');
    }
    public function orderprint(){ //������ӡ
    	$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* ��ȡ������Ϣ */
        $model_order =& m('order');
        $order_info = $model_order->get(array(
            'conditions'    => $order_id,
            'join'          => 'has_orderextm',
            'include'       => array(
                'has_ordergoods',   //ȡ��������Ʒ
            ),
        ));

        if (!$order_info)
        {
            $this->show_warning('no_such_order');
            return;
        }
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        $order_info['group_id'] = 0;
        if ($order_info['extension'] == 'groupbuy')
        {
            $groupbuy_mod =& m('groupbuy');
            $groupbuy = $groupbuy_mod->get(array(
                'fields' => 'groupbuy.group_id',
                'join' => 'be_join',
                'conditions' => "order_id = {$order_info['order_id']} ",
                )
            );
            $order_info['group_id'] = $groupbuy['group_id'];
        }
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            if (substr($goods['goods_image'], 0, 7) != 'http://')
            {
                $order_detail['data']['goods_list'][$key]['goods_image'] = SITE_URL . '/' . $goods['goods_image'];
            }
            $order_detail['data']['goods_list'][$key]['price_total'] = number_format(floatval($goods['price'] * $goods['quantity']),2);
        }
        $this->assign('order', $order_info);
        $this->assign('order_id',$order_id);
        $this->assign($order_detail['data']);
        $this->display('order.detaillist.html');
    }
    /*����*/
	function delivery(){
		$order_id=empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
		$status=$this->_order_mod->getRow("select o.status from pa_order o  where o.order_id = " . $order_id);
		$order_mod=& m('order');
		if(!IS_POST){
			if($order_id!=0){
	    		$or_info=$this->_order_mod->getRow("select * from pa_order o  where o.order_id = " . $order_id);
	    		$this->assign("orinfo",$or_info);
	    		$this->display("order.delv.html");
	    	}
		} else {
			if($status['status']==20){
    			$data=array();
    			$data['status']= 30;
    			$data['ship_no']=trim($_POST['ship_no']);
    			$data['ship_reason']= trim($_POST['ship_reason']);
    			$data['ship_query']=trim($_POST['ship_query']);
    			$data['ship_time']=time();
    			$data['invoice_no']=trim($_POST['invoice_no']);
    			$order_mod->edit($order_id,$data);
    			$this->index();
    		}
		}
	}
	/*�޸�״̬*/
	function audit(){
		$order_id=empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
		$user_name=$this->visitor->get('user_name');
		$status=$this->_order_mod->getRow("select o.status from pa_order o  where o.order_id = " . $order_id);
		$order_mod=& m('order');
		$log_mod=& m('orderlog');
		if(!IS_POST){
    		if($order_id!=0){
    			$this->assign('order_id',$order_id);
    	        $this->display('order.batch.html');
    		}
    	}
    	else{
    		$data1=array();
    		$data1['status']=intval($_POST['status']);
    		$data1['refund_cause']=trim($_POST['refund_cause']);
    		$data1['refund_name']=$user_name;
    		$data1['refund_time']=time();
    		$order_mod->edit($order_id,$data1);
    		//�����ʹ�û���֧��, �ͷ�������
    		$model_member = & m('member');
    		$order_info = $order_mod->get($order_id);
            //�����û����ּ�¼
			changeMemberCreditOrMoney(intval($order_info['buyer_id']),intval($order_info['use_credit']),ADD_CREDIT);
            //������־
            $model_ordergoods =& m('ordergoods');
            $model_goods = & m('goods');
            $order_goods = $model_ordergoods->find("order_id={$order_id}");
            
            $credit_notes_info = array(
            	'credit_change' => intval($order_info['use_credit']),
            	'order_id'		=> $order_id,
            	'uid'			=> intval($order_info['buyer_id']),
            	'notes'			=> 3,
            	'income_expense'=> 'income',
	            'operate_time'	=>	time(),
            	'order_type'	=> 	'onlineorder'
            );
            //�����û����ּ�¼
            addCreditNote($credit_notes_info);
    		$data2=array();
    		$data2['order_id']=$order_id;
    		$data2['operator']=$user_name;
    		$data2['order_status']=$status['status'];
    		$data2['changed_status']=intval($_POST['status']);
    		$data2['remark']=trim($_POST['refund_cause']);
    		$data2['log_time']=time();
    		$log_mod->add($data2);
    		$this->index();
    	}/**/
		//$this->display('order.batch.html');
	}
}
?>
