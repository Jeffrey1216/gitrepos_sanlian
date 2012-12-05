<?php

/* ͶƱ������ */
class VoteApp extends MallbaseApp
{
	function __construct()
    {
        $this->Vote();
    }

    function Vote()
    {
        parent::__construct();
        $this->_theme_mod = &m('theme');       //ʵ����ͶƱ�����
        $this->_contents_mod = &m('contents'); //ʵ����ͶƱ���ݱ�
        $this->_records_mod = &m('records');   //ʵ������ͶƱ��¼��
    }
	/**
     *    ͶƱ�����б�
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
	    		//��ѯ�û��������Ƿ��Ѿ�ͶƱ��
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
								$this->show_warning('���������Ѿ�Ͷ��Ʊ�ˣ������ظ�ͶƱ��');
								return;
							}
						}
						$currenttime = time();
						//�����ǰʱ��С�ڻ��ʼʱ��---��ʾ�δ��ʼ
						if ($currenttime<$rs['th_starttime']) 
						{
							$this->show_warning('ͶƱ���û�п�ʼŶ���㻹����ͶƱ��');
							return;
						}
						//�����ǰʱ����ڻ����ʱ��---��ʾ��ѽ���
						if ($currenttime>$rs['th_endtime']) 
						{
							$this->show_warning('ͶƱ��Ѿ���������');
							return;
						}
						$content = $_POST['contents'];
						$count   = count($content);
						if ($content)
						{
							//ͶƱѡ��ܴ����趨ѡ��
							if ($count!=$rs['th_max'])
							{
								$this->show_warning('һ��Ҫѡ��'.$rs['th_max'].'�����ͶƱ��');
								return;
							}else
							{
								//ͶƱ��ʼ---�޸���������д��
								foreach ($content as $v){
									$this->_contents_mod->edit('c_id='.$v,'c_num=c_num+1');
									$recs['c_id'] = $v;
									$recs['th_id'] = $id;
									$recs['uid'] = $uid;
									$recs['r_time'] = time();
									$this->_records_mod->add($recs);
								}
								//ͳ�ƻ�����������������
								$this->_theme_mod->edit('th_id='.$id,'th_num=th_num+'.$count);
								$this->show_message('��ϲ�㣬ͶƱ�ɹ���');
    							return;
							}
						}else 
						{
							$this->show_warning('��ѡ�����ͶƱ���ݣ�');
							return;
						}
			    	}else
			    	{
			    		$this->show_warning('�㻹û�е��룬����ͶƱ��');
						return;
			    	}
				}
				//ͨ��ͶƱ�����ѯ��������
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
						$contents .= '<li class="vote_list"><span class="vote_li_bg"><span class="vote_cols"></span></span><span class="vote_num">'.$v['c_num'].'</span>Ʊ(<span class="vote_precent">0</span>%)</li>';
					}
				}
				$imgurl = IMAGE_URL.$rs['th_imgurl'];
				$this->assign('rs',$rs);
				$this->assign('imgurl',$imgurl);
				$this->assign('contents',$contents);
				$this->display('vote.index.html');
			}else 
			{
				$this->show_warning('��Ҫ��ѯ��ͶƱ���ⲻ���ڣ�');
				return;
			}
    	}else{
    		$this->show_warning('��Ҫ��ѯ��ͶƱ���ⲻ���ڣ�');
    		return;
    	}
    }
}

?>
