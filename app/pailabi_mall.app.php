<?php
class Pailabi_mallApp extends MallbaseApp
{
	function index()
		{
			$this->assign('index', 2); // 标识当前页面是首页，用于设置导航状态
    		$this->display('pailabi_mall.html');
   
		}
}


?>