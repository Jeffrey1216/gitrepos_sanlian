<?php

/**
 *    �Ƹ�֧ͨ����ʽ���
 *
 *    @author    Garbin
 *    @usage    none
 */

class TenpayPayment extends BasePayment
{
    /* �Ƹ�ͨ���� */
    var $_gateway   =   'https://www.tenpay.com/cgi-bin/med/show_opentrans.cgi';
    var $_code      =   'tenpay';

    /**
     *    ��ȡ֧����
     *
     *    @author    Garbin
     *    @param     array $order_info  ��֧���Ķ�����Ϣ����������ܷ��ü�Ψһ�ⲿ���׺�
     *    @return    array
     */
    function get_payform($order_info)
    {
        /* �汾�� */
        $version = '2';

        /* ������룬��ֵ��12 */
        $cmdno = '12';

        /* �����׼ */
        if (!defined('CHARSET'))
        {
            $encode_type = 2;
        }
        else
        {
            if (CHARSET == 'utf-8')
            {
                $encode_type = 2;
            }
            else
            {
                $encode_type = 1;
            }
        }

        /* ƽ̨�ṩ��,�����̵ĲƸ�ͨ�˺� */
        $chnid = $this->_config['tenpay_account'];

        /* �տ�Ƹ�ͨ�˺� */
        $seller = $this->_config['tenpay_account'];

        /* ��Ʒ���� */
        $mch_name = $this->_get_subject($order_info);

        /* �ܽ�� */
        $mch_price = floatval($order_info['order_amount']) * 100;

        /* ��������˵�� */
        $transport_desc = '';
        $transport_fee = '';

        /* ����˵�� */
        $mch_desc = $this->_get_subject($order_info);
        $need_buyerinfo = '2' ;

        /* �������ͣ�2�����⽻�ף�1��ʵ�ｻ�� */
        $mch_type = $this->_config['tenpay_type'];

        /* ����һ��������� */
        $rand_num = rand(1,9);
        for ($i = 1; $i < 10; $i++)
        {
            $rand_num .= rand(0,9);
        }

        /* ��ö�������ˮ�ţ����㵽10λ */
        $mch_vno = $this->_get_trade_sn($order_info);

        /* ���ص�·�� */
        $mch_returl = $this->_create_notify_url($order_info['order_id']);
        $show_url   = $this->_create_return_url($order_info['order_id']);
        $attach = $rand_num;

        /* ����ǩ�� */
        $sign_text = "attach=" . $attach . "&chnid=" . $chnid . "&cmdno=" . $cmdno . "&encode_type=" . $encode_type . "&mch_desc=" . $mch_desc . "&mch_name=" . $mch_name . "&mch_price=" . $mch_price ."&mch_returl=" . $mch_returl . "&mch_type=" . $mch_type . "&mch_vno=" . $mch_vno . "&need_buyerinfo=" . $need_buyerinfo ."&seller=" . $seller . "&show_url=" . $show_url . "&version=" . $version . "&key=" . $this->_config['tenpay_key'];

        $sign =md5($sign_text);

        /* ���ײ��� */
        $parameter = array(
            'attach'            => $attach,
            'chnid'             => $chnid,
            'cmdno'             => $cmdno,                     // ҵ�����, �Ƹ�֧ͨ��֧���ӿ���  1
            'encode_type'       => $encode_type,                //�����׼
            'mch_desc'          => $mch_desc,
            'mch_name'          => $mch_name,
            'mch_price'         => $mch_price,                  // �������
            'mch_returl'        => $mch_returl,                 // ���ղƸ�ͨ���ؽ����URL
            'mch_type'          => $mch_type,                   //��������
            'mch_vno'           => $mch_vno,             // ���׺�(������)�����̻���վ����(����˳���ۼ�)
            'need_buyerinfo'    => $need_buyerinfo,             //�Ƿ���Ҫ�ڲƸ�ͨ�������Ϣ
            'seller'            => $seller,  // �̼ҵĲƸ�ͨ�̻���
            'show_url'          => $show_url,
            'transport_desc'    => $transport_desc,
            'transport_fee'     => $transport_fee,
            'version'           => $version,                    //�汾�� 2
            'key'               => $this->_config['tenpay_key'],
            'sign'              => $sign,                       // MD5ǩ��
            'sys_id'            => '542554970'                  //PaiLa C�˺� ������ǩ��
        );

        return $this->_create_payform('GET', $parameter);
    }

    /**
     *    ����֪ͨ���
     *
     *    @author    Garbin
     *    @param     array $order_info
     *    @param     bool  $strict
     *    @return    array ���ؽ��
     *               false ʧ��ʱ����
     */
    function verify_notify($order_info, $strict = false)
    {
        /*ȡ���ز���*/
        $cmd_no         = $_GET['cmdno'];
        $retcode        = $_GET['retcode'];
        $status         = $_GET['status'];
        $seller         = $_GET['seller'];
        $total_fee      = $_GET['total_fee'];
        $trade_price    = $_GET['trade_price'];
        $transport_fee  = $_GET['transport_fee'];
        $buyer_id       = $_GET['buyer_id'];
        $chnid          = $_GET['chnid'];
        $cft_tid        = $_GET['cft_tid'];
        $mch_vno        = $_GET['mch_vno'];
        $attach         = !empty($_GET['attach']) ? $_GET['attach'] : '';
        $version        = $_GET['version'];
        $sign           = $_GET['sign'];
        $log_id = $mch_vno; //ȡ��֧����log_id

        /* ���$retcode����0���ʾ֧��ʧ�� */
        if ($retcode > 0)
        {
            //echo '����ʧ��';
            return false;
        }
        $order_amount = $total_fee / 100;

         /* ���֧���Ľ���Ƿ���� */
        if ($order_info['order_amount'] != $order_amount)
        {
            /* ֧���Ľ����ʵ�ʽ�һ�� */
            $this->_error('price_inconsistent');

            return false;
        }

        if ($order_info['out_trade_sn'] != $log_id)
        {
            /* ֪ͨ�еĶ��������ı�Ķ�����һ�� */
            $this->_error('order_inconsistent');

            return false;
        }

        /* �������ǩ���Ƿ���ȷ */
        $sign_text = "attach=" . $attach . "&buyer_id=" . $buyer_id . "&cft_tid=" . $cft_tid . "&chnid=" . $chnid . "&cmdno=" . $cmd_no . "&mch_vno=" . $mch_vno . "&retcode=" . $retcode . "&seller=" .$seller . "&status=" . $status . "&total_fee=" . $total_fee . "&trade_price=" . $trade_price . "&transport_fee=" . $transport_fee . "&version=" . $version . "&key=" . $this->_config['tenpay_key'];
        $sign_md5 = strtoupper(md5($sign_text));
        if ($sign_md5 != $sign)
        {
            /* ������ǩ��������ǩ����һ�£�˵��ǩ�������� */
            $this->_error('sign_inconsistent');

            return false;
        }
        if ($status != 3)
        {
            return false;
        }

        return array(
            'target'    =>  ORDER_ACCEPTED,
        );
    }
    
    /**
     *    ��ȡ�ⲿ���׺� ���ǻ���
     *
     *    @author    huibiaoli
     *    @param     array $order_info
     *    @return    string
     */
    function _get_trade_sn($order_info)
    {
        if (!$order_info['out_trade_sn'] || $order_info['pay_alter'])
        {
            $out_trade_sn = $this->_gen_trade_sn();
        }
        else
        {
            $out_trade_sn = $order_info['out_trade_sn'];
        }
        
        /* ��������д�붩���� */
        $model_order =& m('order');
        $model_order->edit(intval($order_info['order_id']), array('out_trade_sn' => $out_trade_sn, 'pay_alter' => 0));
        return $out_trade_sn;
    }
    
    /**
     *    �����ⲿ���׺�
     *
     *    @author    huibiaoli
     *    @return    string
     */
    function _gen_trade_sn()
    {
        /* ѡ��һ������ķ��� */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        $out_trade_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $model_order =& m('order');
        $orders = $model_order->find('out_trade_sn=' . $out_trade_sn);
        if (empty($orders))
        {
            /* �����ʹ��������׺� */
            return $out_trade_sn;
        }

        /* ������ظ��ģ����������� */
        return $this->_gen_trade_sn();
    }
}

?>