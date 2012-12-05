<?php 
class Ad_moneyApp extends BackendApp
{
	function index()
	{
		if (!IS_POST)
		{
			$this->display('ad_money.html');
		}
		else
		{
			$mobile = empty($_POST['mobile']) ? '' : trim($_POST['mobile']);
			$cash = empty($_POST['cash']) ? 0 : floatval($_POST['cash']);
			$msg = empty($_POST['msg']) ? '' : trim($_POST['msg']);
			$company = empty($_POST['company'])? '':trim($_POST['company']);
			if ($cash == 0)
			{
				$this->show_warning("���Ѳ���Ϊ��!");
				return;
			}

			if($company == '')
			{
				$this->show_warning("������ͻ���λ !");
				return;
			}
			if ($mobile) //�еĻ���Ҫ����
			{
				$customer_info = $this->_getCustomerByMobile($mobile);
				if (!$customer_info)
				{
					$this->show_warning("�Ź�Ա��Ϣ����!");
					return;
				}
				$this->ad_manager_rebate($customer_info['user_id'], $cash);
			}
			
			$param = array(
				'user_id' => 0,
    			'user_money' => $cash,
    			'user_credit' => 0,
    			'change_time' => gmtime(),
    			'change_desc' => "�ͻ���λ:{$company},���ѣ�{$cash}����, ����Ԥ����Ϣ[\"{$msg}\"], ��������.",
    			'change_type' => 60,
			);
			add_account_log($param);
			
			$this->show_message("�������˳ɹ�", "����", "index.php?app=ad_money");
		}
	}
	
	function _getCustomerByMobile($mobile)
	{
		$member_mod = & m('member');
		$member_info = $member_mod->get(array(
			'conditions' => ' mobile = "' . $mobile . '"'
		));
		if(!$member_info)
		{
			$this->show_warning("��������ֻ��Ų���ȷ�����߲����Ź�Ա���ֻ���");
			return ;
		}
		$_customer_manager_mod = & m("customermanager");
		$customer_info = $_customer_manager_mod->get(array(
			'conditions' => ' user_id = "' . $member_info['user_id'] . '"'
		));
		return $customer_info;
	}
};
 

?>