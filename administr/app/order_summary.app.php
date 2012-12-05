<?php

/*
 * 会员订单 控制器
 * @author zhufuqing
 */

class Order_summaryApp extends BackendApp {

    var $store_order_goods_mod;
    var $store_order_mod;
    var $store_mod;
    var $goods_mod;
    var $_store_order_mod;
    var $_store_order_log_mod;
    var $_store_order_extm_mod;

    function __construct() {
        $this->Order_summaryApp();
    }

    function Order_summaryApp() {
        parent::__construct();
        $this->store_mod = &m('store');
        $this->goods_mod = &m('goods');
        $this->store_order_mod = &m('store');
        $this->store_order_goods_mod = &m('storeordergoods');
        $this->_store_order_mod = & m('storeorder');
        $this->_store_order_log_mod = & m('storeorderlog');
        $this->_store_order_extm_mod = & m('storeorderextm');
    }

    //管理
    function index() {
        $this->collection();  //默认显示店铺进货单
    }

    //订单查看
    function Store_statistics_view() {
        $id = intval($_GET['id']);
        $order_info = $this->store_order_mod->getRow('select * from pa_store_order so left join pa_store_order_extm soe on so.order_id = soe.order_id where so.order_id =' . $id);
        $this->assign('order_info', $order_info);
        $goods_list = $this->store_order_goods_mod->getAll('select sog.goods_name,sog.goods_image,sog.goods_id,gs.spec_1,gs.spec_2,sog.zprice,sog.quantity from 
																pa_store_order_goods sog left join pa_store_order so on sog.order_id = so.order_id left join pa_goods_spec
																 gs on sog.spec_id = gs.spec_id where so.order_id =' . $id);

        $this->assign('goods_list', $goods_list);
        $this->display('store_statistics.view.html');
    }

    //加盟店零售统计
    function collection() {
        $search_options = array(
            'o.buyer_name' => Lang::get('会员名称'),
            'm.mobile' => Lang::get('手机号'),
        );
        $time_search_options = array(
            'o.pay_time' => Lang::get('付款时间'),
            'o.add_time' => Lang::get('下单时间'),
            'o.finished_time' => Lang::get('完成时间'),
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'buyer_name';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        array_key_exists($_GET['timeField'], $time_search_options) ? $timeField = $_GET['timeField'] : $timeField = 'o.pay_time';
		empty($store_type) && $store_type = '0';
        //按用户名,店铺名,支付方式名称进行搜索
        $conditions = 'status>=20 and status<=50 and o.buyer_id <>0';
        $conditions .= $this->_get_query_conditions(
                array(array(
                        'field' => $field,
                        'equal' => 'LIKE',
                        'name' => 'search_name',
                    ), array(
                        'field' => $timeField,
                        'name' => 'finished_time_from',
                        'equal' => '>=',
                        'handler' => 'gmstr2time',
                    ), array(
                        'field' => $timeField,
                        'name' => 'finished_time_to',
                        'equal' => '<=',
                        'handler' => 'gmstr2time_end',
                ),array(
					'field' => 'store_type',
					'equal' => '=',
					'type' => 'numeric',
			    ),)
        );

        //分页条数
        $page = $this->_get_page(20);
        $sql = "select o.buyer_id,o.buyer_name,m.real_name,m.mobile,o.order_id,
					SUM(og.quantity) AS sum_quantity,
					sum(og.zprice * og.quantity) AS sum_zprice,
					price * SUM(og.quantity) AS sum_price,
					SUM(og.quantity) * og.credit as sum_credit,
					(SUM(quantity) * og.credit)/2 as member_cate,
					((price - zprice) * SUM(quantity) - SUM(quantity) * og.credit - SUM(quantity) * og.credit/2 ) as member_obtain 
					from pa_order o 
					left join pa_member m on m.user_id = o.buyer_id 
					left join pa_order_goods og on o.order_id = og.order_id where "
                . $conditions . " group by o.buyer_id";
        //统计总数
        $sql_count = "select count(*) from ({$sql}) tab";
        $page['item_count'] = $this->store_order_mod->getOne($sql_count);
        $orders = $this->store_order_mod->getAll($sql . " limit " . $page['limit']);
        foreach ($orders as $key => &$val) {
		    $sqlOrderCount = "SELECT COUNT(*) FROM ( SELECT og.order_id FROM pa_order_goods og 
							LEFT JOIN pa_order o ON o.order_id = og.order_id  WHERE buyer_id = {$val['buyer_id']} GROUP BY og.order_id ) tab";
			$val['order_count'] = $this->store_order_goods_mod->getOne($sqlOrderCount);
            $val['member_cate'] = format_money($val['member_cate']);
            $val['member_obtain'] = format_money($val['member_obtain']);
        }

        $this->_format_page($page);
        $this->assign('orders', $orders);
        $this->assign('page_info', $page);
        $this->assign('goods_collect', $orders_collect);
        $this->assign('search_options', $search_options);
        $this->assign('time_search_options', $time_search_options);

        $this->display('order_summary.index.html');
    }

    /*
     * 会员订单详情
     */

    function member_collection() {
	    if(empty($_GET['buyer_id']))
		{
			$this->show_warning('会员信息有误！');
			return ;
		}
        $search_options = array(
            's.store_name' => Lang::get('store_name'),
            'o.order_sn' => Lang::get('order_num'),
            'o.buyer_name' => Lang::get('会员'),
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'seller_name';
	    $order_id = $_GET['order_id']; 
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        //按用户名,店铺名,支付方式名称进行搜索
        $conditions = 'status>=20 and status<=50';
        $conditions .= $this->_get_query_conditions(array(array(
                'field' => $field,
                'equal' => 'LIKE',
                'name' => 'search_name',
            ), array(
                'field' => 'o.buyer_id',
				'name'  => 'buyer_id',
                'equal' => '=',
                'type' => 'numeric',
            ),array(
                'field' => 'store_type',
                'equal' => '=',
                'type' => 'numeric',
            ), array(
                'field' => 'status',
                'equal' => '=',
                'type' => 'numeric',
            ), array(
                'field' => 'o.pay_time',
                'name' => 'finished_time_from',
                'equal' => '>=',
                'handler' => 'gmstr2time',
            ), array(
                'field' => 'o.pay_time',
                'name' => 'finished_time_to',
                'equal' => '<=',
                'handler' => 'gmstr2time_end',
            ), array(
                'field' => 'oe.shipping_fee',
                'name' => 'order_amount_from',
                'equal' => '>=',
                'type' => 'numeric',
            ), array(
                'field' => 'o.payment_id',
                'name' => 'payment',
                'equal' => '=',
                'type' => 'numeric',
            ), array(
                'field' => 'oe.shipping_fee',
                'name' => 'order_amount_to',
                'equal' => '<=',
                'type' => 'numeric',
            ),
                ));
	
        //支付类型条件
        if (isset($_GET['paytype'])) {
            switch ($_GET['paytype']) {
                case 1: //现金
                    $conditions .= " and o.pay_type in(1,3,5,7)";
                    $this->assign("total_name", "现金总计");
                    $total_field = "o.cash";
                    break;
                case 2: //PL币
                    $conditions .= " and o.pay_type in(2,3,4,7)";
                    $this->assign("total_name", "PL币总计");
                    $total_field = "o.use_credit";
                    break;
                case 3: //余额
                    $conditions .= " and o.pay_type in(4,5,6,7)";
                    $this->assign("total_name", "余额总计");
                    $total_field = "o.use_money";
                    break;
                default:
                    $this->assign("total_name", "销售总计");
                    $total_field = "o.goods_amount";
                    break;
            }
        } else {
            $this->assign("total_name", "销售总计");
            $total_field = "o.goods_amount";
        }
        //分页条数
        $page = $this->_get_page(20);
        //统计总数
        $page['item_count'] = $this->store_order_mod->getOne('select count(*) from pa_order o left join pa_store s on o.seller_id = s.store_id 
       																left join pa_order_extm oe on o.order_id = oe.order_id where ' . $conditions);
        $paytype = array(
            '1' => '现金',
            '2' => 'PL币',
            //'3' => '现金+PL币',
            //'4' => '余额+PL币',
            //'5' => '现金+余额',
            '3' => '余额',
                //'7' => '现金+余额+PL币'
        );

	    $sql = 'select o.order_id,o.order_sn,o.order_amount,o.goods_amount,o.cash,o.use_money,o.use_credit,
		        (o.cash+o.use_money+o.use_credit) as showmany,o.get_credit,o.seller_name,o.buyer_name,o.status,s.store_type,
			    oe.shipping_fee,o.payment_name,o.pay_time,o.pay_type 
			    from pa_order o left join pa_store s on o.seller_id = s.store_id 
			    left join pa_order_extm oe on o.order_id = oe.order_id where ' . $conditions . ' limit ' . $page['limit'];
        $orders = $this->store_order_mod->getAll($sql);
	

        foreach ($orders as $k => $v) {

            $orders_all = $this->store_order_mod->getRow('select sum(og.gprice * og.quantity) as stock_price,sum(og.zprice * og.quantity) as league_price,
																sum(og.price * og.quantity) as user_price from pa_order o left join pa_order_goods og
																 on o.order_id = og.order_id where o.order_id =' . $v['order_id']);
            $orders[$k]['stock_price'] = $orders_all['stock_price']; //采购价
            $orders[$k]['league_price'] = $orders_all['league_price']; //批发价
            $orders[$k]['income'] = floatval($orders[$k]['get_credit']) * 0.5; //团购员收益
            $orders[$k]['send_pl'] = floatval($orders[$k]['income']) * 0.3; //团购员获得PL
            $orders[$k]['send_money'] = floatval($orders[$k]['income']) * 0.7; //团购员获得余额
            $orders[$k]['store_income'] = $orders[$k]['goods_amount'] - $orders[$k]['league_price'] - $orders[$k]['send_pl'] - $orders[$k]['send_money']; //店铺收益
            $orders[$k]['pl_income'] = $orders[$k]['league_price'] - $orders[$k]['stock_price']; //公司收益
            $orders[$k]['pay_type_name'] = $paytype[$v['pay_type']];
        }


        $total = $this->store_order_mod->getRow('select sum(' . $total_field . ') as total,sum(oe.shipping_fee) as ship_fee,sum(o.get_credit) as gcredit from pa_order o left join pa_store s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id = oe.order_id where
       												  ' . $conditions);

        $totals = $this->store_order_mod->getRow('select *,sum(og.gprice * og.quantity) as gprice_totals,sum(og.zprice * og.quantity) as zprice_totals from
	       												 pa_order o left join pa_store s on o.seller_id = s.store_id left join pa_order_extm oe on o.order_id
	       												  = oe.order_id left join pa_order_goods og on o.order_id = og.order_id where ' . $conditions);
        $total['gprice_total'] = $totals['gprice_totals'];
        $total['zprice_total'] = $totals['zprice_totals'];
        $total['send_deduct'] = $total['gcredit'] * 0.5; //团购员提成
        $total['send_pl'] = $total['send_deduct'] * 0.3; //团购员获取PL
        $total['send_money'] = $total['send_deduct'] * 0.7; //团购总则获取余额

        $total['pl_income'] = $totals['zprice_totals'] - $totals['gprice_totals'];

        $this->assign('total', $total);
        $this->assign('store_type', array(
            '0' => '直营店',
            '1' => '加盟店',
        ));
        $this->assign('status', array(
            '20' => '已付款,待发货',
            '30' => '已发货',
            '40' => '交易成功',
            '50' => '退款中',
        ));
        $this->assign('pay_type', $paytype);
        $this->assign('paymeny_type', $this->_get_payment_type());
        $this->assign('orders', $orders);
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('goods_collect', $orders_collect);
        $this->assign('search_options', $search_options);
        $this->display('store_statistics.member_collection.html');
    }

    /**
     * 读取正在使用的支付方式
      return array()
     */
    function _get_payment_type() {
        $cache_server = &cache_server();
        $payment_type = $cache_server->get('payment_type');
        if ($payment_type === false) {
            $sql = "SELECT payment_id,payment_name from pa_payment where is_online =1";
            $payment_mod = &m("payment");
            $payment = $payment_mod->getAll($sql);
            $payment_type = array();
            foreach ($payment as $v) {
                $payment_type[$v['payment_id']] = $v['payment_name'];
            }
            $payment_type[100] = '线下支付';
            $cache_server->set('payment_type', $payment_type, 3600);
        }
        return $payment_type;
    }

}

?>