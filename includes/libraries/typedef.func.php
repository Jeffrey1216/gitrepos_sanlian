<?php
/**
 * 	Paila:商城所有积分操作函数库
 *  @Author: typedef.bottle
 * ============================================================================
 * 版权所有 (C) 2010-2011 三联融创，并保留所有权利。
 * 网站地址: http://www.paila100.com
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: credit.func.php 7715 2009-05-07 06:56:11Z $
 */
define("SUBTRACK_CREDIT",0);   //减去会员积分
define("ADD_CREDIT",1);        //增加会员积分
define("ADD_MONEY",2);         //增加会员余额
define("FROZEN_CREDIT",3);     //冻结会员积分
define("SUBTRACK_MONEY",4);    //减去会员余额
define("CANCLE_FROZEN_CREDIT",5); //取消冻结会员积分
define("FROZEN_MONEY",6);    //冻结会员余额
define("CANCLE_FROZEN_MONEY",7); //取消冻结会员余额
function get_change_type()
{
	return array(
		1 => '用户购物返还积分',
		2 => '用户购物使用积分',
		3 => '用户充值现金',
		4 => '用户提现', 
		5 => '店铺充值余额',
		6 => '店铺提现',
		7 => '扣除店铺余额',	
		8 => '系统赠送余额',
		9 => '系统扣除余额',
		10=> '冻结现金余额',
		11=> '取取冻结现金额',
		12=> '会员充值',
		13=> '店铺充值',
		14 => '系统赠送积分',
		15 => '系统扣除积分',
        16 => '店铺充值余额',
        17 => '团购员充值余额',
        
		31 => '用户购物冻结积分', 
		32 => '取消冻结积分',
		33 => '退还积分和余额',
		34 => '扣除用户积分',
		41 => '用户购物使用账户金额',
		42 => '用户余额赠送',
		43 => '用户PL币赠送',
		44 => '获得赠送余额',
		45 => '获得PL币增送',
		50 => '团购员拉取广告费收益',
		51 => '团购员推荐店铺收益',
		52 => '团购员购买大礼包收益',
		53 => '会员购物团购员返利收益',
		54 => '渠道广告费收益',
		55 => '渠道团购员收益',
		56 => '渠道店铺收益',
		57 => '会员购物渠道返利收益',
		58 => '会员购物店铺返利收益',
		
		60 => '收入广告费',
		100 => '用户线下退款',
		101 => '线下消费金额和消费pl币',
		102 => '线下购物赠送的pl币',
	);
}
function get_bank()
{
	return array(
		'ABC'  => '中国农业银行',
		'ICBC' => '中国工商银行',
		'BC'   => '中国银行',
		'CCB'  => '中国建设银行',	
	);
}

function get_storechange_type()
{
	return array(
		1 => '店铺充值',
		2 => '会员返利',
	);
}

function change_type()
{
	return array(		
		8 => '系统赠送余额',
		9 => '系统扣除余额',
		14 => '系统赠送积分',
		15 => '系统扣除积分',
        16 => '店铺充值余额',
		17 => '团购员充值余额',
	);
}
function _get_date_levels($str)
{
	return array(
		1 => array(
			'text' => '三天内' . $str,
			'ms' => 1000 * 60 * 60 * 24 * 3,
		), 
		2 => array(
			'text' => '一周内' . $str,
			'ms' => 1000 * 60 * 60 * 24 * 7,
		),
		3 => array(
			'text' => '一月内' . $str,
			'ms' => 1000 * 60 * 60 * 24 * 30,
		),
		4 => array(
			'text' => '三月内' . $str,
			'ms' => 1000 * 60 * 60 * 24 * 90,
		),
	);
}

//投诉管现->投诉类型
function complain_type()
{
	return array(
		'1' => '产品相关',
		'2' => '价格相关',
		'3' => '服务相关',
		'4' => '物流相关',
		'5' => '售后相关',
		'6' => '财务相关',
		'7' => '活动相关',
		'8' => '网站相关',
		'9' => '预约相关',
		'10' => '其它方面',
	);
}

//添加会员账户日志
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
 * 	用户积分和余额操作
 * 
 * 	涉及用户数据表()
 * 	@Author : typedef.bottle----edit：lihuoliang
 *  @Param : int $u 用户id 
 *  @Param : int $c 变更的积分数
 *  @Param : int $t 变更类型 默认为增加用户积分
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

//添加账户审核记录
function addCreditVerify($param) {
	if(is_array($param)) {
		$m = & m('creditverify');
		$m->add($param);
		
	} else {
		exit("Param Error!");
	}
}

//获得推荐人的类型
function getfanli($user_id = 0) {
	if(intval($user_id)) 
	{
		$m = & m('member');
		$store = & m('store');
		$_customer_manager_mod = & m('customermanager');
		$member_info = $m->get($user_id);
		if ($member_info)
		{
			//判断此会员是否有邀请人
			if ($member_info['invite_id'])
			{
				//判断邀请人是团购员还是店铺
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
//格式化金额，取最后2位有效数字，舍去后面的数字
function format_money($money)
{
	list($m,$n) = explode('.',$money);
    $p = substr($n,0,2);
    $o = floatval($m.'.'.$p);
    return $o;
}
//获取返利金额分成
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
//生成6位随机的提货验证码
function create_short_code() 
{
		/* 选择一个随机的方案 */
	    mt_srand((double) microtime() * 1000000);
	    return  str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
}
/****************************************后面基本是后期需要废弃的方法*************************/


/**
 *	渠道返利操作
 *	涉及channel_user表
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
 *	渠道返利记录
 *	涉及channel_income
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
