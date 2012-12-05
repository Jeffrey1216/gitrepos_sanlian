<?php

/**
 * ÂÖ²¥Í¼Æ¬¹Ò¼þ
 *
 * @return  array   $image_list
 */
class Agro_activityWidget extends BaseWidget
{
    var $_name = 'agro_activity';

    function _get_data()
    {
    	$activity = array();
        $article_mod = & m("article");
        
            $a = $article_mod->find(array(
        	'join' => 'belongs_to_acategory',
        	'conditions' =>	'acategory.cate_id = 42 AND article.if_show=1',
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
        $data['activity'] = $activity;
        return $data;
    }
}

?>