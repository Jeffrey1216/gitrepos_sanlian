<?php

/**
 *    October 29
 *
 *    @author   zhufuqing 
 *    @usage    none
 */
/*
 * define('ACCOUNT_MANAGER', 1); //客户经理
  define('KEY_ACCOUNT_MANAGER', 2); //大客户经理
  define('GROUP_ACCOUNT_MANAGER', 3); //集团客户经理
  define('PAGE_NUM', 20);
 * 
 */

class member_cardApp extends MemberbaseApp {

	var $_customer_gains_mod;
	var $_feed_enabled = false;
	var $_my_qa_mod;
	var $_widget_mod;

	function __construct() {
		$this->MemberApp();
	}

	function MemberApp() {
		parent::__construct();
		$ms = & ms();
		$this->_feed_enabled = $ms->feed->feed_enabled();
		$this->assign('feed_enabled', $this->_feed_enabled);
		$this->_member_mod = & m("member");
		$this->_my_qa_mod = & m('goodsqa');
		$this->_widget_mod = &m('widget');
	}

	function index() {
		$user_id = $this->visitor->get('user_id');
		/* 姓名搜索条件 */
		$schkey = !$_POST['keyword'] ? "" : $_POST['keyword'];
		$this->assign('schkey', $schkey);
		$schkey != "" && $schkey = " and consignee like '%" . $schkey . "%'";

		/* get data from member_card */
		$model_card = & m('member_card');
		$fileds = array(
		    'fields' => "member_card.*,member.user_name as UserName",
		    'conditions' => ' UID=' . $user_id,
		    'join' => 'belongs_to_member'
		);
		$memberCardInfo = $model_card->find($fileds);
		foreach ($memberCardInfo as &$val) {
			$val['CreateTime'] = date('Y-m-d H:i:s',$val['CreateTime']);
			$val['Verify'] = intval($val['Verify'] * 1000);
			//判断是否要验证：
			(empty($val['Verify'])) ? '' : $val['needVerify'] = TRUE;
			switch ($val['CardStatus']) {
				case 'BIND': $val['CardStatusInfo'] = LANG::get('card_BIND');
					     $val['fixedLock'] = LANG::get('Normal');
					break;
				case 'USRLOCK': $val['CardStatusInfo'] = LANG::get('card_USRLOCK');
					     $val['fixedLock'] =  LANG::get('Lock');
					break;
				case 'SYSLOCK': $val['CardStatusInfo'] = LANG::get('card_SYSLOCK');
					     $val['fixedLock'] =  LANG::get('Lock');
					break;
				default : $val['CardStatusInfo'] = LANG::get('card_NOBIND');
			}
		}
		$this->assign('memberCardInfo', $memberCardInfo);

		/* 当前位置 */
		$this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('my_address'), 'index.php?app=my_address', LANG::get('address_list'));
		/* 当前用户基本信息 */
		$this->_get_user_info();
		/* 当前用户中心菜单 */
		$this->_curitem('memberCardInfo');

		/* 当前所处子菜单 */
		$this->_curmenu('memberCardList');

		$this->import_resource(array(
		    'script' => array(
			array(
			    'path' => 'dialog/dialog.js',
			    'attr' => 'id="dialog_js"',
			),
			array(
			    'path' => 'jquery.ui/jquery.ui.js',
			    'attr' => '',
			),
			array(
			    'path' => 'jquery.plugins/jquery.validate.js',
			    'attr' => '',
			),
		    ),
		    'style' => 'jquery.ui/themes/ui-lightness/jquery.ui.css',
		));
		$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_address'));
		$this->display('member_card_list.html');
	}

	function addCard() {
		$user_id = $this->visitor->get('user_id');
		if (!IS_POST) {
			/* 当前位置 */
			//$this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('basic_information'));

			/* 当前用户中心菜单 */
			$this->_curitem('memberCardInfo');

			/* 当前所处子菜单 */
			$this->_curmenu('addCard');
			/* 当前用户基本信息 */
			$this->_get_user_info();
			$ms = & ms();    //连接用户系统
			$edit_avatar = $ms->user->set_avatar($this->visitor->get('user_id')); //获取头像设置方式
			//$model_card = & m('member_card');
			//$memberCardInfo = $model_card->get(array('conditions' => ' UID="' . $user_id . '"'));
			//$this->assign('memberCardInfo', $memberCardInfo);

			$model_user = & m('member');
			$profile = $model_user->get_info(intval($user_id));
			if ($profile['portrait'] == "") {
				$profile['portrait'] = SITE_URL . "/themes/mall/default/styles/default/images/120X120logo.jpg";
			} else {
				$profile['portrait'] = IMAGE_URL . $profile['portrait'];
			}
			$this->assign('profile', $profile);
			$this->import_resource(array(
			    'script' => 'jquery.plugins/jquery.validate.js',
			));
			$this->assign('edit_avatar', $edit_avatar);
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_profile'));
			$this->display('member_card_add.html');
		} else {

			if (empty($_POST['CardNumber'])) {
				$this->show_warning('内容不全, 请完整填写信息!');
				return;
			}
			$status = $this->_checkExistsAccount(trim($_POST['CardNumber']));	
			if(!$status){
				$this->show_warning(Lang::get('card_number_exists'));
				return;	
			}
			
			$data = array(
			    'CardNumber' => $_POST['CardNumber'],
			    'RealName' => $_POST['RealName'],
			    'BankName' => $_POST['BankName'],
			    'UID' => $user_id,
			    'CreateTime' => time(),
			    'CardStatus' => 'NOBIND',
			);
			$model_card = & m('member_card');
			if (!$model_card->add($data)) {
				$this->show_warning('申请失败, 请重试!');
				return;
			}
			$this->show_message('增加成功!', 'go_back', 'index.php?app=member_card');
		}
	}

	function editCard() {
		$card_id = empty($_GET['card_id']) ? 0 : intval($_GET['card_id']);
		if (!$card_id) {
			echo Lang::get("lackInfo");
			return;
		}
		if (!IS_POST) {
			$model_card = & m('member_card');
			$fileds = array(
				'fields' => "member_card.*,member.user_name as UserName",
				'conditions' => ' member_card.Id=' . $card_id,
				'join' => 'belongs_to_member'
			);
			$memberCardInfo = $model_card->find($fileds);
			
			if (empty($memberCardInfo)){
				echo Lang::get('lackInfo');
				return;
			}
			$memberCardInfo = current($memberCardInfo);
			$tempVerify = intval($memberCardInfo['Verify'] * 1000);
			//判断是否要验证：
			if(!empty($tempVerify)){
				$memberCardInfo['needVerify'] = TRUE;
				$memberCardInfo['Verify'] = '';
			}
			(empty($tempVerify)) ? '' : $memberCardInfo['needVerify'] = TRUE;
			$this->assign('memberCardInfo', $memberCardInfo);
			/* 当前位置 */
			$this->_curlocal(LANG::get('member_center'), 'index.php?app=member_card', LANG::get('my_address'), 'index.php?app=member_card', LANG::get('edit_address'));

			/* 当前用户中心菜单 */
			/* $this->_curitem('my_address');
			  /* 当前所处子菜单 */
			header('Content-Type:text/html;charset=' . CHARSET);
			$this->_curmenu('member_card');
			//$this->import_resource('mlselection.js, jquery.plugins/jquery.validate.js');
			$this->assign('act', 'editCard');
			//$this->_get_regions();
			$this->display('member_card.edit.html');
		} else {
			if(empty($_POST['card_id'])) $this->pop_warning(Lang::get('lackInfo'));
			$model_card = & m('member_card');
			$rs = $model_card->get($card_id);
			$cardStatus = intval($rs['Verify'] * 1000);
			(empty($cardStatus)) ? $checkPram = $_POST['CardNumber'] : $checkPram = $_POST['card_id'];
			if (empty($checkPram)) {
				 $this->pop_warning(Lang::get('warmImperfect'));
				return;
			}
			if(empty($cardStatus)){
				$data = array(
				    'CardNumber' => $_POST['CardNumber'],
				    'RealName' => $_POST['RealName'],
				    'BankName' => $_POST['BankName'],
				);
			}else{
				 if($cardStatus == intval($_POST['Verify'] * 1000)){
					 $data = array(
					     "CardStatus" => "BIND",
					 );
				 }else{
					 $data = array(
					     "VerifyStatus" => intval($rs['VerifyStatus']) + 1,
					 );
					 if(intval($rs['VerifyStatus']) >= 2) $data['CardStatus'] = 'USRLOCK';
					 $checkNumWarm = Lang::get('checkNum1') . (3-$data['VerifyStatus'])  . Lang::get('checkNum2');
					 $addCheck = TRUE;
				 }
			}
			$model_card->edit("Id={$card_id} AND UID=" . $this->visitor->get('user_id'), $data);
			if($addCheck) {
				$this->pop_warning($checkNumWarm);
				if($data['CardStatus']) $this->pop_warning('ok', APP . '_' . ACT);
				return ;
			}
			if ($model_card->has_error()) {
				$this->pop_warning($model_card->get_error());
				return;
			}
			$this->pop_warning('ok', APP . '_' . ACT);
		}
	}

	function cardajax(){
		$status = $this->_checkExistsAccount(trim($_GET['account']));	
		echo $status;
		exit();
	}
	function _checkExistsAccount($account){
		$model_card = & m('member_card');
		$rs = $model_card ->find("CardNumber={$account}");
		return (empty($rs)) ? TRUE : FALSE;
	}

	/**
	 *    二级菜单
	 */
	function _get_member_submenu() {
		$submenus = array(
		    array(
			'name' => 'memberCardList',
			'url' => 'index.php?app=member_card',
		    ),
		    array(
			'name' => 'addCard',
			'url' => 'index.php?app=member_card&amp;act=addCard',
		    ),
		);

		return $submenus;
	}

}

?>
