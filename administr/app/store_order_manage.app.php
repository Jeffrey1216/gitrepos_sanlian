<?php
define('PAGE_NUM',20);
/* 店铺管理控制器 */
class Store_order_manageApp extends BackendApp
{
	private static $recomManager_info = array();
	var $algebra;
	private static $rate_arr = array();
	var $_store_order_mod;
	var $_store_order_log_mod;
	var $_store_order_extm_mod;
	var $_member_mod;
	function __construct(){
		$this->Store_order_manageApp();
	}
 	function Store_order_manageApp(){
    	parent::__construct();
    	$this->_store_order_mod=& m('storeorder');
    	$this->_store_order_log_mod=& m('storeorderlog');
    	$this->_store_order_extm_mod=& m('storeorderextm');
    	$this->_member_mod =& m('member');
    }
    
	//渠道商审核
    function index()
    {
    	$channelrecommend =&m('channelrecommend');    //推荐商户
     	 
        $page = $this->_get_page(30);
        $page['item_count'] = $channelrecommend->getOne("select count(*) from pa_channel_recommend cr left join 
        pa_channel_level cl on cr.level = cl.id where cr.status = 0");
        
        $users = $channelrecommend->getAll("select *,cr.id as rid from pa_channel_recommend cr left join 
        pa_channel_level cl on cr.level = cl.id where cr.status = 0 order by createtime DESC limit " . $page['limit']);
        $this->assign('users', $users);
        $this->_format_pages($page);
        $this->assign('page_info', $page);
        $this->display('channel.index.html');
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

        $store_order_info = $this->_store_order_mod->getAll('select so.order_id,so.order_sn,so.goods_amount,s.store_type,so.pay_message,so.order_amount,s.store_name,so.payment_name,so.add_time,soe.shipping_fee,so.status,so.op_status from pa_store_order
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
        
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_store_order_detail($order_id, $order_info);
        Lang::load(lang_file('admin/store_order'));
        
        $this->assign('app',APP);
        $this->assign('order',$order_info);
        $this->assign('image_url',IMAGE_URL);
        $this->assign('order_detail',$order_detail['data']);
        $this->display('store_order.view.html');
    }
    //渠道审核订单信息---确定订单预付金额
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
        
        $qiankuan_money = floatval(trim($_POST['qiankuan_amount'])); //欠款金额
        
        if ($qiankuan_money > 0 && $qiankuan_money <= $order_info['order_amount'])
        {
	        $data['pay_amount']      = $order_info['order_amount'] - $qiankuan_money; //实付金额
	        $data['arrears_amount']  = $qiankuan_money; 
        }elseif($qiankuan_money == 0) 
        {
        	$data['pay_amount']  = $order_info['order_amount'];
        }else
        {
        	$this->show_warning('订单欠款金额输入错误！');
            return;
        }
        
        $data['op_status'] = 2;
        
		$this->_store_order_mod->edit($order_id,$data);
		$this->show_message('审核订单信息成功！',"返回列表",'index.php?app=channel&act=store_order');
    }
	public function _get_member_count($user_id)
	{
		$all_amount = $this->_member_mod->getRow("SELECT SUM(goods_amount) as all_amount from pa_order where status in (20,30,40) and buyer_id=".$user_id);
		$this->assign('amount',$all_amount);
		$this->assign('achievement',ACHIEVEMENT);
	}
}
?>
