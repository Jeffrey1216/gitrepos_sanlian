<?php
define('MAX_LAYER', 4);

/* ���������� */
class RegionApp extends BackendApp
{
    var $_region_mod;

    function __construct()
    {
        $this->RegionApp();
    }

    function RegionApp()
    {
        parent::__construct();
        $this->_region_mod =& m('region');
    }

    /* ���� */
    function index()
    {
        /* ȡ�õ��� */
        $regions = $this->_region_mod->get_list(0);
        foreach ($regions as $key => $val)
        {
            $regions[$key]['switchs'] = 0;
            if ($this->_region_mod->get_list($val['region_id']))
            {
                $regions[$key]['switchs'] = 1;
            }
        }
        $this->assign('regions', $regions);

        $this->assign('max_layer', MAX_LAYER);

        $this->import_resource(array(
            'script' => 'inline_edit.js,jqtreetable.js',
            'style' => 'res:style/jqtreetable.css'
        ));
        $this->display('region.index.html');
    }

    /* ���� */
    function add()
    {
        if (!IS_POST)
        {
            /* ���� */
            $pid = empty($_GET['pid']) ? 0 : intval($_GET['pid']);
            $region = array('parent_id' => $pid, 'sort_order' => 255);
            $this->assign('region', $region);

            $this->assign('parents', $this->_get_options());
            $this->display('region.form.html');
        }
        else
        {
            $data = array(
                'region_name' => $_POST['region_name'],
                'parent_id' => $_POST['parent_id'],
                'sort_order' => $_POST['sort_order'],
            );

            /* ��������Ƿ��Ѵ��� */
            if (!$this->_region_mod->unique(trim($data['region_name']), $data['parent_id']))
            {
                $this->show_warning('name_exist');
                return;
            }

            /* ���� */
            $region_id = $this->_region_mod->add($data);
            if (!$region_id)
            {
                $this->show_warning($this->_region_mod->get_error());
                return;
            }

            $this->show_message('add_ok',
                'back_list',    'index.php?app=region',
                'continue_add', 'index.php?app=region&amp;act=add&amp;pid=' . $data['parent_id']
                );
        }
    }

    /* �༭ */
    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* �Ƿ���� */
            $region = $this->_region_mod->get_info($id);
            if (!$region)
            {
                $this->show_warning('region_empty');
                return;
            }
            $this->assign('region', $region);

            $this->assign('parents', $this->_get_options($id));
            $this->display('region.form.html');
        }
        else
        {
            $region = $this->_region_mod->get_info($id);
            if (empty($region))
            {
                $this->show_warning('no_such_region');

                return;
            }

            $data = array(
                'region_name' => $_POST['region_name'],
                'parent_id'   => $_POST['parent_id'],
                'sort_order'  => $_POST['sort_order'],
            );

            /* ��������Ƿ��Ѵ��� */
            if (!$this->_region_mod->unique(trim($data['region_name']), $data['parent_id'], $id))
            {
                $this->show_warning('name_exist');
                return;
            }

            /* ���ƶ��ڵ�ʱ����ƶ���Ľṹ�Ƿ�Ϸ� */
            if ($region['parent_id'] != $data['parent_id'])
            {
                /* ��ȡ�µĽڵ���Ϣ */
                $all_children = $this->_region_mod->get_descendant($id);
                $all_parents  = $this->_region_mod->get_parents($data['parent_id']);
                $new_regions = $this->_region_mod->find(array('conditions' => array_merge($all_children, $all_parents)));
                $new_regions[$id]['parent_id'] = $data['parent_id'];

                /* �ж�����Ƿ�Ϸ� */
                $tree = &$this->_tree($new_regions);
                if (max($tree->layer) > MAX_LAYER)
                {
                    $this->show_warning('path_depth_error');

                    return;
                }
            }

            /* ���� */
            $rows = $this->_region_mod->edit($id, $data);
            if ($this->_region_mod->has_error())
            {
                $this->show_warning($this->_region_mod->get_error());
                return;
            }

            $this->show_message('edit_ok',
                'back_list',    'index.php?app=region',
                'edit_again',   'index.php?app=region&amp;act=edit&amp;id=' . $id
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

       if (in_array($column ,array( 'sort_order')))
       {
           $data[$column] = $value;
           $this->_region_mod->edit($id, $data);
           if(!$this->_region_mod->has_error())
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

    /* �첽ȡ��һ������  */
    function ajax_cate()
    {
        if(!isset($_GET['id']) || empty($_GET['id']))
        {
            echo ecm_json_encode(false);
            return;
        }
        $cate = $this->_region_mod->get_list($_GET['id']);
        foreach ($cate as $key => $val)
        {
            $child = $this->_region_mod->get_list($val['region_id']);
            if (!$child || empty($child))
            {
                $cate[$key]['switchs'] = 0;
            }
            else
            {
                $cate[$key]['switchs'] = 1;
            }
        }
        header("Content-Type:text/html;charset=" . CHARSET);
        echo ecm_json_encode(array_values($cate));
        //$this->json_result($cate);
        return ;
    }

    /* ɾ�� */
    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_region_to_drop');
            return;
        }

        $ids = explode(',', $id);
        if (!$this->_region_mod->drop($ids))
        {
            $this->show_warning($this->_region_mod->get_error());
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
            $this->_region_mod->edit($id, array('sort_order' => $sort_orders[$key]));
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

        $regions = $this->_region_mod->get_list();
        $tree =& $this->_tree($regions);
        $csv_data = $tree->getCSVData(0, 'sort_order');
        $this->export_to_csv($csv_data, 'region', $to_charset);
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
            $fileds = array_intersect($data[0],array('region_name', 'sort_order')); //��һ�к��е��ֶ�
            $start_col = intval(array_search('region_name', $fileds)); //����������ʼ�к�
            foreach ($data as $row)
            {
                $layer = -1;
                 if(array_intersect($row,array('region_name', 'sort_order')))
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
                        $region_name  = trim($row[$i]);
                        break;
                    }
                }

                // û����
                if ($layer < 0)
                {
                    continue;
                }

                // ֻ�������ϼ���
                if (isset($parents[$layer]))
                {
                    $region = $this->_region_mod->get("region_name = '$region_name' AND parent_id = '$parents[$layer]'");
                    if (!$region)
                    {
                        // ������
                        $id = $this->_region_mod->add(array(
                            'region_name'   => $region_name,
                            'parent_id'     => $parents[$layer],
                            'sort_order'    => $sort_order,
                        ));
                        $parents[$layer + 1] = $id;
                    }
                    else
                    {
                        // �Ѵ���
                        $parents[$layer + 1] = $region['region_id'];
                    }
                }
            }

            $this->show_message('import_ok',
                'back_list', 'index.php?app=region');
        }
    }

    /* ���첢������ */
    function &_tree($regions)
    {
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($regions, 'region_id', 'parent_id', 'region_name');
        return $tree;
    }

    /* ȡ�ÿ�����Ϊ�ϼ��ĵ������� */
    function _get_options($except = NULL)
    {
        $regions = $this->_region_mod->get_list();
        $tree =& $this->_tree($regions);
        return $tree->getOptions(MAX_LAYER - 1, 0, $except);
    }
}

?>
