<?php

/* ���·��� acategory */
class AcategoryModel extends BaseModel
{
    var $table  = 'acategory';
    var $prikey = 'cate_id';
    var $_name  = 'acategory';
    var $_relation = array(
        // һ�����·����ж�ƪ����
        'has_article' => array(
            'model'         => 'article',
            'type'          => HAS_MANY,
            'foreign_key'   => 'cate_id'
        ),
        // һ�������ж���ӷ���
        'has_acategory' => array(
            'model'         => 'acategory',
            'type'          => HAS_MANY,
            'foreign_key' => 'parent_id',
            'dependent' => true
        ),
    );

    /**
     * ȡ�÷����б�
     *
     * @param int $parent_id ���ڵ���0��ʾȡĳ������¼����࣬С��0��ʾȡ���з���
      * @return array
     */
    function get_list($parent_id = -1)
    {
        $conditions = "1 = 1";
        $parent_id >= 0 && $conditions .= " AND parent_id = '$parent_id'";
        return $this->find(array(
            'conditions' => $conditions,
            'order' => 'sort_order, cate_id',
        ));
    }

        /*
     * �ж������Ƿ�Ψһ
     */
    function unique($cate_name, $parent_id, $cate_id = 0)
    {
        $conditions = "parent_id = '$parent_id' AND cate_name = '$cate_name'";
        $cate_id && $conditions .= " AND cate_id <> '" . $cate_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }

     /*
     * �ж��Ƿ���������¼�����
     */
    function parent_children_valid($parent_id)
    {
        $acategory = $this->get_info($parent_id);
        if($acategory['code'] == ACC_SYSTEM || $acategory['code'] == ACC_NOTICE)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

        /**
     * ��ĳ���༰���ϼ�����ӵ�����ǰ
     */
    function get_parents(&$parents, $id)
    {
        $data = $this->get(intval($id));
        array_unshift($parents, array('cate_id' => $data['cate_id'], 'cate_name' => $data['cate_name'], 'code' => $data['code']));
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
        if (!$this->find("cate_id = '$id'"))
        {
            return false;
        }
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
    function get_ACC($ACC_code = '')
    {
        if ($ACC_code)
        {
            $ACC = $this->get("code = '$ACC_code'");
            return isset($ACC['cate_id'])? $ACC['cate_id'] :false;
        }
        else
        {
            $ACC_code = array(ACC_HELP, ACC_NOTICE, ACC_SYSTEM , ACC_ABOUT ,ACC_ACTIVITY,ACC_CHANNEL, ACC_BRAND,ACC_AGRO,ACC_CREDIT);
            $data = $this->find('code '.db_create_in($ACC_code));
            foreach ($data as $v){
                $ACC[$v['code']] = $v['cate_id'];
            }
            return isset($ACC) ? $ACC :false;
        }
    }
}

?>