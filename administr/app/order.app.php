<?php

/**
 *    ������������
 *
 *    @author    Garbin
 *    @usage    none
 */
class OrderApp extends BackendApp
{
	var $_order_mod;
	var $_log_mod;
	function __construct(){
		$this->OrderApp();
	}
 	function OrderApp(){
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
        $search_options = array(
            //'seller_name'   => Lang::get('store_name'),
            'buyer_name'   => Lang::get('buyer_name'),
            'payment_name'   => Lang::get('payment_name'),
            'order_sn'   => Lang::get('order_sn'),
        );
        /* Ĭ���������ֶ��ǵ����� */
        $field = 'seller_name';
        $status=empty($_GET['status']) ? 11 : intval($_GET['status']);
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = " AND seller_id=".STORE_ID;
        $conditions .= $this->_get_query_conditions(array(array(
                'field' => $field,       //���û���,������,֧����ʽ���ƽ�������
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'status',
                'equal' => '=',
                'type'  => 'numeric',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'order_amount',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'order_amount',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
        $model_order =& m('order');
        $page   =   $this->_get_page(10);    //��ȡ��ҳ��Ϣ
        //��������
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'add_time';
             $order = 'desc';
            }
        }
        else
        {
            $sort  = 'add_time';
            $order = 'desc';
        }
        $orders = $model_order->find(array(
            'conditions'    => '1=1 ' . $conditions,
            'limit'         => $page['limit'],  //��ȡ��ǰҳ������
            'order'         => "$sort $order",
            'count'         => true             //����ͳ��
        )); //�ҳ������̳ǵĺ������
        $page['item_count'] = $model_order->getCount();   //��ȡͳ�Ƶ�����
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //�Ƿ��в�ѯ����
        $this->assign('order_status_list', array(
            ORDER_PENDING => Lang::get('δ����'),
            ORDER_ACCEPTED => Lang::get('�Ѹ���,������ȷ��'),
            ORDER_SHIPPED => Lang::get('�ѷ���'),
            ORDER_FINISHED => Lang::get('���׳ɹ�'),
            ORDER_REFUND => Lang::get('�˿���'),
            ORDER_REFUND_FINISH => Lang::get('�˿����'),
            ORDER_CANCELED => Lang::get('����ȡ��'),
        ));
        $this->assign('search_options', $search_options);
        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->assign('orders', $orders);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('order.index.html');
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
    	$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }
    	if(!IS_POST)
    	{
    	date_default_timezone_set("Asia/Shanghai");
        $status=$this->_order_mod->getRow("select o.status from pa_order o  where o.order_id = " . $order_id);
    	$a=$status['status'];
    	$this->assign('status',$a);

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
    	}else {
    		$order_mod=& m('order');    		
    		if($order_mod->edit($order_id,array('op_status' => 1)))
    		{
    			$this->show_message('ȷ�ϳɹ�!',
				'back_list',	'index.php?app=order'
				);	
    		}
    	}    	
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
		$member = & m('member');
		if(!IS_POST){
    		if($order_id!=0){
    			$this->assign('order_id',$order_id);
    	        $this->display('order.batch.html');
    		}
    	}
    	else{
    		//�����ʹ�û���֧��, �ͷ�������
    		$order_info = $order_mod->get($order_id);
    		
    		//������ɵĶ����˿�
    		if ($order_info['status'] == 40 && $_POST['status']==60)
    		{
    			//�ж��Ƿ��������û�����
    			if ($order_info['get_credit']>0)
    			{
    				$member_info = $member->get($order_info['buyer_id']);
    				$credit = $member_info['credit'] - $member_info['frozen_credit']; //�û����Ի���
    				if ($credit<$order_info['get_credit'])
    				{
    					$this->show_warning('��Ա���û��ֲ����Եֿ۴˶������͵Ļ��֣��˶������ܽ����˿������');
    					return;
    				}
    				//�۳��������͵�PL��
    				changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['get_credit'],SUBTRACK_CREDIT);
    				//��ӻ�Ա�˻���¼
		    		$param = array(
		    			'user_id' => $order_info['buyer_id'],
		    			'credit' => '-'.$order_info['get_credit'],
		    			'change_time' => gmtime(),
		    			'change_desc' => "����Ա��{$user_name}�����������˿�۳��������ͻ��֣�{$order_info['get_credit']}PL",
		    			'change_type' => 34,
		    		    'order_id' => $order_id,
		    		);
		    		add_account_log($param);
    			}
    		}
		    		
    		//������״̬Ҫ�޸�Ϊ����ȡ�����˿����ʱ
    		if($_POST['status']==0||$_POST['status']==60)
    		{
    			//������ԭʼ״̬Ϊ������Ѹ���ѷ����������״̬ʱ�����Խ����˿��ȡ������---�˻��û���ʹ�õĻ��֡�
    			if( $order_info['status'] == 20 || $order_info['status'] == 30 || $order_info['status'] == 40)
    			{
    				//��ӻ�Ա�˻���¼
					$param = array(
		    			'user_id' => $order_info['buyer_id'],
		    			'change_time' => gmtime(),
		    			'change_type' => 33,
					    'order_id' => $order_id,
		    		);
		    		$param['change_desc'] = "����Ա��{$user_name}�����������˿";
    				if ($order_info['use_credit']>0)
    				{
    					//�����û�����
						changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['use_credit'],ADD_CREDIT);
						$param['user_credit'] = $order_info['use_credit'];
						$param['change_desc'] .= "�˻��û����֣�{$order_info['use_credit']}PL ";
    				}
    				$money = $order_info['order_amount'] - $order_info['use_credit'];
    				if ($money>0)
    				{
    					//�����û����
						changeMemberCreditOrMoney(intval($order_info['buyer_id']),$money,ADD_MONEY);
						$param['user_money'] = $money;
						$param['change_desc'] .= "�˻��û�����{$order_info['use_money']}";
    				}
		    		add_account_log($param);
		    		
    			}elseif($order_info['status'] == 11)
    			{
    				if ($order_info['use_credit']>0)
    				{
    					//ȡ���û��������
    					changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['use_credit'],CANCLE_FROZEN_CREDIT);
    					//��ӻ�Ա�˻���¼
			    		$param = array(
			    			'user_id' => $order_info['buyer_id'],
			    			'frozen_credit' => '-'.$order_info['use_credit'],
			    			'change_time' => gmtime(),
			    			'change_desc' => "����Ա��{$user_name}������ȡ������,ȡ��������֣�{$order_info['use_credit']}PL",
			    			'change_type' => 32,
			    		    'order_id' => $order_id,
			    		);
			    		add_account_log($param);
    				}
    			}
    		}
    		
    		$data1=array();
    		$data1['status']=intval($_POST['status']);
    		$data1['refund_cause']=trim($_POST['refund_cause']);
    		$data1['refund_name']=$user_name;
    		$data1['refund_time']=time();
    		$order_mod->edit($order_id,$data1);
    		
    		//��Ӷ���������־
    		$data2['order_id']=$order_id;
    		$data2['operator']=$user_name;
    		$data2['order_status']= order_status($status['status']);
    		$data2['changed_status']= order_status(intval($_POST['status']));
    		$data2['remark']=trim($_POST['refund_cause']);
    		$data2['log_time']=time();
    		$log_mod->add($data2);
    		$this->index();
    	}/**/
	}
}
?>
