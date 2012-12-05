<?php
/* ����ר�� */
class kjzfApp extends StoreadminbaseApp
{
	public $_goods_mod;
	public $_paila_mod;
    /* ���캯�� */
    function __construct()
    {
         $this->Kjzf();
    }

    function Kjzf()
    {
        parent::__construct();
        $this->_goods_mod =& m('goods');
        $this->_paila_mod = & m('pailagoods');
    }
	function index()
	{
		$conditions = ' 1 = 1';
		//�ؼ���
        if (trim($_GET['keyword']) && trim($_GET['keyword']) != '�����������ؼ���!')
        {
            $str = "LIKE '%" . trim($_GET['keyword']) . "%'";
            
            $conditions .= " AND (g.commodity_code = '" . trim($_GET['keyword']) . "' OR g.goods_name {$str} OR g.brand {$str} OR g.cate_name {$str})";
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
        $count = $this->_get_pailagoods($conditions,false,true); //������
        $listRows= 10;        //ÿҳ��ʾ����
        $page=new Page($count,$listRows); //��ʼ������
        //��ȡ������Ʒ�б�
        
        $goods_list = $this->_get_pailagoods($conditions,$page);
		//�۸�����
		$priceRegion = array(
			array('key'=>'a','value'=>'50����'),
			array('key'=>'b','value'=>'50-100'),
			array('key'=>'c','value'=>'100-500'),
			array('key'=>'d','value'=>'500-1000'),
			array('key'=>'e','value'=>'1000-2000'),
			array('key'=>'f','value'=>'2000-5000'),
			array('key'=>'g','value'=>'5000����'),
		);
		//ȡ��Ʒ����� 
		$gcategory_mod = & m('gcategory');
		$gcategory_list = $gcategory_mod->find(array('conditions' => 'parent_id=0'));
		$this->assign('gcategory_list',$gcategory_list);
		$this->assign('priceRegion',$priceRegion);
		$this->assign('goods_list', $goods_list);
		$p=$page->show();
		$this->assign('page',$p);
		
		/* �Ƿ����������� */
		$store_id = $this->visitor->get('store_id');
    	$store_mod = & m('store');
    	$store_info = $store_mod->get($store_id);
    	$this->assign('is_paila_store',$store_info['is_paila_store']);

		$this->display('storeadmin.kjzf.index.html');	
	}
	
	/**
	 * 	���֧��,Ʒ����Ʒչʾҳ
	 * 
	 **/
	public function quick_brand_index() {
		import('chineseSpell.class');
		$cs = new ChineseSpell() ;
		if(IS_POST){
			$userinfo = $_POST['mobile'];
			$member_model = & m("member");
			$member_info = $member_model->getAll("select * from pa_member where mobile='".$userinfo."'");
			if (empty($member_info))
			{
				$member_info = array(
					array(
						user_id => 0,
						user_name => '��δע��',
						mobile => $_POST['mobile'],
						money => '0',
						credit => '0',
					)
				);
			}
			$this->assign("member_info",$member_info);
		}else {
			$userin = $_GET['mobile'];
			$member_model = & m("member");
			$member_info = $member_model->getAll("select * from pa_member where mobile='".$userin."'");
			if (empty($member_info))
				{
					$member_info = array(
						array(
							user_id => 0,
							user_name => '��δע��',
							mobile => $_GET['mobile'],
							money => '0',
							credit => '0',
						)
					);
				}
			$this->assign("member_info",$member_info);
		}
		//exit;
		$conditions = ' 1 = 1';
		//�ؼ���
        if (trim($_GET['keyword']) && trim($_GET['keyword']) != '�����������ؼ���!')
        {
        	$str = "LIKE '%" . trim($_GET['keyword']) . "%'";
            $spellStr = "LIKE '%" . $cs->getFullSpell(trim($_GET['keyword'])) . "%'"; 
            //$letterStr = "LIKE '%" . $cs->getFirstLetter(trim($_GET['keyword'])) . "%'"; 
            $conditions .= " AND (g.commodity_code = '" . trim($_GET['keyword']) . "' OR g.goods_name {$str} OR g.brand {$str} OR g.cate_name {$str} OR g.full_spell {$spellStr})";
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
        $count = $this->_get_pailagoods($conditions,false,true); //������
        $page = $this->_get_page(10);
       
        //��ȡ������Ʒ�б�
        $goods_list = $this->_get_pailagoods($conditions,$page);
        
        $page['item_count'] = $count;
        //var_dump($conditions);
		//�۸�����
		$priceRegion = array(
			array('key'=>'a','value'=>'50����'),
			array('key'=>'b','value'=>'50-100'),
			array('key'=>'c','value'=>'100-500'),
			array('key'=>'d','value'=>'500-1000'),
			array('key'=>'e','value'=>'1000-2000'),
			array('key'=>'f','value'=>'2000-5000'),
			array('key'=>'g','value'=>'5000����'),
		);

		//ȡ��Ʒ����� 
		$gcategory_mod = & m('gcategory');
		$gcategory_list = $gcategory_mod->find(array('conditions' => 'parent_id = 0'));
		$this->assign('gcategory_list',$gcategory_list);
		$this->assign('priceRegion',$priceRegion);
		$this->assign('goods_list', $goods_list);
		$this->_format_page($page);
		$this->assign('page_info',$page);
		/* �Ƿ����������� */
		$store_id = $this->visitor->get('store_id');
    	$this->assign('store_id',$store_id);
    	
		$this->display('storeadmin.kjzf.brand.html');	
	}
	//����С������ 
    public function addToCart() {
    	header("Content-type=text/html;charset=gbk");
    	$id = empty($_GET['goods_id']) ? 0 :intval($_GET['goods_id']);
    	$store_id = empty($_GET['store_id']) ? 0 :intval($_GET['store_id']);
    	$uid = empty($_GET['uid']) ? 0 :intval($_GET['uid']);
    	$mobile = $_GET['mobile'];
    	if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $data = $this->_get_common_info($id,$store_id);
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
		$this->assign('uid',$uid);
		$this->assign('this_store_id',$store_id);
		$this->assign('mobile',$mobile);
        $this->assign('guest_comment_enable', Conf::get('guest_comment'));    	
    	$this->display("addToCart.html");
    }
	/**
     * ȡ�ù�����Ϣ
     *
     * @param   int     $id
     * @return  false   ʧ��
     *          array   �ɹ�
     */
    function _get_common_info($id,$store_id)
    {
        $cache_server =& cache_server();
        $key = 'page_of_goods_' . $id;
        $data = $cache_server->get($key);
        $data = false;
        if ($data === false)
        {
            $cached = false;
            $data = array('id' => $id);
			//����ΪƷ���̳�
			//������Ʒ��ӦΪƷ����Ʒ
			//$area_type = 'brandmall';
            /* ��Ʒ��Ϣ */
            $goods = $this->_goods_mod->get_info($id);
            $stock = $this->_paila_mod->getAll("select * from pa_paila_goods where goods_id=".$id." AND store_id='".$store_id."'" );
            if (!$goods || $goods['if_show'] == 0 || $goods['closed'] == 1 || $goods['state'] != 1)
            {
                $this->show_warning('goods_not_exist');
                return false;
            }
            $goods['tags'] = $goods['tags'] ? explode(',', trim($goods['tags'], ',')) : array();
            //�洢Ҫ�õ�ͼƬ
            $images_arr = array();

            //����goods�����ڲ�ͼƬ��ַ.   λ��ָ��ͬ��������
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
            $data['stock'] = $stock;      
            /* ������Ϣ */
            if (!$store_id)
            {
                $this->show_warning('store of goods is empty');
                return false;
            }
            //����store_data�������ͼƬ��ַ .λ��ָ��ͬ��������.
            $data['store_data']['store_logo'] = IMAGE_URL.$data['store_data']['store_logo'];
            $data['store_data']['store_owner']['portrait'] = IMAGE_URL.$data['store_data']['store_owner']['portrait'];
            
            //����images_arr�е�ͼƬ
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
            $this->set_store($data['store']);
        }

        return $data;
    }
    /* ��ֵ������Ϣ ����תUTF-8�����*/
	function _assign_common_info($data)
    {
        /* ��Ʒ��Ϣ */
        $goods = $data['goods'];
        $goods['stock'] = $data['stock']['0'];
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

        /* Ĭ��ͼƬ */
        $this->assign('default_image', Conf::get('default_goods_image'));

        /* ��Ʒ���� */
        $this->assign('share', $data['share']);

        $this->import_resource(array(
            'script' => 'jquery.jqzoom.js',
            'style' => 'res:jqzoom.css'
        ));
    }
	/**
     * ȡ������������Ϣ
     *
     * @param   int     $id
     * @return  false   ʧ��
     *          array   �ɹ�
     */
    function _get_paila_common_info($id)
    {
        $cache_server =& cache_server();
        $key = 'page_of_paila_goods_' . $id;
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $cached = false;
            $data = array('id' => $id);
            $goods = $this->_goods_mod->get_info($id);
            if (!$goods || $goods['if_show'] == 0 || $goods['closed'] == 1 || $goods['state'] != 1)
            {
                $this->show_warning('goods_not_exist');
                return false;
            }
            $goods['tags'] = $goods['tags'] ? explode(',', trim($goods['tags'], ',')) : array();
            //�洢Ҫ�õ�ͼƬ
            $images_arr = array();

            //����goods�����ڲ�ͼƬ��ַ.   λ��ָ��ͬ��������
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

            /* ������Ϣ */
            if (!$goods['store_id'])
            {
                $this->show_warning('store of goods is empty');
                return false;
            }
            //����store_data�������ͼƬ��ַ .λ��ָ��ͬ��������.
            $data['store_data']['store_logo'] = IMAGE_URL.$data['store_data']['store_logo'];
            $data['store_data']['store_owner']['portrait'] = IMAGE_URL.$data['store_data']['store_owner']['portrait'];
            
            //����images_arr�е�ͼƬ
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
    /* ��ֵ����������Ϣ ����תUTF-8�����*/
	function _assign_paila_common_info($data)
    {
        /* ��Ʒ��Ϣ */
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

        /* Ĭ��ͼƬ */
        $this->assign('default_image', Conf::get('default_goods_image'));

        /* ��Ʒ���� */
        $this->assign('share', $data['share']);

        $this->import_resource(array(
            'script' => 'jquery.jqzoom.js',
            'style' => 'res:jqzoom.css'
        ));
    }
	public function choosepro() {	
		$this->display('storeadmin.kjzf.choosepro.html');
	}
	public function kjzfchoose() {	
		$this->display('storeadmin.kjzfchoose.index.html');
	}
	public function kjzfdxrz() {	
		$this->display('storeadmin.kjzf.dxrz.html');
	}
	public function kjzfddxq() {	
		$this->display('storeadmin.kjzf.ddxq.html');
	}
	public function kjzfddwc() {	
		$this->display('storeadmin.kjzf.ddwc.html');
	}
	
	public function _get_brand_goods($conditions, $page,$count=false) {
		$store_id = $this->visitor->info['store_id'];
		/* ֻȡͨ����˵������̳ǵ���Ʒ */
        $conditions .= " AND area_type='brandmall' AND `status` = 1 AND g.store_id={$store_id}";
        //$conditions .= " AND `status` = 1 ";
    	if ($count)
        {
        	$this->_goods_mod->get_pailalist(array(
	            'conditions' => $conditions,
	            'count' => $count
	        ));
	        return $this->_goods_mod->getCount();
        }else 
        {
        	/* ȡ����Ʒ�б� */
	        $goods_list = $this->_goods_mod->get_pailalist(array(
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
	
	//ȡ�������̳���Ʒ�б�
    function _get_pailagoods($conditions, $page,$count=false)
    {
    	/* ֻȡͨ����˵������̳ǵ���Ʒ */
        $conditions .= " AND `status` = 1 AND pg.store_id = " . $this->visitor->get('store_id');
        //$conditions .= " AND `status` = 1 ";
    	if ($count)
        {
        	$this->_paila_mod->get_pailalist(array(
	            'conditions' => $conditions,
	            'count' => $count
	        ));
	        return $this->_paila_mod->getCount();
        }else 
        {
        	/* ȡ����Ʒ�б� */
	        $goods_list = $this->_paila_mod->get_pailalist(array(
	            'conditions' => $conditions,
	            'limit' => $page['limit'],
	        ));
	        //var_dump($goods_list);
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
    public function  userlogin()
    {
    	if (!IS_POST)
	    {
	    	$this->import_resource(array(
	                'script' => 'jquery.plugins/jquery.validate.js',
	        )); 
	    	$this->display("userlogin.html");
        }
    }
}