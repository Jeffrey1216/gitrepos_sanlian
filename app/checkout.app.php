<?php
/* ����ר�� */
class checkoutApp extends StoreadminbaseApp
{
	/**
	 *  Ʒ���̳ǽ���
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
     *    ��ȡ���궨��
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
    	//��ѯ����ʱ�䣬���û�н���ʱ�䣬Ĭ�ϵ�ǰʱ��
    	$endtime = empty($_GET['endtime']) ? time() : strtotime($_GET['endtime']);
    	//ȡ����ѯ��������ѯ����Ӫҵ��    	
    	$conditions = " Seller_id=".$store_id ." AND status='40' AND add_time>".$starttime." AND add_time<".($endtime);
    	$page = $this->_get_page();
        $model_order =& m('order');
         /* ���Ҷ��� */
        $orders = $model_order->findAll(array(
            'conditions'    => $conditions,
            'count'         => true,
            'join'          => 'has_orderextm',
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
            'include'       =>  array(
                'has_ordergoods',       //ȡ����Ʒ
            ),
        ));
        $total_orders = $model_order->findAll(array(
            'conditions'    => $conditions,
            'join'          => 'has_orderextm',
            'include'       =>  array(
                'has_ordergoods',       //ȡ����Ʒ
            ),
        ));
        $total_amount = 0;//��Ӫҵ��
        $total_usecredit = 0;//��ʹ�û���
        $total_getcredit = 0;//�ܻ�û���
        foreach ($total_orders as $key1 => $torder) {
        	$total_amount += floatval($torder['goods_amount']);
			$total_usecredit += floatval($torder['use_credit']);
			$total_getcredit += floatval($torder['get_credit']);		
        }
        //��Ӫҵ��Ϣ
		$totalinfo = array(
			amount => $total_amount, //��Ʒ�ܼۡ�
			usecredit => $total_usecredit, //ʹ�û����ܼۡ�
			getcredit => $total_getcredit, //��û����ܼۡ�	
			money => $total_amount - $total_usecredit, //ʹ���ֽ�= ��Ʒ�ܼ� - ʹ�û��֡�
		);
		$item_count = $model_order->getOne("SELECT COUNT(*) FROM pa_order WHERE ".$conditions);
		$page['item_count'] = $item_count;
        $this->_format_page($page);
		$this->assign('totalinfo',$totalinfo);
        $this->assign('orders',$orders);
        $this->assign('page_info', $page);
    }
}