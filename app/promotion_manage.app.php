<?php
/*�̻���̨������*/
class Promotion_manageApp extends StoreadminbaseApp
{
	var $_promotionstoregoods_mod;
	var $_gcategory_mod;
	var $_goods_mod;
	var $_store_goods_mod;
	var $_promotion_mod;
    function __construct()
    {
        $this->Promotion_manageApp();
    }
    function Promotion_manageApp()
    {
        parent::__construct();
        $this->_promotionstoregoods_mod =&m('promotionstoregoods');
        $this->_promotion_mod = & m('promotion');
        $this->_gcategory_mod =& m('gcategory');
        $this->_goods_mod = & m('goods');
        $this->_store_goods_mod = & m('storegoods');
    }
    function index()
    {
    	$page = $this->_get_page(10);
    	$keywords = empty($_GET['keyword']) ? '' : trim($_GET['keyword']);
    	$param = array(
    		'keywords' => $keywords,
    	);
		$promotion_info = $this->_promotionstoregoods_mod->get_promotion_all_list($page,intval($this->visitor->get(user_id)),$param);
		$page['item_count'] = $promotion_info['item_count'];
		$this->_format_page($page);
		$this->assign('page_info', $page);
		$this->assign('promotion',$promotion_info);
		$this->display('storeadmin.prmanage.index.html');
    }
    function edit()
    {
    	$pr_id = empty($_GET['pr_id']) ? 0 : intval($_GET['pr_id']);
    	$promotion_info = $this->_promotionstoregoods_mod->get_promotion($pr_id);
    	if(!IS_POST){
	    	if ($promotion_info['pr_status']!=0)
	    	{
	    		$this->show_storeadmin_warning('���ܱ༭�˴�����Ʒ');
	    		return ;
	    	}
	    	$this->assign('pr',$promotion_info);
	    	$this->display('promotion.view.html');
    	} else{
    		$pr_stock = empty($_POST['pr_stock']) ? 0 : intval($_POST['pr_stock']);
    		$pr_name = empty($_POST['pr_name']) ? '' : trim($_POST['pr_name']);
    		if(0 == $pr_stock)
    		{
    			$this->show_storeadmin_warning('����������д�����������������Ƿ���ȷ');
    			return ;
    		}
			$data = array(
				'pr_stock' => $pr_stock,
				'pr_name'  => $pr_name,
			);
			$fla = $this->_promotion_mod->edit(intval($pr_id),$data);
			if(!$fla)
			{
				$this->show_storeadmin_message('edit_fail',
									'edit_goon','index.php?app=promotion_manage&act=edit&pr_id='.$pr_id);
				return ;
			}else {
				$this->show_storeadmin_message('edit_ok',
									'edit_goon','index.php?app=promotion_manage&act=edit&pr_id='.$pr_id);
				return ;
			}
    	}

    }
    //ɾ��������Ʒ 
    function drop()
    {
    	$pr_id = empty($_GET['pr_id']) ? 0 : intval($_GET['pr_id']);
    	$promotion_info = $this->_promotionstoregoods_mod->get_promotion($pr_id);
    	if ($promotion_info['pr_status']==10 || $promotion_info['pr_status']==20)
    	{
    		$this->show_storeadmin_warning('����ɾ���˴�����Ʒ');
    		return ;
    	}
    	$this->_promotion_mod->drop(intval($pr_id));
    	$this->_promotionstoregoods_mod->drop(intval($pr_id));
    	$this->show_storeadmin_message('ɾ��������Ʒ�ɹ�');
    }
	//�رմ�����Ʒ 
    function edit_close()
    {
    	$pr_id = empty($_GET['pr_id']) ? 0 : intval($_GET['pr_id']);
    	$promotion_info = $this->_promotionstoregoods_mod->get_promotion($pr_id);
    	if(0 == $pr_id || !$promotion_info)
    	{
    		$this->show_storeadmin_warning('������Ʒ������');
    		return ;
    	}
    	if ($promotion_info['pr_status']!=10)
    	{
    		$this->show_storeadmin_warning('���ܹرմ˴�����Ʒ');
    		return ;
    	}
    	$data = array(
    		'pr_status' => 20,
    		'pr_stock'  => 0,
    	);
    	$pflag = $this->_promotion_mod->edit($pr_id,$data);
    	$stock = intval($promotion_info['stock'])+intval($promotion_info['pr_stock']);
    	$sdata = array(
    		'stock' => $stock,
    	);
    	$sflag = $this->_store_goods_mod->edit(intval($promotion_info['gs_id']),$sdata);
    	$this->show_storeadmin_message('�رմ�����Ʒ�ɹ�');
    }
}
?>