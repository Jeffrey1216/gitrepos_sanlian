<?php
	class AssessApp extends BackendApp
	{
		var $_order_mod;
		var $_goods_mod;
		var $_gcategory_mod;
		var $_order_goods_mod;
		function __construct()
		{
			$this->AssessApp();
		}
		function AssessApp()
		{
			parent::__construct();
			$this->_order_mod = &m('order');
			$this->_goods_mod = &m('goods');
			$this->_gcategory_mod = &m('gcategory');
			$this->_order_goods_mod = &m('ordergoods');
		}
		//管理
		function index()
		{	
			$conditions = '1=1';
			$conditions .= $this->_get_query_conditions(array(
            array(
                'field' => 'og.goods_name',
                'equal' => 'like',
            	'name' => 'goods_name',
            )
        	));
			 $cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
	         if($cate_id > 0)
	         { 
	            $cate_ids = $this->get_descendant($cate_id);       
	            $conditions .= " AND g.cate_id" . db_create_in($cate_ids);
	         }
			$page = $this->_get_page(20);
			$goods = $this->_order_mod->getAll("select og.rec_id,og.goods_name,o.order_id,o.evaluation_status,og.goods_id,og.comment,g.cate_name,og.evaluation from pa_order_goods og 
												left join pa_order o on og.order_id = o.order_id 
												left join pa_goods g on og.goods_id = g.goods_id 
												left join pa_gcategory gc on g.cate_id = gc.cate_id
												where  o.evaluation_status = 1 and evaluation != 0 and og.comment != ''  and seller_id =".STORE_ID." and ".$conditions." limit ".$page['limit']);
			
			$page['item_count'] = $this->_order_mod->getOne("select count(*) from pa_order_goods og 
												left join pa_order o on og.order_id = o.order_id 
												left join pa_goods g on og.goods_id = g.goods_id 
												left join pa_gcategory gc on g.cate_id = gc.cate_id
												where  o.evaluation_status = 1 and evaluation != 0  and og.comment !='' and seller_id =".STORE_ID.$conditions);
			$this->_format_page($page);
			/* 导入jQuery的表单验证插件 */
	        $this->import_resource(array(
	            'script' => 'jqtreetable.js,inline_edit.js',
	            'style'  => 'res:style/jqtreetable.css'
	        ));
            $this->assign('gcategories', $this->_gcategory_mod->get_all_options(0)); 
       		$this->import_resource(array('script' => 'mlselection.js,inline_edit.js'));
			$this->assign('page_info',$page);
			$this->assign('goods_list',$goods);
			$this->display('assess.index.html');
		}
		function get_descendant($id)
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
		//异步修改数据
	   function ajax_col()
	   {
	       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
	       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
	       if($column == 'evaluation')
	       {
	       		$value  = isset($_GET['value']) ? intval($_GET['value']) : 3;
	       		if($value == 0)
	       		{
	       			$value = 3;
	       		}
	       }else{
	       		$value  = isset($_GET['value']) ? trim($_GET['value']) : '';
	       }     
	       $data   = array();
	
	       if (in_array($column ,array( 'comment','evaluation')))
	       {
	           $data[$column] = $value;
	           $this->_order_goods_mod->edit($id, $data);
	           if(!$this->_order_goods_mod->has_error())
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
		function drop()
		{
			$id = empty($_GET['id']) ? '' : intval($_GET['id']);
			if(!id)
			{
				$this->show_warning('此商品没有评价!');
				return;
			}
			$data = array();
			$data['order_id'] = $id;
			$data['evaluation_status'] = 0;
			if(!$this->_order_mod->edit($id,$data))
			{
				$this->show_warning($this->_order_mod->get_error());
				return;
			}else{
				$this->show_message('批量删除成功!',
				'back_list',	'index.php?app=assess'
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
			$data = array();
			$data['id'] = $ids;
			$data['evaluation_status'] = 0;
			if(!$this->_order_mod->edit($ids,$data))
			{
				$this->show_warning($this->_order_mod->get_error());
				return;
			}else{
				$this->show_message('批量删除成功!',
				'back_list',	'index.php?app=assess'
				);	
			}	
		}
	}
?>