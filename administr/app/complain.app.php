<?php
	class ComplainApp extends BackendApp
	{
		var $_complain_mod;
		function __construct()
		{
			$this->ComplainApp();
		}
		function ComplainApp()
		{
			parent::__construct();
			$this->_complain_mod = &m('complain');
		}
		//管理
		function index()
		{
			$conditions = '1=1';
			$conditions .= $this->_get_query_conditions(array(array(
						'field' => 'o.order_sn',
						'equal' => '=',
						'name'  => 'order_sn',
				),array(
						'field' => 'c.status',
						'equal' => '=',
						'name'  => 'type',
				),
			));
			$data = complain_type();
			$page = $this->_get_page(20);
			$complain = $this->_complain_mod->getAll('select *,o.order_sn,c.status as c_status from pa_complain c left join pa_order o on c.order_id = o.order_id where '.$conditions .' limit '.$page['limit']);
			$page['item_count'] = $this->_complain_mod->getOne('select count(*) from pa_complain c left join pa_order o on c.order_id = o.order_id where '.$conditions);
			foreach($complain as $k => $v)
			{
				$complain[$k]['complain_type'] = $data[$complain[$k]['complain_type']];
			}
			$this->assign('complain_info',$complain);
			$this->_format_page($page);
			$this->assign('page_info',$page);
			$this->assign('type',array(
						'1' => '待处理',
						'2' => '已解决',
			));
			$this->display('complain.index.html');
		}
		//查看回复
		function view()
		{
			$id = empty($_GET['id']) ? '' : intval($_GET['id']);
			if(!$id)
			{
				$this->show_warning('该投诉不存在!');
				return;
			}
			$data = complain_type();
			$complain = $this->_complain_mod->getRow('select *,c.status as c_status,c.add_time as c_add_time from pa_complain c left join pa_order o on c.order_id = o.order_id where c.complain_id ='.$id);
			$complain['complain_type'] = $data[$complain['complain_type']];
			$this->assign('complain_info',$complain);
			if(!IS_POST)
			{
				$this->display('complain.view.html');
			}else{
				$data = array();
				$reason = empty($_POST['reason']) ? '': trim($_POST['reason']);
				if(!$reason)
				{
					$this->show_warning('请填写回复!');
					return;
				}else{
					$data['reply_content'] = $reason;
				}
				$data['reply_time'] = time();
				$data['status'] = 2;
				if(!$this->_complain_mod->edit($id,$data))
				{
					$this->show_warning($this->_complain_mod->get_error());
					return;
				}else{
					$this->show_message('回复成功','返回','index.php?app=complain');
				}
			}		
		}
	}
?>