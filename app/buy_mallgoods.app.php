
<?php
/* 派啦专柜进货 */
class Buy_mallgoodsApp extends StoreadminbaseApp
{
	var $_goods_mod;
	var $_store_mod;
    /* 构造函数 */
    function __construct()
    {
         $this->Buy_mallgoods();
    }

    function Buy_mallgoods()
    {
        parent::__construct();
        $this->_goods_mod =& m('goods');
        $this->_store_mod = & m('store');
    }
	function index()
	{
		$gcategory_mod  =& bm('gcategory');
		$scate = $this->_store_mod->getAll('select cs.cate_id from pa_store s left join pa_category_store cs on cs.store_id=s.store_id where s.store_id='.$_SESSION['user_info']['store_id']);			
       	if($scate[0][cate_id] == 0)
        {
        	$scate = $gcategory_mod->getAll('select * from pa_gcategory where parent_id=0');
        }
		if($scate)
        {
        	foreach ($scate as $k=>$v)
        	{
        		$cate_ids[]= intval($v['cate_id']);
        	}
        }	
        $cate_id = !($_GET['cate_id']) ? $cate_ids : intval($_GET['cate_id']);
		/* 搜索条件 */
        $conditions = "1 = 1";
       	if ($cate_id != $cate_ids)
       	{
            $layer   = $gcategory_mod->get_layer($cate_id, true);
            $conditions .= " AND g.cate_id_{$layer} = " . $cate_id;
       	}else {
       		$cate_id = implode(',',$cate_ids);
       		$conditions .= " AND g.cate_id_1 in (" . $cate_id.")";
       	}
        //关键词
        if (trim($_GET['keyword']))
        {
            $str = "LIKE '%" . trim($_GET['keyword']) . "%'";
            $str2 = " = '" . trim($_GET['keyword']) . "'";
            $conditions .= " AND (gs.commodity_code {$str2} OR g.goods_name {$str} OR g.brand {$str} OR g.cate_name {$str})";
        }
        //厂家供应价
        if (trim($_GET['price']))
        {
        	switch ($_GET['price'])
        	{
        		case 1:
        			$conditions .= " AND g.price <= 5.0 ";
        			break;
        		case 2:
        			$conditions .= " AND g.price > 5.0 AND g.price <= 10.0";
        			break;
        		case 3:
        			$conditions .= " AND g.price > 10.0 AND g.price <= 20.0";
        			break;
        		case 4:
        			$conditions .= " AND g.price > 20.0 AND g.price <= 30.0";
        			break;
        		case 5:
        			$conditions .= " AND g.price > 30.0 AND g.price <= 40.0";
        			break;
        		case 6:
        			$conditions .= " AND g.price > 40.0 AND g.price <= 50.0";
        			break;
        		case 7:
        			$conditions .= " AND g.price > 50.0 AND g.price <= 60.0";
        			break;
        		case 8:
        			$conditions .= " AND g.price > 60.0 AND g.price <= 70.0";
        			break;
        		case 9:
        			$conditions .= " AND g.price > 70.0 AND g.price <= 80.0";
        			break;
        		case 10:
        			$conditions .= " AND g.price > 80.0 AND g.price <= 90.0";
        			break;
        		case 11:
        			$conditions .= " AND g.price > 90.0 AND g.price <= 100.0";
        			break;
        		case 12:
        			$conditions .= " AND g.price > 100.0 AND g.price <= 200.0";
        			break;
        		case 13:
        			$conditions .= " AND g.price > 200.0";
        			break;
        		default:
        			$this->show_storeadmin_warning('条件出错');
        			return;
        	}
        }
        
        import('Page.class');
        $count = $this->_get_pailagoods($conditions,false,true); //总条数
        $listRows= 10;        //每页显示条数
        $page = new Page($count,$listRows); //初始化对象
        //获取派啦商品列表
        $goods_list = $this->_get_pailagoods($conditions,$page);
		$cate_mod =& bm('gcategory', array('_store_id' => 0));
        $this->assign('gcategories', $cate_mod->get_options($scate['cate_id'], true));
        $this->assign('goods_list', $goods_list);
        
		$page->setConfig('header', '条记录');
        $p=$page->show();
        
        $this->assign('page',$p);
      
		$this->display('storeadmin.buymallgoods.index.html');
	}
	//取的派啦商城商品列表
    function _get_pailagoods($conditions, $page,$count=false)
    {
    	/* 只取通过审核的派啦商城的商品 */
        $conditions .= " AND g.if_show = 1 AND g.closed = 0 AND g.`status` = 1 ";
    	if ($count)
        {
        	/*$this->_goods_mod->get_pailalist(array(
	            'conditions' => $conditions,
        		'join' => 'belongs_to_gcategory',
	            'count' => $count
	        ));*/
        	$count = $this->_goods_mod->getOne("SELECT COUNT(*) as c FROM pa_goods g LEFT JOIN 
        	pa_goods_spec gs ON g.default_spec = gs.spec_id LEFT JOIN 
        	pa_goods_statistics gst ON g.goods_id = gst.goods_id left join pa_gcategory gc 
        	on g.cate_id = gc.cate_id WHERE " . $conditions);
	        return $count;
        }
        else 
        {
        	/* 取得商品列表 */
	        /*$goods_list = $this->_goods_mod->get_pailalist(array(
	            'conditions' => $conditions,
	        	'join' => 'belongs_to_gcategory',
	            'limit' => $page->firstRow.','.$page->listRows,
	        ));*/
	        $goods_list = $this->_goods_mod->getAll("SELECT g.goods_id, 
	        g.type, g.goods_name, g.cate_id, g.cate_name, g.brand, g.spec_qty, 
	        g.spec_name_1, g.spec_name_2, g.if_show, g.closed, g.add_time, g.recommended, 
	        g.default_image, g.zprice, g.credit, g.status, 
	        g.reason,g.goods_number,g.yimage_url,g.mimage_url, g.smimage_url, g.dimage_url, 
	        g.simage_url, gs.spec_id, gs.spec_1, gs.spec_2, 
	        gs.color_rgb, gs.price, gst.views, gst.sales, gst.comments FROM 
	        pa_goods g LEFT JOIN pa_goods_spec gs ON g.default_spec = gs.spec_id LEFT JOIN 
	        pa_goods_statistics gst ON g.goods_id = gst.goods_id left join pa_gcategory gc on g.cate_id = gc.cate_id 
	        WHERE " . $conditions . " limit " . $page->firstRow.','.$page->listRows);
	     
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
    //生成小弹窗表单 
    public function addForm() {
    	header("Content-type=text/html;charset=gbk");
    	$id = empty($_GET['goods_id']) ? 0 :intval($_GET['goods_id']);
    	
    	if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $data = $this->_get_common_info($id);
        
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }
		
		$this->assign('IMAGE_URL',IMAGE_URL);
       	
		$this->assign('this_goods_id',$id);
        $this->assign('guest_comment_enable', Conf::get('guest_comment'));
    	
    	$this->display("dialogAddCart.html");
    }
    
    
	
    
	function show_goods() 
	{	
		/* 参数 id */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $mobile = $_GET['mobile'];
        $uid = $_GET['uid'];
        $store_id= empty($_GET['store_id']) ? 0 : intval($_GET['store_id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
		//var_dump($_SESSION);
        /* 可缓存数据 */
        $data = $this->_get_common_info($id);
        //$data = false;
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_goods_info($data);
        }
	  	//判断是否，需要店铺库存数做为库存数
        if ($store_id != 0){
        	$model_storegoods = &m('storegoods');
        	$stock_info = $model_storegoods->getRow("select sg.stock from pa_store_goods sg where sg.goods_id =".$id);
        	$stock = $stock_info['stock'];
			$this->assign('stock',$stock);
        }
        /* 取得cate_id */
        $goods_info = $this->_goods_mod->get_goods_info($id);

        //是否开启验证码
        if (Conf::get('captcha_status.goodsqa'))
        {
            $this->assign('captcha', 1);
        }
		$this->assign('IMAGE_URL',IMAGE_URL);
       	$this->assign('site_url',SITE_URL);
		$this->assign('this_goods_id',$id);
		$this->assign('this_user_id',$uid);
        $this->assign('guest_comment_enable', Conf::get('guest_comment'));
        $this->assign('data',$data);
		$this->assign('store_id',$_GET['store_id']);	
        $this->assign('mobile',$mobile);
		$this->display('storeadmin.buymallorders.view.html');
	}
	function shoppcart() 
	{	
		
		$this->display('storeadmin.buymallgoods.shopcart.html');
	}
	function sureorder()
	{	
		$this->display('storeadmin.buymallgoods.sureorder.html');
	}
	function showorder() 
	{	
		$this->display('storeadmin.buymallgoods.showorder.html');
	}
	function writeorder() 
	{	
		$this->display('storeadmin.buymallgoods.writeorder.html');
	}
	function zhifu() 
	{	
		$this->display('storeadmin.buymallgoods.zhifu.html');
	}
	function zhifu2() 
	{	
		$this->display('storeadmin.buymallgoods.zhifu2.html');
	}
	function zhifu3() 
	{	
		$this->display('storeadmin.buymallgoods.zhifu3.html');
	}
	function zhifu4() 
	{	
		$this->display('storeadmin.buymallgoods.zhifu4.html');
	}
	
    /* 取得商城商品分类，指定parent_id */
    function _get_mgcategory_options($parent_id = 0)
    {
        $res = array();
        $mod =& bm('gcategory', array('_store_id' => 0));
        $gcategories = $mod->get_list($parent_id, true);
        foreach ($gcategories as $gcategory)
        {
                  $res[$gcategory['cate_id']] = $gcategory['cate_name'];
        }
        return $res;
    }
	/**
     * 取得公共信息
     *
     * @param   int     $id
     * @return  false   失败
     *          array   成功
     */
    function _get_common_info($id)
    {
        $cache_server =& cache_server();
        $key = 'page_of_goods_' . $id;
        $data = $cache_server->get($key);
        $cached = true;
        $data = false;
        if ($data === false)
        {
            $cached = false;
            $data = array('id' => $id);
			//因其为品牌商城
			//所属物品皆应为品牌商品
			//$area_type = 'brandmall';
            /* 商品信息 */
            
            $goods = $this->_goods_mod->get_goods_info($id);
            
            if (!$goods || $goods['if_show'] == 0 || $goods['closed'] == 1)
            {
                $this->show_warning('goods_not_exist');
                return false;
            }
            $goods['tags'] = $goods['tags'] ? explode(',', trim($goods['tags'], ',')) : array();
            //存储要用的图片
            $images_arr = array();
			
            //处理goods数组内部图片地址.   位置指向同步服务器
            $goods_info['default_image'] = IMAGE_URL.$goods_info['default_image'];
            $goods_info['yimage_url'] = IMAGE_URL.$goods_info['yimage_url'];
            $goods_info['mimage_url'] = IMAGE_URL.$goods_info['mimage_url'];
            $goods_info['smimage_url'] = IMAGE_URL.$goods_info['smimage_url'];
            $goods_info['dimage_url'] = IMAGE_URL.$goods_info['dimage_url'];
            $goods_info['simage_url'] = IMAGE_URL.$goods_info['simage_url'];
            foreach($goods['_images'] as $k => $good) {
            	$goods['_images'][$k]['image_url'] = IMAGE_URL.$good['image_url'];
            	$goods['_images'][$k]['thumbnail'] = IMAGE_URL.$good['thumbnail'];
            	if($k < 5) {
            		$images_arr[] = $good;
            	}
            }
            
            $data['goods'] = $goods;


            //处理store_data数组里的图片地址 .位置指向同步服务器.
            $data['store_data']['store_logo'] = IMAGE_URL.$data['store_data']['store_logo'];
            $data['store_data']['store_owner']['portrait'] = IMAGE_URL.$data['store_data']['store_owner']['portrait'];
            
            //处理images_arr中的图片
            foreach($images_arr as $k => $img) {
            	$images_arr[$k]['image_url'] = IMAGE_URL.$img['image_url'];
            	$images_arr[$k]['yimage_url'] = IMAGE_URL.$img['yimage_url'];
            	$images_arr[$k]['thumbnail'] = IMAGE_URL.$img['thumbnail'];
            	$images_arr[$k]['mimage_url'] = IMAGE_URL.$img['mimage_url'];
            	$images_arr[$k]['smimage_url'] = IMAGE_URL.$img['smimage_url'];
            	$images_arr[$k]['dimage_url'] = IMAGE_URL.$img['dimage_url'];
            	$images_arr[$k]['simage_url'] = IMAGE_URL.$img['simage_url'];
            }
            
            $this->assign('images_arr',$images_arr);
            
            $cache_server->set($key, $data, 1800);
        }
        if ($cached)
        {
            $this->set_store($data['goods']['store_id']);
        }

        return $data;
    }
    /* 赋值公共信息 经过转UTF-8编码的*/
	function _assign_common_info($data)
    {
        /* 商品信息 */
        $goods = $data['goods'];
        $goods['description'] = iconv('gbk','utf-8',$goods['description']);
        $goods['cate_name'] = iconv('gbk','utf-8',$goods['cate_name']);
        $goods['brand'] = iconv('gbk','utf-8',$goods['brand']);
        $goods['spec_name_1'] = iconv('gbk','utf-8',$goods['spec_name_1']);
        $goods['spec_name_2'] = iconv('gbk','utf-8',$goods['spec_name_2']);
        foreach ($goods['_specs'] as $k => $v) {
	        $goods['_specs'][$k]['spec_1'] = iconv('gbk','utf-8',$v['spec_1']);
	        $goods['_specs'][$k]['spec_2'] = iconv('gbk','utf-8',$v['spec_2']);
        }
   
        $this->assign('goods', $goods);
        $this->assign('sales_info', sprintf(LANG::get('sales'), $goods['sales'] ? $goods['sales'] : 0));
        $this->assign('comments', sprintf(LANG::get('comments'), $goods['comments'] ? $goods['comments'] : 0));

        /* 默认图片 */
        $this->assign('default_image', Conf::get('default_goods_image'));

        /* 商品分享 */
        $this->assign('share', $data['share']);

        $this->import_resource(array(
            'script' => 'jquery.jqzoom.js',
            'style' => 'res:jqzoom.css'
        ));
    }
	/* 赋值公共信息 GBK编码*/
	function _assign_common_goods_info($data)
    {
        /* 商品信息 */
        $goods = $data['goods'];
        $this->assign('goods', $goods);
        $this->assign('sales_info', sprintf(LANG::get('sales'), $goods['sales'] ? $goods['sales'] : 0));
        $this->assign('comments', sprintf(LANG::get('comments'), $goods['comments'] ? $goods['comments'] : 0));

        /* 默认图片 */
        $this->assign('default_image', Conf::get('default_goods_image'));

        /* 商品分享 */
        $this->assign('share', $data['share']);

        $this->import_resource(array(
            'script' => 'jquery.jqzoom.js',
            'style' => 'res:jqzoom.css'
        ));
    }
    
    public function getCategory()
    {
    	$_gcategory_mod = & m('gcategory');
    	$gcategory_list = $_gcategory_mod->getAll("select * from pa_gcategory where 
    	store_id = 0 and parent_id = 0");
    	
    	if (!$gcategory_list)
    	{
    		$this->json_error("error");
    		return;
    	}
    	
    	$this->json_result($gcategory_list);
    }
    
}
    
