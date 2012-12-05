<?php

/**
 * PaiLa: ��վ��̨�������˵�����
 * ============================================================================
 * ��Ȩ���� (C) 2010-2011 �����ڴ�������������Ȩ����
 * ��վ��ַ: http://www.paila.com
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: inc.menu.php 16 2007-12-23 15:36:24Z Redstone $
 */

if (!defined('IN_PL'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

$menu_data = array
(
    'mall_setting' => array
    (
        'default'     => 'default|all',//��̨��¼
        'setting'     => 'setting|all',//��վ����
        'region'       => 'region|all',//��������
        'payment'    => 'payment|all',//֧����ʽ
        'theme'     => 'theme|all',//��������
        'mailtemplate'   => 'mailtemplate|all',//�ʼ�ģ��
        'template'  => 'template|all',//ģ��༭
    ),
    'goods_admin' => array
    (
        'gcategory'    => 'gcategory|all',//�������
        'brand' => 'brand|all',//Ʒ�ƹ���
        'goods'    => 'goods|all',//��Ʒ����
        'recommend'    => 'recommend|all',//�Ƽ�����
    	'audit'	=> 'audit|all', //��Ʒ���
    	'promotion_goods' => 'promotion|all', //��������
    	'promotion_auit'  => 'promotion|all',
    	'OutputExcel'	  => 'OutputExcel|all',
        'salesdetail'     => 'salesdetail|all',
        'sales_rank'     => 'sales_rank|all',
        'order_summary'     => 'order_summary|all',
    ),
    'store_admin' => array
    (
        'sgrade'    => 'sgrade|all',//���̵ȼ�
        'scategory'     => 'scategory|all',//���̷���
        'store'   => 'store|all',//���̹���
    	'supply'	=> 'supply|all',//��Ӧ�̹���
    	'storemanage_statistics' => 'storemanage_statistics|all',//���̶�������
    	'store_order_manage' => 'store_order_manage|all', //���̶������
    	'StoreOrder' => 'StoreOrder|all' //�̳Ƕ�������
    ),
    'member' => array
    (
        'user'  => 'user|all',//��Ա����
        'admin' => 'admin|all',//����Ա����
        'notice' => 'notice|all',//��Ա֪ͨ
    	'smsSend' => 'smsSend|all',
    	'emSend' => 'emSend|all',
    	'vowwall'=>'vowwall|all',
    	'sendcredit' => 'sendcredit|all', //��������
    	'credit_view'=> 'credit_view|all',//���ֱ䶯�鿴
    	'credit_verify' => 'credit_verify|all',//���ֱ䶯���
    	'withdraw_verify' => 'widthdraw_verify|all',//�������
    ),
    'service' => array
    (
    	'service' => 'service|all',
    ),
    'website' => array
    (
        'acategory'    => 'acategory|all',//���·���
        'article'      => array('article' => 'article|all', 'upload' => array('comupload' => 'comupload|all', 'swfupload' => 'swfupload|all')),//���¹���
        'partner'      => 'partner|all',//�������
        'navigation'   => 'navigation|all',//ҳ�浼��
        'db'           => 'db|all',//���ݿ�
        'groupbuy'     => 'groupbuy|all',//�Ź�
        'consulting'   => 'consulting|all',//��ѯ
        'share_link'   => 'share|all',//�������
    ),

    'external' => array
    (
        'plugin' => 'plugin|all',//�������
        'module'   => 'module|all',//ģ�����
        'widget'   => 'widget|all',//�Ҽ�����
    	'question' => 'question|all',//�ʾ������� 
    	'testpaper' => 'testpaper|all',//�ʾ������� 
    	'vote' => 'vote|all',//ͶƱ���� 
    ),
    'clear_cache' =>array
    (
        'clear_cache' => 'clear_cache|all',//��ջ���
    ),
    
    'channel' =>array //�����̹���
    (
//    	'channel_index' => 'channel|index',  //��������ҳ
//      'channel_verify' => 'channel|verify',//���������
//    	'channel_add'    =>  'channel|add',  //����������
//    	'channel_list'   => array('channel_list'=>'channel|channellist','infos'=>array('channel_info'=>'channel|info','channel_sendmsg'=>'channel|sendmsg')),  //�������б�
//    	'channel_charge' => 'channel|manager', //�Ź�Ա����
//    	'channel_income' => 'channel|transfer_accounts', //�����Ƽ������б�
//    	'channel_incomeset' => 'channel|store_order', //�����������
		'channel'	=>'channel|all',
    	'manager'	=>'channel|all',
    ),
	'caiwu' =>array //�������
	(
	    'jiameng'	=>'store_statistics|all',
	    'shop_sell'	=>'shop_sell|all',
	    'user_memage' => 'user_memage|all',
	    'user_verify' => 'user_verify|all',
	    'user_view' => 'user_view|all',
	    'withdraw_verify' => 'withdraw_verify|all',
	    'bankaccount_verify' => 'bankaccount_verify|all',
	    'verify_last_manager' => 'verify_last_manager|all',
	    'ad_money' => 'ad_money|all',
    	'return_money' => 'return_money|all', //�˿��
		'financeStore' => 'financeStore|all', //������˵���	
	),
    'wuliu' =>array //��������
    (
    	 'user_order'   => 'order|all',//��������
    	 'store_order' => 'store_order|all', //���̽����������� 
    ),
    'seo'	=> array(
    	'goods_seo' => 'seo|all', //��ƷSEO�Ż�
    	'youlian'	   => 'frendlink|all',//��������	
    ),
    /*
    'groupbuy' => array(//�Ź�
    	'groupproject' => 'groupproject|all',//��Ŀ���� 
    	'grouporder' => 'grouporder|all',// �Ź���������
    )*/
);
?>