<?php

/**
 *    ģ�����п�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class ModuleApp extends BackendApp
{
    /**
     *    ģ���б�
     *
     *    @author    Garbin
     *    @return    void
     */
    function manage()
    {
        $modules = $this->_list_modules();
        $this->assign('modules', $modules);
        $this->display('module.index.html');
    }

    /**
     *    ��װģ��
     *
     *    @author    Garbin
     *    @return    void
     */
    function install()
    {
        $id = empty($_GET['id']) ? 0 : trim($_GET['id']);
        if (!$id)
        {
            $this->show_warning('no_such_module');

            return;
        }
        if (!IS_POST)
        {
            $module = $this->_get_module_info($id);
            $this->assign('module', $module);
            $this->assign('config', array('enabled' => true));
            $this->assign('enable_options', array(Lang::get('no'), Lang::get('yes')));
            $this->display('module.form.html');
        }
        else
        {
            $data = array();
            $data['module_id']      =   $id;
            $data['module_name']    =   $_POST['name'];
            $data['module_desc']    =   $_POST['desc'];
            $data['module_version'] =   $_POST['version'];
            $data['enabled']         =   $_POST['enabled'];
            !empty($_POST['config']) && $data['module_config'] = serialize($_POST['config']);

            /* ��ģ����Ϣ���ӵ����ݿ� */
            $model_module =& m('module');
            $model_module->add($data);

            /* ���а�װ�ű� */
            $install_script = ROOT_PATH . '/external/modules/' . $id . '/install.php';
            if (is_file($install_script))
            {
                include($install_script);
            }

            $this->show_message('install_module_successed',
                'manage_module', 'index.php?module='. $data['module_id'] . '&act=index');
        }
    }

    /**
     *    ж��
     *
     *    @author    Garbin
     *    @return    void
     */
    function uninstall()
    {
        $id = empty($_GET['id']) ? 0 : trim($_GET['id']);
        if (!$id)
        {
            $this->show_warning('no_such_module');

            return;
        }

        /* ɾ�����ݿ��еļ�¼ */
        $model_module =& m('module');
        $model_module->drop('index:' . $id);

        /* ����ж�ؽű� */
        $uninstall_script = ROOT_PATH . '/external/modules/' . $id . '/uninstall.php';
        if (is_file($uninstall_script))
        {
            include($uninstall_script);
        }

        $this->show_message('uninstall_module_successed',
            'back_list', 'index.php?app=module&act=manage');
    }

    /**
     *    ����
     *
     *    @author    Garbin
     *    @return    void
     */
    function config()
    {
        $id = empty($_GET['id']) ? 0 : trim($_GET['id']);
        if (!$id)
        {
            $this->show_warning('no_such_module');

            return;
        }
        $model_module =& m('module');
        if (!IS_POST)
        {
            $module = $this->_get_module_info($id);
            $find_data = $model_module->find('index:' . $id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_module');

                return;
            }
            $info = current($find_data);
            $config = unserialize($info['module_config']);
            $config['enabled'] = $info['enabled'];

            $this->assign('module', $module);
            $this->assign('config', $config);
            $this->assign('enable_options', array(Lang::get('no'), Lang::get('yes')));
            $this->display('module.form.html');
        }
        else
        {
            $data   = array();
            !empty($_POST['config']) && $data['module_config'] = serialize($_POST['config']);
            $data['enabled']       = intval($_POST['enabled']);
            $model_module->edit('index:' . $id, $data);
            $this->show_message('config_module_successed');
        }
    }

    /**
     *    �б�ģ��
     *
     *    @author    Garbin
     *    @return    array
     */
    function _list_modules()
    {
        $module_dir = ROOT_PATH . '/external/modules';
        static $modules    = null;
        if ($modules === null)
        {
            $modules = array();
            if (!is_dir($module_dir))
            {
                return $modules;
            }
            $dir = dir($module_dir);
            while (false !== ($entry = $dir->read()))
            {
                if (in_array($entry, array('.', '..')) || $entry{0} == '.')
                {
                    continue;
                }
                $info = $this->_get_module_info($entry);
                $modules[$entry] = $info;
                $modules[$entry]['installed'] = $this->_is_installed($entry);
                $modules[$entry]['outofdate'] = $this->_is_outofdate($entry, $info['version']);
            }
        }

        return $modules;
    }

    /**
     *    ��ȡģ����Ϣ
     *
     *    @author    Garbin
     *    @param     string $id
     *    @return    array
     */
    function _get_module_info($id)
    {
        Lang::load(ROOT_PATH . '/external/modules/' . $id . '/languages/' . LANG . '/common.lang.php');
        $module_info_path = ROOT_PATH . '/external/modules/' . $id . '/module.info.php';

        return include($module_info_path);
    }

    /**
     *    �ж��Ƿ��ʱ
     *
     *    @author    Garbin
     *    @param     string $id
     *    @return    bool
     */
    function _is_outofdate($id, $version)
    {
        $installed = $this->_list_installed();
        $info = $installed[$id];
        if (empty($info))
        {
            return false;
        }

        return $info['module_version'] < $version;
    }

    /**
     *    �ж�ģ���Ƿ��Ѱ�װ
     *
     *    @author    Garbin
     *    @param     string $id
     *    @return    bool
     */
    function _is_installed($id)
    {
        $installed = $this->_list_installed();

        return array_key_exists($id, $installed);
    }

    /**
     *    �б��Ѱ�װ��ģ��
     *
     *    @author    Garbin
     *    @return    array
     */
    function _list_installed()
    {
        static $installed = null;
        if ($installed === null)
        {
            $model_module =& m('module');
            $installed = $model_module->find();
        }

        return $installed;
    }
}

?>
