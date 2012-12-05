<?php

/**
 *    挂件管理控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class WidgetApp extends BackendApp
{
    function index()
    {
        /* 读取已安装的挂件 */
        $widgets = list_widget();
        $this->assign('widgets', $widgets);
        $this->display('widget.index.html');
    }

    /**
     *    编辑挂件脚本
     *
     *    @author    Garbin
     *    @return    void
     */
    function edit()
    {
        $name = empty($_GET['name']) ? 0 : trim($_GET['name']);
        if (!$name)
        {
            $this->show_warning('no_such_widget');

            return;
        }
        $script_file = $this->_get_file($name, $_GET['file']);
        if (!IS_POST)
        {
            $this->assign('code', file_get_contents($script_file));
            $this->display('widget.form.html');
        }
        else
        {
            if (!file_put_contents($script_file, stripslashes($_POST['code'])))
            {
                $this->show_warning('edit_file_failed');

                return;
            }

            $this->show_message('edit_file_successed');
        }
    }
    /**
     * 	  更新挂件数据
     * 	 @author typedef 
     * 
     */
    public function editWidgetData() {
    	$flag = false;
    	$widget_name = empty($_GET['widget_name']) ? 0 : trim($_GET['widget_name']);
    	$widget_type = empty($_GET['widget_type']) ? 0 : trim($_GET['widget_type']);
    	$pics_num = empty($_GET['pics_num']) ? 0 : trim($_GET['pics_num']);	
	    $goods_num = empty($_GET['goods_num']) ? 0 : trim($_GET['goods_num']);
   		if (!$widget_name)
        {
            $this->show_warning('no_such_widget');
            return;
        }
        $goods_mod = &m("goods");
        $goods_info = $goods_mod->getAll("select sg.store_id,sg.goods_id,sg.gs_id,g.goods_name from pa_store_goods sg left join pa_goods g on sg.goods_id = g.goods_id ");
        $all_goods = array();
        foreach($goods_info as $k => $v)
		{
			$all_goods[$v['gs_id']] = $v; 
		}
        $widget_mod = &m("widget");
        $widget_info = $widget_mod->get(array('conditions'=>"widget_name='".$widget_name."'"));
        if(!IS_POST) {
        	//var_dump($widget_info);
	        if(!$widget_info) { //没有数据,新建.
	        	$widget_data = array();
				if($pics_num > 0) {
					for($i = 0 ; $i < $pics_num ; $i++) {
						$widget_data['images'][$i]['img'] = '';
						$widget_data['images'][$i]['url'] = '';
						$widget_data['images'][$i]['title'] = '';
					}
				}
				if($goods_num > 0) {
					for($i = 0 ; $i < $goods_num ; $i++) {
						$widget_data['goods'][$i]['gs_id'] = 0;
					}
				}
				$widget_data = serialize($widget_data);
				$widget_mod->add(array(
					'widget_name' => $widget_name,
					'widget_data' => $widget_data,
					'widget_type' => $widget_type		
				));
				header("Location:index.php?app=widget&act=editWidgetData&widget_name=$widget_name&pics_num={$pics_num}&goods_num={$goods_num}");
	        	/*$flag = true;
	        	$this->assign('widget_name',$widget_name);
	        	$this->assign('pics_num',$pics_num);
	        	$this->assign('goods_num',$goods_num);
	        	$this->display("widgetConfigAdd.form.html");*/
	        } else {
	        	//获取商品大分类
	        	$gcategory_mod = & m('gcategory'); 
	        	$gcategory_list = $gcategory_mod->find(array('conditions'=>'parent_id=0'));
	        	$this->assign('gcategory_list',$gcategory_list);
	        	//存在数据, 修改
	        	$widget_data = unserialize($widget_info['widget_data']);
	        	$this->assign('widget_name',$widget_name);
	    //    	var_dump($widget_data);
	        	$this->assign('widget_data',$widget_data);
	        	$this->assign('pics_num',$pics_num);
	        	$this->assign('goods_num',$goods_num);
	        	$this->display("widgetConfigEdit.form.html");
	        }
        } else {
        	//var_dump($_FILES);
        	if(!$widget_info) {//保存提交 
        		
        		
        	} else { //保存修改
        		$widget_data = unserialize($widget_info['widget_data']);
        		if(isset($_POST['label']) && $_POST['label'] != 0 && isset($_POST['gs_id']) && $_POST['gs_id'] != '') {
	        		$label = $_POST['label'];
	        		$gs_id = $_POST['gs_id'];
	        		foreach($widget_data['goods'] as $k => $v) {
	        			if($k == (intval($label)-1)) {
	        				$widget_data['goods'][$k]['gs_id'] = $gs_id;	
	        				break;
	        			}
	        		}
	        	}
        		if(isset($_POST['label']) && $_POST['label'] != 0 && isset($_POST['store_id']) && $_POST['store_id'] != '') {
	        		$label = $_POST['label'];
	        		$store_id = $_POST['store_id'];
	        		foreach($widget_data['goods'] as $k => $v) {
	        			if($k == (intval($label)-1)) {
	        				$widget_data['goods'][$k]['store_id'] = $store_id;	
	        				break;
	        			}
	        		}
	        	}
	        	if(isset($_FILES['pics']['tmp_name']) && $_FILES['pics']['tmp_name'] != null) {
	        		//文件后缀数组
	        		$ends = array();
	        		//取文件后缀
	        		foreach($_FILES['pics']['name'] as $k => $v) {
	        			if($v == '') {
	        				$end[$k] = '';
	        			} else {
	        				$ends[$k] = strrchr($v,'.');
	        			}
	        		}
	        		//var_dump($ends);
	        		//上传文件,并且将上传位置放入$widget_data数组
	        		foreach($_FILES['pics']['tmp_name'] as $k => $tmp) {
	        			if(isset($tmp) && $tmp != '') { //上传图片处理
	        				$fileName = 'data/files/mall/widgetImage/'.$widget_name.$k.$ends[$k];
	        				move_uploaded_file($tmp,ROOT_PATH.'/'.$fileName);
	        				//修改数据库数据
	        				$widget_data['images'][$k]['img'] = $fileName;
	        				//echo "$fileName<br/>";
	        			}
	        		}
	        	}
	        	//修改图片指向URL
	        	if(isset($_POST['urls']) && $_POST['urls'] != null) {
	        		foreach($_POST['urls'] as $k => $url) {
	        			$widget_data['images'][$k]['url'] = $url;
	        		}
	        	}
	        	//修改图片标题信息
	        	if(isset($_POST['title']) && $_POST['title'] != null) {
	        		foreach($_POST['title'] as $k => $url) {
	        			$widget_data['images'][$k]['title'] = $url;
	        		}
	        	}
	        	if(isset($_POST['goods_id']) && $_POST['goods_id'] != null && is_array($_POST['goods_id'])) {
	        		foreach($_POST['goods_id'] as $k => $good) {
	        			$widget_data['goods'][$k]['goods_id'] = $good;
	        		}
	        	}
	        	if(isset($_POST['subjoin_img']) && $_POST['subjoin_img'] != null) {
	        		foreach($_POST['subjoin_img'] as $k => $v) {
	        			$widget_data['goods'][$k]['subjoin_img'] = $v;
	        		}
	        	}
	        	if(isset($_POST['cate_id']) && $_POST['cate_id'] != null) {
	        		foreach($_POST['cate_id'] as $k => $v) {
	        			$widget_data['images'][$k]['cate_id'] = ($v == 'na') ? 0 : $v;
	        		}
	        	}
	            //var_dump($widget_data['goods']);
	        	$widget_data = serialize($widget_data);
	        	$widget_mod->edit($widget_info['widget_id'],"widget_data='".$widget_data."'");
	        	
	        	header("Location:index.php?app=widget&act=editWidgetData&widget_name=$widget_name&pics_num={$pics_num}&goods_num={$goods_num}");
        	}
        }
    }
    public function search() {
    	header("Content-Type:text/html;charset=uft-8");
    	$widget_name = empty($_GET['widget_name']) ? 0 : trim($_GET['widget_name']);
    	$pics_num = empty($_GET['pics_num']) ? 0 : trim($_GET['pics_num']);	
	    $goods_num = empty($_GET['goods_num']) ? 0 : trim($_GET['goods_num']);
    	$label = empty($_GET['label']) ? 0 : trim($_GET['label']);
    	$conditions = "";
    	$goods_mod = &m("goods");
    	
        if(!IS_POST) {
/*	        foreach($gcategory_info as $k => $v) {
	    		$gcategory_info[$k]['cate_name'] = iconv('gb2312','utf-8',$v['cate_name']);
	    	}*/
        	//$this->assign('gcategory_info',$gcategory_info);
        	$this->assign('widget_name',$widget_name);
        	$this->assign('pics_num',$pics_num);
	        $this->assign('goods_num',$goods_num);
        	$this->assign('label',$label);
        	$this->assign('store_id',STORE_ID);
        	$this->display("searchGoods.form.html");
        } else {
        	$this->assign('store_id',STORE_ID);
        	$conditions = '1 = 1 ';
        	if(isset($_POST['gs_id']) && $_POST['gs_id'] != null) {
        		$conditions .= " and sg.gs_id =".$_POST['gs_id']." ";
        	}
        	if(isset($_POST['store_id']) && $_POST['store_id'] != null) {
        		$conditions .= " and sg.store_id =".$_POST['store_id']." ";
        	}
        	if(isset($_POST['goods_name']) && $_POST['goods_name'] != null) {
        		$goods_name = iconv('utf-8','gb2312',$_POST['goods_name']);
        		if($conditions != "") {
        			$conditions .= "and g.goods_name like '%".$goods_name."%' ";
        		} else {
        			$conditions .= " g.goods_name like '%".$goods_name."%' ";
        		}
        	} 
        	if(isset($_POST['store_id']) && $_POST['store_id'] != '') {
        		$area_type = $_POST['store_id'];
        		$conditions .= " and sg.store_id =".$_POST['store_id']." ";
        	}
        	
        	$conditions .= ' AND g.if_show=1 AND g.closed=0';
        	
        	$label = empty($_POST['label']) ? 0 : trim($_POST['label']);
	        $goods_info = $goods_mod->getAll("select sg.gs_id,sg.store_id,sg.goods_id,g.goods_name from pa_store_goods sg left join pa_goods g on sg.goods_id = g.goods_id where " . $conditions ." and g.status = 1 ");
	        $all_goods = array();
	        foreach($goods_info as $k => $v)
			{
				$all_goods[$v['goods_id']] = $v; 
			}
        	//var_dump($all_goods) or die("");
        	foreach ($all_goods as $key=>$val) 
			{ 
				$all_goods[$key]['goods_name'] = urlencode(iconv('gb2312','utf-8',$val['goods_name'])); 
			} 
        	
        	$data['label'] = $label;
        	$data['all_goods'] = $all_goods;
        	
        	//var_dump($GLOBALS);
        	$data = json_encode($data);
        	echo $data;
        }
    }
    /**
     * 		清空挂件数据
     */
    public function clear_widget_base() {
    	$widget_name = empty($_GET['widget_name']) ? '' : $_GET['widget_name'];
    	$pics_num = empty($_GET['pics_num']) ? 0 : trim($_GET['pics_num']);	
	    $goods_num = empty($_GET['goods_num']) ? 0 : trim($_GET['goods_num']);
    	$widget_mod = & m('widget');
    	$widget_mod->drop('widget_name="'.$widget_name.'"'); //删除数据库信息
    	header("Location:index.php?app=widget&act=editWidgetData&widget_name=$widget_name&pics_num={$pics_num}&goods_num={$goods_num}");
    }

    /**
     *    清理垃圾文件
     *
     *    @author    Garbin
     *    @return    void
     */
    function clean_file()
    {
        $continue = isset($_GET['continue']);
        $isolcated_file = $this->_get_isolated_file();
        if (empty($isolcated_file))
        {
            $this->json_error('no_isocated_file');

            return;
        }
        $file_count = count($isolcated_file);
        if (!$continue)
        {
            $this->json_result('', sprintf(Lang::get('isolcated_file_count'), $file_count));

            return;
        }
        else
        {
            foreach ($isolcated_file as $f)
            {
                _at('unlink', ROOT_PATH . '/' . $f);
            }

            $this->json_result('', sprintf('clean_file_successed', $file_count));
        }
    }

    /**
     *    获取孤立的文件
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_isolated_file()
    {
        /* 获取存在的文件列表 */
        $exist_files    = $this->_get_exist_file();
        if (empty($exist_files))
        {
            return array();
        }
        /* 获取所有的选项值 */
        $option_values  = $this->_get_option_value();
        /* 无任何选项，则表示，所有文件都是孤立的，可以删除 */
        if (empty($option_values))
        {
            return $exist_files;
        }
        /* 逐个判断是否被使用 */
        foreach ($exist_files as $k => $f)
        {
            /* 若$f存在于选项中，则表示该文件正被使用，不能删除 */
            /* $options_values可以是二维数组，三维四维可能会有问题，因此，需要注意，所有的存储上传文件的option必须放在第一级数组中 */
            if($this->_check_use($f, $option_values))
            {
                unset($exist_files[$k]);
            }
        }
        return $exist_files;
    }

    /**
     *   检查挂件文件是否在使用
     *
     * @param  $f
     * @param array $option_values
     * @return true | 正在使用中，不能删除
     *         false | 没有使用，可以删除
     */
    function _check_use($f, $option_values)
    {
        if (in_array($f, $option_values, true))
        {
            return true;
        }
        foreach ($option_values as $key => $val)
        {
            if (is_array($val))
            {
                if (in_array($f, $val))
                {
                    return true;
                }
            }
        }
       return false;
    }

    function _get_exist_file()
    {
        $files = array();
        $file_dir = ROOT_PATH . '/data/files/mall/template';
        if (!is_dir($file_dir))
        {

            return $files;
        }
        $dir  = dir($file_dir);
        while (false !== ($item = $dir->read()))
        {
            if (in_array($item, array('.', '..', 'index.htm')) || $item{0} == '.')
            {
                continue;
            }
            $files[] = 'data/files/mall/template/' . $item;
        }

        return $files;
    }

    function _get_option_value()
    {
        $config_dir = ROOT_PATH . '/data/page_config';
        $dir  = dir($config_dir);
        $config_values = array();
        while (false !== ($item = $dir->read()))
        {
            if (in_array($item, array('.', '..', 'index.htm')) || $item{0} == '.')
            {
                continue;
            }
            $tmp = include($config_dir . '/' . $item);
            $config_values = array_merge($config_values, $this->_get_all_value($tmp));
        }

        return $config_values;
    }
    function _get_all_value($widgets)
    {
        $values = array();
        if (isset($widgets['widgets']))
        {
            foreach ($widgets['widgets'] as $widget)
            {
                if (is_array($widget['options']))
                {
                    $values = array_merge($values, array_values($widget['options']));
                }
            }
        }
        if (isset($widgets['tmp']))
        {
            foreach ($widgets['tmp'] as $widget)
            {
                if (is_array($widget['options']))
                {
                    $values = array_merge($values, array_values($widget['options']));
                }
            }
        }

        return $values;
    }

    function _get_file($name, $type = 'script')
    {
        $file = ROOT_PATH . '/external/widgets/' . $name;
        switch ($type)
        {
            case 'script':
                return $file . '/main.widget.php';
            break;
            case 'template':
                return $file . '/widget.html';
            break;
        }
    }
}

?>
