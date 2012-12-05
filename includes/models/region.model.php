<?php

/* ���� region */
class RegionModel extends BaseModel
{
    var $table  = 'region';
    var $prikey = 'region_id';
    var $_name  = 'region';

    var $_relation  = array(
        // һ�������ж���ӵ���
        'has_region' => array(
            'model'         => 'region',
            'type'          => HAS_MANY,
            'foreign_key'   => 'parent_id',
            'dependent'     => true
        ),
    );

    var $_autov = array(
        'region_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'sort_order'    => array(
            'filter'    => 'intval',
        ),
    );

    /**
     * ȡ�õ����б�
     *
     * @param int $parent_id ���ڵ���0��ʾȡĳ���������¼�������С��0��ʾȡ���е���
     * @return array
     */
    function get_list($parent_id = -1)
    {
        if ($parent_id >= 0)
        {
            return $this->find(array(
                'conditions' => "parent_id = '$parent_id'",
                'order' => 'sort_order, region_id',
            ));
        }
        else
        {
            return $this->find(array(
                'order' => 'sort_order, region_id',
            ));
        }
    }

    /*
     * �ж������Ƿ�Ψһ
     */
    function unique($region_name, $parent_id, $region_id = 0)
    {
        $conditions = "parent_id = '" . $parent_id . "' AND region_name = '" . $region_name . "'";
        $region_id && $conditions .= " AND region_id <> '" . $region_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }

    /**
     * ȡ��options�����������б�
     */
    function get_options($parent_id = 0)
    {
        $res = array();
        $regions = $this->get_list($parent_id);
        foreach ($regions as $region)
        {
            $res[$region['region_id']] = $region['region_name'];
        }
        return $res;
    }

    /**
     * ȡ��ĳ�����������������id
     */
    function get_descendant($id)
    {
        $ids = array($id);
        $ids_total = array();
        $this->_get_descendant($ids, $ids_total);
        return array_unique($ids_total);
    }

    /**
     * ȡ��ĳ���������и�������
     *
     * @author Garbin
     * @param  int $region_id
     * @return void
     **/
    function get_parents($region_id)
    {
        $parents = array();
        $region = $this->get($region_id);
        if (!empty($region))
        {
            if ($region['parent_id'])
            {
                $tmp = $this->get_parents($region['parent_id']);
                $parents = array_merge($parents, $tmp);
                $parents[] = $region['parent_id'];
            }
            $parents[] = $region_id;
        }

        return array_unique($parents);
    }

    function _get_descendant($ids, &$ids_total)
    {
        $childs = $this->find(array(
            'fields' => 'region_id',
            'conditions' => "parent_id " . db_create_in($ids)
        ));
        $ids_total = array_merge($ids_total, $ids);
        $ids = array();
        foreach ($childs as $child)
        {
            $ids[] = $child['region_id'];
        }
        if (empty($ids))
        {
            return ;
        }
        $this->_get_descendant($ids, $ids_total);
    }  
    function is_leaf($id)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} " . " WHERE parent_id = '$id'";

        return $this->getOne($sql) == 0;
    }
}

?>
