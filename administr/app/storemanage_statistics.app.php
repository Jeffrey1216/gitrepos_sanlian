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
		//管理
		function index()
		{
			//$this->display('store_statistics.index.html');
			$this->sell();  //默认显示店铺进货单
		}
		//店铺进货
	function sell()
		{
			$search_options = array(
            's.store_name'   => Lang::get('store_name'),
            'so.order_sn'   => Lang::get('order_num'),
            'so.payment_name'   => Lang::get('pay_type'),
			'so.buyer_name' => Lang::get('会员'),
       		 );
        /* 默认搜索的字段是店铺名 */
        $field = 'seller_name';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        //按用户名,店铺名,支付方式名称进行搜索
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
			//分页条数
			$page=$this->_get_page(20);
			//统计总数
       		$page['item_count'] = $this->store_order_mod->getOne('select count(*) from pa_store_order so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id
       															 where '."$conditions".'  and so.status >= 20 and so.status <= 40');
       		
       		//查看所有数据
       		$goods = $this->store_order_mod->getAll('select so.order_id,so.order_sn,so.goods_amount,so.buyer_name,so.pay_amount,so.arrears_amount,so.status,s.store_type,so.pay_message,so.order_amount,s.store_name,so.payment_name,so.finished_time,so.add_time,so.pay_time,soe.shipping_fee from pa_store_order
       												 so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id where
       												  ' . "$conditions" . '  and so.status >= 20 and so.status <= 40 limit ' . $page['limit']);
			foreach ($goods as $k => $v) {
				$v['order_id'] = intval($v['order_id']);
				$sql = "select sum(sog.gprice * sog.quantity) as stock_price,sum(sog.sprice * sog.quantity) as league_price, \n"
					. " sum(sog.price * sog.quantity) as user_price from  pa_store_order_goods sog \n"
					. " where sog.order_id ={$v['order_id']}";
				$goods_all = $this->store_order_mod->getRow($sql);
				$goods[$k]['stock_price']  = $goods_all['stock_price']; //采购价
				$goods[$k]['user_price'] = $goods_all['user_price']; //批发价
				$goods[$k]['league_price'] = $goods_all['league_price']; //批发价
				$goods[$k]['pl_income']    = $goods[$k]['user_price'] - $goods[$k]['stock_price']; //公司收益
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
       				'0' => '直营店',
       				'1' => '加盟店',
       		));
       		$this->assign('status',array(
       				//'20' => '已付款,待发货',
	      			//'30' => '已付款,已发货',
	     			'40' => '交易成功',
       		));
       		$this->assign('goods',$goods);
       		$this->_format_page($page);
       		$this->assign('page_info',$page);
        	$this->assign('search_options', $search_options);
			$this->display('store_statistics.sell.html');
		}
		
		
		//订单查看
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
			//加盟店零售统计
	function collection()
		{
			$search_options = array(
            's.store_name'   => Lang::get('store_name'),
            'o.order_sn'   => Lang::get('order_num'),
			'o.buyer_name' 	 => Lang::get('会员'),
       		);
	        /* 默认搜索的字段是店铺名 */
	        $field = 'seller_name';
	        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
	        //按用户名,店铺名,支付方式名称进行搜索
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
	       		
	       		//分页条数
	       		$page = $this->_get_page(20);
	       		//统计总数
       			$page['item_count'] = $this->store_order_mod->getOne('select count(*) from pa_order o left join pa_store s on o.seller_id = s.store_id 
       																left join pa_order_extm oe on o.order_id = oe.order_id where '.$conditions);
       			$paytype = array(
	     			'1' => '现金',
	       			'2' => 'PL币',
	       			'3' => '现金+PL币',
	       			'4' => '余额+PL币',
                    '5' => '现金+余额',
                    '6' => '余额',
                    '7' => '现金+余额+PL币'
       			);
       			
       			
       			$orders = $this->store_order_mod->getAll('select o.order_id,o.order_sn,o.order_amount,o.goods_amount,o.cash,o.use_money,o.use_credit,(o.cash+o.use_money+o.use_credit) as showmany,o.get_credit,o.seller_name,o.buyer_name,o.status,s.store_type,oe.shipping_fee,o.payment_name,o.pay_time,o.pay_type from pa_order
       													 o left join pa_store s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id = 
       													 oe.order_id where '.$conditions .' limit '.$page['limit']);
               			
       			foreach($orders as $k=>$v)
	       		{
	       			
					$orders_all = $this->store_order_mod->getRow('select sum(og.gprice * og.quantity) as stock_price,sum(og.zprice * og.quantity) as league_price,
																sum(og.price * og.quantity) as user_price from pa_order o left join pa_order_goods og
																 on o.order_id = og.order_id where o.order_id ='.$v['order_id']);
					$orders[$k]['stock_price'] = $orders_all['stock_price']; //采购价
	   				$orders[$k]['league_price'] = $orders_all['league_price'];//批发价
	   				$orders[$k]['income'] = floatval($orders[$k]['get_credit']) * 0.5; //团购员收益
	   				$orders[$k]['send_pl'] = floatval($orders[$k]['income']) * 0.3; //团购员获得PL
	   				$orders[$k]['send_money'] = floatval($orders[$k]['income']) * 0.7;//团购员获得余额
	   				$orders[$k]['store_income'] = $orders[$k]['goods_amount'] - $orders[$k]['league_price'] - $orders[$k]['send_pl']  - $orders[$k]['send_money']; //店铺收益
	   				$orders[$k]['pl_income'] = $orders[$k]['league_price'] - $orders[$k]['stock_price'];//公司收益
                    $orders[$k]['pay_type_name'] = $paytype[$v['pay_type']];         
	       		}
	       		$total = $this->store_order_mod->getRow('select sum(o.goods_amount) as total,sum(oe.shipping_fee) as ship_fee,sum(o.get_credit) as gcredit from pa_order o left join pa_store s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id = oe.order_id where
       												  '.$conditions);
	       		$totals = $this->store_order_mod->getRow('select *,sum(og.gprice * og.quantity) as gprice_totals,sum(og.zprice * og.quantity) as zprice_totals from
	       												 pa_order o left join pa_store s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id
	       												  = oe.order_id left join pa_order_goods og on o.order_id = og.order_id where '.$conditions);
	       		$total['gprice_total'] = $totals['gprice_totals'];
	       		$total['zprice_total'] = $totals['zprice_totals'];
	       		$total['send_deduct'] = $total['gcredit'] * 0.5;//团购员提成
	       		$total['send_pl'] = $total['send_deduct'] * 0.3;//团购员获取PL
	       		$total['send_money'] = $total['send_deduct'] * 0.7;//团购总则获取余额
	       		
	       		$total['pl_income'] = $totals['zprice_totals'] - $totals['gprice_totals'];
	       		$this->assign('total',$total);
	       		$this->assign('store_type',array(
	       				'0' => '直营店',
	       				'1' => '加盟店',
	       		));
	       		$this->assign('status',array(
	     			'20' => '已付款,待发货',
	       			'30' => '已发货',
	       			'40' => '交易成功',
	       			'50' => '退款中',
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
		//店铺零售查看
		function collection_view()
		{
			$id = intval($_GET['id']);
			$order_info = $this->store_order_mod->getRow('select * from pa_order o left join pa_order_extm oe on o.order_id = oe.order_id where o.order_id ='.$id);
			$this->assign('order_info',$order_info);
			$goods_list = $this->store_order_goods_mod->getAll('select og.goods_name,og.order_id,og.specification,og.price,og.goods_image,og.quantity ,og.goods_id from pa_order_goods og left join pa_order o on og.order_id = o.order_id left join 
																pa_order_extm oe on o.order_id = oe.order_id  where o.order_id ='.$id);
			
			if(!$goods_list)
			{
				$this->show_warning('此订单不存在！');
				return ;
			}
			$this->assign('goods_list',$goods_list);
			$this->display('store_statistics.collection_view.html');
		}
		//店铺进货订单打印
		function orderprint()
		{
			$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			if(!$order_id)
			{
				$this->show_warning('没有此定单');
				return;
			}
			/*获取订单信息*/
			$order_info = $this->store_order_mod->getRow('select * from pa_store_order where order_id ='.$order_id);
			if(!$order_info)
			{
				$this->show_warning('没有此定单');
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
		//店铺零售订单打印
		function retail_orderprint()
		{
			$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			if(!$order_id)
			{
				$this->show_warning('没有此定单');
				return;
			}
			/*获取订单信息*/
			$order_info = $this->store_order_mod->getRow('select * from pa_order where order_id ='.$order_id);
			if(!$order_info)
			{
				$this->show_warning('没有此定单');
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
	     *    店铺进货订单管理
	     *
	     *    @author   lihuoliang
	     *    @param    none
	     *    @return    void
	     */
	    function store_order()
	    {
	        $search_options = array(
	            's.store_name'   => '店铺名称',
	            'so.payment_name'   => Lang::get('payment_name'),
	            'so.order_sn'   => Lang::get('order_sn'),
	        );
	        /* 默认搜索的字段是店铺名 */
	        $field = 's.store_name';
	        $status=empty($_GET['status']) ? 11 : intval($_GET['status']);
	        $payment_id=empty($_GET['payment_id']) ? '' : trim($_GET['payment_id']);
	
	        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
	        $conditions = '1=1';
	        $conditions .= $this->_get_query_conditions(array(array(
	                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
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
	        $page   =   $this->_get_page(20);    //获取分页信息
	        //更新排序
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
	        
	       

	        //统计总数
	       	$page['item_count'] = $this->_store_order_mod->getOne('select count(*) from pa_store_order so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id
	       															 where '."$conditions");
	        $this->_format_page($page);
	        $this->assign('filtered', $conditions != '1=1'? 1 : 0); //是否有查询条件
	        $this->assign('order_status_list', array(
	            ORDER_PENDING => Lang::get('待付款'),
	            ORDER_ACCEPTED => Lang::get('已付款,待发货'),
	            ORDER_SHIPPED => Lang::get('已发货'),
	            ORDER_FINISHED => Lang::get('交易成功'),
	            ORDER_REFUND => Lang::get('退款中'),
	            ORDER_REFUND_FINISH => Lang::get('退款完成'),
	            ORDER_CANCELED => Lang::get('交易取消'),
	        ));
	        $this->assign('op_status_list', array(
	            0 => Lang::get('未操作'),
	            1 => Lang::get('物流已更改物流费用'),
	            2 => Lang::get('店面管理已确认订单价格'),
	            3 => Lang::get('财务已确认收款信息'),
	            4 => Lang::get('物流已确认发货'),
	        ));
	        $this->assign('store_type',array(
	       				'0' => '直营店',
	       				'1' => '加盟店',
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
	        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
	        $this->assign('orders', $store_order_info);
	        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
	                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
	        Lang::load(lang_file('admin/store_order'));
	        $this->assign('app',APP);
	        $this->display('store_order.index.html');
	    }
	    //显示店铺进货订单详情
		function view()
	    {
	        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;      
	        /* 获取订单信息 */
	        $order_info = $this->_store_order_mod->get(array(
	            'conditions'    => $order_id,
	            'join'          => 'has_storeorderextm',
	            'include'       => array(
	                'has_storeordergoods',   //取出订单商品
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
		//财务审核订单信息---确定收款金额
	    function audit_store_order()
	    {
	    	$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
	    	/* 获取订单信息 */
	        $order_info = $this->_store_order_mod->get($order_id);
	       
	        if (!$order_info)
	        {
	            $this->show_warning('no_such_order');
	            return;
	        }
	        $data['remark']    = $_POST['remark'];
	        $data['op_status'] = 3;
	        
			$this->_store_order_mod->edit($order_id,$data);
			$this->show_message('审核订单信息成功！',"返回列表",'index.php?app=store_statistics&act=store_order');
	    }
		function GoodsinfoExcel($info)
	    {
	    	import(PHPExcel);
	    	$objExcel = new PHPExcel();
	    	$objWriter = new PHPExcel_Writer_Excel5($objExcel);
			//设置文档基本属性  
			$objProps = $objExcel->getProperties();  
			$objProps->setCreator("小 鱼");  
			$objProps->setLastModifiedBy("小 鱼");  
			$objProps->setTitle("新建文档");  
			$objProps->setSubject("Office XLS Test Document, Demo");  
			$objProps->setDescription("Test document, generated by PHPExcel.");  
			$objProps->setKeywords("office excel PHPExcel");  
			$objProps->setCategory("Test");
			//*************************************  
			//设置当前的sheet索引，用于后续的内容操作。  
			//一般只有在使用多个sheet的时候才需要显示调用。  
			//缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0  
			$objExcel->setActiveSheetIndex(0);
			$objActSheet = $objExcel->getActiveSheet();  			  
			//设置当前活动sheet的名称  
			$objActSheet->setTitle('new');
			//*************************************  
			//设置单元格内容 
			//由PHPExcel根据传入内容自动判断单元格内容类型  
			$objActSheet->setCellValue('A1',iconv('gbk', 'utf-8', '订单号'));  // 字符串内容  
			$objActSheet->setCellValue('B1',iconv('gbk', 'utf-8', '商品名'));  // 字符串内容  
			$objActSheet->setCellValue('C1',iconv('gbk', 'utf-8', '规格'));
			$objActSheet->setCellValue('D1',iconv('gbk', 'utf-8', '数量'));
			$objActSheet->setCellValue('E1',iconv('gbk', 'utf-8', '采购价'));
			$objActSheet->setCellValue('F1',iconv('gbk', 'utf-8', '采购价小计'));
			$objActSheet->setCellValue('G1',iconv('gbk', 'utf-8', '批发价'));
			$objActSheet->setCellValue('H1',iconv('gbk', 'utf-8', '批发价小计'));
			$objActSheet->setCellValue('I1',iconv('gbk', 'utf-8', '派啦价'));
			$objActSheet->setCellValue('J1',iconv('gbk', 'utf-8', '派啦价小计'));
			$objActSheet->setCellValue('K1',iconv('gbk', 'utf-8', '赠送PL'));
			$objActSheet->setCellValue('L1',iconv('gbk', 'utf-8', '赠送PL小计'));
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
			$objActSheet->setCellValue('D'.$row, "=SUM(D2:D".$count_num.")"); // 公式 
			$objActSheet->setCellValue('F'.$row, "=SUM(F2:F".$count_num.")"); 
			$objActSheet->setCellValue('H'.$row, "=SUM(H2:H".$count_num.")"); 
			$objActSheet->setCellValue('J'.$row, "=SUM(J2:J".$count_num.")");
			$objActSheet->setCellValue('L'.$row, "=SUM(L2:L".$count_num.")");  
			$objActSheet->setCellValue('C'.$row, iconv('gbk', 'utf-8', '合计：'));
			$objActSheet->setCellValue('C'.$row1, iconv('gbk', 'utf-8', '订单总价：'));
			$objActSheet->setCellValue('C'.$row2, iconv('gbk', 'utf-8', '物流费用：'));
			$objActSheet->setCellValue('C'.$row3, iconv('gbk', 'utf-8', '欠款金额：'));
			$objActSheet->setCellValue('C'.$row4, iconv('gbk', 'utf-8', '实付金额：'));
			$objActSheet->setCellValue('D'.$row1, iconv('gbk', 'utf-8', $info['order']['order_amount']));
			$objActSheet->setCellValue('D'.$row2, iconv('gbk', 'utf-8', $info['order']['shipping_fee']));
			$objActSheet->setCellValue('D'.$row3, iconv('gbk', 'utf-8', $info['order']['pay_amount']));
			$objActSheet->setCellValue('D'.$row4, iconv('gbk', 'utf-8', $info['order']['arrears_amount']));
			//显式指定内容类型  
			//$objActSheet->setCellValueExplicit('A5', '847475847857487584',PHPExcel_Cell_DataType::TYPE_STRING);  		  
			//合并单元格  
			//$objActSheet->mergeCells('B1:C22');  
			//分离单元格  
			//$objActSheet->unmergeCells('B1:C22');  
			//*************************************  
			//设置单元格样式  
			//设置宽度  
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

			 
			//设置单元格内容的数字格式。  
			//  
			//如果使用了 PHPExcel_Writer_Excel5 来生成内容的话，  
			//这里需要注意，在 PHPExcel_Style_NumberFormat 类的 const 变量定义的  
			//各种自定义格式化方式中，其它类型都可以正常使用，但当setFormatCode  
			//为 FORMAT_NUMBER 的时候，实际出来的效果被没有把格式设置为"0"。需要  
			//修改 PHPExcel_Writer_Excel5_Format 类源代码中的 getXf($style) 方法，  
			//在 if ($this->_BIFF_version == 0x0500) { （第363行附近）前面增加一  
			//行代码:   
			//if($ifmt === '0') $ifmt = 1;  
			//  
			//设置格式为PHPExcel_Style_NumberFormat::FORMAT_NUMBER，避免某些大数字  
			//被使用科学记数方式显示，配合下面的 setAutoSize 方法可以让每一行的内容  
			//设置字体  
			$objFontA1 = $objStyleA1->getFont();  
			$objFontA1->setSize(14);  
			$objFontA1->setBold(true);
			$objFontA1->getColor()->setARGB('FF999999');  
			  
			//设置对齐方式  
			$objAlignA1 = $objStyleA1->getAlignment();  
			$objAlignA1->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
			$objAlignA1->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
			  
			//设置边框  
			$objBorderA1 = $objStyleA1->getBorders();  
			$objBorderA1->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
			$objBorderA1->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
			$objBorderA1->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 
	
			$objBorderA1->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
			  
			//设置填充颜色  
			$objFillA1 = $objStyleA1->getFill();  
			$objFillA1->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
			$objFillA1->getStartColor()->setARGB('FFEEEEEE');   
			//从指定的单元格复制样式信息.  
			$objActSheet->duplicateStyle($objStyleA1, 'A1:L1');	    	
			$objFillC = $objStyleC->getFont();  
			$objFillC->getColor()->setARGB('#00CC00');
			$objActSheet->duplicateStyle($objStyleC, 'C'.$row.':'.'C'.$row4);
			$objActSheet->duplicateStyle($objStyleC, 'D'.$row.':'.'D'.$row4);
			$objActSheet->duplicateStyle($objStyleC, 'C'.$row.':'.'L'.$row);	
			//输出内容  
			$outputFileName = date('Y-m-d-His')."订单商品".".xls";  
			//到文件  
			//$objWriter->save($outputFileName);  
			//or  
			//到浏览器  
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
         * 读取正在使用的支付方式
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
                $payment_type[100] = '线下支付'; 
                $cache_server->set('payment_type',$payment_type,3600);
             }
            return $payment_type;
        }
	}

?>
