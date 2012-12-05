<?php

/* �ֻ������ mobile */
class MobileModel extends BaseModel
{
    var $table  = 'mobile';
    var $prikey = 'mobile';
    var $_name  = 'mobile';
    
    /**
     *    �����ֻ��Ŷ�Ӧ�ĵ���
     *
     *    @author    lihuoliang
     *    @return    string $areaname
     */
    function get_areaname_by_mobile($mobile)
    {
        $mobilepre = substr($mobile,0,7);
        $info = $this->get(array('conditions' =>'mobile='.$mobilepre,'fields'=>'areaname'));
        return $info['areaname'];
    }
}
?>