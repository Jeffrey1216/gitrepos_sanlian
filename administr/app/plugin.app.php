<?php

/**
 *    ������������
 *
 *    @author    Garbin
 *    @usage    none
 */
class PluginApp extends BackendApp
{
    function index()
    {
        /* ��ȡ�Ѱ�װ�Ĳ�� */
        $plugins = $this->_list_plugins();
        $this->assign('plugins', $plugins);
        $this->display('plugin.index.html');
    }

    /**
     *    ����һ�����
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function enable()
    {
        $id = empty($_GET['id']) ? 0 : trim($_GET['id']);
        if (!$id)
        {
            $this->show_warning('no_such_plugin');

            return;
        }
        $plugin_info = $this->_get_plugin_info($id);
        if (!IS_POST)
        {
            $this->assign('plugin', $plugin_info);
            $this->display('plugin.form.html');
        }
        else
        {
            $config = empty($_POST['config']) ? array() : $_POST['config'];
            $result = $this->_enable_plugin($plugin_info['hook'], $id, $config);

            if (!$result)
            {
                $this->show_warning('enable_plugin_failed');

                return;
            }

            $this->show_message('enable_plugin_successed',
                'plugin_manage', 'index.php?app=plugin');
        }
    }

    /**
     *    ���ò��
     *
     *    @author    Garbin
     *    @return    void
     */
    function disable()
    {
        $id = empty($_GET['id']) ? 0 : trim($_GET['id']);
        if (!$id)
        {
            $this->show_warning('no_such_plugin');

            return;
        }
        if(!$this->_disable_plugin($id))
        {
            $this->show_warning('disable_plugin_failed');

            return;
        }

        $this->show_message('disable_plugin_successed');
    }

    function config()
    {
        $id = empty($_GET['id']) ? 0 : trim($_GET['id']);
        if (!$id)
        {
            $this->show_warning('no_such_plugin');

            return;
        }
        $plugin_info = $this->_get_plugin_info($id);
        if (!IS_POST)
        {
            $config      = $this->_get_plugin_config($plugin_info['hook'], $id);
            $this->assign('plugin', $plugin_info);
            $this->assign('config', $config);
            $this->display('plugin.form.html');
        }
        else
        {
            $enabled = $this->_list_enabled_plugins();
            $enabled[$plugin_info['hook']][$id] = $_POST['config'];
            if (!$this->_save_enabled($enabled))
            {
                $this->show_warning('config_plugin_failed');

                return;
            }

            $this->show_message('config_plugin_successed',
            'plugin_manage', 'index.php?app=plugin');
        }
    }

    /**
     *    ��ȡ�Ѱ�װ�Ĳ��
     *
     *    @author    Garbin
     *    @return    array
     */
    function _list_plugins()
    {
        $plugin_dir = ROOT_PATH . '/external/plugins';
        static $plugins    = null;
        if ($plugins === null)
        {
            $plugins = array();
            if (!is_dir($plugin_dir))
            {
                return $plugins;
            }
            $dir = dir($plugin_dir);
            while (false !== ($entry = $dir->read()))
            {
                if (in_array($entry, array('.', '..')) || $entry{0} == '.')
                {
                    continue;
                }
                $info = $this->_get_plugin_info($entry);
                $plugins[$entry] = $info;
                $plugins[$entry]['enabled'] = $this->_is_enabled($info['hook'], $entry);
            }
        }

        return $plugins;
    }

    /**
     *    ��ȡ�����õĲ���б�
     *
     *    @author    Garbin
     *    @return    array
     */
    function _list_enabled_plugins()
    {
        $plugin_inc_file = ROOT_PATH . '/data/plugins.inc.php';
        if (is_file($plugin_inc_file))
        {
            return include($plugin_inc_file);
        }

        return array();
    }

    /**
     *    ��ȡ�����Ϣ
     *
     *    @author    Garbin
     *    @param     string $id
     *    @return    array
     */
    function _get_plugin_info($id)
    {
        $plugin_info_path = ROOT_PATH . '/external/plugins/' . $id . '/plugin.info.php';

        return include($plugin_info_path);
    }

    /**
     *    ��ȡ�����Ϣ
     *
     *    @author    Garbin
     *    @param     string $hook
     *    @param     string $id
     *    @return    array
     */
    function _get_plugin_config($hook, $id)
    {
        $enabled = $this->_list_enabled_plugins();
        return $enabled[$hook][$id];
    }

    /**
     *    �ж�ָ���Ĳ���Ƿ�������
     *
     *    @author    Garbin
     *    @param     string $hook
     *    @param     string $id
     *    @return    bool
     */
    function _is_enabled($hook, $id)
    {
        static $enabled = null;
        if ($enabled === null)
        {
            $enabled = $this->_list_enabled_plugins();
        }

        return isset($enabled[$hook][$id]);
    }

    /**
     *    ����һ�����
     *
     *    @author    Garbin
     *    @param     string $hook
     *    @param     string $id
     *    @param     array  $config
     *    @return    bool
     */
    function _enable_plugin($hook, $id, $config)
    {
        $enabled = $this->_list_enabled_plugins();
        $enabled[$hook][$id] = $config;

        return $this->_save_enabled($enabled);
    }

    /**
     *    ����һ�����
     *
     *    @author    Garbin
     *    @param     string $id
     *    @return    bool
     */
    function _disable_plugin($id)
    {
        $enabled = $this->_list_enabled_plugins();
        $info    = $this->_get_plugin_info($id);
        unset($enabled[$info['hook']][$id]);

        return $this->_save_enabled($enabled);
    }

    /**
     *    �����Ѱ�װ����Ϣ
     *
     *    @author    Garbin
     *    @param     array $enabled
     *    @return    bool
     */
    function _save_enabled($enabled)
    {
        $plugin_inc_file = ROOT_PATH . '/data/plugins.inc.php';
        $php_data = "<?php\n\nreturn " . var_export($enabled, true) . ";\n\n?>";
        return file_put_contents($plugin_inc_file, $php_data);
    }
}

?>