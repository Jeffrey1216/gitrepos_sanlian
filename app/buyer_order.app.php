<?php

/**
 *    买家的订单管理控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class Buyer_orderApp extends MemberbaseApp
{
    function index()
    {
        /* 获取订单列表 */
        $this->_get_orders();
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                         LANG::get('my_order'), 'index.php?app=buyer_order',
                         LANG::get('order_list'));
		
        /* 当前用户基本信息*/
        $this->_get_user_info();

        /* 当前用户中心菜单 */
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
        /* 显示订单列表 */
        $this->display('buyer_order.index.html');
    }
    /**
     *    查看订单详情
     *
     *    @author    Garbin
     *    @return    void
     */
    function view()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $refunds = isset($_GET['refunds']) ? 1 : intval($_GET['refunds']);
        $model_order =& m('order');  
        $order_info = $model_order->get(array(
            'fields'        => "*, order.add_time as order_add_time",
            'conditions'    => "order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'),
            'join'          => 'belongs_to_store,',
            ));   
        if($refunds)
        {
	        if($order_info['seller_id'] != STORE_ID && $order_info['status'] != 20)
	        {
	        	$this->show_warning('该店铺不能此退货!');
	        	return;
	        }
        }
        
        if (!$order_info)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* 团购信息 */
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

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                         LANG::get('my_order'), 'index.php?app=buyer_order',
                         LANG::get('view_order'));

        /* 当前用户中心菜单 */
        $this->_curitem('my_order');

        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_detail'));

        /* 调用相应的订单类型，获取整个订单详情数据 */
        $order_type =& ot('normal');
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
        }
        $this->assign('refunds',$refunds);
        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        if(IS_POST)
        {
        	$reason = empty($_POST['drop_reason']) ? '' : trim($_POST['drop_reason']);
        	if($reason == '' || $reason == null)
        	{
        		$this->show_message('请填写退货原因!');
        		return;
        	}
        	$data = array();
        	$data['status'] = 50;
        	$data['refund_cause'] = $reason;   
        	$data['refund_time'] = time();
        	if(!$model_order->edit($order_id,$data))
        	{
        		 $this->show_warning($this->get_error());
                return;        
        	}else{
        		$this->show_warning('申请退货成功，等待专员审核',
        			'返回','index.php?app=buyer_order'
        		);
        	}
        }
        $this->display('buyer_order.view.html');
    }
	//退款订单
	function order_refunds()
	{
		/* 获取订单列表 */
        $this->_get_orders(1);
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                         LANG::get('my_order'), 'index.php?app=buyer_order&act=order_refunds',
                         LANG::get('order_refunds'));
        /* 当前用户基本信息*/
        $this->_get_user_info();
        
        /* 当前用户中心菜单 */
        $this->_curitem('my_order');
        $this->_curmenu('order_refunds');
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
		$this->display('buyer_order.order_refunds.html');
	}
	//取消退货
	function cancel_refunds()
	{
		$order_id = empty($_GET['order_id']) ? '' : intval($_GET['order_id']);
		if(!$order_id)
		{
			$this->show_message('该定单不存在!');
			return;
		}
		$model_order =& m('order'); 
		$order_info = $model_order->getRow('select * from pa_order where order_id='.$order_id);
		$data = array();
		$data['refund_time'] = 0;
		if($order_info['finished_time'] == 0 || $order_info['finished_time'] == '')
		{
			$data['status'] = 20;
		}else{
			$data['status'] = 40;
		}
		if(!$model_order->edit($order_id,$data))
        {
        	 $this->show_message($this->get_error());
             return;        
        }else{
        	$this->show_warning('取消退货成功',
        		'返回','index.php?app=buyer_order&act=order_refunds'
        	);
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
        $model_order    =&  m('order');
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
            $this->display('buyer_order.cancel.html');
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
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_CANCELED),
                'remark'    => $cancel_reason,
                'log_time'  => gmtime(),
            ));
            
            //判断此订单是否冻结了用户的积分
            $credit = $order_info['use_credit'];
            if ($credit>0)
            {
            	//取消用户冻结积分
    			changeMemberCreditOrMoney(intval($order_info['buyer_id']),$credit,CANCLE_FROZEN_CREDIT);
    			//添加会员账户记录
	    		$param = array(
	    			'user_id' => $order_info['buyer_id'],
	    			'frozen_credit' => '-'.$credit,
	    			'change_time' => gmtime(),
	    			'change_desc' => "会员主动操作取消订单,取消冻结积分：{$credit}PL",
	    			'change_type' => 32,
	    		    'order_id' => $order_id,
	    		);
	    		add_account_log($param);
            }
        	
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
        }

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
        $model_order    =&  m('order');
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
            $this->display('buyer_order.confirm.html');
        }
        else
        {
            $model_order->edit($order_id, array('status' => ORDER_FINISHED, 'finished_time' => gmtime()));
            $model_ordergoods =& m('ordergoods');
            $order_goods = $model_ordergoods->find("order_id={$order_id}");
            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }

            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_FINISHED),
                'remark'    => Lang::get('buyer_confirm'),
                'log_time'  => gmtime(),
            ));
            
	        /**
			* 更新买家积分 
			**/
			$user_id    = $order_info['buyer_id']; 
			$get_credit = $order_info['get_credit'];//订单赠送的积分
			if ($get_credit>0)
			{
				//这里定单差不多成功,直接取order表中的get_credit
				changeMemberCreditOrMoney($user_id,$get_credit,ADD_CREDIT);
				
				//添加会员账户记录
		    	$param = array(
		    		'user_id' => $user_id,
		    		'user_credit'  => $get_credit,
		    		'change_time' => gmtime(),
		    		'change_desc' => "用户确认完成订单，赠送用户积分：{$get_credit}PL",
		    		'change_type' => 1,
		    	    'order_id' => $order_id,
		    	);
		    	add_account_log($param);
			}
	        /* 订单交易完成, 计算返利 */
	    	$model_member =& m('member');
	    	$_customer_manager_mod = & m('customermanager');
			$member_info = $model_member->get($user_id);
			$manager_info = $_customer_manager_mod->get($user_id);
			
			$autotrophy_money = $autotrophy_credit = $credit = 0 ;
			foreach ($order_goods as $goods)
			{
				if ($goods['autotrophy'] == 1)
				{
					$autotrophy_money += $goods['price'] * $goods['quantity']; //订单中大礼包产品的总价
					if ($goods['is_usecredit'] == 0) //取出未使用积分支付的大礼包
					{
						$autotrophy_credit += $goods['credit'] * $goods['quantity']; //订单中未使用积分支付大礼包产品的总派送积分
					}
				}
			}
			//计算出剩余参与返利的积分值
			if ($order_info['get_credit']>$autotrophy_credit)
			{
				$credit = $get_credit - $autotrophy_credit;
			}
				
	        //当购物用户本身不是团购员时
			if (!$manager_info)
			{
				$type = getfanli($user_id); //获得推荐人的类型
				if ($type == 'tuan')
				{
					$this->ad_manager_rebate($member_info['invite_id'], $autotrophy_money ,1,$order_id);
				
					$this->mb_manager_rebate($member_info['invite_id'],$credit,$order_id,$order_info['goods_amount']);
				}elseif ($type == 'store')
				{
					$this->mb_store_rebate($member_info['invite_id'],$credit,$order_id);
				}else
				{
					//购物用户没有任何人推荐归属于渠道
					$this->mb_channel_rebate(CHANNEL_ID,$credit,$order_id);
				}
			}else
			{
				//用户本身已经是团购员
				//大礼包部分按相应等级提成给予返利
				$this->ad_manager_rebate($member_info['user_id'], $autotrophy_money ,1,$order_id);
				
				$type = getfanli($user_id); //获得推荐人的类型
				if ($type == 'tuan')
				{
					$this->mb_manager_rebate($member_info['invite_id'],$credit,$order_id,$order_info['goods_amount']);
				}elseif ($type == 'store')
				{
					//其他部分按积分给予返利计算
					$this->mb_store_rebate($member_info['invite_id'],$credit,$order_id);
				}else
				{
					//购物用户没有任何人推荐归属于渠道
					$this->mb_channel_rebate(CHANNEL_ID,$credit,$order_id);
				}
			}
			

            /* 发送给卖家买家确认收货邮件，交易完成 */
            $seller_info   = $model_member->get($order_info['seller_id']);
            $mail = get_mail('toseller_finish_notify', array('order' => $order_info));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status'    => Lang::get('order_finished'),
                'actions'   => array('evaluate'),
            );

            /* 更新累计销售件数 */
            $model_goodsstatistics =& m('goodsstatistics');
            
            foreach ($order_goods as $goods)
            {
                $model_goodsstatistics->edit($goods['goods_id'], "sales=sales+{$goods['quantity']}");
            }

            $this->pop_warning('ok','','index.php?app=buyer_order&act=evaluate&order_id='.$order_id);;
        }
    }

    /**
     *    给卖家评价
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function evaluate()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* 验证订单有效性 */
        $model_order =& m('order');
        $order_info  = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (!$order_info)
        {
            $this->show_warning('no_such_order');

            return;
        }
        if ($order_info['status'] != ORDER_FINISHED)
        {
            /* 不是已完成的订单，无法评价 */
            $this->show_warning('cant_evaluate');

            return;
        }
        if ($order_info['evaluation_status'] != 0)
        {
            /* 已评价的订单 */
            $this->show_warning('already_evaluate');

            return;
        }
        $model_ordergoods =& m('ordergoods');

        if (!IS_POST)
        {
            /* 显示评价表单 */
            /* 获取订单商品 */
            $goods_list = $model_ordergoods->find("order_id={$order_id}");
            foreach ($goods_list as $key => $goods)
            {
                empty($goods['goods_image']) && $goods_list[$key]['goods_image'] = Conf::get('default_goods_image');
            }
            $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                             LANG::get('my_order'), 'index.php?app=buyer_order',
                             LANG::get('evaluate'));
            $this->assign('goods_list', $goods_list);
            $this->assign('order', $order_info);

            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('credit_evaluate'));
            $this->display('buyer_order.evaluate.html');
        }
        else
        {
            $evaluations = array();
            /* 写入评价 */
            foreach ($_POST['evaluations'] as $rec_id => $evaluation)
            {
                if ($evaluation['evaluation'] <= 0 || $evaluation['evaluation'] > 3)
                {
                    $this->show_warning('evaluation_error');

                    return;
                }
                switch ($evaluation['evaluation'])
                {
                    case 3:
                        $credit_value = 1;
                    break;
                    case 1:
                        $credit_value = -1;
                    break;
                    default:
                        $credit_value = 0;
                    break;
                }
                $evaluations[intval($rec_id)] = array(
                    'evaluation'    => $evaluation['evaluation'],
                    'comment'       => $evaluation['comment'],
                    'credit_value'  => $credit_value
                );
            }
            $goods_list = $model_ordergoods->find("order_id={$order_id}");
            foreach ($evaluations as $rec_id => $evaluation)
            {
                $model_ordergoods->edit("rec_id={$rec_id} AND order_id={$order_id}", $evaluation);
                $goods_url = SITE_URL . '/' . url('app=goods&id=' . $goods_list[$rec_id]['goods_id']);
                $goods_name = $goods_list[$rec_id]['goods_name'];
                $this->send_feed('goods_evaluated', array(
                    'user_id'   => $this->visitor->get('user_id'),
                    'user_name'   => $this->visitor->get('user_name'),
                    'goods_url'   => $goods_url,
                    'goods_name'   => $goods_name,
                    'evaluation'   => Lang::get('order_eval.' . $evaluation['evaluation']),
                    'comment'   => $evaluation['comment'],
                    'images'    => array(
                        array(
                            'url' => SITE_URL . '/' . $goods_list[$rec_id]['goods_image'],
                            'link' => $goods_url,
                        ),
                    ),
                ));
            }

            /* 更新订单评价状态 */
            $model_order->edit($order_id, array(
                'evaluation_status' => 1,
                'evaluation_time'   => gmtime()
            ));

            /* 更新卖家信用度及好评率 */
            $model_store =& m('store');
            $model_store->edit($order_info['seller_id'], array(
                'credit_value'  =>  $model_store->recount_credit_value($order_info['seller_id']),
                'praise_rate'   =>  $model_store->recount_praise_rate($order_info['seller_id'])
            ));

            /* 更新商品评价数 */
            $model_goodsstatistics =& m('goodsstatistics');
            $goods_ids = array();
            foreach ($goods_list as $goods)
            {
                $goods_ids[] = $goods['goods_id'];
            }
            $model_goodsstatistics->edit($goods_ids, 'comments=comments+1');


            $this->show_message('evaluate_successed',
                'back_list', 'index.php?app=buyer_order');
        }
    }
	function complain()
	{	
		/* 获取订单列表 */
        $this->_get_orders(2);
        
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                         LANG::get('my_order'), 'index.php?app=buyer_order&act=complain',
                         LANG::get('complain'));
        /* 当前用户基本信息*/
        $this->_get_user_info();
        
        /* 当前用户中心菜单 */
        $this->_curitem('my_order');
        $this->_curmenu('complain');
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
		$this->display('complain.index.html');
	}
	//我要投诉
	function sue()
	{
		$id = empty($_GET['order_id']) ? '' : intval($_GET['order_id']);
		$data = complain_type();
		$this->assign('data',$data);
		if(!$id)
		{
			$this->show_warning('该订单不存在!');
			return;
		}
		$_order_mod = &m('order');
		$_complain_mod = &m('complain');
		$order_info = $_order_mod->getRow('select * from pa_order where order_id ='.$id);
		$this->assign('order_info',$order_info);
		if(!IS_POST)
		{
			$this->display('sue.index.html');
		}else{
			$order_reason = empty($_POST['ship_reason']) ? '' : trim($_POST['ship_reason']);
			$complain_type = empty($_POST['data']) ? '' : intval($_POST['data']);
			$data = array();
			if($order_reason == '' || $order_reason==null)
			{
				$this->show_message('请填写原因!');
				return;
			}else{
				$data['complain_reason'] = $order_reason;
			}
			$data['order_id'] = $id;
			$data['complain_type'] = $complain_type;
			$data['status'] = 1;
			$data['add_time'] = time();
			if(!$_complain_mod->add($data))
			{
				$this->show_message($_complain_mod->get_error());
				return;
			}else{
				$this->show_message('投诉成功,请耐心等待专员审核..',
				'返回','index.php?app=buyer_order'
				);
			}
		}
	}
	//投诉查看
	function complain_view()
	{
		$id = empty($_GET['id']) ? '' : intval($_GET['id']);
		if(!$id)
		{
			$this->show_warning('该投诉不存在!');
			return;
		}
		$_complain_mod = &m('complain');
		$data = complain_type();
		$complain = $_complain_mod->getRow('select *,c.status as c_status,c.add_time as c_add_time from pa_complain c left join pa_order o on c.order_id = o.order_id where o.order_id ='.$id);
		$complain['complain_type'] = $data[$complain['complain_type']];
		$this->assign('complain_info',$complain);
		$this->display('complain_view.index.html');
	}
    /**
     *    获取订单列表
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_orders($index = 0)
    {
        $page = $this->_get_page(10);
        $model_order =& m('order');
        $con = array(
            array(      //按订单状态搜索
                'field' => 'order_alias.status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
            array(      //按店铺名称搜索
                'field' => 'seller_name',
                'equal' => 'LIKE',
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
            array(      //按下单时间搜索,起始时间
                'field' => 'complain.add_time',
                'name'  => 'c_add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),
            array(      //按下单时间搜索,结束时间
                'field' => 'complain.add_time',
                'name'  => 'c_add_time_to',
                'equal' => '<=',
                'handler'=> 'gmstr2time_end',
            ),
            array(      //按订单号
                'field' => 'order_sn',
            ),
        );
        $conditions = ' 1=1';
        $conditions .= $this->_get_query_conditions($con);
        if(intval($_GET['type']))
        {
        	$conditions .= " and order_alias.status =".intval($_GET['type']);
        }else{
        	 !$_GET['type'] && $_GET['type'] = 'all_orders';
        }
        if($index == 1)
        {
        	$conditions .= " and order_alias.status >= 50 and order_alias.status <=60 and order_alias.buyer_id=" . $this->visitor->get('user_id');
        }elseif($index == 2){
        	$types = empty($_GET['types']) ? '' : intval($_GET['types']);
        	if($types)
        	{
        		$conditions .= " and complain.status=".$types;
        	}else{
        		$conditions .= " and complain.status != 0";
        	}
        }else{
        	$conditions .= " and order_alias.status <> 50 and order_alias.status <> 60 and order_alias.buyer_id=". $this->visitor->get('user_id');
        }
        	/* 查找订单 */
	        $orders = $model_order->findAll(array(
	            'conditions'    => $conditions,
	            'fields'        => 'this.*,complain.status as complain_status,complain.add_time as c_add_time',
	        	'join'			=> 'has_complain',
	            'count'         => true,
	            'limit'         => $page['limit'],
	            'order'         => 'add_time DESC',
	            'include'       =>  array(
	                'has_ordergoods',       //取出商品
	            ),
	        ));
        foreach ($orders as $key1 => $order)
        {
        	if (!empty($order['order_goods']))
        	{	
	            foreach ($order['order_goods'] as $key2 => $goods)            
	            {
	                empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');	
	            }
        	}
        }
        $page['item_count'] = $model_order->getCount();
        if($index == 1)
        {
        	$this->assign('types', array(
        							 ORDER_REFUND => Lang::get('退款中...'),
                                     ORDER_REFUND_FINISH => Lang::get('退款完成')));
        }elseif($index == 2)
        {
        	$this->assign('types',array(
        							'1' => LANG::GET('待处理'),
        							'2' => LANG::GET('已解决'),
        	));
        }else{
        	$this->assign('types', array('all'     => Lang::get('all_orders'),
                                     'pending' => Lang::get('pending_orders'),
                                     'submitted' => Lang::get('submitted_orders'),
                                     'accepted' => Lang::get('accepted_orders'),
                                     'shipped' => Lang::get('shipped_orders'),
                                     'finished' => Lang::get('finished_orders'),
                                     'canceled' => Lang::get('canceled_orders')));	
        } 
        $this->assign('type', $_GET['type']);    
        $this->assign('orders', $orders);
        $this->_format_page($page);
        $this->assign('STORE_ID',STORE_ID);
        $this->assign('page_info', $page);
    }	 
    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name'  => 'order_list',
                'url'   => 'index.php?app=buyer_order',
            ),   
            array(
                'name'  => 'order_refunds',
                'url'   => 'index.php?app=buyer_order&amp;act=order_refunds',
            ), 
            array(
                'name'  => 'complain',
                'url'   => 'index.php?app=buyer_order&amp;act=complain',
            ),      	      	
        );
        return $menus;
    }
	
}

?>
