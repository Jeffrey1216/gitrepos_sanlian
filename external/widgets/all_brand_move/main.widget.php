<?php

/**
 * ÂÖ²¥Í¼Æ¬¹Ò¼þ
 *
 * @return  array   $image_list
 */
class All_brand_moveWidget extends BaseWidget
{
    var $_name = 'all_brand_move';

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
            $brand_mod =& m('brand');
            $data = $brand_mod->find(array(
                'conditions' => "recommended = 1",
                'order' => 'sort_order',
            ));

            $cache_server->set($key, $data, 3600);
        }
        return $data;
    }    
}

?>