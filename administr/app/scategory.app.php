<?php

define('MAX_LAYER', 2);

/* ���̷�������� */
class ScategoryApp extends BackendApp
{
    var $_scategory_mod;

    function __construct()
    {
        $this->ScategoryApp();
    }

    function ScategoryApp()
    {
        parent::__construct();
        $this->_scategory_mod =& m('scategory');
    }

    /* ���� */
    function index()
    {
        /* ȡ�õ��̷��� */
        $scategories = $this->_scategory_mod->get_list();
        $tree =& $this->_tree($scategories);

        /* �ȸ����� */
        $sorted_scategories = array();
        $scategory_ids = $tree->getChilds();
        foreach ($scategory_ids as $id)
        {
            $sorted_scategories[] = array_merge($scategories[$id], array('layer' => $tree->getLayer($id)));
        }
        $this->assign('scategories', $sorted_scategories);

        /* ����ӳ���ÿ�����ĸ�����Ӧ���У���1��ʼ�� */
        $row = array(0 => 0); // cate_id��Ӧ��row
        $map = array(); // parent_id��Ӧ��row
        foreach ($sorted_scategories as $key => $scategory)
        {
            $row[$scategory['cate_id']] = $key + 1;
            $map[] = $row[$scategory['parent_id']];
        }
        $this->assign('map', ecm_json_encode($map));
        //����jquery�����
        $this->import_resource(array(
                                    'script' => 'jqtreetable.js,inline_edit.js',
                                    'style'  => 'res:style/jqtreetable.css'));
        //$this->headtag('<link href="{res file=style/jqtreetable.css}" rel="stylesheet" type="text/css" /><script type="text/javascript" src="{lib file=jqtreetable.js}"></script>');
        $this->display('scategory.index.html');
    }

    /* ���� */
    function add()
    {
        if (!IS_POST)
        {
            /* ���� */
            $pid = empty($_GET['pid']) ? 0 : intval($_GET['pid']);
            $scategory = array('parent_id' => $pid, 'sort_order' => 255);
            $this->assign('scategory', $scategory);
            $this->import_resource(array(
                                        'script' => 'jquery.plugins/jquery.validate.js'));
            $this->assign('parents', $this->_get_options());
            $this->display('scategory.form.html');
        }
        else
        {
            $data = array(
                'cate_name' => $_POST['cate_name'],
                'parent_id' => $_POST['parent_id'],
                'sort_order' => $_POST['sort_order'],
            );

            /* ��������Ƿ��Ѵ��� */
            if (!$this->_scategory_mod->unique(trim($data['cate_name']), $data['parent_id']))
            {
                $this->show_warning('name_exist');
                return;
            }

            /* ���� */
            $cate_id = $this->_scategory_mod->add($data);
            if (!$cate_id)
            {
                $this->show_warning($this->_scategory_mod->get_error());
                return;
            }

            $this->show_message('add_ok',
                'back_list',    'index.php?app=scategory',
                'continue_add', 'index.php?app=scategory&amp;act=add&amp;pid=' . $data['parent_id']
                );
        }
    }

    /* �����̷������Ƶ�Ψһ�� */
    function check_scategory()
    {
        $cate_name = empty($_GET['cate_name']) ? '' : trim($_GET['cate_name']);
        $parent_id = empty($_GET['parent_id']) ? 0  : intval($_GET['parent_id']);
        $cate_id   = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$cate_name)
        {
            echo ecm_json_encode(true);
            return ;
        }
        if ($this->_scategory_mod->unique($cate_name, $parent_id, $cate_id))
        {
            echo ecm_json_encode(true);
        }
        else
        {
            echo ecm_json_encode(false);
        }
        return ;
    }

    /* �༭ */
    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* �Ƿ���� */
            $scategory = $this->_scategory_mod->get_info($id);
            if (!$scategory)
            {
                $this->show_warning('scategory_empty');
                return;
            }
            $this->assign('scategory', $scategory);
            $this->import_resource(array(
                                        'script' => 'jquery.plugins/jquery.validate.js'));
            $this->assign('parents', $this->_get_options($id));
            $this->display('scategory.form.html');
        }
        else
        {
            $data = array(
                'cate_name' => $_POST['cate_name'],
                'parent_id' => $_POST['parent_id'],
                'sort_order' => $_POST['sort_order'],
            );

            /* ��������Ƿ��Ѵ��� */
            if (!$this->_scategory_mod->unique(trim($data['cate_name']), $data['parent_id'], $id))
            {
                $this->show_warning('name_exist');
                return;
            }

            /* ���� */
            $rows = $this->_scategory_mod->edit($id, $data);
            if ($this->_scategory_mod->has_error())
            {
                $this->show_warning($this->_scategory_mod->get_error());
                return;
            }

            $this->show_message('edit_ok',
                'back_list',    'index.php?app=scategory',
                'edit_again',   'index.php?app=scategory&amp;act=edit&amp;id=' . $id
            );
        }
    }

         //�첽�޸�����
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('cate_name', 'sort_order')))
       {
           $data[$column] = $value;
           if($column == 'cate_name')
           {
               $scategory = $this->_scategory_mod->get_info($id);

               if(!$this->_scategory_mod->unique($value, $scategory['parent_id'], $id))
               {
                   echo ecm_json_encode(false);
                   return ;
               }
           }
           $this->_scategory_mod->edit($id, $data);
           if(!$this->_scategory_mod->has_error())
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

    /* ɾ�� */
    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_scategory_to_drop');
            return;
        }

        $ids = explode(',', $id);
        if (!$this->_scategory_mod->drop($ids))
        {
            $this->show_warning($this->_scategory_mod->get_error());
            return;
        }

        $this->show_message('drop_ok');
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
            $this->_scategory_mod->edit($id, array('sort_order' => $sort_orders[$key]));
        }

        $this->show_message('update_order_ok');
    }

/* �������� */
    function export()
    {
        // Ŀ�����
        $to_charset = (CHARSET == 'utf-8') ? substr(LANG, 0, 2) == 'sc' ? 'gbk' : 'big5' : '';

        if (!IS_POST)
        {
            if (CHARSET == 'utf-8')
            {
                $this->assign('note_for_export', sprintf(LANG::get('note_for_export'), $to_charset));
                $this->display('common.export.html');

                return;
            }
        }
        else
        {
            if (!$_POST['if_convert'])
            {
                $to_charset = '';
            }
        }

        $gcategories = $this->_scategory_mod->get_list();
        $tree =& $this->_tree($gcategories);
        $csv_data = $tree->getCSVData(0, 'sort_order');
        $this->export_to_csv($csv_data, 'scategory', $to_charset);
    }

    /* �������� */
    function import()
    {
        if (!IS_POST)
        {
            $this->assign('note_for_import', sprintf(LANG::get('note_for_import'), CHARSET));
            $this->display('common.import.html');
        }
        else
        {
            $file = $_FILES['csv'];
            if ($file['error'] != UPLOAD_ERR_OK)
            {
                $this->show_warning('select_file');
                return;
            }
            if ($file['name'] == basename($file['name'],'.csv'))
            {
                $this->show_warning('not_csv_file');
                return;
            }

            $data = $this->import_from_csv($file['tmp_name'], false, $_POST['charset'], CHARSET);
            $parents = array(0 => 0); // ���layer��parent������
            $fileds = array_intersect($data[0],array('cate_name', 'sort_order')); //��һ�к��е��ֶ�
            $start_col = intval(array_search('cate_name', $fileds)); //����������ʼ�к�
            foreach ($data as $row)
            {
                $layer = -1;
                if(array_intersect($row,array('cate_name', 'sort_order')))
                {
                    continue;
                }
                $sort_order_col = array_search('sort_order', $fileds); //�ӱ�ͷ�õ�sort_order���к�
                $sort_order = is_numeric($sort_order_col) && isset($row[$sort_order_col]) ? $row[$sort_order_col] : 255;
                for ($i = $start_col; $i < count($row); $i++)
                {
                    if (trim($row[$i]))
                    {
                        $layer = $i - $start_col;
                        $cate_name  = trim($row[$i]);
                        break;
                    }
                }

                // û���ݻ򳬳�����
                if ($layer < 0 || $layer >= MAX_LAYER)
                {
                    continue;
                }

                // ֻ�������ϼ���
                if (isset($parents[$layer]))
                {
                    $scategory = $this->_scategory_mod->get("cate_name = '$cate_name' AND parent_id = '$parents[$layer]'");
                    if (!$scategory)
                    {
                        // ������
                        $id = $this->_scategory_mod->add(array(
                            'cate_name'     => $cate_name,
                            'parent_id'     => $parents[$layer],
                            'sort_order'    => $sort_order,
                        ));
                        $parents[$layer + 1] = $id;
                    }
                    else
                    {
                        // �Ѵ���
                        $parents[$layer + 1] = $scategory['cate_id'];
                    }
                }
            }

            $this->show_message('import_ok',
                'back_list', 'index.php?app=scategory');
        }
    }

    /* ���첢������ */
    function &_tree($scategories)
    {
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree;
    }

    /* ȡ�ÿ�����Ϊ�ϼ��ĵ��̷������� */
    function _get_options($except = NULL)
    {
        $scategories = $this->_scategory_mod->get_list();
        $tree =& $this->_tree($scategories);
        return $tree->getOptions(MAX_LAYER - 1, 0, $except);
    }
}

?>