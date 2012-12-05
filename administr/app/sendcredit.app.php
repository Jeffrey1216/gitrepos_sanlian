<?php

/* ���ͻ��ֿ����� */
class SendcreditApp extends BackendApp {
var $_user_mod;
var $_credit_verify_mod;
var $_member_mod;
    function __construct()
    {
        $this->SendcreditApp();
    }
    function SendcreditApp()
    {
        parent::__construct();
        $this->_user_mod =& m('member');
        $this->_credit_verify_mod = &m('creditverify');
        $this->_member_mod = &m('member');
    }
    function index()
    {
    	if(!IS_POST) {
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
	            'real_name' => LANG::get('real_name'),
	//            'phone_tel' => LANG::get('phone_tel'),
	            'mobile' => LANG::get('phone_mob'),
	        ));
	        $this->assign('sort_options', array(
	            'reg_time DESC'   => LANG::get('reg_time'),
	            'last_login DESC' => LANG::get('last_login'),
	            'logins DESC'     => LANG::get('logins'),
	        ));
	        $this->display('sendcredit.index.html');
    	} else {
			$credit = change_type();
			if($credit == '' || $credit == 0 )
			{
				$this->show_warning("���ֱ䶯��������");
				return;
			}
    		$this->assign('credit',$credit);
	    	$ids = empty($_POST['ids']) ? 0 : $_POST['ids'];
	    	if($ids == 0) {
	    		$this->show_warning("��δѡ���κλ�Ա!");
	    		return;
	    	}
	    	$id_str = '';
	    	if(is_array($ids)) {
	    		foreach($ids as $k => $v) {
	    			$id_str .= $v . ",";
	    		}
	    		$id_str =  substr($id_str,0,-1);
	    		$user_list = $this->_user_mod->getAll("select m.user_id,m.user_name from pa_member m where m.user_id in (" . $id_str . ")");
	    		$this->assign('users',$user_list);
	    	} else {
	    		$this->show_warning("�������!.");
	    		return;
	    	}
	    	$this->display("sendcredit.form.html");
    	}
    }
    
    public function send() 
    {
    	$ids = empty($_POST['ids']) ? 0 : $_POST['ids'];
    	$credit = empty($_POST['credit']) ? 0 : floatval($_POST['credit']);
    	$type = empty($_POST['type']) ? 0 : intval($_POST['type']);
    	if($credit <= 0 || $credit == '')
    	{
    		$this->show_message('������д����,��������д!');
    		return;
    	}
    	if($ids == 0) {
    		$this->show_message("��δѡ���κλ�Ա!");
    		return;
    	}
    	if($credit == 0) {
    		$this->show_message("����д���ͻ�����Ŀ!");
    		return;
    	}
        if(is_array($ids)) {
	    	foreach($ids as $k => $v) {
	    		/* ��д���ּ�¼,��������  */
		        $param = array(
		        	'credit' => $credit,
		            'verify' => 0,
		            'user_id'			=> $v,
		            'notes'			=> $type,
		            'add_time'	=>	time(),
		        	'operator'  => $this->visitor->get('user_name'),
		        );
		        addCreditVerify($param);
	    	}
        } else {
        	$this->show_warning("�������!");
	    	return;
        }
    	$this->show_message('���ֱ䶯�ɹ�,��ȴ����!','����','index.php?app=sendcredit&act=index');
    }

}