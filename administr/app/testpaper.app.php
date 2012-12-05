<?php

/**
 *    问卷试卷管理
 *
 *    @author    张壮
 *    @usage    none
 */
class TestpaperApp extends BackendApp
{
	var $_testpaper_info_mod;
	function __construct()
	{
		$this->TestpaperApp();
	}
	function TestpaperApp()
	{
		parent::BackendApp();
		$this->_testpaper_info_mod=& m('testpaperinfo');
		$this->_testpaper_mod = & m('testpaper');
		$this->_question_classes_mod = & m('questionclasses');
		$this->_question_bank_mod = & m('questionbank');
		$this->_options_bank_mod = & m('optionsbank');
	}

    /*问卷的显示*/
    function index()
    {
    	$page = $this->_get_page(10);//获取分页
		/*更新排序*/
		if (isset($_GET['sort']) && isset($_GET['order']))
		{
			$sort=strtolower(trim($_GET['sort']));
			$order=strtolower(trim($_GET['order']));
			if (!in_array($order, array('asc','desc')))
			{
			$sort='create_time';
			$order='desc';
			}
		}
		else
		{
			$sort='create_time';
			$order='desc';
		}
        //拼装sql，统计总数
        $count_sql = "select count(*) from pa_test_paper_info";
        $page['item_count'] = $this->_testpaper_info_mod->getOne($count_sql); //获取统计的数据
        //var_dump($page);
        $this->_format_page($page);//格式化分页信息
        //拼装sql，查询每页显示数据
        $sql="select * from pa_test_paper_info order by $sort $order limit $page[limit]";
        $test=$this->_testpaper_info_mod->getAll($sql);
        //dump($test);
        $this->assign('testpaper',$test);
        //$this->assign('orders',$page['item_count']);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->display("testpaper.index.html");
    }
    
    
    /*搜索*/
    function search()
    {

        	$page = $this->_get_page(10);//获取分页
		/*更新排序*/
		if (isset($_GET['sort']) && isset($_GET['order']))
		{
			$sort=strtolower(trim($_GET['sort']));
			$order=strtolower(trim($_GET['order']));
			if (!in_array($order, array('asc','desc')))
			{
			$sort='create_time';
			$order='desc';
			}
		}
		else
		{
			$sort='create_time';
			$order='desc';
		}
			//获取 查询条件
		    if (isset($_GET['test']))
		{
    		$test=$_GET['test'];
		   
			//拼装sql，统计总数
        	$count_sql = "select count(*) from pa_test_paper_info where tpi_title like '%$test%'";
        	$page['item_count'] = $this->_testpaper_info_mod->getOne($count_sql); //获取统计的数据
        	//var_dump($page);
        	$this->_format_page($page);//格式化分页信息
			$this->assign('page_info', $page);
    		$searchSQL="select * from pa_test_paper_info where tpi_title like '%$test%' order by $sort $order limit $page[limit]";
    		$search=$this->_testpaper_info_mod->getAll($searchSQL);
    		//如果没有搜索条件
    		if(empty($search))
    		{
    			$this->show_warning("没有你要搜索的试卷！请返回重新输入查询条件！");
    		}else 
    		{
    		$this->assign('testpaper',$search);
    		$this->display("testpaper.index.html"); 
    		}
		}
    		
    }
    /*添加*/
    function add()
    {
    	if(IS_POST){
    	
	    	if (isset($_POST['submit']))
	    	{
	    		$data = array(
	                'tpi_title'  => trim($_POST['tpi_title']),
	                'tpi_subhead'  => trim($_POST['tpi_subhead']),
	                'tpi_notice' => $_POST['tpi_notice'],
	    			'tpi_templates' => trim($_POST['tpi_templates']),
	    			'create_time' => strtotime('now'),
	    		);
	    		$tpi_id = $this->_testpaper_info_mod->add($data);
	    		
	    	}    	
    		$this->index();
    	}else 
    	{
    		$this->display("testpaper.form.html");
    	}
    }
    /*编辑*/
    function edit()
    {
    	//
    	$tpi_id = empty($_GET['tpi_id']) ? 0 : intval($_GET['tpi_id']);
    	$sql="select * from pa_test_paper_info where tpi_id=$tpi_id";
    	$test=$this->_testpaper_info_mod->getRow($sql);
    	$this->assign('testpaper',$test);
    	if (IS_POST){
    	if (isset($_POST['submit']))
    	{
    		$data = array(
               'tpi_title'  => trim($_POST['tpi_title']),
               'tpi_subhead'  => trim($_POST['tpi_subhead']),
               'tpi_notice' => $_POST['tpi_notice'],
    		   'tpi_templates' => trim($_POST['tpi_templates']),
    		   'create_time' => strtotime('now'),
    		);
    		$rows=$this->_testpaper_info_mod->edit($tpi_id,$data);
    		$this->index();
    	}
    	}else 
    	{
    		$this->display("testpaper.form.html");
    	}
    	
    }
    /*
    * 删除功能控制器    批量删除
    * @author   贺瑾璞
    */
    function drop()
    {
    	$ids = empty($_POST['id']) ? 0 : $_POST['id'];
    	//var_dump($ids);die();
    	if($ids == 0){
    		$this->show_warning("您尚未选中任何题目！");
    	}
    	else if($ids != 0){
	    	foreach ($ids as $k => $v){
	    		$this->_testpaper_info_mod->drop($v);
	    	}
	    	header("Location:index.php?app=testpaper&act=index");
    	}
    }
    /*试题管理*/
    function insert()
    {
    	
    }
    
    //选择题目页面, 会死出所有题目..   根据分类搜索..
    public function addToPaper() {
    	$id = empty($_GET['id']) ? 0 : intval($_GET['id']); //试卷ID
    	if($id == 0) {
    		$this->show_warning("获取试卷出错!  未知索引!");
    	}
    	$test_paper_info = $this->_testpaper_info_mod->get($id);
    	$question_classes_info = $this->_question_classes_mod->find();
	    $this->assign("question_classes" , $question_classes_info); // 分类 
	    $conditions = ' 1 = 1';
	    $page_num = 10;
    	
	    $question_type = empty($_GET['question_type']) ? 0 : intval($_GET['question_type']);
    	$class_name = empty($_GET['class_name']) ? 0 : intval($_GET['class_name']);
    	$stem = empty($_GET['stem']) ? '' : trim($_GET['stem']);
		switch($question_type) {
			case 1: $conditions .= " AND question_type = 'basicinfo' "; $this->assign('question_type',$question_type); break;
			case 2: $conditions .= " AND question_type = 'question' ";$this->assign('question_type',$question_type); break;
			case 0: $this->assign('question_type',0); ;break;
			default : $this->show_warning("搜索条件出错! 位置 question type!");
		}
		if($class_name != 0) {
			$conditions .= " AND class_id = " . $class_name;	
			$this->assign("class_id" , $class_name);
		}
		if($stem != '') {
			$conditions .= " AND stem like '%" . $stem . "%' ";
			$this->assign("stem" , $stem);
		}
 
    	$page = $this->_get_page($page_num);
    	$page['item_count'] = $this->getQuestion($conditions,$page,1);
    	$list = $this->getQuestion($conditions,$page);
    	$testpapers = $this->_testpaper_mod->find(array('conditions' => " tpi_id=" . $id));
    	$question_ids = array();
    	foreach($testpapers as $k => $v) {
    		$question_ids[] = $v['question_id'];
    	}
    	foreach($list as $_k => $_v) {
    		if(!in_array($_v['question_id'],$question_ids,true)) {
    			$list[$_k]['is_have'] = false;
    		} else {
    			$list[$_k]['is_have'] = true;
    		}
    	}

        $this->_format_page($page);
        $this->assign('list',$list);
    	//获取分页显示条数
		$this->assign("SITE_URL",SITE_URL);
        //将分页信息传递给视图，用于形成分页条
        $this->assign('page_info', $page);
        $this->assign("test_paper_info",$test_paper_info);
        $this->display("testpaper.select.html");
    }
    
    public function handle() {
    	$question_id = empty($_POST['qid']) ? 0 : intval($_POST['qid']); //题ID
    	if($question_id == 0) {
    		$this->json_error("unknow_qid");
    		return;
    	}
    	$flag = empty($_POST['fid']) ? 0 : intval($_POST['fid']); //条件 , 添加还是删除 
    	if($flag == 0) {
    		$this->json_error('unknow_fid');
    		return;
    	}
    	$tid = empty($_POST['tid']) ? 0 :intval($_POST['tid']); //试卷ID
    	if($tid == 0) {
    		$this->json_error('unknow_tid');
    		return;
       	}
       	$is_trigger_question = empty($_POST['is_trigger']) ? 0 : intval($_POST['is_trigger']);
       	if($is_trigger_question == 0 && $is_trigger_question != 1 && $is_trigger_question != -1) {
       		$this->json_error('unknow_is_trigger');
       		return;
       	}
    	$parent_id = empty($_POST['parent']) ? 0 : intval($_POST['parent']); //父ID
       	if($is_trigger_question == 1 && $parent_id == 0) {
       		$this->json_error('unknow_parent_id');
       		return;
       	}
       	$parent_option_id = empty($_POST['parent_opt']) ? 0 : intval($_POST['parent_opt']);
       	if($is_trigger_question == 1 && $parent_option_id == 0) {
       		$this->json_error('unknow_parent_option_id');
       		return;
       	}
       	$tp_id = $this->checkData($tid,$question_id);
    	switch($flag) {
    		case 1: //添加
    			if(!$tp_id) { //如果试卷中没有试题,就添加
    				$tp = $this->addData($tid,$question_id,$is_trigger_question,$parent_id ,$parent_option_id);
    				if(!$tp) {
    					$this->json_error('add_failure');
    					return;
    				}
    				$this->json_result("add_success");
    			} else { //如果试卷中已有试题, 回复一个warning, 不做任何操作
    				$this->json_error("have_question"); //试卷中已有本题. 未执行添加 
    			}
    			break;
    		case -1: //删除
    			if(!$tp_id) { // 如果试卷中没有试题, 回复一个warning , 不做其它操作
    				$this->json_error("not_have_question"); //试卷中不含本题. 未执行删除 
    			} else { //如果试卷中已有试题, 执行删除
    				$tp = $this->delData($tp_id);
    				if(!$tp) {
    					$this->json_error('drop_failure');
    					return;
    				}
    				$this->json_result("drop_success");
    			}
    			break;
    		default: $this->json_error('fid_error'); return;
    	}
    	//$this->json_result("unknow_index");
    }
    
    private function checkData($tid,$id) {
    	$testPaper_info = $this->_testpaper_mod->get(array('conditions' => ' tpi_id = ' . $tid . ' AND question_id = ' . $id));
    	if(!$testPaper_info) {
    		return false; // 当前试卷没有本试题
    	} else { 
    		return $testPaper_info['tp_id']; // 当前试卷已有本试题(返回主键)
    	}
    }
    
    private function addData($tid,$question_id,$is_trigger_question,$parent_id ,$parent_option_id) {
    	$param = array();
    	if($is_trigger_question == 1) {
	    	$param = array(
	    		'tpi_id' => $tid,
	    		'question_id' => $question_id,
	    		'parent_id'	=> $parent_id,
	    		'parent_option_id' => $parent_option_id,
	    		'is_trigger_question' => $is_trigger_question,
	    		'create_time' => time(),
	    	);
    	} else {
    		$param = array(
	    		'tpi_id' => $tid,
	    		'question_id' => $question_id,
	    		'parent_id'	=> 0,
	    		'parent_option_id' => 0,
	    		'is_trigger_question' => 0,
	    		'create_time' => time(),
	    	);
    	}
    	$tp = $this->_testpaper_mod->add($param);
    	return $tp;
    }
    
    private function delData($id) {
    	$tp = $this->_testpaper_mod->drop($id);
    	return $tp;
    }
    
    public function getOptions() {
    	$question_id = empty($_GET['qid']) ? 0 : intval($_GET['qid']);
    	if($question_id == 0) {
    		$this->json_error("unknow_qid");
    		return;
    	}
    	$options = $this->_options_bank_mod->find(array('conditions' => " question_id = " . $question_id));
    	if(!$options) {
    		$this->json_error("get_null_options");
    		return;
    	}
    	$this->json_result($options);
    }
    
	public function getQuestion($conditions , $page , $is_count = 0) {
		if($is_count == 0) { // 不统计数目,也就是直接返回记录
			$list = $this->_question_bank_mod->find(array(
				'conditions' => $conditions,
				'limit' => $page['limit'],
				'join'	=> 'belongs_to_questionclasses',
				'order'	=> 'question_id'
			));
			return $list;
		} else {
			$count = $this->_question_bank_mod->getOne("select count(*) from pa_question_bank qb left join pa_question_classes qc on qb.class_id = qc.class_id  where ".$conditions);
			return $count;
		}
	}
    

   
}
?>
