<?php
	class User_memageApp extends BackendApp
	{
		var $_user_mod;
		var $_credit_verify_mod;
		var $_member_mod;
		function __construct()
		{
			$this->User_memageApp();
		}
		function User_memageApp()
		{
			parent::__construct();
			$this->_user_mod = &m('member');
			$this->_credit_verify_mod = &m('creditverify');
			$this->_member_mod = &m('member');
		}
		//管理
		function index()
		{
			if(!IS_POST)
			{
				$search_options = array(
	            'user_name'   => Lang::get('会员名'),
	            'real_name'   => Lang::get('真实姓名'),
	            'phone_mob'   => Lang::get('手机'),
		        );
		        /* 默认搜索的字段是店铺名 */
		        $field = 'seller_name';
		        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
		        //按用户名,店铺名,支付方式名称进行搜索
		        $conditions = $this->_get_query_conditions(array(array(
		                'field' => $field,       
		                'equal' => 'LIKE',
		                'name'  => 'search_name',
		            ),array(
		                'field' => 'frozen_money',
		                'name'  => 'order_amount_from',
		                'equal' => '>=',
		                'type'  => 'numeric',
		            ),array(
		                'field' => 'frozen_money',
		                'name'  => 'order_amount_to',
		                'equal' => '<=',
		                'type'  => 'numeric',
		            ),array(
		                'field' => 'frozen_credit',
		                'name'  => 'frozen_credit_from',
		                'equal' => '>=',
		                'type'  => 'numeric',
		            ),array(
		                'field' => 'frozen_credit',
		                'name'  => 'frozen_credit_to',
		                'equal' => '<=',
		                'type'  => 'numeric',
		            ),array(
		                'field' => 'reg_time',
		                'name'  => 'add_time_from',
		                'equal' => '>=',
		                'handler'=> 'gmstr2time',
		            ),array(
		                'field' => 'reg_time',
		                'name'  => 'add_time_to',
		                'equal' => '<=',
		                'handler'   => 'gmstr2time_end',
		            ),           
	       			));
				//更新排序
				if(isset($_GET['sort']) && !empty($_GET['order']))
				{
					$sort = strtolower(trim($_GET['sort']));
					$order = strtolower(trim($_GET['order']));
					if(!in_array($order,array('asc','desc')))
					{
						$sort = 'user_id';
						$order = 'asc';
					}
				}else{
					if(isset($_GET['sort']) && empty($_GET['order']))
					{
						$sort = strtolower(trim($_GET['sort']));
						$order = "";
					}else{
						$sort = 'user_id';
						$order = 'asc';
					}
				}
				$page = $this->_get_page(20);			
				$users = $this->_user_mod->find(array(
						'fields' => 'this.*',
						'conditions' => '1=1'.$conditions,
						'limit' => $page['limit'],
						'order' => "$sort $order",
						'count' => true,
				));
				foreach($users as $key=>$val)
				{
					if($val['priv_store_id'] == 0 && $val['privs'] !='')
					{
						$users[$key]['if_admin'] = true;
					}
				}
				$page['item_count'] = $this->_user_mod->getCount();
				$this->assign('search_options',$search_options);
				$this->assign('users',$users);
				$this->_format_page($page);
				$this->assign('filtered',$conditions ? 1 : 0);//是否有查询条件			
				$this->assign('page_info',$page);
				/* 导入jQuery的表单验证插件 */
		        $this->import_resource(array(
		            'script' => 'jqtreetable.js,inline_edit.js',
		            'style'  => 'res:style/jqtreetable.css'
		        ));
		        $this->display('user_memage.index.html');	
			}else{		
				$type = change_type();
				$this->assign('credit_type',$type);
				$ids = empty($_POST['ids']) ? 0 : $_POST['ids'];
				if($ids == 0)
				{
					$this->show_warning('未选中任何会员!');
					return;
				}
				$id_str = '';
				if(is_array($ids))
				{
					foreach($ids as $k=>$v)
					{
						$id_str .=$v .",";
					}
					$ids_str = substr($id_str,0,-1);
					$user_list = $this->_user_mod->getAll("select m.user_id,m.user_name from pa_member m where m.user_id in (" . $ids_str .")"); 
					$this->assign('users',$user_list);
				}else{
					$this->show_warning("程序出错！");
					return;
				}
				$this->display('user_memage.form.html');
			}		
		}
		function send()
		{
			$ids = empty($_POST['ids']) ? 0 : $_POST['ids'];
			$credit = empty($_POST['credit']) ? 0 : floatval($_POST['credit']);
			$type= intval($_POST['type']); 
			$desc = empty($_POST['desc']) ? '' : trim($_POST['desc']);
			if(!$ids)
			{
				$this->show_warning('操作出错！');
				return;
			}	
			if($credit <= 0 || $credit == '')
			{
				$this->show_warning('请填写正确的变动数目!');
				return;
			}
			if($type == '' || $type == null)
			{
				$this->show_warning('请选择变动类型！');
				return;
			}
			if($desc == '')
			{
				$this->show_warning('请填写备注!');
				return;
			}
			$user_id = $this->visitor->get('user_id');
			$user_info = $this->_user_mod->get($user_id);
			if(!empty($user_info['real_name']))
			{
				$operator = $user_info['real_name'];
			}else {
				$operator = $user_info['user_name'];
			}
			if(is_array($ids)) 
			{
		    	foreach($ids as $k => $v) 
			    {
			    	if($type == 8 || $type == 9 || $type ==16 || $type ==17)
			    	{
			    		/* 编写积分记录,返还积分  */
				        $param = array(
				        	'money' => $credit,
				        	'credit' => 0,
				            'verify' => 0,
				            'user_id'			=> $v,
				            'notes'			=> $type,
				            'add_time'	=>	time(),
				        	'remark' => $desc,
				        	'operator'  => $operator
				        );
				        addCreditVerify($param);
			    	}else{
			    		/* 编写积分记录,返还积分  */
				        $param = array(
				        	'credit' => $credit,
				        	'money' => 0,
				            'verify' => 0,
				            'user_id'			=> $v,
				            'notes'			=> $type,
				            'add_time'	=>	time(),
				        	'remark' => $desc,
				        	'operator'  => $operator
				        );
				        addCreditVerify($param);
			    	}
			    }
			} else {
        		$this->show_warning("程序出错!");
	    		return;
        	}	
    		$this->show_message('会员账户变动成功,请耐心等待审核!','返回','index.php?app=user_memage');		
		}
		//单条账户变动
		function change()
		{
			$type = change_type();
			$this->assign('credit_type',$type);	
			$id = empty($_GET['id']) ? 0: trim($_GET['id']);
			if(!$id)
			{
				$this->show_warning('该会员不存在!');
				return;
			}
			$user_list = $this->_user_mod->getRow("select m.user_id,m.user_name from pa_member m where m.user_id =".$id); 
			$this->assign('index',1);
			$this->assign('users',$user_list);
			$this->display('user_memage.form.html');
		}
		//账户详情
		function details()
		{
			$user_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
			$chan_type = $_GET['change_type'];
			$add_time_form = strtotime($_GET['add_time_from']);
	    	$add_time_to = strtotime($_GET['add_time_to']);
			$add_time_to_new = $add_time_to + 86399;
	    	if(0 == $user_id)
	    	{
	    		$this->show_message("用户ID不能为空");
	    		return ;
	    	}
	    	$this->assign('id',$user_id);
	    	$user_mod = & m('member');
	    	$member_info = $user_mod->get($user_id); 
	    	$page = $this->_get_page(20);
			$conditions = " where m.user_id= ".$user_id;
	    	if($chan_type)
	    	{	
	    		if($chan_type == "amount")
	    		{
	    			$conditions .= " and al.change_type in (50,51,52,53,58)";	
	    		}else {
	    			$conditions .= " and al.change_type=".$chan_type;
	    		}
	    	}
	    	if(!empty($_GET['add_time_from']) && !empty($_GET['add_time_to']))
	    	{
	    		$conditions .= " and al.change_time >".$add_time_form." and al.change_time < ".$add_time_to_new;
	    	}elseif (!empty($_GET['add_time_from']) || !empty($_GET['add_time_to']))
	    	{
	    		if(empty($_GET['add_time_from']))
	    		{
	    			$conditions .= " and al.change_time < ".$add_time_to_new;
	    		}else {
	    			$conditions .= " and al.change_time > ".$add_time_form;
	    		}
	    	}
	    	$sql = 'SELECT al.*,o.order_sn,o.order_id from pa_member m left join pa_account_log al on al.user_id=m.user_id left join pa_order o on o.order_id=al.order_id '.$conditions.' order by al.change_time desc limit '.$page['limit'];
	    	$user_info = $user_mod->getAll($sql);
	    	$change_type = get_change_type();
			foreach ($user_info as $k=>$v)
			{
				$user_info[$k]['change_type'] = $change_type[$v['change_type']];
				$user_info[$k]['change_time'] = $v['change_time'];
			}
	    	$user_count = $user_mod->getRow('SELECT SUM(al.user_credit) as amount_user_credit,SUM(al.frozen_credit) as amount_frozen_credit,SUM(al.user_money) as amount_user_money,SUM(al.frozen_money) as amount_frozen_money,count(*) as count from pa_member m left join pa_account_log al on al.user_id=m.user_id left join pa_order o on o.order_id=al.order_id'.$conditions);
	        $page['item_count'] = $user_count['count'];        
	        $this->_format_page($page);
	        $this->assign('change_type',$change_type);
	        $this->assign('page_info', $page);
	        $this->assign('user_count',$user_count);
	    	$this->assign('member',$member_info);
	    	$this->assign('users',$user_info);
	    	$this->display('user_log.html');
		}
	}
?>