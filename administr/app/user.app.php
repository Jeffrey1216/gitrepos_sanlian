<?php

/* ��Ա������ */
class UserApp extends BackendApp
{
    var $_user_mod;

    function __construct()
    {
        $this->UserApp();
    }

    function UserApp()
    {
        parent::__construct();
        $this->_user_mod =& m('member');
    }

    function index()
    {
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => $_GET['field_name'],
                'name'  => 'field_value',
                'equal' => 'like',
            ),
        ));    
	     $credit = empty($_GET['credit']) ? 0 : $_GET['credit'];
	   		if('' !=$credit)
		    {
		    	$pri_arr = explode('-',$credit);
		    	if(!$pri_arr[1])
		    	{
		    		$res['credit'] = array(
		    		'min' => $pri_arr[0],
		    		'max' => $pri_arr[1],
		    		);
		    		}else{
		    			if($pri_arr[0] < $pri_arr[1])
		    			{
			    			$res['credit'] = array(
			    			'min' => $pri_arr[0],
			    			'max' => $pri_arr[1],
		    			);
		    		}
		    	}
		    	$pmin = $pri_arr[0];
		    	$pmax = $pri_arr[1];
		    	$this->assign('pmin',$pmin);
		    	$this->assign('pmax',$pmax);
		    	if(!$pmax)
		    	{
		    		$conditions .= ' AND credit > ' .$pmin;
		    		}else{
		    		if($pmin < $pmax)
		    		{
		    			$conditions .= ' AND credit >= ' . $pmin . ' AND credit <= ' . $pmax;
		    		}
		    	}
		    }   
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
            'mobile' => LANG::get('mobile'),
        ));
        $this->assign('sort_options', array(
            'reg_time DESC'   => LANG::get('reg_time'),
            'last_login DESC' => LANG::get('last_login'),
            'logins DESC'     => LANG::get('logins'),
        )); 
        $this->assign('user_credit',array('0-100'=>'0-100','101-200'=>'101-200','201-300'=>'201-300','301-400'=>'301-400','401-500'=>'401-500','500'=>'500����'));
        $this->display('user.index.html');
    }

    function add()
    {
        if (!IS_POST)
        {
            $this->assign('user', array(
                'gender' => 0,
            ));
            /* ����jQuery�ı���֤��� */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $ms =& ms();
            $this->assign('set_avatar', $ms->user->set_avatar());
            $this->display('user.form.html');
        }
        else
        {
            $user_name = trim($_POST['user_name']);
            $password  = trim($_POST['password']);
            $email     = trim($_POST['email']);
            $real_name = trim($_POST['real_name']);
            $gender    = trim($_POST['gender']);
            $im_qq     = trim($_POST['im_qq']);
            $im_msn    = trim($_POST['im_msn']);

            if (strlen($user_name) < 3 || strlen($user_name) > 25)
            {
                $this->show_warning('user_name_length_error');

                return;
            }

            if (strlen($password) < 6 || strlen($password) > 20)
            {
                $this->show_warning('password_length_error');

                return;
            }

            if (!is_email($email))
            {
                $this->show_warning('email_error');

                return;
            }

            /* �����û�ϵͳ */
            $ms =& ms();

            /* ��������Ƿ��Ѵ��� */
            if (!$ms->user->check_username($user_name))
            {
                $this->show_warning($ms->user->get_error());

                return;
            }

            /* ���汾������ */
            $data = array(
                'real_name' => $_POST['real_name'],
                'gender'    => $_POST['gender'],
//                'phone_tel' => join('-', $_POST['phone_tel']),
//                'phone_mob' => $_POST['phone_mob'],
                'im_qq'     => $_POST['im_qq'],
                'im_msn'    => $_POST['im_msn'],
//                'im_skype'  => $_POST['im_skype'],
//                'im_yahoo'  => $_POST['im_yahoo'],
//                'im_aliww'  => $_POST['im_aliww'],
                'reg_time'  => gmtime(),
            );

            /* ���û�ϵͳ��ע�� */
            $user_id = $ms->user->register($user_name, $password, $email, $data);
            if (!$user_id)
            {
                $this->show_warning($ms->user->get_error());

                return;
            }

            if (!empty($_FILES['portrait']))
            {
                $portrait = $this->_upload_portrait($user_id);
                if ($portrait === false)
                {
                    return;
                }

                $portrait && $this->_user_mod->edit($user_id, array('portrait' => $portrait));
            }


            $this->show_message('add_ok',
                'back_list',    'index.php?app=user',
                'continue_add', 'index.php?app=user&amp;act=add'
            );
        }
    }

    /*����Ա���Ƶ�Ψһ��*/
    function  check_user()
    {
          $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
          if (!$user_name)
          {
              echo ecm_json_encode(false);
              return ;
          }

          /* ���ӵ��û�ϵͳ */
          $ms =& ms();
          echo ecm_json_encode($ms->user->check_username($user_name));
    }

    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* �Ƿ���� */
            $user = $this->_user_mod->get_info($id);
            if (!$user)
            {
                $this->show_warning('user_empty');
                return;
            }

            $ms =& ms();
            $this->assign('set_avatar', $ms->user->set_avatar($id));
            $this->assign('user', $user);
            $this->assign('phone_tel', explode('-', $user['phone_tel']));
            /* ����jQuery�ı���֤��� */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->display('user.form.html');
        }
        else
        {
            $data = array(
                'real_name' => $_POST['real_name'],
                'gender'    => $_POST['gender'],
//                'phone_tel' => join('-', $_POST['phone_tel']),
//                'phone_mob' => $_POST['phone_mob'],
                'im_qq'     => $_POST['im_qq'],
                'im_msn'    => $_POST['im_msn'],
//                'im_skype'  => $_POST['im_skype'],
//                'im_yahoo'  => $_POST['im_yahoo'],
//                'im_aliww'  => $_POST['im_aliww'],
            );
            if (!empty($_POST['password']))
            {
                $password = trim($_POST['password']);
                if (strlen($password) < 6 || strlen($password) > 20)
                {
                    $this->show_warning('password_length_error');

                    return;
                }
            }
            if (!is_email(trim($_POST['email'])))
            {
                $this->show_warning('email_error');

                return;
            }

            if (!empty($_FILES['portrait']))
            {
                $portrait = $this->_upload_portrait($id);
                if ($portrait === false)
                {
                    return;
                }
                $data['portrait'] = $portrait;
            }

            /* �޸ı������� */
            $this->_user_mod->edit($id, $data);

            /* �޸��û�ϵͳ���� */
            $user_data = array();
            !empty($_POST['password']) && $user_data['password'] = trim($_POST['password']);
            !empty($_POST['email'])    && $user_data['email']    = trim($_POST['email']);
            if (!empty($user_data))
            {
                $ms =& ms();
                $ms->user->edit($id, '', $user_data, true);
            }

            $this->show_message('edit_ok',
                'back_list',    'index.php?app=user',
                'edit_again',   'index.php?app=user&amp;act=edit&amp;id=' . $id
            );
        }
    }

    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_user_to_drop');
            return;
        }
        $admin_mod =& m('userpriv');
        if(!$admin_mod->check_admin($id))
        {
            $this->show_message('cannot_drop_admin',
                'drop_admin', 'index.php?app=admin');
            return;
        }

        $ids = explode(',', $id);

        /* �����û�ϵͳ�����û�ϵͳ��ɾ����Ա */
        $ms =& ms();
        if (!$ms->user->drop($ids))
        {
            $this->show_warning($ms->user->get_error());

            return;
        }

        $this->show_message('drop_ok');
    }

    /**
     * �ϴ�ͷ��
     *
     * @param int $user_id
     * @return mix false��ʾ�ϴ�ʧ��,�մ���ʾû���ϴ�,string��ʾ�ϴ��ļ���ַ
     */
    function _upload_portrait($user_id)
    {
        $file = $_FILES['portrait'];
        if ($file['error'] != UPLOAD_ERR_OK)
        {
            return '';
        }

        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->addFile($file);
        if ($uploader->file_info() === false)
        {
            $this->show_warning($uploader->get_error(), 'go_back', 'index.php?app=user&amp;act=edit&amp;id=' . $user_id);
            return false;
        }

        $uploader->root_dir(ROOT_PATH);
        return $uploader->save('data/files/mall/portrait/' . ceil($user_id / 500), $user_id);
    }
    function details()
    {
    	$user_id = empty($_GET['user_id']) ? 0 : intval($_GET['user_id']);
    	if(0 == $user_id)
    	{
    		$this->show_message("�û�ID����Ϊ��");
    	}
    	$user_mod = & m('member');
    	$member_info = $user_mod->get($user_id); 
    	$page = $this->_get_page(20);
    	$user_info = $user_mod->getAll('SELECT al.*,o.order_sn,o.order_id from pa_member m left join pa_account_log al on al.user_id=m.user_id left join pa_order o on o.order_id=al.order_id where m.user_id='.$user_id.' order by al.change_time desc limit '.$page['limit']);
		$change_type = get_change_type();
		foreach ($user_info as $k=>$v)
		{
			$user_info[$k]['change_type'] = $change_type[$v['change_type']];
			$user_info[$k]['change_time'] = $v['change_time'];
		}
    	$user_count = $user_mod->getRow('SELECT count(*) as count from pa_member m left join pa_account_log al on al.user_id=m.user_id left join pa_order o on o.order_id=al.order_id where m.user_id='.$user_id);
        $page['item_count'] = $user_count['count'];        
        $this->_format_page($page);
        $this->assign('page_info', $page);
    	$this->assign('member',$member_info);
    	$this->assign('users',$user_info);
    	$this->display('user_log.html');
    }
    function edit_staff()
    {
		if($_GET['type'] == 'yes')
		{
			$result = $this->_user_mod->edit($_GET['id'],array('is_staff' => 1));
		}else {
			$result = $this->_user_mod->edit($_GET['id'],array('is_staff' => 0));
		}
		if($result)
		{
			$this->show_message("���óɹ�");
			return ;
		}else {
			$this->show_message("����ʧ��");
			return ;
		}	
    }
}

?>
