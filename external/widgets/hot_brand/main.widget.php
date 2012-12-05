<?php

/**
 * ÂÖ²¥Í¼Æ¬¹Ò¼þ
 *
 * @return  array   $image_list
 */
class Hot_brandWidget extends BaseWidget
{
    var $_name = 'hot_brand';
    function _get_data()
    {
    	$brand_mod = &m('brand');
    	$brand = $brand_mod->getAll("select brand_name,recommended,brand_logo,sort_order from pa_brand where recommended = 1 ORDER BY sort_order ASC limit 15");     
       		for($i = 0; $i < 15 ; $i += 3)
       		{
       			$data['brand'][] = array(
       				'0'	=> $brand[$i],
       				'1'	=> $brand[$i + 1],
       				'2' => $brand[$i + 2]
       			);
       		}	
        return $data;
    }
}
?>