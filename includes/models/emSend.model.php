<?php
class SmslogModel extends BaseModel
{
	 var $table  = 'sms_log';
     var $prikey = 'log_id';
     var $_name  = 'smslog';
    /*
     *    ��ȡ������ֻ�������ŷ��͵�������
     *
     *    @author    lihuoliang
     *    @return    int $smscount
     */
    function get_today_smscount($mobile)
    {
        $times = time();
        $starttime = mktime(0, 0, 0, date('m', $times), date('d', $times), date('Y', $times));	//��ȡ�������ʼʱ��
        $endtime   = mktime(23, 59,59, date('m', $times), date('d', $times), date('Y', $times));//��ȡ����Ľ���ʱ��
        $smscount = $this->db->getOne("SELECT COUNT(log_id) as c FROM {$this->table} WHERE mobile={$mobile} 
        							   AND type='register_verify' AND sendtime>={$starttime} AND sendtime<={$endtime}
        							  ");

        return $smscount;
    }
}
?>