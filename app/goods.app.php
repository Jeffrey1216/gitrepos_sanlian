<?php

/* ��Ʒ */
class GoodsApp extends StorebaseApp
{
    var $_goods_mod;
    var $_ordergoods_mod;
    function __construct()
    {
        $this->GoodsApp();
    }
    function GoodsApp()
    {
        parent::__construct();
        $this->_goods_mod =& m('goods');
        $this->_storegoods_mod = & m('storegoods');
        $this->_ordergoods_mod = & m('ordergoods');
    }

    function index()
    {
        /* ���� id */
    	$store_mod = & m('store');

        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('û��ָ����Ʒ��ţ�');
            return;
        }
     //XX�ǴӺ�̨�鿴��Ʒ������ֵ������ǰ̨��ģ���ж�
	($_GET['xx'] == 1) ? $_GET['xx'] = 2 : '';
	if(!empty($_GET['xx']) && $_GET['xx']==2){
		
		$xx = $_GET['xx'];  
    		$this->assign('xx',$xx);
		$data = $this->_get_goods_info_byid($id);
    		$this->assign('data',$data);
       		$this->display('plx_detailed_x2.html');
    		return ;
    	}
        //ȡ��store_goods��goods��Ϣ
	$stock_info = $this->_storegoods_mod->getRow("select * from pa_store_goods sg where sg.gs_id =".$id);
	if (!$stock_info)
	{
		$this->show_warning('����Ʒ�����ڣ�');
		return ;
	}
        /* �ɻ������� */
        $data = $this->_get_common_info($stock_info['gs_id']);
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }
        /* ȡ��cate_id */
        $goods_info = $this->_goods_mod->get(array('conditions' => 'goods_id='.intval($data['goods']['goods_id'])));
		/* ȡ�õ�ǰλ�� */
       	$this->_get_curlocal($goods_info['cate_id']);
        //var_dump($curlocal);
        /* ����������� */
        $this->_update_views(intval($data['goods']['goods_id']));

        //�Ƿ�����֤��
        if (Conf::get('captcha_status.goodsqa'))
        {
            $this->assign('captcha', 1);
        }
        //ȡ����ͬ��������ϼ�����
        $goods_id = $data['goods']['goods_id'];       
        $sql = "select * from pa_gcategory where parent_id=(select parent_id from pa_gcategory where cate_id=(select g.cate_id from pa_goods g where g.goods_id='{$goods_id}'))";
        $gcategory_mod = & m("gcategory");
        $cates = $gcategory_mod->getAll($sql);
       	$this->assign('cates',$cates);
       	//�ϼ�����
       	$sql = "select * from pa_gcategory where cate_id=(select parent_id from pa_gcategory where cate_id=(select g.cate_id from pa_goods g where g.goods_id='{$goods_id}'))";
		$parentCate = $gcategory_mod->getRow($sql);
		$this->assign("parentCate",$parentCate);
		$this->assign('this_goods_id',$id);
        $this->assign('data',$data);
        $this->assign("SITE_URL", SITE_URL);
        $data['comments'] = $this->_get_goods_comment($goods_id, 10);
        $goods_mod=&m('goods');
       	$seo_data=$goods_mod->get('goods_id='.$id);
        $this->assign('goods_seo',$seo_data);
       	$this->display('plx_detailed.html');
    }
    
    //��Ʒ��˲鿴
	function _get_goods_info_byid($id) {
		
		$goods = $this->_goods_mod->get_goods_info($id);
		if (!$goods) {
			$this->show_warning('û�д�����Ʒ��');
			return;
		}
		$goods['tags'] = $goods['tags'] ? explode(',', trim($goods['tags'], ',')) : array();

		$images_arr = array();
		//����goods�����ڲ�ͼƬ��ַ.   λ��ָ��ͬ��������
		$goods['default_image'] = IMAGE_URL . $goods['default_image'];
		$goods['yimage_url'] = IMAGE_URL . $goods['yimage_url'];
		$goods['mimage_url'] = IMAGE_URL . $goods['mimage_url'];
		$goods['smimage_url'] = IMAGE_URL . $goods['smimage_url'];
		$goods['dimage_url'] = IMAGE_URL . $goods['dimage_url'];
		$goods['simage_url'] = IMAGE_URL . $goods['simage_url'];
		foreach ($goods['_images'] as $k => $good) {
			$goods['_images'][$k]['image_url'] = IMAGE_URL . $good['image_url'];
			$goods['_images'][$k]['thumbnail'] = IMAGE_URL . $good['thumbnail'];
			if ($k < 5) {
				$images_arr[] = $good;
			}
		}
		//����images_arr�е�ͼƬ
		foreach ($images_arr as $k => $img) {
			$images_arr[$k]['image_url'] = IMAGE_URL . $img['image_url'];
			$images_arr[$k]['yimage_url'] = IMAGE_URL . $img['yimage_url'];
			$images_arr[$k]['thumbnail'] = IMAGE_URL . $img['thumbnail'];
			$images_arr[$k]['mimage_url'] = IMAGE_URL . $img['mimage_url'];
			$images_arr[$k]['smimage_url'] = IMAGE_URL . $img['smimage_url'];
			$images_arr[$k]['dimage_url'] = IMAGE_URL . $img['dimage_url'];
			$images_arr[$k]['simage_url'] = IMAGE_URL . $img['simage_url'];
		}

		$this->assign('images_arr', $images_arr);
		$this->assign('goods', $goods);
		$data['goods'] = $goods;
		return $data;
	}

	/* ��Ʒ���� */
    function comments()
    {
        /* ���� id */
    	$store_mod = & m('store');

        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        //ȡ��store_goods��goods��Ϣ
        $stock_info = $this->_storegoods_mod->getRow("select * from pa_store_goods sg where sg.gs_id =".$id);
		if (!$stock_info)
		{
			$this->show_warning('Hacking Attempt');
			return ;
		}
        /* �ɻ������� */
        $data = $this->_get_common_info($stock_info['gs_id']);

        //ȡ����ͬ��������ϼ�����
        $goods_id = $data['goods']['goods_id'];
        $sql = "select * from pa_gcategory where parent_id=(select parent_id from pa_gcategory where cate_id=(select g.cate_id from pa_goods g where g.goods_id='{$goods_id}'))";
        $gcategory_mod = & m("gcategory");
        $cates = $gcategory_mod->getAll($sql);
       	$this->assign('cates',$cates);
       	$this->assign('IMAGE_URL',IMAGE_URL);
       	//�ϼ�����
       	$sql = "select * from pa_gcategory where cate_id=(select parent_id from pa_gcategory where cate_id=(select g.cate_id from pa_goods g where g.goods_id='{$goods_id}'))";
		$parentCate = $gcategory_mod->getRow($sql);
		$this->assign("parentCate",$parentCate);
        $this->assign('data',$data);
        //ȡ��ũҵר����������
		$this->assign('navigation' , $this->_get_agro_nav());
		//ȡ��ũҵר��ʡ������
		$this->assign('region',$this->_get_region());
        //$data = false;
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }
		$data['comments'] = $this->_get_goods_comment($goods_id, 10);
        /* ��ֵ��Ʒ���� */
        $data = $this->_get_goods_comment($stock_info['goods_id'], 10);        
        $this->_assign_goods_comment($data);        
        $this->display('plx_goods.comments.html');	  
    }
    /* ���ۼ�¼ */
    function saleslog()
    {
        /* ���� id */
    	$store_mod = & m('store');

        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        //ȡ��store_goods��goods��Ϣ
        $stock_info = $this->_storegoods_mod->getRow("select * from pa_store_goods sg where sg.gs_id =".$id);
		if (!$stock_info)
		{
			$this->show_warning('Hacking Attempt');
			return ;
		}
        /* �ɻ������� */
        $data = $this->_get_common_info($stock_info['gs_id']);
        //ȡ����ͬ��������ϼ�����
        $goods_id = $data['goods']['goods_id'];
        $sql = "select * from pa_gcategory where parent_id=(select parent_id from pa_gcategory where cate_id=(select g.cate_id from pa_goods g where g.goods_id='{$goods_id}'))";
        $gcategory_mod = & m("gcategory");
        $cates = $gcategory_mod->getAll($sql);
       	$this->assign('cates',$cates);
       	//�ϼ�����
       	$sql = "select * from pa_gcategory where cate_id=(select parent_id from pa_gcategory where cate_id=(select g.cate_id from pa_goods g where g.goods_id='{$goods_id}'))";
		$parentCate = $gcategory_mod->getRow($sql);
		$this->assign("parentCate",$parentCate);
        $this->assign('data',$data);
        //ȡ��ũҵר����������
		$this->assign('navigation' , $this->_get_agro_nav());
		//ȡ��ũҵר��ʡ������
		$this->assign('region',$this->_get_region());
        //$data = false;
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }
        /* ��ֵ���ۼ�¼ */
        $data = $this->_get_sales_log($stock_info['goods_id'], 10);
        $this->assign('sales_list',$data['sales_list']);
        $this->assign('page_info',$data['page_info']);
       	$this->display('plx_goods.saleslog.html');
    }
    function qa()
    {
        $goods_qa =& m('goodsqa');
        /* ���� id */
    	$store_mod = & m('store');

        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
       	//ȡ��store_goods��goods��Ϣ
        $stock_info = $this->_storegoods_mod->getRow("select * from pa_store_goods sg where sg.gs_id =".$id);
		if (!$stock_info)
		{
			$this->show_warning('Hacking Attempt');
			return ;
		}
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        //ȡ��store_goods��goods��Ϣ
        $store_goods_info = $this->_storegoods_mod->getRow("select * from pa_store_goods sg where sg.gs_id =".$id);
		if (!$stock_info)
		{
			$this->show_warning('Hacking Attempt');
			return ;
		}
        /* �ɻ������� */
        $data = $this->_get_common_info($stock_info['gs_id']);
         if (!$id)
         {
            $this->show_warning('Hacking Attempt');
            return;
         }
        if(!IS_POST)
        {
            //ȡ����ͬ��������ϼ�����
	        $goods_id = $data['goods']['goods_id'];
	        $sql = "select * from pa_gcategory where parent_id=(select parent_id from pa_gcategory where cate_id=(select g.cate_id from pa_goods g where g.goods_id='{$goods_id}'))";
	        $gcategory_mod = & m("gcategory");
	        $cates = $gcategory_mod->getAll($sql);
	       	$this->assign('cates',$cates);
	       	//�ϼ�����
	       	$sql = "select * from pa_gcategory where cate_id=(select parent_id from pa_gcategory where cate_id=(select g.cate_id from pa_goods g where g.goods_id='{$goods_id}'))";
			$parentCate = $gcategory_mod->getRow($sql);
			$this->assign("parentCate",$parentCate);
            $this->assign('data',$data);
            if ($data === false)
            {
                return;
            }
            else
            {
                $this->_assign_common_info($data);
            }
            $data = $this->_get_goods_qa($id, 10);

            $this->_assign_goods_qa($data);
            //ȡ��ũҵר����������
			$this->assign('navigation' , $this->_get_agro_nav());
			//ȡ��ũҵר��ʡ������
			$this->assign('region',$this->_get_region());
			$this->assign('IMAGE_URL',IMAGE_URL);
            //�Ƿ�����֤��
            if (Conf::get('captcha_status.goodsqa'))
            {
                $this->assign('captcha', 1);
            }
	       	$this->display('plx_goods.qa.html');
        }
        else
        {
            /* �������ο����� */
            if (!Conf::get('guest_comment') && !$this->visitor->has_login)
            {
                $this->show_warning('guest_comment_disabled');

                return;
            }
            $content = (isset($_POST['content'])) ? trim($_POST['content']) : '';
            //$type = (isset($_POST['type'])) ? trim($_POST['type']) : '';
            $email = (isset($_POST['email'])) ? trim($_POST['email']) : '';
            $hide_name = (isset($_POST['hide_name'])) ? trim($_POST['hide_name']) : '';
            if (empty($content))
            {
                $this->show_warning('content_not_null');
                return;
            }
            //����֤����ʼ������ж�

            if (Conf::get('captcha_status.goodsqa'))
            {
                if (base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
                {
                    $this->show_warning('captcha_failed');
                    return;
                }
            }
            if (!empty($email) && !is_email($email))
            {
                $this->show_warning('email_not_correct');
                return;
            }
            $user_id = empty($hide_name) ? $_SESSION['user_info']['user_id'] : 0;
            $conditions = 'g.goods_id ='.$store_goods_info['goods_id'];
            $goods_mod = & m('goods');
            $ids = $goods_mod->get(array(
                'fields' => 'goods_name,store_goods.store_id',
            	'join'       => 'belongs_to_store,g.goods,on g.goods_id = store_goods.goods_id',
                'conditions' => $conditions
            ));
            extract($ids);
            $data = array(
                'question_content' => $content,
                'type' => 'goods',
                'item_id' => $id,
                'item_name' => addslashes($ids['goods_name']),
                'store_id' => $ids['store_id'],
                'email' => $email,
                'user_id' => $user_id,
                'time_post' => gmtime(),
            );
            if ($goods_qa->add($data))
            {
                header("Location: index.php?app=goods&act=qa&id={$id}#module\n");
                exit;
            }
            else
            {
                $this->show_warning('post_fail');
                exit;
            }
        }
    }
	
    /**
     * ȡ�ù�����Ϣ
     *
     * @param   int     $id
     * @return  false   ʧ��
     *          array   �ɹ�
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

            /* ��Ʒ��Ϣ */
            $goods = $this->_goods_mod->get_info($id);
            if (!$goods || $goods['if_show'] == 0 || $goods['closed'] == 1  || $goods['status'] != 1)
            {
                $this->show_warning('goods_not_exist');
                return false;
            }
            $goods['tags'] = $goods['tags'] ? explode(',', trim($goods['tags'], ',')) : array();
            //�洢Ҫ�õ�ͼƬ
            $images_arr = array();
			
            //����goods�����ڲ�ͼƬ��ַ.   λ��ָ��ͬ��������
            $goods['default_image'] = IMAGE_URL.$goods['default_image'];
            $goods['yimage_url'] = IMAGE_URL.$goods['yimage_url'];
            $goods['mimage_url'] = IMAGE_URL.$goods['mimage_url'];
            $goods['smimage_url'] = IMAGE_URL.$goods['smimage_url'];
            $goods['dimage_url'] = IMAGE_URL.$goods['dimage_url'];
            $goods['simage_url'] = IMAGE_URL.$goods['simage_url'];
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
            $this->set_store($goods['store_id']);
            $data['store_data'] = $this->get_store_data();
            
            //����store_data�������ͼƬ��ַ .λ��ָ��ͬ��������.
            $data['store_data']['store_logo'] = IMAGE_URL.$data['store_data']['store_logo'];
            $data['store_data']['store_owner']['portrait'] = IMAGE_URL.$data['store_data']['store_owner']['portrait'];

            /* ��ǰλ�� */
            $data['cur_local'] = $this->_get_curlocal($goods['cate_id']);
            $data['goods']['related_info'] = $this->_get_related_objects($data['goods']['tags']);
            /* �������� */
            $data['share'] = $this->_get_share($goods);
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

            //���� �����
            $goods_statistics_mod = & m("goodsstatistics");
            $goods_statistics_info_by_brand = $goods_statistics_mod->find(array(
            	'conditions'	=>	'g.if_show = 1 AND g.closed = 0 AND g.status=1 AND g.cate_id='.$goods['cate_id'].' AND store_goods.store_id='.STORE_ID,
            	'fields'	=> 	'g.goods_id,g.goods_name,g.price,g.default_image,g.credit,g.simage_url,g.smimage_url,store_goods.gs_id',
            	'join'	=> 'belongs_to_goods,belongs_to_store',
            	'order'	=> 'sales DESC',
            	'limit'	=> 5
            ));
            foreach($goods_statistics_info_by_brand as $k => $v) {
            	$goods_statistics_info_by_brand[$k]['simage_url'] = IMAGE_URL . $v['simage_url'];
            	$goods_statistics_info_by_brand[$k]['smimage_url'] = IMAGE_URL . $v['smimage_url'];
            }
            $this->assign('goods_statistics_info',$goods_statistics_info_by_brand);
            if (isset($_GET['store_id']))
            {
            	$this->assign('store_id', intval(($_GET['store_id'])));
            }
            
            $cache_server->set($key, $data, 1800);
        }
        if ($cached)
        {
            $this->set_store($data['goods']['store_id']);
        }
        return $data;
    }
	
    function _get_related_objects($tags)
    {
        if (empty($tags))
        {
            return array();
        }
        $tag = $tags[array_rand($tags)];
        $ms =& ms();

        return $ms->tag_get($tag);
    }

    /* ��ֵ������Ϣ */
    function _assign_common_info($data)
    {
        /* ��Ʒ��Ϣ */
        $goods = $data['goods'];
        $this->assign('goods', $goods);
        $this->assign('sales_info', sprintf(LANG::get('sales'), $goods['sales'] ? $goods['sales'] : 0));
        $this->assign('comments', sprintf(LANG::get('comments'), $goods['comments'] ? $goods['comments'] : 0));

        /* ������Ϣ */
        $this->assign('store', $data['store_data']);
        
        /* �����ʷ */
        $this->assign('goods_history', $this->_get_goods_history($data['id']));

        /* Ĭ��ͼƬ */
        $this->assign('default_image', Conf::get('default_goods_image'));
        
        /* ��ǰλ�� */
        $this->_curlocal($data['cur_local']);

        /* ����seo��Ϣ */
        $this->_config_seo($this->_get_seo_info($data['goods']));

        /* ��Ʒ���� */
        $this->assign('share', $data['share']);
		//exit;
        $this->import_resource(array(
            'script' => 'jquery.jqzoom.js',
            'style' => 'res:jqzoom.css'
        ));
    }
    
    //�����ʷ��¼
    public function truncateHistory() {
    	if(isset($_GET['id'])) {
    		ecm_setcookie('goodsBrowseHistory', null);
    		header("Location:index.php?app=search&cate_id=".$_GET['id']);
    	} else {
    		return ;
    	}
    	
    }

    /* ȡ�������ʷ */
    function _get_goods_history($id, $num = 9)
    {
        $goods_list = array();
        $goods_ids  = ecm_getcookie('goodsBrowseHistory');
        $goods_ids  = $goods_ids ? explode(',', $goods_ids) : array();
        if ($goods_ids)
        {
            $rows = $this->_goods_mod->find(array(
                'conditions' => $goods_ids,
                'fields'     => 'goods_name,default_image,price,simage_url',
            ));
            foreach ($goods_ids as $goods_id)
            {
                if (isset($rows[$goods_id]))
                {
                    empty($rows[$goods_id]['default_image']) && $rows[$goods_id]['default_image'] = Conf::get('default_goods_image');
                    $goods_list[] = $rows[$goods_id];
                }
            }
        }
        $goods_ids[] = $id;
        if (count($goods_ids) > $num)
        {
            unset($goods_ids[0]);
        }
        ecm_setcookie('goodsBrowseHistory', join(',', array_unique($goods_ids)));
        foreach($goods_list as $k => $v) {
        	$goods_list[$k]['simage_url'] = IMAGE_URL . $v['simage_url'];
        }
		//var_dump($goods_list);
        return $goods_list;
    }

    /* ȡ�����ۼ�¼ */
    function _get_sales_log($goods_id, $num_per_page)
    {
        $data = array();

        $page = $this->_get_page($num_per_page);
        $order_goods_mod =& m('ordergoods');
        $sales_list = $order_goods_mod->find(array(
            'conditions' => "goods_id = '$goods_id' AND status = '" . ORDER_FINISHED . "'",
            'join'  => 'belongs_to_order',
            'fields'=> 'order_goods.order_id,order_alias.finished_time,order_goods.quantity,buyer_id, buyer_name, add_time, anonymous, goods_id, goods_name , specification ,specification, price, quantity, evaluation ',
            'count' => true,
            'order' => 'add_time desc',
            'limit' => $page['limit'],
        ));
        $order_log_mod = & m("orderlog");
        //�����־��Ϣ
        foreach($sales_list as $k => $sale) {
        	$sa = $order_log_mod->get(array('conditions' => 'order_id='.$sale['order_id']));
        	$sales_list[$k]['order_status'] = $sa['order_status'];
        }
        
        $data['sales_list'] = $sales_list;

        $page['item_count'] = $order_goods_mod->getCount();
        $this->_format_page($page);
        $data['page_info'] = $page;
        $data['more_sales'] = $page['item_count'] > $num_per_page;
        return $data;
    }

    /* ��ֵ���ۼ�¼ */
    function _assign_sales_log($data)
    {
        $this->assign('sales_list', $data['sales_list']);
        $this->assign('page_info',  $data['page_info']);
        $this->assign('more_sales', $data['more_sales']);
    }

    /* ȡ����Ʒ���� */
    function _get_goods_comment($goods_id, $num_per_page)
    {
        $data = array();

        $page = $this->_get_page($num_per_page);
        $comments = $this->_ordergoods_mod->find(array(
            'conditions' => "goods_id = '$goods_id' AND evaluation_status = '1' AND comment!=''",
            'join'  => 'belongs_to_order',
            'fields'=> 'buyer_id, buyer_name, anonymous, evaluation_time, comment, evaluation',
            'count' => true,
            'order' => 'evaluation_time desc',
            'limit' => $page['limit'],
        ));
        $data['comments'] = $comments;

        $page['item_count'] = $this->_ordergoods_mod->getCount();
        $this->_format_page($page);
        $data['page_info'] = $page;
        $data['more_comments'] = $page['item_count'] > $num_per_page;	
        return $data;
    }

    /* ��ֵ��Ʒ���� */
    function _assign_goods_comment($data)
    {
    	$this->assign('goods_comments', $data['comments']);
        $this->assign('page_info',      $data['page_info']);
        $this->assign('more_comments',  $data['more_comments']);
    }

    /* ȡ����Ʒ��ѯ */
    function _get_goods_qa($goods_id,$num_per_page)
    {
        $page = $this->_get_page($num_per_page);
        $goods_qa = & m('goodsqa');
        $qa_info = $goods_qa->find(array(
            'join' => 'belongs_to_user',
            'fields' => 'member.user_name,question_content,reply_content,time_post,time_reply',
            'conditions' => '1 = 1 AND item_id = '.$goods_id . " AND type = 'goods'",
            'limit' => $page['limit'],
            'order' =>'time_post desc',
            'count' => true
        ));
        $page['item_count'] = $goods_qa->getCount();
        $this->_format_page($page);

        //�����½������email
        if (!empty($_SESSION['user_info']))
        {
            $user_mod = & m('member');
            $user_info = $user_mod->get(array(
                'fields' => 'email',
                'conditions' => '1=1 AND user_id = '.$_SESSION['user_info']['user_id']
            ));
            extract($user_info);
        }

        return array(
            'email' => $email,
            'page_info' => $page,
            'qa_info' => $qa_info,
        );
    }

    /* ��ֵ��Ʒ��ѯ */
    function _assign_goods_qa($data)
    {
        $this->assign('email',      $data['email']);
        $this->assign('page_info',  $data['page_info']);
        $this->assign('qa_info',    $data['qa_info']);
    }

    /* ����������� */
    function _update_views($id)
    {
        $goodsstat_mod =& m('goodsstatistics');
        $goodsstat_mod->edit($id, "views = views + 1");
    }

    /**
     * ȡ�õ�ǰλ��
     *
     * @param int $cate_id ����id
     */
    function _get_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $gcategory_mod =& bm('gcategory');
            $parents = $gcategory_mod->get_ancestor($cate_id, true);
        }

        $curlocal = array(
            array('text' => LANG::get('all_categories'), 'url' => url('app=category')),
        );
        foreach ($parents as $category)
        {
            $curlocal[] = array('text' => $category['cate_name'], 'url' => url('app=search&cate_id=' . $category['cate_id']));
        }
        $curlocal[] = array('text' => LANG::get('goods_detail'));
		//var_dump($curlocal);
        return $curlocal;
    }

    function _get_share($goods)
    {
        $m_share = &af('share');
        $shares = $m_share->getAll();
        $shares = array_msort($shares, array('sort_order' => SORT_ASC));
        $goods_name = ecm_iconv(CHARSET, 'utf-8', $goods['goods_name']);
        $goods_url = urlencode(SITE_URL . '/' . str_replace('&amp;', '&', url('app=goods&id=' . $goods['gs_id'])));
        $site_title = ecm_iconv(CHARSET, 'utf-8', Conf::get('site_title'));
        $share_title = urlencode($goods_name . '-' . $site_title);
        foreach ($shares as $share_id => $share)
        {
            $shares[$share_id]['link'] = str_replace(
                array('{$link}', '{$title}'),
                array($goods_url, $share_title),
                $share['link']);
        }
        //var_dump($shares);
        return $shares;
    }
    
    function _get_seo_info($data) {
		$seo_info = $keywords = array();
		(!empty($data['seo_title'])) ? $seo_info['title'] = $data['seo_title'] : $seo_info['title'] = $data['goods_name'];
		$seo_info['keywords'] = $data['seo_keywords'];
		$seo_info['description'] = strip_tags($data['seo_description']);
		return $seo_info;
    }
    
    public function _get_agro_nav(){
    	$_gcategroy_mod = & m('gcategory');
	    $navigation = $_gcategroy_mod->getAll('select cate_name,cate_id from  pa_gcategory where parent_id=0 AND mall_type=3');
	    //var_dump($navigation);
	    return  $navigation;
    }
     /*ȡ������1����������Ϣ*/
	public  function _get_region()
	{
    	$region_mod=&m ('region');
    	$region=$region_mod->getAll("SELECT * from pa_region WHERE level=1");
    	//var_dump($region);
    	return $region;	
	}
	
	public function textImgUrl()
	{
		$img_url = trim($_GET['url']);
		$msgArr = get_headers($img_url, 1);
		$this->json_error($msgArr[0]);
	}
}

?>
