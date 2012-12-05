<?php

define('MAX_LAYER', 4);

/* ��Ʒ��������� */
class GcategoryApp extends BackendApp
{
    var $_gcategory_mod;

    /**
     * ���캯��
     */
    function __construct()
    {
        $this->GcategoryApp();
    }
    function GcategoryApp()
    {
        parent::__construct();

        $this->_gcategory_mod =& bm('gcategory', array('_store_id' => 0));
    }

    /* ���� */
    function index()
    {
        /* ȡ����Ʒ���� */
        $gcategories = $this->_gcategory_mod->get_all_list(0);
        $tree =& $this->_tree($gcategories);
		
        /* �ȸ����� */
        foreach ($gcategories as $key => $val)
        {
            $gcategories[$key]['switchs'] = 0;
            if ($this->_gcategory_mod->get_all_list($val['cate_id']))
            {
               $gcategories[$key]['switchs'] = 1;
            }
        }
        
        $this->assign('gcategories', $gcategories);
        /* ����ӳ���ÿ�����ĸ�����Ӧ���У���1��ʼ�� */

        $this->assign('max_layer', MAX_LAYER);

        /* ����jQuery�ı���֤��� */
        $this->import_resource(array(
            'script' => 'jqtable.js,inline_edit.js',
            'style'  => 'res:style/jqtreetable.css'
        ));
        $this->display('gcategory.index.html');
    }

    /* �첽ȥ��Ʒ������Ԫ�� */
    function ajax_cate()
    {
        if(!isset($_GET['id']) || empty($_GET['id']))
        {
            echo ecm_json_encode(false);
            return;
        }
        $this->_gcategory_mod =& bm('gcategory');
        $cate = $this->_gcategory_mod->get_all_list($_GET['id']);
        foreach ($cate as $key => $val)
        {
            $child = $this->_gcategory_mod->get_all_list($val['cate_id']);
            $lay = $this->_gcategory_mod->get_layer($val['cate_id']);
            if ($lay >= MAX_LAYER)
            {
                $cate[$key]['add_child'] = 0;
            }
            else 
            {
                $cate[$key]['add_child'] = 1;
            }
            if (!$child || empty($child) )
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
        return ;
    }

    /* ���� */
    function add()
    {
        if (!IS_POST)
        {
            /* ���� */
            $pid = empty($_GET['pid']) ? 0 : intval($_GET['pid']);
            $gcategory = array('parent_id' => $pid, 'sort_order' => 255, 'if_show' => 1);
            if ($pid){
            	$ginfo = $this->_gcategory_mod->get_info($pid);
            	$gcategory['mall_type'] = $ginfo['mall_type'];
            }
            $this->assign('gcategory', $gcategory);
            $this->assign('parents', $this->_get_options());
            /* ����jQuery�ı���֤��� */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->display('gcategory.form.html');
        }
        else
        {
            $data = array(
                'cate_name'  => $_POST['cate_name'],
                'parent_id'  => $_POST['parent_id'],
                'sort_order' => $_POST['sort_order'],
                'if_show'    => $_POST['if_show'],
            	'mall_type'  => $_POST['mall_type'],
            	'is_index' => intval($_POST['is_index']),
            	'icon' => trim($_POST['icon']),
            	'typelog' => intval($_POST['typelog']),
            );

            /* ��������Ƿ��Ѵ��� */
            if (!$this->_gcategory_mod->unique(trim($data['cate_name']), $data['parent_id'], $data['mall_type']))
            {
                $this->show_warning('name_exist');
                return;
            }

            /* ��鼶�� */
            $ancestor = $this->_gcategory_mod->get_ancestor($data['parent_id']);
            if (count($ancestor) >= MAX_LAYER)
            {
                $this->show_warning('max_layer_error');
                return;
            }

            /* ���� */
            $cate_id = $this->_gcategory_mod->add($data);
            if (!$cate_id)
            {
                $this->show_warning($this->_gcategory_mod->get_error());
                return;
            }

            $this->show_message('add_ok',
                'back_list',    'index.php?app=gcategory',
                'continue_add', 'index.php?app=gcategory&amp;act=add&amp;pid=' . $data['parent_id']
            );
        }
    }

    /* ����������Ψһ*/
    function check_gcategory ()
    {
        $cate_name = empty($_GET['cate_name']) ? '' : trim($_GET['cate_name']);
        $parent_id = empty($_GET['parent_id']) ? 0  : intval($_GET['parent_id']);
        $cate_id   = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$cate_name)
        {
            echo ecm_json_encode(true);
            return ;
        }
        if ($this->_gcategory_mod->unique($cate_name, $parent_id, $cate_id))
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
            $gcategory = $this->_gcategory_mod->get_info($id);
            
            $this->assign('gcategory', $gcategory);
            /* ����jQuery�ı���֤��� */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->assign('parents', $this->_get_options($id));
            $this->display('gcategory.form.html');
        }
        else
        {
            $data = array(
                'cate_name'  => $_POST['cate_name'],
                'parent_id'  => $_POST['parent_id'],
                'sort_order' => $_POST['sort_order'],
                'if_show'    => $_POST['if_show'],
            	'mall_type'  => $_POST['mall_type'],
            	'is_index' => intval($_POST['is_index']),
            	'icon' => trim($_POST['icon']),
            	'typelog' => intval($_POST['typelog']),
            );


            /* ��鼶�� */
            $depth    = $this->_gcategory_mod->get_depth($id);
            $ancestor = $this->_gcategory_mod->get_ancestor($data['parent_id']);
            if ($depth + count($ancestor) > MAX_LAYER)
            {
                $this->show_warning('max_layer_error');
                return;
            }

            /* ���� */
            $old_data = $this->_gcategory_mod->get_info($id); // ����ǰ������
            $rows = $this->_gcategory_mod->edit($id, $data);
            if ($this->_gcategory_mod->has_error())
            {
                $this->show_warning($this->_gcategory_mod->get_error());
                return;
            }

            /* ����ı����ϼ����࣬������Ʒ������Ӧ��¼��cate_id_1��cate_id_4 */
            if ($old_data['parent_id'] != $data['parent_id'])
            {
                // ִ��ʱ����ܱȽϳ������Բ�������
                _at(set_time_limit, 0);

                // ����̳���Ʒ���໺��
                $cache_server =& cache_server();
                $cache_server->delete('goods_category_0');

                // ȡ�õ�ǰ���������������ࣨ��������
                $cids = $this->_gcategory_mod->get_descendant_ids($id, true);

                // �ҳ���Щ����������Ʒ�ķ���
                $mod_goods =& m('goods');
                $mod_gcate =& $this->_gcategory_mod;
                $sql = "SELECT DISTINCT cate_id FROM {$mod_goods->table} WHERE cate_id " . db_create_in($cids);
                $cate_ids = $mod_goods->getCol($sql);

                // ѭ������ÿ�������cate_id_1��cate_id_4
                foreach ($cate_ids as $cate_id)
                {
                    $cate_id_n = array(0,0,0,0);
                    $ancestor = $mod_gcate->get_ancestor($cate_id, true);
                    for ($i = 0; $i < 4; $i++)
                    {
                        isset($ancestor[$i]) && $cate_id_n[$i] = $ancestor[$i]['cate_id'];
                    }
                    $sql = "UPDATE {$mod_goods->table} " .
                            "SET cate_id_1 = '{$cate_id_n[0]}', cate_id_2 = '{$cate_id_n[1]}', cate_id_3 = '{$cate_id_n[2]}', cate_id_4 = '{$cate_id_n[3]}' " .
                            "WHERE cate_id = '$cate_id'";
                    $mod_goods->db->query($sql);
                }
            }

            $this->show_message('edit_ok',
                'back_list',    'index.php?app=gcategory',
                'edit_again',   'index.php?app=gcategory&amp;act=edit&amp;id=' . $id
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
       if (in_array($column ,array('cate_name', 'if_show', 'sort_order')))
       {
           $data[$column] = $value;
           if($column == 'cate_name')
           {
               $gcategory = $this->_gcategory_mod->get_info($id);
               if (!$this->_gcategory_mod->unique($value, $gcategory['parent_id'], $id))
               {
                   echo ecm_json_encode(false);
                   return ;
               }
           }
           $this->_gcategory_mod->edit($id, $data);
           if(!$this->_gcategory_mod->has_error())
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

    /* �����༭ */
    function batch_edit()
    {
        if (!IS_POST)
        {
            $this->display('gcategory.batch.html');
        }
        else
        {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            if (!$id)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $ids = explode(',', $id);
            if ($_POST['if_show'] >= 0)
            {
                $data = array('if_show' => $_POST['if_show'] ? 1 : 0);
                $this->_gcategory_mod->edit($ids, $data);
            }

            $this->show_message('batch_edit_ok',
                'back_list', 'index.php?app=gcategory');
        }
    }

    /* ɾ�� */
    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_gcategory_to_drop');
            return;
        }

        $ids = explode(',', $id);
        if (!$this->_gcategory_mod->drop($ids))
        {
            $this->show_warning($this->_gcategory_mod->get_error());
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
            $this->_gcategory_mod->edit($id, array('sort_order' => $sort_orders[$key]));
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

        $gcategories = $this->_gcategory_mod->get_list();
        $tree =& $this->_tree($gcategories);
        $csv_data = $tree->getCSVData(0, array('sort_order', 'if_show'));
        $this->export_to_csv($csv_data, 'gcategory', $to_charset);
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
            $fileds = array_intersect($data[0],array('cate_name', 'sort_order', 'if_show')); //��һ�к��е��ֶ�
            $start_col = intval(array_search('cate_name', $fileds)); //����������ʼ�к�
            foreach ($data as $row)
            {
                $layer = -1;
                if(array_intersect($row,array('cate_name', 'sort_order', 'if_show')))
                {
                    continue;
                }
                $if_show_col = array_search('if_show', $fileds); //�ӱ�ͷ�õ�if_show���к�
                $if_show = is_numeric($if_show_col) && isset($row[$if_show_col]) ? $row[$if_show_col] : 1;
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
                // û����
                if ($layer < 0)
                {
                    continue;
                }
                // ֻ�������ϼ���
                if (isset($parents[$layer]))
                {
                    $gcategory = $this->_gcategory_mod->get("cate_name = '$cate_name' AND parent_id = '$parents[$layer]'");
                    if (!$gcategory)
                    {
                        // ������
                        $id = $this->_gcategory_mod->add(array(
                            'cate_name'     => $cate_name,
                            'parent_id'     => $parents[$layer],
                            'sort_order'    => $sort_order,
                            'if_show'       => $if_show,
                        ));
                        $parents[$layer + 1] = $id;
                    }
                    else
                    {
                        // �Ѵ���
                        $parents[$layer + 1] = $gcategory['cate_id'];
                    }
                }
            }

            $this->show_message('import_ok',
                'back_list', 'index.php?app=gcategory');
        }
    }

    /* ���첢������ */
    function &_tree($gcategories)
    {
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree;
    }

    /* ȡ�ÿ�����Ϊ�ϼ�����Ʒ�������� */
    function _get_options($except = NULL)
    {
        $gcategories = $this->_gcategory_mod->get_list();
        $tree =& $this->_tree($gcategories);

        return $tree->getOptions(MAX_LAYER - 1, 0, $except);
    }
}

?>