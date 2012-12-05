<?php

/*���������*/
class MallApp extends MallbaseApp
{
    function index()
    {
        $this->assign('index', 1); // ��ʶ��ǰҳ������ҳ���������õ���״̬
        $this->assign('icp_number', Conf::get('icp_number'));

        /* �������� */
        $this->assign('hot_keywords', $this->_get_hot_keywords());

        $this->_config_seo(array(
            'title' => Lang::get('mall_index') . ' - ' . Conf::get('site_title'),
        ));
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));
        $this->display('pailaIndex.html');
    }

    function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }
	/* ȡ��������Ϣ */
    function lmzq()
    {
    	$this->display('pailaIndex.html');
    }
}

?>