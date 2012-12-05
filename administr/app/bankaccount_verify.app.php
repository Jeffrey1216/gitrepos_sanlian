<?php
/*
 * Author: zhufuqing
 * 
 */
class Bankaccount_verifyApp extends BackendApp {

	var $customer_withdraw_ask_mod;
	var $member_mod;

	public function __construct() {
		$this->Bankaccount_verifyApp();
	}

	public function Bankaccount_verifyApp() {
		parent::__construct();
		$this->customer_withdraw_ask_mod = &m('customerwithdrawask');
		$this->member_mod = &m('member');
	}

	function index() {
		$status = empty($_GET['status']) ? '' : intval($_GET['status']);
		//条件查旬
		$conditions = "1=1";
		$conditions .= $this->_get_query_conditions(array(
		    array(
			'field' => $_GET['search_name'],
			'name' => 'search_value',
			'equal' => 'like',
		    ),
			));
		switch ($status) {
			case 0:
				$conditions .= " AND  member_card.CardStatus = 'NOBIND'";
				$this->assign('status', 1);
				break;
			case 1:
				$conditions .= " AND  member_card.CardStatus = 'BIND'";
				$this->assign('status', 2);
				break;
			case 2:
				$conditions .= " AND member_card.CardStatus not in('BIND','NOBIND')";
				$this->assign('status', 3);
				break;
		}
		$type = get_change_type();
		$page = $this->_get_page(20);
		/* get data from member_card */
		$model_card = & m('member_card');
		$filedsCount = array(
		    'fields' => " count(*) as sum ",
		    'conditions' => $conditions,
		    'join' => 'belongs_to_member'
		);
		$fileds = array(
		    'fields' => "member_card.*,member.user_name as UserName",
		    'conditions' => $conditions . " limit " . $page['limit'],
		    'join' => 'belongs_to_member'
		);
		$memberCardInfo = $model_card->find($fileds);
		$cardCount= $model_card->get($filedsCount);
		$page['item_count'] = $cardCount['sum'];
		foreach ($memberCardInfo as &$val) {
			switch ($val['CardStatus']) {
				case 'BIND': $val['CardStatus'] = LANG::get('card_BIND');
					break;
				case 'USRLOCK': $val['CardStatus'] = LANG::get('card_USRLOCK');
					break;
				case 'SYSLOCK': $val['CardStatus'] = LANG::get('card_SYSLOCK');
					break;
				default : $val['CardStatus'] = LANG::get('card_NOBIND');
			}
		}
		
		
		//$page['item_count'] = $this->customer_withdraw_ask_mod->getOne('select count(*) from pa_customer_withdraw_ask cws left join pa_member m on cws.user_id = m.user_id where ' . $conditions);

		/*$member = $this->customer_withdraw_ask_mod->getAll("select cws.id,m.user_id,m.user_name,m.mobile,m.money,m.frozen_money,cws.withdraw_amount,cws.draw_name,cws.draw_type,cws.draw_bank,cws.operator_time,
																cws.draw_accounts,cws.status,cws.reason,cws.operator,cws.withdraw_time from pa_customer_withdraw_ask cws left join 
																pa_member m on cws.user_id = m.user_id where $conditions ORDER BY cws.withdraw_time DESC limit " . $page['limit']);
	*/	
		$this->assign('memberCardInfo', $memberCardInfo);
		$this->assign('search_name', array(
		    'member.user_name' => LANG::get('user_name'),
		    'member_card.BankName' => LANG::get('draw_bank'),
		    'member_card.RealName' => LANG::get('draw_name'),
		));
		$this->_format_page($page);
		$this->assign('page_info', $page);
		$this->import_resource(array('script' => 'mlselection.js,inline_edit.js'));
		$this->assign('imgurl', IMAGE_URL);
		$this->display('bankaccount_verify_index.html');
	}
	    //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('Verify', 'brand', 'closed')))
       {
           $data[$column] = $value;
	   $model_card = & m('member_card');
           $model_card->edit($id, $data);
           if(!$model_card->has_error())
           {
               echo "true";
	       	       exit();
           }
       }
       else
       {
           return ;
       }
       return ;
   }

}

?>