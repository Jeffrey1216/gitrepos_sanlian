<?php

/**
 *    ֧����֧����ʽ���
 *
 *    @author    Garbin
 *    @usage    none
 */

class AlipayPayment extends BasePayment
{
    /* ֧�������� */
    var $_gateway   =   'https://www.alipay.com/cooperate/gateway.do';
    var $_code      =   'alipay';

    /**
     *    ��ȡ֧����
     *
     *    @author    Garbin
     *    @param     array $order_info  ��֧���Ķ�����Ϣ����������ܷ��ü�Ψһ�ⲿ���׺�
     *    @return    array
     */
    function get_payform($order_info)
    {
        $service = $this->_config['alipay_service'];
//		ȡ��������id
//      $agent = 'C4335319945672464113';

        $params = array(

            /* ������Ϣ */
//          'agent'             => $agent,
            'service'           => $service,
            'partner'           => $this->_config['alipay_partner'],
            '_input_charset'    => CHARSET,
            'notify_url'        => $this->_create_notify_url($order_info['order_id']),
            'return_url'        => $this->_create_return_url($order_info['order_id']),

            /* ҵ����� */
            'subject'           => $this->_get_subject($order_info),
            //����ID�ɲ���ǩ����֤��һ���֣������п��ܱ��ͻ������޸ģ������ڽ�������֪ͨʱҪ��ָ֤���Ķ���ID���ⲿ���׺��Ƿ������ش�������һ��
            'out_trade_no'      => $this->_get_trade_sn($order_info),
            'price'             => $order_info['order_amount'],   //Ӧ���ܼ�
            'quantity'          => 1,
            'payment_type'      => 1,

            /* �������� */
            'logistics_type'    => 'EXPRESS',
            'logistics_fee'     => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',

            /* ����˫����Ϣ */
            'seller_email'      => $this->_config['alipay_account']
        );

        $params['sign']         =   $this->_get_sign($params);
        $params['sign_type']    =   'MD5';

        return $this->_create_payform('GET', $params);
    }
    
	/**
     *    ��ȡ���ֽ���֧����
     *
     *    @author    Garbin
     *    @param     array $order_info  ��֧���Ķ�����Ϣ����������ܷ��ü�Ψһ�ⲿ���׺�
     *    @return    array
     */
    function get_credit_payform($order_info)
    {
        $service = $this->_config['alipay_service'];
//		ȡ��������id
//      $agent = 'C4335319945672464113';

        $params = array(

            /* ������Ϣ */
//          'agent'             => $agent,
            'service'           => $service,
            'partner'           => $this->_config['alipay_partner'],
            '_input_charset'    => CHARSET,
            'notify_url'        => SITE_URL . "/index.php?app=plb_cart&act=notify&order_id={$order_info['id']}",
            'return_url'        => SITE_URL . "/index.php?app=plb_cart&act=returnurl&order_id={$order_info['id']}",

            /* ҵ����� */
            'subject'           => $this->_get_subject($order_info),
            //����ID�ɲ���ǩ����֤��һ���֣������п��ܱ��ͻ������޸ģ������ڽ�������֪ͨʱҪ��ָ֤���Ķ���ID���ⲿ���׺��Ƿ������ش�������һ��
            'out_trade_no'      => $this->_get_credit_trade_sn($order_info),
            'price'             => $order_info['order_amount'],   //Ӧ���ܼ�
            'quantity'          => 1,
            'payment_type'      => 1,

            /* �������� */
            'logistics_type'    => 'EXPRESS',
            'logistics_fee'     => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',

            /* ����˫����Ϣ */
            'seller_email'      => $this->_config['alipay_account']
        );

        $params['sign']         =   $this->_get_sign($params);
        $params['sign_type']    =   'MD5';

        return $this->_create_payform('GET', $params);
    }
	/**
     *    ��ȡ֧����
     *
     *    @author    Garbin
     *    @param     array $order_info  ��֧���Ķ�����Ϣ����������ܷ��ü�Ψһ�ⲿ���׺�
     *    @return    array
     */
    function get_store_payform($order_info)
    {
        $service = $this->_config['alipay_service'];
//		ȡ��������id
//      $agent = 'C4335319945672464113';

        $params = array(

            /* ������Ϣ */
//          'agent'             => $agent,
            'service'           => $service,
            'partner'           => $this->_config['alipay_partner'],
            '_input_charset'    => CHARSET,
            'notify_url'        => $this->_create_store_notify_url($order_info['order_id']),
            'return_url'        => $this->_create_store_return_url($order_info['order_id']),

            /* ҵ����� */
            'subject'           => $this->_get_subject($order_info),
            //����ID�ɲ���ǩ����֤��һ���֣������п��ܱ��ͻ������޸ģ������ڽ�������֪ͨʱҪ��ָ֤���Ķ���ID���ⲿ���׺��Ƿ������ش�������һ��
            'out_trade_no'      => $this->_get_store_trade_sn($order_info),
            'price'             => $order_info['order_amount'],   //Ӧ���ܼ�
            'quantity'          => 1,
            'payment_type'      => 1,

            /* �������� */
            'logistics_type'    => 'EXPRESS',
            'logistics_fee'     => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',

            /* ����˫����Ϣ */
            'seller_email'      => $this->_config['alipay_account']
        );

        $params['sign']         =   $this->_get_sign($params);
        $params['sign_type']    =   'MD5';

        return $this->_create_payform('GET', $params);
    }
	
	/**
     *    ��ȡ�Ź�֧����
     *
     *    @author    Garbin
     *    @param     array $order_info  ��֧���Ķ�����Ϣ����������ܷ��ü�Ψһ�ⲿ���׺�
     *    @return    array
     */
    function get_group_payform($order_info)
    {
        $service = $this->_config['alipay_service'];
//		ȡ��������id
//      $agent = 'C4335319945672464113';

        $params = array(

            /* ������Ϣ */
//          'agent'             => $agent,
            'service'           => $service,
            'partner'           => $this->_config['alipay_partner'],
            '_input_charset'    => CHARSET,
            'notify_url'        => $this->_create_group_notify_url($order_info['order_id']),
            'return_url'        => $this->_create_group_return_url($order_info['order_id']),

            /* ҵ����� */
            'subject'           => $this->_get_group_subject($order_info),
            //����ID�ɲ���ǩ����֤��һ���֣������п��ܱ��ͻ������޸ģ������ڽ�������֪ͨʱҪ��ָ֤���Ķ���ID���ⲿ���׺��Ƿ������ش�������һ��
            'out_trade_no'      => $this->_get_group_trade_sn($order_info),
            'price'             => $order_info['order_amount'],   //Ӧ���ܼ�
            'quantity'          => 1,
            'payment_type'      => 1,

            /* �������� */
            'logistics_type'    => 'EXPRESS',
            'logistics_fee'     => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',

            /* ����˫����Ϣ */
            'seller_email'      => $this->_config['alipay_account']
        );

        $params['sign']         =   $this->_get_sign($params);
        $params['sign_type']    =   'MD5';

        return $this->_create_payform('GET', $params);
    }
    
    /**
     *    ����֪ͨ���
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @param     bool  $strict
     *    @return    array
     */
    function verify_notify($order_info, $strict = false)
    {
        if (empty($order_info))
        {
            $this->_error('order_info_empty');

            return false;
        }
		
        /* ��ʼ���������� */
        $notify =   $this->_get_notify();
		
        /* ��֤��·�Ƿ���� */
        if ($strict)
        {
            /* �ϸ���֤ */
            $verify_result = $this->_query_notify($notify['notify_id']);
            if(!$verify_result)
            {
                /* ��·������ */
                $this->_error('notify_unauthentic');

                return false;
            }
        }
		
        /* ��֤֪ͨ�Ƿ���� */
        $sign_result = $this->_verify_sign($notify);
        
        if (!$sign_result)
        {
            /* ������ǩ��������ǩ����һ�£�˵��ǩ�������� */
            $this->_error('sign_inconsistent');

            return false;
        }

        /*----------֪ͨ��֤����----------*/

        /*----------������֤��ʼ----------*/
        /* ��֤�뱾����Ϣ�Ƿ�ƥ�� */
        /* ���ﲻֻ�Ǹ���֪ͨ���п����Ƿ���֪ͨ��ȷ���ջ�֪ͨ */
        if ($order_info['out_trade_sn'] != $notify['out_trade_no'])
        {
            /* ֪ͨ�еĶ��������ı�Ķ�����һ�� */
            $this->_error('order_inconsistent');

            return false;
        }
        $order_info['order_amount'] =  $order_info['order_amount'] - $order_info['use_credit']; //���ʵ����Ҫ֧�����ֽ�
        if ($order_info['order_amount'] != $notify['total_fee'])
        {
            /* ֧���Ľ����ʵ�ʽ�һ�� */
            $this->_error('price_inconsistent');

            return false;
        }
        //���ˣ�˵��֪ͨ�ǿ��ŵģ�����Ҳ�Ƕ�Ӧ�ģ����ŵ�

        /* ��֪ͨ���������Ӧ�Ľ�� */
        switch ($notify['trade_status'])
        {
            case 'WAIT_SELLER_SEND_GOODS':      //����Ѹ���ȴ����ҷ���

                $order_status = ORDER_ACCEPTED;
            break;

            case 'WAIT_BUYER_CONFIRM_GOODS':    //�����ѷ������ȴ����ȷ��

                $order_status = ORDER_SHIPPED;
            break;
			
            case 'TRADE_SUCCESS':               //���׳ɹ�----AddBy(lihuoliang)
                if ($order_info['status'] == ORDER_PENDING)
                {
                    /* ����ǵȴ������У���˵���Ǽ�ʱ���˽��ף���ʱ��״̬��Ϊ�Ѹ��� */
                    $order_status = ORDER_ACCEPTED;
                }
                else
                {
                    /* ˵���ǵ������������ף����׽��� */
                    $order_status = ORDER_FINISHED;
                }
            break;
            
            case 'TRADE_FINISHED':              //���׽���
                if ($order_info['status'] == ORDER_PENDING)
                {
                    /* ����ǵȴ������У���˵���Ǽ�ʱ���˽��ף���ʱ��״̬��Ϊ�Ѹ��� */
                    $order_status = ORDER_ACCEPTED;
                }
                else
                {
                    /* ˵���ǵ������������ף����׽��� */
                    $order_status = ORDER_FINISHED;
                }
            break;
            case 'TRADE_CLOSED':                //���׹ر�
                $order_status = ORDER_CANCLED;
            break;

            default:
                $this->_error('undefined_status');
                return false;
            break;
        }

        switch ($notify['refund_status'])
        {
            case 'REFUND_SUCCESS':              //�˿�ɹ���ȡ������
                $order_status = ORDER_CANCLED;
            break;
        }

        return array(
            'target'    =>  $order_status,
        );
    }

    /**
     *    ��ѯ֪ͨ�Ƿ���Ч
     *
     *    @author    Garbin
     *    @param     string $notify_id
     *    @return    string
     */
    function _query_notify($notify_id)
    {
        $query_url = "http://notify.alipay.com/trade/notify_query.do?partner={$this->_config['alipay_partner']}&notify_id={$notify_id}";

        return (ecm_fopen($query_url, 60) === 'true');
    }

    /**
     *    ��ȡǩ���ַ���
     *
     *    @author    Garbin
     *    @param     array $params
     *    @return    string
     */
    function _get_sign($params)
    {
        /* ȥ��������ǩ�������� */
        unset($params['sign'], $params['sign_type'], $params['order_id'], $params['app'], $params['act']);

        /* ���� */
        ksort($params);
        reset($params);

        $sign  = '';
        foreach ($params AS $key => $value)
        {
            $sign  .= "{$key}={$value}&";
        }

        return md5(substr($sign, 0, -1) . $this->_config['alipay_key']);
    }

    /**
     *    ��֤ǩ���Ƿ����
     *
     *    @author    Garbin
     *    @param     array $notify
     *    @return    bool
     */
    function _verify_sign($notify)
    {
        $local_sign = $this->_get_sign($notify);

        return ($local_sign == $notify['sign']);
    }
    
	function get_recharge_payform($order_info)
    {
        $service = $this->_config['alipay_service'];
//		ȡ��������id
//      $agent = 'C4335319945672464113';

        $params = array(

            /* ������Ϣ */
//          'agent'             => $agent,
            'service'           => $service,
            'partner'           => $this->_config['alipay_partner'],
            '_input_charset'    => CHARSET,
            'notify_url'        => SITE_URL . "/index.php?app=shopadmin&act=notify&order_id=" . $order_info['order_id'],
            'return_url'        => SITE_URL . "/index.php?app=shopadmin&act=return_info&order_id=" . $order_info['order_id'],
        	
        	/* ҵ����� */
            'subject'           => $this->_get_recharge_subject($order_info),
         	//����ID�ɲ���ǩ����֤��һ���֣������п��ܱ��ͻ������޸ģ������ڽ�������֪ͨʱҪ��ָ֤���Ķ���ID���ⲿ���׺��Ƿ������ش�������һ��
            'out_trade_no'      => $this->_get_recharge_trade_sn($order_info),
            'price'             => $order_info['balance'],   //Ӧ���ܼ�
            'quantity'          => 1,
            'payment_type'      => 1,

            /* �������� */
            'logistics_type'    => 'EXPRESS',
            'logistics_fee'     => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',

            /* ����˫����Ϣ */
            'seller_email'      => $this->_config['alipay_account'],
        );

        $params['sign']         =   $this->_get_sign($params);
        $params['sign_type']    =   'MD5';

        return $this->_create_payform('GET', $params);
    }
    
	/**
     *    ����֪ͨ���
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @param     bool  $strict
     *    @return    array
     */
    function verify_recharge_notify($order_info, $strict = false)
    {
        if (empty($order_info))
        {
            $this->_error('order_info_empty');

            return false;
        }
		
        /* ��ʼ���������� */
        $notify =   $this->_get_notify();
		
        /* ��֤��·�Ƿ���� */
        if ($strict)
        {
            /* �ϸ���֤ */
            $verify_result = $this->_query_notify($notify['notify_id']);
            if(!$verify_result)
            {
                /* ��·������ */
                $this->_error('notify_unauthentic');

                return false;
            }
        }
		
        /* ��֤֪ͨ�Ƿ���� */
        $sign_result = $this->_verify_sign($notify);
        
        if (!$sign_result)
        {
            /* ������ǩ��������ǩ����һ�£�˵��ǩ�������� */
            $this->_error('sign_inconsistent');

            return false;
        }

        /*----------֪ͨ��֤����----------*/

        /*----------������֤��ʼ----------*/
        /* ��֤�뱾����Ϣ�Ƿ�ƥ�� */
        /* ���ﲻֻ�Ǹ���֪ͨ���п����Ƿ���֪ͨ��ȷ���ջ�֪ͨ */
        if ($order_info['out_trade_sn'] != $notify['out_trade_no'])
        {
            /* ֪ͨ�еĶ��������ı�Ķ�����һ�� */
            $this->_error('order_inconsistent');

            return false;
        }

        if ($order_info['balance'] != $notify['total_fee'])
        {
            /* ֧���Ľ����ʵ�ʽ�һ�� */
            $this->_error('price_inconsistent');

            return false;
        }
        //���ˣ�˵��֪ͨ�ǿ��ŵģ�����Ҳ�Ƕ�Ӧ�ģ����ŵ�

        /* ��֪ͨ���������Ӧ�Ľ�� */
        switch ($notify['trade_status'])
        {
            case 'WAIT_SELLER_SEND_GOODS':      //����Ѹ���ȴ����ҷ���

                $order_status = ORDER_ACCEPTED;
            break;

            case 'WAIT_BUYER_CONFIRM_GOODS':    //�����ѷ������ȴ����ȷ��

                $order_status = ORDER_SHIPPED;
            break;
			
            case 'TRADE_SUCCESS':               //���׳ɹ�----AddBy(lihuoliang)
                if ($order_info['status'] == ORDER_PENDING)
                {
                    /* ����ǵȴ������У���˵���Ǽ�ʱ���˽��ף���ʱ��״̬��Ϊ�Ѹ��� */
                    $order_status = ORDER_ACCEPTED;
                }
                else
                {
                    /* ˵���ǵ������������ף����׽��� */
                    $order_status = ORDER_FINISHED;
                }
            break;
            
            case 'TRADE_FINISHED':              //���׽���
                if ($order_info['status'] == ORDER_PENDING)
                {
                    /* ����ǵȴ������У���˵���Ǽ�ʱ���˽��ף���ʱ��״̬��Ϊ�Ѹ��� */
                    $order_status = ORDER_ACCEPTED;
                }
                else
                {
                    /* ˵���ǵ������������ף����׽��� */
                    $order_status = ORDER_FINISHED;
                }
            break;
            case 'TRADE_CLOSED':                //���׹ر�
                $order_status = ORDER_CANCLED;
            break;

            default:
                $this->_error('undefined_status');
                return false;
            break;
        }

        switch ($notify['refund_status'])
        {
            case 'REFUND_SUCCESS':              //�˿�ɹ���ȡ������
                $order_status = ORDER_CANCLED;
            break;
        }

        return array(
            'target'    =>  $order_status,
        );
    }
}

?>