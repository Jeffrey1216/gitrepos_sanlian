<?php
	class Return_goodsApp extends MemberbaseApp
	{
		function index()
		{
			 /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    url('app=member'),
                         LANG::get('overview'));

        /* 当前用户中心菜单 */
        $this->_curitem('overview');
        $this->_config_seo('title', Lang::get('member_center'));
			$this->display('return_goods.index.html');
		}
	}


?>