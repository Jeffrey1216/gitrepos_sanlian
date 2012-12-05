<?php

/* 友情链接增删改查*/
class FrendlinkApp extends BackendApp
{ 
    function index()
    {
        $this->assign("imdir",IMAGE_URL);
       $friend_link_mod = &m('friend_link');
        $page = $this->_get_page(20);
        $fl = $friend_link_mod->find(array(
        							'field' => 'link_id,link_url,show_order,link_name,link_logo,type',
        							'limit' => $page['limit'],
        							'count' => true,
        							));
        $num = $friend_link_mod->getCount();
        $page['item_count'] = $num;
        $this->_format_page($page);	
        $this->assign('page_info', $page); 
        foreach($fl as $k=>$v)
        {
        	$fl[$k]['link_logo'] = SITE_URL.'/'.$v['link_logo'];
        }
        $this->assign('fl', $fl);
        $this->display('frendlink.index.html');
    }
    function add(){//增
		$temadd=1;
		$this->assign("temadd",$temadd);
       $friend_link_mod = &m('friend_link');
    	if(!IS_POST){
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'));
    			$this->display('frendlink.form.html');
    	}else
    	{
    		$dat=$this->_upload_files();
    		$data = array();
    		$data['link_name'] = $_POST['link_name'];
    		$data['link_url'] = $_POST['link_url'];
    		$data['show_order'] = $_POST['show_order'];
    		$data['type'] = $_POST['type'];
    		$data['link_logo'] =   $dat['dir'];
    		$name = trim($data['link_name']);
    		$link = trim($data['link_url']);
    		$order = trim($data['show_order']);
    		$type = trim($data['type']);
    		$image = $data['link_logo'];
    		if(empty($name))
    		{
    			$this->show_warning("请输入链接名称");
    			return;
    		}else
    		{
    			$data['link_name'] = $name;
    		}
    		if(empty($link))
    		{
    			$this->show_warning("请输入连接地址");
    			return;
    		}else{
    			if(substr($link,0,7)=='http://'||substr($link,0,7)=='http:\\')
    			{
    				$data['link_url']=$link;
    			}else
    			{
    				$data['link_url']='http://'.$link;
    			}
    		}
			if(empty($order))
			{
				$data['show_order']=1;
			}elseif (!is_numeric($order))
			{
				$this->show_warning("请在排序中输入数字");
				return;
			}else
			{
				$data['show_order'] = $order;
			}
			if(empty($type))
			{
    			$data['type'] = 2;
			}elseif (!is_numeric($type))
			{
				$this->show_warning("请在类型中输入数字");
				return;
			}else
			{
				$data['type'] = $type;
			}
            if($friend_link_mod->add($data))
            {
    			
        		$this->show_warning('增加成功','返回管理首页','index.php?app=frendlink');
        		return;
            }else{
    			$this->show_warning("请重新添加");
        		return;
            }
    	}
    }
    /* 编辑 */
    function edit()
    {
    	$temedit = 2;
    	$this->assign("temedit",$temedit);
        $this->assign("imdir",IMAGE_URL);
        $friend_link_mod = &m('friend_link');
        $id = empty($_GET['link_id']) ? 0 : intval($_GET['link_id']);
        $fdata = $friend_link_mod->get_info($id);
        if (!IS_POST)
        {
           // $this->assign('link_url', $link_url);
            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->assign('frend',$fdata);
            $this->display('frendlink.form.html');
        }
        else
        {	
        	$dat=$this->_upload_files();
    		$data = array();
    		$data['link_name'] = $_POST['link_name'];
    		$data['link_url'] = $_POST['link_url'];
    		$data['show_order'] = $_POST['ordernum'];
    		$data['type'] = $_POST['type'];
    		$data['link_logo'] =   $dat['dir'];
    		$name = trim($data['link_name']);
    		$link = trim($data['link_url']);
    		$order = trim($data['show_order']);
    		$type = trim($data['type']);
    		$image = $data['link_logo'];
    		if(empty($name))
    		{
    			$data['link_url'] = $fdata['link_url'];
    		};
    		if(empty($type))
    		{
    			$data['type'] = $fdata['type'];
    		}
    		if(empty($link))
    		{
    			$data['link_url'] = $fdata['link_url'];
    		}else{
    			if(substr($link,0,7)=='http://'||substr($link,0,7)=='http:\\')
    			{
    				$data['link_url']=$link;
    			}else
    			{
    				$data['link_url']='http://'.$link;
    			}
    		}
    		if(empty($order)||(!is_numeric($order)))
    		{
    			$data['show_order'] = $fdata['show_order'];
    		}
    		if(empty($image))
    		{
    			$data['link_logo'] = $fdata['link_logo'];
    		}
        	if($friend_link_mod->edit($id, $data))
        	{
        		$this->show_warning('修改成功','返回管理首页','index.php?app=frendlink');
        		return;
        	}else {
        		$this->show_warning('修改成功','返回管理首页','index.php?app=frendlink');
        		return;
        	}
            }
        
    }
    
    function dele(){//删
    	$friend_link_mod  = &m('friend_link');
         $id = isset($_GET['link_id']) ? trim($_GET['link_id']) : '';
        if (!$id)
        {
            $this->show_warning('no_such_brand');

            return;
        }
        $data = $friend_link_mod->get_info($id);
        $friend_link_mod->drop($id);
        show_message("删除成功");
        
    }

function _upload_files()
    { 	
    	import('uploader.lib');
    	$daimg = array();
    	$file = $_FILES['file'];
    	$Filedir = 'data/files/articles/';
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
            $uploader->save('data/files/articles',$filena);
			$daimg['dir']=$Filedir.$FileName;
    	}
    	return $daimg;
    }
}

?>
