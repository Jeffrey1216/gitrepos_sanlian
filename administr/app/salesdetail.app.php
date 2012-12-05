<?php

/**
 *    商品销售明细
 *
 *    @author wscsky
 */
class SalesDetailApp extends BackendApp
{
	var $_order_mod;

	function __construct(){
		$this->SalesDetailApp();
	}
 	function SalesDetailApp(){
    	parent::__construct();
    	$this->_order_mod=& m('ordergoods');
    }
    
    function index(){
        $page_per = 20;
        $page = $this->_get_page($page_per); 
        $store_type = isset($_GET['store_type'])?intval($_GET['store_type']):-1;
        $otype= isset($_GET['otype'])?intval($_GET['otype']):-1;
        
        $order_type= isset($_GET['order_type'])?intval($_GET['order_type']):1;
        
        $ufield = isset($_GET['ufield'])? trim($_GET['ufield']):"buyer_name";
        $ufield == "consignee" || $ufield == "phone_tel" || $ufield == "phone_mob" || $ufield = "buyer_name";
        if($ufield=='buyer_name'){
            $ufield = "po.".$ufield;
        }else{
            $ufield = "poe.".$ufield;            
        }
        
        $gfield = isset($_GET['gfield'])? trim($_GET['gfield']):"goods_name";
        $gfield == "commodity_code" || $gfield="goods_name";
        if($gfield == "commodity_code")
            {
                $gfield='pog.'.$gfield;
                $gfield_equal = '=';
                $gfield_handler = "trim";
            
            }
        if($gfield == "goods_name"){
                $gfield='pog.'.$gfield;
                $gfield_equal = 'like';
                $gfield_handler = "trim";
            }
        
        $otime = isset($_GET['otime'])?trim($_GET['otime']):"pay_time";
        $otime == "finished_time" || $otime =="add_time" || $otime = "pay_time";  
    
        $conditions = " where 1=1 ";
        $conditions .= $this->_get_query_conditions(array(
            array(
                'field'     =>  $ufield,
                'name'      =>  'userkey',
                'equal'     =>  '=',
                'handler'   =>  'trim'
            ),
            array( 
                'field'     =>  $gfield,
                'name'      =>  'goodkey',
                'equal'     =>  $gfield_equal,
                'handler'   =>  $gfield_handler
            
            ),
            array(
                'field' => 'po.'.$otime,
                'name'  => 'time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
                ),
            array(
                'field' => 'po.'.$otime,
                'name'  => 'time_to',
                'equal' => '<=',
                'handler'=> 'gmstr2time_end',
                ),
        ));
        if($otype==0||$otype==1){
          $conditions .=' And po.type='.$otype;  
        }
      
        switch($order_type){
            case 2:
                $orderstr = " order by pog.quantity desc,po.".$otime." desc ";
                break;
            default:
                $orderstr = " order by po.".$otime." desc ";
                break;            
        }
        
        $fields = "poe.consignee,poe.address,poe.phone_tel,poe.phone_mob,po.order_sn,po.buyer_id,po.type,po.buyer_name,po.seller_id,po.seller_name,".$ufield." as userinfo,po.".$otime." as showtime,po.pay_time,pog.*";
        $sql = " from pa_order_goods pog
                left join pa_order po on pog.order_id = po.order_id 
                left join pa_order_extm poe on poe.order_id = pog.order_id ";                  
        if($store_type!=-1){
            $sql .=" left join pa_store ps on po.seller_id = ps.store_id " ;
            $conditions .=' And ps.store_type='.$store_type; 
        }
        $page['item_count'] = $this->_order_mod->getOne("select count(0) ".$sql.$conditions);        
        
        $this->_format_page($page);
        //echo "<pre>"."select ".$fields.$sql.$conditions.$orderstr." limit ".$page['limit']."</pre>";
        $goods_list = $this->_order_mod->getAll("select ".$fields.$sql.$conditions.$orderstr." limit ".$page['limit']);
        foreach($goods_list as $key => &$v){
            $v['amount']=$v['quantity']*$v['price'];
            $v['tichen']=format_money($v['credit']/2);
            $v['profit']=format_money($v['amount']-$v['tichen']-$v['credit']-$v['zprice']);                
        }
        //查询参数设置
        $o_type = array(
            '-1'    =>   "全部订单",
            '0'     =>   "线上订单",
            '1'     =>   "线下订单",
        );
        $user_fields= array(
            "buyer_name" => "帐户名",
            "consignee" => "姓名",
            "phone_tel" => "电话",
            "phone_mob" => "手机",
        );
       $good_fields = array(
            "goods_name"     => "商品名称",
            "commodity_code"  => "商品编号",
        ); 
       $order_fields = array(
            "pay_time"      =>  "付款时间",
            "finished_time" =>  "完成时间",
            "add_time"      =>  "下单时间",
       );
       $ordertype = array(
            "1" => "时间排序↓",
            "2" => "销量排序↓",
       );
        $this->assign('stype',$store_type);
        $this->assign('o_type',$o_type);
        $this->assign('user_fields', $user_fields);
        $this->assign('good_fields', $good_fields);
        $this->assign('order_fields', $order_fields);
        $this->assign('ordertype',$ordertype);
        $this->assign('page_info', $page);
        $this->assign('goods_list',$goods_list);
        $this->display("Sales.Detail.index.html");

    }
}
?>
