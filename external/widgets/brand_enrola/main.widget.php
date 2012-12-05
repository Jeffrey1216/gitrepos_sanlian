<?php

/**
 * ฦทลฦอะนÜืขฒแบอตวยผ
 *
 * @return  array   $image_list
 */
class Brand_enrolaWidget extends BaseWidget
{
    var $_name = 'brand_enrola';
    
    function _get_data()
    {
    	/*$data['is_login'] = empty($_SESSION['user_info']) ? false : true;
    	
    	if ($data['is_login']){
    		$data['member_info'] = $_SESSION['user_info'];
    		$widget_mod = &m("widget");
            $member_mod = & m("member");
    		$member_info = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
    		
    		$user_id = $data['member_info']['user_id'];
    		//var_dump($data['member_info']);
    		//var_dump($user_id);
            $mem_info=$member_mod->get(array("select * from pa_member where user_id = " . $user_id));
            //var_dump($member_mod->get(array("select * from pa_member where user_id = " . $user_id)));
            var_dump($mem_info);
            $member_infos[]=$mem_info;
            var_dump($member_infos);
            $data['member'] = $member_infos;
    	}
    	
    	return $data;
    	//var_dump($data);*/
    	$member_infos=array();
    	$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data = false;
    	
    	if($data === false){
    		$data['is_login'] = empty($_SESSION['user_info']) ? false : true;
		    	if ($data['is_login']){
		    		$data['member_info'] = $_SESSION['user_info'];
		    		$widget_mod = &m("widget");
		            $member_mod = & m("member");
		            $supply_mod = & m('supply');
		    		$member_info = $widget_mod->get(array('conditions' => "widget_name = '".$this->_name."'"));
		    		
		    		$user_id = $data['member_info']['user_id'];
		            $mem_info = $member_mod->getRow("select * from pa_member where user_id = " . $user_id);
					$mem_info['portrait'] = IMAGE_URL . $mem_info['portrait'];
					$supply_info = $supply_mod->getRow("select s.supply_id from pa_supply s where s.user_id = " . $user_id);
		            $data['member'] = $mem_info;
		            $data['supply'] = $supply_info;
		           // var_dump($mem_info);
		           //var_dump(IMAGE_URL);
		           $data['url'] = IMAGE_URL;
		    	}
    	 }
    	return $data;
       
    }
    

}

?>