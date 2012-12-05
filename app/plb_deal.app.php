<?php
/*
*�����ҽ���ר��ǰ̨������
* &@author ����
* */
class Plb_dealApp extends MallbaseApp
{
	var $_credit_goods_mod;
	var $_credit_order_mod;
	public function __construct()
    {
    	
        $this->Plb_dealApp();
    }

    public function Plb_dealApp()
    {
    	parent::__construct();
    	$this->_credit_goods_mod=& m('creditgoods');
    	$this->_credit_order_mod=& m('creditorder');
    }
    //��ʾ
	public function index()
	{
		date_default_timezone_set("Asia/Shanghai");
		$this->assign('index', 4); // ��ʶ��ǰҳ������ҳ���������õ���״̬
		$num = 12;
		$conditions = " 1 = 1 AND c.type != 3 AND c.type != 4";
		$credit_goods_info=$this->_credit_goods_mod->getAll("select * from pa_credit_goods c where " . $conditions . " order by c.time desc " . " limit " .$num);
		foreach($credit_goods_info as $k1 => $v1) {
	     	$credit_goods_info[$k1]['time'] = date('Y-m-d H:i',$v1['time']);
	     }
		$order_info = $this->_credit_order_mod->getAll("select * from pa_credit_order o order by pay_time desc limit 0,3");

		/*$arr=array();
		$time=time();
		foreach($order_info as $k=>$v)
		{
    		$order_info[$k]['time']=floor(($time-$v['pay_time'])/60);

		}*/
		foreach($order_info as $k_1 => $v_1) {
	     	$order_info[$k_1]['add_time'] = date('Y-m-d H:i',$v_1['add_time']);
	     }
		$this->assign('order_info',$order_info);
		$this->assign("credit_goods_info",$credit_goods_info);
		$this->display("plb_trading.html");
	}
	//����
	function search()
	{
		date_default_timezone_set("Asia/Shanghai");
		$page_num = 20;
        $page = $this->_get_page($page_num);
        $credit_num = empty($_GET['credit_num']) ? 0 : intval($_GET['credit_num']);
        $info = empty($_GET['info']) ? '' : trim($_GET['info']);
        $opt = empty($_GET['opt']) ? 0 : intval($_GET['opt']);
        $min = empty($_GET['min']) ? NULL : intval($_GET['min']);
        $max = empty($_GET['max']) ? NULL : intval($_GET['max']);
        $searchInfo = array();
        $conditions = " 1 = 1 AND c.type != 3 AND c.type != 4";
        //�۸�
        switch ($opt)
        {
        	case 0:
        		$conditions .= " AND 1 = 1 ";
        		$searchInfo['opt'] = 0;
        		break;
        	case 1:
        		$conditions .= " AND c.price between 0 and 60 ";
        		$searchInfo['opt'] = 1;
        		break;
        	case 2:
        		$conditions .= " AND c.price between 100 and 199 ";
        		$searchInfo['opt'] = 2;
        		break;
        	case 3:
        		$conditions .= " AND c.price between 200 and 299  ";
        		$searchInfo['opt'] = 3;
        		break;
        	case 4:
        		$conditions .= " AND c.price between 300 and 399  ";
        		$searchInfo['opt'] = 4;
        		break;
        	case 5:
        		$conditions .= " AND c.price between 400 and 500  ";
        		$searchInfo['opt'] = 5;
        		break;
        	default : 
        		$this->show_warning("����������������� "); 
        		return;
        }
        //������
        if($credit_num!=0)
        {
        	$conditions .= " AND c.credit_num = " . $credit_num;
        	$searchInfo['credit_num'] = $credit_num;
        	//var_dump($credit_num);
        }
        //�ؼ���
        if($info!='')
        {
        	$conditions .= " AND c.info like '%".$info."%' ";
        	$searchInfo['info'] = $info;
        }
        //����������
        if ($max != NULL && $min != NULL)
        {
        	$conditions .= "AND c.credit_num between  " .$min . " and " .$max;
        	$searchInfo['min'] = $min;
        	$searchInfo['max'] = $max;
        	if ($max <= 0 || $min >= $max || $min < 0 )
        	{
        		$this->show_warning("�������!");
        		return;
        	}
        }
        $this->assign('searchInfo', empty($searchInfo) ? false : $searchInfo);
        $count = $this->_credit_goods_mod->getOne("select count(*) from pa_credit_goods c where " .$conditions);
        $page['item_count'] = $count;
        $credit_info=$this->_credit_goods_mod->getAll("select * from pa_credit_goods c where " .$conditions . " order by c.time desc limit " .$page_num );
		foreach($credit_info as $k2 => $v2) {
	     	$credit_info[$k2]['time'] = date('Y-m-d H:i',$v2['time']);
	     }
	     
        $this->_format_page($page);
		$this->assign('page_info', $page);
		$this->assign('credit_info',$credit_info);
		//���ۺ͹�����Ϣ����ʾ
		$order_info = $this->_credit_order_mod->getAll("select * from pa_credit_order o order by pay_time desc limit 0,3");
		/*$time=time();
		foreach($order_info as $k=>$v)
		{
    		$order_info[$k]['time']=floor(($time-$v['pay_time'])/60);

		}*/
		foreach($order_info as $k3 => $v3) {
	     	$order_info[$k3]['add_time'] = date('Y-m-d H:i',$v3['add_time']);
	     }
		//var_dump($order_info);
		$this->assign('order_info',$order_info);
        $this->display("plb_trading_level2.html");
        
	}
	//�����ҽ��׷���
	/*function add(){
		$user_id=$this->visitor->get('user_id');
		$user_name=$this->visitor->get('user_name');
		$num=$_POST['opt'];//��ȡ������������
		//var_dump($num);
		$cycle=$num * 24 * 60 * 60;//���㽻����������
		$info=$_POST['info'];
		$this->display("credit.add.html");
    	if (IS_POST){
    		
    		if(isset($_POST['Submit'])){
    			$data=array();
    			$data['user_id']=$user_id;
    			$data['user_name']=$user_name;
    			$data['credit_num']=intval($_POST['credit_num']);
    			$data['type']=intval($_POST['type']);
    			$data['price']=intval($_POST['intval']);
    			$data['info']=trim($_POST['info']);
    			$data['time']=time();
    			$data['cycle']=$cycle;
    			$credit = $this->_credit_goods_mod->add($data);
    		}
    		header("Location:index.php?app=member&act=credit");
    		}
		
	}*/
	/*function form()
	{
		$user_id=$this->visitor->get('user_id');
	}*/
}
?>