<?php
/*
 *  �������ɿ�����
 *  
 *  @author ��׳
 * */
class AllocateApp extends BackendApp
{
	var $_allocate_mod;
	var $_order_mod;
	var $_order_extm_mod;
	var $_store_mod;
	var $_region_mod;
	function __construct()
	{
		$this->AllocateApp();
	}
	function AllocateApp()
	{
		parent::BackendApp();
		$this->_order_mod=& m('order');
		$this->_order_extm_mod=& m('orderextm');
		$this->_allocate_mod =& m('allocate');
		$this->_store_mod =& m('store');
		$this->_region_mod =& m('region');
	}
	/*��ʾ*/
/*	function index(){
		$page = $this->_get_page(10);//��ȡ��ҳ
		//��������
		if (isset($_GET['sort']) && isset($_GET['order']))
		{
			$sort=strtolower(trim($_GET['sort']));
			$order=strtolower(trim($_GET['order']));
			if (!in_array($order, array('asc','desc')))
			{
			$sort='add_time';
			$order='desc';
			}
		}
		else
		{
			$sort='add_time';
			$order='desc';
		}
        //ƴװsql��ͳ������
        $count_sql = "select count(*) from pa_order o,pa_order_extm e where o.order_id=e.order_id and o.seller_id=3 ";
        $page['item_count'] = $this->_order_mod->getOne($count_sql); //��ȡͳ�Ƶ�����
        $this->_format_page($page);//��ʽ����ҳ��Ϣ
        //ƴװsql����ѯÿҳ��ʾ����
        $sql="select o.order_id,o.goods_amount, o.order_sn, o.add_time,o.assign_store_id, e.region_name from pa_order o,pa_order_extm e where o.order_id=e.order_id and o.seller_id=3 order by $sort $order limit $page[limit]";
        $aa=$this->_order_mod->getAll($sql);
        //dump($page);
        $this->assign('allocate',$aa);
        //$this->assign('orders',$page['item_count']);
        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
		$this->display('allocate.index.html');
	}*/
	function index() {
	    $order_info = $this->_order_mod->find();
	    $this->assign("order" , $order_info); // ���� 
	    $assign_store_id=$_GET['assign_store_id'];
	    $store_id = $_GET['store_id'];
	    //var_dump($assign_store_id);
	    //$field = 'seller_name';
	    //array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
	    $conditions=$this->_get_query_conditions(array(
	        array(
	        	'filed' => 'assign_store_id',
	            'name' => 'assign_store_id',
                'equal' => '=',
                'type'  => 'numeric',
	        ),
	    	array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),
	    ));
	    //var_dump($conditions);
	    $model_order =& m('order');
	    $model_allocate = & m('allocate');
        $page   =   $this->_get_page(10);    //��ȡ��ҳ��Ϣ
		if (isset($_GET['sort']) && isset($_GET['order']))
	        {
	            $sort  = strtolower(trim($_GET['sort']));
	            $order = strtolower(trim($_GET['order']));
	            if (!in_array($order,array('asc','desc')))
	            {
	             $sort  = 'add_time';
	             $order = 'desc';
	            }
	        }
	    else
	        {
	            $sort  = 'add_time';
	            $order = 'desc';
	        }
	    $orders = $model_order->find(array(
            'conditions'    => 'seller_id=3' . $conditions, // . o.seller_id == 3,
	        //'conditions'  => 'store_id=0' . $conditions,
	        'join'    => 'has_orderextm',
            'limit'         => $page['limit'],  //��ȡ��ǰҳ������
            'order'         => "$sort $order",
            'count'         => true             //����ͳ��
        )); //�ҳ������̳ǵĺ������
        //var_dump($assign_store_id);
         /*$this->assign('assign_store_list', array(
            1 => Lang::get('yes'),
            0 => Lang::get('no'),
        ));*/
        //var_dump($orders);
        $page['item_count'] = $model_order->getCount();   //��ȡͳ�Ƶ�����
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //�Ƿ��в�ѯ����
        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->assign('orders', $orders);
        //var_dump($orders);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display("allocate.index.html");
    }
/*	function index(){
		$allocate_info = $this->_allocate_mod->find();
	    $this->assign("allocate" , $allocate_info); // ���� 
	    $conditions = ' 1 = 1';
	    $page_num = 1;
	    $assign_store_id = empty($_GET['assign_store_id']) ? 0 : intval($_GET['assign_store_id']);
    	$add_time = empty($_GET['add_time']) ? 0 : intval($_GET['add_time']);
		switch($assign_store_id) {
			case 1: $conditions .= " AND assign_store_id = '0' "; break;
			case 2: $conditions .= " AND assign_store_id != '0' "; break;
			//case 0: $this->assign('assign_store_id',0); ;break;
			default : $this->show_warning("������������! λ�� question type!");
		}
		if($add_time != 0) {
			$conditions .= " AND class_id = " . $add_time;	
			$this->assign("add_time" , $add_time);
		}
		$page = $this->_get_page($page_num);
    	$page['item_count'] = $this->getQuestion($conditions,$page,1);
    	$list = $this->getQuestion($conditions,$page);
        $this->_format_page($page);
        $this->assign('list',$list);
    	//��ȡ��ҳ��ʾ����

        //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->assign('page_info', $page);
        //var_dump($a);
        $this->display("allocate.index.html");
	}
	public function getQuestion($conditions , $page , $is_count = 0) {
		if($is_count == 0) { // ��ͳ����Ŀ,Ҳ����ֱ�ӷ��ؼ�¼
			$list = $this->_allocate_mod->find(array(
				'conditions' => 'seller_id=3' .$conditions,
				'limit' => $page['limit'],
				'join'	=> 'has_orderextm',
				'order'	=> 'order_id'
			));
			return $list;
		} else {
			$count = $this->_allocate_mod->getOne("select count(*) from pa_order o left join pa_order_extms e on o.class_id = e.class_id  where ".$conditions);
			return $count;
		}
	}*/
	function view(){
		//��ȡ�鿴������Ϣ��id
		if (isset($_GET['order_id'])){
			$order_id=$_GET['order_id'];
			//var_dump($order_id);
			$viewSQL="select a.order_sn,a.status,a.assign_store_id,a.add_time,b.goods_name,b.price,b.quantity,c.region_name from pa_order a,pa_order_goods b,pa_order_extm c where a.order_id=b.order_id and b.order_id=c.order_id and b.order_id=c.order_id and a.order_id='$order_id'";
			$view=$this->_order_mod->getAll($viewSQL);
			
			$this->assign('view',$view);
			//var_dump($view);
		}
		$this->display("allocate.view.html");
	}
/*
 *   ���ɹ���
 * 
 *  @author ��׳
 * 
 * */
	function add()
	{
		if(!IS_POST) {
			
			//��ȡ����
			$order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
			if($order_id == 0) {
				$this->show_warning("unsettled_required");
				return;
			}
			$store_info = $this->getStoreInfo($order_id);
			//var_dump($store_info);
			if(is_array($store_info)) {
				$this->assign("store_info",$store_info);
			} else {
				$this->assign("store_info",0);
			}
			//ʡ������������
			$this->assign('site_url', site_url());
			$this->assign('order_id',$order_id);
			$this->assign('regions', $this->_region_mod->get_options(0));
			/* ����jQuery�ı���֤��� */
			$this->import_resource(array('script' => 'mlselection.js,jquery.plugins/jquery.validate.js'));
			$this->display('allocate.form.html');
		} else { //�ύ������
			header('location:index.php?app=allocate');
		}
	}

    //���̲�ѯ
    function searchStore(){
    	$region_id = empty($_GET['region_id']) ? 0 : intval($_GET['region_id']);
    	$order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
    	if($order_id == 0) {
			$this->json_error('δ�ṩ������Ϣ��');
			return;
		}
		$store_info = $this->getStoreInfo($order_id , $region_id);
   	 	if(is_array($store_info)) {
			$this->json_result(array(
           		'store_info'  =>  $store_info,                      //���ص�����Ϣ
			));
		} else if($store_info == -1) {
			$this->json_error('δ������������Ʒ�ĵ��̣�');
			return;
		} else {
			$this->json_error('δ���������̣�');
			return;
		}
		
  	}
  	//������Ϣ
  	function getStoreInfo($order_id,$region_id = 0) {
  		//������Ʒ��Ϣ
		$order_info = $this->_order_mod->getAll("select o.order_id,o.order_sn,o.status,o.assign_store_id,o.add_time,o.goods_amount,oe.region_id,oe.region_name,o.order_sn,og.goods_id,og.goods_name,og.price,og.spec_id,og.quantity from pa_order o left join pa_order_extm oe on o.order_id=oe.order_id left join pa_order_goods og on o.order_id=og.order_id where o.order_id={$order_id}");
		//����������Ʒ��Ϣ
		//var_dump($order_info);
		$this->assign('orderinfo',$order_info);
		$region_id = $region_id != 0 ? $region_id : intval($order_info[0]['region_id']);
		/**
		 *	�Զ���ȡ�����̻�
		 */
		//var_dump($region_id);
		//������Ʒ��Ϣ����
		$goods_info = array();
		$store_info = array(); //�ⲿ���飬ȷ������������
		while($region_id != 0) {
			//���ҵ���
			$searchRegionSql = "select * from pa_store where store_id <> 3 and  region_id={$region_id}";
			$store_info = $this->_store_mod->getAll($searchRegionSql);
			if(!empty($store_info)) {
				break;
			} else {
				$region_info = $this->_region_mod->getRow("select * from pa_region where region_id={$region_id}");
				$region_id = $region_info['parent_id']; 
			}
		}
		if(!empty($store_info)) {
			$true_store_info = array(); // ����Ʒ�ĵ���
			
			foreach($store_info as $k => $v) {
				$is_true = true;
				$store_id = $v['store_id'];
				foreach($order_info as $_k => $_v) {
					$searchOrderGoodsSQL = 'select * from pa_store s left join pa_store_goods pg on s.store_id=pg.store_id WHERE pg.goods_id= '.$_v['goods_id'].' AND pg.spec_id= ' . $_v['spec_id'] . ' and pg.stock >= ' . $_v['quantity'] . ' and s.store_id = ' . $store_id;
					$goods = $this->_store_mod->getRow($searchOrderGoodsSQL);
					if(empty($goods)) {
						$is_true = false;
						break;
					}
				}
				if($is_true) {
					$true_store_info[] = $v;
					$true_store_info[] = $v;
				}
			}
			if(!empty($true_store_info)) {//���Զ����������̣����ҵ������ж����е���Ʒ
				//$this->assign("true_store_info",$true_store_info);
				return $true_store_info;
			} else { //�Զ����������̣� ������û����Ʒ
				return -1;
			}
		} else {
			//�Զ�����û��������
			return -2;
		}
  	}
  		/*��������*/
/*	function search()
	{
		
		$statue=$_POST['stu'];
		$time1=$_POST['add_time_from'];
		$time2=$_POST['add_time_to'];
		//var_dump($statue);exit();
		//������������Ƿ�ֵ
		if($statue==2 and !empty($time1) and !empty($time2))
		{
			 $sql="select o.add_time,o.order_sn,e.region_name,o.assign_store_id from pa_order o,pa_order_extm e  where
		     o.seller_id=3 and FROM_UNIXTIME(o.add_time) BETWEEN  '$time1'  AND  '$time2' and e.order_id=o.order_id";
	         $arr=$this->_order_mod->getall($sql);
	         $this->assign('allocate',$arr);
		}
			//�жϷ���״̬��$statueΪ0Ϊδ����״̬,��Ϊ0���Ƿ���״̬
		elseif($statue==0)
		{
		     $sql="select o.add_time,o.order_sn,e.region_name,o.assign_store_id from pa_order o,pa_order_extm e  where
		     o.seller_id=3 and o.assign_store_id=0 or FROM_UNIXTIME(o.add_time) BETWEEN  '$time1'  AND  '$time2' and e.order_id=o.order_id";
	         $arr=$this->_order_mod->getall($sql);
	         var_dump($arr);
	         $this->assign('allocate',$arr);
         //�����������ֵ������ʾ��Ʒ������
         if(!$arr)
         {
         	$this->show_warning("����������Ʒ������ ! ����������!");exit();
         }
         }
         //������䣬��ִ��else
		else 
		{
		  $sql="select o.add_time,o.order_sn,e.region_name,o.assign_store_id from pa_order o,pa_order_extm e  where
	      o.seller_id=3 and e.order_id=o.order_id";
          $arr=$this->_order_mod->getall($sql);         
	      $this->assign('allocate',$arr);	
		 }
		
	
	 $this->display("allocate.index.html");
	}*/
  	/*function search(){
  		$order_info = $this->_order_mod->find();
  		$this->assign("order_infos" , $order_info); // ���� 
  		$conditions = ' 1 = 1';
	    $page_num = 3;
	    $assign_store_id=empty($_GET['assign_store_id']) ? 2 : intval($_GET['assign_store_id']);
	    $add_time=empty($_GET['add_time']) ? '' : trim($_GET['add_time']);
	    switch ($assign_store_id){
	    	case 0: $conditions .= " AND assign_store_id = 0 ". ' . δ����.';break;
	    	case 1: $conditions .= " AND assign_store_id = 1 ". ' . �ѷ���.';break;
	    	case 2: $this->assign('assign_store_id',3);break;
			default : $this->show_warning("������������! λ�� assign_store_id!");
	    }
  	    if($add_time != '') {
			$conditions .= " AND add_time = " . $add_time;
			$this->assign("stem" , $add_time);
		}
		$page = $this->_get_page($page_num);
    	$page['item_count'] = $this->getQuestion($conditions,$page,1);
    	$list = $this->getQuestion($conditions,$page);
        $this->_format_page($page);
        $this->assign('list',$list);
    	//��ȡ��ҳ��ʾ����

        //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->assign('page_info', $page);
	    $this->display("allocate.index.html");
  	}*/

	/*public function getAllocate($conditions , $page , $is_count = 0) {
		if($is_count == 0) { // ��ͳ����Ŀ,Ҳ����ֱ�ӷ��ؼ�¼
			$list = $this->_order_mod->find(array(
				'conditions' => $conditions,
				'limit' => $page['limit'],
				//'join'	=> 'belongs_to_questionclasses',
				'sort'	=> 'add_time'
			));
			return $list;
		} else {
			$count = $this->_order_mod->getOne("select count(*) from pa_order o where" . $conditions);
			return $count;
		}
	}*/
  	
}
?>