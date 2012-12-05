<?php

/**
 *    支付方式管理控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class PaymentApp extends BackendApp
{
    function index()
    {
        /* 读取已安装的支付方式 */
        $model_payment =& m('payment');
        $payments      = $model_payment->get_builtin();
        $white_list    = $model_payment->get_white_list();
        foreach ($payments as $key => $value)
        {
            $payments[$key]['system_enabled'] = in_array($key, $white_list);
        }
        $this->assign('payments', $payments);
        $this->display('payment.index.html');
    }

    /**
     *    启用
     *
     *    @author    Garbin
     *    @return    void
     */
    function enable()
    {
        $code = isset($_GET['code'])    ? trim($_GET['code']) : 0;
        if (!$code)
        {
            $this->show_warning('no_such_payment');

            return;
        }
        $model_payment =& m('payment');
        if (!$model_payment->enable_builtin($code))
        {
            $this->show_warning($model_payment->get_error());

            return;
        }

        $this->show_message('enable_payment_successed');

    }

    /**
     *    禁用
     *
     *    @author    Garbin
     *    @return    void
     */
    function disable()
    {
        $code = isset($_GET['code'])    ? trim($_GET['code']) : 0;
        if (!$code)
        {
            $this->show_warning('no_such_payment');

            return;
        }
        $model_payment =& m('payment');
        if (!$model_payment->disable_builtin($code))
        {
            $this->show_warning($model_payment->get_error());

            return;
        }

        $this->show_message('disable_payment_successed');
    }
    
    /**
     *    配置保存支付信息
     *
     *    @author    lihuoliang
     *    @return    void
     */
    function config()
    {
        $code = isset($_GET['code'])    ? trim($_GET['code']) : 0;
        $model_payment =& m('payment');
        $payment       = $model_payment->get_builtin_info($code);
        if (!$payment)
        {
            $this->show_warning('no_such_payment');

            return;
        }
        $payment_info = $model_payment->get("store_id=0 AND payment_code='{$code}'");
        
        if (!IS_POST)
        {
        	$payment['payment_id']  =   $payment_info['payment_id'];
            $payment['payment_desc']=   $payment_info['payment_desc'];
            $payment['enabled']     =   $payment_info['enabled'];
            $payment['sort_order']  =   $payment_info['sort_order'];
            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->assign('config',  unserialize($payment_info['config']));
            $this->assign('payment', $payment);
        	$this->display('my_payment.form.html');
        }else{
        	$data1 = array(
                'payment_desc'  =>  $_POST['payment_desc'],
                'config'        =>  serialize($_POST['config']),
                'enabled'       =>  $_POST['enabled'],
                'sort_order'    =>  $_POST['sort_order'],
            );
            if ($payment_info)
            {
            	$model_payment->edit("store_id=0 AND payment_code='{$code}'", $data1);
	            if ($model_payment->has_error())
	            {
	                $this->show_message($model_payment->get_error());
	                return;
	            }
            }else 
            {
            	$data2 = array(
	                'store_id'      => 0,
	                'payment_name'  => $payment['name'],
	                'payment_code'  => $code,
	                'is_online'     => $payment['is_online']
           	 	);
           	 	$data = array_merge($data1,$data2);
	            if (!($payment_id = $model_payment->install($data)))
	            {
	                $this->show_message($model_payment->get_error());
	                return;
	            }
            }
            
        	$this->show_message('保存配置信息成功',
                '返回',    'index.php?app=payment'
            );
        }
    }
}

?>