<?php
	class ServiceApp extends BackendApp
	{
		var $_goods_mod;
		var $_gcategory_mod;
		var $_user_mod;
		var $_order_mod;
		var $_member_mod;
		function __construct()
		{
			$this->ServiceApp();
		}
		function ServiceApp()
		{
			parent::__construct();
			$this->_goods_mod = &m('goods');
			$this->_gcategory_mod = &m('gcategory');
			$this->_user_mod =& m('member');
			$this->_order_mod = &m('order');
			$this->_member_mod = &m('member');
		}
		//商品管理
		function index()
		{
			$conditions = '1 =1 ';
	    	$conditions .= $this->_get_query_conditions(array(
	            array(
	                'field' => 'goods_name',
	                'equal' => 'like',
	            ),
	            array(
	                'field' => 'brand',
	                'equal' => 'like',
	            ),
	        ));
	        // 分类
	        $cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
			if ($cate_id > 0)
	        {    
	            $cate_ids = $this->get_descendant_ids($cate_id);
	            $conditions .= " AND g.cate_id" . db_create_in($cate_ids);
	        }
	        //更新排序
	        if (isset($_GET['sort']) && isset($_GET['order']))
	        {
	            $sort  = strtolower(trim($_GET['sort']));
	            $order = strtolower(trim($_GET['order']));
	            if (!in_array($order,array('asc','desc')))
	            {
	             $sort  = 'goods_id';
	             $order = 'desc';
	            }
	        }
	        else
	        {
	            $sort  = 'goods_id';
	            $order = 'desc';
	        }
	        $page = $this->_get_page(20);	       
	        $page['item_count'] = $this->_goods_mod->getOne('select count(*) from (select count(*) from pa_goods g left join pa_store_goods sg on g.goods_id = sg.goods_id where g.status = 1 and '.$conditions.' and g.closed = 0 and g.if_show = 1 group by g.goods_id ) aa ');		
	        $goods_list = $this->_goods_mod->getAll('SELECT g.goods_id,g.goods_name,g.brand,g.cate_name,g.smimage_url,sg.gs_id FROM pa_goods g left join pa_store_goods sg on g.goods_id = sg.goods_id  WHERE ' .$conditions.' and g.status = 1 and g.closed = 0 and g.if_show = 1 group by g.goods_id  limit '.$page['limit']);
	        foreach ($goods_list as $key => $goods)
	        {
	            $goods_list[$key]['cate_name'] = $this->_goods_mod->format_cate_name($goods['cate_name']);
	        }
	        $this->assign('goods_list', $goods_list);
	        $this->_format_page($page);
	        $this->assign('page_info', $page);
	        // 第一级分类
	        $this->assign('gcategories', $this->_gcategory_mod->get_all_options(0));
	        $this->import_resource(array('script' => 'mlselection.js,inline_edit.js'));
	        $this->assign('imgurl', IMAGE_URL);
			$this->display('service.index.html');
		}
		//会员信息
		function user()
		{		
	        $conditions = $this->_get_query_conditions(array(
	            array(
	                'field' => $_GET['field_name'],
	                'name'  => 'field_value',
	                'equal' => 'like',
	            ),
	        ));       
	        //更新排序
	        if (isset($_GET['sort']) && !empty($_GET['order']))
	        {
	            $sort  = strtolower(trim($_GET['sort']));
	            $order = strtolower(trim($_GET['order']));
	            if (!in_array($order,array('asc','desc')))
	            {
	             $sort  = 'user_id';
	             $order = 'asc';
	            }
	        }
	        else
	        {
	            if (isset($_GET['sort']) && empty($_GET['order']))
	            {
	                $sort  = strtolower(trim($_GET['sort']));
	                $order = "";
	            }
	            else
	            {
	                $sort  = 'user_id';
	                $order = 'asc';
	            }
	        }
	        $page = $this->_get_page();
	        $users = $this->_user_mod->find(array(
	            'join' => 'has_store,manage_mall',
	            'fields' => 'this.*,store.store_id,userpriv.store_id as priv_store_id,userpriv.privs',
	            'conditions' => '1=1' . $conditions,
	            'limit' => $page['limit'],
	            'order' => "$sort $order",
	            'count' => true,
	        ));
	        foreach ($users as $key => $val)
	        {
	            if ($val['priv_store_id'] == 0 && $val['privs'] != '')
	            {
	                $users[$key]['if_admin'] = true;
	            }
	        }
	        $this->assign('users', $users);
	        $page['item_count'] = $this->_user_mod->getCount();
	        $this->_format_page($page);
	        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
	        $this->assign('page_info', $page);
	        /* 导入jQuery的表单验证插件 */
	        $this->import_resource(array(
	            'script' => 'jqtreetable.js,inline_edit.js',
	            'style'  => 'res:style/jqtreetable.css'
	        ));
	        $this->assign('query_fields', array(
	            'user_name' => LANG::get('user_name'),
	            'email'     => LANG::get('email'),
	            'real_name' => LANG::get('真实姓名'),
	            'mobile' => LANG::get('mobile'),
	        ));
	        $this->assign('sort_options', array(
	            'reg_time DESC'   => LANG::get('注册时间'),
	            'last_login DESC' => LANG::get('最后登录'),
	            'logins DESC'     => LANG::get('登录次数'),
	        )); 
			$this->display('service.user.html');
		}
		//订单管理
		function order()
		{
	        $search_options = array(
	            'seller_name'   => Lang::get('store_name'),
	            'buyer_name'   => Lang::get('buyer_name'),
	            'payment_name'   => Lang::get('payment_name'),
	            'order_sn'   => Lang::get('order_sn'),
	        );
	        /* 默认搜索的字段是店铺名 */
	        $field = 'seller_name';
	        $status=empty($_GET['status']) ? 11 : intval($_GET['status']);
	        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
	        $conditions = $this->_get_query_conditions(array(array(
	                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
	                'equal' => 'LIKE',
	                'name'  => 'search_name',
	            ),array(
	                'field' => 'status',
	                'equal' => '=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'add_time',
	                'name'  => 'add_time_from',
	                'equal' => '>=',
	                'handler'=> 'gmstr2time',
	            ),array(
	                'field' => 'add_time',
	                'name'  => 'add_time_to',
	                'equal' => '<=',
	                'handler'   => 'gmstr2time_end',
	            ),array(
	                'field' => 'order_amount',
	                'name'  => 'order_amount_from',
	                'equal' => '>=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'order_amount',
	                'name'  => 'order_amount_to',
	                'equal' => '<=',
	                'type'  => 'numeric',
	            ),
	        ));
	        $page   =   $this->_get_page(10);    //获取分页信息
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
	        $orders = $this->_order_mod->find(array(
	            'conditions'    => '1=1 ' . $conditions,
	            'limit'         => $page['limit'],  //获取当前页的数据
	            'order'         => "$sort $order",
	            'count'         => true             //允许统计
	        )); //找出所有商城的合作伙伴
	        $page['item_count'] = $this->_order_mod->getCount();   //获取统计的数据
	        $this->_format_page($page);
	        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
	        $this->assign('order_status_list', array(
	            ORDER_PENDING => Lang::get('等待买家付款'),
	            ORDER_ACCEPTED => Lang::get('买家已付款'),
	            ORDER_SHIPPED => Lang::get('卖家已发货'),
	            ORDER_FINISHED => Lang::get('交易成功'),
	            ORDER_REFUND => Lang::get('退款中'),
	            ORDER_REFUND_FINISH => Lang::get('退款完成'),
	            ORDER_CANCELED => Lang::get('交易取消'),
	        ));
	        $this->assign('search_options', $search_options);
	        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
	        $this->assign('orders', $orders);
	        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
	                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
			$this->display('service.order.html');
		}
		//短信发送
		function send()
		{		  
			$id = empty($_GET['id']) ? '' : intval($_GET['id']);
			if(!id)
			{
				$this->show_message('');
				return;
			}else{
				$user = $this->_member_mod->getRow('select * from pa_member where user_id ='.$id);
			}		
			if(!IS_POST)
			{	
				$this->assign('user',$user);
				$this->display("service.send.html");
			}else{
				$contnet = empty($_POST['content']) ? '' : trim($_POST['content']);
				$smslog =&  m('smslog'); 
		      	import('class.smswebservice');    //导入短信发送类
		   		$sms = SmsWebservice::instance(); //实例化短信接口类
			    $smscontent = $contnet;
			    $mobile = $user['mobile'];
			    $verifytype = "system";	
		      	$result= $sms->SendSms2($mobile,$smscontent); //执行发送短信提醒操作
		      	//短信发送成功
		        if ($result == 0) 
		        {
		        	$time = time();
		        	//执行短信日志写入操作
		        	$smsdata['mobile'] = $mobile;
		        	$smsdata['smscontent'] = $smscontent;
		        	$smsdata['type'] = $verifytype; //短信提醒
		        	$smsdata['sendtime'] = $time;
		        	$smsdata['user_id'] = $id;
		       		$smslog->add($smsdata);
		       		$this->show_message('短信发送成功!',
		       		'返回','index.php?app=service&act=user');
		       	}
			}  
		}
		function get_descendant_ids($id)
	    {
	        $res = array($id);     
	            $cids = array($id);
	            while (!empty($cids))
	            {
	                $sql  = "SELECT cate_id FROM pa_gcategory WHERE parent_id " . db_create_in($cids);
	                $cids = $this->_gcategory_mod->getCol($sql);
	                $res  = array_merge($res, $cids);
	            }
	        return $res;
	    }
	}
?>