<?php
date_default_timezone_set('Asia/Shanghai');
define("PAGE_NUM",20);
/*�Ź���̨������*/
class GroupOrderApp extends ShoppingbaseApp {
	
	public function __construct() {
		$this->GroupOrderApp();
	}
	
	public function GroupOrderApp() {
		parent::__construct();
		$this->_group_gcategory_mod=&m('groupgcategory'); //��Ʒ���� 
		$this->_group_project_mod=&m('groupproject');
		$this->_group_category_mod = & m('groupcategory'); 
		$this->_group_specname_mod = & m('groupspecname'); //�����
		$this->_group_spec_mod = & m('groupspec'); //���ֵ
		$this->_store_mod = & m('store');//�̻�
		$this->_group_project_specname_mod = & m('groupprojectspecname');
		$this->_group_project_spec_mod = & m('groupprojectspec');
		$this->_address_mod = & m('address');
		$this->_region_mod = & m('region');
		$this->_group_order_mod = & m('grouporder');
		$this->_group_order_spec_mod = & m('grouporderspec');
		$this->_group_order_extm_mod = & m('grouporderextm');
	}
	
	public function index() {
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if($id == 0) {
			$this->show_warning('����:δ֪����Ʒ!..');
			return;
		}
		$goods_info = $this->_group_project_mod->getRow("select * from pa_group_project where id = " . $id);
		if(!IS_POST) {
			if(!$goods_info) {
				$this->show_warning("����:��Ʒ������ !");
				return;
			}
			//��ȡ���
			$specnames = $this->_group_project_specname_mod->getAll("select * from pa_group_projectspecname gp left join pa_group_specname gs on gp.specname_id = gs.id where project_id = " . $id);
			foreach($specnames as $k => $v) {
				$specnames[$k]['specs'] = $this->_group_project_spec_mod->getAll("select * from pa_group_spec where specname_id = " . $v['specname_id']);
			} 

			//��ȡ�ջ���ַ
			$user_id = $this->visitor->get('user_id');
			$address = $this->_address_mod->getAll("select * from pa_address where user_id = " . $user_id);
			$region = $this->_region_mod->get_list(0);

			$this->assign("address" , $address);
			
			$this->assign('specnames',$specnames);
			$this->assign("region",$region);
			$this->assign('goods_info',$goods_info);
			$this->display("group_order.form.html");
		} else {
			$user_id = $this->visitor->get('user_id');
			$member_mod = & m('member');
			$user_info = $member_mod->get($user_id);
			$param = array(
				'order_sn' => $this->_gen_order_sn(),
				'extension' => trim($_POST['extension']),
				'seller_id' => $goods_info['store_id'],
				'seller_name' => $goods_info['store_name'],
				'buyer_id' => $user_id,
				'buyer_name' => $user_info['user_name'],
				'buyer_email' => $user_info['email'],
				'status' => 11, //״̬,11�ȴ���Ҹ���,20����Ѹ���ȴ����ҷ���,30�����ѷ���,40���׳ɹ�,0����ȡ��
				'add_time' => time(),
				'payment_id' => 0,
				'payment_name' => '',
				'payment_code' => '',
				'out_trade_sn' => '',
				'pay_time' => 0,
				'pay_message' => '', //֧����Ϣ
				'ship_time' => '', //����ʱ��
				'Invoice_no' => '',
				'finished_time' => 0,
				'goods_amount' => floatval($_POST['order_amount']),
				'discount' => 0, //�ۿۼ�
				'order_amount' => floatval($_POST['order_amount']),
				'order_type' => intval($goods_info['category_id']) == 1 ? 0 : 1, //��������,0Ϊ�Ź�����,1Ϊ��ɱ����
				'evaluation_status' => 0, //����״̬,0Ϊδ����,1Ϊ���������,2Ϊ�̼�������,3Ϊ˫��������
				'evaluation_time' => 0, //����ʱ��
				'anonymous' => 0, //�Ƿ�����
				'postscript' => '', //������Ϣ
				'project_id' => $goods_info['id'],
				'quantity' => intval($_POST['quantity']),
			);
			//���ݲ���
			$order_id = $this->_group_order_mod->add($param);
			if(!$order_id) {
				$this->show_warning("�������");
				return;
			}
			
			//������
			$sql = 'insert into pa_group_order_spec(order_id,spec_id) values';
			$specs = $_POST['spec_value'];
			if(is_array($specs)) {
				foreach($specs as $k => $v) {
					if($v == '') {
						$this->show_warning("����: �й��δѡ��!.");
						return;
					}
					$sql .= "(" . $order_id . "," . $v . "),";
				}
				$sql = substr($sql,0,-1);
				$this->_group_order_spec_mod->db->query($sql);
			}
			
			//�ջ���ַ����
			$order_address = empty($_POST['order_address']) ? 0 : intval($_POST['order_address']);
			if($order_address == 0) { //��д���ջ���ַ
				$param = array(
					'user_id' => $user_id,
					'consignee' => trim($_POST['consignee']),
					'region_id' => intval($_POST['region_id']),
					'region_name' => trim($_POST['region_name']),
					'address' => trim($_POST['address']),
					'zipcode' => trim($_POST['zipcode']),
					'phone_tel' => trim($_POST['phone_tel']),
					'phone_mob' => trim($_POST['phone_mob']),
				);
				$extm_param = array(
					'order_id' => $order_id,
					'consignee' => trim($_POST['consignee']),
					'region_id' => intval($_POST['region_id']),
					'region_name' => trim($_POST['region_name']),
					'address' => trim($_POST['address']),
					'zipcode' => trim($_POST['zipcode']),
					'phone_tel' => trim($_POST['phone_tel']),
					'phone_mob' => trim($_POST['phone_mob']),
				);
				$this->_address_mod->add($param);
				$this->_group_order_extm_mod->add($extm_param);
			} else { //����õ��ջ���ַ 
				$order_address_param = $this->_address_mod->get($order_address);
				$extm_param = array(
					'order_id' => $order_id,
					'consignee' => trim($order_address_param['consignee']),
					'region_id' => intval($order_address_param['region_id']),
					'region_name' => trim($order_address_param['region_name']),
					'address' => trim($order_address_param['address']),
					'zipcode' => trim($order_address_param['zipcode']),
					'phone_tel' => trim($order_address_param['phone_tel']),
					'phone_mob' => trim($order_address_param['phone_mob']),
				);
				$this->_group_order_extm_mod->add($extm_param);
			}
			//��Ʒ����
			
			header("Location:index.php?app=groupOrder&act=gotoPay&order_id=" . $order_id);
		}
	}
	
	/**
     *    ���ɶ�����
     *
     *    @author    Garbin
     *    @return    string
     */
    function _gen_order_sn()
    {
        /* ѡ��һ������ķ��� */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        $order_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $orders = $this->_group_order_mod->find('order_sn=' . $order_sn);
        if (empty($orders))
        {
            /* �����ʹ����������� */
            return $order_sn;
        }

        /* ������ظ��ģ����������� */
        return $this->_gen_order_sn();
    }
    
    public function gotoPay() {
    	/* �ⲿ�ṩ������ */
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }
        /* �ڲ����ݶ���������,��ȡ�ն���Ǯ��ʹ���ĸ�֧���ӿ� */
        $order_info  = $this->_group_order_mod->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_warning('no_such_order');

            return;
        }
        /* ������Ч���ж� */
        if ($order_info['payment_code'] != 'cod' && $order_info['status'] != 11)
        {
            $this->show_warning('no_such_order');
            return;
        }
        $payment_model =& m('payment');
        if (!$order_info['payment_id']) {
        	/* ����û��ѡ��֧����ʽ��������ѡ��֧����ʽ */
            $payments = $payment_model->get_enabled(0);
            if (empty($payments))
            {
                $this->show_warning('store_no_payment');

                return;
            }
            $all_payments = array('online' => array(), 'offline' => array());
            foreach ($payments as $key => $payment)
            {
                if ($payment['is_online'])
                {
                    $all_payments['online'][] = $payment;
                }
                else
                {
                    $all_payments['offline'][] = $payment;
                }
            }
            //ȡ�������е���Ʒ
            $goods_info = $this->_group_project_mod->get($order_info['project_id']);
			if(!goods_info) {
				$this->show_warning('��Ʒ������ !');
				return;
			}
			$order_extm_info = $this->_group_order_extm_mod->get($order_info['order_id']);
			$this->assign('order_extm',$order_extm_info);
            $this->assign('goods_info',$goods_info);
            $this->assign('order', $order_info);
            $this->assign('payments', $all_payments);
            $this->_curlocal(
                LANG::get('cashier')
            );
			
            $this->_config_seo('title', Lang::get('confirm_payment') . ' - ' . Conf::get('site_title'));
            $this->display("groupproject.payment.html");
        } else {
        	/* ����ֱ�ӵ�����֧�� */
            /* ��֤֧����ʽ�Ƿ���ã������ڰ������У�������ʹ�� */
            if (!$payment_model->in_white_list($order_info['payment_code']))
            {
                $this->show_warning('payment_disabled_by_system');

                return;
            }

            $payment_info  = $payment_model->get("payment_code = '{$order_info['payment_code']}' AND store_id=0");
            /* ����̨û������֧����ʽ��������ʹ�� */
            if (!$payment_info['enabled'])
            {
                $this->show_warning('payment_disabled');

                return;
            }
            
            /* ����֧��URL��� */
            $payment    = $this->_get_payment($order_info['payment_code'], $payment_info);
            $payment_form = $payment->get_group_payform($order_info);
            
            /*echo "payform<pre>";
            var_dump($payment);
            echo "payment_form<hr/>";
            var_dump($payment_form);
            echo "</pre>";*/
           
            /* ��ת����ʵ����̨ */
            $this->_config_seo('title', Lang::get('cashier'));
            $this->assign('payform', $payment_form);
            $this->assign('payment', $payment_info);
            $this->assign('order', $order_info);
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->display('groupproject.payform.html');
    	
        }
    }
	/**
     *    ȷ��֧��
     *
     *    @author    Garbin
     *    @return    void
     */
    function goto_pay()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $payment_id = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }
        if (!$payment_id)
        {
            $this->show_warning('no_such_payment');

            return;
        }
        $order_info  = $this->_group_order_mod->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($order_info))
        {
            $this->show_warning('no_such_order');

            return;
        }

        #���ܲ�����
        if ($order_info['payment_id'])
        {
            $this->_goto_pay($order_id);
            return;
        }

        /* ��֤֧����ʽ */
        $payment_model =& m('payment');
        $payment_info  = $payment_model->get($payment_id);
        if (!$payment_info)
        {
            $this->show_warning('no_such_payment');

            return;
        }

        /* ����֧����ʽ */
        $edit_data = array(
            'payment_id'    =>  $payment_info['payment_id'],
            'payment_code'  =>  $payment_info['payment_code'],
            'payment_name'  =>  $payment_info['payment_name'],
        );


        $this->_group_order_mod->edit($order_id, $edit_data);

        /* ��ʼ֧�� */
        $this->_goto_pay($order_id);
    }
	function _goto_pay($order_id)
    {
        header('Location:index.php?app=groupOrder&act=gotoPay&order_id=' . $order_id);
    }
}