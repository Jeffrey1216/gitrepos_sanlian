<?php

/* 手机区域表 mobile */
class MobileModel extends BaseModel
{
    var $table  = 'mobile';
    var $prikey = 'mobile';
    var $_name  = 'mobile';
    
    /**
     *    返回手机号对应的地区
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