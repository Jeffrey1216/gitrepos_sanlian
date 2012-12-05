<?php

/* ͶƱ��̨��������� */
class VoteApp extends BackendApp
{
	function __construct()
    {
        $this->Vote();
    }

    function Vote()
    {
        parent::__construct();
        $this->_theme_mod = &m('theme');       //ʵ����ͶƱ�����
        $this->_contents_mod = &m('contents'); //ʵ����ͶƱ���ݱ�
        $this->_records_mod = &m('records');   //ʵ������ͶƱ��¼��
    }
	/**
     *    ͶƱ�����б�
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return   void
     */
    function index()
    {
    	$page   =   $this->_get_page(10);    //��ȡ��ҳ��Ϣ
    	$rs = $this->_theme_mod->find(array(
									'fields'        => 'th.th_id,th.th_name,th_num,th_starttime,th_endtime',
						            'order'         => 'th.th_id DESC',
						            'limit'         => $page['limit'],
    								'count'         => true    //����ͳ��
						        ));
		foreach ($rs as $k=>$v)
		{

			$rs[$k]['th_starttime'] = date('Y-m-d H:i:s',$v['th_starttime']);
			$rs[$k]['th_endtime']   = date('Y-m-d H:i:s',$v['th_endtime']);
		}
		$page['item_count'] = $this->_theme_mod->getCount();   //��ȡͳ�Ƶ�����
		
        $this->_format_page($page);  		//��ʽ����ҳ��Ϣ
        
        $this->assign('page_info', $page);  //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��	

        $this->assign('rs', $rs);  			//��ͶƱ�����б��������ݸ���ͼ
	
        $this->display('vote.index.html');
    }
    
    /**
     *    ���ͶƱ���⼰ͶƱ����
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return   void
     */
    function addVote()
    {
    	//�ж��Ƿ�Ϊpost�ύ
    	if (!IS_POST)
    	{
    		$this->display('vote.add.html');
    	}else
    	{
    		$data['th_name']  = trim($_POST['title']); //ͶƱ����
    		$data['th_rules'] = $_POST['rules'];       //ͶƱ����˵��
    		$data['th_template'] = $_POST['template']; //����ģ��
    		$data['th_repeat']   = $_POST['repeat'] ? $_POST['repeat'] : 'no';   //�Ƿ������ظ�ͶƱ
    		$data['th_max']  = trim($_POST['maxnum']); //����ͶƱ�����ѡ��ֵ
    		$data['th_starttime'] = strtotime(trim($_POST['starttime'])); //ͶƱ��ʼʱ��
    		$data['th_endtime']   = strtotime(trim($_POST['endtime']));   //ͶƱ����ʱ��
    		$data['contents']     = $_POST['contents'];     //ͶƱ����
    		$this->_check_Data($data);               //��֤���ݵ���Ч��
    	
	    	if (!$_FILES['themeimg']['name'])
	    	{
	    		$this->show_warning('ͶƱר��ͼƬ�����ϴ���');
	    		exit;
	    	}
	    	if (empty($data['contents'])||!is_array($data['contents']))
	    	{
	    		$this->show_warning('����дͶƱ�������ݣ�');
	    		exit;
	    	}
    		$data['th_imgurl'] = $this->_upload_files();//�ϴ�����ͼƬ
    		$rs = $this->_theme_mod->add($data);     //���ͶƱ�������
    		if ($rs)
    		{
    			foreach ($data['contents'] as $v)
    			{
    				$contents['c_content'] = $v;
    				$contents['th_id']     = $rs;
    				if (trim($v))
    				{
    					$infos = $this->_contents_mod->add($contents);     //���ͶƱ�������
	    				if (!$infos)
	    				{
	    					$this->show_warning('ͶƱ�������ʧ�ܣ�');
	    					return;
	    				}
    				}
    				$contents = array();
    			}
    			$this->show_message('ͶƱ������ӳɹ���','back_list',        'index.php?app=vote',
                					'add_again',    'index.php?app=vote&amp;act=addVote');
    			return;
    		}else
    		{
    			$this->show_warning('ͶƱ�������ʧ�ܣ�');
    			return;
    		}
    	}
    }
    
    /**
     *    �鿴ͶƱ�����Ӧ��ͶƱ����
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return   void
     */
    function detailVote()
    {
    	$id = intval($_GET['tid']);  	
    	$theme = $this->_theme_mod->get($id);
    	if ($theme)
    	{
    		//ͨ��ͶƱ�����ѯ��������
			$content = $this->_contents_mod->find(array(
													'conditions' => 'th_id='.$id,
													'order'      => 'c_id ASC'
													));
	    	
			//����ʱ���
			$theme['th_starttime'] = date('Y-m-d H:i:s',$theme['th_starttime']);
			$theme['th_endtime']   = date('Y-m-d H:i:s',$theme['th_endtime']);
			$theme['th_repeat']     = $theme['th_repeat']=='yes' ? '��' : '��';	
			$this->assign('theme',$theme);
			$this->assign('content',$content);
    		$this->display('vote.detail.html');
    	}else 
    	{
    		$this->show_warning('��Ҫ��ѯ��ͶƱ���ⲻ���ڣ�');
    		return;
    	}
    }
    /**
     *    �༭ͶƱ�����Լ���Ӧ��ͶƱ����
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return   void
     */
    function editVote()
    {
    	$id = $_GET['tid']; //����id
    	if ($id)
    	{
    		if (!IS_POST)
    		{
    			$theme = $this->_theme_mod->get($id); //��ȡͶƱ���������
    			$theme['th_starttime'] = date('Y-m-d H:i:s',$theme['th_starttime']);
				$theme['th_endtime']   = date('Y-m-d H:i:s',$theme['th_endtime']);
				$this->assign('rs',$theme);
    			$this->display('vote.edit_vote.html');
    		}else
    		{
    			$data['th_name']  = trim($_POST['title']); //ͶƱ����
	    		$data['th_rules'] = $_POST['rules'];       //ͶƱ����˵��
	    		$data['th_template'] = $_POST['template']; //����ģ��
	    		$data['th_repeat']   = $_POST['repeat'] ? $_POST['repeat'] : 'no';   //�Ƿ������ظ�ͶƱ
	    		$data['th_max']  = trim($_POST['maxnum']); //����ͶƱ�����ѡ��ֵ
	    		$data['th_starttime'] = strtotime(trim($_POST['starttime'])); //ͶƱ��ʼʱ��
	    		$data['th_endtime']   = strtotime(trim($_POST['endtime']));   //ͶƱ����ʱ��
	    		$this->_check_Data($data);               //��֤���ݵ���Ч��
	    		$this->_theme_mod->edit($id,$data);//����ͶƱ����
	    		$this->show_message('����ͶƱ����ɹ���');
    			return;
    		}
    	}else
    	{
    		$this->show_warning('��Դ���Ϸ���ϵͳ����');
    		return;
    	}
    }
	/**
     *    ɾ��ͶƱ���⼰��Ӧ��ͶƱ����
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return   void
     */
    function deleteVote()
    {
    	$id = $_GET['tid']; //����id
    	if ($id)
    	{
    		$theme = $this->_theme_mod->get($id); //��ȡͶƱ���������
    		if ($theme['th_num']>0)
    		{
    			$this->show_warning('�����û�����ͶƱ������ɾ�������⣡');
    			return;
    		}else
    		{
    			if ($theme)
    			{
    				//ɾ��ͶƱ���⣬��ɾ�������Ӧ������
    				$this->_theme_mod->drop($id);
    				$this->_contents_mod->drop(array('condtion'=>'th_id = '.$id));
    				$this->show_message('�ɹ�ɾ��ͶƱ��������ݡ�');
    				return;
    			}else 
    			{
    				$this->show_warning('ͶƱ���ⲻ���ڣ�');
    				return;
    			}
    		}
    	}else
    	{
    		$this->show_warning('��Դ���Ϸ���ϵͳ����');
    		return;
    	}
    }
    /**
     *    ���ͶƱ���⼰ͶƱ����
     *
     *    @author   lihuoliang
     *    @param    array
     *    @return   void
     */
    function _check_Data($data)
    {
    	if (!$data['th_name'])
    	{
    		$this->show_warning('����дͶƱ���⣡');
    		exit;
    	}
    	if (!$data['th_rules'])
    	{
    		$this->show_warning('����дͶƱ����˵����');
    		exit;
    	}
    	if(!$data['th_template'])
    	{
    		$this->show_warning('ͶƱר����벻��Ϊ�գ�');
    		exit;
    	}
    	if(!$data['th_starttime']||!$data['th_endtime'])
    	{
    		$this->show_warning('ͶƱ��ʼʱ�䲻��Ϊ�գ�');
    		exit;
    	}else
    	{
    		if (!$this->_is_Date($data['th_starttime']))
    		{
    			$this->show_warning('ͶƱ��ʼʱ�䲻�Ϸ���');
    			exit;
    		}
    		if (!$this->_is_Date($data['th_endtime']))
    		{
    			$this->show_warning('ͶƱ����ʱ�䲻�Ϸ���');
    			exit;
    		}
    		if ($data['th_starttime']>$data['th_endtime'])
    		{
    			$this->show_warning('ͶƱ��ʼʱ�䲻�ܴ��ڽ���ʱ�䣡');
    			exit;
    		}
    	}
    }
    /*
     * �ж������Ƿ�Ϸ�
     */
	function _is_Date($unixTime_1,$format="Y-m-d"){
		 if ( !is_numeric($unixTime_1) ) 
		 {
		 	return false; //��������ڣ��򷵻�
		 }else
		 {
		 	return true;
		 }
//		 $checkDate = date($format, $unixTime_1);
//		 $unixTime_2 = strtotime($checkDate);;
//		 if($unixTime_1 == $unixTime_2)
//		 {
//		 	return true;
//		 }else{
//		 	return false;
//		 }
	 }
	/*
     * �ϴ�����ͼƬ������������
     */
	function _upload_files(){
		//����ͼƬ�ϴ���
        import('uploader.lib');
        $data      = array();
        $file = $_FILES['themeimg'];
        $fileurl = '';
        if ($file['error'] == UPLOAD_ERR_OK && $file !='')
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);//����ͼƬ�ϴ����ͣ�gif|jpg|jpeg|png
            $uploader->allowed_size(1024000);        // ����ͼƬ�ϴ���С�����1M
            $uploader->addFile($file);
            if ($uploader->file_info() === false)
            {
                $this->show_warning($uploader->get_error());
            }
            $uploader->root_dir(ROOT_PATH);
        	$filename  = $uploader->random_filename(); //����ͼƬ�ļ�����
            $fileurl = $uploader->save('data/files/votebanner', $filename); //����ͼƬ
        }
        return $fileurl;
   	 }
   	/*
     * �鿴ͶƱ������
     */
	function detail_examine()
    {
    	$id = $_GET['cid']; //����id
    	if ($id)
    	{
    		$page   =   $this->_get_page(10);    //��ȡ��ҳ��Ϣ
    		$sql = "SELECT r.*,m.user_name,m.email,m.mobile,m.real_name FROM pa_records r , pa_member m WHERE r.uid=m.user_id AND r.c_id=".$id." LIMIT ".$page['limit'];
    		$sql1 = "SELECT count(r.uid) FROM pa_records r , pa_member m WHERE r.uid=m.user_id AND r.c_id=".$id;
    		$rs = $this->_contents_mod->getAll($sql);
    		$rs1 = $this->_contents_mod->getOne($sql1);
    		foreach ($rs as $k=>$v)
			{
	
				$rs[$k]['r_time'] = date('Y-m-d H:i:s',$v['r_time']);
			}
			$page['item_count'] = $rs1;   		//��ȡͳ�Ƶ�����
			
	        $this->_format_page($page);  		//��ʽ����ҳ��Ϣ
	        
	        $this->assign('page_info', $page);  //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��	
	
	        $this->assign('rs', $rs);  			//��ͶƱ�����б��������ݸ���ͼ
    		$this->display('vote.detail_examine.html');
    	}else
    	{
    		$this->show_warning('��Դ���Ϸ���ϵͳ����');
    		return;
    	}
    }
	/*
     * ɾ��ͶƱ�ľ���������
     */
	function delContent()
    {
    	$id = $_GET['cid']; //����id
    	if ($id)
    	{
    		$content = $this->_contents_mod->get($id); //��ȡͶƱ���ݵ�����
    		if ($content['c_num']>0)
    		{
    			$this->show_warning('�����û�����ͶƱ������ɾ�������ݣ�');
    			return;
    		}else
    		{
    			if ($content)
    			{
    				//ɾ��ͶƱ����
    				$this->_contents_mod->drop($id);
    				$this->show_message('�ɹ�ɾ��ͶƱ���ݡ�');
    				return;
    			}else 
    			{
    				$this->show_warning('ͶƱ���ݲ����ڣ�');
    				return;
    			}
    		}
    	}else
    	{
    		$this->show_warning('��Դ���Ϸ���ϵͳ����');
    		return;
    	}
    }
	/*
     * �޸����ݾ�����Ϣ
     */
	function editContent()
    {
    	$id = $_GET['cid']; //����id
    	if ($id)
    	{
    		if (!IS_POST)
    		{
    			$content = $this->_contents_mod->get($id); //��ȡͶƱ���ݵ�����
    			if ($content)
    			{
    				$this->assign('rs',$content);
    				$this->display('vote.edit_content.html');
    				return;
    			}else 
    			{
    				$this->show_warning('ͶƱ���ݲ����ڣ�');
    				return;
    			}
    		}else
    		{
    			$data = $_POST['content'];
	    		$content = $this->_contents_mod->edit($id,"c_content='$data'");
	    		$this->show_message('����ͶƱ���ݳɹ���');
	    		return;
    		}
    	}else
    	{
    		$this->show_warning('��Դ���Ϸ���ϵͳ����');
    		return;
    	}
    }
}

?>
