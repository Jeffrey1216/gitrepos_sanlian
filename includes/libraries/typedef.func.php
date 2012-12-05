<?php
/**
 * 	Paila:�̳����л��ֲ���������
 *  @Author: typedef.bottle
 * ============================================================================
 * ��Ȩ���� (C) 2010-2011 �����ڴ�������������Ȩ����
 * ��վ��ַ: http://www.paila100.com
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: credit.func.php 7715 2009-05-07 06:56:11Z $
 */
define("SUBTRACK_CREDIT",0);   //��ȥ��Ա����
define("ADD_CREDIT",1);        //���ӻ�Ա����
define("ADD_MONEY",2);         //���ӻ�Ա���
define("FROZEN_CREDIT",3);     //�����Ա����
define("SUBTRACK_MONEY",4);    //��ȥ��Ա���
define("CANCLE_FROZEN_CREDIT",5); //ȡ�������Ա����
define("FROZEN_MONEY",6);    //�����Ա���
define("CANCLE_FROZEN_MONEY",7); //ȡ�������Ա���
function get_change_type()
{
	return array(
		1 => '�û����ﷵ������',
		2 => '�û�����ʹ�û���',
		3 => '�û���ֵ�ֽ�',
		4 => '�û�����', 
		5 => '���̳�ֵ���',
		6 => '��������',
		7 => '�۳��������',	
		8 => 'ϵͳ�������',
		9 => 'ϵͳ�۳����',
		10=> '�����ֽ����',
		11=> 'ȡȡ�����ֽ��',
		12=> '��Ա��ֵ',
		13=> '���̳�ֵ',
		14 => 'ϵͳ���ͻ���',
		15 => 'ϵͳ�۳�����',
        16 => '���̳�ֵ���',
        17 => '�Ź�Ա��ֵ���',
        
		31 => '�û����ﶳ�����', 
		32 => 'ȡ���������',
		33 => '�˻����ֺ����',
		34 => '�۳��û�����',
		41 => '�û�����ʹ���˻����',
		42 => '�û��������',
		43 => '�û�PL������',
		44 => '����������',
		45 => '���PL������',
		50 => '�Ź�Ա��ȡ��������',
		51 => '�Ź�Ա�Ƽ���������',
		52 => '�Ź�Ա������������',
		53 => '��Ա�����Ź�Ա��������',
		54 => '������������',
		55 => '�����Ź�Ա����',
		56 => '������������',
		57 => '��Ա����������������',
		58 => '��Ա������̷�������',
		
		60 => '�������',
		100 => '�û������˿�',
		101 => '�������ѽ�������pl��',
		102 => '���¹������͵�pl��',
	);
}
function get_bank()
{
	return array(
		'ABC'  => '�й�ũҵ����',
		'ICBC' => '�й���������',
		'BC'   => '�й�����',
		'CCB'  => '�й���������',	
	);
}

function get_storechange_type()
{
	return array(
		1 => '���̳�ֵ',
		2 => '��Ա����',
	);
}

function change_type()
{
	return array(		
		8 => 'ϵͳ�������',
		9 => 'ϵͳ�۳����',
		14 => 'ϵͳ���ͻ���',
		15 => 'ϵͳ�۳�����',
        16 => '���̳�ֵ���',
		17 => '�Ź�Ա��ֵ���',
	);
}
function _get_date_levels($str)
{
	return array(
		1 => array(
			'text' => '������' . $str,
			'ms' => 1000 * 60 * 60 * 24 * 3,
		), 
		2 => array(
			'text' => 'һ����' . $str,
			'ms' => 1000 * 60 * 60 * 24 * 7,
		),
		3 => array(
			'text' => 'һ����' . $str,
			'ms' => 1000 * 60 * 60 * 24 * 30,
		),
		4 => array(
			'text' => '������' . $str,
			'ms' => 1000 * 60 * 60 * 24 * 90,
		),
	);
}

//Ͷ�߹���->Ͷ������
function complain_type()
{
	return array(
		'1' => '��Ʒ���',
		'2' => '�۸����',
		'3' => '�������',
		'4' => '�������',
		'5' => '�ۺ����',
		'6' => '�������',
		'7' => '����',
		'8' => '��վ���',
		'9' => 'ԤԼ���',
		'10' => '��������',
	);
}

//��ӻ�Ա�˻���־
function add_account_log($param)
{
	$_accountlog_mod = & m('accountlog');
	$result = $_accountlog_mod->add($param);
	if (!$result)
	{
		return -1;
	}
	return 0;
}

/**
 * 	�û����ֺ�������
 * 
 * 	�漰�û����ݱ�()
 * 	@Author : typedef.bottle----edit��lihuoliang
 *  @Param : int $u �û�id 
 *  @Param : int $c ����Ļ�����
 *  @Param : int $t ������� Ĭ��Ϊ�����û�����
 */
function changeMemberCreditOrMoney($u , $c , $t = ADD_CREDIT) {
	$m = & m('member');
	switch($t) {
		case SUBTRACK_CREDIT:$m->edit($u,"credit=credit-".$c);break;
		case ADD_CREDIT:$m->edit($u,"credit=credit+".$c);break;
		case ADD_MONEY:$m->edit($u,"money=money+".$c);break;
		case FROZEN_CREDIT:$m->edit($u,"frozen_credit = frozen_credit+".$c);break;
		case SUBTRACK_MONEY:$m->edit($u,"money=money-".$c);break;
		case CANCLE_FROZEN_CREDIT:$m->edit($u,"frozen_credit = frozen_credit-".$c);break;
		case FROZEN_MONEY:$m->edit($u,"frozen_money = frozen_money+".$c);break;
		case CANCLE_FROZEN_MONEY:$m->edit($u,"frozen_money = frozen_money-".$c);break;
		default : exit("Error!");
	}
}

//����˻���˼�¼
function addCreditVerify($param) {
	if(is_array($param)) {
		$m = & m('creditverify');
		$m->add($param);
		
	} else {
		exit("Param Error!");
	}
}

//����Ƽ��˵�����
function getfanli($user_id = 0) {
	if(intval($user_id)) 
	{
		$m = & m('member');
		$store = & m('store');
		$_customer_manager_mod = & m('customermanager');
		$member_info = $m->get($user_id);
		if ($member_info)
		{
			//�жϴ˻�Ա�Ƿ���������
			if ($member_info['invite_id'])
			{
				//�ж����������Ź�Ա���ǵ���
				$customer_info = $_customer_manager_mod->get($member_info['invite_id']);
				$store_info    = $store->get($member_info['invite_id']);
				if ($customer_info)
				{
					return 'tuan';
				}elseif($store_info)
				{
					if ($store_info['state']==1)
					{
						return 'store';
					}else 
					{
						return 'channel';
					}
				}else
				{
					return 'channel';
				}
			}else
			{
				return 'channel';
			}
		}else
		{
			exit("Member Not exsit Error!");
		}
	} else 
	{
		exit("Param Error!");
	}
}
//��ʽ����ȡ���2λ��Ч���֣���ȥ���������
function format_money($money)
{
	list($m,$n) = explode('.',$money);
    $p = substr($n,0,2);
    $o = floatval($m.'.'.$p);
    return $o;
}
//��ȡ�������ֳ�
function format_fanli_money($money)
{
	$array = array();
	$array['money']  = format_money($money);
	$array['cash']   = format_money($money*0.7);
	$array['credit'] = format_money($money - $array['cash']);
	if ($array['credit']>$array['cash'])
	{
		$array['cash']   = $array['credit'];
		$array['credit'] = 0;
	}
	if ($array['credit']==0&&$array['cash']==0)
	{
		return false;
	}else
	{
    	return $array;
	}
}
//����6λ����������֤��
function create_short_code() 
{
		/* ѡ��һ������ķ��� */
	    mt_srand((double) microtime() * 1000000);
	    return  str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
}
/****************************************��������Ǻ�����Ҫ�����ķ���*************************/


/**
 *	������������
 *	�漰channel_user��
 *	
 *	@Author : typedef.bottle
 *	@param : 
 *
 **/
function channelKickback($cid , $c , $t = ADD_CREDIT) {
	$m = & m('channeluser');	
	switch($t) {
		case ADD_CREDIT : $m->edit($cid , "total_credit = total_credit + " . $c);break;
		case SUBTRACK_CREDIT : $m->edit($cid , "total_credit = total_credit - " . $c);break;
		default : exit("Error!");
	}
}
/**
 *	����������¼
 *	�漰channel_income
 *	@Author : typedef.bottle
 *	@param : array $param
 **/
function addChannelIncome($param) {
	if(is_array($param)) {
		$m = & m('channelincome');
		$m->add($param);
	} else {
		exit("Param Error!");
	}
}

?>
