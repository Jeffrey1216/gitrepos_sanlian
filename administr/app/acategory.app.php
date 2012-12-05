<?php

define('MAX_LAYER', 2);

/* ���·�������� */
class AcategoryApp extends BackendApp
{
    var $_acategory_mod;

    function __construct()
    {
        $this->AcategoryApp();
    }

    function AcategoryApp()
    {
        parent::BackendApp();

        $this->_acategory_mod =& m('acategory');
    }

    /* ���� */
    function index()
    {
        /* ȡ�����·��� */
        $acategories = $this->_acategory_mod->get_list();
        $tree = & $this->_tree($acategories);
        /* �ȸ����� */
        $sorted_acategories = array();
        $cate_ids = $tree->getChilds();
        foreach ($cate_ids as $id)
        {
            $parent_children_valid = $this->_acategory_mod->parent_children_valid($id);
            $sorted_acategories[] = array_merge($acategories[$id], array('layer' => $tree->getLayer($id), 'parent_children_valid'=>$parent_children_valid));
        }
        $this->assign('acategories', $sorted_acategories);

        /* ����ӳ���ÿ�����ĸ�����Ӧ���У���1��ʼ�� */
        $row = array(0 => 0);   // cate_id��Ӧ��row
        $map = array();         // parent_id��Ӧ��row
        foreach ($sorted_acategories as $key => $acategory)
        {
            $row[$acategory['cate_id']] = $key + 1;
            $map[] = $row[$acategory['parent_id']];
        }
        $this->assign('map', ecm_json_encode($map));

        $this->assign('max_layer', MAX_LAYER);

        $this->import_resource(array(
            'script' => 'jqtreetable.js,inline_edit.js',
            'style'  => 'res:style/jqtreetable.css')
        );
        $this->display('acategory.index.html');
    }

    /* ���� */
    function add()
    {
        if (!IS_POST)
        {
            /* ���� */
            $pid = empty($_GET['pid']) ? 0 : intval($_GET['pid']);
            $acategory = array('parent_id' => $pid, 'sort_order' => 255);
            $this->assign('acategory', $acategory);

            /* �����ǰ�����ǲ��������¼��ķ��࣬�򲻿�����¼����� */
            if(!$this->_acategory_mod->parent_children_valid($pid))
            {
                $this->show_warning('cannot_add_children');
                return;
            }
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->assign('parents', $this->_get_options());
            $this->display('acategory.form.html');
        }
        else
        {
            $data = array(
                'cate_name'  => $_POST['cate_name'],
                'parent_id'  => $_POST['parent_id'],
                'sort_order' => $_POST['sort_order'],
            );

            /* ��������Ƿ��Ѵ��� */
            if (!$this->_acategory_mod->unique(trim($data['cate_name']), $data['parent_id']))
            {
                $this->show_warning('name_exist');
                return;
            }

            /* ѡ����ϼ����಻�������¼����� */
            if(!$this->_acategory_mod->parent_children_valid($data['parent_id']))
            {
                $this->show_warning('cannot_be_parent');
                return;
            }

            /* ���� */
            $cate_id = $this->_acategory_mod->add($data);
            if (!$cate_id)
            {
                $this->show_warning($this->_acategory_mod->get_error());
                return;
            }

            $this->show_message('add_ok',
                'back_list',    'index.php?app=acategory',
                'continue_add', 'index.php?app=acategory&amp;act=add&amp;pid=' . $data['parent_id']
            );
        }
    }

    /* ������·����Ψһ�� */
    function check_acategory()
    {
        $cate_name = empty($_GET['cate_name']) ? '' : trim($_GET['cate_name']);
        $parent_id = empty($_GET['parent_id']) ? 0 : intval($_GET['parent_id']);
        $cate_id   = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$cate_name)
        {
            echo ecm_json_encode(true);
            return ;
        }
        if ($this->_acategory_mod->unique($cate_name, $parent_id, $cate_id))
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
            $acategory = $this->_acategory_mod->get_info($id);
            if (!$acategory)
            {
                $this->show_warning('acategory_empty');
                return;
            }
            /* �����ǰ������ϵͳ���࣬�򲻿ɱ༭ */
            if($acategory['code'])
            {
                $this->show_warning('cannot_edit_system_acategory');
                return;
            }
            $this->assign('acategory', $acategory);
            if ($this->_acategory_mod->parent_children_valid($id))
            {
                $this->assign('parents', $this->_get_options($id));
            }
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->display('acategory.form.html');
        }
        else
        {
            $data = array(
                'cate_name'  => $_POST['cate_name'],
                'parent_id'  => $_POST['parent_id'],
                'sort_order' => $_POST['sort_order'],
            );

            /* ѡ����ϼ����಻�������¼����� */
            if(!$this->_acategory_mod->parent_children_valid($data['parent_id']))
            {
                $this->show_warning('cannot_be_parent');
                return;
            }
            /* ��������Ƿ��Ѵ��� */
            if (!$this->_acategory_mod->unique(trim($data['cate_name']), $data['parent_id'], $id))
            {
                $this->show_warning('name_exist');
                return;
            }

            /* ���� */
            $rows = $this->_acategory_mod->edit($id, $data);
            if ($this->_acategory_mod->has_error())
            {
                $this->show_warning($this->_acategory_mod->get_error());
                return;
            }

            $this->show_message('edit_ok',
                'back_list',    'index.php?app=acategory',
                'edit_again',   'index.php?app=acategory&amp;act=edit&amp;id=' . $id
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
               $acategory = $this->_acategory_mod->get_info($id);

               if(!$this->_acategory_mod->unique($value, $acategory['parent_id'], $id))
               {
                   echo ecm_json_encode(false);
                   return ;
               }
           }
           $this->_acategory_mod->edit($id, $data);
           if(!$this->_acategory_mod->has_error())
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
            $this->show_warning('no_acategory_to_drop');
            return;
        }

        $ids = explode(',', $id);
        $message = 'drop_ok';
        foreach ($ids as $key=>$id){
            $acategory=$this->_acategory_mod->find(intval($id));
            $acategory=current($acategory);
            if($acategory['code']!=null)
            {
                unset($ids[$key]);  //�в�����ϵͳ���� ���˵�
                $message = 'drop_ok_system_acategory';
            }
        }
        if (!$ids)
        {
            $message = 'system_acategory'; //ȫ��ϵͳ����
            $this->show_warning($message);

            return;
        }

        if (!$this->_acategory_mod->drop($ids))
        {
            $this->show_warning($this->_acategory_mod->get_error());
            return;
        }

        $this->show_message($message);
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
            $this->_acategory_mod->edit($id, array('sort_order' => $sort_orders[$key]));
        }

        $this->show_message('update_order_ok');
    }

    /* ���첢������ */
    function &_tree($acategories)
    {
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($acategories, 'cate_id', 'parent_id', 'cate_name');
       
        return $tree;
    }

    /* ȡ�ÿ�����Ϊ�ϼ������·������� */
    function _get_options($except = NULL)
    {
        $acategories = $this->_acategory_mod->get_list();

        /* ���˵�������Ϊ�ϼ��ķ��� */
        foreach ($acategories as $key => $acategorie)
        {
            if (!$this->_acategory_mod->parent_children_valid($acategorie['cate_id']))
            {
                unset($acategories[$key]);
            }
        }

        $tree =& $this->_tree($acategories);
        return $tree->getOptions(MAX_LAYER - 1, 0, $except);
    }
}

?>