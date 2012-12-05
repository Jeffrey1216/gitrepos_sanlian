<?php

/**
 *    �ҵ��ջ���ַ������
 *
 *    @author    Garbin
 *    @usage    none
 */
class My_partnerApp extends StoreadminbaseApp
{
    var $_store_id;

    function __construct()
    {
        $this->My_partnerApp();
    }

    function My_partnerApp()
    {
        parent::__construct();

        $this->_store_id  = intval($this->visitor->get('manage_store'));
    }

    function index()
    {
        $conditions = $this->_get_query_conditions(array(array(
                'field' => 'title',         //�������ֶ�title
                'equal' => 'LIKE',          //�ȼ۹�ϵ,������LIKE, =, <, >, <>
            ),
        ));
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        // ��ʶ��û�й�������
        if ($conditions)
        {
            $this->assign('filtered', 1);
        }

        /* ȡ���б����� */
        $model_partner =& m('partner');
        $page   =   $this->_get_page(10);    //��ȡ��ҳ��Ϣ
        $partners     = $model_partner->find(array(
            'conditions'    => 'store_id = ' . $this->visitor->get('manage_store') . $conditions,
            'order'         => 'sort_order, partner_id ASC',
            'limit'         => $page['limit'],  //��ȡ��ǰҳ������
            'count'         => true
        ));
        $page['item_count'] = $model_partner->getCount();   //��ȡͳ�Ƶ�����
        $this->assign('partners', $partners);

        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('my_partner'), 'index.php?app=my_partner',
                         LANG::get('partner_list'));

        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('my_partner');

        /* ��ǰ�����Ӳ˵� */
        $this->_curmenu('partner_list');

        $this->_format_page($page);
        $this->assign('page_info', $page);          //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_partner'));
        header("Content-Type:text/html;charset=" . CHARSET);
        $this->display('storeadmin.mypartner.index.html');
    }

    /**
     *    ��ӵ�ַ
     *
     *    @author    Garbin
     *    @return    void
     */
    function add()
    {
        if (!IS_POST)
        {
            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                             LANG::get('my_partner'), 'index.php?app=my_partner',
                             LANG::get('add_partner'));

            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('my_partner');

            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('add_partner');
            $this->_assign_form();
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->import_resource('jquery.plugins/jquery.validate.js');
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->display('my_partner.form.html');
        }
        else
        {
            $data = array(
                'store_id'       => $this->visitor->get('manage_store'),
                'title'     => $_POST['title'],
                'link'       => $_POST['link'],
                'sort_order'       => $_POST['sort_order'],
            );
            $model_partner =& m('partner');
            if (!($partner_id = $model_partner->add($data)))
            {
                $this->pop_warning($model_partner->get_error());

                return;
            }
            $logo       =   $this->_upload_logo($partner_id);
            $logo && $model_partner->edit($partner_id, array('logo' => $logo)); //��logo��ַ����

            $this->pop_warning('ok');
        }
    }
    function edit()
    {
        $partner_id = empty($_GET['partner_id']) ? 0 : intval($_GET['partner_id']);
        if (!$partner_id)
        {
            echo Lang::get('no_such_partner');

            return;
        }
        if (!IS_POST)
        {
            $model_partner =& m('partner');
            $find_data     = $model_partner->find("partner_id = {$partner_id} AND store_id=" . $this->visitor->get('manage_store'));
            if (empty($find_data))
            {
                echo Lang::get('no_such_partner');

                return;
            }
            $partner = current($find_data);

            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                             LANG::get('my_partner'), 'index.php?app=my_partner',
                             LANG::get('edit_partner'));

            /* ��ǰ�û����Ĳ˵� */
            $this->_curitem('my_partner');

            header('Content-Type:text/html;charset=' . CHARSET);
            /* ��ǰ�����Ӳ˵� */
            $this->_curmenu('edit_partner');
            $this->import_resource('jquery.plugins/jquery.validate.js');
            $this->assign('partner', $partner);
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->display('my_partner.form.html');
        }
        else
        {
            $data = array(
                'title'         => $_POST['title'],
                'link'       => $_POST['link'],
                'sort_order'       => $_POST['sort_order'],
            );

            $logo               =   $this->_upload_logo($partner_id);
            $logo && $data['logo'] = $logo;


            $model_partner =& m('partner');
            $model_partner->edit("partner_id = {$partner_id} AND store_id=" . $this->visitor->get('manage_store'), $data);
            if ($model_partner->has_error())
            {
                $this->pop_warning($model_partner->get_error());

                return;
            }

            $this->pop_warning('ok');
        }
    }
    function drop()
    {
        $partner_id = isset($_GET['id']) ? trim($_GET['id']) : 0;
        if (!$partner_id)
        {
            $this->show_storeadmin_warning('no_such_partner');

            return;
        }
        $ids = explode(',', $partner_id);//��ȡһ������array(1, 2, 3)������
        $model_partner  =& m('partner');
        $drop_count = $model_partner->drop("store_id = " . $this->visitor->get('manage_store') . " AND partner_id " . db_create_in($ids));
        if (!$drop_count)
        {
            /* û�п�ɾ������ */
            $this->show_storeadmin_warning('no_such_partner');

            return;
        }

        if ($model_partner->has_error())    //������
        {
            $this->show_storeadmin_warning($model_partner->get_error());

            return;
        }

        $this->show_storeadmin_message('drop_partner_successed');
    }

    /**
     *    �����˵�
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name'  => 'partner_list',
                'url'   => 'index.php?app=my_partner',
            ),
        );
        if (ACT == 'edit')
        {
            $menus[] = array(
                'name'  => 'edit_partner',
            );
        }
        return $menus;
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
        import('uploader.lib');             //�����ϴ���
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE); //�����ļ�����
        $uploader->allowed_size(SIZE_STORE_PARTNER); // 100KB
        $uploader->addFile($_FILES['logo']);//�ϴ�logo

        /* ָ������λ�õĸ�Ŀ¼ */
        $uploader->root_dir(ROOT_PATH);

        /* �ϴ� */
        if ($file_path = $uploader->save('data/files/store_' . $this->_store_id . '/partner', $partner_id))   //���浽ָ��Ŀ¼������ָ���ļ���$partner_id�洢
        {
            return $file_path;
        }
        else
        {
            return false;
        }
    }
    function _assign_form()
    {
        /*����ʼֵ*/
        $partner = array(
            'link'       => 'http://',
            'sort_order'    => '255',
        );
        $this->assign('partner' , $partner);
    }
}

?>