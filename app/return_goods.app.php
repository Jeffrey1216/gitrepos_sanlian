<?php
	class Return_goodsApp extends MemberbaseApp
	{
		function index()
		{
			 /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    url('app=member'),
                         LANG::get('overview'));

        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('overview');
        $this->_config_seo('title', Lang::get('member_center'));
			$this->display('return_goods.index.html');
		}
	}


?>