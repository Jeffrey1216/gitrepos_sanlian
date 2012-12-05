<?php
date_default_timezone_set('Asia/Shanghai');
define('PAGE_NUM',20); //�����ҳ��ʾ��
class GroupprojectApp extends BackendApp {
	
	public function __construct() {
		$this->GroupprojectApp();
	}
	
	public function GroupprojectApp() {
		parent::__construct();
		$this->_group_project_mod = & m('groupproject'); //��Ŀ
		$this->_group_category_mod = & m('groupcategory'); //��Ŀ����
		$this->_group_gcategory_mod = & m('groupgcategory'); //��Ʒ���� 
		$this->_group_specname_mod = & m('groupspecname'); //�����
		$this->_group_spec_mod = & m('groupspec'); //���ֵ
		$this->_store_mod = & m('store');//�̻�
		$this->_group_project_specname_mod = & m('groupprojectspecname');
		$this->_group_project_spec_mod = & m('groupprojectspec');
	}
	
	public function index() {
		//��Ŀ����
		$category_list = $this->_group_category_mod->getAll("select * from pa_group_category");
		$conditions = ' 1 = 1';
		$time = time();//��ǰʱ��
		$page = $this->_get_page(PAGE_NUM);
		$status = empty($_GET['status']) ? 0 : intval($_GET['status']); //�Ź�״̬ , �� ȫ��Ϊ0, δ��ʼΪ1 ,���ڽ���Ϊ2, �ѽ���Ϊ3
		$project_classify = empty($_GET['project_classify']) ? 0 : intval($_GET['project_classify']);
		switch($status) {
			case 0:$this->assign('status',$status);break;
			case 1: $conditions .= " AND gp.start_time > " . $time;$this->assign('status',$status);break;
			case 2: $conditions .= " AND gp.start_time <= " . $time . " AND gp.finish_time >= " . $time;$this->assign('status',$status);break;
			case 3: $comditions .= " AND gp.finish_time < " . $time;$this->assign('status',$status);break;
			default: $this->show_warning('�������:����״̬δ֪!');
			return;
		}
		if($project_classify != 0) {
			$conditions .= " AND gc.id = " . $project_classify;
			$this->assign('project_classify',$project_classify);
		}
		$count = $this->_group_project_mod->getOne("select count(*) from pa_group_project gp 
		right join pa_group_category gc on gp.category_id = gc.id left join pa_group_gcategory gg on 
		gp.gcategory_id = gg.id where " . $conditions);
		$page['item_count'] = $count;
		$group_porject_list = $this->_group_project_mod->getAll("select gp.id,gp.title,gc.category,gp.cprice,
		gp.price,gp.min_quantity,gp.start_time,gp.virtual_buy,gp.finish_time,gp.sale,gg.category as gcategory from pa_group_project gp 
		left join pa_group_category gc on gp.category_id = gc.id left join pa_group_gcategory gg on 
		gp.gcategory_id = gg.id where " . $conditions . " ORDER BY gp.id" . " limit " . $page['limit']);
		$this->_format_page($page);
		foreach($group_porject_list as $k => $v) {
			$group_porject_list[$k]['start_time'] = date("Y-m-d H:i",$v['start_time']);
			$group_porject_list[$k]['finish_time'] = date("Y-m-d H:i",$v['finish_time']);
		}
		$this->assign('category_list',$category_list);
		$this->assign('page_info',$page);
		$this->assign("group_project_list",$group_porject_list);
		$this->display('groupproject.list.html');
	}
	
	public function add() {
		$this->assign("PAILASTORE",PAILASTORE);
		$this->assign("PAILAMALL",PAILAMALL);
		$this->assign("SITE_URL",site_url());
		if(!IS_POST) {
			$store_list = $this->_store_mod->find();
			$category_list = $this->_group_category_mod->find();
			$gcategory_list = $this->_group_gcategory_mod->find(array('conditions' => ' parent_id = 0'));
			$specname_list = $this->_group_specname_mod->find();
			$this->assign('specname_list',$specname_list);
			$this->assign('store_list',$store_list);
			$this->assign('category_list',$category_list);
			$this->assign('gcategory_list',$gcategory_list);
			$this->display('groupproject.add.html');
		} else {
			$store_id = intval($_POST['store_id']);
			$store_mod = & m('store');
			$store_info = $store_mod->get($store_id);
			//��ͨ��Ϣ
			$time = time();//��ǰʱ��,���ʱ��
			$param = array(
				'category_id' => trim($_POST['project_classify']),
				'gcategory_id' => trim($_POST['goods_classify']),
				'title'	=> trim($_POST['title']),
				'cprice' => number_format(floatval($_POST['cprice']),2,'.',''),
				'price' => number_format(floatval($_POST['price']),2,'.',''),
				'virtual_buy' => intval($_POST['virtual_buy']),
				'min_quantity' => intval($_POST['min_quantity']),
				'max_quantity' => intval($_POST['max_quantity']),
				'astrict_num' => intval($_POST['astrict_num']),
				'min_buy_num' => intval($_POST['min_buy_num']),
				'allows_refund' => intval($_POST['allows_refund']), //�Ƿ������˿�
				'introduction' => trim($_POST['introduction']), //�������
				'warm_prompt' => trim($_POST['warm_prompt']), //��ܰ��ʾ
				'sort' => intval($_POST['sort']), //����
				'store_id' => $store_id, //����ID
				'store_name' => $store_info['store_name'],
				'goods_name' => trim($_POST['goods_name']), //��Ʒ��
				'goods_desc' => trim($_POST['goods_desc']), //��Ʒ��ϸ 
				'promotion' => trim($_POST['promotion']), //�ƹ��
				'add_time' => trim($time),
				'sale' => 0,
			);
			//��ʼʱ��ת��Ϊʱ���
			if(!empty($_POST['start_time'])) {
				$start_arr = explode(' ', trim($_POST['start_time']),2);
				$start_date_arr = explode('-', $start_arr[0],3);
				$start_time_arr = explode(':',$start_arr[1],3);
				//��ʼʱ��
				$param['start_time'] = mktime(intval($start_time_arr[0]),intval($start_time_arr[1]),intval($start_time_arr[2]),intval($start_date_arr[1]),intval($start_date_arr[2]),intval($start_date_arr[0]));
			} else {
				$this->show_warning('��ʼʱ��δ��д!');
				return;
			}
			//����ʱ��ת��Ϊʱ���
			if(!empty($_POST['finish_time'])) {
				$finish_arr = explode(' ', trim($_POST['finish_time']),2);
				$finish_date_arr = explode('-', $finish_arr[0],3);
				$finish_time_arr = explode(':',$finish_arr[1],3);
				//����ʱ��
				$param['finish_time'] = mktime(intval($finish_time_arr[0]),intval($finish_time_arr[1]),intval($finish_time_arr[2]),intval($finish_date_arr[1]),intval($finish_date_arr[2]),intval($finish_date_arr[0]));
			} else {
				$this->show_warning('����ʱ��δ��д!');
				return;
			}
			
			/**
			 *  �ϴ�ͼƬ����
			 *  ͼƬ��ַ /data/files/mall/group/$time/	
			 *  ���� $time + ͼƬ���� + ͼƬ��׺
			 **/
			//�б���ʾͼ
			if(!empty($_FILES['image_list']['name'])) {
				$image_list_end =  strrchr(trim($_FILES['image_list']['name']),'.');
				if($_FILES['image_list']['tmp_name'] != '') {
					$image_list = '/data/files/mall/group/'.$time.'/'.$time.'image_list'.$image_list_end;
					if(!file_exists(ROOT_PATH.'/data/files/mall/group/'.$time.'/')) {
						mkdir(ROOT_PATH.'/data/files/mall/group/'.$time.'/',0777,true);
					}
	        		move_uploaded_file(trim($_FILES['image_list']['tmp_name']),ROOT_PATH.'/'.$image_list);
	        		$param['image_list'] = $image_list;
				}
			}
			//��Ʒ��ϸҳͼ
			if(!empty($_FILES['image_show']['name'])) {
				$image_show_end =  strrchr(trim($_FILES['image_show']['name']),'.');
				if($_FILES['image_show']['tmp_name'] != '') {
					$image_show = '/data/files/mall/group/'.$time.'/'.$time.'image_show'.$image_show_end;
					if(!file_exists(ROOT_PATH.'/data/files/mall/group/'.$time.'/')) {
						mkdir(ROOT_PATH.'/data/files/mall/group/'.$time.'/',0777,true);
					}
	        		move_uploaded_file(trim($_FILES['image_show']['tmp_name']),ROOT_PATH.'/'.$image_show);
	        		$param['image_show'] = $image_show;
				}
			}
			//�����Ź���ʾ
			if(!empty($_FILES['image_other']['name'])) {
				$image_other_end =  strrchr(trim($_FILES['image_other']['name']),'.');
				if($_FILES['image_other']['tmp_name'] != '') {
					$image_other = '/data/files/mall/group/'.$time.'/'.$time.'image_other'.$image_other_end;
					if(!file_exists(ROOT_PATH.'/data/files/mall/group/'.$time.'/')) {
						mkdir(ROOT_PATH.'/data/files/mall/group/'.$time.'/',0777,true);
					}
	        		move_uploaded_file(trim($_FILES['image_other']['tmp_name']),ROOT_PATH.'/'.$image_other);
	        		$param['image_other'] = $image_other;
				}
			}
			
			$group_project_id = $this->_group_project_mod->add($param);
			if(!$group_project_id) {
				$this->show_warning('δ֪�Ĵ���:�������ʧ��!');
				return;
			}
			//���
			if(is_array($_POST['specname'])) { //�����
				foreach($_POST['specname'] as $k => $v) {
					$this->_group_project_specname_mod->add(array('project_id' => $group_project_id,'specname_id' => $v));
				}
			}
			if(is_array($_POST['spec_value'])) { //���ֵ
				foreach($_POST['spec_value'] as $k => $v) {
					$this->_group_project_spec_mod->add(array('project_id' => $group_project_id,'spec_id' => $v));
				}
			}
			$this->show_message('����ɹ�!','��Ŀ����','index.php?app=groupproject&act=index');
		}
	}
	
	public function edit() {
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		$this->assign("SITE_URL",site_url());
		$project = $this->_group_project_mod->get($id);
		$time = $project['add_time'];
		if($id == 0) {
			$this->show_warning('����: δ֪����Ŀ!.');
			return;
		}
		$group_project_specname_list = $this->_group_project_specname_mod->getAll("select * from pa_group_projectspecname where project_id = " . $id);
		$group_project_spec_list = $this->_group_project_spec_mod->getAll("select * from pa_group_projectspec where project_id = " . $id);	
		if(!IS_POST) {
			$store_list = $this->_store_mod->find();
			$category_list = $this->_group_category_mod->find();
			$gcategory_list = $this->_group_gcategory_mod->find(array('conditions' => ' parent_id = 0'));
			$specname_list = $this->_group_specname_mod->find();
			
			$specs_sel = $this->_group_spec_mod->getAll("select * from pa_group_projectspec where project_id = " . $project['id']);
			foreach($group_project_specname_list as $k => $v) {
				//var_dump($v);
				$specs_all = $this->_group_spec_mod->getAll("select * from pa_group_spec where specname_id = " . $v['specname_id']);
				foreach($specs_all as $_k => $_v) {
					$specs_all[$_k]['have'] = 0;
					foreach($specs_sel as $spec_sel) {
						if($_v['id'] == $spec_sel['spec_id']) {
							$specs_all[$_k]['have'] = 1;
						}
					}
					$group_project_specname_list[$k]['spec'] = $specs_all;
				}
			}
			
			$project['image_list'] = 'http://192.168.0.31/paila'.$project['image_list'];
			$project['image_show'] = 'http://192.168.0.31/paila'.$project['image_show'];
			$project['image_other'] = 'http://192.168.0.31/paila'.$project['image_other'];
			
			$project['start_time'] = date("Y-m-d H:i:s",$project['start_time']);		
			$project['finish_time'] = date("Y-m-d H:i:s",$project['finish_time']);	
			$this->assign('specname_list',$specname_list);
			$this->assign('store_list',$store_list);
			$this->assign('category_list',$category_list);
			$this->assign('gcategory_list',$gcategory_list);
			$this->assign('project',$project);	
			$this->assign('group_project_specname_list',$group_project_specname_list);
			$this->assign('group_project_spec_list',$group_project_spec_list);
			$this->display('groupproject.add.html');
		} else {
			$store_id = intval($_POST['store_id']);
			$store_mod = & m('store');
			$store_info = $store_mod->get($store_id);
			$param = array(
				'category_id' => trim($_POST['project_classify']),
				'gcategory_id' => trim($_POST['goods_classify']),
				'title'	=> trim($_POST['title']),
				'cprice' => number_format(floatval($_POST['cprice']),2,'.',''),
				'price' => number_format(floatval($_POST['price']),2,'.',''),
				'virtual_buy' => intval($_POST['virtual_buy']),
				'min_quantity' => intval($_POST['min_quantity']),
				'max_quantity' => intval($_POST['max_quantity']),
				'astrict_num' => intval($_POST['astrict_num']),
				'min_buy_num' => intval($_POST['min_buy_num']),
				'allows_refund' => intval($_POST['allows_refund']), //�Ƿ������˿�
				'introduction' => trim($_POST['introduction']), //�������
				'warm_prompt' => trim($_POST['warm_prompt']), //��ܰ��ʾ
				'sort' => intval($_POST['sort']), //����
				'store_id' => $store_id, //����ID
				'store_name' => $store_info['store_name'],
				'goods_name' => trim($_POST['goods_name']), //��Ʒ��
				'goods_desc' => trim($_POST['goods_desc']), //��Ʒ��ϸ 
				'promotion' => trim($_POST['promotion']), //�ƹ��
				'add_time' => trim($time),
			);
			//��ʼʱ��ת��Ϊʱ���
			if(!empty($_POST['start_time'])) {
				$start_arr = explode(' ', trim($_POST['start_time']),2);
				$start_date_arr = explode('-', $start_arr[0],3);
				$start_time_arr = explode(':',$start_arr[1],3);
				//��ʼʱ��
				$param['start_time'] = mktime(intval($start_time_arr[0]),intval($start_time_arr[1]),intval($start_time_arr[2]),intval($start_date_arr[1]),intval($start_date_arr[2]),intval($start_date_arr[0]));
			} else {
				$this->show_warning('��ʼʱ��δ��д!');
				return;
			}
			//����ʱ��ת��Ϊʱ���
			if(!empty($_POST['finish_time'])) {
				$finish_arr = explode(' ', trim($_POST['finish_time']),2);
				$finish_date_arr = explode('-', $finish_arr[0],3);
				$finish_time_arr = explode(':',$finish_arr[1],3);
				//����ʱ��
				$param['finish_time'] = mktime(intval($finish_time_arr[0]),intval($finish_time_arr[1]),intval($finish_time_arr[2]),intval($finish_date_arr[1]),intval($finish_date_arr[2]),intval($finish_date_arr[0]));
			} else {
				$this->show_warning('����ʱ��δ��д!');
				return;
			}
			/**
			 *  �ϴ�ͼƬ����
			 *  ͼƬ��ַ /data/files/mall/group/$time/	
			 *  ���� $time + ͼƬ���� + ͼƬ��׺
			 **/
			//�б���ʾͼ
			if(!empty($_FILES['image_list']['name'])) {
				$image_list_end =  strrchr(trim($_FILES['image_list']['name']),'.');
				if($_FILES['image_list']['tmp_name'] != '') {
					$image_list = '/data/files/mall/group/'.$time.'/'.$time.'image_list'.$image_list_end;
					if(!file_exists(ROOT_PATH.'/data/files/mall/group/'.$time.'/')) {
						mkdir(ROOT_PATH.'/data/files/mall/group/'.$time.'/',0777,true);
					}
	        		move_uploaded_file(trim($_FILES['image_list']['tmp_name']),ROOT_PATH.'/'.$image_list);
	        		$param['image_list'] = $image_list;
				}
			}
			//��Ʒ��ϸҳͼ
			if(!empty($_FILES['image_show']['name'])) {
				$image_show_end =  strrchr(trim($_FILES['image_show']['name']),'.');
				if($_FILES['image_show']['tmp_name'] != '') {
					$image_show = '/data/files/mall/group/'.$time.'/'.$time.'image_show'.$image_show_end;
					if(!file_exists(ROOT_PATH.'/data/files/mall/group/'.$time.'/')) {
						mkdir(ROOT_PATH.'/data/files/mall/group/'.$time.'/',0777,true);
					}
	        		move_uploaded_file(trim($_FILES['image_show']['tmp_name']),ROOT_PATH.'/'.$image_show);
	        		$param['image_show'] = $image_show;
				}
			}
			//�����Ź���ʾ
			if(!empty($_FILES['image_other']['name'])) {
				$image_other_end =  strrchr(trim($_FILES['image_other']['name']),'.');
				if($_FILES['image_other']['tmp_name'] != '') {
					$image_other = '/data/files/mall/group/'.$time.'/'.$time.'image_other'.$image_other_end;
					if(!file_exists(ROOT_PATH.'/data/files/mall/group/'.$time.'/')) {
						mkdir(ROOT_PATH.'/data/files/mall/group/'.$time.'/',0777,true);
					}
	        		move_uploaded_file(trim($_FILES['image_other']['tmp_name']),ROOT_PATH.'/'.$image_other);
	        		$param['image_other'] = $image_other;
				}
			}
			$this->_group_project_mod->edit($id,$param);
			
			//���
			$specname = $_POST['specname'];
			if(is_array($specname)) { //�����
				$have_specname = array();
				foreach($group_project_specname_list as $k => $v) { //ɾ��û�е�ֵ
					if(!in_array($v['specname_id'],$specname)) {
						$this->_group_project_specname_mod->drop(" specname_id = " . $v['specname_id'] . " AND project_id = " . $id);
					} else {
						$have_specname[] = $v['specname_id'];
					}
				}
				$addspecname = array_diff($specname,$have_specname);
				foreach($addspecname as $k => $v) {
					$this->_group_project_specname_mod->add(array('project_id' => $id,'specname_id' => $v));
				}
			}
			if(is_array($_POST['spec_value'])) { //���ֵ
				$have_spec = array();
				foreach($group_project_spec_list as $k => $v) { //ɾ��û�е�ֵ
					if(!in_array($v['spec_id'],$_POST['spec_value'])) {
						$this->_group_project_spec_mod->drop(" spec_id = " . $v['spec_id'] . " AND project_id = " . $id);
					} else {
						$have_spec[] = $v['spec_id'];
					}
				}
				$addspec = array_diff($_POST['spec_value'],$have_spec);
				foreach($addspec as $k => $v) {
					$this->_group_project_spec_mod->add(array('project_id' => $id,'spec_id' => $v));
				}
			}
			$this->show_message('�޸ĳɹ�!','��Ŀ����','index.php?app=groupproject&act=index');
		}
	}
	
	public function del() {
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if($id == 0) {
			$this->show_warning('����: δ֪����Ŀ!.');
			return;
		}
		if(!$this->_group_project_mod->drop($id)) {
			$this->show_warning('����: ɾ����Ŀʧ��!.');
			return;
		}
		//ɾ����� 
		if(!$this->_group_project_specname_mod->drop(" project_id = " . $id)) {
			$this->show_warning('����: ��ع��ɾ��ʧ��!.');
			return;
		}
		if(!$this->_group_project_spec_mod->drop(" project_id = " . $id)) {
			$this->show_warning('����: ��ع��ɾ��ʧ��!.');
			return;
		}
		$this->show_message('ɾ���ɹ�!','��Ŀ����','index.php?app=groupproject&act=index');
	}
	
	public function getSpec() {
		$specname_id = empty($_GET['specname_id']) ? 0 : intval($_GET['specname_id']);
		if($specname_id == 0) {
			$this->json_error('undefined_specname_id');
			return;
		}
		$groupspec_list = $this->_group_spec_mod->getAll("select * from pa_group_spec where specname_id = " . $specname_id);
		if(!$groupspec_list) {
			$this->json_error('have_no_value');
			return;
		}
		$this->json_result($groupspec_list);
	}
	
}