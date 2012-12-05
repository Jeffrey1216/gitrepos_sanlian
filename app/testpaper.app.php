<?php
//�������õ��Ծ�ID.. ��ʱʹ��
define('PAPER_NUM', 4);

/* �ʾ��������� */
class TestpaperApp extends MallbaseApp {
	
	public function __construct() {
		$this->TestpaperApp();
	}
	
	public function TestpaperApp() {
		$this->_testpaper_info_mod=& m('testpaperinfo');
		$this->_testpaper_mod = & m('testpaper');
		$this->_question_classes_mod = & m('questionclasses');
		$this->_question_bank_mod = & m('questionbank');
		$this->_options_bank_mod = & m('optionsbank');
		$this->_useranswer_mod = & m('useranswer');
		$this->_activity_awardnum_mod = &m('activityawardnum'); //ʵ������Ա�齱�����
		$this->_activity_awardcount_mod = &m('activityawardcount'); //ʵ������齱ͳ�Ʊ�
		parent::__construct(); 	
	}
	
	public function index() {
		$this->assign("SITE_URL",SITE_URL);
		$user_id = $this->visitor->get("user_id");
		$activity_awardnum_info = $this->_activity_awardnum_mod->get(array('conditions' => ' uid='.$user_id));
		if($activity_awardnum_info['question'] == 1) {
			$this->show_message("���Ѿ�������ʾ����, �����ٴβ���,лл...",'javascript:history.back();');
			//header("Location:index.php?app=topics&act=greatsale");
		} 
		$testPaperInfo = $this->_testpaper_info_mod->get(PAPER_NUM);
		if(!IS_POST) {
			import("TestPaper.class");
			$testPaper = new TestPaper();
			$paper_info = $testPaper->index(PAPER_NUM);

			$regions = $this->_get_regions();
			$this->assign("regions",$regions);
			$this->assign("paper",$paper_info);
			$this->display($testPaperInfo['tpi_templates']);
		} else {
			$tpi_id = empty($_POST['tpi_id']) ? 0 : intval($_POST['tpi_id']);
			if($tpi_id == 0) {
				$this->show_warning('δ��ȡ���Ծ���Ϣ!  ������!');
			}
			$is_true = true;
			
			$post_info = $_POST;
			$dataArr = array();
			foreach($post_info as $k => $v) {
				if($k != 'tpi_id') {
					//�ָ� $k
					$k_arr = explode('_', $k);
					//ȡ����Ϣ
					$question_info = $this->_question_bank_mod->get(array('conditions' => " sn = '". $k_arr[1] . "'"));
					var_dump($question_info);
					switch($k_arr[0]) { //�����ͻ���
						case 'c' :  //ѡ����
							//ȡѡ����Ϣ
							if(is_array($v)) {
								foreach($v as $_k => $_v) {
									$option_info = $this->_options_bank_mod->get(array('conditions' => " option_tab = '" . $_v . "'" ));
									$v_arr = explode('_', $_v);
									$answer = '';
									if(array_key_exists('o_' . $v_arr[1] . '_' . $v_arr[2] , $post_info)) {
										$answer = $post_info['o_' . $v_arr[1] . '_' . $v_arr[2]];
									}
									$param = array(
										'user_id'	=> $user_id,
										'question_type' => $question_info['class_id'],
										'question_id'	=> $question_info['question_id'],
										'tpi_id'	=>	$tpi_id,
										'option_id'	=>	$option_info['option_id'],
										'answer'	=> $answer,
										'region_id'	=> 0,
										'sn'	=>	$k_arr[1],
									);
									$flag = false;
									foreach($dataArr as $value) {
										if($option_info['option_id'] == $value['option_id']) { //option_id ��ҳ����Ψһ��
											$flag = true;
										}
									}
									if(!$flag) {
										$dataArr[] = $param;
									}
								}
							} else {
								$option_info = $this->_options_bank_mod->get(array('conditions' => " option_tab = '" . $v . "'" ));
								$answer = '';
								if(array_key_exists('o_' . $v_arr[1] . '_' . $v_arr[2] , $post_info)) {
									$answer = $post_info['o_' . $v_arr[1] . '_' . $v_arr[2]];
								}
								$param = array(
									'user_id'	=> $user_id,
									'question_type' => $question_info['class_id'],
									'question_id'	=> $question_info['question_id'],
									'tpi_id'	=>	$tpi_id,
									'option_id'	=>	$option_info['option_id'],
									'answer'	=> $answer,
									'region_id'	=> 0,
									'sn'	=>	$k_arr[1],
								);
								$flag = false;
								foreach($dataArr as $value) {
									if($option_info['option_id'] == $value['option_id']) { //option_id ��ҳ����Ψһ��
										$flag = true;
									}
								}
								if(!$flag) {
									$dataArr[] = $param;
								}
							}
							;break;
						case 'o' :  //ѡ������� 
							//�����Ѿ������.  ���ﲻ������
							;break;
						case 'q' :  //�������
							$param = array(
								'user_id'	=> $user_id,
								'question_type' => $question_info['class_id'],
								'question_id'	=> $question_info['question_id'],
								'tpi_id'	=>	$tpi_id,
								'option_id'	=>	0,
								'answer'	=> $v,
								'region_id'	=> 0,
								'sn'	=>	$k_arr[1],
							);
							$dataArr[] = $param;
							break;
						case 'a' :  //������
							$param = array(
								'user_id'	=> $user_id,
								'question_type' => $question_info['class_id'],
								'question_id'	=> $question_info['question_id'],
								'tpi_id'	=>	$tpi_id,
								'option_id'	=>	0,
								'answer'	=> '',
								'region_id'	=> $v,
								'sn'	=>	$k_arr[1],
							);
							$dataArr[] = $param;
							break; 
						default:
							$this->show_warning("ȡֵ����!..");
					}
				}
			}
			foreach($dataArr as $val) {
				$id = $this->add($val);
				if(!$id) {
					$is_true = false;
					$this->show_warning("�������ݳ���, ��������ʧ��!");
				}
			}
			if($is_true) { //ȫ���ɹ�
				$this->_activity_awardnum_mod->edit($activity_awardnum_info['id'],array('num' => intval($activity_awardnum_info['num']  + 1),'question'=>1));
				$infos['uid']     = $activity_awardnum_info['uid'];
             	$infos['username'] = $activity_awardnum_info['username'];
             	$infos['mobile']   = $activity_awardnum_info['mobile'];
             	$infos['num'] = 1;
             	$infos['action'] = 'question';
             	$infos['type'] = 'get';
             	$infos['add_time'] = time();
             	$infos['act_id'] = PAISONG;
             	$infos['remark'] = '�����������ʾ�������һ�δ����ͻ�齱����';
	         	$this->_activity_awardcount_mod->add($infos);
			}
			header("Location:index.php?app=topics&act=greatsale");
		}
	}
	
	/**
     *    ��ȡһ������
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_regions()
    {
        $model_region =& m('region');
        $regions = $model_region->get_list(0);
        if ($regions)
        {
            $tmp  = array();
            foreach ($regions as $key => $value)
            {
                $tmp[$key] = $value['region_name'];
            }
            $regions = $tmp;
        }

        return $regions;
    }
	
	public function add($param) {
		return $this->_useranswer_mod->add($param);
	}
	
	function _run_action()
    {
        /* ֻ�е�¼���û��ſɷ��� */
        if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user')))
        {

        	header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

            return;
        }

        parent::_run_action();
    }
}