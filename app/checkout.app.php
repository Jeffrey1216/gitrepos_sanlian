<?php
/* 派啦专柜 */
class checkoutApp extends StoreadminbaseApp
{
	/**
	 *  品牌商城结算
	 */
	function index()
	{
		$this->_get_orders();
		$this->display('storeadmin.checkout.index.html');
	}
	public function checkoutview() {	
		$this->display('storeadmin.checkout.view.html');
	}
	/**
     *    获取本店定单
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_orders()
    {
    	//var_dump($_GET);
    	$store_id = $this->visitor->get('store_id');
    	date_default_timezone_set('PRC');
    	$defstarttime = mktime(0,0,0,date('m'),date('d'),date('Y'));
    	$defendtime = mktime(23,59,59,date('m'),date('d'),date('Y'));
    	$starttime = empty($_GET['starttime']) ? intval($defstarttime) : strtotime($_GET['starttime']);
    	//查询结束时间，如果没有结束时间，默认当前时间
    	$endtime = empty($_GET['endtime']) ? time() : strtotime($_GET['endtime']);
    	//取出查询条件，查询本店营业额    	
    	$conditions = " Seller_id=".$store_id ." AND status='40' AND add_time>".$starttime." AND add_time<".($endtime);
    	$page = $this->_get_page();
        $model_order =& m('order');
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
        $total_amount = 0;//总营业额
        $total_usecredit = 0;//总使用积分
        $total_getcredit = 0;//总获得积分
        foreach ($total_orders as $key1 => $torder) {
        	$total_amount += floatval($torder['goods_amount']);
			$total_usecredit += floatval($torder['use_credit']);
			$total_getcredit += floatval($torder['get_credit']);		
        }
        //总营业信息
		$totalinfo = array(
			amount => $total_amount, //商品总价。
			usecredit => $total_usecredit, //使用积分总价。
			getcredit => $total_getcredit, //获得积分总价。	
			money => $total_amount - $total_usecredit, //使用现金= 商品总价 - 使用积分。
		);
		$item_count = $model_order->getOne("SELECT COUNT(*) FROM pa_order WHERE ".$conditions);
		$page['item_count'] = $item_count;
        $this->_format_page($page);
		$this->assign('totalinfo',$totalinfo);
        $this->assign('orders',$orders);
        $this->assign('page_info', $page);
    }
}