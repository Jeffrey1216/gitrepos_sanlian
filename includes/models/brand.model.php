<?php

/* Ʒ�� brand */
class BrandModel extends BaseModel
{
    var $table  = 'brand';
    var $prikey = 'brand_id';
    var $_name  = 'brand';

    /* ��ӱ༭ʱ�Զ���֤ */
    var $_autov = array(
        'brand_name' => array(
            'required'  => true,    //����
            'min'       => 1,       //���1���ַ�
            'max'       => 100,     //�100���ַ�
            'filter'    => 'trim',
        ),
        'sort_order'  => array(
            'filter'    => 'intval',
        )
    );

    /**
     *    ɾ����ƷƷ��
     *
     *    @author    Hyber
     *    @param     string $conditions
     *    @param     string $fields
     *    @return    void
     */
    function drop($conditions, $fields = 'brand_logo')
    {
        $droped_rows = parent::drop($conditions, $fields);
        if ($droped_rows)
        {
            restore_error_handler();
            $droped_data = $this->getDroppedData();
            foreach ($droped_data as $key => $value)
            {
                if ($value['brand_logo'])
                {
                    @unlink(ROOT_PATH . '/' . $value['brand_logo']);  //ɾ��Logo�ļ�
                }
            }
            reset_error_handler();
        }

        return $droped_rows;
    }

        /*
     * �ж������Ƿ�Ψһ
     */
    function unique($brand_name, $brand_id = 0)
    {
        $conditions = "brand_name = '" . $brand_name . "' AND brand_id != ".$brand_id."";
        //dump($conditions);
        return count($this->find(array('conditions' => $conditions))) == 0;
    }
    
    /* ����ǩ����ȡ�����е�Ʒ��   */
    
    function getAllBrands()
    {
        $sql = "SELECT group_concat(brand_id) as brand_ids,COUNT(*) as count,tag FROM {$this->table} WHERE if_show = 1 GROUP BY tag ORDER BY count DESC";
        return $this->db->getAll($sql);
    }
    /* ȡ�����е�Ʒ��   */
    
    function getBrands()
    {
        $sql = "SELECT brand_id,brand_name FROM {$this->table} WHERE if_show = 1";
        return $this->db->getAll($sql);
    }
}

?>