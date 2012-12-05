<?php
	class Stock_destoryApp extends BackendApp
	{
		var $_stock_destory_mod;
		var $_store_goods_mod;
		function __construct()
		{
			$this->Stock_destoryApp();
		}
		function Stock_destoryApp()
		{
			parent::__construct();
			$this->_stock_destory_mod = &m('stockdestory');
			$this->_store_goods_mod = &m('storegoods');
		}
		//列表管理
		function index()
		{
			$conditions = '1=1';
			$status = empty($_GET['status']) ? 1 : intval($_GET['status']);
			if($status)
			{
				$conditions .= ' and sd.stock_status='.$status;
			}
			$conditions .= $this->_get_query_conditions(array(array(
	                'field' => 'g.goods_name',
	                'equal' => 'LIKE',
	                'name'  => 'goods_name',
	            ),array(
	                'field' => 's.store_name',
	                'equal' => 'LIKE',
	                'name'  => 'store_name',
	            ),array(
	                'field' => 'sd.add_time',
	                'name'  => 'add_time_from',
	                'equal' => '>=',
	                'handler'=> 'gmstr2time',
	            ),array(
	                'field' => 'sd.add_time',
	                'name'  => 'add_time_to',
	                'equal' => '<=',
	                'handler'   => 'gmstr2time_end',
	            ),
	        ));
			$page = $this->_get_page(20);
			$stock_info = $this->_stock_destory_mod->getAll('select g.goods_name,g.goods_id,g.simage_url,
															sd.stock_id,sd.quantity,sd.stock_id,sd.stock_reason,sd.add_time,sd.stock_status,
															s.store_name from pa_stock_destory sd 
															left join pa_goods g on sd.goods_id = g.goods_id 
															left join pa_store s on sd.store_id = s.store_id where '.$conditions.' limit '.$page['limit']);
			$page['item_count'] = $this->_stock_destory_mod->getOne('select count(*) from pa_stock_destory sd 
																	left join pa_goods g on sd.goods_id = g.goods_id 
																	left join pa_store s on sd.store_id = s.store_id where sd.stock_status = 1 and '.$conditions.' limit '.$page['limit']);
			$this->_format_page($page);
			$this->assign('page_info',$page);
			$this->assign('image',IMAGE_URL);
			$this->assign('verify',1);
			$this->assign('stock_info',$stock_info);
			$this->display('stock_destory.index.html');
		}
		//审核，查看
		function view()
		{
			$id = empty($_GET['id']) ? '' : intval($_GET['id']);
			$verfiy = intval($_GET['verify']);
			if(!$id)
			{
				$this->show_warning('没有此商品');
				return;
			}
			$stock_info = $this->_stock_destory_mod->getRow('select g.goods_name,g.goods_id,
															sd.stock_id,sd.verify_name,sd.varify_time,sd.quantity,sd.reason,sd.stock_id,sd.stock_reason,sd.add_time,sd.stock_status,sd.quantity,
															s.store_name,s.store_id,sg.gs_id,sg.stock from pa_stock_destory sd 
															left join pa_goods g on sd.goods_id = g.goods_id 
															left join pa_store s on sd.store_id = s.store_id
															left join pa_store_goods sg on sd.store_id = sg.store_id where sd.stock_id='.$id);
			if(!IS_POST)
			{
				$this->assign('stock',$stock_info);
				$this->assign('verify',$verfiy);
				$this->display('stock_destory.view.html');
			}else{
				$reason = empty($_POST['drop_reason']) ? '' : trim($_POST['drop_reason']);
				$status = empty($_POST['pass']) ? '' : intval($_POST['pass']);
				$data = array();
				$data['stock_status'] = $status;
				$data['varify_time'] = time();	
				$data['verify_name'] = $this->visitor->get('user_name');
				
				$data1 = array();
				$data1['stock'] = $stock_info['stock'] - $stock_info['quantity'];
				if($status == 3 && $reason == '')
				{
					$this->show_warning('请填写未通过原因!');
					return;
				}else{
					$data['reason'] = $reason;
				}
				if(!$this->_stock_destory_mod->edit($id,$data))
				{
					$this->show_warning($this->_stock_destory_mod->get_error());
					return;
				}else{
					if(!$this->_store_goods_mod->edit($stock_info['gs_id'],$data1))
					{
						$this->show_warning($this->_store_goods_mod->get_error());
						return;
					}else{
						$this->show_message('审核成功','返回','index.php?app=stock_destory');
					}
				}
			}
		}
	}
?>