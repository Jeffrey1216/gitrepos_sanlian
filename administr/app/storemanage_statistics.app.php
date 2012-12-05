<?php
	class Storemanage_statisticsApp extends BackendApp
	{
		var $store_order_goods_mod;
		var $store_order_mod;
		var $store_mod;
		var $goods_mod;
		var $_store_order_mod;
		var $_store_order_log_mod;
		var $_store_order_extm_mod;
		
		function __construct()
		{
			$this->Storemanage_statisticsApp();
		}
		function Storemanage_statisticsApp()
		{
			parent::__construct();
			$this->store_mod = &m('store');
			$this->goods_mod = &m('goods');
			$this->store_order_mod = &m('store');
			$this->store_order_goods_mod = &m('storeordergoods');
			$this->_store_order_mod=& m('storeorder');
    		$this->_store_order_log_mod=& m('storeorderlog');
    		$this->_store_order_extm_mod=& m('storeorderextm');
    		$this->assign('storemanage','true');
		}
		//����
		function index()
		{
			//$this->display('store_statistics.index.html');
			$this->sell();  //Ĭ����ʾ���̽�����
		}
		//���̽���
	function sell()
		{
			$search_options = array(
            's.store_name'   => Lang::get('store_name'),
            'so.order_sn'   => Lang::get('order_num'),
            'so.payment_name'   => Lang::get('pay_type'),
			'so.buyer_name' => Lang::get('��Ա'),
       		 );
        /* Ĭ���������ֶ��ǵ����� */
        $field = 'seller_name';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        //���û���,������,֧����ʽ���ƽ�������
        $conditions = 'status=40';
        $conditions .= $this->_get_query_conditions(array(array(
                'field' => $field,       
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'store_type',
                'equal' => '=',
                'type'  => 'numeric',
//	        ),array(
//                'field' => 'status',
//                'equal' => '=',
//                'type'  => 'numeric',
	        ),array(
                'field' => 'so.finished_time',
                'name'  => 'pay_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'so.finished_time',
                'name'  => 'pay_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'soe.shipping_fee',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'soe.shipping_fee',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),           
       		));
			//��ҳ����
			$page=$this->_get_page(20);
			//ͳ������
       		$page['item_count'] = $this->store_order_mod->getOne('select count(*) from pa_store_order so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id
       															 where '."$conditions".'  and so.status >= 20 and so.status <= 40');
       		
       		//�鿴��������
       		$goods = $this->store_order_mod->getAll('select so.order_id,so.order_sn,so.goods_amount,so.buyer_name,so.pay_amount,so.arrears_amount,so.status,s.store_type,so.pay_message,so.order_amount,s.store_name,so.payment_name,so.finished_time,so.add_time,so.pay_time,soe.shipping_fee from pa_store_order
       												 so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id where
       												  ' . "$conditions" . '  and so.status >= 20 and so.status <= 40 limit ' . $page['limit']);
			foreach ($goods as $k => $v) {
				$v['order_id'] = intval($v['order_id']);
				$sql = "select sum(sog.gprice * sog.quantity) as stock_price,sum(sog.sprice * sog.quantity) as league_price, \n"
					. " sum(sog.price * sog.quantity) as user_price from  pa_store_order_goods sog \n"
					. " where sog.order_id ={$v['order_id']}";
				$goods_all = $this->store_order_mod->getRow($sql);
				$goods[$k]['stock_price']  = $goods_all['stock_price']; //�ɹ���
				$goods[$k]['user_price'] = $goods_all['user_price']; //������
				$goods[$k]['league_price'] = $goods_all['league_price']; //������
				$goods[$k]['pl_income']    = $goods[$k]['user_price'] - $goods[$k]['stock_price']; //��˾����
			}

			$total = $this->store_order_mod->getRow('select sum(so.goods_amount) as total,sum(soe.shipping_fee) as ship_fee from pa_store_order so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id where
	       												  ' . "$conditions" . ' and so.buyer_id in (select store_id from pa_store) and so.status >= 20 and so.status <= 40');
			$totals = $this->store_order_mod->getRow('select sum(sog.gprice * sog.quantity) as gprice_totals,sum(sog.sprice * sog.quantity) as league_price,sum(sog.price * sog.quantity) as price_totals from
       												 pa_store_order so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id
       												  = soe.order_id left join pa_store_order_goods sog on so.order_id = sog.order_id where  so.buyer_id in (select store_id
       												   from pa_store) and so.status >= 20 and so.status <= 40 and '.$conditions);
       		$total['gprice_total'] = $totals['gprice_totals'];
       		$total['zprice_total'] = $totals['price_totals'];
       		$total['total'] = $totals['league_price'];
       		$total['pl_income'] = $totals['price_totals'] - $totals['gprice_totals'];
       		$this->assign('total',$total);    		
       		$this->assign('store_type',array(
       				'0' => 'ֱӪ��',
       				'1' => '���˵�',
       		));
       		$this->assign('status',array(
       				//'20' => '�Ѹ���,������',
	      			//'30' => '�Ѹ���,�ѷ���',
	     			'40' => '���׳ɹ�',
       		));
       		$this->assign('goods',$goods);
       		$this->_format_page($page);
       		$this->assign('page_info',$page);
        	$this->assign('search_options', $search_options);
			$this->display('store_statistics.sell.html');
		}
		
		
		//�����鿴
	function Store_statistics_view()
		{
			$id = intval($_GET['id']);
			$order_info = $this->store_order_mod->getRow('select * from pa_store_order so left join pa_store_order_extm soe on so.order_id = soe.order_id where so.order_id ='.$id);
			$this->assign('order_info',$order_info);
			$goods_list = $this->store_order_goods_mod->getAll('select sog.goods_name,sog.goods_image,sog.goods_id,gs.spec_1,gs.spec_2,sog.zprice,sog.quantity from 
																pa_store_order_goods sog left join pa_store_order so on sog.order_id = so.order_id left join pa_goods_spec
																 gs on sog.spec_id = gs.spec_id where so.order_id ='.$id);
			
			$this->assign('goods_list',$goods_list);
			$this->display('store_statistics.view.html');
		}
			//���˵�����ͳ��
	function collection()
		{
			$search_options = array(
            's.store_name'   => Lang::get('store_name'),
            'o.order_sn'   => Lang::get('order_num'),
			'o.buyer_name' 	 => Lang::get('��Ա'),
       		);
	        /* Ĭ���������ֶ��ǵ����� */
	        $field = 'seller_name';
	        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
	        //���û���,������,֧����ʽ���ƽ�������
	        $conditions = 'status>=20 and status<=50';
	        $conditions .= $this->_get_query_conditions(array(array(
	                'field' => $field,       
	                'equal' => 'LIKE',
	                'name'  => 'search_name',
		            ),array(
	                'field' => 'store_type',
	                'equal' => '=',
	                'type'  => 'numeric',
		       		),array(
	                'field' => 'status',
	                'equal' => '=',
	                'type'  => 'numeric',
		       		),array(
	                'field' => 'o.pay_time',
	                'name'  => 'finished_time_from',
	                'equal' => '>=',
	                'handler'=> 'gmstr2time',
	            ),array(
	                'field' => 'o.pay_time',
	                'name'  => 'finished_time_to',
	                'equal' => '<=',
	                'handler'   => 'gmstr2time_end',
	            ),array(
	                'field' => 'oe.shipping_fee',
	                'name'  => 'order_amount_from',
	                'equal' => '>=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'o.payment_id',
	                'name'  => 'payment',
	                'equal' => '=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'o.pay_type',
	                'name'  => 'paytype',
	                'equal' => '=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'oe.shipping_fee',
	                'name'  => 'order_amount_to',
	                'equal' => '<=',
	                'type'  => 'numeric',
	            ),           
	       		));
	       		
	       		//��ҳ����
	       		$page = $this->_get_page(20);
	       		//ͳ������
       			$page['item_count'] = $this->store_order_mod->getOne('select count(*) from pa_order o left join pa_store s on o.seller_id = s.store_id 
       																left join pa_order_extm oe on o.order_id = oe.order_id where '.$conditions);
       			$paytype = array(
	     			'1' => '�ֽ�',
	       			'2' => 'PL��',
	       			'3' => '�ֽ�+PL��',
	       			'4' => '���+PL��',
                    '5' => '�ֽ�+���',
                    '6' => '���',
                    '7' => '�ֽ�+���+PL��'
       			);
       			
       			
       			$orders = $this->store_order_mod->getAll('select o.order_id,o.order_sn,o.order_amount,o.goods_amount,o.cash,o.use_money,o.use_credit,(o.cash+o.use_money+o.use_credit) as showmany,o.get_credit,o.seller_name,o.buyer_name,o.status,s.store_type,oe.shipping_fee,o.payment_name,o.pay_time,o.pay_type from pa_order
       													 o left join pa_store s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id = 
       													 oe.order_id where '.$conditions .' limit '.$page['limit']);
               			
       			foreach($orders as $k=>$v)
	       		{
	       			
					$orders_all = $this->store_order_mod->getRow('select sum(og.gprice * og.quantity) as stock_price,sum(og.zprice * og.quantity) as league_price,
																sum(og.price * og.quantity) as user_price from pa_order o left join pa_order_goods og
																 on o.order_id = og.order_id where o.order_id ='.$v['order_id']);
					$orders[$k]['stock_price'] = $orders_all['stock_price']; //�ɹ���
	   				$orders[$k]['league_price'] = $orders_all['league_price'];//������
	   				$orders[$k]['income'] = floatval($orders[$k]['get_credit']) * 0.5; //�Ź�Ա����
	   				$orders[$k]['send_pl'] = floatval($orders[$k]['income']) * 0.3; //�Ź�Ա���PL
	   				$orders[$k]['send_money'] = floatval($orders[$k]['income']) * 0.7;//�Ź�Ա������
	   				$orders[$k]['store_income'] = $orders[$k]['goods_amount'] - $orders[$k]['league_price'] - $orders[$k]['send_pl']  - $orders[$k]['send_money']; //��������
	   				$orders[$k]['pl_income'] = $orders[$k]['league_price'] - $orders[$k]['stock_price'];//��˾����
                    $orders[$k]['pay_type_name'] = $paytype[$v['pay_type']];         
	       		}
	       		$total = $this->store_order_mod->getRow('select sum(o.goods_amount) as total,sum(oe.shipping_fee) as ship_fee,sum(o.get_credit) as gcredit from pa_order o left join pa_store s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id = oe.order_id where
       												  '.$conditions);
	       		$totals = $this->store_order_mod->getRow('select *,sum(og.gprice * og.quantity) as gprice_totals,sum(og.zprice * og.quantity) as zprice_totals from
	       												 pa_order o left join pa_store s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id
	       												  = oe.order_id left join pa_order_goods og on o.order_id = og.order_id where '.$conditions);
	       		$total['gprice_total'] = $totals['gprice_totals'];
	       		$total['zprice_total'] = $totals['zprice_totals'];
	       		$total['send_deduct'] = $total['gcredit'] * 0.5;//�Ź�Ա���
	       		$total['send_pl'] = $total['send_deduct'] * 0.3;//�Ź�Ա��ȡPL
	       		$total['send_money'] = $total['send_deduct'] * 0.7;//�Ź������ȡ���
	       		
	       		$total['pl_income'] = $totals['zprice_totals'] - $totals['gprice_totals'];
	       		$this->assign('total',$total);
	       		$this->assign('store_type',array(
	       				'0' => 'ֱӪ��',
	       				'1' => '���˵�',
	       		));
	       		$this->assign('status',array(
	     			'20' => '�Ѹ���,������',
	       			'30' => '�ѷ���',
	       			'40' => '���׳ɹ�',
	       			'50' => '�˿���',
       			));
                $this->assign('pay_type',$paytype);
                $this->assign('paymeny_type',$this->_get_payment_type());        
       			$this->assign('orders',$orders);       			
	       		$this->_format_page($page);
	       		$this->assign('page_info',$page);
	       		$this->assign('goods_collect',$orders_collect);
	        	$this->assign('search_options', $search_options);
				$this->display('store_statistics.collection.html');
		}
		//�������۲鿴
		function collection_view()
		{
			$id = intval($_GET['id']);
			$order_info = $this->store_order_mod->getRow('select * from pa_order o left join pa_order_extm oe on o.order_id = oe.order_id where o.order_id ='.$id);
			$this->assign('order_info',$order_info);
			$goods_list = $this->store_order_goods_mod->getAll('select og.goods_name,og.order_id,og.specification,og.price,og.goods_image,og.quantity ,og.goods_id from pa_order_goods og left join pa_order o on og.order_id = o.order_id left join 
																pa_order_extm oe on o.order_id = oe.order_id  where o.order_id ='.$id);
			
			if(!$goods_list)
			{
				$this->show_warning('�˶��������ڣ�');
				return ;
			}
			$this->assign('goods_list',$goods_list);
			$this->display('store_statistics.collection_view.html');
		}
		//���̽���������ӡ
		function orderprint()
		{
			$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			if(!$order_id)
			{
				$this->show_warning('û�д˶���');
				return;
			}
			/*��ȡ������Ϣ*/
			$order_info = $this->store_order_mod->getRow('select * from pa_store_order where order_id ='.$order_id);
			if(!$order_info)
			{
				$this->show_warning('û�д˶���');
				return;
			}
			$order_type =& ot($order_info['extension']);
	        $order_detail = $order_type->get_store_order_detail($order_id, $order_info);
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
	        $this->assign('site_url',SITE_URL);
	        $this->assign('order', $order_info);
	        $this->assign('order_id',$order_id);
	        $this->assign($order_detail['data']);
	        $this->display('store_statistics.detaillist.html');			
		}
		//�������۶�����ӡ
		function retail_orderprint()
		{
			$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			if(!$order_id)
			{
				$this->show_warning('û�д˶���');
				return;
			}
			/*��ȡ������Ϣ*/
			$order_info = $this->store_order_mod->getRow('select * from pa_order where order_id ='.$order_id);
			if(!$order_info)
			{
				$this->show_warning('û�д˶���');
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
	        $this->assign('site_url',SITE_URL);
	        $this->assign('order', $order_info);
	        $this->assign('order_id',$order_id);
	        $this->assign($order_detail['data']);
	        $this->display('store_statistics.detaillist_view.html');	
		}
		
		/**
	     *    ���̽�����������
	     *
	     *    @author   lihuoliang
	     *    @param    none
	     *    @return    void
	     */
	    function store_order()
	    {
	        $search_options = array(
	            's.store_name'   => '��������',
	            'so.payment_name'   => Lang::get('payment_name'),
	            'so.order_sn'   => Lang::get('order_sn'),
	        );
	        /* Ĭ���������ֶ��ǵ����� */
	        $field = 's.store_name';
	        $status=empty($_GET['status']) ? 11 : intval($_GET['status']);
	        $payment_id=empty($_GET['payment_id']) ? '' : trim($_GET['payment_id']);
	
	        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
	        $conditions = '1=1';
	        $conditions .= $this->_get_query_conditions(array(array(
	                'field' => $field,       //���û���,������,֧����ʽ���ƽ�������
	                'equal' => 'LIKE',
	                'name'  => 'search_name',
	            ),array(
	                'field' => 'store_type',
	                'equal' => '=',
	                'type'  => 'numeric',
		        ),array(
	                'field' => 'status',
	                'equal' => '=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'op_status',
	                'equal' => '=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'so.pay_time',
	                'name'  => 'add_time_from',
	                'equal' => '>=',
	                'handler'=> 'gmstr2time',
	            ),array(
	                'field' => 'so.pay_time',
	                'name'  => 'add_time_to',
	                'equal' => '<=',
	                'handler'   => 'gmstr2time_end',
	            ),array(
	                'field' => 'so.order_amount',
	                'name'  => 'order_amount_from',
	                'equal' => '>=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'so.order_amount',
	                'name'  => 'order_amount_to',
	                'equal' => '<=',
	                'type'  => 'numeric',
	            ),
	        ));
	    	if(!$payment_id == '') {
	        	$conditions .= " AND s.payment_id = " . $payment_id;
	        	$this->assign('payment_id',$payment_id);
	        }
	        $model_order =& m('storeorder');
	        $page   =   $this->_get_page(20);    //��ȡ��ҳ��Ϣ
	        //��������
	        if (isset($_GET['sort']) && isset($_GET['order']))
	        {
	            $sort  = strtolower(trim($_GET['sort']));
	            $order = strtolower(trim($_GET['order']));
	            if (!in_array($order,array('asc','desc')))
	            {
	             $sort  = 'pay_time';
	             $order = 'desc';
	            }
	        }
	        else
	        {
	            $sort  = 'pay_time';
	            $order = 'desc';
	        }
	        $store_order_info = $this->_store_order_mod->getAll('select so.order_id,so.order_sn,so.goods_amount,s.store_type,so.pay_message,so.order_amount,s.store_name,so.payment_name,so.add_time,so.finished_time,so.pay_time,soe.shipping_fee,so.status,so.op_status,so.pay_amount,so.arrears_amount from pa_store_order
	       												 so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id where
	       												  '."$conditions".'  ORDER BY so.pay_time DESC limit '.$page['limit']) ;
	        
	       

	        //ͳ������
	       	$page['item_count'] = $this->_store_order_mod->getOne('select count(*) from pa_store_order so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id
	       															 where '."$conditions");
	        $this->_format_page($page);
	        $this->assign('filtered', $conditions != '1=1'? 1 : 0); //�Ƿ��в�ѯ����
	        $this->assign('order_status_list', array(
	            ORDER_PENDING => Lang::get('������'),
	            ORDER_ACCEPTED => Lang::get('�Ѹ���,������'),
	            ORDER_SHIPPED => Lang::get('�ѷ���'),
	            ORDER_FINISHED => Lang::get('���׳ɹ�'),
	            ORDER_REFUND => Lang::get('�˿���'),
	            ORDER_REFUND_FINISH => Lang::get('�˿����'),
	            ORDER_CANCELED => Lang::get('����ȡ��'),
	        ));
	        $this->assign('op_status_list', array(
	            0 => Lang::get('δ����'),
	            1 => Lang::get('�����Ѹ�����������'),
	            2 => Lang::get('���������ȷ�϶����۸�'),
	            3 => Lang::get('������ȷ���տ���Ϣ'),
	            4 => Lang::get('������ȷ�Ϸ���'),
	        ));
	        $this->assign('store_type',array(
	       				'0' => 'ֱӪ��',
	       				'1' => '���˵�',
	       		));
	       	foreach ($store_order_info as $_key => $_val)
	       	{
	       		$all_amount['order_amount'] += $_val['order_amount'];
	       		$all_amount['goods_amount'] += $_val['goods_amount'];
	       		$all_amount['shipping_fee'] += $_val['shipping_fee'];
	       		$all_amount['pay_amount'] += $_val['pay_amount'];
	       		$all_amount['arrears_amount'] += $_val['arrears_amount'];
	       	}
			$this->assign('all_amount',$all_amount);
	        $this->assign('search_options', $search_options);
	        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
	        $this->assign('orders', $store_order_info);
	        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
	                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
	        Lang::load(lang_file('admin/store_order'));
	        $this->assign('app',APP);
	        $this->display('store_order.index.html');
	    }
	    //��ʾ���̽�����������
		function view()
	    {
	        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;      
	        /* ��ȡ������Ϣ */
	        $order_info = $this->_store_order_mod->get(array(
	            'conditions'    => $order_id,
	            'join'          => 'has_storeorderextm',
	            'include'       => array(
	                'has_storeordergoods',   //ȡ��������Ʒ
	            ),
	        ));
	        if (!$order_info)
	        {
	            $this->show_warning('no_such_order');
	            return;
	        }
	        $order_type =&ot($order_info['extension']);
	        $order_detail = $order_type->get_store_order_detail($order_id, $order_info);
	        $order_detail['order'] = $order_info;
	        Lang::load(lang_file('admin/store_order'));
	    	if ($_GET['output'] == 'true')
			{
			    $this->GoodsinfoExcel($order_detail);
			    exit;
			}
	        $this->assign('app',APP);
	        $this->assign('image_url',IMAGE_URL);
	        $this->assign('order',$order_info);
	        $this->assign('order_detail',$order_detail['data']);
	        $this->display('store_order.view.html');
	    }
		//������˶�����Ϣ---ȷ���տ���
	    function audit_store_order()
	    {
	    	$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
	    	/* ��ȡ������Ϣ */
	        $order_info = $this->_store_order_mod->get($order_id);
	       
	        if (!$order_info)
	        {
	            $this->show_warning('no_such_order');
	            return;
	        }
	        $data['remark']    = $_POST['remark'];
	        $data['op_status'] = 3;
	        
			$this->_store_order_mod->edit($order_id,$data);
			$this->show_message('��˶�����Ϣ�ɹ���',"�����б�",'index.php?app=store_statistics&act=store_order');
	    }
		function GoodsinfoExcel($info)
	    {
	    	import(PHPExcel);
	    	$objExcel = new PHPExcel();
	    	$objWriter = new PHPExcel_Writer_Excel5($objExcel);
			//�����ĵ���������  
			$objProps = $objExcel->getProperties();  
			$objProps->setCreator("С ��");  
			$objProps->setLastModifiedBy("С ��");  
			$objProps->setTitle("�½��ĵ�");  
			$objProps->setSubject("Office XLS Test Document, Demo");  
			$objProps->setDescription("Test document, generated by PHPExcel.");  
			$objProps->setKeywords("office excel PHPExcel");  
			$objProps->setCategory("Test");
			//*************************************  
			//���õ�ǰ��sheet���������ں��������ݲ�����  
			//һ��ֻ����ʹ�ö��sheet��ʱ�����Ҫ��ʾ���á�  
			//ȱʡ����£�PHPExcel���Զ�������һ��sheet������SheetIndex=0  
			$objExcel->setActiveSheetIndex(0);
			$objActSheet = $objExcel->getActiveSheet();  			  
			//���õ�ǰ�sheet������  
			$objActSheet->setTitle('new');
			//*************************************  
			//���õ�Ԫ������ 
			//��PHPExcel���ݴ��������Զ��жϵ�Ԫ����������  
			$objActSheet->setCellValue('A1',iconv('gbk', 'utf-8', '������'));  // �ַ�������  
			$objActSheet->setCellValue('B1',iconv('gbk', 'utf-8', '��Ʒ��'));  // �ַ�������  
			$objActSheet->setCellValue('C1',iconv('gbk', 'utf-8', '���'));
			$objActSheet->setCellValue('D1',iconv('gbk', 'utf-8', '����'));
			$objActSheet->setCellValue('E1',iconv('gbk', 'utf-8', '�ɹ���'));
			$objActSheet->setCellValue('F1',iconv('gbk', 'utf-8', '�ɹ���С��'));
			$objActSheet->setCellValue('G1',iconv('gbk', 'utf-8', '������'));
			$objActSheet->setCellValue('H1',iconv('gbk', 'utf-8', '������С��'));
			$objActSheet->setCellValue('I1',iconv('gbk', 'utf-8', '������'));
			$objActSheet->setCellValue('J1',iconv('gbk', 'utf-8', '������С��'));
			$objActSheet->setCellValue('K1',iconv('gbk', 'utf-8', '����PL'));
			$objActSheet->setCellValue('L1',iconv('gbk', 'utf-8', '����PLС��'));
			$objActSheet->setCellValueExplicit('A2',iconv('gbk', 'utf-8', $info['order']['order_sn']),PHPExcel_Cell_DataType::TYPE_STRING);
			foreach ($info['data']['goods_list'] as $k => $v)
			{
				$key1=1;
	    		$key2+=$key1;
	    		$k = $key2 +1;
				$objActSheet->setCellValue('B'.$k,iconv('gbk', 'utf-8', $v['goods_name']));  
				$objActSheet->setCellValue('C'.$k,iconv('gbk', 'utf-8', $v['specification']));
				$objActSheet->setCellValue('D'.$k,iconv('gbk', 'utf-8', $v['quantity']));
				$objActSheet->setCellValue('E'.$k,iconv('gbk', 'utf-8', $v['gprice']));
				$objActSheet->setCellValue('F'.$k,iconv('gbk', 'utf-8', $v['gprice']*$v['quantity']));
				$objActSheet->setCellValue('G'.$k,iconv('gbk', 'utf-8', $v['price']));
				$objActSheet->setCellValue('H'.$k,iconv('gbk', 'utf-8', $v['price']*$v['quantity']));
				$objActSheet->setCellValue('I'.$k,iconv('gbk', 'utf-8', $v['sprice']));
				$objActSheet->setCellValue('J'.$k,iconv('gbk', 'utf-8', $v['sprice']*$v['quantity']));
				$objActSheet->setCellValue('K'.$k,iconv('gbk', 'utf-8', $v['credit']));
				$objActSheet->setCellValue('L'.$k,iconv('gbk', 'utf-8', $v['credit']*$v['quantity']));		
			}
			$_num = count($info['data']['goods_list']);
			$row = $_num + 3;
			$row1 = $row + 1;
			$row2 = $row + 2;
			$row3 = $row + 3;
			$row4 = $row + 4;
			$count_num = $_num + 2;
			$objActSheet->setCellValue('D'.$row, "=SUM(D2:D".$count_num.")"); // ��ʽ 
			$objActSheet->setCellValue('F'.$row, "=SUM(F2:F".$count_num.")"); 
			$objActSheet->setCellValue('H'.$row, "=SUM(H2:H".$count_num.")"); 
			$objActSheet->setCellValue('J'.$row, "=SUM(J2:J".$count_num.")");
			$objActSheet->setCellValue('L'.$row, "=SUM(L2:L".$count_num.")");  
			$objActSheet->setCellValue('C'.$row, iconv('gbk', 'utf-8', '�ϼƣ�'));
			$objActSheet->setCellValue('C'.$row1, iconv('gbk', 'utf-8', '�����ܼۣ�'));
			$objActSheet->setCellValue('C'.$row2, iconv('gbk', 'utf-8', '�������ã�'));
			$objActSheet->setCellValue('C'.$row3, iconv('gbk', 'utf-8', 'Ƿ���'));
			$objActSheet->setCellValue('C'.$row4, iconv('gbk', 'utf-8', 'ʵ����'));
			$objActSheet->setCellValue('D'.$row1, iconv('gbk', 'utf-8', $info['order']['order_amount']));
			$objActSheet->setCellValue('D'.$row2, iconv('gbk', 'utf-8', $info['order']['shipping_fee']));
			$objActSheet->setCellValue('D'.$row3, iconv('gbk', 'utf-8', $info['order']['pay_amount']));
			$objActSheet->setCellValue('D'.$row4, iconv('gbk', 'utf-8', $info['order']['arrears_amount']));
			//��ʽָ����������  
			//$objActSheet->setCellValueExplicit('A5', '847475847857487584',PHPExcel_Cell_DataType::TYPE_STRING);  		  
			//�ϲ���Ԫ��  
			//$objActSheet->mergeCells('B1:C22');  
			//���뵥Ԫ��  
			//$objActSheet->unmergeCells('B1:C22');  
			//*************************************  
			//���õ�Ԫ����ʽ  
			//���ÿ��  
			//$objActSheet->getColumnDimension('B')->setAutoSize(true);  
			$objActSheet->getColumnDimension('A')->setWidth(20);
			$objActSheet->getColumnDimension('B')->setWidth(25);
			$objActSheet->getColumnDimension('C')->setWidth(20); 
			$objActSheet->getColumnDimension('D')->setWidth(12); 
			$objActSheet->getColumnDimension('E')->setWidth(12); 
			$objActSheet->getColumnDimension('F')->setWidth(15); 
			$objActSheet->getColumnDimension('G')->setWidth(12);
			$objActSheet->getColumnDimension('H')->setWidth(15);
			$objActSheet->getColumnDimension('I')->setWidth(12); 
			$objActSheet->getColumnDimension('J')->setWidth(12); 
			$objActSheet->getColumnDimension('K')->setWidth(12); 
			$objActSheet->getColumnDimension('L')->setWidth(12);      
			$objStyleA1 = $objActSheet->getStyle('A1');  
			$objStyleC = $objActSheet->getStyle('C'.$row); 

			 
			//���õ�Ԫ�����ݵ����ָ�ʽ��  
			//  
			//���ʹ���� PHPExcel_Writer_Excel5 ���������ݵĻ���  
			//������Ҫע�⣬�� PHPExcel_Style_NumberFormat ��� const ���������  
			//�����Զ����ʽ����ʽ�У��������Ͷ���������ʹ�ã�����setFormatCode  
			//Ϊ FORMAT_NUMBER ��ʱ��ʵ�ʳ�����Ч����û�аѸ�ʽ����Ϊ"0"����Ҫ  
			//�޸� PHPExcel_Writer_Excel5_Format ��Դ�����е� getXf($style) ������  
			//�� if ($this->_BIFF_version == 0x0500) { ����363�и�����ǰ������һ  
			//�д���:   
			//if($ifmt === '0') $ifmt = 1;  
			//  
			//���ø�ʽΪPHPExcel_Style_NumberFormat::FORMAT_NUMBER������ĳЩ������  
			//��ʹ�ÿ�ѧ������ʽ��ʾ���������� setAutoSize ����������ÿһ�е�����  
			//��������  
			$objFontA1 = $objStyleA1->getFont();  
			$objFontA1->setSize(14);  
			$objFontA1->setBold(true);
			$objFontA1->getColor()->setARGB('FF999999');  
			  
			//���ö��뷽ʽ  
			$objAlignA1 = $objStyleA1->getAlignment();  
			$objAlignA1->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
			$objAlignA1->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
			  
			//���ñ߿�  
			$objBorderA1 = $objStyleA1->getBorders();  
			$objBorderA1->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
			$objBorderA1->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
			$objBorderA1->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 
	
			$objBorderA1->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
			  
			//���������ɫ  
			$objFillA1 = $objStyleA1->getFill();  
			$objFillA1->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
			$objFillA1->getStartColor()->setARGB('FFEEEEEE');   
			//��ָ���ĵ�Ԫ������ʽ��Ϣ.  
			$objActSheet->duplicateStyle($objStyleA1, 'A1:L1');	    	
			$objFillC = $objStyleC->getFont();  
			$objFillC->getColor()->setARGB('#00CC00');
			$objActSheet->duplicateStyle($objStyleC, 'C'.$row.':'.'C'.$row4);
			$objActSheet->duplicateStyle($objStyleC, 'D'.$row.':'.'D'.$row4);
			$objActSheet->duplicateStyle($objStyleC, 'C'.$row.':'.'L'.$row);	
			//�������  
			$outputFileName = date('Y-m-d-His')."������Ʒ".".xls";  
			//���ļ�  
			//$objWriter->save($outputFileName);  
			//or  
			//�������  
			header("Content-Type: application/force-download");  
			header("Content-Type: application/octet-stream");  
			header("Content-Type: application/download");  
			header('Content-Disposition:inline;filename="'.$outputFileName.'"');  
			//header("Content-Transfer-Encoding: binary");  
			//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
			header("Pragma: no-cache");  
			$objWriter->save('php://output');  
	    }
	   /**
         * ��ȡ����ʹ�õ�֧����ʽ
         @author wscsky
         return array()
         */
        function _get_payment_type(){
            $cache_server =&cache_server();
            $payment_type = $cache_server->get('payment_type');
    	    if($payment_type === false){   
                $sql = "SELECT payment_id,payment_name from pa_payment where is_online =1";
                $payment_mod = &m("payment");
                $payment = $payment_mod->getAll($sql);
                $payment_type = array();
                foreach($payment as $v){
                    $payment_type[$v['payment_id']] = $v['payment_name'];                    
                }
                $payment_type[100] = '����֧��'; 
                $cache_server->set('payment_type',$payment_type,3600);
             }
            return $payment_type;
        }
	}

?>
