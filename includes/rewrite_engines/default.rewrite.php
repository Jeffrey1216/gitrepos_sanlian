<?php

/**
 *    Ĭ��Rewrite����
 *
 *    @author    Garbin
 *    @usage    none
 */

/*

##### Rewrite Rule #####

RewriteEngine On

#��Ʒ����
RewriteRule ^goods/([0-9]+)/?$ index.php?app=goods&id=$1 [L]
RewriteRule ^goods/([0-9]+)/([^/]+)/?$ index.php?app=goods&id=$1&act=$2 [L]
RewriteRule ^goods/([0-9]+)/([^/]+)/page_([^/]+)/?$ index.php?app=goods&id=$1&act=$2&page=$3 [L]
RewriteRule ^groupbuy/([0-9]+)/?$ index.php?app=groupbuy&id=$1 [L]

#����
RewriteRule ^category/goods/?$ index.php?app=category [L]
RewriteRule ^category/(.*)/?$ index.php?app=category&act=$1 [L]

#Ʒ��
RewriteRule ^brand/?$ index.php?app=brand [L]

#����
RewriteRule ^article/([0-9]+).html$ index.php?app=article&act=view&article_id=$1 [L]

#����ҳ��
RewriteRule ^store/([0-9]+)/?$ index.php?app=store&id=$1 [L]
RewriteRule ^store/article/([0-9]+).html$ index.php?app=store&act=article&id=$1 [L]
RewriteRule ^store/([0-9]+)/credit/?$ index.php?app=store&id=$1&act=credit [L]
RewriteRule ^store/([0-9]+)/credit/page_([^/]+)/?$ index.php?app=store&id=$1&act=credit&page=$2 [L]
RewriteRule ^store/([0-9]+)/credit/([0-9]+)/?$ index.php?app=store&id=$1&act=credit&eval=$2 [L]
RewriteRule ^store/([0-9]+)/credit/([0-9]+)/page_([^/]+)/?$ index.php?app=store&id=$1&act=credit&eval=$2&page=$3 [L]
RewriteRule ^store/([0-9]+)/goods/?$ index.php?app=store&id=$1&act=search [L]
RewriteRule ^store/([0-9]+)/goods/page_([^/]+)/?$ index.php?app=store&id=$1&act=search&page=$2 [L]
RewriteRule ^store/([0-9]+)/category/([0-9]+)/?$ index.php?app=store&id=$1&act=search&cate_id=$2 [L]
RewriteRule ^store/([0-9]+)/category/([0-9]+)/page_([^/]+)/?$ index.php?app=store&id=$1&act=search&cate_id=$2&page=$3 [L]
RewriteRule ^store/([0-9]+)/groupbuy/?$ index.php?app=store&id=$1&act=groupbuy [L]
RewriteRule ^store/([0-9]+)/groupbuy/page_([^/]+)/?$ index.php?app=store&id=$1&act=groupbuy&page=$2 [L]

*/

class DefaultRewrite extends BaseRewrite
{
    /* Rewrite�����ͼ����¼������Ӧ��rule���� */
    var $_rewrite_maps  = array(
        /* '{app����}_{�����б�����������"_"����}' => '��д��������', */

        /* ������ҳ */
        'store_id'  => 'store_index',

        /* ��Ʒ���� */
        'goods_id'  => 'goods_detail',
        'groupbuy_id'   => 'groupbuy_detail',

        /* ��Ʒ���� */
        'category'  => 'goods_cate',

        /* Ʒ���б� */
        'brand'     => 'brand_list',

        /* ���̷��� */
        'category_act' => 'store_cate',

        /* �������� */
        'article_act_id' => 'article_detail',
        'article_act_article_id' => 'article_detail',

        /* �������� */
        'store_act_id'  => REWRITE_RULE_FN,
        'store_act_id_page' => REWRITE_RULE_FN,
        'store_act_eval_id' => 'store_credit_eval',
        'store_act_eval_id_page'    => 'store_credit_eval_page',
        'store_act_cate_id_id'  => 'store_goodscate',
        'store_act_cate_id_id_page' => 'store_goodscate_page',
        'goods_act_id'      => 'goods_extra_info',
        'goods_act_id_page' => 'goods_extra_info_page',
    );

    /* Rewrite rules����¼��������Ϣ */
    var $_rewrite_rules = array(
        'store_index'   => array(
            'rewrite'   => 'store/%id%',
        ),
        'goods_detail'  => array(
            'rewrite'   => 'goods/%id%',
        ),
        'goods_cate'    => array(
            'rewrite'   => 'category/goods',
        ),
        'brand_list'    => array(
            'rewrite'   => 'brand',
        ),
        'store_cate'    => array(
            'rewrite'   => 'category/%act%',
        ),
        'article_detail'    => array(
            'rewrite'   => 'article/%article_id%.html',
        ),
        'store_article' => array(
            'rewrite'   => 'store/article/%id%.html',
        ),
        'store_credit'  => array(
            'rewrite'   => 'store/%id%/credit',
        ),
        'store_credit_page'  => array(
            'rewrite'   => 'store/%id%/credit/page_%page%',
        ),
        'store_credit_eval'  => array(
            'rewrite'   => 'store/%id%/credit/%eval%',
        ),
        'store_credit_eval_page'    => array(
            'rewrite'   => 'store/%id%/credit/%eval%/page_%page%',
        ),
        'store_goodslist'   => array(
            'rewrite'   => 'store/%id%/goods',
        ),
        'store_goodslist_page'   => array(
            'rewrite'   => 'store/%id%/goods/page_%page%',
        ),
        'store_goodscate'   => array(
            'rewrite'   => 'store/%id%/category/%cate_id%',
        ),
        'store_goodscate_page'   => array(
            'rewrite'   => 'store/%id%/category/%cate_id%/page_%page%',
        ),
        'goods_extra_info' => array(
            'rewrite'   => 'goods/%id%/%act%',
        ),
        'goods_extra_info_page' => array(
            'rewrite'   => 'goods/%id%/%act%/page_%page%',
        ),
        'groupbuy_detail'   =>  array(
            'rewrite'   => 'groupbuy/%id%',
        ),
        'store_groupbuy'   =>  array(
            'rewrite'   => 'store/%id%/groupbuy',
        ),
        'store_groupbuy_page'   =>  array(
            'rewrite'   => 'store/%id%/groupbuy/page_%page%',
        ),
    );


    function rule_store_act_id($params)
    {
        $rule_name = '';
        switch ($params['act'])
        {
            case 'article':
                $rule_name = 'store_article';
            break;
            case 'credit':
                $rule_name = 'store_credit';
            break;
            case 'search':
                $rule_name = 'store_goodslist';
            break;
            case 'groupbuy':
                $rule_name = 'store_groupbuy';
            break;
        }

        return $rule_name;
    }

    function rule_store_act_id_page($params)
    {
        $rule_name = '';
        switch ($params['act'])
        {
            case 'credit':
                $rule_name = 'store_credit_page';
            break;
            case 'search':
                $rule_name = 'store_goodslist_page';
            break;
            case 'groupbuy':
                $rule_name = 'store_groupbuy_page';
            break;
        }

        return $rule_name;
    }
}

?>
