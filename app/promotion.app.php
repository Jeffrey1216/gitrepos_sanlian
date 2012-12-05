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
    			$this->show_storeadmin_message('请选择您要促销的产品');
    			return ;
    		}
    		//判断此产品是否已经添加促销
    		$info = $this->_promotion_mod->getRow('select * from pa_promotion p left join pa_promotion_store_goods psg on p.promotion_id= psg.promotion_id where psg.gs_id='.$gs_id.' AND pr_status IN (0,10) ');
    		if ($info)
    		{
    			$this->show_storeadmin_message('该产品正在促销或正在审核中！');
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
    			$this->show_storeadmin_message("请输入促销价格");
    			return ;
    		}
    	    if(0 == $stock)
    		{
    			$this->show_storeadmin_message("请输入促销的数量");
    			return ;
    		}
    	    if(empty($_POST['starttime']) && empty($_POST['endtime']))
    		{
    			$this->show_storeadmin_message("请选择促销开启时间与结束时间");
    			return ;
    		}
    	    if($end < $start)
    		{
    			$this->show_storeadmin_message("促销开启时间不能大于结束时间");
    			return ;
    		}
    	   	if($gs_id == 0)
    		{
    			$this->show_storeadmin_message("商品中间表ID不能为空");
    			return ;    			
    		}
    		$goods_info = $this->_storegoods_mod->getRow('SELECT * from pa_store_goods gs left join pa_goods_spec gc on gc.spec_id=gs.spec_id where gs.gs_id='.$gs_id);
    		$pr_discount = intval($price/floatval($goods_info['price'])*100)/100;
			if($pr_discount > 1)
			{
				$this->show_storeadmin_message('您输入的促销价不能大于原价');
				return ;
			}
			if($goods_info['stock'] < $stock)
			{
				$this->show_storeadmin_message('您输入的促销数量不能大于原数量');
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
				$this->show_storeadmin_message("申请失败");
    			return ;
			}
				$pgdata = array(
					'promotion_id' => $promotion_id,
					'gs_id' => intval($_POST['gs_id']),
				);
    		if(!$this->_promotionstoregoods_mod->add($pgdata))
			{
				$this->show_storeadmin_message("申请失败");
    			return ;
			}	
            $this->show_storeadmin_message('add_ok',
                'back_list', 'index.php?app=my_pailagoods'
            );
    	}
    }
    function _get_conditions()
    {
        /* 搜索条件 */
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