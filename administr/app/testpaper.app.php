<?php

/**
 *    �ʾ��Ծ����
 *
 *    @author    ��׳
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

    /*�ʾ����ʾ*/
    function index()
    {
    	$page = $this->_get_page(10);//��ȡ��ҳ
		/*��������*/
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
        //ƴװsql��ͳ������
        $count_sql = "select count(*) from pa_test_paper_info";
        $page['item_count'] = $this->_testpaper_info_mod->getOne($count_sql); //��ȡͳ�Ƶ�����
        //var_dump($page);
        $this->_format_page($page);//��ʽ����ҳ��Ϣ
        //ƴװsql����ѯÿҳ��ʾ����
        $sql="select * from pa_test_paper_info order by $sort $order limit $page[limit]";
        $test=$this->_testpaper_info_mod->getAll($sql);
        //dump($test);
        $this->assign('testpaper',$test);
        //$this->assign('orders',$page['item_count']);
        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->display("testpaper.index.html");
    }
    
    
    /*����*/
    function search()
    {

        	$page = $this->_get_page(10);//��ȡ��ҳ
		/*��������*/
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
			//��ȡ ��ѯ����
		    if (isset($_GET['test']))
		{
    		$test=$_GET['test'];
		   
			//ƴװsql��ͳ������
        	$count_sql = "select count(*) from pa_test_paper_info where tpi_title like '%$test%'";
        	$page['item_count'] = $this->_testpaper_info_mod->getOne($count_sql); //��ȡͳ�Ƶ�����
        	//var_dump($page);
        	$this->_format_page($page);//��ʽ����ҳ��Ϣ
			$this->assign('page_info', $page);
    		$searchSQL="select * from pa_test_paper_info where tpi_title like '%$test%' order by $sort $order limit $page[limit]";
    		$search=$this->_testpaper_info_mod->getAll($searchSQL);
    		//���û����������
    		if(empty($search))
    		{
    			$this->show_warning("û����Ҫ�������Ծ��뷵�����������ѯ������");
    		}else 
    		{
    		$this->assign('testpaper',$search);
    		$this->display("testpaper.index.html"); 
    		}
		}
    		
    }
    /*���*/
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
    /*�༭*/
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
    * ɾ�����ܿ�����    ����ɾ��
    * @author   ����
    */
    function drop()
    {
    	$ids = empty($_POST['id']) ? 0 : $_POST['id'];
    	//var_dump($ids);die();
    	if($ids == 0){
    		$this->show_warning("����δѡ���κ���Ŀ��");
    	}
    	else if($ids != 0){
	    	foreach ($ids as $k => $v){
	    		$this->_testpaper_info_mod->drop($v);
	    	}
	    	header("Location:index.php?app=testpaper&act=index");
    	}
    }
    /*�������*/
    function insert()
    {
    	
    }
    
    //ѡ����Ŀҳ��, ������������Ŀ..   ���ݷ�������..
    public function addToPaper() {
    	$id = empty($_GET['id']) ? 0 : intval($_GET['id']); //�Ծ�ID
    	if($id == 0) {
    		$this->show_warning("��ȡ�Ծ����!  δ֪����!");
    	}
    	$test_paper_info = $this->_testpaper_info_mod->get($id);
    	$question_classes_info = $this->_question_classes_mod->find();
	    $this->assign("question_classes" , $question_classes_info); // ���� 
	    $conditions = ' 1 = 1';
	    $page_num = 10;
    	
	    $question_type = empty($_GET['question_type']) ? 0 : intval($_GET['question_type']);
    	$class_name = empty($_GET['class_name']) ? 0 : intval($_GET['class_name']);
    	$stem = empty($_GET['stem']) ? '' : trim($_GET['stem']);
		switch($question_type) {
			case 1: $conditions .= " AND question_type = 'basicinfo' "; $this->assign('question_type',$question_type); break;
			case 2: $conditions .= " AND question_type = 'question' ";$this->assign('question_type',$question_type); break;
			case 0: $this->assign('question_type',0); ;break;
			default : $this->show_warning("������������! λ�� question type!");
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
    	//��ȡ��ҳ��ʾ����
		$this->assign("SITE_URL",SITE_URL);
        //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->assign('page_info', $page);
        $this->assign("test_paper_info",$test_paper_info);
        $this->display("testpaper.select.html");
    }
    
    public function handle() {
    	$question_id = empty($_POST['qid']) ? 0 : intval($_POST['qid']); //��ID
    	if($question_id == 0) {
    		$this->json_error("unknow_qid");
    		return;
    	}
    	$flag = empty($_POST['fid']) ? 0 : intval($_POST['fid']); //���� , ��ӻ���ɾ�� 
    	if($flag == 0) {
    		$this->json_error('unknow_fid');
    		return;
    	}
    	$tid = empty($_POST['tid']) ? 0 :intval($_POST['tid']); //�Ծ�ID
    	if($tid == 0) {
    		$this->json_error('unknow_tid');
    		return;
       	}
       	$is_trigger_question = empty($_POST['is_trigger']) ? 0 : intval($_POST['is_trigger']);
       	if($is_trigger_question == 0 && $is_trigger_question != 1 && $is_trigger_question != -1) {
       		$this->json_error('unknow_is_trigger');
       		return;
       	}
    	$parent_id = empty($_POST['parent']) ? 0 : intval($_POST['parent']); //��ID
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
    		case 1: //���
    			if(!$tp_id) { //����Ծ���û������,�����
    				$tp = $this->addData($tid,$question_id,$is_trigger_question,$parent_id ,$parent_option_id);
    				if(!$tp) {
    					$this->json_error('add_failure');
    					return;
    				}
    				$this->json_result("add_success");
    			} else { //����Ծ�����������, �ظ�һ��warning, �����κβ���
    				$this->json_error("have_question"); //�Ծ������б���. δִ����� 
    			}
    			break;
    		case -1: //ɾ��
    			if(!$tp_id) { // ����Ծ���û������, �ظ�һ��warning , ������������
    				$this->json_error("not_have_question"); //�Ծ��в�������. δִ��ɾ�� 
    			} else { //����Ծ�����������, ִ��ɾ��
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
    		return false; // ��ǰ�Ծ�û�б�����
    	} else { 
    		return $testPaper_info['tp_id']; // ��ǰ�Ծ����б�����(��������)
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
		if($is_count == 0) { // ��ͳ����Ŀ,Ҳ����ֱ�ӷ��ؼ�¼
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
