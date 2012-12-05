<?php

/* ���̷��� scategory */
class ScategoryModel extends BaseModel
{
    var $table  = 'scategory';
    var $prikey = 'cate_id';
    var $_name  = 'scategory';
    var $_relation  =   array(
        // һ�������ж���ӷ���
        'has_scategory' => array(
            'model'         => 'scategory',
            'type'          => HAS_MANY,
            'foreign_key'   => 'parent_id',
            'dependent'     => true
        ),
        // ����͵����Ƕ�Զ�Ĺ�ϵ
        'belongs_to_store' => array(
            'model'         => 'store',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'category_store',
            'foreign_key'   => 'cate_id',
            'reverse'       => 'has_scategory',
        ),
    );

    var $_autov = array(
        'cate_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'parent_id'  => array(
        ),
        'sort_order'    => array(
            'filter'    => 'intval',
        ),
    );

    /**
     * ȡ�õ��̷����б�
     *
     * @param int $parent_id ���ڵ���0��ʾȡĳ�����̷�����¼����̷��࣬С��0��ʾȡ���е��̷���
     * @return array
     */
    function get_list($parent_id = -1)
    {
        if ($parent_id >= 0)
        {
            return $this->find(array(
                'conditions' => "parent_id = '$parent_id'",
                'order' => 'sort_order, cate_id',
            ));
        }
        else
        {
            return $this->find(array(
                'order' => 'sort_order, cate_id',
            ));
        }
    }

    /*
     * �ж������Ƿ�Ψһ
     */
    function unique($cate_name, $parent_id, $cate_id = 0)
    {
        $conditions = "parent_id = '" . $parent_id . "' AND cate_name = '" . $cate_name . "'";
        $cate_id && $conditions .= " AND cate_id <> '" . $cate_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }

    /**
     * ��ĳ���༰���ϼ�����ӵ�����ǰ
     */
    function get_parents(&$parents, $id)
    {
        $data = $this->get(intval($id));
        array_unshift($parents, array('cate_id' => $data['cate_id'], 'cate_name' => $data['cate_name']));
        if ($data['parent_id'] > 0)
        {
            $this->get_parents($parents, $data['parent_id']);
        }
    }

     /**
     * ȡ��ĳ����������������id
     */
    function get_descendant($id)
    {
        $ids = array($id);
        $this->_get_descendant($ids, $id);
        return $ids;
    }
    function _get_descendant(&$ids, $id)
    {
        $childs = $this->find("parent_id = '$id'");
        foreach ($childs as $child)
        {
            $ids[] = $child['cate_id'];
            $this->_get_descendant($ids, $child['cate_id']);
        }
    }
}

?>