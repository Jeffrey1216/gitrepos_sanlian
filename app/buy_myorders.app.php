<?php
/* ����ר�� */
class Buy_myordersApp extends StoreadminbaseApp
{
	function index()
	{
		/* ��ȡ�����б� */
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
     *    ��ȡ�����б�
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
            array(      //������״̬����
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
            array(      //���µ�ʱ������,��ʼʱ��
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),
            array(      //���µ�ʱ������,����ʱ��
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'=> 'gmstr2time_end',
            ),
            array(      //��������
                'field' => 'order_sn',
            ),
        );
        $conditions = $this->_get_query_conditions($con);
        /* ���Ҷ��� */
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
        $this->assign('types', array('all'     => '���ж���',
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
     *    �鿴��������
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

        /* ������Ӧ�Ķ������ͣ���ȡ���������������� */
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_store_order_detail($order_id, $order_info);
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
            $order_detail['data']['goods_list'][$key]['amount'] = intval($goods['price']) * intval($goods['quantity']);
        }
        //�����ܼ�
        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
		/*echo "<pre>";
        var_dump($order_detail);
        echo "</pre>"; exit();*/
        $this->display('storeadmin.buymyorders.view.html');
    }
    
	/**
     *    ȷ�϶���
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
        /* ֻ���ѷ����Ķ�������ȷ�� */
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

            /* ��¼����������־ */
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

            /* �����ۼ����ۼ��� */
            $model_goodsstatistics =& m('goodsstatistics');
            
            $store_goods_mod = & m('storegoods');
            foreach ($order_goods as $goods)
            {
                $model_goodsstatistics->edit($goods['goods_id'], "sales=sales+{$goods['quantity']}");
                //�鿴�����Ƿ��б���Ʒ.  �еĻ��������,û��ֱ�������Ʒ
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
            
            //���㱾�����Ƿ���Ƿ����-----��ʱͣ�ô˷�������������
//            if ($order_info['arrears_amount'])
//            {
//            	changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['arrears_amount'],SUBTRACK_MONEY);
//		    	$log_info = array(
//			            	'user_id'	    => intval($order_info['buyer_id']),
//			            	'user_money'    => '-'.$order_info['arrears_amount'],
//			            	'change_desc'   => '���̽����۳��˻�����Ƿ���'.$order_info['arrears_amount'].'Ԫ',
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
     *    ȡ������
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
        /* ֻ�д�����Ķ�������ȡ�� */
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
            /* ��¼����������־ */
            $order_log =& m('storeorderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_CANCELED),
                'remark'    => $cancel_reason,
                'log_time'  => gmtime(),
            ));

            /* ���͸����Ҷ���ȡ��֪ͨ */
            $model_member =& m('member');
            $seller_info   = $model_member->get($order_info['seller_id']);
            $mail = get_mail('toseller_cancel_order_notify', array('order' => $order_info, 'reason' => $_POST['remark']));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_canceled'),
                'actions'   => array(), //ȡ��������Ͳ������κβ�����
            );

            $this->pop_warning('ok');
            header("Location:index.php?app=buy_myorders");
        }

    }
}
	
