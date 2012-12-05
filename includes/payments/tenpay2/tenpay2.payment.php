<?php

/**
 *    �Ƹ�֧ͨ����ʽ���
 *
 *    @author    Garbin
 *    @usage    none
 */

class Tenpay2Payment extends BasePayment
{
    /* �Ƹ�ͨ���� */
    var $_gateway   =   'https://www.tenpay.com/cgi-bin/v1.0/pay_gate.cgi';
    var $_code      =   'tenpay2';

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
        $version = '1.0';

        /* ������룬��ֵ��1 */
        $cmd_no = '1';

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

        /* �������� */
        $today = date('Ymd');
        
        /* ��������:֧�ִ����غͲƸ�ͨ */
        $bank_type = '0';
        /* �����������ö�������� */
        if (!empty($order_info['order_id']))
        {
            $attach = '';
        }
        else
        {        
            $attach = 'voucher';
        }
        
        /* ƽ̨�ṩ��,�����̵ĲƸ�ͨ�˺� */
        $chnid = $this->_config['tenpay_account'];

        /* �տ�Ƹ�ͨ�˺� */
        $seller = $this->_config['tenpay_account'];

        /* ��Ʒ���� */
        $mch_name = $this->_get_subject($order_info);

        /* �ܽ�� */
        $mch_price = floatval($order_info['order_amount']) * 100;

       

        /* ����˵�� */
        $mch_desc = $this->_get_subject($order_info);
        $need_buyerinfo = '2' ;

        /* �������� */
        $fee_type = '1';

        /* ����һ��������� */
        /*$rand_num = rand(1,9);
        for ($i = 1; $i < 10; $i++)
        {
            $rand_num .= rand(0,9);
        }*/

        /* ��ö�������ˮ�ţ����㵽10λ */    
        $mch_vno = $this->_get_trade_sn($order_info);    

        /* ���̻���+������+��ˮ�� */
        $transaction_id = $this->_config['tenpay_account'].$today.$mch_vno;
        /* ���ص�·�� */
        $mch_returl = $this->_create_notify_url($order_info['order_id']);
        $show_url   = $this->_create_return_url($order_info['order_id']);
        //$attach = $rand_num;
        $spbill_create_ip =  real_ip();

        /* ����ǩ�� */
        $sign_text = "cmdno=" . $cmd_no . "&date=" . $today . "&bargainor_id=" . $seller .
          "&transaction_id=" . $transaction_id . "&sp_billno=" . $mch_vno .
          "&total_fee=" . $mch_price . "&fee_type=" . $fee_type . "&return_url=" . $mch_returl .
          "&attach=" . $attach . "&spbill_create_ip=" . $spbill_create_ip . "&key=" . $this->_config['tenpay_key'];
        $sign = strtoupper(md5($sign_text));

        /* ���ײ��� */
        $parameter = array(
            'cmdno'             => $cmd_no,                      // ҵ�����, �Ƹ�֧ͨ��֧���ӿ���  1
            'date'              => $today,                       // �̻����ڣ���20051212
            'bank_type'         => $bank_type,                    // ��������:֧�ִ����غͲƸ�ͨ                            
            'desc'              => $mch_name,
            'purchaser_id'      => '',                            // �û�(��)�ĲƸ�ͨ�ʻ�,����Ϊ��
            'bargainor_id'      => $seller,                        // �̼ҵĲƸ�ͨ�̻���
            'transaction_id'    => $transaction_id,             // ���׺�(������)�����̻���վ����(����˳���ۼ�)
            'sp_billno'         => $mch_vno,                    //�̻�ϵͳ�ڲ��Ķ�����,���10λ    
            'total_fee'         => $mch_price,                    //�����ܼ�
            'fee_type'          => $fee_type,                    //�ֽ�֧������
            'return_url'        => $mch_returl,
            'attach'            => $attach,
            'sign'              => $sign,                      // MD5ǩ��
            'sys_id'            => '542554970',                  //PaiLa C�˺� ������ǩ��
            'spbill_create_ip'  => $spbill_create_ip   //�Ƹ�ͨ���շ�������
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
        $pay_result     = $_GET['pay_result'];
        $pay_info       = $_GET['pay_info'];
        $bill_date      = $_GET['date'];
        $bargainor_id   = $_GET['bargainor_id'];
        $transaction_id = $_GET['transaction_id'];
        $sp_billno      = $_GET['sp_billno'];
        $total_fee      = $_GET['total_fee'];
        $fee_type       = $_GET['fee_type'];
        $attach         = $_GET['attach'];
        $sign           = $_GET['sign'];
        
        $order_amount = $total_fee / 100;
        
        if ($attach == 'voucher')
        {
            $this->_error('no_order');
            return false;
        }
        /* ���pay_result����0���ʾ֧��ʧ�� */
        if ($pay_result > 0)
        {
            $this->_error('pay_fail');
            return false;
        }
         /* ���֧���Ľ���Ƿ���� */
        if ($order_info['order_amount'] != $order_amount)
        {
            /* ֧���Ľ����ʵ�ʽ�һ�� */
            $this->_error('price_inconsistent');

            return false;
        }
        if ($order_info['out_trade_sn'] != $sp_billno)
        {
            /* ֪ͨ�еĶ��������ı�Ķ�����һ�� */
            $this->_error('order_inconsistent');

            return false;
        }
        /* �������ǩ���Ƿ���ȷ */
        $sign_text  = "cmdno=" . $cmd_no . "&pay_result=" . $pay_result .
                      "&date=" . $bill_date . "&transaction_id=" . $transaction_id .
                      "&sp_billno=" . $sp_billno . "&total_fee=" . $total_fee .
                      "&fee_type=" . $fee_type . "&attach=" . $attach .
                      "&key=" . $this->_config['tenpay_key'];
        $sign_md5 = strtoupper(md5($sign_text));
        if ($sign_md5 != $sign)
        {
            /* ������ǩ��������ǩ����һ�£�˵��ǩ�������� */
            $this->_error('sign_inconsistent');

            return false;
        }
         return array(
            'target'    =>  ORDER_ACCEPTED,
        );
    }
    
    function verify_result($result) 
    {
        if ($result)
        {
            $url = $this->_create_return_url($_GET['order_id']);
            $back_url = $url . '&cmdno=' . $_GET['cmdno'] . '&pay_result=' . $_GET['pay_result'] . '&pay_info=' . $_GET['pay_info'].
                '&date=' . $_GET['date'] . '&bargainor_id=' . $_GET['bargainor_id'] .'&transaction_id=' . $_GET['transaction_id'].
                '&sp_billno=' . $_GET['sp_billno'] . '&total_fee=' . $_GET['total_fee'] . '&fee_type=' . $_GET['fee_type'] . '&attach=' . $_GET['attach'] . '&sign=' . $_GET['sign'];
            echo "<meta name='TENCENT_ONLINE_PAYMENT' content='China TENCENT'><html><script language=javascript>window.location.href='". $back_url ."';</script></html>";
        }
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