<?php
	class ConsultApp extends BackendApp
	{
		var $_goods_qa_mod;
		function __construct()
		{
			$this->ConsultApp();
		}
		function ConsultApp()
		{
			parent::__construct();
			$this->_goods_qa_mod = &m('goodsqa');
		}
		//管理
		function index()
		{
			//搜索条件
			$conditions = '1=1';
			if(trim($_GET['name'] == '游客'))
			{
				$conditions .= " and gq.user_id = 0";
			}else{
				$conditions .= $this->_get_query_conditions(array(
					array(
						'field' => 'gq.item_name',
						'equal' => 'like',
						'name'  => 'goods_name',
					),array(
						'field' => 'm.user_name',
						'equal' => 'like',
						'name'  => 'name',
					),array(
		                'field' => 'time_post',
		                'name'  => 'add_time_from',
		                'equal' => '>=',
		                'handler'=> 'gmstr2time',
		            ),array(
		                'field' => 'time_post',
		                'name'  => 'add_time_to',
		                'equal' => '<=',
		                'handler'   => 'gmstr2time_end',
          			),
				));
			}		
			$page = $this->_get_page(20);
			$goods_info = $this->_goods_qa_mod->getAll('select gq.ques_id,gq.question_content,gq.item_id,gq.user_id,gq.item_name,gq.time_post,m.user_name from pa_goods_qa gq 
														left join pa_member m on gq.user_id = m.user_id where gq.store_id='.STORE_ID.' and '.$conditions.' order by time_post DESC limit '.$page['limit']);
			$page['item_count'] = $this->_goods_qa_mod->getOne('select count(*) from pa_goods_qa gq 
																left join pa_member m on gq.user_id = m.user_id where gq.store_id ='.STORE_ID.' and '.$conditions);
			$this->_format_page($page);
			$this->assign('page_info',$page);
			$this->assign('consult',1);
			$this->import_resource(array('script' => 'mlselection.js,inline_edit.js'));
			$this->assign('goods_info',$goods_info);
			$this->display('consult.index.html');
		}
		//删除
		function drop()
		{
			$id = empty($_GET['id']) ? '' : intval($_GET['id']);
			if(!id)
			{
				$this->show_warning('此商品没有评价!');
				return;
			}
			if(!$this->_goods_qa_mod->drop($id))
			{
				$this->show_warning($this->_order_mod->get_error());
				return;
			}else{
				$this->show_message('批量删除成功!',
				'back_list',	'index.php?app=consult'
				);	
			}	
		}
		//批量删除
		function bath_edit()
		{
			$id = isset($_GET['id']) ? trim($_GET['id']) : '';
			if(!id)
			{
				$this->show_warning('此商品没有评价!');
				return;
			}
			$ids = explode(',',$id);
			if(!$this->_goods_qa_mod->drop($ids))
			{
				$this->show_warning($this->_order_mod->get_error());
				return;
			}else{
				$this->show_message('批量删除成功!',
				'back_list',	'index.php?app=consult'
				);	
			}	
		}
		//异步修改数据
	   function ajax_col()
	   {
	       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
	       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
	       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';    
	       $data   = array();
	       if (in_array($column ,array( 'question_content')))
	       {
	           $data[$column] = $value;
	           $this->_goods_qa_mod->edit($id, $data);
	           if(!$this->_goods_qa_mod->has_error())
	           {
	               echo ecm_json_encode(true);
	           }
	       }
	       else
	       {
	           return ;
	       }
	       return ;
	   }
	}
?>