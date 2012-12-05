<?php

/**
 *    ���֧��
 *
 *    @author    Garbin
 *    @usage    none
 */
class BalancepayPayment extends BasePayment
{
    var $_code = 'balancepay';
    
    function _pay($order_info)
    {
    	//�鿴����Ƿ��㹻
    	$model_member =& m('member');
        $member_info  = $model_member->get($order_info['buyer_id']);
        $member_info['money'] = $member_info['money'] - $member_info['frozen_money'];
        if ($member_info['money']<$order_info['pay_amount'])
        {
            return -1;
        }
        
        if ($order_info['pay_amount']>0)
        {
	    	//֧���ɹ�-ֱ�ӿ۳��������, �����˻���¼.  
	    	changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['pay_amount'],SUBTRACK_MONEY);
	    	$log_info = array(
		            	'user_id'	    => intval($order_info['buyer_id']),
		            	'user_money'    => '-'.$order_info['pay_amount'],
		            	'change_desc'   => '���̽����۳��˻���'.$order_info['pay_amount'].'Ԫ',
		            	'order_id'      => $order_info['order_id'],
			            'change_time'	=>	time(),
		            	'change_type'	=> 	7,
		            ); 
		    add_account_log($log_info);
        }
    	//�����Ķ���״̬Ϊ�Ѹ���
    	$_storeorder_mod = & m('storeorder');
    	$param = array(
            'status'    => ORDER_ACCEPTED,
            'pay_time'  => time(),
        );
    	$_storeorder_mod->edit($order_info['order_id'], $param);
    	return 0;
    }
    
	/**
	 * ��Ա���֧��
	 * 	
	 * 	@return array
	 **/
	
	public function get_payform($order_info) {
		if ($order_info['status'] == ORDER_PENDING)
        {
			$order_model =& m('order');
	        $order_info  = $order_model->getRow("select o.*,s.address from pa_order o left join pa_store s on o.seller_id = s.store_id where order_id={$order_info['order_id']}");
			$model_member =& m('member');
	        $member_info  = $model_member->get($order_info['buyer_id']);
	        $member_info['money'] = $member_info['money'] - $member_info['frozen_money'];
	        if ($member_info['money']<$order_info['order_amount'])
	        {
	            return -1;
	        }
	        
	        //֧���ɹ�-ֱ�ӿ۳���Ա���, ���»�Ա�˻���¼.  
	    	changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['order_amount'],SUBTRACK_MONEY);
	    	$log_info = array(
		            	'user_id'	    => intval($order_info['buyer_id']),
		            	'user_money'	        => '-'.$order_info['order_amount'],
		            	'change_desc'   => '������ID��'.$order_info['seller_id'].',��������'.$order_info['seller_name'].'�� ���ѻ�Ա��'.$order_info['order_amount'].'Ԫ',
		            	'order_id'      => $order_info['order_id'],
			            'change_time'	=>	time(),
		            	'change_type'	=> 	41,
		            ); 
		    add_account_log($log_info); 
			if ($order_info['use_credit'] == 0)
	    	{
	    		$paytype = 6;
	    	}else
	    	{
	    		//ʹ���˻��������Ŀ۳��û�����--���ⶳ������ֲ���
	    		changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['use_credit'],SUBTRACK_CREDIT);
	    		$log_info1 = array(
		            	'user_id'	    => intval($order_info['buyer_id']),
		            	'user_credit'	        => '-'.$order_info['use_credit'],
		            	'change_desc'   => '������ID��'.$order_info['seller_id'].',��������'.$order_info['seller_name'].'�� ����ʹ�û��֣�'.$order_info['use_credit'].'PL',
		            	'order_id'      => $order_info['order_id'],
			            'change_time'	=>	time(),
		            	'change_type'	=> 	2,
		            ); 
		        add_account_log($log_info1); 
	    		changeMemberCreditOrMoney(intval($order_info['buyer_id']),$order_info['use_credit'],CANCLE_FROZEN_CREDIT);
	    		$log_info2 = array(
		            	'user_id'	    => intval($order_info['buyer_id']),
		            	'frozen_credit'	        => '-'.$order_info['use_credit'],
		            	'change_desc'   => '������ID��'.$order_info['seller_id'].',��������'.$order_info['seller_name'].'��ȡ��������֣�'.$order_info['use_credit'].'PL',
		            	'order_id'      => $order_info['order_id'],
			            'change_time'	=>	time(),
		            	'change_type'	=> 	32,
		            ); 
		        add_account_log($log_info2); 
	    		$paytype = 4;
	    	}
	    	   
	    	//�����Ķ���״̬Ϊ�Ѹ���-������
	        $param = array(
	        	'pay_type'  => $paytype,
	        	'use_money' => $order_info['order_amount'],
	            'status'    => ORDER_ACCEPTED,
	            'pay_time'  => time(),
	        	'short_code' => create_short_code(),
	        );
	        
	        $order_model->edit($order_info['order_id'],$param);
	         /* ��ȥ��Ʒ��� */
	        $order_model->change_stock('-', $order_info['order_id']);
	        
			$smslog =&  m('smslog'); 
	        import('class.smswebservice');    //������ŷ�����
	        $sms = SmsWebservice::instance(); //ʵ�������Žӿ���
	        	
	        if ($order_info['seller_id'] == STORE_ID)
	        {
			    $smscontent = "�ͻ�:{$order_info['buyer_name']}���Ѿ��ɹ����������:{$order_info['order_sn']}���뾡�췢��";
			    $mobile = DEF_MOBILE;
			    $verifytype = "buygoods";	
	        	$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ�����֤�����
	        	//���ŷ��ͳɹ�
	        	if ($result == 0) 
	        	{
	        		//����֤��д��SESSION
	        		$time = time();
	        		//ִ�ж�����־д�����
	        		$smsdata['mobile'] = $mobile;
	        		$smsdata['smscontent'] = $smscontent;
	        		$smsdata['type'] = $verifytype; //ע����֤����
	        		$smsdata['sendtime'] = $time;
	        		$smslog->add($smsdata);
	       		}
	        }else 
	        {
	        	$smscontent = "�𾴵�:{$order_info['buyer_name']}�����Ѿ��ɹ�������Ķ�����:{$order_info['order_sn']}��ȡ����֤��:{$param['short_code']}��ȡ����ַ:{$order_info['address']}���뾡�쵽��ȡ��";
			    $mobile = $order_info['buyer_mobile'];
			    $verifytype = "buygoodsverify";	
		      	$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ������Ѳ���
		      	//���ŷ��ͳɹ�
		        if ($result == 0) 
		        {
		        	$time = time();
		        	//ִ�ж�����־д�����
		        	$smsdata['mobile'] = $mobile;
		        	$smsdata['smscontent'] = $smscontent;
		        	$smsdata['type'] = $verifytype; //��������
		        	$smsdata['sendtime'] = $time;
		       		$smslog->add($smsdata);
		       	}
	        }
        }		
        header('Location:index.php?app=cashier&order_id=' . $order_info['order_id']);
	}
}

?>