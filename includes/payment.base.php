<?php

!defined('ROOT_PATH') && exit('Forbidden');

/**
 *    支付方式基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class BasePayment extends Object
{
    /* 外部处理网关 */
    var $_gateway   = '';
    /* 支付方式唯一标识 */
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
     *    获取支付表单
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
     *    获取支付表单
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
     *    获取规范的支付表单数据
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
     *    获取通知地址
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
     *    获取商铺支付通知地址
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
     *    获取团购和秒杀通知地址
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
     *    获取返回地址
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
     *    获取商铺支付返回地址
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
     *    获取团购支付返回地址
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
     *    获取外部交易号
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

            /* 将此数据写入订单中 */
            $model_order =& m('order');
            $model_order->edit(intval($order_info['order_id']), array('out_trade_sn' => $out_trade_sn));
        }

        return $out_trade_sn;
    }
    
	/**
     *    获取派啦币外部交易号
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

            /* 将此数据写入订单中 */
            $model_order =& m('creditorder');
            $model_order->edit(intval($order_info['id']), array('out_trade_sn' => $out_trade_sn));
        }

        return $out_trade_sn;
    }
    
	/**
     *    获取充值外部交易号
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

            /* 将此数据写入订单中 */
            $model_order =& m('rechargeorder');
            $model_order->edit(intval($order_info['order_id']), array('out_trade_sn' => $out_trade_sn));
        }

        return $out_trade_sn;
    }
    
	/**
     *    获取团购外部交易号
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

            /* 将此数据写入订单中 */
            $model_order =& m('grouporder');
            $model_order->edit(intval($order_info['order_id']), array('out_trade_sn' => $out_trade_sn));
        }

        return $out_trade_sn;
    }

	/**
     *    获取外部交易号
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

            /* 将此数据写入订单中 */
            $model_order =& m('storeorder');
            $model_order->edit(intval($order_info['order_id']), array('out_trade_sn' => $out_trade_sn));
        }

        return $out_trade_sn;
    }
    /**
     *    获取商品简介
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
     *    获取商品简介
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
     *    获取通知信息
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_notify()
    {
        /* 如果有POST的数据，则认为POST的数据是通知内容 */
        if (!empty($_POST))
        {
            return $_POST;
        }

        /* 否则就认为是GET的 */
        return $_GET;
    }

    /**
     *    验证支付结果
     *
     *    @author    Garbin
     *    @return    void
     */
    function verify_notify()
    {
        #TODO
    }

    /**
     *    将验证结果反馈给网关
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