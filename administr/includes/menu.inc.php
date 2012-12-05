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
    // 设置
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
//            	'text'	=> '物流设置',
//            	'url'	=> 'index.php?app=distribution'
//            ),
            'scheduled_task' => array(
            	'text'	=> '计划任务',
            	'url'	=> 'index.php?app=scheduled'
            ),
        ),
    ),
    // 商品
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
                'text'  =>LANG::get('商品审核'),
                'url'   =>'index.php?app=audit',
            ),
            'promotion_audit'=>array(
                'text'  =>'促销审核',
                'url'   =>'index.php?app=promotion&act=audit',
            ),
            'promotion_goods'=>array(
                'text'  =>'产品促销',
                'url'   =>'index.php?app=promotion',
            ),
            'outputExcel'=>array(
                'text'  =>'商品信息导出',
                'url'   =>'index.php?app=OutputExcel',
            ),
	    'goodsSalesDetail'=>array(
                'text'  =>'商品销售明细',
                'url'   =>'index.php?app=salesdetail',
            ),
	    'goodsSalesRank'=>array(
                'text'  =>'商品销售排行',
                'url'   =>'index.php?app=sales_rank',
            ),
	    'memberOrderDetail'=>array(
                'text'  =>'会员订单汇总',
                'url'   =>'index.php?app=order_summary',
            ),
        ),
    ),
    // 店铺
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
            	'text'	=> '供应商管理 ',
            	'url'	=> 'index.php?app=supply',
            ),
            'storemanage_statistics' => array(
            	'text'	=> '店铺订单管理 ',
            	'url'	=> 'index.php?app=storemanage_statistics',
            ),
            'store_order_manage' => array(
                'text'  => '店铺进货订单审核',
                'url'   => 'index.php?app=store_order_manage&act=store_order',
           	 ),
           	'StoreOrder' => array(
                'text'  => '网上商城订单管理',
                'url'   => 'index.php?app=StoreOrder',
           	 ),
           	 'stock_destory' => array(
                'text'  => '库存销毁审核',
                'url'   => 'index.php?app=stock_destory',
           	 ),
        ),
    ),
    // 会员
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
                'text' => Lang::get('短信群发'),
                'url'  => 'index.php?app=smsSend',
             ),
             'email_send' => array(
             	'text' => Lang::get('邮件群发'),
             	'url'  => 'index.php?app=emSend',
             ),
//             'vow_wall'=> array(
//             	'text' => Lang::get('许愿墙'),
//             	'url' => 'index.php?app=vowwall',
//             ),
             'sendcredit' => array(
             	'text' => '积分变动',
             	'url' => 'index.php?app=sendcredit',
             ),
//             'salesman' => array(
//             	'text' => '业务员管理',
//             	'url' => 'index.php?app=salesman',
//             ),
             'credit_view' => array(
                'text'  => '积分变动查看',
                'url'   => 'index.php?app=credit_view'
            ),
            'credit_verify' => array(
                'text'  => '积分变动审核',
                'url'   => 'index.php?app=credit_verify'
            ),
           
            
        ),
    ),
    // 交易
    'trade' => array(
        'text'      => Lang::get('trade'),
        'default'   => 'service',
        'children'  => array(
/*       		'order_manage' => array(
       				'text'  => Lang::get('order_manage'),
        				'url'	=> 'index.php?app=order'
       		),
        	'store_order_manage' => array(
        				'text'	=> '商铺进货定单管理',
        				'url'	=> 'index.php?app=store_order'
        	),
			
            'allocate' => array(
                'text'  => Lang::get('订单分派'),
                'url'   => 'index.php?app=allocate'
            ),
            'credit' => array(
                'text'  => '派啦币交易订单',
                'url'   => 'index.php?app=credit'
            ),   
            'balance_credit_log' => array(
            	'text'	=> '余额积分记录',
            	'url'	=> 'index.php?app=balance_credit_log'
            ),
            */ 
   			'service' => array(
            	'text'	=> '客服管理',
            	'url'	=> 'index.php?app=service'
            ),
            'assess' => array(
            	'text'	=> '商品评价',
            	'url'	=> 'index.php?app=assess'
            ),
            'consulting' => array(
                'text'  =>  LANG::get('consulting'),
                'url'   => 'index.php?app=consulting',
            ),
            'complain' => array(
            	'text'	=> '投诉管理',
            	'url'	=> 'index.php?app=complain'
            ),
        ),
    ),
    // 网站
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
    // 扩展
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
                'text'  => '问卷题库管理',
                'url'   => 'index.php?app=question',
            ),
            'testpaper' => array(
                'text'  => '问卷试卷管理',
                'url'   => 'index.php?app=testpaper',
            ),
            'vote' => array(
                'text'  => '投票管理',
                'url'   => 'index.php?app=vote',
            ),
        ),
    ),
    //财务管理
    'finance' => array(
        'text'      => '财务管理',
        'default'   => 'store_statistics',
        'children'  => array(
            'store_statistics' => array(
                'text'  => '加盟店统计',
                'url'   => 'index.php?app=store_statistics',
            ),
            'shop_sell' => array(
                'text'  => '商城直销财务',
                'url'   => 'index.php?app=shop_sell',
            ),
            'store_order_manage' => array(
                'text'  => '店铺进货订单',
                'url'   => 'index.php?app=store_statistics&act=store_order',
           	 ),
           	 'user_memage' => array(
                'text'  => '会员账户管理',
                'url'   => 'index.php?app=user_memage',
           	 ),
           	 'user_verify' => array(
                'text'  => '会员账户变动审核',
                'url'   => 'index.php?app=user_verify',
           	 ),
           	 'user_view' => array(
                'text'  => '会员账户变动查看',
                'url'   => 'index.php?app=user_view',
           	 ),
//	         'withdraw_verify' => array(
//                'text'  => '提现审核',
//                'url'   => 'index.php?app=withdraw_verify'
//            ),
//	     'bankaccount_verify' => array(
//                'text'  => '银行账户审核',
//                'url'   => 'index.php?app=bankaccount_verify'
//            ),
             'verify_last_manager' => array(
                'text'  => '团购员审核',
                'url'   => 'index.php?app=verify_last_manager'
            ),
            'ad_money' => array(
                'text'  => '广告费入账',
                'url'   => 'index.php?app=ad_money'
            ),
            'return_money' => array(
                'text'  => Lang::get('退款功能'),
                'url'   => 'index.php?app=return_money'
            ),
            'financeStore' => array(
                'text'  => Lang::get('店铺管理'),
                'url'   => 'index.php?app=financeStore'
            ),
        ),
    ),
  // 渠道商
 	    'partner' => array(
        'text'      => '渠道管理',
        'default'   => 'channel',
        'children'  => array(
            'channel' => array(
                'text'  => '渠道商管理',
                'url'   => 'index.php?app=channel',
            ),
           /*'fee' => array(
                'text'  => '渠道数据管理',
                'url'   => 'index.php?app=channel&act=fee',
            ),
            'charge' => array(
                'text'  => '商户缴费管理',
                'url'   => 'index.php?app=channel&act=charge',
            ),
            'income' => array(
                'text'  => '商户收益管理',
                'url'   => 'index.php?app=channel&act=income',
            ),*/
            'manager' => array(
                'text'  => '团购员管理',
                'url'   => 'index.php?app=channel&act=manager',
            ),
            /*
            'transfer_accounts' => array(
                'text'  => '转账记录',
                'url'   => 'index.php?app=channel&act=transfer_accounts',
            ),
            */
        ),
    ),
    
			'logistics'=>array(
				'text'=>'物流管理',
				'default'=>'order_manage',
				'children'=>array(
						'order_manage'=>array(
								'text'=>'商城用户订单',
								'url'=>'index.php?app=order'
						),
						'store_order_manage' => array(
                			'text'  => '店铺进货订单',
                			'url'   => 'index.php?app=store_order',
           				 ),
           				 'refunds' => array(
                			'text'  => '退货订单',
                			'url'   => 'index.php?app=refunds',
           				 ),
					),
			),
			'seo'=>array(
				'text'=>'SEO优化',
				'default'=>'goods_seo',
				'children'=>array(
						'goods_seo'=>array(
								'text'=>'商品SEO优化',
								'url'=>'index.php?app=seo'
						),
						'youlian' => array(
                			'text'  => Lang::get('友情链接'),
                			'url'   => 'index.php?app=frendlink',
           				 ),
					),
			),
    //团购,秒杀
//    'groupbuy' => array(
//    	'text'	=> '团购秒杀',
//		'default' => 'groupproject',
//    	'children' => array(
//   		'groupproject' => array(
//   			'text' => '项目管理',
//   			'url' => 'index.php?app=groupproject',
//   		),
//   		'grouporder' => array(
//   			'text' => '订单管理',
//   			'url' => 'index.php?app=grouporder',
//    		),
//    	),
//    ),
//    'brandmandate' => array(
//    	'text' => '品牌托管',	
//    	'default' => 'brandmandate',
//		'children'=> array(	
//    		'brandmandate' => array(
//				'text' => '品牌托管管理',
//    			'url'=> 'index.php?app=brandmandate',	
//    		),
//    		'brand_goods' => array(
//				'text' => '品牌托管商品管理',
//    			'url'=> 'index.php?app=brandmandate&act=brand_index',	
//    		),					    	
//    	),
//    ),
    
);

?>
