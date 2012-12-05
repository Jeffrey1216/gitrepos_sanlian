<?php
return array(
    'dashboard' => array(
        'text'      => Lang::get('dashboard'),
        'subtext'   => Lang::get('offen_used'),
        'default'   => 'welcome',
        'children'  => array(
            'welcome'   => array(
                'text'  => Lang::get('welcome_page'),
                'url'   => 'index.php?act=welcome',
            ),
            'aboutus'   => array(
                'text'  => Lang::get('aboutus_page'),
                'url'   => 'index.php?act=aboutus',
            ),
            'base_setting'  => array(
                'parent'=> 'setting',
                'text'  => Lang::get('base_setting'),
                'url'   => 'index.php?app=setting&act=base_setting',
            ),
            'user_manage' => array(
                'text'  => Lang::get('user_manage'),
                'parent'=> 'user',
                'url'   => 'index.php?app=user',
            ),
            'store_manage'  => array(
                'text'  => Lang::get('store_manage'),
                'parent'=> 'store',
                'url'   => 'index.php?app=store',
            ),
            'goods_manage'  => array(
                'text'  => Lang::get('goods_manage'),
                'parent'=> 'goods',
                'url'   => 'index.php?app=goods',
            ),
            'order_manage' => array(
                'text'  => Lang::get('order_manage'),
                'parent'=> 'trade',
                'url'   => 'index.php?app=order'
            ),
        ),
    ),    
    // ����
    'setting'   => array(
        'text'      => Lang::get('setting'),
        'default'   => 'base_setting',
        'children'  => array(
            'base_setting'  => array(
                'text'  => Lang::get('base_setting'),
                'url'   => 'index.php?app=setting&act=base_setting',
            ),
            'region' => array(
                'text'  => Lang::get('region'),
                'url'   => 'index.php?app=region',
            ),
            'payment' => array(
                'text'  => Lang::get('payment'),
                'url'   => 'index.php?app=payment',
            ),
            'theme' => array(
                'text'  => Lang::get('theme'),
                'url'   => 'index.php?app=theme',
            ),
            'template' => array(
                'text'  => Lang::get('template'),
                'url'   => 'index.php?app=template',
            ),
            'mailtemplate' => array(
                'text'  => Lang::get('noticetemplate'),
                'url'   => 'index.php?app=mailtemplate',
            ),
//            'physical_distribution' => array(
//            	'text'	=> '��������',
//            	'url'	=> 'index.php?app=distribution'
//            ),
            'scheduled_task' => array(
            	'text'	=> '�ƻ�����',
            	'url'	=> 'index.php?app=scheduled'
            ),
        ),
    ),
    // ��Ʒ
    'goods' => array(
        'text'      => Lang::get('goods'),
        'default'   => 'goods_manage',
        'children'  => array(
            'gcategory' => array(
                'text'  => Lang::get('gcategory'),
                'url'   => 'index.php?app=gcategory',
            ),
            'brand' => array(
                'text'  => Lang::get('brand'),
                'url'   => 'index.php?app=brand',
            ),
            'goods_manage' => array(
                'text'  => Lang::get('goods_manage'),
                'url'   => 'index.php?app=goods',
            ),
//            'recommend_type' => array(
//                'text'  => LANG::get('recommend_type'),
//                'url'   => 'index.php?app=recommend'
//            ),
			'goods_audit'=>array(
                'text'  =>LANG::get('��Ʒ���'),
                'url'   =>'index.php?app=audit',
            ),
            'promotion_audit'=>array(
                'text'  =>'�������',
                'url'   =>'index.php?app=promotion&act=audit',
            ),
            'promotion_goods'=>array(
                'text'  =>'��Ʒ����',
                'url'   =>'index.php?app=promotion',
            ),
            'outputExcel'=>array(
                'text'  =>'��Ʒ��Ϣ����',
                'url'   =>'index.php?app=OutputExcel',
            ),
	    'goodsSalesDetail'=>array(
                'text'  =>'��Ʒ������ϸ',
                'url'   =>'index.php?app=salesdetail',
            ),
	    'goodsSalesRank'=>array(
                'text'  =>'��Ʒ��������',
                'url'   =>'index.php?app=sales_rank',
            ),
	    'memberOrderDetail'=>array(
                'text'  =>'��Ա��������',
                'url'   =>'index.php?app=order_summary',
            ),
        ),
    ),
    // ����
    'store'     => array(
        'text'      => Lang::get('store'),
        'default'   => 'store_manage',
        'children'  => array(
            'sgrade' => array(
                'text'  => Lang::get('sgrade'),
                'url'   => 'index.php?app=sgrade',
            ),
            'scategory' => array(
                'text'  => Lang::get('scategory'),
                'url'   => 'index.php?app=scategory',
            ),
            'store_manage'  => array(
                'text'  => Lang::get('store_manage'),
                'url'   => 'index.php?app=store',
            ),
            'supply_manage' => array(
            	'text'	=> '��Ӧ�̹��� ',
            	'url'	=> 'index.php?app=supply',
            ),
            'storemanage_statistics' => array(
            	'text'	=> '���̶������� ',
            	'url'	=> 'index.php?app=storemanage_statistics',
            ),
            'store_order_manage' => array(
                'text'  => '���̽����������',
                'url'   => 'index.php?app=store_order_manage&act=store_order',
           	 ),
           	'StoreOrder' => array(
                'text'  => '�����̳Ƕ�������',
                'url'   => 'index.php?app=StoreOrder',
           	 ),
           	 'stock_destory' => array(
                'text'  => '����������',
                'url'   => 'index.php?app=stock_destory',
           	 ),
        ),
    ),
    // ��Ա
    'user' => array(
        'text'      => Lang::get('user'),
        'default'   => 'user_manage',
        'children'  => array(
            'user_manage' => array(
                'text'  => Lang::get('user_manage'),
                'url'   => 'index.php?app=user',
            ),
            'admin_manage' => array(
                'text' => Lang::get('admin_manage'),
                 'url'   => 'index.php?app=admin',
             ),
             'user_notice' => array(
                'text' => Lang::get('user_notice'),
                'url'  => 'index.php?app=notice',
             ),
             'user_send' => array(
                'text' => Lang::get('����Ⱥ��'),
                'url'  => 'index.php?app=smsSend',
             ),
             'email_send' => array(
             	'text' => Lang::get('�ʼ�Ⱥ��'),
             	'url'  => 'index.php?app=emSend',
             ),
//             'vow_wall'=> array(
//             	'text' => Lang::get('��Ըǽ'),
//             	'url' => 'index.php?app=vowwall',
//             ),
             'sendcredit' => array(
             	'text' => '���ֱ䶯',
             	'url' => 'index.php?app=sendcredit',
             ),
//             'salesman' => array(
//             	'text' => 'ҵ��Ա����',
//             	'url' => 'index.php?app=salesman',
//             ),
             'credit_view' => array(
                'text'  => '���ֱ䶯�鿴',
                'url'   => 'index.php?app=credit_view'
            ),
            'credit_verify' => array(
                'text'  => '���ֱ䶯���',
                'url'   => 'index.php?app=credit_verify'
            ),
           
            
        ),
    ),
    // ����
    'trade' => array(
        'text'      => Lang::get('trade'),
        'default'   => 'service',
        'children'  => array(
/*       		'order_manage' => array(
       				'text'  => Lang::get('order_manage'),
        				'url'	=> 'index.php?app=order'
       		),
        	'store_order_manage' => array(
        				'text'	=> '���̽�����������',
        				'url'	=> 'index.php?app=store_order'
        	),
			
            'allocate' => array(
                'text'  => Lang::get('��������'),
                'url'   => 'index.php?app=allocate'
            ),
            'credit' => array(
                'text'  => '�����ҽ��׶���',
                'url'   => 'index.php?app=credit'
            ),   
            'balance_credit_log' => array(
            	'text'	=> '�����ּ�¼',
            	'url'	=> 'index.php?app=balance_credit_log'
            ),
            */ 
   			'service' => array(
            	'text'	=> '�ͷ�����',
            	'url'	=> 'index.php?app=service'
            ),
            'assess' => array(
            	'text'	=> '��Ʒ����',
            	'url'	=> 'index.php?app=assess'
            ),
            'consulting' => array(
                'text'  =>  LANG::get('consulting'),
                'url'   => 'index.php?app=consulting',
            ),
            'complain' => array(
            	'text'	=> 'Ͷ�߹���',
            	'url'	=> 'index.php?app=complain'
            ),
        ),
    ),
    // ��վ
    'website' => array(
        'text'      => Lang::get('website'),
        'default'   => 'acategory',
        'children'  => array(
            'acategory' => array(
                'text'  => Lang::get('acategory'),
                'url'   => 'index.php?app=acategory',
            ),
            'article' => array(
                'text'  => Lang::get('article'),
                'url'   => 'index.php?app=article',
            ),
            'partner' => array(
                'text'  => Lang::get('partner'),
                'url'   => 'index.php?app=partner',
            ),
            'navigation' => array(
                'text'  => Lang::get('navigation'),
                'url'   => 'index.php?app=navigation',
            ),
//            'db' => array(
//                'text'  => Lang::get('db'),
//                'url'   => 'index.php?app=db&amp;act=backup',
//            ),
            'groupbuy' => array(
                'text' => Lang::get('groupbuy'),
                'url'  => 'index.php?app=groupbuy',
            ),
            'consulting' => array(
                'text'  =>  LANG::get('consulting'),
                'url'   => 'index.php?app=consulting',
            ),
//            'apconsult' => array(
//                'text'  =>  LANG::get('apconsult'),
//                'url'   => 'index.php?app=apconsult',
//            ),
            'share_link' => array(
                'text'  =>  LANG::get('share_link'),
                'url'   => 'index.php?app=share',
            ),
        ),
    ),
    // ��չ
    'extend' => array(
        'text'      => Lang::get('extend'),
        'default'   => 'plugin',
        'children'  => array(
            'plugin' => array(
                'text'  => Lang::get('plugin'),
                'url'   => 'index.php?app=plugin',
            ),
            'module' => array(
                'text'  => Lang::get('module'),
                'url'   => 'index.php?app=module&act=manage',
            ),
            'widget' => array(
                'text'  => Lang::get('widget'),
                'url'   => 'index.php?app=widget',
            ),
            'question' => array(
                'text'  => '�ʾ�������',
                'url'   => 'index.php?app=question',
            ),
            'testpaper' => array(
                'text'  => '�ʾ��Ծ����',
                'url'   => 'index.php?app=testpaper',
            ),
            'vote' => array(
                'text'  => 'ͶƱ����',
                'url'   => 'index.php?app=vote',
            ),
        ),
    ),
    //�������
    'finance' => array(
        'text'      => '�������',
        'default'   => 'store_statistics',
        'children'  => array(
            'store_statistics' => array(
                'text'  => '���˵�ͳ��',
                'url'   => 'index.php?app=store_statistics',
            ),
            'shop_sell' => array(
                'text'  => '�̳�ֱ������',
                'url'   => 'index.php?app=shop_sell',
            ),
            'store_order_manage' => array(
                'text'  => '���̽�������',
                'url'   => 'index.php?app=store_statistics&act=store_order',
           	 ),
           	 'user_memage' => array(
                'text'  => '��Ա�˻�����',
                'url'   => 'index.php?app=user_memage',
           	 ),
           	 'user_verify' => array(
                'text'  => '��Ա�˻��䶯���',
                'url'   => 'index.php?app=user_verify',
           	 ),
           	 'user_view' => array(
                'text'  => '��Ա�˻��䶯�鿴',
                'url'   => 'index.php?app=user_view',
           	 ),
//	         'withdraw_verify' => array(
//                'text'  => '�������',
//                'url'   => 'index.php?app=withdraw_verify'
//            ),
//	     'bankaccount_verify' => array(
//                'text'  => '�����˻����',
//                'url'   => 'index.php?app=bankaccount_verify'
//            ),
             'verify_last_manager' => array(
                'text'  => '�Ź�Ա���',
                'url'   => 'index.php?app=verify_last_manager'
            ),
            'ad_money' => array(
                'text'  => '��������',
                'url'   => 'index.php?app=ad_money'
            ),
            'return_money' => array(
                'text'  => Lang::get('�˿��'),
                'url'   => 'index.php?app=return_money'
            ),
            'financeStore' => array(
                'text'  => Lang::get('���̹���'),
                'url'   => 'index.php?app=financeStore'
            ),
        ),
    ),
  // ������
 	    'partner' => array(
        'text'      => '��������',
        'default'   => 'channel',
        'children'  => array(
            'channel' => array(
                'text'  => '�����̹���',
                'url'   => 'index.php?app=channel',
            ),
           /*'fee' => array(
                'text'  => '�������ݹ���',
                'url'   => 'index.php?app=channel&act=fee',
            ),
            'charge' => array(
                'text'  => '�̻��ɷѹ���',
                'url'   => 'index.php?app=channel&act=charge',
            ),
            'income' => array(
                'text'  => '�̻��������',
                'url'   => 'index.php?app=channel&act=income',
            ),*/
            'manager' => array(
                'text'  => '�Ź�Ա����',
                'url'   => 'index.php?app=channel&act=manager',
            ),
            /*
            'transfer_accounts' => array(
                'text'  => 'ת�˼�¼',
                'url'   => 'index.php?app=channel&act=transfer_accounts',
            ),
            */
        ),
    ),
    
			'logistics'=>array(
				'text'=>'��������',
				'default'=>'order_manage',
				'children'=>array(
						'order_manage'=>array(
								'text'=>'�̳��û�����',
								'url'=>'index.php?app=order'
						),
						'store_order_manage' => array(
                			'text'  => '���̽�������',
                			'url'   => 'index.php?app=store_order',
           				 ),
           				 'refunds' => array(
                			'text'  => '�˻�����',
                			'url'   => 'index.php?app=refunds',
           				 ),
					),
			),
			'seo'=>array(
				'text'=>'SEO�Ż�',
				'default'=>'goods_seo',
				'children'=>array(
						'goods_seo'=>array(
								'text'=>'��ƷSEO�Ż�',
								'url'=>'index.php?app=seo'
						),
						'youlian' => array(
                			'text'  => Lang::get('��������'),
                			'url'   => 'index.php?app=frendlink',
           				 ),
					),
			),
    //�Ź�,��ɱ
//    'groupbuy' => array(
//    	'text'	=> '�Ź���ɱ',
//		'default' => 'groupproject',
//    	'children' => array(
//   		'groupproject' => array(
//   			'text' => '��Ŀ����',
//   			'url' => 'index.php?app=groupproject',
//   		),
//   		'grouporder' => array(
//   			'text' => '��������',
//   			'url' => 'index.php?app=grouporder',
//    		),
//    	),
//    ),
//    'brandmandate' => array(
//    	'text' => 'Ʒ���й�',	
//    	'default' => 'brandmandate',
//		'children'=> array(	
//    		'brandmandate' => array(
//				'text' => 'Ʒ���йܹ���',
//    			'url'=> 'index.php?app=brandmandate',	
//    		),
//    		'brand_goods' => array(
//				'text' => 'Ʒ���й���Ʒ����',
//    			'url'=> 'index.php?app=brandmandate&act=brand_index',	
//    		),					    	
//    	),
//    ),
    
);

?>
