<?php
	class User_viewApp extends BackendApp
	{
		var $_account_log_mod;
		var $_user_mod;
		function __construct()
		{
			$this->User_viewApp();
		}
		function User_viewApp()
		{
			parent::__construct();
			$this->_account_log_mod = &m('accountlog');
			$this->_user_mod = &m('member');
		}
		//管理
		function index()
		{
			//搜索
			$conditions = "1=1";
			$search_options = array(
            'm.user_name'   => Lang::get('会员名'),
            'm.real_name'   => Lang::get('真实姓名'),
       		);
	        /* 默认搜索的字段是店铺名 */
	        $field = 'seller_name';
	        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
			$conditions = '1=1';
        	$conditions .= $this->_get_query_conditions(array(array(
                'field' => $field,       
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'al.change_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'al.change_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'al.user_money',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'al.user_money',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ), array(
                'field' => 'al.user_credit',
                'name'  => 'frozen_credit_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'al.user_credit',
                'name'  => 'frozen_credit_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),          
       		));
			$type = get_change_type();
			//分页条数
			$page = $this->_get_page(20);
			$page['item_count'] = $this->_account_log_mod->getOne("select count(*) from pa_account_log al left join pa_member m on al.user_id = m.user_id where " .$conditions);
			$member_info  = $this->_account_log_mod->getAll("select al.*,m.user_name,m.real_name,m.email from pa_account_log al left join pa_member m on al.user_id = m.user_id
															 where ".$conditions. " order by al.change_time desc limit " .$page['limit']);
			foreach($member_info as $k=>$v)
			{
				$member_info[$k]['change_type'] = $type[$v['change_type']];
			}
			$this->_format_page($page);
			$this->assign('search_options',$search_options);
			$this->assign('page_info',$page);
			$this->assign("member_info",$member_info);
			$this->display('user_view.index.html');
		}
	}
?>