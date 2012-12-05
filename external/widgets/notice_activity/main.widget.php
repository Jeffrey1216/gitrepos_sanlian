<?php

/**
 * ÂÖ²¥Í¼Æ¬¹Ò¼þ
 *
 * @return  array   $image_list
 */
class Notice_activityWidget extends BaseWidget
{
    var $_name = 'notice_activity';

    function _get_data()
    {
    	$notice = array();
    	$activity = array();
        $article_mod = & m("article");
        
        
        $n= $article_mod->find(array(
        	'join' => 'belongs_to_acategory',
        	'conditions' =>	'acategory.cate_id = 2 AND article.if_show=1',
        	'order'	=> 'article.add_time DESC',
        	'fields'	=> 'article.article_id,article.title,article.cate_id,article.add_time,acategory.cate_id,acategory.cate_name',
        	'limit' => 5
        ));
        foreach($n as $value) {
	        foreach($value as $k=>$v) {
	        	if($k == 'add_time') {
	        		$value[$k] = date("y-m-d",$v);
	        		$notice[] = $value;
	        	}
	        }
        }
        $a = $article_mod->find(array(
        	'join' => 'belongs_to_acategory',
        	'conditions' =>	'acategory.cate_id = 12 AND article.if_show=1',
        	'order'	=> 'article.add_time DESC',
        	'fields'	=> 'article.article_id,article.title,article.cate_id,article.add_time,acategory.cate_id,acategory.cate_name',
        	'limit' => 5,
        ));
    	foreach($a as $value) {
	        foreach($value as $k=>$v) {
	        	if($k == 'add_time') {
	        		$value[$k] = date("y-m-d",$v);
	        		$activity[] = $value;
	        	}
	        }
        }
        
        $data['notice'] = $notice;
        $data['activity'] = $activity;
        //var_dump($data['notice']);
        return $data;
    }

    function parse_config($input)
    {
        //return $input;
    }
	
    
}

?>