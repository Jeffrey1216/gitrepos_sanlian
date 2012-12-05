<?php
	class Notice_activityApp extends MallbaseApp
	{
		var $_article_mod;
		var $_acategory_mod;
		function __construct()
		{
			$this->Notice_activityApp();
		}
		function Notice_activityApp()
		{
			parent::__construct();
			$this->_article_mod = &m('article');
			$this->_acategory_mod = &m('acategory');
		}

		function index()
		{
			$acategory = $this->_article_mod->getAll("select ar.*,ac.cate_id,ac.cate_name from pa_article ar left join pa_acategory ac on ar.cate_id = ac.cate_id where ac.cate_id =2 order by ar.add_time desc limit 9 ");
			$activity = $this->_article_mod->getAll('select ar.*,ac.cate_id,ac.cate_name from pa_article ar left join pa_acategory ac on ar.cate_id = ac.cate_id where ac.cate_id =12 order by ar.add_time desc limit 9');
			$this->assign('activity',$activity);
			$this->assign('acategory',$acategory);
			$this->display('notice_activity.index.html');
		}	
//		function activity()
//		{
//			
//			$this->display('notice_activity.from.html');
//		}
	}
?>