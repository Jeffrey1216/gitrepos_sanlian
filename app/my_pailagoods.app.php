<?php

define('THUMB_WIDTH', 300);
define('THUMB_HEIGHT', 300);
define('THUMB_QUALITY', 85);

/* 品牌申请状态 */
define('BRAND_PASSED', 1);
define('BRAND_REFUSE', 0);

/* 品牌商城商品管理控制器 */
class My_pailagoodsApp extends StoreadminbaseApp
{
    var $_goods_mod;
    var $_spec_mod;
    var $_image_mod;
    var $_uploadedfile_mod;
    var $_store_id;
    var $_brand_mod;
    var $_last_update_id;
    var $_storegoods_mod;
    public $_paila_mod;
    
    public function __construct() {
    	$this->My_PailagoodsApp();
    }
    
    public function My_pailagoodsApp() {
    	parent::__construct();
        $this->_storegoods_mod = & m('storegoods');
        $this->_stock_destory_mod = & m('stockdestory');
        $this->_stock_log_mod = &m('stocklog');
    }

	/* 取得本店商品列表 */
    function index()
    {
    	//取得本店的派拉商品列表 
    	$conditions = ' 1 = 1 and sg.store_id = ' . $this->visitor->get('store_id');
		//关键词
        if (trim($_GET['keyword']) && trim($_GET['keyword']) != '请输入搜索关键字!')
        {
            $str = "LIKE '%" . trim($_GET['keyword']) . "%'";
            $conditions .= " AND (goods_name {$str} OR brand {$str} OR cate_name {$str})";
            $this->assign('keyword',trim($_GET['keyword']));
        }
		if(isset($_GET['priceRegion'])) {
			$this->assign('priceReg',trim($_GET['priceRegion']));
			switch(trim($_GET['priceRegion'])) {
				case 'a':$conditions .= ' and g.price < 50 ' ; break;
				case 'b':$conditions .= ' and g.price >= 50 and g.price < 100 '  ; break;
				case 'c':$conditions .= ' and g.price >= 100 and g.price < 500 ' ; break;
				case 'd':$conditions .= ' and g.price >= 500 and g.price < 1000 ' ; break;
				case 'e':$conditions .= ' and g.price >= 1000 and g.price < 2000 ' ; break;
				case 'f':$conditions .= ' and g.price >= 2000 and g.price < 5000 ' ; break;
				case 'g':$conditions .= ' and g.price >= 5000 ' ; break;
				default: $conditions .= ' ';
			}
		}
		if(isset($_GET['cate_id'])) {
			if(intval($_GET['cate_id'] == 0)) {
				$conditions .= ' ';
			} else {
				$conditions .= ' and cate_id_1 = '.intval($_GET['cate_id']);
				$this->assign('cate_id',intval($_GET['cate_id']));
			}
		}
		import('Page.class');
		
        //$count = $this->_get_storegoods($conditions,false,true); //总条数
        $count = $this->_storegoods_mod->getOne("select count(*) from pa_store_goods sg 
        left join pa_goods g on sg.goods_id = g.goods_id left join pa_goods_spec gs 
        on sg.spec_id = gs.spec_id left join pa_goods_statistics gst on 
        g.goods_id = gst.goods_id where " . $conditions);
        
        $listRows= 10;        //每页显示条数
        $page=new Page($count,$listRows); //初始化对象
        //获取派啦商品列表
        //$goods_list = $this->_get_storegoods($conditions,$page);
		$goods_list = $this->_storegoods_mod->getAll("select g.goods_id, g.type, g.goods_name, 
				g.cate_id, g.cate_name, g.brand, g.spec_qty, g.spec_name_1, 
				g.spec_name_2, g.if_show, g.closed, g.add_time, g.recommended, 
				g.default_image, " .
        		"g.zprice, g.credit, g.status, g.reason,g.goods_number,g.yimage_url,
        		g.mimage_url, g.smimage_url, g.dimage_url, g.simage_url,".
                "gs.spec_id, gs.spec_1, gs.spec_2 , gs.zprice, gs.color_rgb, gs.price, sg.stock, sg.gs_id," .
                "gst.views, gst.sales, gst.comments from pa_store_goods sg 
		        left join pa_goods g on sg.goods_id = g.goods_id left join pa_goods_spec gs 
		        on sg.spec_id = gs.spec_id left join pa_goods_statistics gst on 
		        g.goods_id = gst.goods_id  where " . $conditions . " limit " . $page->firstRow.",".$page->listRows);
		//价格区间
		$priceRegion = array(
			array('key'=>'a','value'=>'50以下'),
			array('key'=>'b','value'=>'50-100'),
			array('key'=>'c','value'=>'100-500'),
			array('key'=>'d','value'=>'500-1000'),
			array('key'=>'e','value'=>'1000-2000'),
			array('key'=>'f','value'=>'2000-5000'),
			array('key'=>'g','value'=>'5000以上'),
		);
		//取商品大分类 
		$gcategory_mod = & m('gcategory');
		$gcategory_list = $gcategory_mod->find(array('conditions' => 'parent_id=00'));
		$this->assign('gcategory_list',$gcategory_list);
		$this->assign('priceRegion',$priceRegion);
		$this->assign('goods_list', $goods_list);

		$p=$page->show();
		$this->assign('page',$p);
		
        $this->display('storeadmin.mygoods.index.html');
    }
    //减少库存
    function cut_stock()
    {
    	$id = empty($_GET['id']) ? '' : intval($_GET['id']);
    	if(!$id)
    	{
    		$this->show_storeadmin_message('没有此商品!');
    		return;
    	}
    	$goods_info = $this->_storegoods_mod ->getAll('select sg.stock,sg.gs_id,sd.stock_status,s.store_type from pa_store_goods sg 
    												   left join pa_stock_destory sd on sg.goods_id = sd.goods_id 
    												   left join pa_store s on sg.store_id = s.store_id where sd.stock_status = 1 and  sg.goods_id ='.$id .' and sg.store_id='.$this->visitor->get('store_id'));
    	if(!IS_POST)
    	{		
	    	if($goods_info)
	    	{
	    		$this->show_storeadmin_message('请等待上次操作审核完成后,再进行操作!');
	    		return;
	    	}	
    		$this->display('storeadmin.cut_stock.html');
    	}else{
    		$store_goods = $this->_storegoods_mod->getRow('select sg.goods_id,sg.store_id,sg.stock,s.store_type from pa_store_goods sg left join pa_store s on sg.store_id = s.store_id where sg.store_id='.$this->visitor->get('store_id'));
    		$reason = empty($_POST['text1']) ? '' : trim($_POST['text1']);
			$num = empty($_POST['input1']) ? '' : intval($_POST['input1']);
			if($num > $store_goods['stock'])
			{
				$this->show_storeadmin_message('库存不足!');
				return;
			}
			if($num == '' || $num == null)
			{
				$this->show_storeadmin_message('库存数不能为空!');
				return;
			}
			if(!$reason)
			{
				$this->show_storeadmin_message('请填写减少库存原因!');
				return;
			}	
			if($store_goods['store_type'] == 1)
			{
				$data = array();
				$data['store_id'] = $this->visitor->get('store_id');
				$data['stock_num'] = $num;
				$data['reason'] = $reason;
				$data['add_time'] = time();
				$data['goods_id'] = $store_goods['goods_id'];
				
				$data1 = array();
				$data1['stock'] = $store_goods['stock']-$num;
				if(!$this->_stock_log_mod->add($data))
				{
					$this->show_storeadmin_message($this->_stock_log_mod->get_error());
					return;
				}else{
					if(!$this->_storegoods_mod->edit($store_goods['gs_id'],$data1))
					{
						$this->show_storeadmin_message($this->_storegoods_mod->get_error());
						return;
					}else{
						$this->show_storeadmin_message('库存减少成功','返回','index.php?app=my_pailagoods');
					}	
				}
			}else{
				$data = array();
				$data['quantity'] =  $num;
				$data['stock_reason'] = $reason;
				$data['goods_id'] = $store_goods['goods_id'];
				$data['store_id'] = $this->visitor->get('store_id');
				$data['add_time'] = time();
				$data['stock_status'] = 1;
				if(!$this->_stock_destory_mod->add($data))
				{
					$this->show_storeadmin_message($this->_stock_destory_mod->get_error());
					return;
				}else{
					$this->show_storeadmin_message('库存减少成功,请等待专员审核','返回','index.php?app=my_pailagoods');
				}
			}
    	}
    }

	//取的派啦商城商品列表
    function _get_storegoods($conditions, $page,$count=false)
    {
    	/* 只取通过审核的派啦商城的商品 */
        $conditions .= " AND 1 = 1";
        //$conditions .= " AND `status` = 1 ";
    	if ($count)
        {
        	$this->_storegoods_mod->get_list(array(
	            'conditions' => $conditions,
	            'count' => $count
	        ));
	        return $this->_storegoods_mod->getCount();
        }else 
        {
        	/* 取得商品列表 */
	        $goods_list = $this->_storegoods_mod->get_list(array(
	            'conditions' => $conditions,
	            'limit' => $page->firstRow.','.$page->listRows,
	        ));
	        foreach ($goods_list as $k => $v) {
	        	$goods_list[$k]['default_image'] = IMAGE_URL.$v['default_image'];
	        	$goods_list[$k]['yimage_url'] = IMAGE_URL.$v['yimage_url'];
	        	$goods_list[$k]['mimage_url'] = IMAGE_URL.$v['mimage_url'];
	        	$goods_list[$k]['smimage_url'] = IMAGE_URL.$v['smimage_url'];
	        	$goods_list[$k]['dimage_url'] = IMAGE_URL.$v['dimage_url'];
	        	$goods_list[$k]['simage_url'] = IMAGE_URL.$v['simage_url'];
	        }
	        return $goods_list;
        } 
    }
}

?>
