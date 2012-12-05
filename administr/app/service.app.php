<?php
	class ServiceApp extends BackendApp
	{
		var $_goods_mod;
		var $_gcategory_mod;
		var $_user_mod;
		var $_order_mod;
		var $_member_mod;
		function __construct()
		{
			$this->ServiceApp();
		}
		function ServiceApp()
		{
			parent::__construct();
			$this->_goods_mod = &m('goods');
			$this->_gcategory_mod = &m('gcategory');
			$this->_user_mod =& m('member');
			$this->_order_mod = &m('order');
			$this->_member_mod = &m('member');
		}
		//��Ʒ����
		function index()
		{
			$conditions = '1 =1 ';
	    	$conditions .= $this->_get_query_conditions(array(
	            array(
	                'field' => 'goods_name',
	                'equal' => 'like',
	            ),
	            array(
	                'field' => 'brand',
	                'equal' => 'like',
	            ),
	        ));
	        // ����
	        $cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
			if ($cate_id > 0)
	        {    
	            $cate_ids = $this->get_descendant_ids($cate_id);
	            $conditions .= " AND g.cate_id" . db_create_in($cate_ids);
	        }
	        //��������
	        if (isset($_GET['sort']) && isset($_GET['order']))
	        {
	            $sort  = strtolower(trim($_GET['sort']));
	            $order = strtolower(trim($_GET['order']));
	            if (!in_array($order,array('asc','desc')))
	            {
	             $sort  = 'goods_id';
	             $order = 'desc';
	            }
	        }
	        else
	        {
	            $sort  = 'goods_id';
	            $order = 'desc';
	        }
	        $page = $this->_get_page(20);	       
	        $page['item_count'] = $this->_goods_mod->getOne('select count(*) from (select count(*) from pa_goods g left join pa_store_goods sg on g.goods_id = sg.goods_id where g.status = 1 and '.$conditions.' and g.closed = 0 and g.if_show = 1 group by g.goods_id ) aa ');		
	        $goods_list = $this->_goods_mod->getAll('SELECT g.goods_id,g.goods_name,g.brand,g.cate_name,g.smimage_url,sg.gs_id FROM pa_goods g left join pa_store_goods sg on g.goods_id = sg.goods_id  WHERE ' .$conditions.' and g.status = 1 and g.closed = 0 and g.if_show = 1 group by g.goods_id  limit '.$page['limit']);
	        foreach ($goods_list as $key => $goods)
	        {
	            $goods_list[$key]['cate_name'] = $this->_goods_mod->format_cate_name($goods['cate_name']);
	        }
	        $this->assign('goods_list', $goods_list);
	        $this->_format_page($page);
	        $this->assign('page_info', $page);
	        // ��һ������
	        $this->assign('gcategories', $this->_gcategory_mod->get_all_options(0));
	        $this->import_resource(array('script' => 'mlselection.js,inline_edit.js'));
	        $this->assign('imgurl', IMAGE_URL);
			$this->display('service.index.html');
		}
		//��Ա��Ϣ
		function user()
		{		
	        $conditions = $this->_get_query_conditions(array(
	            array(
	                'field' => $_GET['field_name'],
	                'name'  => 'field_value',
	                'equal' => 'like',
	            ),
	        ));       
	        //��������
	        if (isset($_GET['sort']) && !empty($_GET['order']))
	        {
	            $sort  = strtolower(trim($_GET['sort']));
	            $order = strtolower(trim($_GET['order']));
	            if (!in_array($order,array('asc','desc')))
	            {
	             $sort  = 'user_id';
	             $order = 'asc';
	            }
	        }
	        else
	        {
	            if (isset($_GET['sort']) && empty($_GET['order']))
	            {
	                $sort  = strtolower(trim($_GET['sort']));
	                $order = "";
	            }
	            else
	            {
	                $sort  = 'user_id';
	                $order = 'asc';
	            }
	        }
	        $page = $this->_get_page();
	        $users = $this->_user_mod->find(array(
	            'join' => 'has_store,manage_mall',
	            'fields' => 'this.*,store.store_id,userpriv.store_id as priv_store_id,userpriv.privs',
	            'conditions' => '1=1' . $conditions,
	            'limit' => $page['limit'],
	            'order' => "$sort $order",
	            'count' => true,
	        ));
	        foreach ($users as $key => $val)
	        {
	            if ($val['priv_store_id'] == 0 && $val['privs'] != '')
	            {
	                $users[$key]['if_admin'] = true;
	            }
	        }
	        $this->assign('users', $users);
	        $page['item_count'] = $this->_user_mod->getCount();
	        $this->_format_page($page);
	        $this->assign('filtered', $conditions? 1 : 0); //�Ƿ��в�ѯ����
	        $this->assign('page_info', $page);
	        /* ����jQuery�ı���֤��� */
	        $this->import_resource(array(
	            'script' => 'jqtreetable.js,inline_edit.js',
	            'style'  => 'res:style/jqtreetable.css'
	        ));
	        $this->assign('query_fields', array(
	            'user_name' => LANG::get('user_name'),
	            'email'     => LANG::get('email'),
	            'real_name' => LANG::get('��ʵ����'),
	            'mobile' => LANG::get('mobile'),
	        ));
	        $this->assign('sort_options', array(
	            'reg_time DESC'   => LANG::get('ע��ʱ��'),
	            'last_login DESC' => LANG::get('����¼'),
	            'logins DESC'     => LANG::get('��¼����'),
	        )); 
			$this->display('service.user.html');
		}
		//��������
		function order()
		{
	        $search_options = array(
	            'seller_name'   => Lang::get('store_name'),
	            'buyer_name'   => Lang::get('buyer_name'),
	            'payment_name'   => Lang::get('payment_name'),
	            'order_sn'   => Lang::get('order_sn'),
	        );
	        /* Ĭ���������ֶ��ǵ����� */
	        $field = 'seller_name';
	        $status=empty($_GET['status']) ? 11 : intval($_GET['status']);
	        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
	        $conditions = $this->_get_query_conditions(array(array(
	                'field' => $field,       //���û���,������,֧����ʽ���ƽ�������
	                'equal' => 'LIKE',
	                'name'  => 'search_name',
	            ),array(
	                'field' => 'status',
	                'equal' => '=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'add_time',
	                'name'  => 'add_time_from',
	                'equal' => '>=',
	                'handler'=> 'gmstr2time',
	            ),array(
	                'field' => 'add_time',
	                'name'  => 'add_time_to',
	                'equal' => '<=',
	                'handler'   => 'gmstr2time_end',
	            ),array(
	                'field' => 'order_amount',
	                'name'  => 'order_amount_from',
	                'equal' => '>=',
	                'type'  => 'numeric',
	            ),array(
	                'field' => 'order_amount',
	                'name'  => 'order_amount_to',
	                'equal' => '<=',
	                'type'  => 'numeric',
	            ),
	        ));
	        $page   =   $this->_get_page(10);    //��ȡ��ҳ��Ϣ
	        //��������
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
	        $orders = $this->_order_mod->find(array(
	            'conditions'    => '1=1 ' . $conditions,
	            'limit'         => $page['limit'],  //��ȡ��ǰҳ������
	            'order'         => "$sort $order",
	            'count'         => true             //����ͳ��
	        )); //�ҳ������̳ǵĺ������
	        $page['item_count'] = $this->_order_mod->getCount();   //��ȡͳ�Ƶ�����
	        $this->_format_page($page);
	        $this->assign('filtered', $conditions? 1 : 0); //�Ƿ��в�ѯ����
	        $this->assign('order_status_list', array(
	            ORDER_PENDING => Lang::get('�ȴ���Ҹ���'),
	            ORDER_ACCEPTED => Lang::get('����Ѹ���'),
	            ORDER_SHIPPED => Lang::get('�����ѷ���'),
	            ORDER_FINISHED => Lang::get('���׳ɹ�'),
	            ORDER_REFUND => Lang::get('�˿���'),
	            ORDER_REFUND_FINISH => Lang::get('�˿����'),
	            ORDER_CANCELED => Lang::get('����ȡ��'),
	        ));
	        $this->assign('search_options', $search_options);
	        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
	        $this->assign('orders', $orders);
	        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
	                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
			$this->display('service.order.html');
		}
		//���ŷ���
		function send()
		{		  
			$id = empty($_GET['id']) ? '' : intval($_GET['id']);
			if(!id)
			{
				$this->show_message('');
				return;
			}else{
				$user = $this->_member_mod->getRow('select * from pa_member where user_id ='.$id);
			}		
			if(!IS_POST)
			{	
				$this->assign('user',$user);
				$this->display("service.send.html");
			}else{
				$contnet = empty($_POST['content']) ? '' : trim($_POST['content']);
				$smslog =&  m('smslog'); 
		      	import('class.smswebservice');    //������ŷ�����
		   		$sms = SmsWebservice::instance(); //ʵ�������Žӿ���
			    $smscontent = $contnet;
			    $mobile = $user['mobile'];
			    $verifytype = "system";	
		      	$result= $sms->SendSms2($mobile,$smscontent); //ִ�з��Ͷ������Ѳ���
		      	//���ŷ��ͳɹ�
		        if ($result == 0) 
		        {
		        	$time = time();
		        	//ִ�ж�����־д�����
		        	$smsdata['mobile'] = $mobile;
		        	$smsdata['smscontent'] = $smscontent;
		        	$smsdata['type'] = $verifytype; //��������
		        	$smsdata['sendtime'] = $time;
		        	$smsdata['user_id'] = $id;
		       		$smslog->add($smsdata);
		       		$this->show_message('���ŷ��ͳɹ�!',
		       		'����','index.php?app=service&act=user');
		       	}
			}  
		}
		function get_descendant_ids($id)
	    {
	        $res = array($id);     
	            $cids = array($id);
	            while (!empty($cids))
	            {
	                $sql  = "SELECT cate_id FROM pa_gcategory WHERE parent_id " . db_create_in($cids);
	                $cids = $this->_gcategory_mod->getCol($sql);
	                $res  = array_merge($res, $cids);
	            }
	        return $res;
	    }
	}
?>