<?php
/*
 * ��Ʒ������˹��ܿ�����
 * @author ����  & ���ʵ�  & �η���
 */
class AuditApp extends BackendApp
{
    var $_goods_mod;
    var $_store_mod;
    var $_storegoods_mod;
    function __construct(){
    	$this->AuditApp();
    }
    function AuditApp(){
    	parent::__construct();
    	$this->_goods_mod =& m('goods');
    	$this->_store_mod = &m('store');
    	$this->_goodsspec_mod = & m('goodsspec'); 
    	$this->_storegoods_mod = & m('storegoods');
    }
    /*�б���ʾ*/
    function index(){
    	$page_num = 50;
    	//��ȡ��ҳ��ʾ����
        $page = $this->_get_page($page_num);
        $goods_name = empty($_GET['goods_name']) ? '' : trim($_GET['goods_name']);
        $store_id = empty($_GET['store_id']) ? 728 : intval($_GET['store_id']);
        $status = empty($_GET['status']) ? 0 : intval($_GET['status']);
        $time_from = empty($_GET['time_from']) ? NULL : explode('-',trim($_GET['time_from']));
        $time_to = empty($_GET['time_to']) ? NULL : explode('-',trim($_GET['time_to']));
        $conditions = " 1 = 1 AND g.if_show=1 AND g.closed = 0";
        //����ʱ�����ֻѡ����һ���� ����Ϊ�ǵ���ġ� ���ǰһ��ʱ����ں�һ��ʱ�䣬 ���һ��ʱ��ȡ����ġ�
        if ($time_from != NULL || $time_to != NULL) //ֻ��һ��ΪNULL������� �򶼲�ΪNULL�����
        {
        	$real_time_from = empty($time_from) ? time() : mktime(0, 0, 1, $time_from[1], $time_from[2], $time_from[0]);
        	$real_time_to = empty($time_to) ? time() : mktime(23, 59, 59, $time_to[1], $time_to[2], $time_to[0]);
        	if ($real_time_from > $real_time_to)
        	{
        		$real_time_to = $real_time_from;
        	}
        	$conditions .= " AND g.examine_time >= '" . $real_time_from . "' AND g.examine_time <= '" . $real_time_to . "'";
        } 	
        if(!$goods_name == '') {
        	$conditions .= " AND g.goods_name like '%".$goods_name."%' ";
        	$this->assign('goods_name',$goods_name);
        }  
        switch($status) {
        	case 0: 
        		$conditions .= " AND g.status = 0";
        		$this->assign('sta',0);
        		break;
        	case 1: 
        		$conditions .= " AND g.status = 1";
        		$this->assign('sta',1);
        		break;
        	case 2: 
        		$conditions .= " AND g.status = 2";
        		$this->assign('sta',2);
        		break;
        	default : 
        		$this->show_warning("������� "); 
        		return;
        }
	        $count = $this->_goods_mod->getOne("select count(*) from pa_goods g left join pa_gcategory gc on g.cate_id = gc.cate_id left join pa_supply_goods sg on g.goods_id = sg.goods_id left join pa_supply s on sg.supply_id = s.supply_id  where " . $conditions  );
	        $page['item_count'] = $count;        
	        $goods_info = $this->_goods_mod->getAll("select g.goods_id,g.goods_name,g.brand,g.simage_url,g.status,g.status,g.reason,g.add_time,s.supply_name from pa_goods g left join pa_gcategory gc on g.cate_id = gc.cate_id left join pa_supply_goods sg on g.goods_id = sg.goods_id left join pa_supply s on sg.supply_id = s.supply_id  where " . $conditions . " ORDER BY g.last_update,g.add_time DESC limit " . $page['limit']);
	        $this->_format_page($page);
	        $this->assign('goods_info',$goods_info);
	        //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
	        $this->assign('page_info', $page);  
	        $this->assign('image',IMAGE_URL);
	    	$this->display('audit.index.html');
    }
    /*���*/
    function audit(){
    	$goods_id = isset($_GET['goods_id']) ? intval($_GET['goods_id']) : 0;
    	$goods_mod=& m(goods);
    	$spec_mod=& m(goodsspec);
    	if(!IS_POST)
    	{
    		if($goods_id != 0){
    			//��ѯ��Ʒ��Ϣ
    			$goods_info = $this->_goods_mod->getRow("select g.mimage_url,g.goods_id,g.reason,g.status,g.goods_name,g.cate_name,g.brand,s.supply_name,s.address,s.mobile from pa_goods g left join pa_supply_goods sg on g.goods_id = sg.goods_id left join pa_supply s on sg.supply_id = s.supply_id where g.goods_id = " . $goods_id);
    			$spec_info = $this->_goodsspec_mod->find(array('conditions' => ' goods_id = ' . $goods_id));
    			foreach($spec_info as $_k => $_v) {
    				$goods_info['spec'][] = $_v;
    			}
    			$goods_info['mimage_url']	 = IMAGE_URL . $goods_info['mimage_url'];
    			$this->assign("goods_info",$goods_info);
    	        $this->display('audit.form.html');
    		}
    	} else
    	{ 
    		$data=array();
    		$data['status']= intval($_POST['status']);
    		$data['examine_time'] = time();
    		if($data['status'] == 2)
    		{
    			$data['reason']= trim($_POST['drop_reason']);
    			if($data['reason'] == '')
    			{
    				$this->show_warning('����дδͨ��ԭ��!');
    				return;
    			}
    		}
    		$goods_mod->edit($goods_id,$data);
    		//����Ĭ�Ͻ�������
    		$this->_get_goods_info($goods_id);
    		
    		$this->show_message('audit_ok','continue_audit','index.php?app=audit');
    	}
    }
	//�������
	function bath_edit()
	{
		$id = isset($_GET['id']) ? trim($_GET['id']) : '';
		if(!IS_POST)
		{
			$this->display('audit.bath_edit.html');
		}else{
				if(!id)
				{
					$this->show_warning('û�������Ʒ');
					return;
				}
				$ids = explode(',',$id);
				$data = array();
				$data['id'] = $ids;
				$data['status'] = intval($_POST['status']);
				if($data['status'] == 2)
				{
					if(trim($_POST['drop_reason']) == '')
					{
						$this->show_warning('����дδͨ��ԭ��!');
						return;
					}else{
						$data['reason'] = trim($_POST['drop_reason']);
					}
				}
				if(!$this->_goods_mod->edit($ids,$data))
				{
					$this->show_warning($this->_goods_mod->get_error());
					return;
				}else{
					$this->_multiple_goods_info($ids);
					$this->show_message('�����༭�ɹ�!',
					'back_list',	'index.php?app=audit'
					);	
				}
		}	
	}
    /*****
     * @means	Ĭ�Ͻ������� 
     * @param   int     $goods_id    ��Ʒid
	 * @author  xiaoyu	
     *
     ******/
    function _get_goods_info($goods_id)
    {
	   	$goods_info =  $this->_goods_mod->getAll("select g.goods_id,gc.spec_id from pa_goods g left join pa_goods_spec gc on gc.goods_id = g.goods_id where g.goods_id = " . $goods_id);
		if(empty($goods_info))
		{
			$this->show_message('goods_not_exists');
			return ;
		}
		foreach ($goods_info as $key=>$val)
		{
			$sgoods_info = $this->_store_mod->getRow("select * from pa_store_goods where goods_id=".$val['goods_id']." AND spec_id=".$val['spec_id']." AND store_id=".STORE_ID);
			if($sgoods_info)
			{
				$this->_storegoods_mod->edit($sgoods_info['gs_id'],array('stock' => 1000));
			}else {
				$data = array(
					'goods_id' => $val['goods_id'],
					'spec_id' => $val['spec_id'],
					'stock' => 1000,
					'store_id' => STORE_ID,
				);
				$this->_storegoods_mod->add($data);
			}
		}
    }
    function _multiple_goods_info($good_ids){
	if(!is_array($good_ids)){
		$this->show_warning('������������ϵ����Ա!');
		return;
	}
	foreach($good_ids as $val){
		$val = intval($val);
		$this->_get_goods_info($val);
	}
    }
}
?>