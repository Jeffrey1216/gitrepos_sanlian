<?php

class DefaultApp extends MallbaseApp
{
    function index()
    {
        $this->assign('index', 0); // ��ʶ��ǰҳ������ҳ���������õ���״̬
        $this->assign('icp_number', Conf::get('icp_number'));

        $this->_config_seo(array(
            'title' => Lang::get('mall_index') . ' - ' . Conf::get('site_title'),
        ));
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));
        $search_arr = parse_url($_SERVER['REQUEST_URI']);
        if (!$search_arr['query'])
        {
        	$str = substr($search_arr['path'],strlen($search_arr['path'])-9,9);
        	if ($str != 'index.php')
        	{
        		header('Location: index.php?app=promotion_index');
        	}
        }
        $this->display('index.html');
    }

	/* ȡ��������Ϣ */
    function lmzq()
    {
    	$this->display('lmzq_index.html');
    }
    
//    function test() {
//    	$model_setting = &af('settings');
//        $setting = $model_setting->getAll(); //����ϵͳ��������
//        
//        var_dump($setting);
//    }
    function map()
    {
    	$this->display('pailamap.html');
    }
    
    public function web_map()
    {
    	$this->display('paila_web_map.html');
    }
    public function web_links()
    {
    	$this->display('paila_web_links.html');
    }
	function weibo()
    {
    	$this->display('weibo_special.html');
    }
}

?>
