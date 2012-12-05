<?php

/* 投票控制器 */
class VoteApp extends MallbaseApp
{
	function __construct()
    {
        $this->Vote();
    }

    function Vote()
    {
        parent::__construct();
        $this->_theme_mod = &m('theme');       //实例化投票主题表
        $this->_contents_mod = &m('contents'); //实例化投票内容表
        $this->_records_mod = &m('records');   //实例化获投票记录表
    }
	/**
     *    投票主题列表
     *
     *    @author   lihuoliang
     *    @param    none
     *    @return   void
     */
    function index()
    {
    	$id = intval($_GET['tid']);
    	if ($id)
    	{
    		$rs = $this->_theme_mod->get($id);
			if ($rs)
			{
				
				$uid = $this->visitor->get('user_id');
	    		//查询用户此主题是否已经投票过
				$record = $this->_records_mod->find(array(
														'conditions' => "th_id = $id AND uid = $uid"
														));	
				if (IS_POST)
				{
					if($this->visitor->has_login)
					{
						if ($record)
						{
							if ($rs['th_repeat'] == 'no')
							{
								$this->show_warning('此主题你已经投过票了，不能重复投票！');
								return;
							}
						}
						$currenttime = time();
						//如果当前时间小于活动开始时间---表示活动未开始
						if ($currenttime<$rs['th_starttime']) 
						{
							$this->show_warning('投票活动还没有开始哦，你还不能投票！');
							return;
						}
						//如果当前时间大于活动结束时间---表示活动已结束
						if ($currenttime>$rs['th_endtime']) 
						{
							$this->show_warning('投票活动已经结束啦！');
							return;
						}
						$content = $_POST['contents'];
						$count   = count($content);
						if ($content)
						{
							//投票选项不能大于设定选项
							if ($count!=$rs['th_max'])
							{
								$this->show_warning('一定要选择'.$rs['th_max'].'项进行投票！');
								return;
							}else
							{
								//投票开始---修改总数，并写入
								foreach ($content as $v){
									$this->_contents_mod->edit('c_id='.$v,'c_num=c_num+1');
									$recs['c_id'] = $v;
									$recs['th_id'] = $id;
									$recs['uid'] = $uid;
									$recs['r_time'] = time();
									$this->_records_mod->add($recs);
								}
								//统计话题总数量，并更新
								$this->_theme_mod->edit('th_id='.$id,'th_num=th_num+'.$count);
								$this->show_message('恭喜你，投票成功。');
    							return;
							}
						}else 
						{
							$this->show_warning('请选择你的投票内容！');
							return;
						}
			    	}else
			    	{
			    		$this->show_warning('你还没有登入，不能投票！');
						return;
			    	}
				}
				//通过投票主题查询所有内容
				$content = $this->_contents_mod->find(array(
														'conditions' => 'th_id='.$id,
														'order'      => 'c_id ASC'
														));
				$type = $rs['th_max']>1 ? 'checkbox' : 'radio';								
				foreach ($content as $v)
				{
					$contents .= "<li class=\"vote_text\"><input type=\"$type\" name=\"contents[]\" value=\"$v[c_id]\"/>$v[c_content]</li>";
					if ($record)
					{
						$contents .= '<li class="vote_list"><span class="vote_li_bg"><span class="vote_cols"></span></span><span class="vote_num">'.$v['c_num'].'</span>票(<span class="vote_precent">0</span>%)</li>';
					}
				}
				$imgurl = IMAGE_URL.$rs['th_imgurl'];
				$this->assign('rs',$rs);
				$this->assign('imgurl',$imgurl);
				$this->assign('contents',$contents);
				$this->display('vote.index.html');
			}else 
			{
				$this->show_warning('你要查询的投票主题不存在！');
				return;
			}
    	}else{
    		$this->show_warning('你要查询的投票主题不存在！');
    		return;
    	}
    }
}

?>
