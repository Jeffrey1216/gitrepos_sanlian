<?php 
class PromotionApp extends StoreadminbaseApp
{
    var $_storegoods_mod;
    var $_promotion_mod;
    var $_promotionstoregoods_mod;
    public function __construct() {
    	$this->PromotionApp();
    }
    
    public function PromotionApp() {
    	parent::__construct();
        $this->_storegoods_mod = & m('storegoods');
        $this->_promotion_mod = & m('promotion');
        $this->_promotionstoregoods_mod = & m('promotionstoregoods');
    }
    function index(){
	    	$page = $this->_get_page();
			$promotion_info = $this->_promotionstoregoods_mod->get_promotion_list($page,$this->visitor->get(user_id));
			$page['item_count'] = $promotion_info['item_count'];
			$this->_format_page($page);
			$this->assign('page_info', $page);
			$this->assign('promotion_info',$promotion_info['goods']);
    		$this->display('my_promotion.index.html');
    }
    function apply()
    {
    	if(!IS_POST)
    	{
    		$gs_id = empty($_GET['gs_id']) ? 0 : $_GET['gs_id']; 
    		if($gs_id == 0)
    		{
    			$this->show_storeadmin_message('��ѡ����Ҫ�����Ĳ�Ʒ');
    			return ;
    		}
    		//�жϴ˲�Ʒ�Ƿ��Ѿ���Ӵ���
    		$info = $this->_promotion_mod->getRow('select * from pa_promotion p left join pa_promotion_store_goods psg on p.promotion_id= psg.promotion_id where psg.gs_id='.$gs_id.' AND pr_status IN (0,10) ');
    		if ($info)
    		{
    			$this->show_storeadmin_message('�ò�Ʒ���ڴ�������������У�');
    			return ;
    		}
    		$goods=$this->_storegoods_mod->getRow('select gc.price,gc.credit,gc.weight,gc.zprice,g.goods_name,sg.store_id,sg.stock,sg.gs_id from pa_store_goods sg left join pa_goods g on sg.goods_id= g.goods_id left join pa_goods_spec gc on sg.spec_id=gc.spec_id where sg.gs_id='.$gs_id);
    		$this->assign('goods',$goods);
    		$this->display('promotion.form.html');
    	} else {
    		$price = empty($_POST['pr_price']) ? 0 : floatval($_POST['pr_price']);
    		$stock = empty($_POST['pr_stock']) ? 0 : intval($_POST['pr_stock']);
    		$start = empty($_POST['starttime']) ? 0 : strtotime($_POST['starttime']);
    		$end   = empty($_POST['endtime']) ? 0 : strtotime($_POST['endtime']);
    		$gs_id = empty($_POST['gs_id']) ? 0 : intval($_POST['gs_id']);    
    		if(0 == $price)
    		{
    			$this->show_storeadmin_message("����������۸�");
    			return ;
    		}
    	    if(0 == $stock)
    		{
    			$this->show_storeadmin_message("���������������");
    			return ;
    		}
    	    if(empty($_POST['starttime']) && empty($_POST['endtime']))
    		{
    			$this->show_storeadmin_message("��ѡ���������ʱ�������ʱ��");
    			return ;
    		}
    	    if($end < $start)
    		{
    			$this->show_storeadmin_message("��������ʱ�䲻�ܴ��ڽ���ʱ��");
    			return ;
    		}
    	   	if($gs_id == 0)
    		{
    			$this->show_storeadmin_message("��Ʒ�м��ID����Ϊ��");
    			return ;    			
    		}
    		$goods_info = $this->_storegoods_mod->getRow('SELECT * from pa_store_goods gs left join pa_goods_spec gc on gc.spec_id=gs.spec_id where gs.gs_id='.$gs_id);
    		$pr_discount = intval($price/floatval($goods_info['price'])*100)/100;
			if($pr_discount > 1)
			{
				$this->show_storeadmin_message('������Ĵ����۲��ܴ���ԭ��');
				return ;
			}
			if($goods_info['stock'] < $stock)
			{
				$this->show_storeadmin_message('������Ĵ����������ܴ���ԭ����');
				return ;
			}
			    $data = array(
    			'pr_price' => $price,
	    		'pr_credit' => $goods_info['credit'],
	    		'pr_stock' => $stock,
	    		'pr_addtime' => $start,
	    		'pr_endtime' => $end,
	    		'pr_status' => 5,
			    'pr_discount' => $pr_discount,
    			);
			if(!$promotion_id=$this->_promotion_mod->add($data))
			{
				$this->show_storeadmin_message("����ʧ��");
    			return ;
			}
				$pgdata = array(
					'promotion_id' => $promotion_id,
					'gs_id' => intval($_POST['gs_id']),
				);
    		if(!$this->_promotionstoregoods_mod->add($pgdata))
			{
				$this->show_storeadmin_message("����ʧ��");
    			return ;
			}	
            $this->show_storeadmin_message('add_ok',
                'back_list', 'index.php?app=my_pailagoods'
            );
    	}
    }
    function _get_conditions()
    {
        /* �������� */
        $conditions = " and 1 = 1";
        if (trim($_GET['keyword']))
        {
            $str = "LIKE '%" . trim($_GET['keyword']) . "%'";
            $conditions .= " AND (g.goods_name {$str} OR g.brand {$str} OR g.cate_name {$str})";
        }
        if ($_GET['cate_id'])
        {
			$conditions .= " AND g.cate_id_1 = ".$_GET['cate_id'];
        }  
        return $conditions;
    }
}    
?>