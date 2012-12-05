<?php
/* 派啦专柜 */
class Buy_myordersApp extends StoreadminbaseApp
{
	function index()
	{
		/* 获取订单列表 */
        $this->_get_orders();

        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_order'));
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
		
		$this->display('storeadmin.buymyorders.index.html');
	}
	public function show_myorder() {	
		$this->display('storeadmin.buymyorders.view.html');
	}
	public function show_myorderzhifu() {	
		$this->display('storeadmin.buymyorders.zhifu.html');
	}
	/**
     *    获取订单列表
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_orders()
    {
        $page = $this->_get_page(10);
        $model_order =& m('storeorder');
        !$_GET['type'] && $_GET['type'] = 'all_orders';
        $con = array(
            array(      //按订单状态搜索
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
            array(      //按下单时间搜索,起始时间
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),
            array(      //按下单时间搜索,结束时间
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'=> 'gmstr2time_end',
            ),
            array(      //按订单号
                'field' => 'order_sn',
            ),
        );
        $conditions = $this->_get_query_conditions($con);
        /* 查找订单 */
        $orders = $model_order->findAll(array(
            'conditions'    => "buyer_id=" . $this->visitor->get('user_id') . "{$conditions}",
            'fields'        => 'this.*',
            'count'         => true,
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
        	'include'		=> array('has_storeordergoods'),
        ));
        foreach ($orders as $key1 => $order)
        {
        	if(is_array($order['order_goods'])) {
	            foreach ($order['order_goods'] as $key2 => $goods)
	            {
	                empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
	            }
        	}
        }
        $page['item_count'] = $model_order->getCount();
        $this->assign('types', array('all'     => '所有定单',
                                     'pending' => Lang::get('pending_orders'),
                                     'submitted' => Lang::get('submitted_orders'),
                                     'accepted' => Lang::get('accepted_orders'),
                                     'shipped' => Lang::get('shipped_orders'),
                                     'finished' => Lang::get('finished_orders'),
                                     'canceled' => Lang::get('canceled_orders')));
        $this->assign('type', $_GET['type']);
        $this->assign('orders', $orders);
        $this->_format_page($page);
        $this->assign('page_info', $page);
    }
	/**
     *    查看订单详情
     *
     *    @author    Garbin
     *    @return    void
     */
    public function view()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $model_order =& m('storeorder');
        //$order_info  = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        $order_info = $model_order->get(array(
            'fields'        => "*, store_order_alias.add_time as order_add_time",
            'conditions'    => "order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'),
            'join'          => 'belongs_to_supply,',
            ));
        if (!$order_info)
        {
            $this->show_warning('no_such_order');

            return;
        }

        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_detail'));

        /* 调用相应的订单类型，获取整个订单详情数据 */
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_store_order_detail($order_id, $order_info);
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
            $order_detail['data']['goods_list'][$key]['amount'] = intval($goods['price']) * intval($goods['quantity']);
        }
        //处理总价
        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
		/*echo "<pre>";
        var_dump($order_detail);
        echo "</pre>"; exit();*/
        $this->display('storeadmin.buymyorders.view.html');
    }
    
	/**
     *    确认订单
     *
     *    @author    Garbin
     *    @return    void
     */
    function confirm_order()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        $model_order    =&  m('storeorder');
        /* 只有已发货的订单可以确认 */
        $order_info     = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id') . " AND status=" . ORDER_SHIPPED);
        if (empty($order_info))
        {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('buyer_store_order.confirm.html');
        }
        else
        {
            $model_order->edit($order_id, array('status' => ORDER_FINISHED, 'finished_time' => gmtime()));
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            /* 记录订单操作日志 */
            $order_log =& m('storeorderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_FINISHED),
                'remark'    => Lang::get('buyer_confirm'),
                'log_time'  => gmtime(),
            ));
            
            $model_ordergoods =& m('storeordergoods');
            $order_goods = $model_ordergoods->find("order_id={$order_id}");
       		
//            $model_member =& m('member');
//            $seller_info   = $model_member->get($order_info['seller_id']);
//            $mail = get_mail('toseller_finish_notify', array('order' => $order_info));
//            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_finished'),
                'actions'   => array('evaluate'),
            );

            /* 更新累计销售件数 */
            $model_goodsstatistics =& m('goodsstatistics');
            
            $store_goods_mod = & m('storegoods');
            foreach ($order_goods as $goods)
            {
                $model_goodsstatistics->edit($goods['goods_id'], "sales=sales+{$goods['quantity']}");
                //查看本店是否有本商品.  有的话添加数量,没有直接添加商品
                $goods_this = $store_goods_mod->get("goods_id = " . $goods['goods_id'] . " and spec_id = " . $goods['spec_id'] . " and store_id = " . $this->visitor->get('store_id'));
                if(!$goods_this) {
                	$store_goods_mod->add(array(
                		'goods_id' => intval($goods['goods_id']),
                		'store_id' => $this->visitor->get('store_id'),
                		'spec_id' => intval($goods['spec_id']),
                		'stock' => intval($goods['quantity']),
                	));
                } else {
                	$store_goods_mod->edit($goods_this['gs_id'],"stock = stock + " . $goods['quantity']);
                }
            }
            
            //计算本订单是否有欠款金额-----暂时停用此方法，后续启用
//            if ($order_info['arrears_amount'])
//            {
//            	changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['arrears_amount'],SUBTRACK_MONEY);
//		    	$log_info = array(
//			            	'user_id'	    => intval($order_info['buyer_id']),
//			            	'user_money'    => '-'.$order_info['arrears_amount'],
//			            	'change_desc'   => '店铺进货扣除账户订单欠款金额：'.$order_info['arrears_amount'].'元',
//			            	'order_id'      => $order_info['order_id'],
//				            'change_time'	=>	time(),
//			            	'change_type'	=> 	7,
//			            ); 
//			    add_account_log($log_info);
//		    }
            
            header('Location:index.php?app=buy_myorders');
        }
    }

    /**
     *    取消订单
     *
     *    @author    Garbin
     *    @return    void
     */
    function cancel_order()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        $model_order    =&  m('storeorder');
        /* 只有待付款的订单可以取消 */
        $order_info     = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id') . " AND status " . db_create_in(array(ORDER_PENDING, ORDER_SUBMITTED)));
        if (empty($order_info))
        {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('buy_myorders.cancel.html');
        }
        else
        {
            $model_order->edit($order_id, array('status' => ORDER_CANCELED));
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            $cancel_reason = (!empty($_POST['remark'])) ? $_POST['remark'] : $_POST['cancel_reason'];
            /* 记录订单操作日志 */
            $order_log =& m('storeorderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_CANCELED),
                'remark'    => $cancel_reason,
                'log_time'  => gmtime(),
            ));

            /* 发送给卖家订单取消通知 */
            $model_member =& m('member');
            $seller_info   = $model_member->get($order_info['seller_id']);
            $mail = get_mail('toseller_cancel_order_notify', array('order' => $order_info, 'reason' => $_POST['remark']));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_canceled'),
                'actions'   => array(), //取消订单后就不能做任何操作了
            );

            $this->pop_warning('ok');
            header("Location:index.php?app=buy_myorders");
        }

    }
}
	
