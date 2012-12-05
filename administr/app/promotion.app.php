<?php 
class PromotionApp extends BackendApp
{
    var $_storegoods_mod;
    var $_promotion_mod;
    var $_promotionstoregoods_mod;
    var $_brand_mod;
    var $_store_mod;
    var $_uploadedfile_mod;
    public function __construct() {
    	$this->PromotionApp();
    }
    
    public function PromotionApp() {
    	parent::__construct();
        $this->_storegoods_mod = & m('storegoods');
        $this->_promotion_mod = & m('promotion');
        $this->_promotionstoregoods_mod = & m('promotionstoregoods');
        $this->_brand_mod = & m('brand');
        $this->_store_mod = & m('store');
        $this->_uploadedfile_mod = &m('uploadedfile');
    }
    function index(){
    		$goods_name = trim($_GET['goods_name']);	
		    if(!isset($_GET['type'])) $_GET['type'] = 10;
    		$status_type = intval($_GET['type']);
	    	$page = $this->_get_page();
	    	$type = array(
	    		5  => Lang::get('待编辑'),
	            10 => Lang::get('已通过'),
	            20 => Lang::get('已关闭'),
	            11 => Lang::get('未通过'),
       		);
	    	$params = array(
	    		'page' 	=> $page,
	    		'status' => $status_type,
	    	);
			$promotion_info = $this->_promotionstoregoods_mod->get_promotioninfo($params,$goods_name);
			$page['item_count'] = $promotion_info['item_count'];
			$this->_format_page($page);
			$this->assign('page_info', $page);
			/* 导入jQuery的表单验证插件 */
	        $this->import_resource(array(
	            'script' => 'jqtreetable.js,inline_edit.js',
	            'style'  => 'res:style/jqtreetable.css'
	        ));

       		$this->assign('type',$type);
			$this->assign('promotion_info',$promotion_info['goods']);
    		$this->display('promotion.apply.html');
    }
    function apply()
    {
    	if(!IS_POST)
    	{
    		$gs_id = empty($_GET['gs_id']) ? 0 : $_GET['gs_id']; 
    		if($gs_id == 0)
    		{
    			$this->show_message('请选择您要促销的产品');
    			return ;
    		}
    		$goods=$this->_storegoods_mod->getRow('select gc.price,gc.credit,gc.weight,gc.zprice,g.goods_name,sg.store_id,sg.stock,sg.gs_id from pa_store_goods sg left join pa_goods g on sg.goods_id= g.goods_id left join pa_goods_spec gc on sg.spec_id=gc.spec_id where sg.gs_id='.$gs_id);	
    		$this->assign('goods',$goods);
    		$this->display('promotion.ask.html');
    	} else {
    		$price = empty($_POST['pr_price']) ? 0 : floatval($_POST['pr_price']);
    		$stock = empty($_POST['pr_stock']) ? 0 : intval($_POST['pr_stock']);
    		$start = empty($_POST['starttime']) ? 0 : strtotime($_POST['starttime']);
    		$end   = empty($_POST['endtime']) ? 0 : strtotime($_POST['endtime']);
    		$gs_id = empty($_POST['gs_id']) ? 0 : intval($_POST['gs_id']);    		
    		if(0 == $price)
    		{
    			$this->show_message("请输入促销价");
    			return ;
    		}
    	    if(0 == $stock)
    		{
    			$this->show_message("请输入促销的数量");
    			return ;
    		}
    	    if(empty($_POST['starttime']) && empty($_POST['endtime']))
    		{
    			$this->show_message("请选择促销开启时间与结束时间");
    			return ;
    		}
    	    if($end < $start)
    		{
    			$this->show_message("促销开启时间不能大于结束时间");
    			return ;
    		}
    		if($gs_id == 0)
    		{
    			$this->show_message("商品中间表ID不能为空");
    			return ;    			
    		}
    		$goods_info = $this->_storegoods_mod->getRow('SELECT * from pa_store_goods gs left join pa_goods_spec gc on gc.spec_id=gs.spec_id where gs.gs_id='.$gs_id);
    		$pr_discount = intval($price/floatval($goods_info['price'])*100)/100;
			if($goods_info['stock'] < $stock)
			{
				$this->show_message('您输入的促销数量不能大于原数量');
				return ;
			}
			$data = array(
				'pr_price' => $price,
				'pr_credit' => $goods_info['credit'],
				'pr_stock' => $stock,
				'pr_addtime' => $start,
				'pr_endtime' => $end,
				'pr_status' => 5,
				'pr_discount' => $pr_discount,
				'pr_sort' => 0
			);
			if(!$promotion_id=$this->_promotion_mod->add($data))
			{
				$this->show_message("申请失败");
    			return ;
			}
				$pgdata = array(
					'promotion_id' => $promotion_id,
					'gs_id' => intval($_POST['gs_id']),
				);
    		if(!$this->_promotionstoregoods_mod->add($pgdata))
			{
				$this->show_message("申请失败");
    			return ;
			}	
            $this->show_message('add_ok',
                'back_list', 'index.php?app=promotion'
            );
    	}
    }
    function varify()
    {
    	if(!IS_POST){
    		$promotion_id = empty($_GET['pr_id']) ? 0 : intval($_GET['pr_id']);
	    	if(0 == $promotion_id)
	    	{
	    		$this->show_message('促销ID不能为空');
	    		return ;
	    	};
	    	$promotion = $this->_promotionstoregoods_mod->get_promotion($promotion_id);
			$this->assign('pr',$promotion);
			$this->assign('build_upload', $this->_build_upload(array('belong' => BELONG_ARTICLE, 'item_id' => $promotion_id)));
	    	$this->display('promotion.form.html');
  		}else {
  			$promotion_id = empty($_POST['promotion_id']) ? 0 : intval($_POST['promotion_id']);
  			if(0 == $promotion_id)
	    	{
	    		$this->show_message('促销ID不能为空');
	    		return ;
	    	};	    	
			$promotion = $this->_promotionstoregoods_mod->get_promotion($promotion_id);
    		$pr_discount = intval($promotion['pr_price']/floatval($promotion['price'])*100)/100;
	    	$data = array(
	    		'pr_status' => $_POST['pr_status'],
	    		'pr_reason' => $_POST['pr_reason'],
	    		'pr_discount' => $pr_discount,
	    	);
  			if(empty($promotion['gs_id']))
		   	{
		    	$this->show_warning("系统报错");
		    	return ;
		    }
	    	$goods_info = $this->_storegoods_mod->get($promotion['gs_id']);	    	
  		    if($promotion['pr_stock'] > intval($goods_info['stock']))
    		{
    			$this->show_warning('您输入的促销数量大于，您店里的库存数不能促销');
    			return ;
    		}
	    	if(!$this->_promotion_mod->edit($promotion_id,$data)){
	    		$this->show_message("审核未成功");
	    		return ;
	    	};
	    	if($_POST['pr_status'] == 10){
	    		$undata = array(
		    		'stock' => $goods_info['stock']-$promotion['pr_stock'],
		    	);
	    		$this->_storegoods_mod->edit($promotion['gs_id'],$undata);
	    	}
	    	$this->show_message('edit_ok',
                'back_list', 'index.php?app=promotion&act=audit&status=0'
            );
  		}
    }
    function check()
    {
    	$promotion_id = empty($_GET['pr_id']) ? 0 : intval($_GET['pr_id']);
    	if(0 == $promotion_id)
    	{
    		$this->show_message('促销ID不能为空');
    		return ;
    	};
    	$promotion = $this->_promotionstoregoods_mod->get_promotion($promotion_id);
		$this->assign('pr',$promotion);
		//var_dump($promotion);
		/* 查看详细信息 */
		$this->assign('check','ture');
    	$this->display('promotion.form.html');

    }
    function _get_conditions()
    {
        /* 搜索条件 */
        $conditions = " where 1 = 1 and closed = 0 and status = 1";
        if (trim($_GET['keyword']))
        {
            $str = "LIKE '%" . trim($_GET['keyword']) . "%'";
            $conditions .= " AND (g.goods_name {$str} OR g.brand {$str} OR g.cate_name {$str})";
        }
        if ($_GET['store_id'] && $_GET['store_id'] != 0)
        {
			$conditions .= " AND sg.store_id = ".$_GET['store_id'];
        }
        if ($_GET['brand_id'] && $_GET['brand_id'] != 0)
        {
			$conditions .= " AND sg.store_id = ".$_GET['brand_id'];
        }   
        return $conditions;
    }
    function add(){
    	//获取检索条件
    	$conditions = $this->_get_conditions();
    	$page = $this->_get_page();
    	$sql = "select sg.*,gc.*,g.goods_name,g.cate_id,g.cate_name,g.spec_name_1,g.spec_name_2,g.brand_id,g.brand from pa_store_goods sg left join pa_goods g on g.goods_id=sg.goods_id left join pa_goods_spec gc on gc.spec_id=sg.spec_id ";
		$goods_list = $this->_storegoods_mod->getAll($sql.$conditions." limit ".$page['limit']);
		$count = $this->_storegoods_mod->getRow("select COUNT(*) sg from pa_store_goods sg left join pa_goods g on g.goods_id=sg.goods_id left join pa_goods_spec gc on gc.spec_id=sg.spec_id ".$conditions);
        $page['item_count'] = $count['sg'];
		$this->_format_page($page);
		foreach($goods_list as &$val){
			$checkRs= $this->_check_exists_promotion($val['gs_id']);
			if($checkRs) $val['is_exists'] = TRUE;
		}
        $this->assign('page_info', $page);
        $this->assign('goods_list',$goods_list);
		$this->assign('brands',$this->_brand_mod->getBrands());
		$this->assign('stores',$this->_store_mod->getStore());
    	$this->display('promotion.add.html'); 	
    }
    function edit()
    {
    	if(!IS_POST)
    	{
    		$promotion_id = empty($_GET['pr_id']) ? 0 : intval($_GET['pr_id']);
	    	if(0 == $promotion_id)
	    	{
	    		$this->show_message('促销ID不能为空');
	    		return ;
	    	};
	    	$promotion = $this->_promotionstoregoods_mod->get_promotion($promotion_id);
			$this->assign('pr',$promotion);
			$this->assign('site_url',SITE_URL);
	    	$this->display('promotion.edit.html');
    	} else {
    		$dat=$this->_upload_files();
			if($dat)
			{
				$img = $dat['dir'];
			}else {
				$img = trim($_POST['pr_img']);
			}
    		$virtual_stock = empty($_POST['virtual_stock']) ? 0 : intval($_POST['virtual_stock']);
    		$start = empty($_POST['starttime']) ? 0 : strtotime($_POST['starttime']);
    		$end   = empty($_POST['endtime']) ? 0 : strtotime($_POST['endtime']);
    		$pr_id = empty($_POST['pr_id']) ? 0 : intval($_POST['pr_id']);
    		$pr_name = empty($_POST['pr_name']) ? '' : trim($_POST['pr_name']);
    		$pr_sort = empty($_POST['pr_sort']) ? 255 : intval($_POST['pr_sort']);
    		$pr_art = empty($_POST['pr_art']) ? 0 : intval($_POST['pr_art']);
    	   	if(0 == $pr_id)
    		{
    			$this->show_message("pr_id不能为空");
    			return ;
    		}
    	    if(0 == $virtual_stock)
    		{
    			$this->show_message("请输入虚拟销售的数量");
    			return ;
    		}
			$promotion = $this->_promotionstoregoods_mod->get_promotion($promotion_id);
    		$goods_info = $this->_storegoods_mod->getRow('SELECT * from pa_store_goods gs left join pa_goods_spec gc on gc.spec_id=gs.spec_id where gs.gs_id='.$promotion['gs_id']);
			$pr_discount = intval($promotion['pr_price']/floatval($promotion['price'])*100)/100;			
    		if($start == 0 && $end == 0)
    		{
				$data = array(
					'virtual_log' => $virtual_stock,
					'pr_discount' => $pr_discount,
					'pr_img'	  => $img,
					'pr_name'	  => $pr_name,
					'pr_sort'	  => $pr_sort,
					'pr_art'	  => $pr_art,
					'pr_status'	  => 0
				);  			
    		}else {
    	    	if($end < $start)
	    		{
	    			$this->show_message("促销开启时间不能大于结束时间");
	    			return ;
	    		}
			    $data = array(
    			'virtual_log' => $virtual_stock,
	    		'pr_addtime' => $start,
	    		'pr_endtime' => $end,
			    'pr_discount' => $pr_discount,
			    'pr_img'	  => $img,
				'pr_name'	  => $pr_name,
				'pr_sort'	  => $pr_sort,
				'pr_art'	  => $pr_art,
			    'pr_status'   => 0		
    			);
    		}
			if(!$this->_promotion_mod->edit($pr_id,$data))
			{
				$this->show_message("编辑失败");
    			return ;
			}
            $this->show_message('edit_ok',
                'back_list', 'index.php?app=promotion'
            );
    	}
    }
	function _upload_files()
    {	
    	import('uploader.lib');
    	$daimg = array();
    	$file = $_FILES['file'];
    	$Filedir = 'data/files/pr_goods/';
    	$filena = date('Ymdhis'); 
    	$FileName = $filena.strrchr($_FILES['file']['name'],'.');
    	if ($file['error'] == UPLOAD_ERR_OK && $file !='')
    	{
    		$uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->allowed_size(SIZE_STORE_PARTNER); // 100KB
            $uploader->addFile($file);
            if ($uploader->file_info() === false)
            {
            	$this->show_warning($uploader->get_error());
            	return false;
            }
            $uploader->root_dir(ROOT_PATH);
            $uploader->save('data/files/pr_goods',$filena);
			$daimg['dir']=$Filedir.$FileName;
    	}
    	return $daimg;
    }
    function audit()
    {
    		$page = $this->_get_page();
	    	$stats = empty($_GET['status']) ? 0 : intval($_GET['status']);
	    	$params = array(
	    		'page' 	=> $page,
	    		'status'	=> $stats,
	    	);
			$promotion_info = $this->_promotionstoregoods_mod->get_promotioninfo($params,$goods_name);
			$page['item_count'] = $promotion_info['item_count'];
			$this->_format_page($page);
			$this->assign('page_info', $page);
			$this->assign('promotion_info',$promotion_info['goods']);
    		$this->display('promotion.audit.html');
    }
  //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array( 'pr_sort')))
       {
           $data[$column] = $value;
           $this->_promotion_mod->edit($id, $data);
           if(!$this->_promotion_mod->has_error())
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
    /*判断此产品是否已经添加促销 */
    function _check_exists_promotion($gs_id){
         if(empty($gs_id)) return true;
         $sql = "select * from pa_promotion p left join pa_promotion_store_goods psg on p.promotion_id= psg.promotion_id \n"
              . " where psg.gs_id={$gs_id} AND pr_status IN (0,10) ";
         $info = $this->_promotion_mod->getRow($sql);
         if ($info)
         {
             return TRUE;
         }
    }
}    
?>
