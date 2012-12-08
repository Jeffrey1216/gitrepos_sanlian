<?php

/**
 *    �������������
 *
 *    @author   zhufuqing 
 *    @usage    none
 */
class ExpressApp extends BackendApp
{
    var $_partner_mod;

    function __construct()
    {
        $this->ExpressApp();
    }

    function ExpressApp()
    {
        parent::BackendApp();

        $this->_partner_mod =& m('partner');
    }

    /**
     *    ����
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    {
        $conditions = $this->_get_query_conditions(array(array(
                'field' => 'title',         //�������ֶ�title
                'equal' => 'LIKE',          //�ȼ۹�ϵ,������LIKE, =, <, >, <>
            ),
        ));
        $page   =   $this->_get_page(10);    //��ȡ��ҳ��Ϣ
        $partners = $this->_partner_mod->find(array(
            'conditions'    => 'store_id=0' . $conditions,
            'limit'         => $page['limit'],  //��ȡ��ǰҳ������
            'order'         => 'sort_order,partner_id ASC',
            'count'         => true             //����ͳ��
        )); //�ҳ������̳ǵĺ������
        foreach ($partners as $key => $partner)
        {
            $partner['logo']&&$partners[$key]['logo'] = SITE_URL . '/' . $partner['logo'];
        }
        $page['item_count'] = $this->_partner_mod->getCount();   //��ȡͳ�Ƶ�����
        $this->_format_page($page);
        $this->import_resource(array('script' => 'inline_edit.js'));
        $this->assign('filtered', $conditions? 1 : 0); //�Ƿ��в�ѯ����
        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->assign('partners', $partners);
        $this->display('express.index.html');
    }
    /**
     *    ����
     *
     *    @author    Garbin
     *    @return    void
     */
    function add()
    {
        if (!IS_POST)
        {
            /* ��ʾ������ */
            $partner = array(
            'sort_order'    => '255',
            'link'          => 'http://',
            );
            $this->assign('partner' , $partner);
            $this->import_resource('jquery.plugins/jquery.validate.js');
            $this->display('partner.form.html');
        }
        else
        {
            $data = array();
            $data['store_id']   =   0;
            $data['title']      =   $_POST['title'];
            $data['link']       =   $_POST['link'];
            $data['sort_order'] =   $_POST['sort_order'];

            if (!$partner_id = $this->_partner_mod->add($data))  //��ȡpartner_id
            {
                $this->show_warning($this->_partner_mod->get_error());

                return;
            }

            /* �����ϴ���ͼƬ */
            $logo       =   $this->_upload_logo($partner_id);
            if ($logo === false)
            {
                return;
            }
            $logo && $this->_partner_mod->edit($partner_id, array('logo' => $logo)); //��logo��ַ����

            $this->show_message('add_partner_successed',
                'back_list',    'index.php?app=partner',
                'continue_add', 'index.php?app=partner&amp;act=add'
            );
        }
    }

    /**
     *    �༭
     *
     *    @author    Garbin
     *    @return    void
     */
    function edit()
    {
        $partner_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$partner_id)
        {
            $this->show_warning('no_such_partner');

            return;
        }
        if (!IS_POST)
        {
            $find_data     = $this->_partner_mod->find($partner_id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_partner');

                return;
            }
            $partner    =   current($find_data);
            if ($partner['logo'])
            {
                $partner['logo']  =   SITE_URL . "/" . $partner['logo'];
            }
            $this->assign('partner', $partner);
            $this->import_resource('jquery.plugins/jquery.validate.js');
            $this->display('partner.form.html');
        }
        else
        {
            $data = array();
            $data['title']      =   $_POST['title'];
            $data['link']       =   $_POST['link'];
            $data['sort_order'] =   $_POST['sort_order'];
            $logo               =   $this->_upload_logo($partner_id);
            $logo && $data['logo'] = $logo;
            if ($logo === false)
            {
                return;
            }
            $rows = $this->_partner_mod->edit($partner_id, $data);
            if ($this->_partner_mod->has_error())    //�д���
            {
                $this->show_warning($this->_partner_mod->get_error());

                return;
            }

            $this->show_message('edit_partner_successed',
                'back_list',     'index.php?app=partner',
                'edit_again', 'index.php?app=partner&amp;act=edit&amp;id=' . $partner_id);
        }
    }

    //�첽�޸�����
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('title', 'sort_order')))
       {
           $data[$column] = $value;
           $this->_partner_mod->edit($id, $data);
           if(!$this->_partner_mod->has_error())
           {
               echo ecm_json_encode(true);
           }
       }
       else
       {
           return ;
       }
       return ;
   }

    function drop()
    {
        $partner_ids = isset($_GET['id']) ? trim($_GET['id']) : 0;
        if (!$partner_ids)
        {
            $this->show_warning('no_such_partner');

            return;
        }
        $partner_ids = explode(',', $partner_ids);//��ȡһ������array(1, 2, 3)������
        if (!$this->_partner_mod->drop($partner_ids))    //ɾ��
        {
            $this->show_warning($this->_partner_mod->get_error());

            return;
        }

        $this->show_message('drop_partner_successed');
    }

    /* �������� */
    function update_order()
    {
        if (empty($_GET['id']))
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        $ids = explode(',', $_GET['id']);
        $sort_orders = explode(',', $_GET['sort_order']);
        foreach ($ids as $key => $id)
        {
            $this->_partner_mod->edit($id, array('sort_order' => $sort_orders[$key]));
        }

        $this->show_message('update_order_ok');
    }

    /**
     *    �����ϴ���־
     *
     *    @author    Garbin
     *    @param     int $partner_id
     *    @return    string
     */
    function _upload_logo($partner_id)
    {
        $file = $_FILES['logo'];
        if ($file['error'] == UPLOAD_ERR_NO_FILE) // û���ļ����ϴ�
        {
            return '';
        }
        import('uploader.lib');             //�����ϴ���
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE); //�����ļ�����
        $uploader->addFile($_FILES['logo']);//�ϴ�logo
        if (!$uploader->file_info())
        {
            $this->show_warning($uploader->get_error() , 'go_back', 'index.php?app=partner&amp;act=edit&amp;id=' . $partner_id);
            return false;
        }
        /* ָ������λ�õĸ�Ŀ¼ */
        $uploader->root_dir(ROOT_PATH);

        /* �ϴ� */
        if ($file_path = $uploader->save('data/files/mall/partner', $partner_id))   //���浽ָ��Ŀ¼������ָ���ļ���$partner_id�洢
        {
            return $file_path;
        }
        else
        {
            return false;
        }
    }
}

?>