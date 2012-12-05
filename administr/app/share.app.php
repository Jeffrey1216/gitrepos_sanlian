<?php

/**
 *    ��Ʒ������������
 *
 *    @author    Hyber
 *    @usage    none
 */
class ShareApp extends BackendApp
{
    var $_m_share;

    function __construct()
    {
        $this->ShareApp();
    }

    function ShareApp()
    {
        parent::BackendApp();

        $this->_m_share =& af('share');
    }

    /**
     *    ��Ʒ��������
     *
     *    @author    Hyber
     *    @return    void
     */
    function index()
    {
        $shares = $this->_m_share->getAll();
        $shares = array_msort($shares, array('sort_order' => SORT_ASC));
        $this->import_resource(array('script' => 'inline_edit.js'));
        $this->assign('shares', $shares);
        $this->assign('type', $this->_get_share_type());
        $this->display('share.index.html');
    }

    function add()
    {
        if (!IS_POST)
        {
            $this->import_resource('jquery.plugins/jquery.validate.js');
            $this->assign('share', array('sort_order' => 255, 'type' => 'share'));
            $this->assign('type', $this->_get_share_type());
            $this->display('share.form.html');
        }
        else
        {
            $data = array(
                'title' => trim($_POST['title']),
                'link'  => trim($_POST['link']),
                'type'  => $_POST['type'],
                'sort_order' => intval($_POST['sort_order']),
            );

            if (!$share_id = $this->_m_share->add($data))
            {
                $this->show_warning($this->_m_share->get_error());
                return;
            }

            /* �����ϴ���ͼƬ */
            $logo       =   $this->_upload_logo($share_id);
            if ($logo === false)
            {
                return;
            }
            $data['logo'] = $logo;
            $logo && $this->_m_share->setOne($share_id, $data); //��logo��ַ����

            $this->_clear_cache();
            $this->show_message('add_share_successed',
                'back_list',    'index.php?app=share',
                'continue_add', 'index.php?app=share&amp;act=add'
            );
        }
    }

    function edit()
    {
        $share_id = empty($_GET['id']) ? 0 : $_GET['id'];
        if (!$share_id)
        {
            $this->show_warning('no_such_share');
            return;
        }
        $share = $this->_m_share->getOne($share_id);
        if (!$share)
        {
            $this->show_warning('no_such_share');
            return;
        }
        if (!IS_POST)
        {
            $this->import_resource('jquery.plugins/jquery.validate.js');
            if ($share['logo'])
            {
                $share['logo']  =   SITE_URL . "/" . $share['logo'];
            }
            $this->assign('share', $share);
            $this->assign('type', $this->_get_share_type());
            $this->display('share.form.html');
        }
        else
        {
            $data = $this->_m_share->getAll($share_id);
            $data[$share_id] = array(
                'title' => trim($_POST['title']),
                'link'  => trim($_POST['link']),
                'type'  => $_POST['type'],
                'sort_order' => intval($_POST['sort_order']),
            );
            if (empty($_FILES['logo']['tmp_name'])) // ���û��ͼƬ�ϴ���ʹ���޸�ǰ��ͼƬ
            {
                $data[$share_id]['logo'] = empty($share['logo']) ? '' : $share['logo'];
            }
            if (!$this->_m_share->setAll($data))
            {
                $this->show_warning($this->_m_share->get_error());
                return;
            }

            /* �����ϴ���ͼƬ */
            $logo       =   $this->_upload_logo($share_id);
            if ($logo === false)
            {
                return;
            }
            $data[$share_id]['logo'] = $logo;
            $logo && $this->_m_share->setAll($data); //��logo��ַ����

            $this->_clear_cache();
            $this->show_message('edit_share_successed',
                'back_list',     'index.php?app=share',
                'edit_again', 'index.php?app=share&amp;act=edit&amp;id=' . $share_id);
            }
    }

    function drop()
    {
        $share_ids = isset($_GET['id']) ? trim($_GET['id']) : 0;
        if (!$share_ids)
        {
            $this->show_warning('no_such_share');

            return;
        }
        $share_ids = explode(',', $share_ids);//��ȡһ������array(1, 2, 3)������
        foreach ($share_ids as $share_id)
        {
            $this->_m_share->drop($share_id);
        }
        $this->_clear_cache();
        $this->show_message('drop_share_successed');
    }

    //�첽�޸�����
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = $this->_m_share->getAll();

       if (in_array($column ,array('title', 'sort_order')))
       {
           $data[$id][$column] = $value;
           if($this->_m_share->setAll($data))
           {
               $this->_clear_cache();
               echo ecm_json_encode(true);
           }
       }
       else
       {
           return ;
       }
       return ;
   }

    function _get_share_type()
    {
        return array(
            'share'   => Lang::get('share'),
            'collect' => Lang::get('collect'),
        );
    }

       /**
     *    �����ϴ���־
     *
     *    @author    Hyber
     *    @param     int $brand_id
     *    @return    string
     */
    function _upload_logo($share_id)
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
            $this->show_warning($uploader->get_error() , 'go_back', 'index.php?app=$share&amp;act=edit&amp;id=' . $share_id);
            return false;
        }
        /* ָ������λ�õĸ�Ŀ¼ */
        $uploader->root_dir(ROOT_PATH);

        /* �ϴ� */
        if ($file_path = $uploader->save('data/files/mall/share', $share_id))   //���浽ָ��Ŀ¼������ָ���ļ���$brand_id�洢
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
