<?php
class SmslogModel extends BaseModel
{
	 var $table  = 'sms_log';
     var $prikey = 'log_id';
     var $_name  = 'smslog';
    /*
     *    获取当天该手机号码短信发送的总条数
     *
     *    @author    lihuoliang
     *    @return    int $smscount
     */
    function get_today_smscount($mobile)
    {
        $times = time();
        $starttime = mktime(0, 0, 0, date('m', $times), date('d', $times), date('Y', $times));	//获取当天的起始时间
        $endtime   = mktime(23, 59,59, date('m', $times), date('d', $times), date('Y', $times));//获取当天的结束时间
        $smscount = $this->db->getOne("SELECT COUNT(log_id) as c FROM {$this->table} WHERE mobile={$mobile} 
        							   AND type='register_verify' AND sendtime>={$starttime} AND sendtime<={$endtime}
        							  ");

        return $smscount;
    }
}
?>