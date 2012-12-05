<?php

/**
 *    ֧������֪ͨ�ӿ�
 *
 *    @author    Garbin
 *    @usage    none
 */
class Store_paynotifyApp extends MallbaseApp
{
    /**
     *    ֧����ɺ󷵻ص�URL���ڴ�ֻ������ʾ�����Զ��������κ��޸Ĳ���,���ﲻ�ϸ���֤�����ı䶩��״̬
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        //������֧�������Ƹ�ͨ�ȵ�����״̬�ı�ʱ��֪ͨ��ַ
        $order_id   = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0; //�ĸ�����
        if (!$order_id)
        {
            /* ��Ч��֪ͨ���� */
            $this->show_warning('forbidden');

            return;
        }

        /* ��ȡ������Ϣ */
        $model_order =& m('storeorder');
        $order_info  = $model_order->get($order_id);
        if (empty($order_info))
        {
            /* û�иö��� */
            $this->show_warning('forbidden');

            return;
        }
		
        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("payment_code='{$order_info['payment_code']}' AND store_id=0");
        if (empty($payment_info))
        {
            /* û��ָ����֧����ʽ */
            $this->show_warning('no_such_payment');

            return;
        }

        /* ������Ӧ��֧����ʽ */
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);

        /* ��ȡ��֤��� */
        $notify_result = $payment->verify_notify($order_info);
        
        
        if ($notify_result === false)
        {
            /* ֧��ʧ�� */
            $this->show_warning($payment->get_error());

            return;
        }
		
        
        #TODO ��ʱ�ڴ�Ҳ�ı䶩��״̬Ϊ������ԣ�ʵ�ʷ���ʱӦ�Ѵ˶�ȥ��������״̬�ĸı���notifyΪ׼
        $this->_change_order_status($order_id, $order_info['extension'], $notify_result);

        /* ֻ��֧��ʱ��ʹ�õ�return_url������������ʾ����Ϣ��֧���ɹ�����ʾ��Ϣ */
        $this->_curlocal(LANG::get('pay_successed'));
        $this->assign('order', $order_info);
        $this->assign('payment', $payment_info);
        $this->display('paynotify.index.html');
    }

    /**
     *    ֧����ɺ��ⲿ���ص�֪ͨ��ַ���ڴ˻���ж���״̬�ĸı䣬�����ϸ���֤���ı䶩��״̬
     *
     *    @author    Garbin
     *    @return    void
     */
    function notify()
    {
        //������֧�������Ƹ�ͨ�ȵ�����״̬�ı�ʱ��֪ͨ��ַ
        $order_id   = 0;
        if(isset($_POST['order_id']))
        {
            $order_id = intval($_POST['order_id']);
        }
        else
        {
            $order_id = intval($_GET['order_id']);
        }
        if (!$order_id)
        {
            /* ��Ч��֪ͨ���� */
            $this->show_warning('no_such_order');
            return;
        }

        /* ��ȡ������Ϣ */
        $model_order =& m('storeorder');
        $order_info  = $model_order->get($order_id);
        if (empty($order_info))
        {
            /* û�иö��� */
            $this->show_warning('no_such_order');
            return;
        }

        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("payment_code='{$order_info['payment_code']}' AND store_id=0");
        if (empty($payment_info))
        {
            /* û��ָ����֧����ʽ */
            $this->show_warning('no_such_payment');
            return;
        }

        /* ������Ӧ��֧����ʽ */
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);

        /* ��ȡ��֤��� */
        $notify_result = $payment->verify_notify($order_info, true);
        if ($notify_result === false)
        {
            /* ֧��ʧ�� */
            $payment->verify_result(false);
            return;
        }
        
    	
        //�ı䶩��״̬
        $this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        $payment->verify_result(true);
        
        if ($notify_result['target'] == ORDER_ACCEPTED)
        {
            /* �����ʼ������ң����Ѹ���ɹ� */
            $model_member =& m('member');
            $seller_info  = $model_member->get($order_info['seller_id']);

            $mail = get_mail('toseller_online_pay_success_notify', array('order' => $order_info));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            /* �첽���� */
            $this->_sendmail(false);
        }
    }

    /**
     *    �ı䶩��״̬
     *
     *    @author    Garbin
     *    @param     int $order_id
     *    @param     string $order_type
     *    @param     array  $notify_result
     *    @return    void
     */
    function _change_order_status($order_id, $order_type, $notify_result)
    {
        /* ����֤������ݸ��������ʹ��� */
        $order_type  =& ot($order_type);
        $order_type->respond_store_notify($order_id, $notify_result);    //��Ӧ֪ͨ
    }
}

?>
