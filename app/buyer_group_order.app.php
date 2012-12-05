<?php

/**
 *    ��ҵĶ������������
 *
 *    @author    Garbin
 *    @usage    none
 */
class Buyer_group_orderApp extends MemberbaseApp
{
    function index()
    {
        /* ��ȡ�����б� */
        $this->_get_orders();

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                         LANG::get('my_order'), 'index.php?app=buyer_group_order',
                         LANG::get('order_list'));

        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('my_order');
        $this->_curmenu('order_list');
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


        /* ��ʾ�����б� */
        $this->display('buyer_groupbuy.index.html');
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
        $model_order =& m('grouporder');
        //$order_info  = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        $order_info = $model_order->get(array(
            'fields'        => "*, order.add_time as order_add_time",
            'conditions'    => "order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'),
            'join'          => 'belongs_to_store,',
            ));
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
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                         LANG::get('my_order'), 'index.php?app=buyer_group_order',
                         LANG::get('view_order'));

        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('my_order');

        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_detail'));

        /* ������Ӧ�Ķ������ͣ���ȡ���������������� */
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
        }
        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        
        $this->display('buyer_order.view.html');
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
        $model_order    =&  m('grouporder');
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
            $this->display('buyer_group_order.cancel.html');
        }
        else
        {
            $model_order->edit($order_id, array('status' => ORDER_CANCELED));
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }
            /* �ӻ���Ʒ��� */
            $cancel_reason = (!empty($_POST['remark'])) ? $_POST['remark'] : $_POST['cancel_reason'];
            /* ��¼����������־ */
            $order_log =& m('grouporderlog');
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
        }

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
        $model_order    =&  m('grouporder');
        /* ֻ���ѷ����Ķ�������ȷ�� */
        $order_info     = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id') . " AND status=" . ORDER_SHIPPED);
        if (empty($order_info))
        {
            echo Lang::get('no_such_order');

            return;
        }
        //var_dump($order_info);
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('buyer_group_order.confirm.html');
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
            $order_log =& m('grouporderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_FINISHED),
                'remark'    => Lang::get('buyer_confirm'),
                'log_time'  => gmtime(),
            ));
            
       	
            $model_member =& m('member');
            $seller_info   = $model_member->get($order_info['seller_id']);
            $mail = get_mail('toseller_finish_notify', array('order' => $order_info));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_finished'),
                'actions'   => array('evaluate'),
            );

            /* �����ۼ����ۼ��� */
            $groupproject_mod =& m('groupproject');
            $groupproject_mod->edit($order_info['project_id']," sale = sale + " . intval($order_info['quantity']));
  			
            $this->pop_warning('ok','','index.php?app=buyer_group_order');;
        }
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
        $model_order =& m('grouporder');
        !$_GET['type'] && $_GET['type'] = 'all_orders';
        $con = array(
            array(      //������״̬����
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
            array(      //��������������
                'field' => 'seller_name',
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
        );
        $conditions = $this->_get_query_conditions($con);
        /* ���Ҷ��� */
        $conditions = "buyer_id=" . $this->visitor->get('user_id') . "{$conditions}";
        $count = $model_order->getOne("select count(*) from pa_group_order go left join pa_group_project gp on 
        go.project_id = gp.id left join pa_group_category gc on gp.category_id = gc.id where " . $conditions);
        $page['item_count'] = $count;
        $orders = $model_order->getAll("select * from pa_group_order go left join pa_group_project gp on 
        go.project_id = gp.id left join pa_group_category gc on gp.category_id = gc.id where " . $conditions . " limit " . $page['limit']);
        /*echo "<pre>";
        var_dump($orders);
        echo "</pre>";
        exit();*/

        $this->assign('types', array('all'     => 'ȫ������',
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

    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name'  => '�Ź�����',
                'url'   => 'index.php?app=buyer_group_order',
            ),
        );
        return $menus;
    }

}

?>
