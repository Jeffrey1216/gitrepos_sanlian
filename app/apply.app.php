<?php

/* ���뿪�� */
class ApplyApp extends MemberbaseApp
{

    function index()
    {
        $step = isset($_GET['step']) ? intval($_GET['step']) : 1;
        /* �ж��Ƿ����˵������� */
        if (!Conf::get('store_allow'))
        {
            $this->show_warning('apply_disabled');
            return;
        }

        /* ֻ�е�¼���û��ſ����� */
        if (!$this->visitor->has_login)
        {
            $this->login();
            return;
        }

    	$user_id = $this->visitor->get('user_id');
    	$cust_manager_mod =& m('customermanager');
    	$manager=$cust_manager_mod->get($user_id);
    	if(!$manager)
    	{
    		$this->show_message("��Ŀǰ�������Ź�Ա���������뿪�꣡");
    		return ;
    	}
    	
        /* ������������е��̲��������� */
        $store_mod =& m('store');
        $store = $store_mod->get($this->visitor->get('user_id'));
        if ($store)
        {
            if ($store['state'])
            {
                $this->show_warning('user_has_store');
                return;
            }
            else
            {
                if ($step != 2)
                {
                    $this->show_warning('user_has_application');
                    return;
                }                
            }
        }
        $sgrade_mod =& m('sgrade');
        
        switch ($step)
        {
            case 1:
            	/* ��ǰ�û����Ĳ˵� */
	            $this->_curitem('state');
	            /* ��ǰ�����Ӳ˵� */
	            $this->_curmenu('basic_information');
				/* ��ǰ�û�������Ϣ*/
	            $this->_get_user_info();
                $this->display('apply.newstep1.html');
                break;
            case 2:
                if (!IS_POST)
                {                	
                	/* ��ǰ�û����Ĳ˵� */
		            $this->_curitem('state');		
		            /* ��ǰ�����Ӳ˵� */
		            $this->_curmenu('basic_information');
					/* ��ǰ�û�������Ϣ*/
		            $this->_get_user_info();
		            $gcategory_mod = & bm('gcategory');
		            if($_GET['cate_id'])
		            {
		            	if(!is_array($_GET['cate_id']))
		            	{
		            		$this->show_warning("���Ĳ���ʧ��");
		            		return ;
		            	}
						$cate_ids = implode(',', $_GET['cate_id']);
						$gcate_info = $gcategory_mod->getAll("SELECT cate_id, cate_name FROM pa_gcategory where cate_id in (".$cate_ids.")");
		            }else {
		            	$gcate_info = $gcategory_mod->get_children(0);	
		            }
		            $this->assign('gcate_info',$gcate_info);
                    $region_mod =& m('region');
                    $this->assign('regions', $region_mod->get_options(0));
                    /* ����jQuery�ı���֤��� */
                    $this->import_resource(array('script' => 'mlselection.js,jquery.plugins/jquery.validate.js'));
                    $this->_config_seo('title', Lang::get('title_step2') . ' - ' . Conf::get('site_title'));
                    $this->assign('store', $store);
                    $this->display('apply.newstep2.html');
                }
                else
                {
               		$tel_phone = empty($_POST['tel_phone']) ? '' : trim($_POST['tel_phone']);                	
                	$_customerManager_mod = & m('customermanager');
                	$customerManager_info = $_customerManager_mod->get(" tel_phone = '" . $tel_phone."'");
                	if (!isset($customerManager_info['user_id']))
                	{
                		$customerManager_info['user_id'] = 0;
                	}
                	$store_mod  =& m('store');
                	$store_id = $this->visitor->get('user_id');
                	$data = array(
                        'store_id'     => $store_id,
                        'store_name'   => trim($_POST['store_name']),
                        'owner_name'   => trim($_POST['owner_name']),
                        'owner_card'   => trim($_POST['owner_card']),
                        'region_id'    => intval($_POST['region_id']),
                        'region_name'  => trim($_POST['region_name']),
                        'address'      => trim($_POST['address']),
                        'zipcode'      => trim($_POST['zipcode']),
                        'tel'          => trim($_POST['tel']),
                        'sgrade'       => 1,
                    	'manager_id'   => $customerManager_info['user_id'],
                        'state'        => 0,
                        'add_time'     => gmtime(),
                    	'store_type' => intval($_GET['store_type']),
                    	'secret_key' => $this->_get_secret_key(),
                    );
                    $image = $this->_upload_image($store_id);
                    if ($this->has_error())
                    {
                        $this->show_warning($this->get_error());
                        return;
                    }                    
                    /* �ж��Ƿ��Ѿ������ */
                    $state = $this->visitor->get('state');
                    if ($state != '' && $state == STORE_APPLYING)
                    {
                        $store_mod->edit($store_id, array_merge($data, $image));
                    }
                    else
                    {
                        $store_mod->add(array_merge($data, $image));
                    }
                    
                    if ($store_mod->has_error())
                    {
                        $this->show_warning($store_mod->get_error());
                        return;
                    }
                    $cod_cates = $_POST['cod_cates'];
					if(empty($cod_cates)){
                    	$cate_id = intval($_POST['cate_id']);
                    	$store_mod->unlinkRelation('has_scategory', $store_id);
                    	$store_mod->createRelation('has_scategory', $store_id, $cate_id);
					} else {
						$store_mod->unlinkRelation('has_scategory', $store_id);
						foreach ($cod_cates as $key => $val)
						{
							$cate_id = intval($val);
                    		$store_mod->createRelation('has_scategory', $store_id, $cate_id);
						}
					}
					$smslog =&  m('smslog'); 
		            import('class.smswebservice');    //������ŷ�����
		        	$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
                    $content = get_msg('toseller_store_passed_notify');	        		
					$smscontent = "������{$_POST['owner_name']}���ύ���룬������Ϊ��{$_POST['store_name']}���뾡�춨����ˡ�";
					$mobile = CHANNEL_VERIFY_MOBILE;
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
					$sgrade['need_confirm'] = 1 ;
                    if ($sgrade['need_confirm'])
                    {
                        $this->show_message('apply_ok',
                            'index', 'index.php');
                    }
                    else
                    {
                        $this->send_feed('store_created', array(
                            'user_id'   => $this->visitor->get('user_id'),
                            'user_name'   => $this->visitor->get('user_name'),
                            'store_url'   => SITE_URL . '/' . url('app=store&id=' . $store_id),
                            'seller_name'   => $data['store_name'],
                        ));
                        $this->_hook('after_opening', array('user_id' => $store_id));
                        $this->show_message('store_opened',
                            'index', 'index.php');
                    }
                }
                break;
            default:
                header("Location:index.php?app=apply&step=1");
                break;
        }
    }

    function check_name()
    {
        $store_name = empty($_GET['store_name']) ? '' : trim($_GET['store_name']);
        $store_id = empty($_GET['store_id']) ? 0 : intval($_GET['store_id']);

        $store_mod =& m('store');
        if (!$store_mod->unique($store_name, $store_id))
        {
            echo ecm_json_encode(false);
            return;
        }
        echo ecm_json_encode(true);
    }
	function check_owner_card(){
        $card = empty($_GET['owner_card']) ? '' : trim($_GET['owner_card']);
        $store_mod =& m('store');
        $d=$store_mod->get("owner_card ='".$card."'");
        if (isset($d['store_id']))
        {
            echo 'false';   
            return;        
        }
        echo 'true';
        return;
	}
    /* �ϴ�ͼƬ */
    function _upload_image($store_id)
    {
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_STORE_CERT); // 400KB

        $data = array();
        for ($i = 1; $i <= 3; $i++)
        {
            $file = $_FILES['image_' . $i];
            if ($file['error'] == UPLOAD_ERR_OK)
            {
                if (empty($file))
                {
                    continue;
                }
                $uploader->addFile($file);
                if (!$uploader->file_info())
                {
                    $this->_error($uploader->get_error());
                    return false;
                }

                $uploader->root_dir(ROOT_PATH);
                $dirname   = 'data/files/mall/application';
                $filename  = 'store_' . $store_id . '_' . $i;
                $data['image_' . $i] = $uploader->save($dirname, $filename);
            }
        }
        return $data;
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
    function _get_secret_key()
    {
    	return md5(rand(100000000, 999999999));
    }
}

?>
