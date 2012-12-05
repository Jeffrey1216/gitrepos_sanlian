<?php

/* ����Ա������ */
class AdminApp extends BackendApp
{
    var $_admin_mod;
    var $_user_mod;

    function __construct()
    {
        $this->AdminApp();
    }

    function AdminApp()
    {
        parent::__construct();
        $this->_admin_mod = & m('userpriv');
        $this->_user_mod = & m('member');
    }
    function index()
    {
        $this->assign('query_fields', array(
            'user_name' => LANG::get('user_name'),
            'email'     => LANG::get('email'),
        ));
        $tmp = $_GET['field_name'];
        $tem = $_GET['field_value'];
        if($tmp)
        {		
        $conditions = " AND store_id = 0 AND $tmp like '%".$tem."%'";
        //��������
        $sort  = 'userpriv.user_id';
        $order = 'asc';
        $page = $this->_get_page();
        $admin_info = $this->_admin_mod->find(array(
            'conditions' => '1=1' . $conditions,
            'join' => 'mall_be_manage',
            'limit' => $page['limit'],
            'order' => "$sort $order",
            'count' => true,
        ));
        $page['item_count'] = $this->_admin_mod->getCount();
        $this->_format_page($page);
        }else{
        	$conditions = ' AND store_id = 0';
        //��������
        $sort  = 'userpriv.user_id';
        $order = 'asc';
        $page = $this->_get_page();
        $admin_info = $this->_admin_mod->find(array(
            'conditions' => '1=1' . $conditions,
            'join' => 'mall_be_manage',
            'limit' => $page['limit'],
            'order' => "$sort $order",
            'count' => true,
        ));
        $page['item_count'] = $this->_admin_mod->getCount();
        $this->_format_page($page);
        }
        $this->assign('page_info',$page);
        $this->assign('admins',$admin_info);
        $this->display('admin.index.html');
    }
    function drop()
    {
        $id = (isset($_GET['id']) && $_GET['id'] !='') ? trim($_GET['id']) : '';
        //�ж��Ƿ�ѡ�����Ա
        $ids = explode(',',$id);
        if (!$id||$this->_admin_mod->check_admin($id))
        {
            $this->show_warning('choose_admin');
            return;
        }
        //�ж��Ƿ���ϵͳ��ʼ����Ա
        if ($this->_admin_mod->check_system_manager($id))
        {
            $this->show_warning('system_admin_drop');
            return;
         }
         //ɾ������Ա
        $conditions = "store_id = 0 AND user_id " . db_create_in($ids);
        if (!$res = $this->_admin_mod->drop($conditions))
        {
            $this->show_warning('drop_failed');
            return;
        }
        $this->show_message('drop_ok', 'admin_list', 'index.php?app=admin');
    }
    function edit()
    {
        $id = (isset($_GET['id']) && $_GET['id'] !='') ? intval($_GET['id']) : '';
        //�ж��Ƿ�ѡ���˹���Ա
        if (!$id || $this->_admin_mod->check_admin($id))
        {
            $this->show_warning('choose_admin');
            return;
        }
        //�ж��Ƿ���ϵͳ��ʼ����Ա
         if ($this->_admin_mod->check_system_manager($id))
        {
            $this->show_warning('system_admin_edit');
            return;
        }
        if (!IS_POST)
        {
            //��ȡ��ǰ����ԱȨ��
            $privs = $this->_admin_mod->get(array(
                'conditions' => '1=1 AND  store_id =0 AND user_id = '.$id,
                'fields' => 'privs',
            ));
           $admins = $this->_user_mod->get(array(
                    'conditions' => '1=1 AND user_id ='.$id,
                    'fields' => 'user_name,real_name',
                ));
            $priv=explode(',', $privs['privs']);
            include(ROOT_PATH.'/administr/includes/priv.inc.php');
            $act = 'edit';
            $this->assign('act',$act);
            $this->assign('admin',$admins);
            $this->assign('checked_priv',$priv);
            $this->assign('priv',$menu_data);
            $this->display('admin.form.html');
        }
        else
        {
            //���¹���ԱȨ��
            $privs = (isset($_POST['priv']) && $_POST['priv']!='priv') ? $_POST['priv']: '';
            $priv = '';
            if ($privs == '')
            {
                $this->show_warning('add_priv');
                return;
            }
            else
            {
                $priv = implode(',', $privs);
            }
            $data = array(
                    'user_id' => $id,
                    'store_id' => '0',
                    'privs' => $priv,
               );
            $this->_admin_mod->edit($id, $data);
            if($this->_admin_mod->has_error())
            {
                 $this->show_warning($this->_admin_mod->get_error());
                 return;
             }
             else
            {
                $this->show_message('edit_admin_ok');
                return true;
             }
        }
    }
    function add()
    {
        $id = (isset($_GET['id']) && $_GET['id'] != '') ? intval($_GET['id']) : '';
        if (empty($_POST['priv']))
        {
           if ($id != '')
           {
                $condition = ' AND  user_id = '.$id;
                $admin = $this->_user_mod->get(array(
                    'conditions' => '1=1' . $condition,
                    'fields' => 'user_name,real_name',
                ));
                //��ѯ�Ƿ��ǹ���Ա
                if (!$admin)
                {
                    $this->show_warning('choose_admin');
                    return;
                }
                //��ѯ�Ƿ����ǹ���Ա
                if (!$this->_admin_mod->check_admin($id))
                {
                    $this->show_warning('already_admin');
                    return;
                }
                $this->assign('admin',$admin);
                include(ROOT_PATH.'/administr/includes/priv.inc.php');
                $this->assign('priv', $menu_data);
                $this->display('admin.form.html');
            }
            else
            {
                if(!IS_POST)
                {
                    $this->display('admin.test.html');
                }
                else
                {
                    $user_name = (isset($_POST['user_name'])&&$_POST['user_name']!='') ? $_POST['user_name']:'';

                    /* �����û�ϵͳ */
                    $ms =& ms();
                    $info = $ms->user->get($user_name, true);
                    if (empty($info))
                    {
                        $this->show_message('add_member', 'go_back', 'index.php?app=admin&amp;act=add', 'to_add_member', 'index.php?app=user&amp;act=add');
                        return;
                    }
                    else
                    {
                        $id = $info['user_id'];
                        header("Location: index.php?app=admin&act=add&id=".$id." ");
                     }
                }
            }
        }
        else
        {
            //��ȡȨ�޲�����
            $privs = (isset($_POST['priv']) && $_POST['priv'] != 'priv') ? $_POST['priv'] : '';
            $priv = 'default|all,';
            if ($privs == '')
            {
                $this->show_warning('add_priv');
                return;
            }
            else
            {
                $priv .= implode(',', $privs);
            }
             //�ж��Ƿ����ǹ���Ա
             if (!$this->_admin_mod->check_admin($id))
                {
                    $this->show_warning('already_admin');
                    return;
                }
             $data = array(
                    'user_id' => $id,
                    'store_id' => '0',
                    'privs' => $priv,
                );
             if ($this->_admin_mod->add($data) === fasle)
             {
                 $this->show_warning($this->_admin_mod->get_error());
                 return;
             }
             else
            {
                $this->show_message('add_admin_ok', 'admin_list', 'index.php?app=admin', 'user_list', 'index.php?app=user');
             }
        }
    }
}

?>
