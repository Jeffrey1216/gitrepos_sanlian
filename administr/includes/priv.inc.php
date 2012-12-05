<?php

/**
 * PaiLa: 网站后台管理左侧菜单数据
 * ============================================================================
 * 版权所有 (C) 2010-2011 三联融创，并保留所有权利。
 * 网站地址: http://www.paila.com
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
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
        'default'     => 'default|all',//后台登录
        'setting'     => 'setting|all',//网站设置
        'region'       => 'region|all',//地区设置
        'payment'    => 'payment|all',//支付方式
        'theme'     => 'theme|all',//主题设置
        'mailtemplate'   => 'mailtemplate|all',//邮件模板
        'template'  => 'template|all',//模板编辑
    ),
    'goods_admin' => array
    (
        'gcategory'    => 'gcategory|all',//分类管理
        'brand' => 'brand|all',//品牌管理
        'goods'    => 'goods|all',//商品管理
        'recommend'    => 'recommend|all',//推荐类型
    	'audit'	=> 'audit|all', //商品审核
    	'promotion_goods' => 'promotion|all', //促销管理
    	'promotion_auit'  => 'promotion|all',
    	'OutputExcel'	  => 'OutputExcel|all',
        'salesdetail'     => 'salesdetail|all',
        'sales_rank'     => 'sales_rank|all',
        'order_summary'     => 'order_summary|all',
    ),
    'store_admin' => array
    (
        'sgrade'    => 'sgrade|all',//店铺等级
        'scategory'     => 'scategory|all',//店铺分类
        'store'   => 'store|all',//店铺管理
    	'supply'	=> 'supply|all',//供应商管理
    	'storemanage_statistics' => 'storemanage_statistics|all',//店铺订单管理
    	'store_order_manage' => 'store_order_manage|all', //店铺订单审核
    	'StoreOrder' => 'StoreOrder|all' //商城订单管理
    ),
    'member' => array
    (
        'user'  => 'user|all',//会员管理
        'admin' => 'admin|all',//管理员管理
        'notice' => 'notice|all',//会员通知
    	'smsSend' => 'smsSend|all',
    	'emSend' => 'emSend|all',
    	'vowwall'=>'vowwall|all',
    	'sendcredit' => 'sendcredit|all', //积分赠送
    	'credit_view'=> 'credit_view|all',//积分变动查看
    	'credit_verify' => 'credit_verify|all',//积分变动审核
    	'withdraw_verify' => 'widthdraw_verify|all',//提现审核
    ),
    'service' => array
    (
    	'service' => 'service|all',
    ),
    'website' => array
    (
        'acategory'    => 'acategory|all',//文章分类
        'article'      => array('article' => 'article|all', 'upload' => array('comupload' => 'comupload|all', 'swfupload' => 'swfupload|all')),//文章管理
        'partner'      => 'partner|all',//合作伙伴
        'navigation'   => 'navigation|all',//页面导航
        'db'           => 'db|all',//数据库
        'groupbuy'     => 'groupbuy|all',//团购
        'consulting'   => 'consulting|all',//咨询
        'share_link'   => 'share|all',//分享管理
    ),

    'external' => array
    (
        'plugin' => 'plugin|all',//插件管理
        'module'   => 'module|all',//模块管理
        'widget'   => 'widget|all',//挂件管理
    	'question' => 'question|all',//问卷题库管理 
    	'testpaper' => 'testpaper|all',//问卷题库管理 
    	'vote' => 'vote|all',//投票管理 
    ),
    'clear_cache' =>array
    (
        'clear_cache' => 'clear_cache|all',//清空缓存
    ),
    
    'channel' =>array //渠道商管理
    (
//    	'channel_index' => 'channel|index',  //渠道商首页
//      'channel_verify' => 'channel|verify',//渠道商审核
//    	'channel_add'    =>  'channel|add',  //渠道商新增
//    	'channel_list'   => array('channel_list'=>'channel|channellist','infos'=>array('channel_info'=>'channel|info','channel_sendmsg'=>'channel|sendmsg')),  //渠道商列表
//    	'channel_charge' => 'channel|manager', //团购员管理
//    	'channel_income' => 'channel|transfer_accounts', //渠道推荐收益列表
//    	'channel_incomeset' => 'channel|store_order', //渠道收益结算
		'channel'	=>'channel|all',
    	'manager'	=>'channel|all',
    ),
	'caiwu' =>array //财务管理
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
    	'return_money' => 'return_money|all', //退款功能
		'financeStore' => 'financeStore|all', //财务审核店铺	
	),
    'wuliu' =>array //物流管理
    (
    	 'user_order'   => 'order|all',//订单管理
    	 'store_order' => 'store_order|all', //商铺进货定单管理 
    ),
    'seo'	=> array(
    	'goods_seo' => 'seo|all', //商品SEO优化
    	'youlian'	   => 'frendlink|all',//友情链接	
    ),
    /*
    'groupbuy' => array(//团购
    	'groupproject' => 'groupproject|all',//项目管理 
    	'grouporder' => 'grouporder|all',// 团购定单管理
    )*/
);
?>