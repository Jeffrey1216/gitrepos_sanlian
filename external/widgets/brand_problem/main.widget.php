<?php 
/**
 *在线客服和常见问题
 *
 * @return  array   $image_list
 */
class Brand_problemWidget extends BaseWidget
{
    var $_name = 'brand_problem';
	function _get_data()
	    {
	    	$brand = array();
	    	$activity = array();
	        $article_mod = & m("article");
	        $widget_mod = & m("widget");
	        $widget_info = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
	        $widget_info_data = $widget_info['widget_data'];
	        $widget_data = unserialize($widget_info_data);
            /*$user_id = $this->visitor->get('user_id');
            var_dump($user_id);
            $this->assign('id',$user_id);
	    	foreach ($widget_data['id'] as $_k => $_v) {
            	$data['id'][] = $user_id;
            }*/
            foreach ($widget_data['images'] as $k => $v) {
            	$data['images'][] = $v;
            }
	        $n= $article_mod->find(array(
	        	'join' => 'belongs_to_acategory',
	        	'conditions' =>	'acategory.cate_id = 24 AND article.if_show=1',
	        	'order'	=> 'article.add_time DESC',
	        	'fields'	=> 'article.article_id,article.title,article.cate_id,article.add_time,acategory.cate_id,acategory.cate_name',
	        	'limit' => 10
	        ));
	        
	        foreach($n as $value) {
		        foreach($value as $k=>$v) {
		        	if($k == 'add_time') {
		        		$value[$k] = date("y-m-d",$v);
		        		$brand[] = $value;
		        	}
		        }
	        }
	        

	        $data['brand'] = $brand;
	        return $data;
	    }
	    
	function parse_config($input)
	    {
	        //return $input;
	    }
}

?>