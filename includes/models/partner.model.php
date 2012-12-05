<?php

/* �������� partner */
class PartnerModel extends BaseModel
{
    var $table  = 'partner';
    var $prikey = 'partner_id';
    var $_name  = 'partner';

    /* ��ӱ༭ʱ�Զ���֤ */
    var $_autov = array(
        'title' => array(
            'required'  => true,    //����
            'min'       => 1,       //���1���ַ�
            'max'       => 100,     //�100���ַ�
            'filter'    => 'trim',
        ),
        'link'  => array(
            'required'  => true,    //����
            'filter'    => 'trim',
        ),
        'sort_order'    => array(
            'filter'    => 'intval',//����
        ),
    );
    var $_relation = array(
        // һ����������ֻ�ܱ�һ������ӵ��
        'belongs_to_store' => array(
            'model'       => 'store',
            'type'        => BELONGS_TO,
            'foreign_key' => 'store_id',
            'reverse'     => 'has_partner',
        ),
    );

    /**
     *    ɾ����������
     *
     *    @author    Garbin
     *    @param     string $conditions
     *    @param     string $fields
     *    @return    void
     */
    function drop($conditions, $fields = 'logo')
    {
        $droped_rows = parent::drop($conditions, $fields);
        if ($droped_rows)
        {
            restore_error_handler();
            $droped_data = $this->getDroppedData();
            foreach ($droped_data as $key => $value)
            {
                if ($value['logo'])
                {
                    @unlink(ROOT_PATH . '/' . $value['logo']);  //ɾ��Logo�ļ�
                }
            }
            reset_error_handler();
        }

        return $droped_rows;
    }
}

?>