<?php

/**
 *    �ҵ��ջ���ַ������
 *
 *    @author    Garbin
 *    @usage    none
 */
class My_addressApp extends MemberbaseApp
{
    function index()
    {
    	/*������������*/
    	$schkey = !$_POST['keyword']? "" :  $_POST['keyword'];
    	$this->assign('schkey', $schkey);
    	$schkey!="" && $schkey=" and consignee like '%" . $schkey . "%'";
    	
    	/* ȡ���б����� */
        $model_address =& m('address');
        $addresses     = $model_address->find(array(
            'conditions'    => 'user_id = ' . $this->visitor->get('user_id').$schkey,
        ));
        $this->assign('addresses', $addresses);
        /* ��ǰλ�� */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('my_address'), 'index.php?app=my_address',
                         LANG::get('address_list'));
       	/* ��ǰ�û�������Ϣ*/
        $this->_get_user_info();
        /* ��ǰ�û����Ĳ˵� */
        $this->_curitem('adress_manager');

        /* ��ǰ�����Ӳ˵� */
        $this->_curmenu('address_list');

       	$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                	'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_address'));
        $this->display('my_address.index.html');
    }

    /**
     *    ���ӵ�ַ
     *
     *    @author    Garbin
     *    @return    void
     */
    function add()
    {
        if (!IS_POST)
        {
            /* ��ǰλ�� */
            /*$this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                             LANG::get('my_address'), 'index.php?app=my_address',
                             LANG::get('add_address'));*/
            //$this->import_resource('mlselection.js, jquery.plugins/jquery.validate.js');
            $this->assign('act', 'add');
            $this->_get_regions();
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->display('my_address.form.html');
        }
        else
        {
            /* �绰���ֻ�������һ�� */
            if (!$_POST['phone_tel'] && !$_POST['phone_mob'])
            {
                $this->pop_warning('phone_required');

                return;
            }

            $region_name = $_POST['region_name'];
            $data = array(
                'user_id'       => $this->visitor->get('user_id'),
                'consignee'     => $_POST['consignee'],
                'region_id'     => $_POST['region_id'],
                'region_name'   => $_POST['region_name'],
                'address'       => $_POST['address'],
                'zipcode'       => $_POST['zipcode'],
                'phone_tel'     => $_POST['phone_tel'],
                'phone_mob'     => $_POST['phone_mob'],
            );
            $model_address =& m('address');
            if (!($address_id = $model_address->add($data)))
            {
                $this->pop_warning($model_address->get_error());

                return;
            }
            $this->pop_warning('ok', APP.'_'.ACT);
        }
    }
    function edit()
    {
        $addr_id = empty($_GET['addr_id']) ? 0 : intval($_GET['addr_id']);
        if (!$addr_id)
        {
            echo Lang::get("no_such_address");
            return;
        }
        if (!IS_POST)
        {
            $model_address =& m('address');
            $find_data     = $model_address->find("addr_id = {$addr_id} AND user_id=" . $this->visitor->get('user_id'));
            if (empty($find_data))
            {
                echo Lang::get('no_such_address');

                return;
            }
            $address = current($find_data);

            /* ��ǰλ�� */
            $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                             LANG::get('my_address'), 'index.php?app=my_address',
                             LANG::get('edit_address'));

            /* ��ǰ�û����Ĳ˵� */
            /*$this->_curitem('my_address');

            
            /* ��ǰ�����Ӳ˵� */
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->_curmenu('edit_address');

            $this->assign('address', $address);
            //$this->import_resource('mlselection.js, jquery.plugins/jquery.validate.js');
            $this->assign('act', 'edit');
            $this->_get_regions();
            $this->display('my_address.form.html');
        }
        else
        {
            /* �绰���ֻ�������һ�� */
            if (!$_POST['phone_tel'] && !$_POST['phone_mob'])
            {
                $this->pop_warning('phone_required');

                return;
            }
            $data = array(
                'consignee'     => $_POST['consignee'],
                'region_id'     => $_POST['region_id'],
                'region_name'   => $_POST['region_name'],
                'address'       => $_POST['address'],
                'zipcode'       => $_POST['zipcode'],
                'phone_tel'     => $_POST['phone_tel'],
                'phone_mob'     => $_POST['phone_mob'],
            );
            $model_address =& m('address');
            $model_address->edit("addr_id = {$addr_id} AND user_id=" . $this->visitor->get('user_id'), $data);
            if ($model_address->has_error())
            {
                $this->pop_warning($model_address->get_error());

                return;
            }
            $this->pop_warning('ok', APP.'_'.ACT);
        }
    }
    function drop()
    {
        $addr_id = isset($_GET['addr_id']) ? trim($_GET['addr_id']) : 0;
        if (!$addr_id)
        {
            $this->show_warning('no_such_address');

            return;
        }
        $ids = explode(',', $addr_id);//��ȡһ������array(1, 2, 3)������
        $model_address  =& m('address');
        $drop_count = $model_address->drop("user_id = " . $this->visitor->get('user_id') . " AND addr_id " . db_create_in($ids));
        if (!$drop_count)
        {
            /* û�п�ɾ������ */
            $this->show_warning('no_such_address');

            return;
        }

        if ($model_address->has_error())    //������
        {
            $this->show_warning($model_address->get_error());

            return;
        }
		header("Location:index.php?app=my_address");
        //$this->show_message('drop_address_successed');
    }
    function _get_regions()
    {
        $model_region =& m('region');
        $regions = $model_region->get_list(0);
        if ($regions)
        {
            $tmp  = array();
            foreach ($regions as $key => $value)
            {
                $tmp[$key] = $value['region_name'];
            }
            $regions = $tmp;
        }
        $this->assign('regions', $regions);
    }
    /**
     *    �����˵�
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name'  => 'address_list',
                'url'   => 'index.php?app=my_address',
            ),
/*            array(
                'name'  => 'add_address',
                'url'   => 'index.php?app=my_address&act=add',
            ),*/
        );
/*        if (ACT == 'edit')
        {
            $menus[] = array(
                'name' => 'edit_address',
                'url'  => '',
            );
        }*/
        return $menus;
    }
}

?>