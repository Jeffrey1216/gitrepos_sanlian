<?php
define("MaxGoodsNum",2000); //���õ����������������Ʒ��

/* ���̿����� */
class StoreApp extends BackendApp
{
    var $_store_mod;
    var $_store_goods_mod;

    function __construct()
    {
        $this->StoreApp();
    }

    function StoreApp()
    {
        parent::__construct();
        $this->_store_mod =& m('store');
        $this->_store_goods_mod = &m('storegoods');
    }

    function index()
    {
        $conditions = empty($_GET['wait_verify']) ? "state <> '" . STORE_APPLYING . "'" : "state = '" . STORE_APPLYING . "'";
        $filter = $this->_get_query_conditions(array(
            array(
                'field' => 'store_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'sgrade',
            ),
        ));
        $owner_name = trim($_GET['owner_name']);
        if ($owner_name)
        {

            $filter .= " AND (user_name LIKE '%{$owner_name}%' OR owner_name LIKE '%{$owner_name}%') ";
        }
        //��������
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
                $sort  = 'sort_order';
                $order = '';
            }
        }
        else
        {
            $sort  = 'store_id';
            $order = 'desc';
        }
        $this->assign('filter', $filter);
        $conditions .= $filter;
        $page = $this->_get_page();
        $stores = $this->_store_mod->find(array(
            'conditions' => $conditions,
            'join'  => 'belongs_to_user',
            'fields'=> 'this.*,member.user_name',
            'limit' => $page['limit'],
            'count' => true,
            'order' => "$sort $order"
        ));
        $sgrade_mod =& m('sgrade');
        $grades = $sgrade_mod->get_options();
        $this->assign('sgrades', $grades);

        $states = array(
            STORE_APPLYING  => LANG::get('wait_verify'),
            STORE_OPEN      => Lang::get('open'),
            STORE_CLOSED    => Lang::get('close'),
        );
        foreach ($stores as $key => $store)
        {
            $stores[$key]['sgrade'] = $grades[$store['sgrade']];
            $stores[$key]['state'] = $states[$store['state']];
            $certs = empty($store['certification']) ? array() : explode(',', $store['certification']);
            for ($i = 0; $i < count($certs); $i++)
            {
                $certs[$i] = Lang::get($certs[$i]);
            }
            $stores[$key]['certification'] = join('<br />', $certs);
        }
        $this->assign('stores', $stores);
		$this->assign('chan_store',"true");
        $page['item_count'] = $this->_store_mod->getCount();
        $this->import_resource(array('script' => 'inline_edit.js'));
        $this->_format_page($page);
        $this->assign('filtered', $filter? 1 : 0); //�Ƿ��в�ѯ����
        $this->assign('page_info', $page);	
        $this->display('store.index.html');
    }
	function settle(){
    	$this->display('settle.index.html');
    }
    /**
     * ȫ�����
     * @author wscsky
     * 
     */
    function stock()
    {
    	$store_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	$page = $this->get_pages();
    	$pagelimit = $page['limit'];
        $conditions = '';
 		if(isset($_GET['goods_name']))
 		{
    		$conditions.= " AND g.goods_name like "."'%".$_GET['goods_name']."%'";	
    	}
    	//�������ݲ���
 		if($_GET['output'] == true){
 			$stock_all_info = $this->_store_goods_mod->getAll("select s.store_name,sg.stock,pp.pr_stock,sg.selllog,pp.pr_selllog,gs.spec_1,gs.spec_2,gs.color_rgb,gs.commodity_code,g.goods_name,gs.commodity_code,g.goods_id,gs.price,gs.gprice,gs.zprice,gs.credit 
                    from pa_store_goods sg 
                    left join pa_goods_spec gs on gs.spec_id = sg.spec_id
                    left join pa_goods g on g.goods_id =sg.goods_id 
                    left join pa_store s on s.store_id=sg.store_id
                    left Join pa_promotion_store_goods psg on psg.gs_id = sg.goods_id
                    left Join pa_promotion pp on pp.promotion_id = psg.promotion_id
                    where sg.store_id =".$store_id.$conditions);
            
            foreach ($stock_all_info as $k=>&$v)
        	{
        	    $v['pr_stock'] && $v['stock']+=$v['pr_stock'];
                $v['pr_selllog'] && $v['selllog']+=$v['pr_selllog'];
                
        	}
        
    		$this->StockExcel($stock_all_info);
    		exit();
    	}
    	
    	//�����ҳ����
    	$stock_info_count = $this->_store_goods_mod->getOne("select count(*) as c from pa_store_goods sg left join pa_goods_spec gs on gs.spec_id = sg.spec_id left join pa_goods g on g.goods_id =gs.goods_id left join pa_store s on s.store_id=sg.store_id where sg.store_id=".$store_id.$conditions);
    	//��ҳ����
    	$conditions.= ' limit '.$page['limit'];
    
    	$stock_info = $this->_store_goods_mod->getAll("select s.store_name,sg.stock,pp.pr_stock,sg.selllog,pp.pr_selllog,gs.spec_1,gs.spec_2,gs.color_rgb,gs.commodity_code,g.goods_name,gs.commodity_code,g.goods_id,gs.price,gs.gprice,gs.zprice,gs.credit 
            from pa_store_goods sg 
            left join pa_goods_spec gs on gs.spec_id = sg.spec_id 
            left join pa_goods g on g.goods_id =sg.goods_id 
			left join pa_store s on s.store_id=sg.store_id
            left Join pa_promotion_store_goods psg on psg.gs_id = sg.goods_id
            left Join pa_promotion pp on pp.promotion_id = psg.promotion_id
            where sg.store_id =".$store_id.$conditions);  	

    	foreach ($stock_info as $k=>&$v)
    	{
    	    $v['pr_stock'] && $v['stock']+=$v['pr_stock'];
            $v['pr_selllog'] && $v['selllog']+=$v['pr_selllog'];
    		$v['samount']=$v['stock']*$v['price'];
            
    	}
    	$page['item_count'] = $stock_info_count; 
    	$this->_format_page($page); 	
    	$this->assign('store_id',$store_id);
    	$this->assign('stock_info',$stock_info);
    	$this->assign('page_info', $page);
    	$this->display('stock.all.html'); 
    }
    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* �Ƿ���� */
            $store = $this->_store_mod->get_info($id);
            if (!$store)
            {
                $this->show_warning('store_empty');
                return;
            }
            if ($store['certification'])
            {
                $certs = explode(',', $store['certification']);
                foreach ($certs as $cert)
                {
                    $store['cert_' . $cert] = 1;
                }
            }
            $this->assign('store', $store);
		    $gcategory_mod = & m('gcategory');
            $gcategory_info = $gcategory_mod->getAll("select * from pa_gcategory where parent_id=0");
            $this->assign('gcategory', $gcategory_info);
            $sgrade_mod =& m('sgrade');
            $scate = $this->_store_mod->getAll('select cs.cate_id,gy.cate_name from pa_store s left join pa_category_store cs on cs.store_id=s.store_id left join pa_gcategory gy on gy.cate_id=cs.cate_id where s.store_id='.$id);
            if($scate[0][cate_id] == 0)
        	{
        		$scate = $gcategory_mod->getAll('select * from pa_gcategory where parent_id=0');
       	 	}
       	 	$this->assign('scate',$scate);	
            $this->assign('sgrades', $sgrade_mod->get_options());

            $this->assign('states', array(
                STORE_OPEN   => Lang::get('open'),
                STORE_CLOSED => Lang::get('close'),
            ));

            $this->assign('recommended_options', array(
                '1' => Lang::get('yes'),
                '0' => Lang::get('no'),
            ));

            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));

            $this->assign('scategories', $this->_get_scategory_options());

            $scates = $this->_store_mod->getRelatedData('has_scategory', $id);
            $this->assign('scates', array_values($scates));

            /* ����jQuery�ı���֤��� */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js,mlselection.js'
            ));
            $this->assign('enabled_subdomain', ENABLED_SUBDOMAIN);
            $this->display('store.form.html');
        }
        else
        {

            /* ��������Ƿ��Ѵ��� */
            if (!$this->_store_mod->unique(trim($_POST['store_name']), $id))
            {
                $this->show_warning('name_exist');
                return;
            }
            $store_info = $this->_store_mod->get_info($id);
            $domain = empty($_POST['domain']) ? '' : trim($_POST['domain']);
            if ($domain && $domain != $store_info['domain'])
            {
                if (!$this->_store_mod->check_domain($domain, Conf::get('subdomain_reserved'), Conf::get('subdomain_length')))
                {
                    $this->show_warning($this->_store_mod->get_error());

                    return;
                }
            }
			$dat=$this->_upload_files($id);
            $data = array(
                'store_name'   => $_POST['store_name'],
                'owner_name'   => $_POST['owner_name'],
                'owner_card'   => $_POST['owner_card'],
                'region_id'    => $_POST['region_id'],
                'region_name'  => $_POST['region_name'],
                'address'      => $_POST['address'],
                'zipcode'      => $_POST['zipcode'],
                'tel'          => $_POST['tel'],
                'sgrade'       => $_POST['sgrade'],
                'end_time'     => empty($_POST['end_time']) ? 0 : gmstr2time(trim($_POST['end_time'])),
                'state'        => $_POST['state'],
                'sort_order'   => $_POST['sort_order'],
                'recommended'  => $_POST['recommended'],
                'domain'       => $domain,
            	'store_logo'   => $dat['dir'],
            );          
            $data['state'] == STORE_CLOSED && $data['close_reason'] = $_POST['close_reason'];
            $certs = array();
            isset($_POST['autonym']) && $certs[] = 'autonym';
            isset($_POST['material']) && $certs[] = 'material';
            $data['certification'] = join(',', $certs);

            $old_info = $this->_store_mod->get_info($id); // �޸�ǰ�ĵ�����Ϣ
            $this->_store_mod->edit($id, $data);
			$store_mod  =& m('store');
            $cod_cates = $_POST['cod_cates'];
			if(empty($cod_cates)){
               	$cate_id = intval($_POST['cate_id']);
      		 	$store_mod->unlinkRelation('has_scategory', $id);
               	$store_mod->createRelation('has_scategory', $id, $cate_id);
				} else {
				$store_mod->unlinkRelation('has_scategory', $id);
				foreach ($cod_cates as $key => $val)
				{
					$cate_id = intval($val);
              		$store_mod->createRelation('has_scategory', $id, $cate_id);
				}
			}
            /* ����޸��˵���״̬��֪ͨ���� */
            if ($old_info['state'] != $data['state'])
            {
                $ms =& ms();
                if ($data['state'] == STORE_CLOSED)
                {
                    // �رյ���
                    $subject = Lang::get('close_store_notice');
                    //$content = sprintf(Lang::get(), $data['close_reason']);
                    $content = get_msg('toseller_store_closed_notify',array('reason' => $data['close_reason']));
                }
                else
                {
                    // ��������
                    $subject = Lang::get('open_store_notice');
                    $content = Lang::get('toseller_store_opened_notify');
                }
                $ms->pm->send(MSG_SYSTEM, $old_info['store_id'], '', $content);
                $this->_mailto($old_info['email'], $subject, $content);
            }

            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            $this->show_message('edit_ok',
                'back_list',    'index.php?app=store&page=' . $ret_page,
                'edit_again',   'index.php?app=store&amp;act=edit&amp;id=' . $id
            );
        }
    }
	function _upload_files($id)
    { 	
    	import('uploader.lib');
    	$daimg = array();
    	$file = $_FILES['file'];
    	$Filedir = 'data/files/store_'.$id.'/other/';
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
            $uploader->save('data/files/store_'.$id.'/other',$filena);
			$daimg['dir']=$Filedir.$FileName;
    	}
    	return $daimg;
    }

    //�첽�޸�����
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();
       if (in_array($column ,array('recommended','sort_order')))
       {
           $data[$column] = $value;
           $this->_store_mod->edit($id, $data);
           if(!$this->_store_mod->has_error())
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
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_store_to_drop');
            return;
        }

        $ids = explode(',', $id);
        foreach ($ids as $id)
        {
            $this->_drop_store_image($id); // ע������Ҫ��ɾ��ͼƬ����ɾ�����̣���Ϊɾ��ͼƬʱҪ�������Ϣ
        }
        if (!$this->_store_mod->drop($ids))
        {
            $this->show_warning($this->_store_mod->get_error());
            return;
        }

        /* ֪ͨ���� */
        $user_mod =& m('member');
        $users = $user_mod->find(array(
            'conditions' => "user_id" . db_create_in($ids),
            'fields'     => 'user_id, user_name, email',
        ));
        foreach ($users as $user)
        {
            $ms =& ms();
            $subject = Lang::get('drop_store_notice');
            $content = get_msg('toseller_store_droped_notify');
            $ms->pm->send(MSG_SYSTEM, $user['user_id'], $subject, $content);
            $this->_mailto($user['email'], $subject, $content);
        }

        $this->show_message('drop_ok');
    }

    /* �������� */
    function update_order()
    {
        if (empty($_GET['id']))
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        $ids = explode(',', $_GET['id']);
        $sort_orders = explode(',', $_GET['sort_order']);
        foreach ($ids as $key => $id)
        {
            $this->_store_mod->edit($id, array('sort_order' => $sort_orders[$key]));
        }

        $this->show_message('update_order_ok');
    }

    /* �鿴������������� */
    function view()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        /* �Ƿ���� */
        $store = $this->_store_mod->get_info($id);
        if (!$store)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        if (!IS_POST)
        {
            $sgrade_mod =& m('sgrade');
            $sgrades = $sgrade_mod->get_options();
            $store['sgrade'] = $sgrades[$store['sgrade']];
            $this->assign('store', $store);
            $gcategory_mod = & m('gcategory');
            $scates = $gcategory_mod->getAll("select gg.cate_id,gg.cate_name from  pa_category_store cg left join pa_gcategory gg on gg.cate_id=cg.cate_id where cg.store_id = ".$id);
            $this->assign('scates', $scates);
            $this->display('store.view.html');
        }
        else
        {
            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
			$use_money =empty($_POST['use_money']) ? -1 : floatval($_POST['use_money']);
            $smslog =&  m('smslog'); 
            import('class.smswebservice');    //������ŷ�����
        	$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
            /* ��׼ */
            if (isset($_POST['agree']))
            {
            	if($use_money < 0)
				{
					$this->show_warning("���̼��˷Ѳ���С��0");
					return ;
				}
                $this->_store_mod->edit($id, array(
                    'use_money'      => $use_money,
                    'add_time'   => gmtime(),
                    'sort_order' => 65535,
                ));
                $content = get_msg('toseller_store_passed_notify');	        		
				$smscontent = "�����Ѿ�Ϊ���̣�{$store['store_name']}���ۣ��۸�Ϊ��{$use_money}";
				$mobile = FI_VERIFY_MOBILE;
				$verifytype = "storeaudit";
	        	$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ�����֤�����
	        	//���ŷ��ͳɹ�
	        	if ($result == 0) 
	        	{
	        		$time = time();
	        		$smsdata['mobile'] = $mobile;
	        		$smsdata['smscontent'] = $smscontent;
	        		$smsdata['sendtime'] = $time;
	        		$smsdata['type'] = $verifytype; //��������
	        		$smslog->add($smsdata);
	       		}
                $this->_hook('after_opening', array('user_id' => $id));
                $this->show_message('channel_ok',
                    'back_list', 'index.php?app=store&wait_verify=1&page=' . $ret_page
                );
            }
            /* �ܾ� */
            elseif (isset($_POST['reject']))
            {
                $reject_reason = trim($_POST['reject_reason']);
                if (!$reject_reason)
                {
                    $this->show_warning('input_reason');
                    return;
                }

            	$content = get_msg('toseller_store_passed_notify');	        		
				$smscontent = "�����Ѿ��ܾ����̣�{$store['store_name']}�Ķ��ۣ�ԭ��Ϊ��{$reject_reason}";
				$user_mod = & m('member');
				$user_info = $user_mod->get($store['store_id']);
				$mobile = $user_info['mobile'];
				$verifytype = "storeaudit";
	        	$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ�����֤�����
	        	//���ŷ��ͳɹ�
	        	if ($result == 0) 
	        	{
	        		$time = time();
	        		$smsdata['mobile'] = $mobile;
	        		$smsdata['smscontent'] = $smscontent;
	        		$smsdata['sendtime'] = $time;
	        		$smsdata['type'] = $verifytype; //��������
	        		$smslog->add($smsdata);
	       		}
                $this->_drop_store_image($id); // ע������Ҫ��ɾ��ͼƬ����ɾ�����̣���Ϊɾ��ͼƬʱҪ�������Ϣ
                $this->_store_mod->drop($id);
                $this->show_message('channel_reject_ok',
                    'back_list', 'index.php?app=store&wait_verify=1&page=' . $ret_page
                );
            }
            else
            {
                $this->show_warning('Hacking Attempt');
                return;
            }
        }
    }

    function batch_edit()
    {
        if (!IS_POST)
        {
            $sgrade_mod =& m('sgrade');
            $this->assign('sgrades', $sgrade_mod->get_options());

            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));

            $this->headtag('<script type="text/javascript" src="{lib file=mlselection.js}"></script>');
            $this->display('store.batch.html');
        }
        else
        {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            if (!$id)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $ids = explode(',', $id);
            $data = array();
            if ($_POST['region_id'] > 0)
            {
                $data['region_id'] = $_POST['region_id'];
                $data['region_name'] = $_POST['region_name'];
            }
            if ($_POST['sgrade'] > 0)
            {
                $data['sgrade'] = $_POST['sgrade'];
            }
            if ($_POST['certification'])
            {
                $certs = array();
                if ($_POST['autonym'])
                {
                    $certs[] = 'autonym';
                }
                if ($_POST['material'])
                {
                    $certs[] = 'material';
                }
                $data['certification'] = join(',', $certs);
            }
            if ($_POST['recommended'] > -1)
            {
                $data['recommended'] = $_POST['recommended'];
            }
            if (trim($_POST['sort_order']))
            {
                $data['sort_order'] = intval(trim($_POST['sort_order']));
            }

            if (empty($data))
            {
                $this->show_warning('no_change_set');
                return;
            }

            $this->_store_mod->edit($ids, $data);
            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            $this->show_message('edit_ok',
                'back_list', 'index.php?app=store&page=' . $ret_page);
        }
    }

    function check_name()
    {
        $id         = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $store_name = empty($_GET['store_name']) ? '' : trim($_GET['store_name']);

        if (!$this->_store_mod->unique($store_name, $id))
        {
            echo ecm_json_encode(false);
            return;
        }
        echo ecm_json_encode(true);
    }

    /* ɾ���������ͼƬ */
    function _drop_store_image($store_id)
    {
        $files = array();

        /* �������ʱ�ϴ���ͼƬ */
        $store = $this->_store_mod->get_info($store_id);
        for ($i = 1; $i <= 3; $i++)
        {
            if ($store['image_' . $i])
            {
                $files[] = $store['image_' . $i];
            }
        }

        /* ���������е�ͼƬ */
        if ($store['store_banner'])
        {
            $files[] = $store['store_banner'];
        }
        if ($store['store_logo'])
        {
            $files[] = $store['store_logo'];
        }

        /* ɾ�� */
        foreach ($files as $file)
        {
            $filename = ROOT_PATH . '/' . $file;
            if (file_exists($filename))
            {
                @unlink($filename);
            }
        }
    }

    /* ȡ�õ��̷��� */
    function _get_scategory_options()
    {
        $mod =& m('scategory');
        $scategories = $mod->get_list();
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');

        return $tree->getOptions();
    }
    /**
     * ������Ʒ����
     * @author wscsky
     * 
     */
   function get_promotion(){
	   	$store_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
	    $page = $this->get_pages();
    	$pagelimit = $page['limit'];
        $conditions = ' And p.pr_status in(10,20)';
        $promotion_mod = &m("promotion");
        $fileds = array(
				'fields' => "store_name",
				'conditions' => ' store_id=' . $store_id
			);
        $storeinfo = &m("store")->get($fileds);
        $this->assign("store",$storeinfo);        
        
        if(isset($_GET['goods_name']))
 		{
    		$conditions.= " AND g.goods_name like "."'%".$_GET['goods_name']."%'";	
    	}
    	$sql = "SELECT p.*,g.goods_name,g.goods_number,gs.commodity_code,gs.spec_1,gs.spec_2,gs.color_rgb,gs.zprice,gs.gprice 
                from pa_promotion p
    			left JOIN pa_promotion_store_goods ps on ps.promotion_id=p.promotion_id
    			left JOIN pa_store_goods sg on ps.gs_id=sg.gs_id 
    			left join pa_goods g on sg.goods_id = g.goods_id
    			left join pa_goods_spec gs on gs.spec_id=sg.spec_id
    			where sg.store_id=".$store_id;
    	//�������ݲ���
  		 if($_GET['output'] == true){
  		 	$stock_all_info = $promotion_mod->getAll($sql.$conditions);
	  		 foreach ($stock_all_info as $k=>$v)
	    		{
	    			$stock_all_info[$k]['samount']=$v['pr_stock']*$v['gprice'];
	    			$stock_all_info[$k]['store_name']=$storeinfo['store_name'];
	    		}
    			$this->PromotiomExecl($stock_all_info);
    			exit();
    	  }
    	 
    	//�����ҳ����
    	$stock_info_count = $promotion_mod->getOne("select count(*) as c from pa_promotion p
    			left JOIN pa_promotion_store_goods ps on ps.promotion_id=p.promotion_id
    			left JOIN pa_store_goods sg on ps.gs_id=sg.gs_id
                left join pa_goods g on sg.goods_id = g.goods_id
    			where sg.store_id= ".$store_id.$conditions);
    	
    	//��ҳ����
    	$conditions.= ' limit '.$page['limit'];
    	$stock_info = $promotion_mod->getAll($sql.$conditions);
    	foreach ($stock_info as $k=>$v)
    	{
    		$stock_info[$k]['samount']=$v['pr_stock']*$v['gprice'];
    		$stock_info[$k]['store_name']=$storeinfo['store_name'];	
    	}
    	
    	$page['item_count'] = $stock_info_count; 
    	$this->_format_page($page); 	
    	$this->assign('store_id',$store_id);
    	$this->assign('stock_info',$stock_info);
    	$this->assign('page_info', $page);
    	$this->display('stock.promotion.html'); 
   }
    /**
     *��ͨ��Ʒ����б� 
     *
     */
 	function get_stock()
 	{
 		$store_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	$page = $this->get_pages();
    	$pagelimit = $page['limit'];
        $conditions = '';
 		if(isset($_GET['goods_name']))
 		{
    		$conditions.= " AND g.goods_name like "."'%".$_GET['goods_name']."%'";	
    	}
    	//�������ݲ���
 		if($_GET['output'] == true){
 			$stock_all_info = $this->_store_goods_mod->getAll("select s.store_name,sg.stock,sg.selllog,gs.spec_1,gs.spec_2,gs.color_rgb,gs.commodity_code,g.goods_name,gs.commodity_code,g.goods_id,gs.price,gs.gprice,gs.zprice,gs.credit 
                    from pa_store_goods sg 
                    left join pa_goods_spec gs on gs.spec_id = sg.spec_id
                    left join pa_goods g on g.goods_id =sg.goods_id 
                    left join pa_store s on s.store_id=sg.store_id where sg.store_id =".$store_id.$conditions);
    		$this->StockExcel($stock_all_info);
    		exit();
    	}
    	
    	//�����ҳ����
    	$stock_info_count = $this->_store_goods_mod->getOne("select count(*) as c from pa_store_goods sg left join pa_goods_spec gs on gs.spec_id = sg.spec_id left join pa_goods g on g.goods_id =gs.goods_id left join pa_store s on s.store_id=sg.store_id where sg.store_id=".$store_id.$conditions);
    	//��ҳ����
    	$conditions.= ' limit '.$page['limit'];
    	$stock_info = $this->_store_goods_mod->getAll("select s.store_name,sg.stock,sg.selllog,gs.spec_1,gs.spec_2,gs.color_rgb,gs.commodity_code,g.goods_name,gs.commodity_code,g.goods_id,gs.price,gs.gprice,gs.zprice,gs.credit 
            from pa_store_goods sg 
            left join pa_goods_spec gs on gs.spec_id = sg.spec_id 
            left join pa_goods g on g.goods_id =sg.goods_id 
			left join pa_store s on s.store_id=sg.store_id where sg.store_id =".$store_id.$conditions);  	
    	foreach ($stock_info as $k=>$v)
    	{
    		$stock_info[$k]['samount']=$v['stock']*$v['price'];
    	}
    	$page['item_count'] = $stock_info_count; 
    	$this->_format_page($page); 	
    	$this->assign('store_id',$store_id);
    	$this->assign('stock_info',$stock_info);
    	$this->assign('page_info', $page);
    	$this->display('stock.index.html'); 
 	}
 	function get_settle()
 	{
 		$store_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	date_default_timezone_set('PRC');
    	$starttime = empty($_GET['starttime']) ? 0 : strtotime($_GET['starttime']);
    	$endtime = empty($_GET['endtime']) ? 0 : strtotime($_GET['endtime'].' 23:23:59');
    	$stime = $_GET['starttime'];
    	$etime = $_GET['endtime'];
    	$page = $this->get_pages();
    	$pagelimit = $page['limit'];

    	$store_order_model = &m('storeorder');
    	//��ʼ������
    	$conditions = "";
    	if($starttime != 0){
    		$conditions=' AND so.pay_time>='.$starttime;
    	}
 		if($starttime != 0 || $endtime != 0){
    		$conditions=' AND so.pay_time<='.$endtime;
    	}
    	if($_GET['output'] == true){
    		
	    	$store_order_goods_info = $store_order_model->getAll("select so.order_sn,so.buyer_id,so.finished_time,sg.goods_name,sg.spec_id,sg.quantity,sg.price,sg.credit,sg.sprice,sg.gprice,sg.sprice,gc.discount,gc.spec_1,gc.spec_2,gc.sku,gc.color_rgb
															from pa_store_order so right join pa_store_order_goods sg on sg.order_id=so.order_id left join pa_goods_spec gc on sg.spec_id=gc.spec_id where status=40 and buyer_id=".$store_id.$conditions);	    	
	    	foreach ($store_order_goods_info as $k=>$v)
	    	{
	    		$store_order_goods_info[$k]['samount']=$v['quantity']*$v['zprice'];
	    		$store_order_goods_info[$k]['amcredit']=$v['quantity']*$v['credit'];		
	    	}
	 		
	    	$this->SettleExcel($store_order_goods_info);
    	}
    	
    	$store_order_count = $store_order_model->getOne("select count(*) as c from pa_store_order so right join pa_store_order_goods sg on sg.order_id=so.order_id left join pa_goods_spec gc on sg.spec_id=gc.spec_id where status=40 and buyer_id=".$store_id.$conditions);
			$conditions.= " limit ".$pagelimit;	
	    	$store_order_info = $store_order_model->getAll("select so.order_sn,so.buyer_id,so.finished_time,sg.goods_name,sg.spec_id,sg.quantity,sg.price,sg.credit,sg.sprice,sg.gprice,sg.zprice,gc.discount,gc.spec_1,gc.spec_2,gc.sku,gc.color_rgb
															from pa_store_order so right join pa_store_order_goods sg on sg.order_id=so.order_id left join pa_goods_spec gc on sg.spec_id=gc.spec_id where status=40 and buyer_id=".$store_id.$conditions);
    	$page['item_count'] = $store_order_count; 
    	$this->_format_page($page);
    	$this->assign('store_order_info',$store_order_info);
    	$this->assign('page_info', $page);
    	$this->assign('stime',$stime);
    	$this->assign('etime',$etime);
    	$this->assign('store_id',$store_id);
    	$this->display('settle.index.html');
 	}
 	/**
 	 * ������浼������
 	 * @author wscsky
 	 * 
 	 */
 	function PromotiomExecl($stock_all_info){
 		import(PHPExcel);
    	$objExcel = new PHPExcel();
    	$objWriter = new PHPExcel_Writer_Excel5($objExcel);
		//�����ĵ���������  
		$objProps = $objExcel->getProperties();  
		$objProps->setCreator("���̴��������Ϣ");  
		$objProps->setLastModifiedBy("���̴����������");  
		$objProps->setTitle("���̴����������");  
		$objProps->setSubject("Office XLS Test Document, Demo");  
		$objProps->setDescription("Test document, generated by PHPExcel.");  
		$objProps->setKeywords("office excel PHPExcel");  
		$objProps->setCategory("Test"); 
		
		$objExcel->setActiveSheetIndex(0);
		$objActSheet = $objExcel->getActiveSheet(); 
		$objActSheet->setTitle('new'); 
		$objActSheet->setCellValue('A1',iconv('gbk', 'utf-8', '��������'));  // �ַ�������  
		$objActSheet->setCellValue('B1',iconv('gbk', 'utf-8', '��Ʒ����'));
		$objActSheet->setCellValue('C1',iconv('gbk', 'utf-8', '��Ʒ���'));
		$objActSheet->setCellValue('D1',iconv('gbk', 'utf-8', '������'));
		$objActSheet->setCellValue('E1',iconv('gbk', 'utf-8', '�������'));
		$objActSheet->setCellValue('F1',iconv('gbk', 'utf-8', '������'));
		$objActSheet->setCellValue('G1',iconv('gbk', 'utf-8', '�۳�����'));
		$objActSheet->setCellValue('H1',iconv('gbk', 'utf-8', '������'));
		//$objActSheet->setCellValue('I1',iconv('gbk', 'utf-8', '�ɹ���'));
		$objActSheet->setCellValue('I1',iconv('gbk', 'utf-8', '����PL'));
		$objActSheet->setCellValue('J1',iconv('gbk', 'utf-8', '�ɱ����С��'));;
		foreach ($stock_all_info as $k => $v)
		{
			$key1=1;
    		$key2+=$key1;
    		$k = $key2 +1;
			$objActSheet->setCellValue('A'.$k,iconv('gbk', 'utf-8', $v['store_name']));  // �ַ�������  
			$objActSheet->setCellValue('B'.$k,iconv('gbk', 'utf-8', $v['goods_name']));
			$objActSheet->setCellValue('C'.$k,iconv('gbk', 'utf-8', $v['spec_1'].$v['spec_2']));
			$objActSheet->setCellValueExplicit('D'.$k,iconv('gbk', 'utf-8', $v['commodity_code']),PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->setCellValue('E'.$k,iconv('gbk', 'utf-8', $v['pr_stock']));
			$objActSheet->setCellValue('F'.$k,iconv('gbk', 'utf-8', $v['pr_price']));
			$objActSheet->setCellValue('G'.$k,iconv('gbk', 'utf-8', $v['pr_selllog']));
			$objActSheet->setCellValue('H'.$k,iconv('gbk', 'utf-8', $v['zprice']));
			//$objActSheet->setCellValue('I'.$k,iconv('gbk', 'utf-8', $v['gprice']));
			$objActSheet->setCellValue('I'.$k,iconv('gbk', 'utf-8', $v['pr_credit']));
			$objActSheet->setCellValue('J'.$k,iconv('gbk', 'utf-8', $v['samount']));
		}
		
		//���ÿ��  
		//$objActSheet->getColumnDimension('B')->setAutoSize(true);  
		$objActSheet->getColumnDimension('A')->setWidth(30);
		$objActSheet->getColumnDimension('B')->setWidth(35);
		$objActSheet->getColumnDimension('C')->setWidth(20);
		$objActSheet->getColumnDimension('D')->setWidth(20); 
		$objActSheet->getColumnDimension('E')->setWidth(12); 
		$objActSheet->getColumnDimension('F')->setWidth(12); 
		$objActSheet->getColumnDimension('G')->setWidth(12); 
		$objActSheet->getColumnDimension('H')->setWidth(15); 
		$objActSheet->getColumnDimension('I')->setWidth(15);
		$objActSheet->getColumnDimension('J')->setWidth(15); 
		//$objActSheet->getColumnDimension('K')->setWidth(15);     
		  
		$objStyleA1 = $objActSheet->getStyle('A1');  
		
		$objStyleA1  
		    ->getNumberFormat()  
		    ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);  
		  
		//��������  
		$objFontA1 = $objStyleA1->getFont();  
		$objFontA1->setName('Courier New');  
		$objFontA1->setSize(12);  
		$objFontA1->setBold(true);  
		$objFontA1->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);  
		$objFontA1->getColor()->setARGB('FF999999');  
		  
		//���ö��뷽ʽ  
		$objAlignA1 = $objStyleA1->getAlignment();  
		$objAlignA1->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
		$objAlignA1->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
		  
		//���ñ߿�  
		$objBorderA1 = $objStyleA1->getBorders();  
		$objBorderA1->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		$objBorderA1->getTop()->getColor()->setARGB('FFFF0000'); // color  
		$objBorderA1->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		$objBorderA1->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 

		$objBorderA1->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		  
		//���������ɫ  
		$objFillA1 = $objStyleA1->getFill();  
		$objFillA1->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
		$objFillA1->getStartColor()->setARGB('FFEEEEEE');  
		  
		//��ָ���ĵ�Ԫ������ʽ��Ϣ.  
		$objActSheet->duplicateStyle($objStyleA1, 'B1:K1');  	
		//*************************************  
		  
		$outputFileName = date('Y-m-d-Hms')."[�������]".".xls";  
		//���ļ�  
		header("Content-Type: application/force-download");  
		header("Content-Type: application/octet-stream");  
		header("Content-Type: application/download");  
		header('Content-Disposition:inline;filename="'.$outputFileName.'"');  
		//header("Content-Transfer-Encoding: binary");  
		//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
		header("Pragma: no-cache");  
		$objWriter->save('php://output');  
 	}
	function StockExcel($stock_all_info)
    {
    	import(PHPExcel);
    	$objExcel = new PHPExcel();
    	$objWriter = new PHPExcel_Writer_Excel5($objExcel);
		//�����ĵ���������  
		$objProps = $objExcel->getProperties();  
		$objProps->setCreator("���̿����Ϣ");  
		$objProps->setLastModifiedBy("���̿������");  
		$objProps->setTitle("���̿������");  
		$objProps->setSubject("Office XLS Test Document, Demo");  
		$objProps->setDescription("Test document, generated by PHPExcel.");  
		$objProps->setKeywords("office excel PHPExcel");  
		$objProps->setCategory("Test");  
		  
		//*************************************  
		//���õ�ǰ��sheet���������ں��������ݲ�����  
		//һ��ֻ����ʹ�ö��sheet��ʱ�����Ҫ��ʾ���á�  
		//ȱʡ����£�PHPExcel���Զ�������һ��sheet������SheetIndex=0  
		$objExcel->setActiveSheetIndex(0);  
		  
		  
		$objActSheet = $objExcel->getActiveSheet();  
		  
		//���õ�ǰ�sheet������  
		$objActSheet->setTitle('new');  
		  
		//*************************************  
		//���õ�Ԫ������  
		//  
		//��PHPExcel���ݴ��������Զ��жϵ�Ԫ����������  
		$objActSheet->setCellValue('A1',iconv('gbk', 'utf-8', '��������'));  // �ַ�������  
		$objActSheet->setCellValue('B1',iconv('gbk', 'utf-8', '��Ʒ����'));
		$objActSheet->setCellValue('C1',iconv('gbk', 'utf-8', '��Ʒ���'));
		$objActSheet->setCellValue('D1',iconv('gbk', 'utf-8', '������'));
		$objActSheet->setCellValue('E1',iconv('gbk', 'utf-8', '�������'));
		$objActSheet->setCellValue('F1',iconv('gbk', 'utf-8', '������'));
		$objActSheet->setCellValue('G1',iconv('gbk', 'utf-8', '�۳�����'));
		$objActSheet->setCellValue('H1',iconv('gbk', 'utf-8', '������'));
		//$objActSheet->setCellValue('I1',iconv('gbk', 'utf-8', '�ɹ���'));
		$objActSheet->setCellValue('I1',iconv('gbk', 'utf-8', '����PL'));
		$objActSheet->setCellValue('J1',iconv('gbk', 'utf-8', '�����С��'));;
		foreach ($stock_all_info as $k => $v)
		{
			$key1=1;
    		$key2+=$key1;
    		$k = $key2 +1;
    		$v['samount'] = $v['stock'] * $v['zprice']; //�����С�� = ������� *�������� 
			$objActSheet->setCellValue('A'.$k,iconv('gbk', 'utf-8', $v['store_name']));  // �ַ�������  
			$objActSheet->setCellValue('B'.$k,iconv('gbk', 'utf-8', $v['goods_name']));
			$objActSheet->setCellValue('C'.$k,iconv('gbk', 'utf-8', $v['spec_1'].$v['spec_2']));
			$objActSheet->setCellValueExplicit('D'.$k,iconv('gbk', 'utf-8', $v['commodity_code']),PHPExcel_Cell_DataType::TYPE_STRING);
			$objActSheet->setCellValue('E'.$k,iconv('gbk', 'utf-8', $v['stock']));
			$objActSheet->setCellValue('F'.$k,iconv('gbk', 'utf-8', $v['price']));
			$objActSheet->setCellValue('G'.$k,iconv('gbk', 'utf-8', $v['selllog']));
			$objActSheet->setCellValue('H'.$k,iconv('gbk', 'utf-8', $v['zprice']));
		//	$objActSheet->setCellValue('I'.$k,iconv('gbk', 'utf-8', $v['gprice']));
			$objActSheet->setCellValue('I'.$k,iconv('gbk', 'utf-8', $v['credit']));
			$objActSheet->setCellValue('J'.$k,'=E'.$k.'*H'.$k);
		}
		
		  
		//���ÿ��  
		//$objActSheet->getColumnDimension('B')->setAutoSize(true);  
		$objActSheet->getColumnDimension('A')->setWidth(25);
		$objActSheet->getColumnDimension('B')->setWidth(40);
		$objActSheet->getColumnDimension('C')->setWidth(20);
		$objActSheet->getColumnDimension('D')->setWidth(20); 
		$objActSheet->getColumnDimension('E')->setWidth(12); 
		$objActSheet->getColumnDimension('F')->setWidth(12); 
		$objActSheet->getColumnDimension('G')->setWidth(12); 
		$objActSheet->getColumnDimension('H')->setWidth(15); 
		$objActSheet->getColumnDimension('I')->setWidth(15);  
		$objActSheet->getColumnDimension('J')->setWidth(15);  
		//$objActSheet->getColumnDimension('K')->setWidth(15);
  	  
		$objStyleA1 = $objActSheet->getStyle('A1');  
		  
		//���õ�Ԫ�����ݵ����ָ�ʽ��  
		//  
		//���ʹ���� PHPExcel_Writer_Excel5 ���������ݵĻ���  
		//������Ҫע�⣬�� PHPExcel_Style_NumberFormat ��� const ���������  
		//�����Զ����ʽ����ʽ�У��������Ͷ���������ʹ�ã�����setFormatCode  
		//Ϊ FORMAT_NUMBER ��ʱ��ʵ�ʳ�����Ч����û�аѸ�ʽ����Ϊ"0"����Ҫ  
		//�޸� PHPExcel_Writer_Excel5_Format ��Դ�����е� getXf($style) ������  
		//�� if ($this->_BIFF_version == 0x0500) { ����363�и�����ǰ������һ  
		//�д���:   
		//if($ifmt === '0') $ifmt = 1;  
		//  
		//���ø�ʽΪPHPExcel_Style_NumberFormat::FORMAT_NUMBER������ĳЩ������  
		//��ʹ�ÿ�ѧ������ʽ��ʾ���������� setAutoSize ����������ÿһ�е�����  
		//����ԭʼ����ȫ����ʾ������  
		$objStyleA1  
		    ->getNumberFormat()  
		    ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);  
		  
		//��������  
		$objFontA1 = $objStyleA1->getFont();  
		$objFontA1->setName('Courier New');  
		$objFontA1->setSize(12);  
		$objFontA1->setBold(true);  
		$objFontA1->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);  
		$objFontA1->getColor()->setARGB('FF999999');  
		  
		//���ö��뷽ʽ  
		$objAlignA1 = $objStyleA1->getAlignment();  
		$objAlignA1->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
		$objAlignA1->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
		  
		//���ñ߿�  
		$objBorderA1 = $objStyleA1->getBorders();  
		$objBorderA1->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		$objBorderA1->getTop()->getColor()->setARGB('FFFF0000'); // color  
		$objBorderA1->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		$objBorderA1->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 

		$objBorderA1->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		  
		//���������ɫ  
		$objFillA1 = $objStyleA1->getFill();  
		$objFillA1->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
		$objFillA1->getStartColor()->setARGB('FFEEEEEE');  
		  
		//��ָ���ĵ�Ԫ������ʽ��Ϣ.  
		$objActSheet->duplicateStyle($objStyleA1, 'B1:J1');  	
		//*************************************  
		  
		  
		//���һ���µ�worksheet  
		//$objExcel->createSheet();  
		//$objExcel->getSheet(1)->setTitle('����2');  
 
		//������Ԫ��  
		//$objExcel->getSheet(1)->getProtection()->setSheet(true);  
		//$objExcel->getSheet(1)->protectCells('A1:C22', 'PHPExcel');  
		  
		  
		//*************************************  
		//�������  
		//
		$outputFileName = date('Y-m-d-Hms')."stock".".xls";  
		//���ļ�  
		//$objWriter->save($outputFileName);  
		//or  
		//�������  
		header("Content-Type: application/force-download");  
		header("Content-Type: application/octet-stream");  
		header("Content-Type: application/download");  
		header('Content-Disposition:inline;filename="'.$outputFileName.'"');  
		//header("Content-Transfer-Encoding: binary");  
		//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
		header("Pragma: no-cache");  
		$objWriter->save('php://output');  
    }
	function SettleExcel($store_order_goods_info)
    {
    	import(PHPExcel);
    	$objExcel = new PHPExcel();
    	$objWriter = new PHPExcel_Writer_Excel5($objExcel);
		//�����ĵ���������  
		$objProps = $objExcel->getProperties();  
		$objProps->setCreator("С ��");  
		$objProps->setLastModifiedBy("С ��");  
		$objProps->setTitle("�½��ĵ�");  
		$objProps->setSubject("Office XLS Test Document, Demo");  
		$objProps->setDescription("Test document, generated by PHPExcel.");  
		$objProps->setKeywords("office excel PHPExcel");  
		$objProps->setCategory("Test");  
		  
		//ȱʡ����£�PHPExcel���Զ�������һ��sheet������SheetIndex=0  
		$objExcel->setActiveSheetIndex(0);  
		  
		  
		$objActSheet = $objExcel->getActiveSheet();  
		  
		//���õ�ǰ�sheet������  
		$objActSheet->setTitle('new');  
		  
		//*************************************  
		//���õ�Ԫ������  
		//  
		//��PHPExcel���ݴ��������Զ��жϵ�Ԫ����������  
		$objActSheet->setCellValue('A1',iconv('gbk', 'utf-8', '�������'));  // �ַ�������  
		$objActSheet->setCellValue('B1',iconv('gbk', 'utf-8', '���ʱ��'));
		$objActSheet->setCellValue('C1',iconv('gbk', 'utf-8', '������Ʒ'));
		$objActSheet->setCellValue('D1',iconv('gbk', 'utf-8', '�������'));
		$objActSheet->setCellValue('E1',iconv('gbk', 'utf-8', '��������'));
		$objActSheet->setCellValue('F1',iconv('gbk', 'utf-8', '������'));
		$objActSheet->setCellValue('H1',iconv('gbk', 'utf-8', '������'));
		$objActSheet->setCellValue('I1',iconv('gbk', 'utf-8', '����PL'));
		$objActSheet->setCellValue('J1',iconv('gbk', 'utf-8', '�����۸�С��'));
		$objActSheet->setCellValue('K1',iconv('gbk', 'utf-8', '����PLС��'));;
		foreach ($store_order_goods_info as $k => $v)
		{
			$key1=1;
    		$key2+=$key1;
    		$k = $key2 +1; 
			$objActSheet->setCellValue('A'.$k,iconv('gbk', 'utf-8', $v['order_sn']),PHPExcel_Cell_DataType::TYPE_STRING);  // �ַ�������  
			$objActSheet->setCellValue('B'.$k, date('Y-m-d H:m:s',$v['finished_time']));
			$objActSheet->setCellValue('C'.$k,iconv('gbk', 'utf-8', $v['goods_name']));
			$objActSheet->setCellValue('D'.$k,iconv('gbk', 'utf-8', $v['spec_1'].$v['spec_2']));
			$objActSheet->setCellValue('E'.$k,iconv('gbk', 'utf-8', $v['quantity']));
			$objActSheet->setCellValue('F'.$k,iconv('gbk', 'utf-8', $v['price']));
			$objActSheet->setCellValue('G'.$k,iconv('gbk', 'utf-8', $v['zprice']));
			$objActSheet->setCellValue('H'.$k,iconv('gbk', 'utf-8', $v['credit']));
			$objActSheet->setCellValue('I'.$k,iconv('gbk', 'utf-8', $v['samount']));
			$objActSheet->setCellValue('J'.$k,iconv('gbk', 'utf-8', $v['amcredit']));
		} 
		//���ÿ��  
		//$objActSheet->getColumnDimension('B')->setAutoSize(true);  
		$objActSheet->getColumnDimension('A')->setWidth(15);
		$objActSheet->getColumnDimension('B')->setWidth(23);
		$objActSheet->getColumnDimension('C')->setWidth(60); 
		$objActSheet->getColumnDimension('D')->setWidth(12); 
		$objActSheet->getColumnDimension('E')->setWidth(12); 
		$objActSheet->getColumnDimension('F')->setWidth(12); 
		$objActSheet->getColumnDimension('G')->setWidth(15); 
		$objActSheet->getColumnDimension('H')->setWidth(15);
		$objActSheet->getColumnDimension('I')->setWidth(15); 
		$objActSheet->getColumnDimension('J')->setWidth(15); 
		  
		  
		$objStyleA1 = $objActSheet->getStyle('A1');  
		  
		//����ԭʼ����ȫ����ʾ������  
		$objStyleA1  
		    ->getNumberFormat()  
		    ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);  
		  
		//��������  
		$objFontA1 = $objStyleA1->getFont();  
		$objFontA1->setName('Courier New');  
		$objFontA1->setSize(12);  
		$objFontA1->setBold(true);  
		$objFontA1->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);  
		$objFontA1->getColor()->setARGB('FF999999');  
		  
		//���ö��뷽ʽ  
		$objAlignA1 = $objStyleA1->getAlignment();  
		$objAlignA1->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
		$objAlignA1->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
		  
		//���ñ߿�  
		$objBorderA1 = $objStyleA1->getBorders();  
		$objBorderA1->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		$objBorderA1->getTop()->getColor()->setARGB('FFFF0000'); // color  
		$objBorderA1->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		$objBorderA1->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 

		$objBorderA1->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		  
		//���������ɫ  
		$objFillA1 = $objStyleA1->getFill();  
		$objFillA1->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
		$objFillA1->getStartColor()->setARGB('FFEEEEEE');  
		  
		//��ָ���ĵ�Ԫ������ʽ��Ϣ.  
		$objActSheet->duplicateStyle($objStyleA1, 'B1:K1');  	
		//�������  
		$outputFileName = date('Y-m-d H:m:s',time())."ר�����ͳ��".".xls";   
		//�������  
		header("Content-Type: application/force-download");  
		header("Content-Type: application/octet-stream");  
		header("Content-Type: application/download");  
		header('Content-Disposition:inline;filename="'.$outputFileName.'"');   
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
		header("Pragma: no-cache");  
		$objWriter->save('php://output');  
    }
    
    /**������������
     * 
     */
    function get_shipment()
    {
    	$store_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	date_default_timezone_set('PRC');
    	$starttime = empty($_GET['starttime']) ? "" : $_GET['starttime'];
    	$endtime = empty($_GET['endtime']) ? "" : $_GET['endtime'];
    	$order_sn = empty($_GET['order_sn'])? '' : trim($_GET['order_sn']);
    	$status = empty($_GET['status']) ? '0' :intval($_GET['status']);
    	$stime = $_GET['starttime'];
    	$etime = $_GET['endtime'];
    	$output = empty($_GET['output'])? "":trim(strtolower($_GET['output']));
    	$page = $this->get_pages();
    	$pagelimit = $page['limit'];
    	
    	$order_model = &m('order');
    	    	
    	$order_status = array(
    		"20" => "����ɹ�,������",
    		"30" => "�ѷ���",
    		"40" => "�����"
    	);
    	$status2 = $status;
    	if($status==0){
    		$status2 = "20,30,40";    		
    	 }
    	//��ʼ������
    	$conditions = " where seller_id={$store_id} AND Status in({$status2})";
    	$conditions .= $this->_get_query_conditions(array(
    		array(
                'field' => 'order_sn',
                'equal' => 'like',
                'type'  => 'string',
            ),array(
                'field' => 'pay_time',
                'name'  => 'starttime',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'pay_time',
                'name'  => 'endtime',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            )
      	  ));
    	//ͳ�����ж�����
    	$store_order_count = $order_model->getOne("select count(*) as c from pa_order".$conditions);
        
    	
    	//�������ж�������
    	if($output == 'all'){
    		$sql = "select * from pa_order ".$conditions;
    		$order_info_all = $order_model->getAll($sql);
    		$this->ShipmentExcel($order_info_all);
    		exit();
    	}
    	//�������ж�������
    	if($output=='alldetail'){
    		$sql = "select o.order_sn,g.* from(
    			select order_id,order_sn from pa_order {$conditions}) o,pa_order_goods g
    			where o.order_id=g.order_id
    			order by o.order_id";
    		$order_detail_all = $order_model->getAll($sql);
    		$this->ShipmentExcelDetail($order_detail_all);
    		exit();
    	}

        //����ͳ�Ƽ���
        $sql2 = "select sum(order_amount) as order_amount,
                        sum(goods_amount) as goods_amount,
                        sum(cash) as cash,
                        sum(use_money) as use_money,
                        sum(use_credit) as use_credit,
                        sum(get_credit) as get_credit
                        from pa_order {$conditions}";
        $count2 = $order_model->getRow($sql2);
                
        //��ҳ����
    	$conditions.= " limit ".$pagelimit;
    	
		//������ҳ��������
    	if($output == 'page'){
    		$sql = "select * from pa_order {$conditions}";
    		$order_info_page = $order_model->getAll($sql);
    		$this->ShipmentExcel($order_info_page);
    		exit();
    	}
    	//������ҳ������ϸ
    	if($output=='pagedetail'){
    		$sql = "select o.order_sn,g.* from(
    			select order_id,order_sn from pa_order {$conditions}) o,pa_order_goods g
    			where o.order_id=g.order_id
    			order by o.order_id";
    		$order_detail_page = $order_model->getAll($sql);
    		$this->ShipmentExcelDetail($order_detail_page);
    		exit();
    	}
    	    	
    	$store_order_info = $order_model->getAll("select *,(cash+use_money+use_credit) as showmany from pa_order {$conditions}");

    	if($_GET['output2'] == true){
    		$order_goods_info = $order_model->getAll("select o.order_sn,o.seller_id,o.seller_name,o.finished_time,o.goods_amount,o.use_credit,o.get_credit,
														os.goods_id,os.goods_name,os.quantity,gc.spec_1,gc.spec_2,os.credit,os.price,os.gprice,os.zprice
														from pa_order_goods os left join pa_order o on o.order_id=os.order_id left join pa_goods_spec gc on os.spec_id=gc.spec_id {$conditions}");
            foreach ($order_goods_info as $k=>$v)
	    	{
	    		$order_goods_info[$k]['samount']=$v['quantity']*$v['price'];
	    		$order_goods_info[$k]['frimCost']=$v['quantity']*$v['gprice'];
	    		$order_goods_info[$k]['storeCost']=$v['quantity']*$v['zprice'];
	    		$order_goods_info[$k]['amountCredit']=$v['quantity']*$v['credit'];			
	    	}
    		$this->ShipmentExcel($order_goods_info);
    	}
    	
 		$count = array(
 			"order_amount" =>0,
	 		"goods_amount" =>0,
	 		"cash" =>0,
	 		"use_money" =>0,
	 		"use_credit" =>0,
	 		"get_credit" =>0
 		);		   
 		//��ǰҳ������ͳ��
    	foreach ($store_order_info as $key=>$val)
 		{
 			$count['order_amount']+=$val['order_amount'];
 			$count['goods_amount']+=$val['goods_amount'];
 			$count['cash']+= $val['cash'];
 			$count['use_money']+= $val['use_money'];
 			$count['use_credit']+= $val['use_credit'];
 			$count['get_credit']+= $val['get_credit'];
 		}
 		
    	$page['item_count'] = $store_order_count; 
    	$this->_format_page($page);
    	$this->assign('store_order_info',$store_order_info);
    	$this->assign("status",$order_status);
    	$this->assign('count',$count);
        $this->assign('count2',$count2);       
    	$this->assign('page_info', $page);
    	$acturl="index.php?app=store&act=get_shipment&id={$store_id}&order_sn={$order_sn}&status={$status}&starttime={$starttime}&endtime={$endtime}&page={$page['curr_page']}&output=";
    	$this->assign('acturl',$acturl);
    	$this->assign('stime',$stime);
    	$this->assign('etime',$etime);
    	$this->assign('nowstatus',$status);
    	$this->assign('store_id',$store_id);
    	$this->display('shipment.index.html');
    }
    /**
     *���̳����������� 
     */
	function ShipmentExcel($store_order_info)
    {
    	import(PHPExcel);
    	$objExcel = new PHPExcel();
    	$objWriter = new PHPExcel_Writer_Excel5($objExcel);
		//�����ĵ���������  
		$objProps = $objExcel->getProperties();  
		$objProps->setCreator("������");  
		$objProps->setLastModifiedBy("������");  
		$objProps->setTitle("������");  
		$objProps->setSubject("Office XLS Test Document, Demo");  
		$objProps->setDescription("Test document, generated by PHPExcel.");  
		$objProps->setKeywords("office excel PHPExcel");  
		$objProps->setCategory("Test");  
		  
		//ȱʡ����£�PHPExcel���Զ�������һ��sheet������SheetIndex=0  
		$objExcel->setActiveSheetIndex(0);  
		  
		$objActSheet = $objExcel->getActiveSheet();  
		  
		//���õ�ǰ�sheet������  
		$objActSheet->setTitle('new');  
		
		//״̬����
		$pay_type = array(
			"1" => "�ֽ�",
			"2" => "������",
			"3" => "�ֽ�+������",
			"4" => "��Ա���+������",
			"5" => "�ֽ�+��Ա���",
			"6"	=> "��Ա���",
			"7" => "�ֽ�+��Ա���+������"
			);
		$order_status = array(
    		"20" => "����ɹ�,������",
    		"30" => "�ѷ���",
    		"40" => "�����"
    	);		  
		//*************************************  
		//���õ�Ԫ������  
		//  
		//��PHPExcel���ݴ��������Զ��жϵ�Ԫ����������  
		$objActSheet->setCellValue('A1',iconv('gbk', 'utf-8', '�������'));  // �ַ�������  
		$objActSheet->setCellValue('B1',iconv('gbk', 'utf-8', '���ʱ��'));
		$objActSheet->setCellValue('C1',iconv('gbk', 'utf-8', '����״̬'));
		$objActSheet->setCellValue('D1',iconv('gbk', 'utf-8', '֧����ʽ'));
		$objActSheet->setCellValue('E1',iconv('gbk', 'utf-8', '�����ܼ�(���˷�)'));
		$objActSheet->setCellValue('F1',iconv('gbk', 'utf-8', '��Ʒ�ܼ�'));
		$objActSheet->setCellValue('G1',iconv('gbk', 'utf-8', 'ʹ���ֽ�'));
		$objActSheet->setCellValue('H1',iconv('gbk', 'utf-8', 'ʹ�����'));
		$objActSheet->setCellValue('I1',iconv('gbk', 'utf-8', 'ʹ��PL��'));
		$objActSheet->setCellValue('J1',iconv('gbk', 'utf-8', '����PL��'));
		$objActSheet->setCellValue('K1',iconv('gbk', 'utf-8', '�û��ֻ�'));
		$key=1;
		foreach ($store_order_info as $k => $v)
		{
			$key+=1;
			$objActSheet->setCellValue('A'.$key,iconv('gbk', 'utf-8', $v['order_sn']),PHPExcel_Cell_DataType::TYPE_STRING);  // �ַ�������  
			$objActSheet->setCellValue('B'.$key,date('Y-m-d H:m:s',$v['finished_time']));
			$objActSheet->setCellValue('C'.$key,iconv('gbk', 'utf-8', $order_status[$v['status']]));
			$objActSheet->setCellValue('D'.$key,iconv('gbk', 'utf-8', $pay_type[$v['pay_type']]));
			$objActSheet->setCellValue('E'.$key,iconv('gbk', 'utf-8', $v['order_amount']));
			$objActSheet->setCellValue('F'.$key,iconv('gbk', 'utf-8', $v['goods_amount']));
			$objActSheet->setCellValue('G'.$key,iconv('gbk', 'utf-8', $v['cash']));
			$objActSheet->setCellValue('H'.$key,iconv('gbk', 'utf-8', $v['use_money']));
			$objActSheet->setCellValue('I'.$key,iconv('gbk', 'utf-8', $v['use_credit']));
			$objActSheet->setCellValue('J'.$key,iconv('gbk', 'utf-8', $v['get_credit']));
			$objActSheet->setCellValue('K'.$key,iconv('gbk', 'utf-8', $v['buyer_mobile']));			
		}
		$key +=2;
		$objActSheet->setCellValue('D'.$key,iconv('gbk', 'utf-8', "�ܼ�:"));  
		$objActSheet->setCellValue('E'.$key,iconv('gbk', 'utf-8', '=sum(E2:E'.($key-2).')'));
		$objActSheet->setCellValue('F'.$key,iconv('gbk', 'utf-8', '=sum(F2:F'.($key-2).')'));
		$objActSheet->setCellValue('G'.$key,iconv('gbk', 'utf-8', '=sum(G2:G'.($key-2).')'));
		$objActSheet->setCellValue('H'.$key,iconv('gbk', 'utf-8', '=sum(H2:H'.($key-2).')'));
		$objActSheet->setCellValue('I'.$key,iconv('gbk', 'utf-8', '=sum(I2:I'.($key-2).')'));
		$objActSheet->setCellValue('J'.$key,iconv('gbk', 'utf-8', '=sum(J2:J'.($key-2).')'));
		//���ÿ��  
		//$objActSheet->getColumnDimension('B')->setAutoSize(true);  
		$objActSheet->getColumnDimension('A')->setWidth(15);
		$objActSheet->getColumnDimension('B')->setWidth(23);
		$objActSheet->getColumnDimension('C')->setWidth(20); 
		$objActSheet->getColumnDimension('D')->setWidth(25); 
		$objActSheet->getColumnDimension('E')->setWidth(25); 
		$objActSheet->getColumnDimension('F')->setWidth(12); 
		$objActSheet->getColumnDimension('G')->setWidth(15); 
		$objActSheet->getColumnDimension('H')->setWidth(15);
		$objActSheet->getColumnDimension('I')->setWidth(15); 
		$objActSheet->getColumnDimension('J')->setWidth(20);   
		  
		$objStyleA1 = $objActSheet->getStyle('A1'); 
		  
		//����ԭʼ����ȫ����ʾ������  
		$objStyleA1  
		    ->getNumberFormat()  
		    ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
	  
		//��������  
		$objFontA1 = $objStyleA1->getFont();  
		$objFontA1->setName('Courier New');  
		$objFontA1->setSize(12);  
		$objFontA1->setBold(true);  
		$objFontA1->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);  
		$objFontA1->getColor()->setARGB('FFFF0000'); 
		  
		//���ö��뷽ʽ  
		$objAlignA1 = $objStyleA1->getAlignment();  
		$objAlignA1->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
		$objAlignA1->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
		  
		//���ñ߿�  
		$objBorderA1 = $objStyleA1->getBorders();  
		$objBorderA1->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		$objBorderA1->getTop()->getColor()->setARGB('#666666'); // color  
		$objBorderA1->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		$objBorderA1->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 

		$objBorderA1->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		  
		//���������ɫ  
		$objFillA1 = $objStyleA1->getFill();  
		$objFillA1->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
		$objFillA1->getStartColor()->setARGB('FFEEEEEE');  
		  
		//��ָ���ĵ�Ԫ������ʽ��Ϣ.  
		$objActSheet->duplicateStyle($objStyleA1, 'B1:J1');
		
		//ͳ����ʽ����
		$objStyleTotal = $objActSheet->getStyle('D'.$key);	
		//����
		$objFillC = $objStyleTotal->getFont();  
		$objFillC->getColor()->setARGB('FFFF0000');
		$objFillC->setName('Arial');  
		$objFillC->setSize(12);  
		$objFillC->setBold(true);
		//����
		$objFillC = $objStyleTotal->getFill();  
		$objFillC->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
		$objFillC->getStartColor()->setARGB('FFEEEEEE');  
		//����
		$objFillC = $objStyleTotal->getAlignment();  
		$objFillC->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);  
		$objFillC->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
		
		//�߿�
		$objFillC = $objStyleTotal->getBorders();  
		$objFillC->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		$objFillC->getTop()->getColor()->setARGB('FF666666'); // color  
		$objFillC->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		$objFillC->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 				
		$objActSheet->duplicateStyle($objStyleTotal, 'E'.$key.':'.'J'.$key);
		$objFillC->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		
		
	
		//�������  
		$outputFileName = date('Y-m-d-Hms',time())."[���̳���ͳ��]".".xls";   
		//�������  
		header("Content-Type: application/force-download");  
		header("Content-Type: application/octet-stream");  
		header("Content-Type: application/download");  
		header('Content-Disposition:inline;filename="'.$outputFileName.'"');   
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
		header("Pragma: no-cache");  
		$objWriter->save('php://output');
    }
    /**
     * �������鵼�� 
     * @author wscsky
     */
	private function ShipmentExcelDetail($store_order_info)
    {
    	set_time_limit(0);
    	import(PHPExcel);
    	$objExcel = new PHPExcel();
    	$objWriter = new PHPExcel_Writer_Excel5($objExcel);
		//�����ĵ���������  
		$objProps = $objExcel->getProperties();  
		$objProps->setCreator("������");  
		$objProps->setLastModifiedBy("������");  
		$objProps->setTitle("����������");  
		$objProps->setSubject("Office XLS Test Document, Demo");  
		$objProps->setDescription("Test document, generated by PHPExcel.");  
		$objProps->setKeywords("office excel PHPExcel");  
		$objProps->setCategory("Test");  
		  
		//ȱʡ����£�PHPExcel���Զ�������һ��sheet������SheetIndex=0  
		$objExcel->setActiveSheetIndex(0);  
		  
		$objActSheet = $objExcel->getActiveSheet();  
		  
		//���õ�ǰ�sheet������  
		$objActSheet->setTitle('detail');  
			  
		//*************************************  
		//���õ�Ԫ������  
		//  
		//��PHPExcel���ݴ��������Զ��жϵ�Ԫ����������  
		$objActSheet->setCellValue('A1',iconv('gbk', 'utf-8', '�������'));  // �ַ�������  
		$objActSheet->setCellValue('B1',iconv('gbk', 'utf-8', '��Ʒ��'));  // �ַ�������  
		$objActSheet->setCellValue('C1',iconv('gbk', 'utf-8', '���'));
		$objActSheet->setCellValue('D1',iconv('gbk', 'utf-8', '����'));
		$objActSheet->setCellValue('E1',iconv('gbk', 'utf-8', '������'));
		$objActSheet->setCellValue('F1',iconv('gbk', 'utf-8', '������С��'));
		$objActSheet->setCellValue('G1',iconv('gbk', 'utf-8', '������'));
		$objActSheet->setCellValue('H1',iconv('gbk', 'utf-8', '������С��'));
		$objActSheet->setCellValue('I1',iconv('gbk', 'utf-8', '����PL'));
		$objActSheet->setCellValue('J1',iconv('gbk', 'utf-8', '����PLС��'));
		
		$key=2;$sn=0;$snum1=$key+1;$goodsNum=0;
		foreach ($store_order_info as $k => $v)
		{
			$key+=1;
			if($sn != $v['order_sn']){
				if($goodsNum>=MaxGoodsNum){$key-=1;break;}
				if($sn!=0){ //������һ��������ͳ��
					//$key+=1;
					//ͳ����ʽ����
					$objActSheet->setCellValue('C'.$key,iconv('gbk', 'utf-8', '����С��:'));
					$objActSheet->setCellValue('D'.$key,'=SUM(D'.$snum1.':D'.($key-1).')');
					$objActSheet->setCellValue('E'.$key,'=SUM(G'.$snum1.':G'.($key-1).')');
					$objActSheet->setCellValue('F'.$key,'=SUM(H'.$snum1.':H'.($key-1).')');
					$objActSheet->setCellValue('G'.$key,'=SUM(I'.$snum1.':I'.($key-1).')');
					$objActSheet->setCellValue('H'.$key,'=SUM(J'.$snum1.':J'.($key-1).')');
					$objActSheet->setCellValue('I'.$key,'=SUM(K'.$snum1.':K'.($key-1).')');
					$objActSheet->setCellValue('J'.$key,'=SUM(L'.$snum1.':L'.($key-1).')');
					
					$objStyleTotal = $objActSheet->getStyle('A'.$key);	
					//����
					$objFillC = $objStyleTotal->getFont();  
					$objFillC->getColor()->setARGB('FFFF0000');
					$objFillC->setName('Arial');  
					$objFillC->setSize(12);  
					$objFillC->setBold(true);
					//����
					$objFillC = $objStyleTotal->getFill();  
					$objFillC->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
					$objFillC->getStartColor()->setARGB('FFEEEEEE');  
					//����
					$objFillC = $objStyleTotal->getAlignment();  
					$objFillC->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);  
					$objFillC->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
					
					//�߿�
					$objFillC = $objStyleTotal->getBorders();  
					$objFillC->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
					$objFillC->getTop()->getColor()->setARGB('FF666666'); // color  
					$objFillC->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
					$objFillC->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 				
					$objActSheet->duplicateStyle($objStyleTotal, 'B'.$key.':'.'L'.$key);
					$objFillC->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	
					$key+=2;
					$snum1=$key;
				}
				$sn = $v['order_sn'];
				//����������ʽ
				
				$objActSheet->setCellValue('A'.$key, iconv('gbk', 'utf-8', $v['order_sn']),PHPExcel_Cell_DataType::TYPE_STRING);  // �������
				//�߿�
				$goodsLine = $objActSheet->getStyle('A'.$key);
				$goodsC = $goodsLine->getBorders();  
				$goodsC->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
				$goodsC->getTop()->getColor()->setARGB('FF666666'); // color  
				$goodsC->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
				$goodsC->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 				
				$goodsC->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			}			
			$objActSheet->setCellValue('B'.$key,iconv('gbk', 'utf-8', $v['goods_name']));
			$objActSheet->setCellValue('C'.$key,iconv('gbk', 'utf-8', $v['specification']));
			$objActSheet->setCellValue('D'.$key,iconv('gbk', 'utf-8', $v['quantity']));
			$objActSheet->setCellValue('E'.$key,iconv('gbk', 'utf-8', $v['gprice']));
			$objActSheet->setCellValue('F'.$key,iconv('gbk', 'utf-8', "=D$key*E$key"));
			$objActSheet->setCellValue('G'.$key,iconv('gbk', 'utf-8', $v['price']));
			$objActSheet->setCellValue('H'.$key,iconv('gbk', 'utf-8', "=D$key*G$key"));
			$objActSheet->setCellValue('I'.$key,iconv('gbk', 'utf-8', $v['price']));
			$objActSheet->setCellValue('J'.$key,iconv('gbk', 'utf-8', "=D$key*I$key"));
			$objActSheet->setCellValue('K'.$key,iconv('gbk', 'utf-8', $v['credit']));
			$objActSheet->setCellValue('L'.$key,iconv('gbk', 'utf-8', "=D$key*K$key"));
			
			$goodsLine = $objActSheet->getStyle('A'.$snum1);
			$goodsC = $goodsLine->getBorders();
			$objActSheet->duplicateStyle($goodsLine, 'B'.$key.':'.'L'.$key);
			
			$goodsNum+=1;	
		}
		
		if($sn!=0){ //�������һ��������ͳ��
				$key+=1;
				$objActSheet->setCellValue('C'.$key,iconv('gbk', 'utf-8', '����С��:'));
				$objActSheet->setCellValue('D'.$key,'=SUM(D'.$snum1.':D'.($key-1).')');
				$objActSheet->setCellValue('E'.$key,'=SUM(E'.$snum1.':E'.($key-1).')');
				$objActSheet->setCellValue('F'.$key,'=SUM(F'.$snum1.':F'.($key-1).')');
				$objActSheet->setCellValue('G'.$key,'=SUM(G'.$snum1.':G'.($key-1).')');
				$objActSheet->setCellValue('H'.$key,'=SUM(H'.$snum1.':H'.($key-1).')');
				$objActSheet->setCellValue('I'.$key,'=SUM(I'.$snum1.':I'.($key-1).')');
				$objActSheet->setCellValue('J'.$key,'=SUM(J'.$snum1.':J'.($key-1).')');
				$objActSheet->setCellValue('K'.$key,'=SUM(K'.$snum1.':K'.($key-1).')');
				$objActSheet->setCellValue('L'.$key,'=SUM(L'.$snum1.':L'.($key-1).')');
					
				$objStyleTotal = $objActSheet->getStyle('A'.$key);	
				//����
				$objFillC = $objStyleTotal->getFont();  
				$objFillC->getColor()->setARGB('FFFF0000');
				$objFillC->setName('Arial');  
				$objFillC->setSize(12);  
				$objFillC->setBold(true);
				//����
				$objFillC = $objStyleTotal->getFill();  
				$objFillC->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
				$objFillC->getStartColor()->setARGB('FFEEEEEE');  
				//����
				$objFillC = $objStyleTotal->getAlignment();  
				$objFillC->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);  
				$objFillC->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
				
				//�߿�
				$objFillC = $objStyleTotal->getBorders();  
				$objFillC->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
				$objFillC->getTop()->getColor()->setARGB('FF666666'); // color  
				$objFillC->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
				$objFillC->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 				
				$objActSheet->duplicateStyle($objStyleTotal, 'B'.$key.':'.'L'.$key);
				$objFillC->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
								
		}
		//���ÿ��  
		//$objActSheet->getColumnDimension('B')->setAutoSize(true);  
		$objActSheet->getColumnDimension('A')->setWidth(12);
		$objActSheet->getColumnDimension('B')->setWidth(40);
		$objActSheet->getColumnDimension('C')->setWidth(20); 
		$objActSheet->getColumnDimension('D')->setWidth(15); 
		$objActSheet->getColumnDimension('E')->setWidth(15); 
		$objActSheet->getColumnDimension('F')->setWidth(15); 
		$objActSheet->getColumnDimension('G')->setWidth(15); 
		$objActSheet->getColumnDimension('H')->setWidth(15);
		$objActSheet->getColumnDimension('I')->setWidth(15); 
		$objActSheet->getColumnDimension('J')->setWidth(15); 
		$objActSheet->getColumnDimension('K')->setWidth(15);
		$objActSheet->getColumnDimension('L')->setWidth(15);   
		  
		$objStyleA1 = $objActSheet->getStyle('A1'); 
		  
		//����ԭʼ����ȫ����ʾ������  
		$objStyleA1  
		    ->getNumberFormat()  
		    ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
	  
		//��������  
		$objFontA1 = $objStyleA1->getFont();  
		$objFontA1->setName('Courier New');  
		$objFontA1->setSize(12);  
		$objFontA1->setBold(true);  
		$objFontA1->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);  
		$objFontA1->getColor()->setARGB('FFFF0000'); 
		  
		//���ö��뷽ʽ  
		$objAlignA1 = $objStyleA1->getAlignment();  
		$objAlignA1->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
		$objAlignA1->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
		  
		//���ñ߿�  
		$objBorderA1 = $objStyleA1->getBorders();  
		$objBorderA1->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		$objBorderA1->getTop()->getColor()->setARGB('#666666'); // color  
		$objBorderA1->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		$objBorderA1->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 

		$objBorderA1->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
		  
		//���������ɫ  
		$objFillA1 = $objStyleA1->getFill();  
		$objFillA1->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
		$objFillA1->getStartColor()->setARGB('FFEEEEEE');  
		  
		//��ָ���ĵ�Ԫ������ʽ��Ϣ.  
		$objActSheet->duplicateStyle($objStyleA1, 'B1:L1');
		
		
		
		//�������  
		$outputFileName = date('Y-m-d-Hms',time())."[���̳�����������]".".xls";   
		//�������  
		header("Content-Type: application/force-download");  
		header("Content-Type: application/octet-stream");  
		header("Content-Type: application/download");  
		header('Content-Disposition:inline;filename="'.$outputFileName.'"');   
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
		header("Pragma: no-cache");  
		$objWriter->save('php://output');
    }
    
    function payment()
    {
    	$this->show_warning('�ݲ�֧�ִ˹��ܣ�');
        return;
    	$store_id = empty($_GET['store_id']) ? 0 : intval($_GET['store_id']);
    	if (!$store_id)
    	{
    		$this->show_warning('δ֪�ĵ���');
    		return;
    	}
    	//������Ϣ
    	$store_info = $this->_store_mod->get($store_id);
    	if (!IS_POST)
    	{
    		$this->assign('store_info', $store_info);
    		$this->display("store_payment.form.html");
    	}
    	else 
    	{
    		$amount = empty($_POST['amount']) ? 0 : floatval($_POST['amount']);
    		$log_msg = empty($_POST['log_msg']) ? '' : trim($_POST['log_msg']);
    		if ($amount == 0)
    		{
    			$this->show_warning('������Ľ��');
    			return;
    		}
    		
    		$param = array(
    			'user_id' => $store_info['store_id'],
    			'user_money' => $amount,
    			'change_time' => gmtime(),
    			'change_desc' => "����(ID:{$store_info['store_id']},������:{$store_info['store_name']})�ɷѣ�{$amount}, ��������������Ϣ:\{{$log_msg}\}.",
    			'change_type' => 7,
    		);
    		add_account_log($param);
    		
    		//����(������ecapp��)
    		if ($store_info['manager_id'] != 0)
    		{
    			$this->manager_rebate($store_info['manager_id'], $amount, $store_id);
    		}
    		
    		$this->show_message('�ɷѼ�¼���', '���ص��̹���', 'index.php?app=store');
    	}
    }
  	function paymentdetail()
    {
    	if (!IS_POST)
    	{
    		$this->display("store_payment.detail.html");
    	}
    }
}

?>
