<?php

/**
 *    ��ƷƷ�ƹ��������
 *
 *    @author    Hyber
 *    @usage    none
 */
class BrandApp extends BackendApp
{
    var $_brand_mod;

    function __construct()
    {
        $this->BrandApp();
    }

    function BrandApp()
    {
        parent::BackendApp();

        $this->_brand_mod =& m('brand');
    }

    /**
     *    ��ƷƷ������
     *
     *    @author    Hyber
     *    @return    void
     */
    function index()
    {
        $conditions = $this->_get_query_conditions(array(array(
                'field' => 'brand_name',
                'equal' => 'LIKE',
                'assoc' => 'AND',
                'name'  => 'brand_name',
                'type'  => 'string',
            ),
            array(
                'field' => 'tag',
                'equal' => 'LIKE',
                'assoc' => 'AND',
                'name' => 'tag',
                'type' => 'string',
            ),
        ));
        $page   =   $this->_get_page(10);   //��ȡ��ҳ��Ϣ
        //��������
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'brand_id';
             $order = 'desc';
            }
        }
        else
        {
            $sort  = 'brand_id';
            $order = 'desc';
        }
        $verify =  empty($_GET['wait_verify']) ? ' AND if_show = 1' : ' AND if_show = 0';
        $brands=$this->_brand_mod->find(array(
        'conditions'    => '1=1' . $conditions . $verify,
        'limit'         => $page['limit'],
        'order'         => "$sort $order",
        'count'         => true
        ));
        foreach ($brands as $key => $brand)
        {
            $brand['brand_logo']&&$brands[$key]['brand_logo'] = SITE_URL . '/' . $brand['brand_logo'];
        }
        $page['item_count']=$this->_brand_mod->getCount();   //��ȡͳ������
        /* ����jQuery�ı���֤��� */
        $this->import_resource(array(
            'script' => 'jqtreetable.js,inline_edit.js',
            'style'  => 'res:style/jqtreetable.css'
        ));
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //�Ƿ��в�ѯ����
        $this->assign('wait_verify', $_GET['wait_verify']);
        $this->assign('page_info', $page);   //����ҳ��Ϣ���ݸ���ͼ�������γɷ�ҳ��
        $this->assign('brands', $brands);
        $this->display('brand.index.html');
    }
     /**
     *    ������ƷƷ��
     *
     *    @author    Hyber
     *    @return    void
     */
    function add()
    {
        if (!IS_POST)
        {
            /* ��ʾ������ */
            $brand = array(
                'sort_order' => 255,
                'recommended' => 0,
            );
            $yes_or_no = array(
                1 => Lang::get('yes'),
                0 => Lang::get('no'),
            );
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            //������Ҫ�ķ���
            $gcategory_mod = & m('gcategory');
            $gcategory_info = $gcategory_mod->find(array('conditions' => 'parent_id=0 and if_show=1'));
            $this->assign('gcategory_info',$gcategory_info);
            $this->assign('yes_or_no', $yes_or_no);
            $this->assign('brand', $brand);
            $this->display('brand.form.html');
        }
        else
        {
            $data = array();
            $data['brand_name']     = $_POST['brand_name'];
            $data['sort_order']     = $_POST['sort_order'];
            $data['recommended']    = $_POST['recommended'];
            $data['cate_id']		= $_POST['cate_id'];
            $data['tag'] = $_POST['tag'];
            $data['if_show'] = 1;

            /* ��������Ƿ��Ѵ��� */
            if (!$this->_brand_mod->unique(trim($data['brand_name'])))
            {
                $this->show_warning('name_exist');
                return;
            }
            if (!$brand_id = $this->_brand_mod->add($data))  //��ȡbrand_id
            {
                $this->show_warning($this->_brand_mod->get_error());

                return;
            }

            /* �����ϴ���ͼƬ */
            $logo       =   $this->_upload_logo($brand_id);
            if ($logo === false)
            {
                return;
            }
            $logo && $this->_brand_mod->edit($brand_id, array('brand_logo' => $logo)); //��logo��ַ����

            $this->show_message('add_brand_successed',
                'back_list',    'index.php?app=brand',
                'continue_add', 'index.php?app=brand&amp;act=add'
            );
        }
    }

    /* ���Ʒ��Ψһ */
    function check_brand ()
    {
        $brand_name = empty($_GET['brand_name']) ? '' : trim($_GET['brand_name']);
        $brand_id   = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$brand_name) {
            echo ecm_json_encode(false);
        }
        if ($this->_brand_mod->unique($brand_name, $brand_id)) {
            echo ecm_json_encode(true);
        }
        else
        {
            echo ecm_json_encode(false);
        }
        return ;
    }

     /**
     *    �༭��ƷƷ��
     *
     *    @author    Hyber
     *    @return    void
     */
    function edit()
    {
        $brand_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$brand_id)
        {
            $this->show_warning('no_such_brand');
            return;
        }
         if (!IS_POST)
        {
            $find_data     = $this->_brand_mod->find($brand_id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_brand');

                return;
            }
            $brand    =   current($find_data);
            if ($brand['brand_logo'])
            {
                $brand['brand_logo']  =   SITE_URL . "/" . $brand['brand_logo'];
            }
            //��ʾ��Ʒ����,��ѡ��
            $gcategory_mod = & m('gcategory');
            $gcategory_info = $gcategory_mod->find(array('conditions' => 'parent_id=0 and if_show=1'));
            $this->assign('gcategory_info',$gcategory_info);
            /* ��ʾ������ */
            $yes_or_no = array(
                1 => Lang::get('yes'),
                0 => Lang::get('no'),
            );
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->assign('yes_or_no', $yes_or_no);
            $this->assign('brand', $brand);
            $this->display('brand.form.html');
        }
        else
        {
            $data = array();
            $data['brand_name']     =   $_POST['brand_name'];
            $data['sort_order']     =   $_POST['sort_order'];
            $data['recommended']    =   $_POST['recommended'];
            $data['cate_id']		= $_POST['cate_id'];
            $data['tag'] = $_POST['tag'];
            $logo               =   $this->_upload_logo($brand_id);
            $logo && $data['brand_logo'] = $logo;
            if ($logo === false)
            {
                return;
            }
             /* ��������Ƿ��Ѵ��� */
            if (!$this->_brand_mod->unique(trim($data['brand_name']), $brand_id))
            {
                $this->show_warning('name_exist');
                return;
            }
            $rows=$this->_brand_mod->edit($brand_id, $data);
            if ($this->_brand_mod->has_error())
            {
                $this->show_warning($this->_brand_mod->get_error());

                return;
            }

            $this->show_message('edit_brand_successed',
                'back_list',        'index.php?app=brand',
                'edit_again',    'index.php?app=brand&amp;act=edit&amp;id=' . $brand_id);
        }
    }

         //�첽�޸�����
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('brand_name', 'recommended', 'sort_order', 'tag')))
       {
           $data[$column] = $value;
           if($column == 'brand_name')
           {
               $brand = $this->_brand_mod->get_info($id);

               if(!$this->_brand_mod->unique($value, $id))
               {
                   echo ecm_json_encode(false);
                   return ;
               }
           }
           $this->_brand_mod->edit($id, $data);
           if(!$this->_brand_mod->has_error())
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
        $brand_ids = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$brand_ids)
        {
            $this->show_warning('no_such_brand');

            return;
        }
        $brand_ids=explode(',',$brand_ids);
        $this->_brand_mod->drop($brand_ids);
        if ($this->_brand_mod->has_error())    //ɾ��
        {
            $this->show_warning($this->_brand_mod->get_error());

            return;
        }

        $this->show_message('drop_brand_successed');
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
            $this->_brand_mod->edit($id, array('sort_order' => $sort_orders[$key]));
        }

        $this->show_message('update_order_ok');
    }

        /**
     *    �����ϴ���־
     *
     *    @author    Hyber
     *    @param     int $brand_id
     *    @return    string
     */
    function _upload_logo($brand_id)
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
            $this->show_warning($uploader->get_error() , 'go_back', 'index.php?app=brand&amp;act=edit&amp;id=' . $brand_id);
            return false;
        }
        /* ָ������λ�õĸ�Ŀ¼ */
        $uploader->root_dir(ROOT_PATH);

        /* �ϴ� */
        if ($file_path = $uploader->save('data/files/mall/brand', $brand_id))   //���浽ָ��Ŀ¼������ָ���ļ���$brand_id�洢
        {
            return $file_path;
        }
        else
        {
            return false;
        }
    }

    /**
     * �����ֶ�
     *
     */
    function update()
    {
        $allow_cols=array(
        'recommended',  //������µ��ֶ�
        );
        $col    =   trim($_GET['col']);
        $value  =   trim($_GET['value']);
        if (!in_array($col, $allow_cols))
        {
            $this->show_warning('Hacking attempt');
            return;
        }
        $brand_ids = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$brand_ids)
        {
            $this->show_warning('no_such_brand');

            return;
        }
        $brand_ids=explode(',',$brand_ids);
        $data = array();
        $data[$col] = $value;

        $rows=$this->_brand_mod->edit($brand_ids, $data);
        if ($this->_brand_mod->has_error())
        {
            $this->show_warning($this->_brand_mod->get_error());

            return;
        }
        $this->show_message('update_' . $col . '_successed');
    }

    function pass()
    {
        $id = $_GET['id'];
        if (empty($id))
        {
            $this->show_warning('request_error');
            exit;
        }
        $ids = explode(',', $id);
        $brands = $this->_brand_mod->find(db_create_in($ids, 'brand_id') . " AND if_show = 0");
        $this->_brand_mod->edit(db_create_in(array_keys($brands), 'brand_id'), array('if_show' => 1));
        if ($this->_brand_mod->has_error())
        {
            $this->show_warning($this->_brand_mod->get_error());
            exit;
        }
        $ms =& ms();
        $content = '';
        foreach ($brands as $brand)
        {
            $content = get_msg('toseller_brand_passed_notify', array('brand_name' => $brand['brand_name']));
            $ms->pm->send(MSG_SYSTEM, $brand['store_id'], '', $content);
        }
        $this->show_message('brand_passed',
            'back_list', 'index.php?app=brand&wait_verify=1');
    }

    function refuse()
    {
        $id = $_GET['id'];
        if (empty($id))
        {
            $this->show_warning('request_error');
            exit;
        }
        if (!IS_POST)
        {
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->display('brand_refuse.html');
        }
        else
        {
            if (empty($_POST['content']))
            {
                $this->show_warning('content_required');
                exit;
            }
            $ids = explode(',', trim($_GET['id']));
            $brands = $this->_brand_mod->find(db_create_in($ids, 'brand_id') . ' AND if_show = 0');
            $ms =& ms();
            $content = '';
            foreach ($brands as $brand)
            {
                $content = get_msg('toseller_brand_refused_notify', array('brand_name' => $brand['brand_name'], 'reason' => trim($_POST['content'])));
                $ms->pm->send(MSG_SYSTEM, $brand['store_id'], '', $content);
                if (is_file(ROOT_PATH . '/' . $brand['brand_logo']) && file_exists(ROOT_PATH . '/' . $brand['brand_logo']))
                {
                    unlink(ROOT_PATH . '/' . $brand['brand_logo']);
                }
                $this->_brand_mod->drop($brand['brand_id']);
            }
            $this->show_message('brand_refused',
                    'back_list', 'index.php?app=brand&wait_verify=1');
        }
    }


}

?>