<?php

/**
 *    问卷题库管理
 *
 *    @author   Bottle 
 *    @usage    none
 */
class QuestionApp extends BackendApp
{
	public function __construct() {
		parent::BackendApp();
		$this->_question_classes_mod = & m('questionclasses');
		$this->_question_bank_mod = & m('questionbank');
		$this->_options_bank_mod = & m('optionsbank');
	}
    /**
    *    题库显示 & 搜索
    *
    *    @author   贺瑾璞 
    * 
    */
	function index() {
	    $question_classes_info = $this->_question_classes_mod->find();
	    $this->assign("question_classes" , $question_classes_info); // 分类 
	    $conditions = ' 1 = 1';
	    $page_num = 10;
	    $question_type = empty($_GET['question_type']) ? 0 : intval($_GET['question_type']);
    	$class_name = empty($_GET['class_name']) ? 0 : intval($_GET['class_name']);
    	$stem = empty($_GET['stem']) ? '' : trim($_GET['stem']);
		switch($question_type) {
			case 1:
				 $conditions .= " AND question_type = 'basicinfo' ";
				 $this->assign('question_type',$question_type);
				 break;
			case 2: 
				$conditions .= " AND question_type = 'question' ";
				$this->assign('question_type',$question_type); 
				break;
			case 0: 
				$this->assign('question_type',0); ;break;
			default : 
				$this->show_warning("搜索条件出错! 位置 question type!");
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
        $this->_format_page($page);
        $this->assign('list',$list);
    	//获取分页显示条数

        //将分页信息传递给视图，用于形成分页条
        $this->assign('page_info', $page);
        //var_dump($a);
        $this->display("question.index.html");
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
	
	function add() {
		date_default_timezone_set('Asia/Shanghai');
	 	if(!IS_POST) {
	 		//生成sn ,  保证不重复让题目和答案匹配
	 		$sn = "QS" . time();
	    	//分类 
	    	$question_classes_info = $this->_question_classes_mod->find();
	    	$this->assign("sn",$sn);
	    	$this->assign("question_classes" , $question_classes_info);
	    	$this->display("question.form.html");
		} else {
			$option_num = intval($_POST['option_num']);
			//题目信息
			$question_info = array(
				'stem' => trim($_POST['question_content']),
				'class_id' => intval($_POST['class_id']),
				'question_type' => trim($_POST['question_type']),
				'option_num' => $option_num, 
				'create_time' => time(),
				'sn' => trim($_POST['sn']),
			);		
			$question_id = $this->_question_bank_mod->add($question_info);

			if($option_num > 0) {	
				for($i = 0 ; $i < $option_num ; $i++ ) {
					$arr = array(
						'option_tab' => trim($_POST['optionsn'.$i]),
						'option_content' => trim($_POST['options'.$i]),
						'question_id' => $question_id ,
						'sn' => trim($_POST['sn']),
					);
					$this->_options_bank_mod->add($arr);
				}
			}
			header("Location:index.php?app=question");
		}
	
		
   	 }
   	 public function edit() {
   	 	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	if($id == 0) {
    		$this->show_warning("未知索引, 无法找到!");
    		return;
    	}
    	$old_option_ids = array();
   	 	//获取和选项信息
    	$options_info = $this->_options_bank_mod->find(array('conditions' => " question_id=".$id));
    	foreach($options_info as $v) {
    		$old_option_ids[] = $v['option_id'];
    	}
   	 	if(!IS_POST) {
   	 		//分类 
	    	$question_classes_info = $this->_question_classes_mod->find();
	    	//获取题目信息
	    	$question_info = $this->_question_bank_mod->get($id);
	    	if(!$question_info) {
	    		$this->show_warning("题目不存在!");
	    		return;
	    	}
	    	
	    	$this->assign("sn",$question_info['sn']);
	    	$this->assign("options_num",$question_info['option_num']);
	    	$this->assign("question_info",$question_info);
	    	$this->assign("options_info",$options_info);
	    	$this->assign("question_classes" , $question_classes_info);
	    	$this->display("question.form.html");
		} else {
			$option_num = intval($_POST['option_num']);
			$old_option_num = intval($_POST['old_option_num']);
			//题目信息
			$question_info = array(
				'stem' => trim($_POST['question_content']),
				'class_id' => intval($_POST['class_id']),
				'question_type' => trim($_POST['question_type']),
				'option_num' => $option_num, 
				'create_time' => time(),
				'sn' => trim($_POST['sn']),
			);		
			$this->_question_bank_mod->edit($id,$question_info);

			//对原有的执行修改
			
			if($old_option_num > 0) {	
				if($old_option_num > $option_num) { //如果修改前的大于现在的.
					//删除多余记录
					for($i = $old_option_num - 1 ; $i > $option_num - 1 ; $i--) {
						$this->_options_bank_mod->drop($old_option_ids[$i]);
					}
					//修改原有记录
					for($i = 0 ; $i < $option_num ; $i++) {
						$arr = array(
							'option_tab' => trim($_POST['optionsn'.$i]),
							'option_content' => trim($_POST['options'.$i]),
							'question_id' => $id ,
							'sn' => trim($_POST['sn']),
						);
						$this->_options_bank_mod->edit($old_option_ids[$i],$arr);
					}
				}
				if($old_option_num == $option_num) {
					for($i = 0 ; $i < $old_option_num ; $i++ ) {
						$arr = array(
							'option_tab' => trim($_POST['optionsn'.$i]),
							'option_content' => trim($_POST['options'.$i]),
							'question_id' => $id ,
							'sn' => trim($_POST['sn']),
						);
						$this->_options_bank_mod->edit($old_option_ids[$i],$arr);
					}
				}
				//新增
				if($old_option_num < $option_num) { // 如果新的选项数目大于原有的, 则先修改原有的, 再增加新的
					for($i = 0 ; $i < $old_option_num ; $i++ ) {
						$arr = array(
							'option_tab' => trim($_POST['optionsn'.$i]),
							'option_content' => trim($_POST['options'.$i]),
							'question_id' => $id ,
							'sn' => trim($_POST['sn']),
						);
						$this->_options_bank_mod->edit($old_option_ids[$i],$arr);
					}
					$new_option_num = $old_option_num - $option_num;
					for($i = $old_option_num ; $i < $option_num ; $i++) {
						$arr = array(
							'option_tab' => trim($_POST['optionsn'.$i]),
							'option_content' => trim($_POST['options'.$i]),
							'question_id' => $id ,
							'sn' => trim($_POST['sn']),
						);
						$this->_options_bank_mod->add($arr);
					}
				}
			}
			if($old_option_num == 0) {
				//新增
				if($old_option_num < $option_num) {
					$new_option_num = $old_option_num - $option_num;
					for($i = $old_option_num ; $i < $option_num ; $i++) {
						$arr = array(
							'option_tab' => trim($_POST['optionsn'.$i]),
							'option_content' => trim($_POST['options'.$i]),
							'question_id' => $id ,
							'sn' => trim($_POST['sn']),
						);
						$this->_options_bank_mod->add($arr);
					}
				}
			}
			header("Location:index.php?app=question");
		}
   	 }
   /*
    * 删除功能控制器    批量删除
    * @author   贺瑾璞
    */
    function drop()
    {
        $ids = empty($_POST['id']) ? 0 : $_POST['id'];
        if($ids == 0) {
        	$this->show_warning("没有选中任何商品！");
        } 
        else if($ids != 0){
        	foreach ($ids as $k => $v){
        		$this->_question_bank_mod->drop($v);
        	}
        }
        header("Location:index.php?app=question&act=index");
    }
}
?>
