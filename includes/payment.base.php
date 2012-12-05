<?php

!defined('ROOT_PATH') && exit('Forbidden');

/**
 *    ֧����ʽ������
 *
 *    @author    Garbin
 *    @usage    none
 */
class BasePayment extends Object
{
    /* �ⲿ�������� */
    var $_gateway   = '';
    /* ֧����ʽΨһ��ʶ */
    var $_code      = '';


    function __construct($payment_info = array())
    {
        $this->BasePayment($payment_info);
    }
    function BasePayment($payment_info = array())
    {
        $this->_info   = $payment_info;
        $this->_config = unserialize($payment_info['config']);
    }

    /**
     *    ��ȡ֧����
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @return    array
     */
    function get_payform()
    {
        return $this->_create_payform('POST');
    }
    
	/**
     *    ��ȡ֧����
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @return    array
     */
    function get_store_payform()
    {
        return $this->_create_payform('POST');
    }

    /**
     *    ��ȡ�淶��֧��������
     *
     *    @author    Garbin
     *    @param     string $method
     *    @param     array  $params
     *    @return    void
     */
    function _create_payform($method = '', $params = array())
    {
        return array(
            'online'    =>  $this->_info['is_online'],
            'desc'      =>  $this->_info['payment_desc'],
            'method'    =>  $method,
            'gateway'   =>  $this->_gateway,
            'params'    =>  $params,
        );
    }

    /**
     *    ��ȡ֪ͨ��ַ
     *
     *    @author    Garbin
     *    @param     int $store_id
     *    @param     int $order_id
     *    @return    string
     */
    function _create_notify_url($order_id)
    {
        return SITE_URL . "/index.php?app=paynotify&act=notify&order_id={$order_id}";
    }
	/**
     *    ��ȡ����֧��֪ͨ��ַ
     *
     *    @author    Garbin
     *    @param     int $store_id
     *    @param     int $order_id
     *    @return    string
     */
    function _create_store_notify_url($order_id)
    {
        return SITE_URL . "/index.php?app=store_paynotify&act=notify&order_id={$order_id}";
    }
    
	/**
     *    ��ȡ�Ź�����ɱ֪ͨ��ַ
     *
     *    @author    Garbin
     *    @param     int $store_id
     *    @param     int $order_id
     *    @return    string
     */
    function _create_group_notify_url($order_id)
    {
        return SITE_URL . "/index.php?app=group_paynotify&act=notify&order_id={$order_id}";
    }

    /**
     *    ��ȡ���ص�ַ
     *
     *    @author    Garbin
     *    @param     int $store_id
     *    @param     int $order_id
     *    @return    string
     */
    function _create_return_url($order_id)
    {
        return SITE_URL . "/index.php?app=paynotify&order_id={$order_id}";
    }
	/**
     *    ��ȡ����֧�����ص�ַ
     *
     *    @author    Garbin
     *    @param     int $store_id
     *    @param     int $order_id
     *    @return    string
     */
    function _create_store_return_url($order_id)
    {
        return SITE_URL . "/index.php?app=store_paynotify&order_id={$order_id}";
    }
    
	/**
     *    ��ȡ�Ź�֧�����ص�ַ
     *
     *    @author    Garbin
     *    @param     int $store_id
     *    @param     int $order_id
     *    @return    string
     */
    function _create_group_return_url($order_id)
    {
        return SITE_URL . "/index.php?app=group_paynotify&order_id={$order_id}";
    }

    /**
     *    ��ȡ�ⲿ���׺�
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @return    string
     */
    function _get_trade_sn($order_info)
    {
        $out_trade_sn = $order_info['out_trade_sn'];
        if (!$out_trade_sn)
        {
            $out_trade_sn = $this->_config['pcode'] . $order_info['order_sn'];

            /* ��������д�붩���� */
            $model_order =& m('order');
            $model_order->edit(intval($order_info['order_id']), array('out_trade_sn' => $out_trade_sn));
        }

        return $out_trade_sn;
    }
    
	/**
     *    ��ȡ�������ⲿ���׺�
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @return    string
     */
    function _get_credit_trade_sn($order_info)
    {
        $out_trade_sn = $order_info['out_trade_sn'];
        if (!$out_trade_sn)
        {
            $out_trade_sn = $this->_config['pcode'] . $order_info['order_sn'];

            /* ��������д�붩���� */
            $model_order =& m('creditorder');
            $model_order->edit(intval($order_info['id']), array('out_trade_sn' => $out_trade_sn));
        }

        return $out_trade_sn;
    }
    
	/**
     *    ��ȡ��ֵ�ⲿ���׺�
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @return    string
     */
    function _get_recharge_trade_sn($order_info)
    {
        $out_trade_sn = $order_info['out_trade_sn'];
        if (!$out_trade_sn)
        {
            $out_trade_sn = $this->_config['pcode'] . $order_info['order_sn'];

            /* ��������д�붩���� */
            $model_order =& m('rechargeorder');
            $model_order->edit(intval($order_info['order_id']), array('out_trade_sn' => $out_trade_sn));
        }

        return $out_trade_sn;
    }
    
	/**
     *    ��ȡ�Ź��ⲿ���׺�
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @return    string
     */
    function _get_group_trade_sn($order_info)
    {
        $out_trade_sn = $order_info['out_trade_sn'];
        if (!$out_trade_sn)
        {
            $out_trade_sn = $this->_config['pcode'] . $order_info['order_sn'];

            /* ��������д�붩���� */
            $model_order =& m('grouporder');
            $model_order->edit(intval($order_info['order_id']), array('out_trade_sn' => $out_trade_sn));
        }

        return $out_trade_sn;
    }

	/**
     *    ��ȡ�ⲿ���׺�
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @return    string
     */
    function _get_store_trade_sn($order_info)
    {
        $out_trade_sn = $order_info['out_trade_sn'];
        if (!$out_trade_sn)
        {
            $out_trade_sn = $this->_config['pcode'] . $order_info['order_sn'];

            /* ��������д�붩���� */
            $model_order =& m('storeorder');
            $model_order->edit(intval($order_info['order_id']), array('out_trade_sn' => $out_trade_sn));
        }

        return $out_trade_sn;
    }
    /**
     *    ��ȡ��Ʒ���
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @return    string
     */
    function _get_subject($order_info)
    {
        return 'PaiLa Order:' . $order_info['order_sn'];
    }
    
    function _get_recharge_subject($order_info)
    {
    	return 'PaiLa Recharge Order:' . $order_info['order_sn'];
    }
    
	/**
     *    ��ȡ��Ʒ���
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @return    string
     */
    function _get_group_subject($order_info)
    {
        return 'PaiLa GroupBuy Order:' . $order_info['order_sn'];
    }

    /**
     *    ��ȡ֪ͨ��Ϣ
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_notify()
    {
        /* �����POST�����ݣ�����ΪPOST��������֪ͨ���� */
        if (!empty($_POST))
        {
            return $_POST;
        }

        /* �������Ϊ��GET�� */
        return $_GET;
    }

    /**
     *    ��֤֧�����
     *
     *    @author    Garbin
     *    @return    void
     */
    function verify_notify()
    {
        #TODO
    }

    /**
     *    ����֤�������������
     *
     *    @author    Garbin
     *    @param     bool   $result
     *    @return    void
     */
    function verify_result($result)
    {
        if ($result)
        {
            echo 'success';
        }
        else
        {
            echo 'fail';
        }
    }
}

?>