<?php

/* ��Ʒ���� gcategory */
class GcategoryModel extends BaseModel
{
    var $table  = 'gcategory';
    var $prikey = 'cate_id';
    var $_name  = 'gcategory';
    var $_relation  = array(
        // һ������ֻ������һ������
        'belongs_to_store' => array(
            'model'         => 'model',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'store_id',
            'reverse'       => 'has_gcategory',
        ),
        // һ�������ж���ӷ���
        'has_gcategory' => array(
            'model'         => 'gcategory',
            'type'          => HAS_MANY,
            'foreign_key'   => 'parent_id',
            'dependent'     => true
        ),
        // �������Ʒ�Ƕ�Զ�Ĺ�ϵ
        'has_goods' => array(
            'model'         => 'goods',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'category_goods',
            'foreign_key'   => 'cate_id',
            'reverse'       => 'belongs_to_gcategory',
        ),
    );

    var $_autov = array(
        'cate_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'parent_id' => array(
        ),
        'sort_order' => array(
            'filter'    => 'intval',
        ),
        'if_show' => array(
        ),
    );

    /**
     * ȡ�÷����б�
     *
     * @param int $parent_id ���ڵ���0��ʾȡĳ������¼����࣬С��0��ʾȡ���з���
     * @param bool $shown ֻȡҪ��ʾ�ķ���
     * @return array
     */
    function get_list($parent_id = -1, $shown = false)
    {
        $conditions = "1 = 1";
        $parent_id >= 0 && $conditions .= " AND parent_id = '$parent_id'";
        $shown && $conditions .= " AND if_show = 1";
        return $this->find(array(
            'conditions' => $conditions,
            'order' => 'sort_order, cate_id',
        ));
    }
    /**
     * ȡ�÷����б�
     *
     * @param int $parent_id ���ڵ���0��ʾȡĳ������¼����࣬С��0��ʾȡ���з���
     * @param bool $shown ֻȡҪ��ʾ�ķ���
     * @return array
     */
    function get_agro_list($parent_id = 0, $shown = false, $mall_type = 1)
    {
        $conditions = "1 = 1";
        $parent_id >= 0 && $conditions .= " AND parent_id = '$parent_id' AND mall_type = " . $mall_type;
        $shown && $conditions .= " AND if_show = 1 AND mall_type = " . $mall_type;
        return $this->find(array(
            'conditions' => $conditions,
            'order' => 'sort_order, cate_id',
        ));
    }
    
	/**
     * ȡ�÷����б�
     *
     * @param int $parent_id ���ڵ���0��ʾȡĳ������¼����࣬С��0��ʾȡ���з���
     * @param bool $shown ֻȡҪ��ʾ�ķ���
     * @return array
     */
    function get_all_list($parent_id = -1, $shown = false)
    {
        $conditions = "1 = 1";
        $parent_id >= 0 && $conditions .= " AND parent_id = '$parent_id'";
        $shown && $conditions .= " AND if_show = 1";
        return $this->find(array(
            'conditions' => $conditions,
            'order' => 'sort_order, cate_id',
        ));
    }

	function get_all_options($parent_id = -1, $shown = false)
    {
        $options = array();
        $rows = $this->get_all_list($parent_id, $shown);
        foreach ($rows as $row)
        {
            $options[$row['cate_id']] = $row['cate_name'];
        }

        return $options;
    }
    
    function get_options($parent_id = -1, $shown = false)
    {
        $options = array();
        $rows = $this->get_list($parent_id, $shown);
        foreach ($rows as $row)
        {
            $options[$row['cate_id']] = $row['cate_name'];
        }
        return $options;
    }
    
	function get_mall_options($parent_id = -1, $shown = false, $mall_type = 0)
    {
        $options = array();
        $rows = $this->get_list($parent_id, $shown, $mall_type);
        foreach ($rows as $row)
        {
            $options[$row['cate_id']] = $row['cate_name'];
        }

        return $options;
    }
    

    /**
     * ȡ��ĳ����������������id�����Ƽ�ʹ�ã�������Ϊ����ģ���ר��ģ�����õ��ˣ�������ʱ�������Ƽ�ʹ��ҵ��ģ���е���غ�����
     */
    function get_descendant($id)
    {
        $ids = array($id);
        $ids_total = array();
        $this->_get_descendant($ids, $ids_total);
        return array_unique($ids_total);
    }
    function _get_descendant($ids, &$ids_total)
    {
        $childs = $this->find(array(
            'fields' => 'cate_id',
            'conditions' => "parent_id " . db_create_in($ids)
        ));
        $ids_total = array_merge($ids_total, $ids);
        $ids = array();
        foreach ($childs as $child)
        {
            $ids[] = $child['cate_id'];
        }
        if (empty($ids))
        {
            return ;
        }
        $this->_get_descendant($ids, $ids_total);
    }
}

/* ��Ʒ����ҵ��ģ�� */
class GcategoryBModel extends GcategoryModel
{
    var $_store_id = 0;

    /*
     * �ж������Ƿ�Ψһ
     */
    function unique($cate_name, $parent_id, $mall_type = 1, $cate_id = 0)
    {
        $conditions = "parent_id = '$parent_id' AND cate_name = '$cate_name' AND mall_type = " . $mall_type;
        $cate_id && $conditions .= " AND cate_id <> '" . $cate_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }

    /* ���ǻ��෽�� */
    function add($data, $compatible = false)
    {
        $this->clear_cache();

        $data['store_id'] = $this->_store_id;
        return parent::add($data, $compatible);
    }

    function edit($conditions, $edit_data)
    {
        $this->clear_cache();

        return parent::edit($conditions, $edit_data);
    }

    function drop($conditions, $fields = '')
    {
        $this->clear_cache();

        return parent::drop($conditions, $fields);
    }

    /* ���ǻ��෽�� */
    function _getConditions($conditions, $if_add_alias = false)
    {
        $alias = '';
        if ($if_add_alias)
        {
            $alias = $this->alias . '.';
        }
        $res = parent::_getConditions($conditions, $if_add_alias);
        return $res ? $res . " AND {$alias}store_id = '{$this->_store_id}'" : " WHERE {$alias}store_id = '{$this->_store_id}'";
    }
    
    function is_leaf($id)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} " . $this->_getConditions("parent_id = '$id'");

        return $this->getOne($sql) == 0;
    }

    /**
     * ȡ��ĳ�������ȣ�û���ӽڵ����Ϊ1��
     * 
     * @param   int     $id     ����id���豣֤�Ǵ��ڵģ�
     * @return  int     ���
     */
    function get_depth($id)
    {
        $depth = 0;

        $pids = array($id);
        while ($pids)
        {
            $depth++;
            $sql  = "SELECT cate_id FROM {$this->table} " . $this->_getConditions("parent_id " . db_create_in($pids));
            $pids = $this->getCol($sql);
        }

        return $depth;
    }

    /**
     * ȡ��ĳ������ӷ���
     *
     * @param   int     $id     ����id
     * @param   bool    $cached �Ƿ񻺴棨�������ݲ���������ʾ�ķ��࣬һ������ǰ̨���ǻ������ݰ�������ʾ�ķ��࣬һ�����ں�̨��
     * @return  array(
     *              array('cate_id' => 1, 'cate_name' => '�����Ʒ'),
     *              array('cate_id' => 2, 'cate_name' => '�ֻ�'),
     *              ...
     *          )
     */
    function get_children($id, $cached = false)
    {
        $res = array();

        if ($cached)
        {
            $data = $this->_get_structured_data();
            if (isset($data[$id]))
            {
                $cids = $data[$id]['cids'];
                foreach ($cids as $id)
                {
                    $res[$id] = array(
                        'cate_id'   => $id, 
                        'cate_name' => $data[$id]['name'],
                    );
                }
            }
        }
        else
        {
            $sql = "SELECT cate_id, cate_name FROM {$this->table} WHERE parent_id = '$id' AND store_id = '{$this->_store_id}'";
            $res = $this->getAll($sql);
        }

        return $res;
    }

    /**
     * ȡ��ĳ��������ȷ��ࣨ�����������㼶����
     *
     * @param   int     $id     ����id
     * @param   bool    $cached �Ƿ񻺴棨�������ݲ���������ʾ�ķ��࣬һ������ǰ̨���ǻ������ݰ�������ʾ�ķ��࣬һ�����ں�̨��
     * @return  array(
     *              array('cate_id' => 1, 'cate_name' => '�����Ʒ'),
     *              array('cate_id' => 2, 'cate_name' => '�ֻ�'),
     *              ...
     *          )
     */
    function get_ancestor($id, $cached = false)
    {
        $res = array();
        if ($cached)
        {
            $data = $this->_get_structured_data();
            if ($id > 0 && isset($data[$id]))
            {
                while ($id > 0)
                {
                    $res[] = array('cate_id' => $id, 'cate_name' => $data[$id]['name']);
                    $id    = $data[$id]['pid'];
                }
            }
        }
        else
        {
            while ($id > 0)
            {
                $sql = "SELECT cate_id, cate_name, parent_id FROM {$this->table} WHERE cate_id = '$id' AND store_id = '{$this->_store_id}'";
                $row = $this->getRow($sql);
                
                if ($row)
                {
                    $res[] = array('cate_id' => $row['cate_id'], 'cate_name' => $row['cate_name']);
                    $id    = $row['parent_id'];
                }
            }
        }
        return array_reverse($res);
    }

    /**
     * ȡ��ĳ����Ĳ㼶����1��ʼ����
     * 
     * @param   int     $id     ����id
     * @param   bool    $cached �Ƿ񻺴棨�������ݲ���������ʾ�ķ��࣬һ������ǰ̨���ǻ������ݰ�������ʾ�ķ��࣬һ�����ں�̨��
     * @return  int     �㼶     �����಻���ڻ���ʾʱ����false
     */
    function get_layer($id, $cached = false)
    {
        $ancestor = $this->get_ancestor($id, $cached);
        if (empty($ancestor))
        {
            return false; //���಻���ڻ���ʾ
        }
        else
        {
            return count($ancestor);
        }        
    }
    
    /**
     * ȡ��ĳ������������id����������id��
     *
     * @param   int     $id     ����id
     * @param   bool    $cached �Ƿ񻺴棨�������ݲ���������ʾ�ķ��࣬һ������ǰ̨���ǻ������ݰ�������ʾ�ķ��࣬һ�����ں�̨��
     * @return  array(1, 2, 3, ...)
     */
    function get_descendant_ids($id, $cached = false)
    {
        $res = array($id);

        if ($cached)
        {
            $data = $this->_get_structured_data();
            if ($id > 0 && isset($data[$id]))
            {
                $i = 0;
                while ($i < count($res))
                {
                    $id  = $res[$i];
                    $res = array_merge($res, $data[$id]['cids']);
                    $i++;
                }
            }
        }
        else
        {
            $cids = array($id);
            while (!empty($cids))
            {
                $sql  = "SELECT cate_id FROM {$this->table} WHERE parent_id " . db_create_in($cids) . " AND store_id = '{$this->_store_id}'";
                $cids = $this->getCol($sql);
                $res  = array_merge($res, $cids);
            }
        }

        return $res;
    }


    /**
     * ȡ�ýṹ���ķ������ݣ�����������ʾ�ķ��࣬���ݻỺ�棩
     *
     * @return array(
     *      0 => array(                             'cids' => array(1, 2, 3))
     *      1 => array('name' => 'abc', 'pid' => 0, 'cids' => array(2, 3, 4)),
     *      2 => array('name) => 'xyz', 'pid' => 1, 'cids' => array()
     * )
     *    ����id        ��������             ������id     �ӷ���ids
     */
    function _get_structured_data()
    {
        $cache_server =& cache_server();
        $key  = 'goods_category_' . $this->_store_id;
        $data = $cache_server->get($key);
        $data = false;
        if ($data === false)
        {
            $data = array(0 => array('cids' => array()));

            $cate_list = $this->get_all_list(-1, true);
            
            foreach ($cate_list as $id => $cate)
            {
                $data[$id] = array(
                    'name' => $cate['cate_name'],
                    'pid'  => $cate['parent_id'],
                    'cids' => array()
                );
            }
			
            foreach ($cate_list as $id => $cate)
            {
                $pid = $cate['parent_id'];
                isset($data[$pid]) && $data[$pid]['cids'][] = $id;
            }
            $cache_server->set($key, $data, 1800);
        }

        return $data;
    }

    /* ������� */
    function clear_cache()
    {
        $cache_server =& cache_server();
        $keys = array('goods_category_' . $this->_store_id, 'page_goods_category', 'function_get_store_data_' . $this->_store_id);
        foreach ($keys as $key)
        {
            $cache_server->delete($key);
        }
    }
}

?>