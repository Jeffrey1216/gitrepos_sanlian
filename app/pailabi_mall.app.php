<?php
class Pailabi_mallApp extends MallbaseApp
{
	function index()
		{
			$this->assign('index', 2); // ��ʶ��ǰҳ������ҳ���������õ���״̬
    		$this->display('pailabi_mall.html');
   
		}
}


?>