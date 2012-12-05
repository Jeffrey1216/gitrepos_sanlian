<?php

/**
 *    合作伙伴控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class Store_orderApp extends BackendApp
{
	var $_store_order_mod;
	var $_store_order_log_mod;
	var $_store_order_extm_mod;
	function __construct(){
		$this->Store_orderApp();
	}
 	function Store_orderApp(){
    	parent::__construct();
    	$this->_store_order_mod=& m('storeorder');
    	$this->_store_order_log_mod=& m('storeorderlog');
    	$this->_store_order_extm_mod=& m('storeorderextm');
    	
    }
    /**
     *    管理
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
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
                'field' => 'so.add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'so.add_time',
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
             $sort  = 'add_time';
             $order = 'desc';
            }
        }
        else
        {
            $sort  = 'add_time';
            $order = 'desc';
        }

        $store_order_info = $this->_store_order_mod->getAll('select so.arrears_amount,so.pay_amount,so.order_id,so.order_sn,so.goods_amount,s.store_type,so.pay_message,so.order_amount,s.store_name,so.payment_name,so.add_time,soe.shipping_fee,so.status,so.op_status from pa_store_order
       												 so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id where
       												  '."$conditions".'  ORDER BY so.add_time DESC limit '.$page['limit']) ;

        //统计总数
       	$page['item_count'] = $this->_store_order_mod->getOne('select count(*) from pa_store_order so left join pa_store s on so.buyer_id = s.store_id left join pa_store_order_extm soe on so.order_id = soe.order_id
       															 where '."$conditions");
        $this->_format_page($page);
        $this->assign('filtered', $conditions != '1=1'? 1 : 0); //是否有查询条件
        $this->assign('order_status_list', array(
            ORDER_PENDING => Lang::get('待付款'),
            ORDER_ACCEPTED => Lang::get('待发货'),
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
       	if($store_order_info)
       	{
       		foreach ($store_order_info as $_key => $_val)
       		{
       			$all_amount['arrears_amount'] += $_val['arrears_amount'];
       			$all_amount['pay_amount'] 	  += $_val['pay_amount'];
       			$all_amount['goods_amount'] += $_val['goods_amount'];
       			$all_amount['order_amount'] += $_val['order_amount'];
       			$all_amount['shipping_fee'] += $_val['shipping_fee'];
       		}
       		$this->assign('all_amount',$all_amount);
       	}
        $this->assign('search_options', $search_options);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('orders', $store_order_info);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style' => 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->assign('app',APP);
        $this->display('store_order.index.html');
    }

    /**
     *    查看店铺订单
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return   void
     */
    function view()
    {
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        /* 获取订单信息 */
        $model_order =& m('storeorder');
        $order_info = $model_order->get(array(
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
        
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_store_order_detail($order_id, $order_info);
        
        $this->assign('app',APP);
        $this->assign('image_url',IMAGE_URL);
        $this->assign('order',$order_info);
        $this->assign('order_detail',$order_detail['data']);
        $this->display('store_order.view.html');
    }
    
	public function orderprint(){ //订单打印
    	$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* 获取订单信息 */
        $model_order =& m('storeorder');
        $order_info = $model_order->get(array(
            'conditions'    => $order_id,
            'join'          => 'has_orderextm',
            'include'       => array(
                'has_ordergoods',   //取出订单商品
            ),
        ));
       if (!$order_info)
        {
            $this->show_warning('no_such_order');
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
        $this->assign('order', $order_info);
        $this->assign('order_id',$order_id);
        $this->assign($order_detail['data']);
        $this->display('store_order.detaillist.html');
    }
	/*发货*/
	function delivery(){
		$order_id=empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
		$order=$this->_store_order_mod->get($order_id);
		if (!$order)
		{
			$this->show_warning('no_such_order');
			return;
		}
		if ($order['status']!=20 || $order['op_status']!=3)
		{
			$this->show_warning('操作错误！');
			return;
		}
		if(!IS_POST)
		{
    		$this->assign("orinfo",$order);
    		$this->display("store_order.delv.html");
		} else 
		{
			if (!$_POST['ship_no'])
			{
				$this->show_warning('物流单号必填！');
				return;
			}
			//添加物流信息，并修改订单状态-发货
    		$data['status']     = 30;
    		$data['op_status']  = 4;
    		$data['ship_no']    = trim($_POST['ship_no']);
    		$data['ship_reason']= trim($_POST['ship_reason']);
    		$data['ship_query'] =trim($_POST['ship_query']);
    		$data['ship_time']  =time();
    		$data['invoice_no'] =trim($_POST['invoice_no']);
    		$this->_store_order_mod->edit($order_id,$data);
    		
    		//添加订单操作日志
    		$data2['order_id'] = $order_id;
    		$data2['operator'] = $this->visitor->get('user_name');
    		$data2['order_status']   = order_status(20);
    		$data2['changed_status'] = order_status(30);
    		$data2['log_time'] = time();
    		$data2['remark']   = '物流后台发货';
    		$log_mod=& m('storeorderlog');
    		$log_mod->add($data2);
    		$this->show_message('店铺进货订单发货成功！',"返回列表",'index.php?app=store_order');
		}
	}
	/*修改物流费用*/
	function audit(){
		$order_id=empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
		$order=$this->_store_order_mod->getRow("select so.order_id,so.order_sn,so.goods_amount,so.order_amount,so.status,so.op_status,soe.shipping_fee from pa_store_order so left join pa_store_order_extm soe on so.order_id = soe.order_id  where so.order_id = " . $order_id);
		if (!$order)
		{
			$this->show_warning('no_such_order');
			return;
		}
		if ($order['status']!=11 || $order['op_status']!=0)
		{
			$this->show_warning('操作错误！');
			return;
		}
		if(!IS_POST){
			
    		$this->assign('orinfo',$order);
            $this->display('store_order.batch.html');
    	}
    	else
    	{
    		//更新订单的物流费用
			$data1['shipping_fee'] = floatval($_POST['shipping_fee']);
			$this->_store_order_extm_mod->edit($order_id,$data1);
			//更改订单总价及操作状态
			$data2['order_amount'] = $order['goods_amount']+floatval($_POST['shipping_fee']);
			$data2['op_status']    = 1;
			$this->_store_order_mod->edit($order_id,$data2);
			$this->show_message('更改物流费用成功！',"返回列表",'index.php?app=store_order');
    	}
	}
	function pay(){
		
		$order_id=empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
		$store_info=$this->_store_order_mod->getRow("select s.pay_message,s.status from pa_store_order s  where s.order_id = " . $order_id);
		$this->assign('store_info',$store_info);
		$user_name=$this->visitor->get('user_name');
		$store_order_mod=& m('storeorder');
		$store_order_log_mod=& m('storeorderlog');
		if(!IS_POST){
			if($order_id!=0){
				$this->assign('order_id',$order_id);
				$this->display("store_order.pay.html");
			}
		}
		else{
			$data1=array();
			$data1['status'] = 20; 
			$store_order_mod->edit($order_id,$data1);
			$data2=array();
			$data2['order_id']=$order_id;
			$data2['operator']=$user_name;
    		$data2['order_status']=$store_info['status'];
    		$data2['changed_status']=20;
    		$data2['remark']="汇款确认收款";
    		$data2['log_time']=time();
    		$store_order_log_mod->add($data2);
    		$this->index();
		}
	}
}
?>
