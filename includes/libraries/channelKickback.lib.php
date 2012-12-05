<?php
/**
 *	@功能类, 渠道反利功能类
 *	@author bottle
 *
 **/
define('ONLINEORDER',1); //线上定单常量
define('QUICKORDER',0); //线下定单常量

class ChannelKickback {
	private $order_type; //定单类型
	private $order_id; // 定单ID
	private $order_mod; // 定单模型
	private $quickOrder_mod; // 快捷支付定单模型
	private $member_mod; // 用户模型
	private $store_mod; //店铺模型
	private $channelUser_mod; // 渠道用户模型
	private $channelFee_mod; //渠道利润参照模型
	private $region_mod; // 地区模型

	public function __construct() {
		$this->order_mod = & m('order');
		$this->quickOrder_mod = & m('quickorder');
		$this->member_mod = & m('member');
		$this->store_mod = & m('store');
		$this->channelUser_mod = & m('channeluser');
		$this->channelFee_mod = & m('channelfee');
		$this->region_mod = & m('region');
	}
	/**
	 *	主方法, 功能实现
	 **/
	public function main($order_id,$order_type = ONLINEORDER) {
		$this->order_id = $order_id; // order_id 初始化
		$this->order_type = $order_type; // order_type 初始化
		if(!$this->order_id) { //如果没有order_id 则程序执行完毕
			echo "date Error!";
			sleep(5);
			return -1; // 没有order_id
		}
		$order_info = $this->getOrderInfo();
		if(!$order_info || $order_info == -1) {
			echo "未成功获取订单信息,或订单不存在!";
			sleep(5);
			return -2; //未成功获取定单信息
		}
		$user_info = $this->getBuyerInfo($order_info['buyer_id']);
		if(!$user_info) {
			echo "获取买家信息失败,或买家不存在!";
			sleep(5);
			return -3; // 买家信息未获取到
		}
		if($user_info['sid'] == 0) { //用户sid为0, 表示该用户并未是店铺会员
			return -8; // 用户不属于任何商铺, 不需要返利	
		} else { //店铺会员
			$store_info = $this->getSellerStoreInfo($user_info['sid']);	
			if(!$store_info) {
				echo "获取关联信息失败";
				sleep(5);
				return -4; //店铺信息获取失败
			}
			if($store_info['channel_id'] == NULL) { // 没有channel_id, 就是本商铺并不是渠道商
				return -10; // 店铺不是渠道, 不需要返利
			} else {
				//是渠道,需要层层返利
				$ret = $this->storeKickback($store_info['channel_id'],$order_info,$store_info);
				if($ret != 0) {
					echo "数据操作不成功!";
					return -5;
				} else {
					return 0; //执行结束 
				}
			}
		}

	}

	/**
	 *	外部设置封装好的order_id
	 **/

	public function setOrderId($order_id) {
		$this->order_id = $order_id;
	}

	/**
	 * 	获取定单信息		
	 **/
	private function getOrderInfo() {
		switch($this->order_type) {
			case 0: //为快捷定单
				return $this->quickOrder_mod->get($this->order_id);		
			break;
			case 1: //为线上定单
				return $this->order_mod->get($this->order_id);
			break;
			default:
				echo "定单类型错误,其值为0或1的常量,为0表示此定单为快捷定单,为1表示此定单为线上订单!";
				sleep(5);
				return -1;
		}
	}
		
	/**
	 *	获取用户信息
	 **/
	private function getBuyerInfo($uid) {
		return $this->member_mod->get($uid);	
	}

	/**
	 *	获取用户所关联的店铺信息
	 **/
	private function getSellerStoreInfo($sid) {
		return $this->store_mod->get($sid);	
	}

	/**
	 *	判断店铺是否为渠道,如果是, 本身反利 ,并返回其区域ID
	 **/
	private function storeKickback($channel_id,$order_info,$store_info) {
		$channel_info = $this->channelUser_mod->get($channel_id);	
		$level = $channel_info['level'];
		$area_id = $channel_info['area_id'];	
		$kickBack = $this->kickBack($channel_id,$level,$area_id,$order_info['get_credit']);	

		// 返利, channel_user中加入积分
		channelKickback($channel_id,$kickBack,ADD_CREDIT);
		switch($this->order_type) {
			case ONLINEORDER : $money = $order_info['goods_amount']; break;
			case QUICKORDER : $money = $order_info['order_amount']; break;
			default:
				echo "定单类型错误,其值为0或1的常量,为0表示此定单为快捷定单,为1表示此定单为线上订单!";
				sleep(5);
				return -1;
		}
		$param = array(
			'userid' => $order_info['buyer_id'],
			'username' => $order_info['buyer_name'],
			'sid' => $store_info['store_id'],
			'shopname' => $store_info['store_name'],	
			'money'	=> $money,
			'income' => $kickBack,
			'channel_id' => $channel_id,
			'constime' => $order_info['finished_time'],
			'status' => 0,
			'order_id' => $order_info['order_id'],
		);
		addChannelIncome($param); //写入渠道返利数据
		
		$parent_channel = $this->getChannelInfo($area_id); //上级渠道信息

		if($parent_channel['level1']) { //有经营中心			
			$level1 = $parent_channel['level1'];

			$kickBack_level1 = $this->kickBack($level1['channel_id'],$level1['level'],$level1['area_id'],$order_info['get_credit']);
			// 返利, channel_user中加入积分
			channelKickback($level1['channel_id'],$kickBack_level1,ADD_CREDIT);
			$param_level1 = array(
				'userid' => $order_info['buyer_id'],
				'username' => $order_info['buyer_name'],
				'sid' => $store_info['store_id'],
				'shopname' => $store_info['store_name'],	
				'money'	=> $money,
				'income' => $kickBack_level1,
				'channel_id' => $level1['channel_id'],
				'constime' => $order_info['finished_time'],
				'status' => 0,
				'order_id' => $order_info['order_id'],
			);
			addChannelIncome($param_level1); //写入渠道返利数据
		} 
		
		if($parent_channel['level2']) { //有服务中心
			$level2 = $parent_channel['level2'];
			$kickBack_level2 = $this->kickBack($level2['channel_id'],$level2['level'],$level2['area_id'],$order_info['get_credit']);
			// 返利, channel_user中加入积分
			channelKickback($level2['channel_id'],$kickBack_level2,ADD_CREDIT);
			$param_level2 = array(
				'userid' => $order_info['buyer_id'],
				'username' => $order_info['buyer_name'],
				'sid' => $store_info['store_id'],
				'shopname' => $store_info['store_name'],	
				'money'	=> $money,
				'income' => $kickBack_level2,
				'channel_id' => $level2['channel_id'],
				'constime' => $order_info['finished_time'],
				'status' => 0,
				'order_id' => $order_info['order_id'],
			);
			addChannelIncome($param_level2);
		}

		return 0;
	}

	/**
	 *	查找渠道树的上层渠道,并返利	
	 **/
	private function getChannelInfo($area_id) {
		//当前地区的服务中心
		$level2 = $this->channelUser_mod->get(array('conditions' => 'level = 2 AND area_id = ' . $area_id));

		//当前地区的经营中心 area_id 
		$area_info = $this->region_mod->get($area_id);
		$area_level1_id = $area_info['parent_id'];
		$level1 = $this->channelUser_mod->get(array('conditions' => 'level = 1 AND area_id = ' . $area_level1_id));
		
		return array('level1' => $level1 , 'level2' => $level2);
	}

	/**
	 *	返利计算方法
	 **/
	private function kickBack($channel_id,$level,$area_id,$credit) {
		$fee_info = 	$this->channelFee_mod->get(array('conditions' => 'level = ' . $level . ' AND area_id= ' . $area_id));
		$return_rate = $fee_info['return_rate'];
		
		return $credit * ($return_rate/100); // 需要返的利润
	}
}
?>
