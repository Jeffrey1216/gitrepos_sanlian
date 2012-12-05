<?php
/**
 *	@������, ��������������
 *	@author bottle
 *
 **/
define('ONLINEORDER',1); //���϶�������
define('QUICKORDER',0); //���¶�������

class ChannelKickback {
	private $order_type; //��������
	private $order_id; // ����ID
	private $order_mod; // ����ģ��
	private $quickOrder_mod; // ���֧������ģ��
	private $member_mod; // �û�ģ��
	private $store_mod; //����ģ��
	private $channelUser_mod; // �����û�ģ��
	private $channelFee_mod; //�����������ģ��
	private $region_mod; // ����ģ��

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
	 *	������, ����ʵ��
	 **/
	public function main($order_id,$order_type = ONLINEORDER) {
		$this->order_id = $order_id; // order_id ��ʼ��
		$this->order_type = $order_type; // order_type ��ʼ��
		if(!$this->order_id) { //���û��order_id �����ִ�����
			echo "date Error!";
			sleep(5);
			return -1; // û��order_id
		}
		$order_info = $this->getOrderInfo();
		if(!$order_info || $order_info == -1) {
			echo "δ�ɹ���ȡ������Ϣ,�򶩵�������!";
			sleep(5);
			return -2; //δ�ɹ���ȡ������Ϣ
		}
		$user_info = $this->getBuyerInfo($order_info['buyer_id']);
		if(!$user_info) {
			echo "��ȡ�����Ϣʧ��,����Ҳ�����!";
			sleep(5);
			return -3; // �����Ϣδ��ȡ��
		}
		if($user_info['sid'] == 0) { //�û�sidΪ0, ��ʾ���û���δ�ǵ��̻�Ա
			return -8; // �û��������κ�����, ����Ҫ����	
		} else { //���̻�Ա
			$store_info = $this->getSellerStoreInfo($user_info['sid']);	
			if(!$store_info) {
				echo "��ȡ������Ϣʧ��";
				sleep(5);
				return -4; //������Ϣ��ȡʧ��
			}
			if($store_info['channel_id'] == NULL) { // û��channel_id, ���Ǳ����̲�����������
				return -10; // ���̲�������, ����Ҫ����
			} else {
				//������,��Ҫ��㷵��
				$ret = $this->storeKickback($store_info['channel_id'],$order_info,$store_info);
				if($ret != 0) {
					echo "���ݲ������ɹ�!";
					return -5;
				} else {
					return 0; //ִ�н��� 
				}
			}
		}

	}

	/**
	 *	�ⲿ���÷�װ�õ�order_id
	 **/

	public function setOrderId($order_id) {
		$this->order_id = $order_id;
	}

	/**
	 * 	��ȡ������Ϣ		
	 **/
	private function getOrderInfo() {
		switch($this->order_type) {
			case 0: //Ϊ��ݶ���
				return $this->quickOrder_mod->get($this->order_id);		
			break;
			case 1: //Ϊ���϶���
				return $this->order_mod->get($this->order_id);
			break;
			default:
				echo "�������ʹ���,��ֵΪ0��1�ĳ���,Ϊ0��ʾ�˶���Ϊ��ݶ���,Ϊ1��ʾ�˶���Ϊ���϶���!";
				sleep(5);
				return -1;
		}
	}
		
	/**
	 *	��ȡ�û���Ϣ
	 **/
	private function getBuyerInfo($uid) {
		return $this->member_mod->get($uid);	
	}

	/**
	 *	��ȡ�û��������ĵ�����Ϣ
	 **/
	private function getSellerStoreInfo($sid) {
		return $this->store_mod->get($sid);	
	}

	/**
	 *	�жϵ����Ƿ�Ϊ����,�����, ������ ,������������ID
	 **/
	private function storeKickback($channel_id,$order_info,$store_info) {
		$channel_info = $this->channelUser_mod->get($channel_id);	
		$level = $channel_info['level'];
		$area_id = $channel_info['area_id'];	
		$kickBack = $this->kickBack($channel_id,$level,$area_id,$order_info['get_credit']);	

		// ����, channel_user�м������
		channelKickback($channel_id,$kickBack,ADD_CREDIT);
		switch($this->order_type) {
			case ONLINEORDER : $money = $order_info['goods_amount']; break;
			case QUICKORDER : $money = $order_info['order_amount']; break;
			default:
				echo "�������ʹ���,��ֵΪ0��1�ĳ���,Ϊ0��ʾ�˶���Ϊ��ݶ���,Ϊ1��ʾ�˶���Ϊ���϶���!";
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
		addChannelIncome($param); //д��������������
		
		$parent_channel = $this->getChannelInfo($area_id); //�ϼ�������Ϣ

		if($parent_channel['level1']) { //�о�Ӫ����			
			$level1 = $parent_channel['level1'];

			$kickBack_level1 = $this->kickBack($level1['channel_id'],$level1['level'],$level1['area_id'],$order_info['get_credit']);
			// ����, channel_user�м������
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
			addChannelIncome($param_level1); //д��������������
		} 
		
		if($parent_channel['level2']) { //�з�������
			$level2 = $parent_channel['level2'];
			$kickBack_level2 = $this->kickBack($level2['channel_id'],$level2['level'],$level2['area_id'],$order_info['get_credit']);
			// ����, channel_user�м������
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
	 *	�������������ϲ�����,������	
	 **/
	private function getChannelInfo($area_id) {
		//��ǰ�����ķ�������
		$level2 = $this->channelUser_mod->get(array('conditions' => 'level = 2 AND area_id = ' . $area_id));

		//��ǰ�����ľ�Ӫ���� area_id 
		$area_info = $this->region_mod->get($area_id);
		$area_level1_id = $area_info['parent_id'];
		$level1 = $this->channelUser_mod->get(array('conditions' => 'level = 1 AND area_id = ' . $area_level1_id));
		
		return array('level1' => $level1 , 'level2' => $level2);
	}

	/**
	 *	�������㷽��
	 **/
	private function kickBack($channel_id,$level,$area_id,$credit) {
		$fee_info = 	$this->channelFee_mod->get(array('conditions' => 'level = ' . $level . ' AND area_id= ' . $area_id));
		$return_rate = $fee_info['return_rate'];
		
		return $credit * ($return_rate/100); // ��Ҫ��������
	}
}
?>
