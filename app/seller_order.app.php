<?php

/**
 *    ��ҵĶ������������
 *
 *    @author    Garbin
 *    @usage    none
 */
class Seller_orderApp extends StoreadminbaseApp
{
    function index()
    {
        /* ��ȡ�����б� */
      	$this->_get_orders();	
        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('order_manage'), 'index.php?app=seller_order',
                         LANG::get('order_list'));

        /* ��ǰ�û����Ĳ˵� */
        $type = (isset($_GET['type']) && $_GET['type'] != '') ? trim($_GET['type']) : 'all_orders';
        $this->_curitem('order_manage');
        $this->_curmenu($type);
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_manage'));
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
        /* ��ʾ�����б� */
        $this->display('storeadmin.sellerorder.index.html');
    }
    	
    /**
     * ��ָ�ɵ��������������
     */
	function assign_paila_order()
    {
        /* ��ȡ�����б� */
        $this->_get_paila_orders();

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('order_manage'), 'index.php?app=seller_order',
                         LANG::get('order_list'));

        /* ��ǰ�û����Ĳ˵� */
        $type = (isset($_GET['type']) && $_GET['type'] != '') ? trim($_GET['type']) : 'all_orders';
        $this->_curitem('order_manage');
        $this->_curmenu($type);
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_manage'));
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
        /* ��ʾ�����б� */
        $this->display('storeadmin.sellerorder.paila_index.html');
    }

    /**
     *    �鿴��������
     *
     *    @author    Garbin
     *    @return    void
     */
    function view()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $model_order =& m('order');
        $order_info  = $model_order->findAll(array(
            'conditions'    => "order_alias.order_id={$order_id} AND seller_id=" . $this->visitor->get('manage_store'),
            'join'          => 'has_orderextm',
        ));
        $order_info = current($order_info);
		if (empty($order_info['extension']))
		{
			$order_info['extension'] = normal ;	
		}
        if (!$order_info)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* �Ź���Ϣ */
        if ($order_info['extension'] == 'groupbuy')
        {
            $groupbuy_mod = &m('groupbuy');
            $group = $groupbuy_mod->get(array(
                'join' => 'be_join',
                'conditions' => 'order_id=' . $order_id,
                'fields' => 'gb.group_id',
            ));
            $this->assign('group_id',$group['group_id']);
        }
        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('order_manage'), 'index.php?app=seller_order',
                         LANG::get('view_order'));

        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('order_manage');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('detail'));
        /* ������Ӧ�Ķ������ͣ���ȡ���������������� */

        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        
        $spec_ids = array();
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
            $spec_ids[] = $goods['spec_id'];

        }
		
//        /* ������µ���Ӧ�Ļ��� */
//        $model_spec =& m('goodsspec');
//        $spec_info = $model_spec->find(array(
//            'conditions'    => $spec_ids,
//            'fields'        => 'sku',
//        ));
//        foreach ($order_detail['data']['goods_list'] as $key => $goods)
//        {
//            $order_detail['data']['goods_list'][$key]['sku'] = $spec_info[$goods['spec_id']]['sku'];
//        }

        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        $this->assign('ty',$_GET['type']);
        $this->display('storeadmin.sellerorder.view.html');
    }
    /**
     *    �յ�����
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function received_pay()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(ORDER_PENDING);
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('seller_order.received_pay.html');
        }
        else
        {
            $model_order    =&  m('order');
            $model_order->change_stock('-', $order_id);
	        if ($order_info['use_credit']>0)
	        {
	        	$data['pay_type'] = 3;
	        }else
	        {
	        	$data['pay_type'] = 1;
	        }
	        $data['cash']   = $order_info['order_amount'] - $order_info['use_credit'];
	        $data['status'] = ORDER_ACCEPTED;
	        $data['pay_time'] = gmtime();
	        $data['payment_id'] = 100;
	        $data['payment_name'] = '����֧��';
	        $data['payment_code'] = 'storepay';
	        
            $model_order->edit(intval($order_id), $data);
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            #TODO ���ʼ�֪ͨ
            /* ��¼����������־ */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_ACCEPTED),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));

            $this->pop_warning('ok');
        }

    }

    /**
     *    ��������Ķ�����ȷ�ϲ���
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function confirm_order()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(ORDER_SUBMITTED);
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('seller_order.confirm.html');
        }
        else
        {
            $model_order    =&  m('order');
            $model_order->edit($order_id, array('status' => ORDER_ACCEPTED));
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            /* ��¼����������־ */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_ACCEPTED),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));

            /* ���͸�����ʼ���������ȷ�ϣ��ȴ����ŷ��� */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $mail = get_mail('tobuyer_confirm_cod_order_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_accepted'),
                'actions'   => array(
                    'cancel',
                    'shipped'
                ), //����ȡ�����Է���
            );

            $this->pop_warning('ok');;
        }
    }

    /**
     *    ��������
     *
     *    @author    Garbin
     *    @return    void
     */
    function adjust_fee()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(array(ORDER_SUBMITTED, ORDER_PENDING));
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        $model_order    =&  m('order');
        $model_orderextm =& m('orderextm');
        $shipping_info   = $model_orderextm->get($order_id);
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->assign('shipping', $shipping_info);
            $this->display('seller_order.adjust_fee.html');
        }
        else
        {
            /* ���ͷ��� */
            $shipping_fee = isset($_POST['shipping_fee']) ? abs(floatval($_POST['shipping_fee'])) : 0;
            /* �ۿ۽�� */
            $goods_amount     = isset($_POST['goods_amount'])     ? abs(floatval($_POST['goods_amount'])) : 0;
            /* ����ʵ���ܽ�� */
            $order_amount = round($goods_amount + $shipping_fee, 2);
            if ($order_amount <= 0)
            {
                /* ����Ʒ�ܼۣ����ͷ��ÿ۶��ۿ�С�ڵ���0������һ����Ч������ */
                $this->pop_warning('invalid_fee');

                return;
            }
            $data = array(
                'goods_amount'  => $goods_amount,    //�޸���Ʒ�ܼ�
                'order_amount'  => $order_amount,     //�޸Ķ���ʵ���ܽ��
                'pay_alter' => 1    //֧�����
            );

            if ($shipping_fee != $shipping_info['shipping_fee'])
            {
                /* ���˷��б䣬���޸��˷� */

                $model_extm =& m('orderextm');
                $model_extm->edit($order_id, array('shipping_fee' => $shipping_fee));
            }
            $model_order->edit($order_id, $data);

            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }
            /* ��¼����������־ */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status($order_info['status']),
                'remark'    => Lang::get('adjust_fee'),
                'log_time'  => gmtime(),
            ));

            /* ���͸�����ʼ�֪ͨ����������Ѹı䣬�ȴ����� */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $mail = get_mail('tobuyer_adjust_fee_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'order_amount'  => price_format($order_amount),
            );

            $this->pop_warning('ok');
        }
    }

    /**
     *    �������Ķ�������
     *
     *    @author    Garbin
     *    @return    void
     */
    function shipped()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(array(ORDER_ACCEPTED, ORDER_SHIPPED));
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        $model_order    =&  m('order');
        if (!IS_POST)
        {
            /* ��ʾ������ */
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('seller_order.shipped.html');
        }
        else
        {
            if (!$_POST['invoice_no'])
            {
                $this->pop_warning('invoice_no_empty');

                return;
            }
            $edit_data = array('status' => ORDER_SHIPPED, 'ship_no' => $_POST['invoice_no'],'ship_reason'=> $_POST['remark']);
            $is_edit = true;
            if (empty($order_info['invoice_no']))
            {
                /* �����޸ķ������� */
                $edit_data['ship_time'] = gmtime();
                $is_edit = false;
            }
            $model_order->edit($order_id, $edit_data);
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            #TODO ���ʼ�֪ͨ
            /* ��¼����������־ */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_SHIPPED),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));


            /* ���͸���Ҷ����ѷ���֪ͨ */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $order_info['invoice_no'] = $edit_data['invoice_no'];
            $mail = get_mail('tobuyer_shipped_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_shipped'),
                'actions'   => array(
                    'cancel',
                    'edit_invoice_no'
                ), //����ȡ�����Է���
            );
            if ($order_info['payment_code'] == 'cod')
            {
                $new_data['actions'][] = 'finish';
            }

            $this->pop_warning('ok');
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
        /* ȡ���ĺ���ɵĶ���������ȡ�� */
        $order_id = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';
        if (!$order_id)
        {
            echo Lang::get('no_such_order');
        }
        $status = array(ORDER_SUBMITTED, ORDER_PENDING, ORDER_ACCEPTED, ORDER_SHIPPED);
        $order_ids = explode(',', $order_id);
        if ($ext)
        {
            $ext = ' AND ' . $ext;
        }

        $model_order    =&  m('order');
        /* ֻ���ѷ����Ļ������������ȡ������ */
        $order_info     = $model_order->find(array(
            'conditions'    => "order_id" . db_create_in($order_ids) . " AND seller_id=" . $this->visitor->get('manage_store') . " AND status " . db_create_in($status) . $ext,
        ));
        $ids = array_keys($order_info);
        if (!$order_info)
        {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('orders', $order_info);
            $this->assign('order_id', count($ids) == 1 ? current($ids) : implode(',', $ids));
            $this->display('seller_order.cancel.html');
        }
        else
        {
            $model_order    =&  m('order');
            foreach ($ids as $val)
            {
                $id = intval($val);
                $model_order->edit($id, array('status' => ORDER_CANCELED));
                if ($model_order->has_error())
                {
                    continue;
                }
                $status2 = array(ORDER_ACCEPTED, ORDER_SHIPPED);
                /* �ӻض�����Ʒ��� */
                if (in_array($order_info[$id]['status'],$status2))
                {
                	$model_order->change_stock('+', $id);
                }
                $cancel_reason = (!empty($_POST['remark'])) ? $_POST['remark'] : $_POST['cancel_reason'];
                /* ��¼����������־ */
                $order_log =& m('orderlog');
                $order_log->add(array(
                    'order_id'  => $id,
                    'operator'  => addslashes($this->visitor->get('user_name')),
                    'order_status' => order_status($order_info[$id]['status']),
                    'changed_status' => order_status(ORDER_CANCELED),
                    'remark'    => $cancel_reason,
                    'log_time'  => gmtime(),
                ));
                $credit = $order_info[$id]['use_credit'];
                if (in_array($order_info[$id]['status'],$status2))
	            {
	            	//��ӻ�Ա�˻���¼
		    		$param = array(
		    			'user_id' => $order_info[$id]['buyer_id'],
		    			'change_time' => gmtime(),
		    			'change_type' => 33,
		    		    'order_id' => $order_info[$id]['order_id'],
		    		);
			    	$param['change_desc'] = "���̹���Ա��{$this->visitor->get('user_name')}������ȡ��������";	
		            //�жϴ˶����Ƿ񶳽����û��Ļ���
		            if ($credit>0)
		            {
		            	//ȡ���û��������
		    			changeMemberCreditOrMoney(intval($order_info[$id]['buyer_id']),$credit,ADD_CREDIT);
		    			$param['user_credit'] = $credit;
		    			$param['change_desc'] .= "�˻��û����֣�{$credit}PL ";
		            }
		            
	            	$money = $order_info[$id]['order_amount'] - $credit; //ʣ��Ҫ�˵Ľ��
	            	if ($money>0)
	            	{
	            		//�����û����
					    changeMemberCreditOrMoney(intval($order_info['buyer_id']),$money,ADD_MONEY);
					    $param['user_money'] = $money;
					    $param['change_desc'] .= "�˻��û�����{$money}";
	            	}
					add_account_log($param);
	            }else{
	            	if ($credit>0)
    				{
    					//ȡ���û��������
    					changeMemberCreditOrMoney(intval($order_info[$id]['buyer_id']),$credit,CANCLE_FROZEN_CREDIT);
    					//��ӻ�Ա�˻���¼
			    		$param = array(
			    			'user_id' => $order_info[$id]['buyer_id'],
			    			'frozen_credit' => '-'.$credit,
			    			'change_time' => gmtime(),
			    			'change_desc' => "���̹���Ա��{$this->visitor->get('user_name')}������ȡ������,ȡ��������֣�{$credit}PL",
			    			'change_type' => 32,
			    		    'order_id' => $order_id,
			    		);
			    		add_account_log($param);
    				}
	            }
                
                /* ���͸���Ҷ���ȡ��֪ͨ */
                $model_member =& m('member');
                $buyer_info   = $model_member->get($order_info[$id]['buyer_id']);
                $mail = get_mail('tobuyer_cancel_order_notify', array('order' => $order_info[$id], 'reason' => $_POST['remark']));
                $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

                $new_data = array(
                    'status'    => Lang::get('order_canceled'),
                    'actions'   => array(), //ȡ��������Ͳ������κβ�����
                );
            }
            $this->pop_warning('ok', 'seller_order_cancel_order');
        }

    }

    /**
     *    ��ȡ��Ч�Ķ�����Ϣ
     *
     *    @author    Garbin
     *    @param     array $status
     *    @param     string $ext
     *    @return    array
     */
    function _get_valid_order_info($status, $ext = '')
    {
        $order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
        if (!$order_id)
        {

            return array();
        }
        if (!is_array($status))
        {
            $status = array($status);
        }

        if ($ext)
        {
            $ext = ' AND ' . $ext;
        }

        $model_order    =&  m('order');
        /* ֻ���ѷ����Ļ�������������ջ� */
        $order_info     = $model_order->get(array(
            'conditions'    => "order_id={$order_id} AND seller_id=" . $this->visitor->get('manage_store') . " AND status " . db_create_in($status) . $ext,
        ));
        if (empty($order_info))
        {

            return array();
        }

        return array($order_id, $order_info);
    }
    /**
     *    ��ȡ�����б�
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_orders()
    {
        $page = $this->_get_page();
        $model_order =& m('order');
		$pagelimit = $page['limit'];		
        !$_GET['type'] && $_GET['type'] = 'all_orders';

        $conditions = '';	
        // �Ź�����
        if (!empty($_GET['group_id']) && intval($_GET['group_id']) > 0)
        {
            $groupbuy_mod = &m('groupbuy');
            $order_ids = $groupbuy_mod->get_order_ids(intval($_GET['group_id']));
            $order_ids && $conditions .= ' AND order_alias.order_id' . db_create_in($order_ids);
        }
        $conditions .= $this->_get_query_conditions(array(
            array(      //������״̬����
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
            array(      //�������������
                'field' => 'buyer_name',
                'equal' => 'LIKE',
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
        ));
        if ($_GET['mobile'])
        {
        	$conditions .= " AND m.mobile = '" . trim($_GET['mobile']) . "'";
        	$this->assign('mobile', trim($_GET['mobile']));
        }
        
        $order_count = $model_order->getOne("SELECT COUNT(*) as c FROM pa_order order_alias LEFT JOIN pa_order_extm 
        order_extm ON order_alias.order_id=order_extm.order_id left join pa_member m on m.user_id = order_alias.buyer_id WHERE 
        seller_id=" . $this->visitor->get('manage_store') . "{$conditions}");
        
        $orders = $model_order->getAll("SELECT order_alias.*,m.*,order_extm.consignee,order_extm.region_id,
        						order_extm.region_name,order_extm.address,order_extm.zipcode,order_extm.shipping_name,order_extm.shipping_id,
        						order_extm.phone_mob,order_extm.phone_tel FROM pa_order order_alias LEFT JOIN pa_order_extm 
        order_extm ON order_alias.order_id=order_extm.order_id left join pa_member m on m.user_id = order_alias.buyer_id WHERE 
        seller_id=" . $this->visitor->get('manage_store') ."{$conditions}"." order by add_time desc". " limit ".$pagelimit );
        /* ���Ҷ��� */
       	$orderGoods_mod = & m('ordergoods');
        foreach ($orders as $key1 => $order)
        {
      		$orders[$key1]['order_goods'] = $orderGoods_mod->getAll("select * from pa_order_goods og 
      		where og.order_id = " . $orders[$key1]['order_id']);
            foreach ($orders[$key1]['order_goods'] as $key2 => $goods)
            {
                empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
            }
        }
        $page['item_count'] = $order_count;
        //$page['item_count'] = $model_order->getCount();
        $this->_format_page($page);
        $this->assign('types', array('all' => Lang::get('all_orders'),
                                     'pending' => Lang::get('pending_orders'),
                                     'submitted' => Lang::get('submitted_orders'),
                                     'accepted' => Lang::get('accepted_orders'),
                                     'shipped' => Lang::get('shipped_orders'),
                                     'finished' => Lang::get('finished_orders'),
                                     'canceled' => Lang::get('canceled_orders')));
        $this->assign('type', $_GET['type']);
        $this->assign('orders', $orders);
        $this->assign('page_info', $page);
    }
    
	/**
     *    ��ȡ��ָ�ɵ����������б�
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_paila_orders()
    {
        $page = $this->_get_page();
        $model_order =& m('order');

        !$_GET['type'] && $_GET['type'] = 'all_orders';

        $conditions = '';

        // �Ź�����
        if (!empty($_GET['group_id']) && intval($_GET['group_id']) > 0)
        {
            $groupbuy_mod = &m('groupbuy');
            $order_ids = $groupbuy_mod->get_order_ids(intval($_GET['group_id']));
            $order_ids && $conditions .= ' AND order_alias.order_id' . db_create_in($order_ids);
        }
        $conditions .= $this->_get_query_conditions(array(
            array(      //������״̬����
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
            array(      //�������������
                'field' => 'buyer_name',
                'equal' => 'LIKE',
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
        ));

        /* ���Ҷ��� */
        $orders = $model_order->findAll(array(
            'conditions'    => "assign_store_id=" . $this->visitor->info['store_id'] . "{$conditions}",
            'count'         => true,
            'join'          => 'has_orderextm',
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
            'include'       =>  array(
                'has_ordergoods',       //ȡ����Ʒ
            ),
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
        $this->_format_page($page);
        $this->assign('types', array('all' => Lang::get('all_orders'),
                                     'pending' => Lang::get('pending_orders'),
                                     'submitted' => Lang::get('submitted_orders'),
                                     'accepted' => Lang::get('accepted_orders'),
                                     'shipped' => Lang::get('shipped_orders'),
                                     'finished' => Lang::get('finished_orders'),
                                     'canceled' => Lang::get('canceled_orders')));
        $this->assign('type', $_GET['type']);
        $this->assign('orders', $orders);
        $this->assign('page_info', $page);
    }
    /*�����˵�*/
    function _get_member_submenu()
    {
        $array = array(
            array(
                'name' => 'all_orders',
                'url' => 'index.php?app=seller_order&amp;type=all_orders',
            ),
            array(
                'name' => 'pending',
                'url' => 'index.php?app=seller_order&amp;type=pending',
            ),
            array(
                'name' => 'submitted',
                'url' => 'index.php?app=seller_order&amp;type=submitted',
            ),
            array(
                'name' => 'accepted',
                'url' => 'index.php?app=seller_order&amp;type=accepted',
            ),
            array(
                'name' => 'shipped',
                'url' => 'index.php?app=seller_order&amp;type=shipped',
            ),
            array(
                'name' => 'finished',
                'url' => 'index.php?app=seller_order&amp;type=finished',
            ),
            array(
                'name' => 'canceled',
                'url' => 'index.php?app=seller_order&amp;type=canceled',
        ),
        );
        return $array;
    }
	/**
     *    �鿴���ж�������
     *
     *    @author    Garbin
     *    @return    void
     */
    function viewAll()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

        $model_order =& m('order');
        $order_info  = $model_order->findAll(array(
            'conditions'    => "order_alias.order_id={$order_id}",
            'join'          => 'has_orderextm',
        ));
        $order_info = current($order_info);
        if (!$order_info)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* �Ź���Ϣ */
        if ($order_info['extension'] == 'groupbuy')
        {
            $groupbuy_mod = &m('groupbuy');
            $group = $groupbuy_mod->get(array(
                'join' => 'be_join',
                'conditions' => 'order_id=' . $order_id,
                'fields' => 'gb.group_id',
            ));
            $this->assign('group_id',$group['group_id']);
        }

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('order_manage'), 'index.php?app=seller_order',
                         LANG::get('view_order'));

        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('order_manage');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('detail'));

        /* ������Ӧ�Ķ������ͣ���ȡ���������������� */
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        $spec_ids = array();
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
            $spec_ids[] = $goods['spec_id'];

        }

        /* ������µ���Ӧ�Ļ��� */
        $model_spec =& m('goodsspec');
        $spec_info = $model_spec->find(array(
            'conditions'    => $spec_ids,
            'fields'        => 'sku',
        ));
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            $order_detail['data']['goods_list'][$key]['sku'] = $spec_info[$goods['spec_id']]['sku'];
        }

        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        $this->display('storeadmin.sellerorder.view.html');
    }
    
    /** ��Ϣ��ʾ��Ϣ
     * @author wscsky
     * @return void
 
     */
    function message(){
      $order =&m('order');
    	$type = isset($_GET['type'])? $_GET['type']:"0";
    	//����״̬
    	$sql = "update pa_order set popstatus =1 where popstatus=0 and status=11 and seller_id=".$this->visitor->get('user_id');
    	$sql2 = "update pa_order set popstatus =2 where popstatus<2 and status=20 and seller_id=".$this->visitor->get('user_id');
    	if($type=="chgall"){	
    		mysql_query($sql);
    		mysql_query($sql2);
    		echo "0|0";exit();			
    	}
    	if($type=="chg1"){	
    		mysql_query($sql);
    		echo "0";exit();	
    	}
    	if($type=="chg2"){	
    		mysql_query($sql2); 
    		echo "0";exit();			
    	}
        $sql="select count(*) as num from pa_order where status=11 and seller_id=".$this->visitor->get("user_id")." and popstatus=0";
        $sql2="select count(*) as num from pa_order where status=20 and seller_id=".$this->visitor->get("user_id")." and popstatus<2";
        $sum= $order->getall($sql);
        $sum2= $order->getall($sql2);
        echo $sum[0]['num']."|".$sum2[0]['num'];  	
    }
}

?>
