<?php

/**
 *    �ʾ�������
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
    *    �����ʾ & ����
    *
    *    @author   ���� 
    * 
    */
	function index() {
	    $question_classes_info = $this->_question_classes_mod->find();
	    $this->assign("question_classes" , $question_classes_info); // ���� 
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
				$this->show_warning("������������! λ�� question type!");
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
    	//��ȡ��ҳ��ʾ����

        //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->assign('page_info', $page);
        //var_dump($a);
        $this->display("question.index.html");
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
	
	function add() {
		date_default_timezone_set('Asia/Shanghai');
	 	if(!IS_POST) {
	 		//����sn ,  ��֤���ظ�����Ŀ�ʹ�ƥ��
	 		$sn = "QS" . time();
	    	//���� 
	    	$question_classes_info = $this->_question_classes_mod->find();
	    	$this->assign("sn",$sn);
	    	$this->assign("question_classes" , $question_classes_info);
	    	$this->display("question.form.html");
		} else {
			$option_num = intval($_POST['option_num']);
			//��Ŀ��Ϣ
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
    		$this->show_warning("δ֪����, �޷��ҵ�!");
    		return;
    	}
    	$old_option_ids = array();
   	 	//��ȡ��ѡ����Ϣ
    	$options_info = $this->_options_bank_mod->find(array('conditions' => " question_id=".$id));
    	foreach($options_info as $v) {
    		$old_option_ids[] = $v['option_id'];
    	}
   	 	if(!IS_POST) {
   	 		//���� 
	    	$question_classes_info = $this->_question_classes_mod->find();
	    	//��ȡ��Ŀ��Ϣ
	    	$question_info = $this->_question_bank_mod->get($id);
	    	if(!$question_info) {
	    		$this->show_warning("��Ŀ������!");
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
			//��Ŀ��Ϣ
			$question_info = array(
				'stem' => trim($_POST['question_content']),
				'class_id' => intval($_POST['class_id']),
				'question_type' => trim($_POST['question_type']),
				'option_num' => $option_num, 
				'create_time' => time(),
				'sn' => trim($_POST['sn']),
			);		
			$this->_question_bank_mod->edit($id,$question_info);

			//��ԭ�е�ִ���޸�
			
			if($old_option_num > 0) {	
				if($old_option_num > $option_num) { //����޸�ǰ�Ĵ������ڵ�.
					//ɾ�������¼
					for($i = $old_option_num - 1 ; $i > $option_num - 1 ; $i--) {
						$this->_options_bank_mod->drop($old_option_ids[$i]);
					}
					//�޸�ԭ�м�¼
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
				//����
				if($old_option_num < $option_num) { // ����µ�ѡ����Ŀ����ԭ�е�, �����޸�ԭ�е�, �������µ�
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
				//����
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
    * ɾ�����ܿ�����    ����ɾ��
    * @author   ����
    */
    function drop()
    {
        $ids = empty($_POST['id']) ? 0 : $_POST['id'];
        if($ids == 0) {
        	$this->show_warning("û��ѡ���κ���Ʒ��");
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
