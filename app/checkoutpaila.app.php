<?php
/* 派啦专柜 */
class checkoutpailaApp extends StoreadminbaseApp
{
	function index()
	{
		$this->_get_paila_orders();  // 获取需要的定单
		
		
		$this->display('storeadmin.checkoutpaila.index.html');
	}
	/*
	 *  派拉商城线下完成订单
	 */
	public function quickCheckOutPaila() {
		$this->_get_quick_paila_orders();
		
		$this->display('storeadmin.checkoutpaila.quick.html');
	}
	
	/**
	 *  派拉商城结算清单
	 */
	public function settle_accounts_detailed() {
		//已结算定单, 当前月
		date_default_timezone_set('Asia/Shanghai');
		$date = getDate();
		$month = empty($_GET['month']) ? $date['mon'] : intval($_GET['month']);
		$year = empty($_GET['year']) ? $date['year'] : intval($_GET['year']);
		$fristDay = mktime(0, 0, 0, $month, 1, $year);
		$lastDay = $this->getLastDay($year,$month);
		
		$model_order =& m('order');
		$model_quick_order =& m('quickorder');
		//已结算收益金额
		$is_settle_income_inline = 0 ; //线上
		$is_settle_income_offline = 0 ;//线下
		$is_settle_amount = 0;
		//未结算收益金额
		$not_settle_income_inline = 0 ; //线上
		$not_settle_income_offline = 0 ;//线下
		$not_settle_amount = 0;
		
		//收到金额
		$is_settle_get_cash = 0;
		$not_settle_get_cash = 0;
		
		//已结算(线上)
		$conditions_1 = ' 1 = 1 AND status = 40 AND  assign_store_id = ' . $this->visitor->info['store_id'] . ' AND finished_time > ' . $fristDay . ' AND finished_time < ' . $lastDay . ' AND is_settle_accounts=1';
		//$conditions_1 = 'assign_store_id = ' . $this->visitor->info['store_id'] . '  AND add_time > ' . $fristDay . ' AND add_time < ' . $lastDay . ' AND is_settle_accounts=1';
		$orders_is_settle = $model_order->findAll(array(
            'conditions'    => $conditions_1,
            'count'         => true,
        ));
        foreach($orders_is_settle as $v) {
        	$is_settle_income_inline += $v['goods_amount'] * ONLINE_PAILA_INCOME_PERCENT;
        }
        $count_1 = $model_order->getCount();
        $this->assign('is_settle_income_inline',$is_settle_income_inline);

		//已结算(快捷支付)
		$conditions_3 = ' 1 = 1 AND status = 40 AND  seller_id = ' . $this->visitor->info['store_id'] . ' AND finished_time > ' . $fristDay . ' AND finished_time < ' . $lastDay . ' AND is_settle_accounts=1';
		$quick_orders_is_settle = $model_quick_order->findAll(array(
            'conditions'    => $conditions_3,
            'count'         => true,
        ));
		foreach($quick_orders_is_settle as $v) {
        	$is_settle_income_offline += $v['order_amount'] * OFFLINE_PAILA_INCOME_PERCENT;
        	$is_settle_get_cash += $v['pay_cash']; 
        }
		$count_3 = $model_quick_order->getCount();
		//已结算定单数
		$this->assign('is_settle_count',$count_1 + $count_3);
		$this->assign('is_settle_income_offline',$is_settle_income_offline);
		//未结算(线上)
		$conditions_2 = ' 1 = 1 AND status = 40 AND  assign_store_id = ' . $this->visitor->info['store_id'] . ' AND finished_time > ' . $fristDay . ' AND finished_time < ' . $lastDay . ' AND is_settle_accounts=0';
		//$conditions_2 = 'assign_store_id = ' . $this->visitor->info['store_id'] . '  AND add_time > ' . $fristDay . ' AND add_time < ' . $lastDay . ' AND is_settle_accounts=0';
		$orders_not_settle = $model_order->findAll(array(
            'conditions'    => $conditions_2,
            'count'         => true,
        ));
		foreach($orders_not_settle as $v) {
        	$not_settle_income_inline += $v['goods_amount'] * ONLINE_PAILA_INCOME_PERCENT;
        }
        $count_2 = $model_order->getCount();
        $this->assign('not_settle_income_inline',$not_settle_income_inline);

		//未结算(快捷支付)
		$conditions_4 = ' 1 = 1 AND status = 40 AND  seller_id = ' . $this->visitor->info['store_id'] . ' AND finished_time > ' . $fristDay . ' AND finished_time < ' . $lastDay . ' AND is_settle_accounts=0';
		$quick_orders_not_settle = $model_quick_order->findAll(array(
            'conditions'    => $conditions_4,
            'count'         => true,
        ));
		foreach($quick_orders_not_settle as $v) {
        	$not_settle_income_offline += $v['order_amount'] * OFFLINE_PAILA_INCOME_PERCENT;
        	$not_settle_get_cash += $v['pay_cash'];
        }
		$count_4 = $model_quick_order->getCount();
		//未结算定单数
		$this->assign('not_settle_count',$count_2 + $count_4);
		$this->assign('not_settle_income_offline',$not_settle_income_offline);
		
		$is_settle_amount = $is_settle_income_inline + $is_settle_income_offline;
		$not_settle_amount = $not_settle_income_inline + $not_settle_income_offline;
		
		$income_pay_is_settle = 0; //为零表示支出, 为1表示收入
		$income_pay_not_settle = 0; //为零表示支出, 为1表示收入
		if($is_settle_amount > $is_settle_get_cash) {//总收益大于实收,  
			$income_pay_is_settle = 1;
			$amount_is_settle = $is_settle_amount - $is_settle_get_cash;
		} else {
			$income_pay_is_settle = 0;
			$amount_is_settle = $is_settle_get_cash - $is_settle_amount;
		}
		if($not_settle_amount > $not_settle_get_cash) { //总收益大于实收,应收入
			$income_pay_not_settle = 1;
			$amount_not_settle = $not_settle_amount - $not_settle_get_cash;
		} else {
			$income_pay_not_settle = 0;
			$amount_not_settle = $not_settle_get_cash - $not_settle_amount;
		}
		
		$this->assign('income_pay_is_settle',$income_pay_is_settle);
		$this->assign('income_pay_not_settle',$income_pay_not_settle);
		$this->assign('amount_is_settle',$amount_is_settle);
		$this->assign('amount_not_settle',$amount_not_settle);
		$this->assign('year',$year);
		$this->assign('month',$month);
		$this->assign('is_settle_get_cash',$is_settle_get_cash);
		$this->assign('not_settle_get_cash',$not_settle_get_cash);
		$this->display("detailed_paila.index.html");
	}
	public function quickcheckoutview() {	
		$order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
		$quick_mod = & m('quickorder');
		$quick_order_view = $quick_mod->getRow("SELECT * FROM pa_quick_order qo LEFT JOIN pa_store s on qo.seller_id=s.store_id WHERE qo.order_id=".$order_id);
		$quick_goods_mod = & m('quickordergoods');
		$quick_goods = $quick_goods_mod->find(array('conditions' => 'order_id='.$order_id));
		/*echo '<pre>';
		var_dump($quick_order_view);
		var_dump($quick_goods);
		echo "</pre>";exit();*/
		$this->assign('quick_goods',$quick_goods);
		
		$this->assign('quick_order_view',$quick_order_view);
		$this->display('storeadmin.checkoutpaila.quick_view.html');
	}
	private function getLastDay($year,$month) {
		//判断是否为闰年
		if(intval($year)%4 == 0 && intval($year)%100 != 0 || intval($year)%400 == 0 ) {
			//是闰年
			if($month == 2) {
				$lastDay = mktime(23, 59, 59, $month, 29, $year);
				return $lastDay;
			} else {
				if($month < 8) {
					if(intval($month)%2 == 1) {
						$lastDay = mktime(23, 59, 59, $month, 31, $year);
						return $lastDay;
					} else {
						$lastDay = mktime(23, 59, 59 ,$month, 30, $year);
						return $lastDay;
					}
				} else {
					if(intval($month)%2 == 1) {
						$lastDay = mktime(23, 59, 59 ,$month, 30, $year);
						return $lastDay;
					} else {
						$lastDay = mktime(23, 59, 59, $month, 31, $year);
						return $lastDay;
					}
				}
			}
		} else {
			//不是闰年
			if($month == 2) {
				$lastDay = mktime(23, 59, 59, $month, 28, $year);
				return $lastDay;
			} else {
				if($month < 8) {
					if(intval($month)%2 == 1) {
						$lastDay = mktime(23, 59, 59, $month, 31, $year);
						return $lastDay;
					} else {
						$lastDay = mktime(23, 59, 59 ,$month, 30, $year);
						return $lastDay;
					}
				} else {
					if(intval($month)%2 == 1) {
						$lastDay = mktime(23, 59, 59 ,$month, 30, $year);
						return $lastDay;
					} else {
						$lastDay = mktime(23, 59, 59, $month, 31, $year);
						return $lastDay;
					}
				}
			}
		}
	}
	
	/**
     *    获取被指派的派拉订单列表(已完成)
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_paila_orders()
    {
    	date_default_timezone_set('Asia/Shanghai');
		$date = getDate();
		$month = empty($_GET['month']) ? $date['mon'] : intval($_GET['month']);
		$year = empty($_GET['year']) ? $date['year'] : intval($_GET['year']);
		$fristDay = mktime(0, 0, 0, $month, 1, $year);
		$lastDay = $this->getLastDay($year,$month);
		//$conditions = ' 1 = 1 AND area_type="pailamall" AND status = 40 AND  assign_store_id = ' . $this->visitor->info['store_id'] . ' AND finished_time > ' . $fristDay . ' AND finished_time < ' . $lastDay;
		$conditions = 'area_type="pailamall" AND assign_store_id = ' . $this->visitor->info['store_id'] . '  AND add_time > ' . $fristDay . ' AND add_time < ' . $lastDay;
        $page = $this->_get_page();
        $model_order =& m('order');

        // 团购订单
        if (!empty($_GET['group_id']) && intval($_GET['group_id']) > 0)
        {
            $groupbuy_mod = &m('groupbuy');
            $order_ids = $groupbuy_mod->get_order_ids(intval($_GET['group_id']));
            $order_ids && $conditions .= ' AND order_alias.order_id' . db_create_in($order_ids);
        }


        /* 查找订单 */
        $orders = $model_order->findAll(array(
            'conditions'    => $conditions,
            'count'         => true,
            'join'          => 'has_orderextm',
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
            'include'       =>  array(
                'has_ordergoods',       //取出商品
            ),
        ));
        $total_orders = $model_order->findAll(array(
            'conditions'    => $conditions,
            'join'          => 'has_orderextm',
            'include'       =>  array(
                'has_ordergoods',       //取出商品
            ),
        ));
        $total_income = 0; //总收益, 已结算
        $total_unincome = 0; //总收益,未结算
        foreach ($total_orders as $key2 => $torder) {
        	if($torder['is_settle_accounts'] == 1) {
        		$total_income += floatval($torder['goods_amount']) * ONLINE_PAILA_INCOME_PERCENT;
        	} else {
        		$total_unincome += floatval($torder['goods_amount']) * ONLINE_PAILA_INCOME_PERCENT;
        	}
        }
        
        foreach ($orders as $key1 => $order)
        {
        	if(is_array($order['order_goods'])) {
        	foreach ($order['order_goods'] as $key2 => $goods)
	            {
	                empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
	            }
        	}
        	$orders[$key1]['total_amount'] = floatval($order['order_amount']) + floatval($order['use_credit']);
        	$orders[$key1]['income'] = floatval($order['goods_amount']) * ONLINE_PAILA_INCOME_PERCENT;
        }
        $page['item_count'] = $model_order->getCount();
        $this->_format_page($page);
        $this->assign('total_income',$total_income);
        $this->assign('total_unincome',$total_unincome);
		$this->assign('year',$year);
		$this->assign('month',$month);
        $this->assign('orders', $orders);
        $this->assign('page_info', $page);
    }
	/**
     *    获取派拉快捷支付订单列表(已完成)
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_quick_paila_orders()
    {
    	date_default_timezone_set('Asia/Shanghai');
		$date = getDate();
		$month = empty($_GET['month']) ? $date['mon'] : intval($_GET['month']);
		$year = empty($_GET['year']) ? $date['year'] : intval($_GET['year']);
		$fristDay = mktime(0, 0, 0, $month, 1, $year);
		$lastDay = $this->getLastDay($year,$month);
		$conditions = ' 1 = 1 AND quick_order_type="pailaorder" AND status = 40 AND  seller_id = ' . $this->visitor->info['store_id'] . ' AND finished_time > ' . $fristDay . ' AND finished_time < ' . $lastDay;
		//$conditions = 'seller_id = ' . $this->visitor->info['store_id'] . '  AND add_time > ' . $fristDay . ' AND add_time < ' . $lastDay;
        $page = $this->_get_page();
        $model_order =& m('quickorder');


        /* 查找订单 */
        $orders = $model_order->findAll(array(
            'conditions'    => $conditions,
            'count'         => true,
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
            'include'       =>  array(
                'has_quickordergoods',       //取出商品
            ),
        ));
        $total_orders = $model_order->findAll(array(
            'conditions'    => $conditions,
            'include'       =>  array(
                'has_quickordergoods',       //取出商品
            ),
        ));
        $$total_income = 0; //总收益, 已结算
        $total_unincome = 0; //总收益,未结算
        foreach ($total_orders as $key2 => $torder) {
        	if($torder['is_settle_accounts'] == 1) {
        		$total_income += floatval($torder['order_amount']) * OFFLINE_PAILA_INCOME_PERCENT;
        	} else {
        		$total_unincome += floatval($torder['order_amount']) * OFFLINE_PAILA_INCOME_PERCENT;
        	}
        }
        
        foreach ($orders as $key1 => $order)
        {
        	if(is_array($order['order_goods'])) {
        	foreach ($order['order_goods'] as $key2 => $goods)
	            {
	                empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
	            }
        	}
        	$orders[$key1]['total_amount'] = floatval($order['order_amount']);
        	$orders[$key1]['income'] = floatval($order['order_amount']) * OFFLINE_PAILA_INCOME_PERCENT;
        }
        $page['item_count'] = $model_order->getCount();
        $this->_format_page($page);
        $this->assign('total_income',$total_income);
        $this->assign('total_unincome',$total_unincome);
		$this->assign('year',$year);
		$this->assign('month',$month);
        $this->assign('orders', $orders);
        $this->assign('page_info', $page);
    }
    
	
}

