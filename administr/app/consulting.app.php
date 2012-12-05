<?php
class ConsultingApp extends BackendApp
{
    var $goodsqa_mod;
    function __construct()
    {
        $this->ConsultingApp();
    }
    function ConsultingApp()
    {
        $this->goodsqa_mod = & m('goodsqa');
        parent::__construct();
    }
    function index()
    {

        $conditions = $this->_get_query_conditions(array(array(
                'field' => 'member.user_name',
                'equal' => 'LIKE',
                'assoc' => 'AND',
                'name'  => 'asker',
                'type'  => 'string',
            ),
            array(
                'field' => 'question_content',
                'equal' => 'LIKE',
                'assoc' => 'AND',
                'name'  => 'content',
                'type' => 'string',
            )));
        $page = $this->_get_page();
        $list_data = $this->goodsqa_mod->find(array(
            'join' => 'belongs_to_user,belongs_to_store',
            'fields' => 'ques_id,question_content, reply_content,goods_qa.user_id,goods_qa.agree,goods_qa.store_id,goods_qa.type,goods_qa.item_name,goods_qa.item_id,user_name,store_name,time_post,goods_qa.reply_content',
            'limit' => $page['limit'],
            'order' => 'time_post desc',
            'count' => true,
            'conditions' => '1=1 '.$conditions,
        ));
        $page['item_count'] = $this->goodsqa_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('filtered', empty($conditions) ? 0 : 1);
        $this->assign ('list_data', $list_data);
        $this->display('goodsqa.index.html');
    }
    function edit()
    {
            $ques_id = empty($_GET['id']) ? 0 :trim($_GET['id']);
            if(!$ques_id)
            {
            	$this->show_warning('no_such_partner');
            	return;
            }
            
            if(!IS_POST)
            {
        	$data = $this->goodsqa_mod->find(array(
            	'join' => 'belongs_to_user,belongs_to_store',
           	 	'fields' => 'ques_id,question_content, reply_content,goods_qa.user_id,goods_qa.answer,goods_qa.agree,goods_qa.store_id,goods_qa.type,goods_qa.item_name,goods_qa.item_id,user_name,store_name,time_post,goods_qa.reply_content',
            	'conditions' => 'ques_id='.$ques_id,
        		));
        		//test($data[$ques_id]);
            	//$data = $this->goodsqa_mod->getRow("select * from pa_goods_qa where ques_id=".$ques_id);
            	$this->assign('data',$data[$ques_id]);
            	$this->display("goodsqa.form.html");
            }
            else
            {
            	$data=array();
            	$data['agree'] = $_POST['agreemod'];
            	$data['reason'] = $_POST['reason'];
            	$data['answer']= $_POST['review'];
            	if(!$this->goodsqa_mod->edit($ques_id,$data)){
                $this->show_warning('成功','继续编辑','index.php?app=consulting');
            		return ;
            	}
                $this->show_warning('成功','继续编辑','index.php?app=consulting');
                return ;
            }
    }
    function delete()
    {
            $ques_id = empty($_GET['id']) ? 0 :trim($_GET['id']);
            $ids = explode(',',$ques_id);
            $conditions = "1 = 1 AND ques_id ".db_create_in($ids);
            if ((!$res = $this->goodsqa_mod->drop($conditions)))
            {
                $this->show_warning('drop_failed');
                return;
            }
            else
            {
                $this->show_warning('drop_successful',
                    'to_qa_list', 'index.php?app=consulting');
                return;
            }
    }
}
?>
