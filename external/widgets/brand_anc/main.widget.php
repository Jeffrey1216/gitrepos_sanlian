<?php

/**
 * 品牌托管公告
 *
 * @return  array   $image_list
 */
class Brand_ancWidget extends BaseWidget
{
    var $_name = 'brand_anc';
 	function _get_data()
	    {
	    	$brand = array();
	    	$activity = array();
	        $article_mod = & m("article");
	        
	        
	        $n= $article_mod->find(array(
	        	'join' => 'belongs_to_acategory',
	        	'conditions' =>	'acategory.cate_id = 25 AND article.if_show=1',
	        	'order'	=> 'article.add_time DESC',
	        	'fields'	=> 'article.article_id,article.title,article.cate_id,article.add_time,acategory.cate_id,acategory.cate_name',
	        	'limit' => 8
	        ));
	        foreach($n as $value) {
		        foreach($value as $k=>$v) {
		        	if($k == 'add_time') {
		        		$value[$k] = date("y-m-d",$v);
		        		$brand[] = $value;
		        	}
		        }
	        }
	        //var_dump($brand);
	        $data['brand'] = $brand;
	        //var_dump($data);
	        return $data;
	    }
	 function parse_config($input)
	    {
	        //return $input;
	    }
	    

}

?>
