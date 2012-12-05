<?php

/**
 *    �ʼ�����ģ��
 *
 *    @author    Garbin
 *    @usage    none
 */
class MailqueueModel extends BaseModel
{
    var $table  = 'mail_queue';
    var $prikey = 'queue_id';
    var $_name  = 'mailqueue';

    /**
     *    �������N�δ���͹��ڵ��ʼ�
     *
     *    @author    Garbin
     *    @return    void
     */
    function clear()
    {
        return $this->drop("err_num > 3 OR add_time < " . (gmtime() - 259200));
    }

    /**
     *    �����ʼ�
     *
     *    @author    Garbin
     *    @param     int $limit
     *    @return    void
     */
    function send($limit = 5)
    {
        /* ������ܷ��͵��ʼ� */
        $this->clear();

        /* ��ȡ�����͵��ʼ���������ʱ�䣬���ȼ����򣬴���������� */
        $gmtime = gmtime();

        /* ȡ������δ������ */
        $mails  = $this->find(array(
            'conditions'    =>  "lock_expiry < {$gmtime}",
            'order'         =>  'add_time DESC, priority DESC, err_num ASC',
            'limit'         =>  "0, {$limit}",
        ));
        if (!$mails)
        {
            /* û���ʼ�������Ҫ���� */
            return 0;
        }

        /* �����������ʼ� */
        $queue_ids = array_keys($mails);
        $lock_expiry = $gmtime + 30;    //����30��
        $this->edit($queue_ids, "err_num = err_num + 1, lock_expiry = {$lock_expiry}");

        /* ��ȡ�ʼ����ͽӿ� */
        $mailer =& get_mailer();
        $mail_count = count($queue_ids);
        $error_count= 0;
        $error      = '';

        /* �������� */
        for ($i = 0; $i < $mail_count; $i++)
        {
            $mail = $mails[$queue_ids[$i]];
            $result = $mailer->send($mail['mail_to'], $mail['mail_subject'], $mail['mail_body'], $mail['mail_encoding'], 1);
            if ($result)
            {
                /* ���ͳɹ����Ӷ�����ɾ�� */
                $this->drop($queue_ids[$i]);
            }
            else
            {
                $error_count++;
            }
        }

        return array('mail_count' => $mail_count, 'error_count' => $error_count, 'error' => $mailer->errors);

    }
}

?>