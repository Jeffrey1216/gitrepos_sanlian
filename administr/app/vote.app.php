<?php

/* 投票后台管理控制器 */
class VoteApp extends BackendApp
{
	function __construct()
    {
        $this->Vote();
    }

    function Vote()
    {
        parent::__construct();
        $this->_theme_mod = &m('theme');       //实例化投票主题表
        $this->_contents_mod = &m('contents'); //实例化投票内容表
        $this->_records_mod = &m('records');   //实例化获投票记录表
    }
	/**
     *    投票主题列表
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return   void
     */
    function index()
    {
    	$page   =   $this->_get_page(10);    //获取分页信息
    	$rs = $this->_theme_mod->find(array(
									'fields'        => 'th.th_id,th.th_name,th_num,th_starttime,th_endtime',
						            'order'         => 'th.th_id DESC',
						            'limit'         => $page['limit'],
    								'count'         => true    //允许统计
						        ));
		foreach ($rs as $k=>$v)
		{

			$rs[$k]['th_starttime'] = date('Y-m-d H:i:s',$v['th_starttime']);
			$rs[$k]['th_endtime']   = date('Y-m-d H:i:s',$v['th_endtime']);
		}
		$page['item_count'] = $this->_theme_mod->getCount();   //获取统计的数据
		
        $this->_format_page($page);  		//格式化分页信息
        
        $this->assign('page_info', $page);  //将分页信息传递给视图，用于形成分页条	

        $this->assign('rs', $rs);  			//将投票主题列表结果集传递给视图
	
        $this->display('vote.index.html');
    }
    
    /**
     *    添加投票主题及投票内容
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return   void
     */
    function addVote()
    {
    	//判断是否为post提交
    	if (!IS_POST)
    	{
    		$this->display('vote.add.html');
    	}else
    	{
    		$data['th_name']  = trim($_POST['title']); //投票主题
    		$data['th_rules'] = $_POST['rules'];       //投票规则说明
    		$data['th_template'] = $_POST['template']; //主题模板
    		$data['th_repeat']   = $_POST['repeat'] ? $_POST['repeat'] : 'no';   //是否允许重复投票
    		$data['th_max']  = trim($_POST['maxnum']); //允许投票的最大选项值
    		$data['th_starttime'] = strtotime(trim($_POST['starttime'])); //投票开始时间
    		$data['th_endtime']   = strtotime(trim($_POST['endtime']));   //投票结束时间
    		$data['contents']     = $_POST['contents'];     //投票内容
    		$this->_check_Data($data);               //验证数据的有效性
    	
	    	if (!$_FILES['themeimg']['name'])
	    	{
	    		$this->show_warning('投票专题图片必须上传！');
	    		exit;
	    	}
	    	if (empty($data['contents'])||!is_array($data['contents']))
	    	{
	    		$this->show_warning('请填写投票话题内容！');
	    		exit;
	    	}
    		$data['th_imgurl'] = $this->_upload_files();//上传主题图片
    		$rs = $this->_theme_mod->add($data);     //添加投票主题入库
    		if ($rs)
    		{
    			foreach ($data['contents'] as $v)
    			{
    				$contents['c_content'] = $v;
    				$contents['th_id']     = $rs;
    				if (trim($v))
    				{
    					$infos = $this->_contents_mod->add($contents);     //添加投票内容入库
	    				if (!$infos)
	    				{
	    					$this->show_warning('投票内容添加失败！');
	    					return;
	    				}
    				}
    				$contents = array();
    			}
    			$this->show_message('投票主题添加成功。','back_list',        'index.php?app=vote',
                					'add_again',    'index.php?app=vote&amp;act=addVote');
    			return;
    		}else
    		{
    			$this->show_warning('投票主题添加失败！');
    			return;
    		}
    	}
    }
    
    /**
     *    查看投票主题对应的投票内容
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
    		//通过投票主题查询所有内容
			$content = $this->_contents_mod->find(array(
													'conditions' => 'th_id='.$id,
													'order'      => 'c_id ASC'
													));
	    	
			//处理时间戳
			$theme['th_starttime'] = date('Y-m-d H:i:s',$theme['th_starttime']);
			$theme['th_endtime']   = date('Y-m-d H:i:s',$theme['th_endtime']);
			$theme['th_repeat']     = $theme['th_repeat']=='yes' ? '是' : '否';	
			$this->assign('theme',$theme);
			$this->assign('content',$content);
    		$this->display('vote.detail.html');
    	}else 
    	{
    		$this->show_warning('你要查询的投票主题不存在！');
    		return;
    	}
    }
    /**
     *    编辑投票主题以及对应的投票内容
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return   void
     */
    function editVote()
    {
    	$id = $_GET['tid']; //主题id
    	if ($id)
    	{
    		if (!IS_POST)
    		{
    			$theme = $this->_theme_mod->get($id); //获取投票主题的详情
    			$theme['th_starttime'] = date('Y-m-d H:i:s',$theme['th_starttime']);
				$theme['th_endtime']   = date('Y-m-d H:i:s',$theme['th_endtime']);
				$this->assign('rs',$theme);
    			$this->display('vote.edit_vote.html');
    		}else
    		{
    			$data['th_name']  = trim($_POST['title']); //投票主题
	    		$data['th_rules'] = $_POST['rules'];       //投票规则说明
	    		$data['th_template'] = $_POST['template']; //主题模板
	    		$data['th_repeat']   = $_POST['repeat'] ? $_POST['repeat'] : 'no';   //是否允许重复投票
	    		$data['th_max']  = trim($_POST['maxnum']); //允许投票的最大选项值
	    		$data['th_starttime'] = strtotime(trim($_POST['starttime'])); //投票开始时间
	    		$data['th_endtime']   = strtotime(trim($_POST['endtime']));   //投票结束时间
	    		$this->_check_Data($data);               //验证数据的有效性
	    		$this->_theme_mod->edit($id,$data);//更新投票主题
	    		$this->show_message('更新投票主题成功。');
    			return;
    		}
    	}else
    	{
    		$this->show_warning('来源不合法，系统出错！');
    		return;
    	}
    }
	/**
     *    删除投票主题及对应的投票内容
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return   void
     */
    function deleteVote()
    {
    	$id = $_GET['tid']; //主题id
    	if ($id)
    	{
    		$theme = $this->_theme_mod->get($id); //获取投票主题的详情
    		if ($theme['th_num']>0)
    		{
    			$this->show_warning('已有用户进行投票，不能删除此主题！');
    			return;
    		}else
    		{
    			if ($theme)
    			{
    				//删除投票主题，在删除主题对应的内容
    				$this->_theme_mod->drop($id);
    				$this->_contents_mod->drop(array('condtion'=>'th_id = '.$id));
    				$this->show_message('成功删除投票主题和内容。');
    				return;
    			}else 
    			{
    				$this->show_warning('投票主题不存在！');
    				return;
    			}
    		}
    	}else
    	{
    		$this->show_warning('来源不合法，系统出错！');
    		return;
    	}
    }
    /**
     *    添加投票主题及投票内容
     *
     *    @author   lihuoliang
     *    @param    array
     *    @return   void
     */
    function _check_Data($data)
    {
    	if (!$data['th_name'])
    	{
    		$this->show_warning('请填写投票主题！');
    		exit;
    	}
    	if (!$data['th_rules'])
    	{
    		$this->show_warning('请填写投票规则说明！');
    		exit;
    	}
    	if(!$data['th_template'])
    	{
    		$this->show_warning('投票专题代码不能为空！');
    		exit;
    	}
    	if(!$data['th_starttime']||!$data['th_endtime'])
    	{
    		$this->show_warning('投票起始时间不能为空！');
    		exit;
    	}else
    	{
    		if (!$this->_is_Date($data['th_starttime']))
    		{
    			$this->show_warning('投票开始时间不合法！');
    			exit;
    		}
    		if (!$this->_is_Date($data['th_endtime']))
    		{
    			$this->show_warning('投票结束时间不合法！');
    			exit;
    		}
    		if ($data['th_starttime']>$data['th_endtime'])
    		{
    			$this->show_warning('投票开始时间不能大于结束时间！');
    			exit;
    		}
    	}
    }
    /*
     * 判断日期是否合法
     */
	function _is_Date($unixTime_1,$format="Y-m-d"){
		 if ( !is_numeric($unixTime_1) ) 
		 {
		 	return false; //如果非日期，则返回
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
     * 上传主题图片到服务器保存
     */
	function _upload_files(){
		//导入图片上传类
        import('uploader.lib');
        $data      = array();
        $file = $_FILES['themeimg'];
        $fileurl = '';
        if ($file['error'] == UPLOAD_ERR_OK && $file !='')
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);//限制图片上传类型：gif|jpg|jpeg|png
            $uploader->allowed_size(1024000);        // 限制图片上传大小：最大1M
            $uploader->addFile($file);
            if ($uploader->file_info() === false)
            {
                $this->show_warning($uploader->get_error());
            }
            $uploader->root_dir(ROOT_PATH);
        	$filename  = $uploader->random_filename(); //设置图片文件名称
            $fileurl = $uploader->save('data/files/votebanner', $filename); //保存图片
        }
        return $fileurl;
   	 }
   	/*
     * 查看投票人数的
     */
	function detail_examine()
    {
    	$id = $_GET['cid']; //内容id
    	if ($id)
    	{
    		$page   =   $this->_get_page(10);    //获取分页信息
    		$sql = "SELECT r.*,m.user_name,m.email,m.mobile,m.real_name FROM pa_records r , pa_member m WHERE r.uid=m.user_id AND r.c_id=".$id." LIMIT ".$page['limit'];
    		$sql1 = "SELECT count(r.uid) FROM pa_records r , pa_member m WHERE r.uid=m.user_id AND r.c_id=".$id;
    		$rs = $this->_contents_mod->getAll($sql);
    		$rs1 = $this->_contents_mod->getOne($sql1);
    		foreach ($rs as $k=>$v)
			{
	
				$rs[$k]['r_time'] = date('Y-m-d H:i:s',$v['r_time']);
			}
			$page['item_count'] = $rs1;   		//获取统计的数据
			
	        $this->_format_page($page);  		//格式化分页信息
	        
	        $this->assign('page_info', $page);  //将分页信息传递给视图，用于形成分页条	
	
	        $this->assign('rs', $rs);  			//将投票主题列表结果集传递给视图
    		$this->display('vote.detail_examine.html');
    	}else
    	{
    		$this->show_warning('来源不合法，系统出错！');
    		return;
    	}
    }
	/*
     * 删除投票的具体内容项
     */
	function delContent()
    {
    	$id = $_GET['cid']; //内容id
    	if ($id)
    	{
    		$content = $this->_contents_mod->get($id); //获取投票内容的详情
    		if ($content['c_num']>0)
    		{
    			$this->show_warning('已有用户进行投票，不能删除此内容！');
    			return;
    		}else
    		{
    			if ($content)
    			{
    				//删除投票内容
    				$this->_contents_mod->drop($id);
    				$this->show_message('成功删除投票内容。');
    				return;
    			}else 
    			{
    				$this->show_warning('投票内容不存在！');
    				return;
    			}
    		}
    	}else
    	{
    		$this->show_warning('来源不合法，系统出错！');
    		return;
    	}
    }
	/*
     * 修改内容具体信息
     */
	function editContent()
    {
    	$id = $_GET['cid']; //内容id
    	if ($id)
    	{
    		if (!IS_POST)
    		{
    			$content = $this->_contents_mod->get($id); //获取投票内容的详情
    			if ($content)
    			{
    				$this->assign('rs',$content);
    				$this->display('vote.edit_content.html');
    				return;
    			}else 
    			{
    				$this->show_warning('投票内容不存在！');
    				return;
    			}
    		}else
    		{
    			$data = $_POST['content'];
	    		$content = $this->_contents_mod->edit($id,"c_content='$data'");
	    		$this->show_message('更新投票内容成功。');
	    		return;
    		}
    	}else
    	{
    		$this->show_warning('来源不合法，系统出错！');
    		return;
    	}
    }
}

?>
