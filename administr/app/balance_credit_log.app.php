<?php
class Balance_credit_logApp extends BackendApp
{
	function index()
	{
		$time = gmtime();
		$change_type_key = empty($_POST['change_type']) ? 0 : intval($_POST['change_type']);
		$date_level_key = empty($_POST['change_date_level']) ? 0 : intval($_POST['change_date_level']);
		
		$change_types = get_change_type();
		$date_levels = _get_date_levels('记录');
		$conditions = " 1 = 1 and m.user_id<>0";
		if ($change_type_key != 0)
		{
			$conditions .= " AND l.change_type = " . $change_type_key;
		}
		
		if ($date_level_key != 0)
		{
			$local_time = $time - $date_levels[$date_level_key]['ms'];
			$conditions .= " AND l.change_time >= " . $local_time;
		}
		//搜索条件
		$_accountlog_mod = & m("accountlog");
		$page   =   $this->_get_page(10);   //获取分页信息
		$page['item_count']=$_accountlog_mod->getOne("select count(*) from 
			pa_account_log l left join pa_member m on 
			l.user_id = m.user_id where " . $conditions);   //获取统计数据
		$list = $_accountlog_mod->getAll("select l.*,m.user_name,m.mobile from pa_account_log l left join pa_member m 
			on l.user_id = m.user_id where " . $conditions . " order by l.change_time desc limit " . $page['limit']);
		foreach ($list as $k=>$v)
		{
			$list[$k]['change_time'] = $v['change_time'];
		}
		$this->_format_page($page);
		$this->assign('page_info', $page);   //将分页信息传递给视图，用于形成分页条
		$this->assign('change_type', $change_types);
		$this->assign('date_levels', $date_levels);
		$this->assign('list', $list);
		$this->display('balance_credit_log.index.html');
	}
}