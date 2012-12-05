<?php

/**
 * ��
 *
 * 0�Ǹ����
 */
class Tree extends Object
{
    var $data   = array();
    var $child  = array(-1 => array());
    var $layer  = array(0 => 0);
    var $parent = array();
    var $is_index = '';
    var $value_field = '';
    public $nav_img = ''; //��¼ͼƬ��ַ
    public $nodes;
    /**
     * ���캯��
     *
     * @param mix $value
     */
    function construct($value = 'root')
    {
        $this->Tree($value);
    }

    function Tree($value = 'root')
    {
        $this->setNode(0, -1, $value );
    }

    /**
     * ������
     *
     * @param array $nodes �������
     * @param string $id_field
     * @param string $parent_field
     * @param string $value_field
     */
    function setTree($nodes, $id_field, $parent_field, $value_field ,$img_field = 'nav_img', $is_index = NULL)
    {
        $this->value_field = $value_field;
        $this->nav_img = $img_field;
        $this->is_index = empty($is_index) ? false : $is_index;
        $this->nodes = $nodes;
        
        foreach ($nodes as $node)
        {
            $this->setNode($node[$id_field], $node[$parent_field], $node );
        }
        
        $this->setLayer();
    }

    /**
     * ȡ��options
     *
     * @param int $layer
     * @param int $root
     * @param string $space
     * @return array (id=>value)
     */
    function getOptions($layer = 0, $root = 0, $except = NULL, $space = '&nbsp;&nbsp;')
    {
        $options = array();
        $childs = $this->getChilds($root, $except);
        foreach ($childs as $id)
        {
            if ($id > 0 && ($layer <= 0 || $this->getLayer($id) <= $layer))
            {
                $options[$id] = $this->getLayer($id, $space) . htmlspecialchars($this->getValue($id));
            }
        }
        return $options;
    }

    /**
     * ���ý��
     *
     * @param mix $id
     * @param mix $parent
     * @param mix $value
     */
    function setNode($id, $parent, $value )
    {
        $parent = $parent ? $parent : 0;
        $this->data[$id] = $value;
        if (!isset($this->child[$id]))
        {
            $this->child[$id] = array();
        }

        if (isset($this->child[$parent]))
        {
            $this->child[$parent][] = $id;
        }
        else
        {
            $this->child[$parent] = array($id);
        }
		
        $this->parent[$id] = $parent;
    }

    /**
     * ����layer
     */
    function setLayer($root = 0)
    {
        foreach ($this->child[$root] as $id)
        {
           	$this->layer[$id] = $this->layer[$this->parent[$id]] + 1;
           	if ($this->child[$id])
           	{ 
           		$this->setLayer($id);
           	}     	
        }
    }

    /**
     * �ȸ�������������root
     *
     * @param array $tree
     * @param mix $root
     * @param mix $except ����Ľ�㣬���ڱ༭���ʱ���ϼ�����ѡ�������ӽ��
     */
    function getList(&$tree, $root = 0, $except = NULL)
    {
        foreach ($this->child[$root] as $id)
        {
            if ($id == $except)
            {
                continue;
            }

            $tree[] = $id;

            if ($this->child[$id]) $this->getList($tree, $id, $except);
        }
    }

    function getValue($id)
    {
        return $this->data[$id][$this->value_field];
    }
    
    public function getIcon($id)
    {
    	return $this->data[$id]['icon'];
    }
     
    private function getIsIndex($id)
    {
    	return $this->data[$id][$this->is_index];
    }
   
    function getNavImg($id) {
    	return $this->data[$id][$this->nav_img];
    }

    function getLayer($id, $space = false)
    {
        return $space ? str_repeat($space, $this->layer[$id]) : $this->layer[$id];
    }

    function getParent($id)
    {
        return $this->parent[$id];
    }

    /**
     * ȡ�����ȣ�����������
     *
     * @param mix $id
     * @return array
     */
    function getParents($id)
    {
        while ($this->parent[$id] != -1)
        {
            $id = $parent[$this->layer[$id]] = $this->parent[$id];
        }

        ksort($parent);
        reset($parent);

        return $parent;
    }

    function getChild($id)
    {
        return $this->child[$id];
    }

    /**
     * ȡ��������������ȸ�����
     *
     * @param int $id
     * @return array
     */
    function getChilds($id = 0, $except = NULL)
    {
        $child = array($id);
        $this->getList($child, $id, $except);
        unset($child[0]);

        return $child;
    }

    /**
     * �ȸ������������ʽ
     * array(
     *     array('id' => '', 'value' => '', children => array(
     *         array('id' => '', 'value' => '', children => array()),
     *     ))
     * )
     */
    function getArrayList($root = 0 , $layer = NULL)
    {
        $data = array();
        foreach ($this->child[$root] as $id)
        {
            if($layer && $this->layer[$this->parent[$id]] > $layer-1)
            {
                continue;
            }
            if (!$this->is_index) {
            	$data[] = array('id' => $id, 'value' => $this->getValue($id),'children' => $this->child[$id] ? $this->getArrayList($id , $layer) : array());
            } else {
            	
            	$data[] = array('id' => $id, 'value' => $this->getValue($id),'is_index' => $this->getIsIndex($id) ,'children' => $this->child[$id] ? $this->getArrayList($id , $layer) : array());
            }
            
        }
        return $data;
    }
    
	/**
     * �ȸ������������ʽ,  ��ǿ����
     * array(
     *     array('id' => '', 'value' => '', children => array(
     *         array('id' => '', 'value' => '', children => array()),
     *     ))
     * )
     */
    function getArrayListNav($root = 0 , $layer = NULL, $mall_type = 1)
    {	
    	$data = array();
        foreach ($this->child[$root] as $id)
        {
            if($layer && $this->layer[$this->parent[$id]] > $layer-1)
            {
                continue;
            }
            $data[] = array('id' => $id, 'value' => $this->getValue($id), 'icon' => $this->getIcon($id, $layer),'children' => $this->child[$id] ? $this->getArrayList($id , $layer) : array());
        }
        return $data;
    }

    /**
     * ȡ��csv��ʽ����
     *
     * @param int $root
     * @param mix $ext_field �����ֶ�
     * @return array(
     *      array('�����ֶ���','���ֶ���'), //���޸����ֶ����޴�Ԫ��
     *      array('�����ֶ�ֵ','һ������'), //���޸����ֶ����޸����ֶ�ֵ
     *      array('�����ֶ�ֵ','һ������'),
     *      array('�����ֶ�ֵ','', '��������'),
     *      array('�����ֶ�ֵ','', '', '��������'),
     * )
     */
    function getCSVData($root = 0, $ext_field = array())
    {
        $data = array();
        $main = $this->value_field; //������ʾ���ּ�������ֶ�
        $extra =array(); //�������ֶ�
        if (!empty($ext_field))
        {
            if (is_array($ext_field))
            {
                $extra = $ext_field;
            }
            elseif (is_string($ext_field))
            {
                $extra = array($ext_field);
            }
        }
        $childs = $this->getChilds($root);
        array_values($extra) && $data[0] = array_values($extra);
        $main && $data[0] && array_push($data[0], $main);
        foreach ($childs as $id)
        {
            $row = array();
            $value = $this->data[$id];
            foreach ($extra as $field)
            {
                $row[] = $value[$field];
            }
            for ($i = 1; $i < $this->getLayer($id); $i++)
            {
                $row[] = '';
            }
            if ($main)
            {
                $row[] = $value[$main];
            }
            else
            {
                $row[] = $value;
            }
            $data[] = $row;
        }
        return $data;

    }
}

?>