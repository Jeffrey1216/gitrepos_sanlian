<?php

/**
 * 
 * 试卷生成类
 * @author bottle
 *
 */
class TestPaper {
	
	private $_testpaper_info_mod;
	private $_testpaper_mod;
	private $_question_classes_mod;
	private $_question_bank_mod;
	private $_options_bank_mod;
	private $_useranswer_mod;
	private $_paper_id;
	
	public function __construct() {
		$this->_testpaper_info_mod=& m('testpaperinfo');
		$this->_testpaper_mod = & m('testpaper');
		$this->_question_classes_mod = & m('questionclasses');
		$this->_question_bank_mod = & m('questionbank');
		$this->_options_bank_mod = & m('optionsbank');
		$this->_useranswer_mod = & m('useranswer');
	}
	
	public function index($id) { //主方法, 处理数组
		if(!$id) {
			return;
		}
		$this->_paper_id = $id;
		$paper_info = $this->_testpaper_info_mod->get($this->_paper_id);
		$paper_info['basic_question_info'] = $this->getBasicInfoQuestion($paper_info);
		$paper_info['paper_question_info'] = $this->getPaperInfoQuestion($paper_info);
		
		return $paper_info;
	}
	
	private function getBasicInfoQuestion($paper_info) { //获取基本信息题
		$tpi_id = intval($paper_info['tpi_id']);
		$basicInfoQuestions = $this->_testpaper_mod->getAll("select * from pa_test_paper tp 
		left join pa_question_bank qb on tp.question_id=qb.question_id where 
		tp.tpi_id = " . $tpi_id . " AND qb.question_type='basicinfo'");
		foreach($basicInfoQuestions as $_k => $_v) {
			$basicInfoQuestions[$_k]['options'] = $this->_options_bank_mod->find(array('conditions' => ' question_id = ' . $_v['question_id']));
		}
		return $basicInfoQuestions;
	}
	
	private function getPaperInfoQuestion($paper_info) { //获取问卷题信息
		$tpi_id = intval($paper_info['tpi_id']);
		$paperInfoQuestions = $this->_testpaper_mod->getAll("select * from pa_test_paper tp 
		left join pa_question_bank qb on tp.question_id=qb.question_id where 
		tp.tpi_id = " . $tpi_id . " AND qb.question_type='question'");
		foreach($paperInfoQuestions as $_k => $_v) {
			$paperInfoQuestions[$_k]['options'] = $this->_options_bank_mod->find(array('conditions' => ' question_id = ' . $_v['question_id']));
		}
		return $paperInfoQuestions;
	}
}

?>