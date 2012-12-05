<?php

/**
 * 派啦币交易专区公告
 *
 * @return  array   $image_list
 */
class Plb_afficheWidget extends BaseWidget
{
    var $_name = 'plb_affiche';
 	function _get_data()
	    {
	    	$credit = array();
	    	$activity = array();
	        $article_mod = & m("article");
	        
	        
	        $n= $article_mod->find(array(
	        	'join' => 'belongs_to_acategory',
	        	'conditions' =>	'acategory.cate_id = 30 AND article.if_show=1',
	        	'order'	=> 'article.add_time DESC',
	        	'fields'	=> 'article.article_id,article.title,article.cate_id,article.add_time,acategory.cate_id,acategory.cate_name',
	        	'limit' => 4
	        ));
	        foreach($n as $value) {
		        foreach($value as $k=>$v) {
		        	if($k == 'add_time') {
		        		$value[$k] = date("y-m-d",$v);
		        		$credit[] = $value;
		        	}
		        }
	        }
	        $data['credit'] = $credit;
	        //var_dump($data);
	        return $data;
	    }
	 function parse_config($input)
	    {
	        //return $input;
	    }
	    

}

?>
