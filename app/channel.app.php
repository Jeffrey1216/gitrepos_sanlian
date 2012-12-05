<?php
define('PAGE_NUM',20);
/* �����̿����� */
class ChannelApp extends MemberbaseApp
{
	private static $recomManager_info = array();
	var $algebra;
	private static $rate_arr = array();
	var $_store_order_mod;
	var $_store_order_log_mod;
	var $_store_order_extm_mod;
	function __construct(){
		$this->ChannelApp();
	}
 	function ChannelApp(){
    	parent::__construct();
    	$this->_store_order_mod=& m('storeorder');
    	$this->_store_order_log_mod=& m('storeorderlog');
    	$this->_store_order_extm_mod=& m('storeorderextm');
    	
    }
    
	//���������
    function index()
    {
    	return false;
    	/*����Ȩ��*/
    	$this->prower();
    	/* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                         LANG::get('basic_information'));

        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('channel_verify');

        /* ��ǰ�����Ӳ˵� */
        $this->_curmenu('basic_information');
		/* ��ǰ�û�������Ϣ*/
        $this->_get_user_info();
		$customerAsk_mod = & m('customerask');
    	$page = $this->_get_page(PAGE_NUM);
    	$page['item_count'] = $customerAsk_mod->getOne('select count(*) from pa_customer_ask');
    	
    	$customerAsk_list = $customerAsk_mod->getAll('select * from pa_customer_ask limit ' . $page['limit']);
    	
    	$this->_format_page($page);
    	
    	$this->assign('customerAsk_list', $customerAsk_list);
    	
    	$this->assign('page_info', $page);
        $this->display('channel_verify.index.html');
    }
    
    
	function _upload_files($style)
   	 {
        import('uploader.lib');
        $data      = array();
        /* acticle_logo */
        $file = $_FILES[$style];
      
        if ($file['error'] == UPLOAD_ERR_OK && $file !='')
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->allowed_size(1024000); // 1M
            $uploader->addFile($file);
            if ($uploader->file_info() === false)
            {
                $this->show_warning($uploader->get_error());
                return false;
            }
            $uploader->root_dir(ROOT_PATH);
        	$filename  = $uploader->random_filename();
            $fileurl = $uploader->save('data/files/partner/'.$style, $filename);
        }
        return $fileurl;
   	 }
   	 
   	 function create_sn() {
		/* ѡ��һ������ķ��� */
	    mt_srand((double) microtime() * 1000000);
	    return  date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
	}
   	 function create_ordersn() {
		/* ѡ��һ������ķ��� */
	    mt_srand((double) microtime() * 1000000);
	    return  date('Ymd') . str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
	}
	
	//�����Ƽ�������
	function getRecomRate($area,$level,$recom_area,$recom_level){
		$channelfee = & m('channelfee');    //��ѯ�����̷��ñ�
		$channelUser_mod = & m('channeluser');
		$channelLevel_mod = & m('channellevel');
		$channelLevel_info = $channelLevel_mod->get("id = " . $level );
		$recomChannelLevel_info = $channelLevel_mod->get("id = " . $recom_level);
		//ֱ���Ƽ��˷���
		if ($this->isIdenticalArea($area, $level, $recom_area, $recom_level)) //��ͬ����
		{
			$recom_info = $channelfee->get(" level = " . $recom_level . " and area_id = " . $recom_area);
			$recom_rate = $recom_info['recom_rate'];
			$channelUser_info = $channelUser_mod->get(" level = " . $recom_level . " and area_id = " . $recom_area);
			$this->getParentArea($area, $channelLevel_info['parent_level'], $recom_rate, $channelUser_info['channel_id'], $recomChannelLevel_info['hierarchy'], true);
		} else { //����ͬ����
			$recom_info = $channelfee->get(" level = " . $level . " and area_id = " . $area);
			$recom_rate = $recom_info['recom_rate'];
			$channelUser_info = $channelUser_mod->get(" level = " . $recom_level . " and area_id = " . $recom_area);
			$this->getParentArea($area, $channelLevel_info['parent_level'], $recom_rate, $channelUser_info['channel_id'], $recomChannelLevel_info['hierarchy'], false);
		}
		//ֱ���Ƽ��˵�channel_id

		return array(0=>$recom_rate,1=>self::$rate_arr);
	}
	
	public function getParentArea($area, $level, $rate, $channel_id, $recom_hierarchy, $flag)  //����Ƽ�����
	{
		//����Ѱ��ֱ���ϼ�
		$channelLevel_mod = & m('channellevel');
		$channelUser_mod = & m('channeluser');
		$region_mod = & m('region');
		$channelFee_mod = & m('channelfee');
		$channelLevel_info = $channelLevel_mod->get("id = " . $level );
		
		$area_id = 0; //����Ҫ��ȡ�����ĵ���ID
		//��ֱ���ϼ��ĵ���
		$area_info = $region_mod->get($area);
		if ($area_info['level'] > $channelLevel_info['area_level']) //�ϼ��������������ڱ������ϼ����� 
		{
			//Ѱ������
			$region_info = $region_mod->getRow("select * from pa_region where region_id = (select parent_id from pa_region where region_id = " . $area . ")");
			$area_id = $region_info['region_id'];
		} else { //�ϼ��������ڵĵ����ͱ������ڵ�������ͬ
			$area_id = $area;
		}
		//����fee , ��ȡӦ�÷���
		$fee_info = $channelFee_mod->get(" area_id = " . $area_id . " AND level = " . $level); //�ϼ�������Ϣ
		
		$channelUser_info = $channelUser_mod->get(" area_id = " . $area_id . " AND level = " . $level); //�鿴�Ƿ����⵱ǰ�ȼ�������,����оͶ��巵������
		if ($flag)
		{
			if ($channelUser_info && $channelUser_info['channel_id'] != $channel_id && $channelLevel_info['hierarchy'] < $recom_hierarchy) {
				self::$rate_arr[$channelUser_info['channel_id']] = $fee_info['recom_rate'] - $rate;
			}
		} else {
			if ($channelUser_info && $channelUser_info['channel_id'] != $channel_id) {
				self::$rate_arr[$channelUser_info['channel_id']] = $fee_info['recom_rate'] - $rate;
			}
		}
		$rate = $fee_info['recom_rate'];
		
		if ($channelLevel_info['parent_level'] == 0) { //����ϼ���parent_idΪ0,  ��ʾ����Ϊ���ϼ�.  ����Ҫ���Ϸ���
			return false;
		}
		$this->getParentArea($area_id, $channelLevel_info['parent_level'], $rate, $channel_id, $recom_hierarchy, $flag);
	}
	
	public function getBelongArea($area)
	{
		$region_mod = & m("region");
		$region_info = $region_mod->get($area);
		if ($region_info['level'] == 1)
		{
			$this->show_warning("���ĵ�����д����ȷ.��ѡ�� ��/�� ��.");
			return false;
		} 
		if ($region_info['level'] == 2) //������м�ֱ�ӷ��� 
		{
			return $region_info['region_id'];
		} 
		//�������ݹ����
		return $this->getBelongArea($region_info['parent_id']);
	}
	
	public function isIdenticalArea($area,$level,$recom_area,$recom_level)
	{
		if (($this->getBelongArea($area)) == ($this->getBelongArea($recom_area)))
		{
			return true;
		} else {
			return false;
		}
		
	}
	
	//�����̷��������ѯ�Ƽ�������
	function checkfee_arr($fee,$area,$level){
		foreach ($fee as $v){
			if($v['area_id']==$area && $v['level']==$level){
				return $v['recom_rate'];exit;
			}
		}
	}
	
	//�ж��Ƿ���ڼ���Ƽ���
	function check_recom($area,$level){
		$channeluser =&m('channeluser');    //������
		$cuserarea = $channeluser->get("level='$level' and area_id='$area' and exp_time>".time());
		if($cuserarea){
			return $cuserarea['channel_id'];
		}		
	}
    
    public function verifyManager() //����Ź�Ա
    {
    	/*����Ȩ��*/
    	$this->prower();
    	/* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                         LANG::get('basic_information'));

        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('channel_verify');

        /* ��ǰ�����Ӳ˵� */
        $this->_curmenu('basic_information');
		/* ��ǰ�û�������Ϣ*/
        $this->_get_user_info();
    	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	$customerAsk_mod = & m('customerask');
    	$customer_info = $customerAsk_mod->getRow('select * from pa_customer_ask ca left join pa_customer_level cl on 
    	ca.need_level = cl.level_id where ca.id = ' . $id);
    	
    	if ($id == 0)
    	{
    		$this->show_warning('�������...');
    	}
    	if (!IS_POST)
    	{
    		$this->assign('info', $customer_info);
    		$this->display('channel.verify.html');
    	} else {
    		if (trim($_POST['submit']) == 'ͬ��')
    		{
    			//�жϱ����Ƿ�Ϊ�ͻ�����
    			$customerManager_mod = & m('customermanager');
    			$customerManager_info = $customerManager_mod->get($customer_info['user_id']);
    			$algebra = $this->get_algebra($customer_info);
    			if (!$customerManager_info) //���˲��ǿͻ�����,  ��Ҫ���
    			{
    				$data = array(
    					'user_id' => $customer_info['user_id'],
	    				'user_name' => $customer_info['user_name'],
	    				'outstanding_achievement_total' => 0,
    					'gains_total' => 0,
	    				'parent_id' => $customer_info['recom_user_id'],
	    				'customer_level' => $customer_info['need_level'],
	    				'real_name' => $customer_info['real_name'],
	    				'sex' => $customer_info['sex'],
	    				'identity_num' => $customer_info['identity_num'],
	    				'identity_card' => $customer_info['identity_card'],
	    				'tel_phone' => $customer_info['tel_phone'],
	    				'email' => $customer_info['email'],
	    				'region_id' => $customer_info['region_id'],
	    				'region_name' => $customer_info['region_name'],
	    				'address' => $customer_info['address'],
    					'reg_time' => gmtime(),
    					'algebra'  => $algebra,
    				);
    				$customerManager_mod->add($data);
    			} else { //�����ǿͻ�����, �༭
    				$data = array(
    					'user_id' => $customer_info['user_id'],
	    				'user_name' => $customer_info['user_name'],
	    				'outstanding_achievement_total' => $customerManager_info['outstanding_achievement_total'],
	    				'parent_id' => $customer_info['recom_user_id'],
	    				'customer_level' => $customer_info['need_level'],
	    				'real_name' => $customer_info['real_name'],
	    				'sex' => $customer_info['sex'],
	    				'identity_num' => $customer_info['identity_num'],
	    				'identity_card' => $customer_info['identity_card'],
	    				'tel_phone' => $customer_info['tel_phone'],
	    				'email' => $customer_info['email'],
	    				'region_id' => $customer_info['region_id'],
	    				'region_name' => $customer_info['region_name'],
	    				'address' => $customer_info['address'],
    					'algebra' => $algebra,
    				);
    				$customerManager_mod->edit($customer_info['user_id'], $data);
    			}
    			
    			$this->clearManagerAsk($id);
    			header("Location:index.php?app=member&act=uninvitgroup");
    		} else {
    			$this->clearManagerAsk($id);
    			header("Location:index.php?app=member&act=uninvitgroup");
    		}
    	}
    }
    //��ȡ�Ƽ�������,   �Ƽ��˵ļ���Ϊ����� key , ��ȡ������Ϊ val
    private function getRecomManager($id, $key)
    {
    	$customerManager_mod = & m('customermanager');
    	self::$recomManager_info[$key] = $customerManager_mod->getRow('select cm.user_id, cm.parent_id, 
    	cl.recom_yidle_level_' . $key . ' as recom_yidle, cm.gains_total, cm.gains_now, 
    	cm.outstanding_achievement_total from pa_customer_manager cm left join pa_customer_level cl on 
    	cm.customer_level = cl.level_id where cm.user_id = ' . $id);
    	
    	if (self::$recomManager_info[$key]['parent_id'] != 0 && $key <= 5)
    	{
    		$id = self::$recomManager_info[$key]['parent_id'];
    		$key = $key + 1;		
    		$this->getRecomManager($id, $key);
    	}
    }
    
    private function clearManagerAsk($id)
    {
    	$customerAsk_mod = & m('customerask');
    	$customerAsk_mod->drop($id);
    } 
    function detail()
    {
    	$user_mod = & m('member');
    	$page = $this->_get_page(20);
    	$user_info = $user_mod->getAll('SELECT * from pa_account_log al where al.user_id=0 and al.change_type in(54,55,56,57) order by al.change_time desc limit '.$page['limit']);
		$change_type = get_change_type();
		foreach ($user_info as $k=>$v)
		{
			$user_info[$k]['change_type'] = $change_type[$v['change_type']];
			$user_info[$k]['change_time'] = $v['change_time'];
		}
    	$user_count = $user_mod->getRow('SELECT count(*) as count from pa_account_log al where al.user_id=0 and al.change_type in(54,55,56,57)');
        $page['item_count'] = $user_count['count'];        
        $this->_format_page($page);
        $this->assign('page_info', $page);
    	$this->assign('users',$user_info);
    	$this->display('channel.detail.html');
    }
    function manager_underling()
    {
    	$user_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
    	$page = $this->_get_page();   //��ȡ��ҳ��Ϣ
		
		$customermanager_mod = &m('customermanager');
  		$unintvitgroup = $customermanager_mod->getAll('select * from pa_customer_manager cm left join pa_customer_level cl on cl.level_id=cm.customer_level where parent_id='.$user_id.' limit '.$page['limit']);
  		$item_count = $customermanager_mod->getRow('select count(*) as count from pa_customer_manager where parent_id='.$user_id);
  		$page['item_count'] = $item_count['count'];
  		$this->assign('ungroup',$unintvitgroup);
  		$this->_format_page($page);
        $this->assign('page_info', $page);
        $this->display('ch_underling.html');
    }
    //ת�˹���
    function transfer_accounts()
    {
    	$user_name = empty($_GET['user_name']) ? 'all' : trim($_GET['user_name']);
    	$type = empty($_GET['type']) ? 'all' : trim($_GET['type']);
		$conditions = " where 1=1 ";
    	if($user_name != 'all')
		{
			$conditions .= " and m.user_name like '%".$user_name."%'";
		}
		if($type != 'all')
		{
			$conditions .= " and al.change_type in (".$type.")";
		}else {
			$conditions .= " and al.change_type in (42,43)";
		}
    	$accountlog_mod = &m ('accountlog');
    	$page = $this->_get_page(10);
    	$tr_account = $accountlog_mod->getAll('select * from pa_account_log al left join pa_member m on m.user_id=al.user_id '.$conditions.' limit '.$page['limit']);
    	$user_count = $accountlog_mod->getRow('SELECT count(*) as count from pa_account_log al left join pa_member m on m.user_id=al.user_id'.$conditions);
    	$change_type = get_change_type();
    	foreach ($tr_account as $k=>$v)
		{
			$tr_account[$k]['change_type'] = $change_type[$v['change_type']];
			$tr_account[$k]['change_time'] = $v['change_time'];
			$tr_account[$k]['user_money'] = abs($v['user_money']);
			$tr_account[$k]['user_credit'] = abs($v['user_credit']);
		}
    	$page['item_count'] = $user_count['count'];        
        $this->_format_page($page);
		$this->assign('page_info', $page);
    	$this->assign('users',$tr_account);
    	$this->display('tr_account.html');
    }
    
    //��ȡ�Ź�Ա����
    function get_algebra($user)
    {
    	if($user['recom_user_id'] == 0)
    	{
    		return 1;  		
    	}else {
    		$user_id = intval($user['recom_user_id']);
    		$algeb = $this->_get_last($user_id);
    		return $algeb;	
    	}
    }
    private function _get_last($user_id,$algebra = 2){
		$customerManager_mod = & m('customermanager');
		$cust_info = $customerManager_mod->get($user_id); //������һ��
		$u_id = intval($cust_info['parent_id']);			
		$this->algebra = $algebra;
		if ($u_id > 0)
		{
			$algebra++;	
			$this->algebra = $algebra;		
			$this->_get_last($u_id,$this->algebra);
		}
		return $this->algebra;
    }
    //���Ȩ��
    function prower()
    {
    	$user_id = $this->visitor->get(user_id);
    	if (intval($user_id) != CHANNEL_ID)
    	{
    		$this->show_warning('����Ȩ���ʵ�ǰҳ��');
    		return ;
    	}
    } 	
    
}

?>
