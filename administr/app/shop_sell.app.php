<?php
	class Shop_sellApp extends BackendApp
	{
		var $order_mod;
		var $order_goods_mod;
		var $order_extm_mod;
		var $store_order_mod;
		function __construct()
		{
			$this->Shop_sellApp();
		}
		function Shop_sellApp()
		{
			parent::__construct();
			$this->order_mod = &m('order');
			$this->store_order_mod=& m('storeorder');
			$this->order_goods_mod = &m('ordergoods');
			$this->order_extm_mod = &m('orderextm');
		}
		//����
		function index()
		{
			//$this->display('shop_sell.index.html');
				$this->ready(); //Ĭ����ʾ�ֽ𶩵�
		}
		//�ֽ𶩵�
		function ready()
		{
			$search_options = array(
            'o.order_sn'   => Lang::get('�������'),
            'o.buyer_name'   => Lang::get('��Ա'),
	        );
			/* Ĭ���������ֶ��ǵ����� */
	        $field = 'seller_name';
	        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
	        //���û���,������,֧����ʽ���ƽ�������
	        $conditions = 'status>=20 and status<=50 and s.store_id = '.STORE_ID;
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
	                'field' => 'oe.shipping_fee',
	                'name'  => 'order_amount_to',
	                'equal' => '<=',
	                'type'  => 'numeric',
	            ),           
	       		));
                
                //֧����������
                if(isset($_GET['paytype']))
                {
                 switch($_GET['paytype']){
                    case 1: //�ֽ�
                        $conditions .= " and o.pay_type in(1,3,5,7)";
                        $this->assign("total_name","�ֽ��ܼ�");
                        $total_field = "o.cash";                     
                        break;
                    case 2: //PL��
                        $conditions .= " and o.pay_type in(2,3,4,7)";
                        $this->assign("total_name","PL���ܼ�");
                        $total_field = "o.use_credit";
                        break;
                    case 3: //���
                        $conditions .= " and o.pay_type in(4,5,6,7)";
                        $this->assign("total_name","����ܼ�");
                        $total_field = "o.use_money";
                        break;
                    default:
                        $this->assign("total_name","�����ܼ�");
                        $total_field = "o.goods_amount";                        
                        break;                    
                    } 
  
                    }
                else{
                        $this->assign("total_name","�����ܼ�");
                        $total_field = "o.goods_amount";    
                 }
                if(!empty($_GET['op_status']))
                {
					$conditions .= " and o.op_status = ".$_GET['op_status'];
                }
	       		//��ҳ����
	       		$page = $this->_get_page(20);
	       		//ͳ������
       			$page['item_count'] = $this->store_order_mod->getOne('select count(*) from pa_order o left join pa_store s on o.seller_id = s.store_id 
       																left join pa_order_extm oe on o.order_id = oe.order_id where '.$conditions);
       			$paytype = array(
	     			'1' => '�ֽ�',
	       			'2' => 'PL��',
	       			//'3' => '�ֽ�+PL��',
	       			//'4' => '���+PL��',
                    //'5' => '�ֽ�+���',
                    '3' => '���',
                    //'7' => '�ֽ�+���+PL��'
       			);
       			$op_status = array(
       				'1' => '��������ˣ����������',
       				'2'	=> '������ͨ�����'
       			);       			       			
       			$orders = $this->store_order_mod->getAll('select o.op_status,o.order_id,o.order_sn,o.order_amount,o.goods_amount,o.cash,o.use_money,o.use_credit,(o.cash+o.use_money+o.use_credit) as showmany,o.get_credit,o.seller_name,o.buyer_name,o.status,s.store_type,oe.shipping_fee,o.payment_name,o.pay_time,o.pay_type from pa_order
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
	       		

                $total = $this->store_order_mod->getRow('select sum('.$total_field.') as total,sum(oe.shipping_fee) as ship_fee,sum(o.get_credit) as gcredit from pa_order o left join pa_store s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id = oe.order_id where
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
	       		$this->assign('op_status',$op_status);
	       		$this->assign('goods_collect',$orders_collect);
	        	$this->assign('search_options', $search_options);
				$this->display('shop_sell.ready.html');
		}
		//�����Ҷ���	
		function pl_order()
		{
			$search_options = array(
            'o.order_sn'   => Lang::get('�������'),
            'o.buyer_name'   => Lang::get('��Ա'),
	        );
	        /* Ĭ���������ֶ��ǵ����� */
	        $field = 'seller_name';
	        $status=empty($_GET['status']) ? 11 : intval($_GET['status']);
	        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
	        $conditions = '1=1';
	        $conditions .= $this->_get_query_conditions(array(array(
	                'field' => $field,       //���û���,������,֧����ʽ���ƽ�������
	                'equal' => 'LIKE',
	                'name'  => 'search_name',
	            ),array(
	                'field' => 'type',
	                'equal' => '=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'status',
	                'equal' => '=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'o.pay_time',
	                'name'  => 'pay_time_from',
	                'equal' => '>=',
	                'handler'=> 'gmstr2time',
	            ),array(
	                'field' => 'o.pay_time',
	                'name'  => 'pay_time_to',
	                'equal' => '<=',
	                'handler'   => 'gmstr2time_end',
	            ),array(
	                'field' => 'oe.shipping_fee',
	                'name'  => 'order_amount_from',
	                'equal' => '>=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'oe.shipping_fee',
	                'name'  => 'order_amount_to',
	                'equal' => '<=',
	                'type'  => 'numeric',
	            ),
	        ));
	        //��ȡ��ҳ��Ϣ
	        $page = $this->_get_page(20);
	        $page['item_count'] = $this->order_mod->getOne('select count(*) from pa_order o left join pa_store s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id = oe.order_id
	        												where o.pay_type = 2 and '.$conditions.' and s.store_id ='.STORE_ID);
	        $order = $this->order_mod->getAll('select o.order_id,o.order_sn,o.order_amount,o.goods_amount,o.get_credit,oe.shipping_fee,o.type,o.status,o.buyer_name,o.pay_time from pa_order o left join pa_store
	        									 s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id = oe.order_id where o.pay_type = 2 and '.$conditions.'
	        									  and s.store_id ='.STORE_ID .' limit '.$page['limit']);
	        foreach($order as $k=>$v)
	        {
	        	$v['order_id'] = intval($v['order_id']);
	        	$goods_all = $this->order_mod->getRow('select sum(og.gprice * og.quantity) as stock_price,sum(og.zprice * og.quantity) as league_price,sum(og.price * og.quantity)
	        										 as user_price from pa_order o left join pa_order_goods og on o.order_id = og.order_id  where o.seller_id ='.STORE_ID .' and o.order_id='.$v['order_id'] .' and pay_type = 2 group by o.order_sn ');
				$order[$k]['stock_price'] = $goods_all['stock_price']; //�ɹ���
   				$order[$k]['user_price'] = $goods_all['user_price'];//��������
				$order[$k]['income'] = floatval($v['get_credit'] * 0.5);//�Ź�Ա���
   				$order[$k]['send_pl'] = $order[$k]['income'] * 0.3; //�Ź�Ա���PL
   				$order[$k]['send_money'] = $order[$k]['income'] * 0.7;//�Ź�Ա������
   				$order[$k]['pl_income'] = $order[$k]['user_price'] -$order[$k]['stock_price'];//��˾����				
	        }
	        $total = $this->order_mod->getRow('select sum(o.goods_amount) as total,sum(oe.shipping_fee) as ship_fee,sum(o.get_credit) as gcredit from pa_order o left join pa_store s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id = oe.order_id where
       												  o.pay_type = 2 and '.$conditions .' and s.store_id ='.STORE_ID);
       		$totals = $this->order_mod->getRow('select *,sum(og.gprice * og.quantity) as gprice_totals,sum(og.zprice * og.quantity) as zprice_totals,sum(og.price * og.quantity) as price_totals from
       												 pa_order o left join pa_store s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id
       												  = oe.order_id left join pa_order_goods og on o.order_id = og.order_id where o.pay_type = 2 and '.$conditions.' and s.store_id ='.STORE_ID);
       		$total['gprice_total'] = $totals['gprice_totals'];
       		$total['zprice_total'] = $totals['zprice_totals'];
       		$total['send_deduct'] = $total['gcredit'] * 0.5;//�Ź�Ա���
       		$total['send_pl'] = $total['send_deduct'] * 0.3;//�Ź�Ա��ȡPL
       		$total['send_money'] = $total['send_deduct'] * 0.7;//�Ź������ȡ���
       		
       		$total['pl_income'] = $totals['price_totals'] - $totals['gprice_totals'];
       		$this->assign('total',$total);
	        $this->assign('type',array(
	        		'0' => '���϶���',
	        		'1' => '���¶���',
	        ));
	        $this->assign('status',array(
       				'11' => '������',
	      			'12' => '�ȴ�ȷ�ϸ���',
	     			'20' => '�Ѹ���,������',
	       			'30' => '�ѷ���',
	       			'40' => '���׳ɹ�',
	       			'50' => '�˿���',
	       			'60' => '�˿����',
	        		'0' => '��ȡ��',
       			));
	        $this->assign('order',$order);
	        $this->assign('goods_collect',$goods_collect);
	        $this->assign('search_options',$search_options);
	        $this->_format_page($page);
	        $this->assign('page_info',$page);
			$this->display('shop_sell.plorder.html');
		}
		//����
		function cashier()
		{
			$id = isset($_GET['id']) ? trim($_GET['id']) : '';
			if(!id)
			{
				$this->show_warning('û�д˶���!');
				return;
			}
			$ids = explode(',',$id);
			$data = array();
			$data['order_id'] = $ids;
			$data['op_status'] = 2;
			if(!$this->order_mod->edit($ids,$data))
			{
				$this->show_warning($this->order_mod->get_error());
				return;
			}else{
				$this->show_message('ȷ�ϳɹ�!',
				'back_list',	'index.php?app=shop_sell'
				);	
			}	
		}
		//�����Ҷ����鿴
		function plorder_view()
		{
			$id = intval($_GET['id']);
			$order_info = $this->order_mod->getRow('select *,o.order_id from pa_order o left join pa_order_extm oe on o.order_id = oe.order_id where o.seller_id = '.STORE_ID.' AND o.order_id ='.$id );
			if(!$order_info)
			{
				$this->show_warning('�˶���������!');
				return;
			}
			$this->assign('order_info',$order_info);
			$goods_list = $this->order_goods_mod->getAll('select og.order_id,og.goods_name,og.order_id,og.specification,og.price,og.goods_image,og.quantity ,og.goods_id from pa_order_goods og left join pa_order o on og.order_id = o.order_id left join 
																pa_order_extm oe on o.order_id = oe.order_id  where o.seller_id = '.STORE_ID.' AND o.order_id ='.$id);
			$this->assign('goods_list',$goods_list);
			$this->display('shop_sell.plorder_view.html');
		}
		//���̶�����ӡ
		function orderprint()
		{
			$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			if(!$order_id)
			{
				$this->show_warning('û�д˶���');
				return;
			}
			/*��ȡ������Ϣ*/
			$order_info = $this->order_mod->getRow('select * from pa_order where order_id ='.$order_id);
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
         * ��ȡ����ʹ�õ�֧����ʽ
         @author wscsky
         return array()
         */
        function _get_payment_type(){
            $cache_server =&cache_server();
            $payment_type = $cache_server->get('payment_type');            
    	    if($payment_type === false){   
                $sql = "SELECT payment_id,payment_name from pa_payment where is_online =1 and payment_id <>5";
                $payment_mod = &m("payment");
                $payment = $payment_mod->getAll($sql);
                $payment_type = array();
                foreach($payment as $v){
                    $payment_type[$v['payment_id']] = $v['payment_name'];                    
                }
                $cache_server->set('payment_type',$payment_type,3600);
             }
            return $payment_type;
        }
	}
?>
