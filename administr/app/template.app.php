<?php

/* �Ҽ������� */
include(ROOT_PATH . '/includes/widget.base.php');

/**
 *    ģ��༭������
 *
 *    @author    Garbin
 *    @usage    none
 */
class TemplateApp extends BackendApp
{
    /* �ɱ༭��ҳ���б� */
    function index()
    {
        $this->assign('pages', $this->_get_editable_pages());
        $this->display('template.index.html');
    }

    /**
     *    �༭ҳ��
     *
     *    @author    Garbin
     *    @return    void
     */
    function edit()
    {
        /* ��ǰ���༭��ҳ�� */
        $page    = !empty($_GET['page']) ? trim($_GET['page']) : null;
        if (!$page)
        {
            $this->show_warning('no_such_page');

            return;
        }

        /* ע�⣬ͨ�����ַ�ʽ��ȡ��ҳ���и��û���ص����ݶ����οͣ������ͱ�֤��ͳһ�ԣ����������ñ༭������Ϊ���Ƿ��ѵ�¼�����ֲ�ͬ */
        $html = $this->_get_page_html($page);
        
        if (!$html)
        {
            $this->show_warning('no_such_page');

            return;
        }
        /* ��ҳ��ɱ༭ */
        $html = $this->_make_editable($page, $html);

        echo $html;
    }

    /**
     *    ����༭��ҳ��
     *
     *    @author    Garbin
     *    @return    void
     */
    function save()
    {
        /* ��ʼ������ */
        /* ҳ�������еĹҼ�id=>name */
        $widgets = !empty($_POST['widgets']) ? $_POST['widgets'] : array();

        /* ҳ�������йҼ���λ���������� */
        $config  = !empty($_POST['config']) ? $_POST['config'] : array();

        /* ��ǰ���༭��ҳ�� */
        $page    = !empty($_GET['page']) ? trim($_GET['page']) : null;
        if (!$page)
        {
            $this->json_error('no_such_page');

            return;
        }
        $editable_pages = $this->_get_editable_pages();
        if (empty($editable_pages[$page]))
        {
            $this->json_error('no_such_page');

            return;
        }

        $page_config = get_widget_config(Conf::get('template_name'), $page);

        /* д��λ��������Ϣ */
        $page_config['config'] = $config;

        /* ԭʼ�Ҽ���Ϣ */
        $old_widgets = $page_config['widgets'];

        /* ���ԭʼ�Ҽ���Ϣ */
        $page_config['widgets']  = array();

        /* д��Ҽ���Ϣ,ָ���Ҽ�ID���ĸ��Ҽ��Լ�������� */
        foreach ($widgets as $widget_id => $widget_name)
        {
            /* д���µĹҼ���Ϣ */
            $page_config['widgets'][$widget_id]['name']     = $widget_name;
            $page_config['widgets'][$widget_id]['options']  = array();

            /* ����������µ����ã���д�� */
            if (isset($page_config['tmp'][$widget_id]))
            {
                $page_config['widgets'][$widget_id]['options'] = $page_config['tmp'][$widget_id]['options'];

                continue;
            }

            /* д��ɵ�������Ϣ */
            $page_config['widgets'][$widget_id]['options'] = $old_widgets[$widget_id]['options'];
        }

        /* �����ʱ��������Ϣ */
        unset($page_config['tmp']);

        /* �������� */
        $this->_save_page_config(Conf::get('template_name'), $page, $page_config);
        $this->json_result('', 'save_successed');
    }

    /**
     *    ��ȡ�༭�����
     *
     *    @author    Garbin
     *    @return    void
     */
    function get_editor_panel()
    {
        /* ��ȡ�Ҽ��б� */
        $widgets = list_widget();
        header('Content-Type:text/html;charset=' . CHARSET);
        $this->assign('widgets', ecm_json_encode($widgets));
        $this->assign('site_url', SITE_URL);
        $this->display('template.panel.html');
    }

    /**
     *    ��ӹҼ���ҳ����
     *
     *    @author    Garbin
     *    @return    void
     */
    function add_widget()
    {
        $name = !empty($_GET['name']) ? trim($_GET['name']) : null;
        /* ��ǰ���༭��ҳ�� */
        $page    = !empty($_GET['page']) ? trim($_GET['page']) : null;
        if (!$name || !$page)
        {
            $this->json_error('no_such_widget');

            return;
        }
        $page_config = get_widget_config(Conf::get('template_name'), $page);
        $id = $this->_get_unique_id($page_config);
        $widget =& widget($id, $name, array());
        $contents = $widget->get_contents();
        $this->json_result(array('contents' => $contents, 'widget_id' => $id));
    }

    function _get_unique_id($page_config)
    {
        $id = '_widget_' . rand(100, 999);
        if (array_key_exists($id, $page_config['widgets']))
        {
            return $this->_get_unique_id($page_config);
        }

        return $id;
    }

    /**
     *    ��ȡ�Ҽ������ñ�
     *
     *    @author    Garbin
     *    @return    void
     */
    function get_widget_config_form()
    {
        $name = !empty($_GET['name']) ? trim($_GET['name']) : null;
        $id   = !empty($_GET['id']) ? trim($_GET['id']) : null;
        /* ��ǰ���༭��ҳ�� */
        $page    = !empty($_GET['page']) ? trim($_GET['page']) : null;
        if (!$name || !$id || !$page)
        {
            $this->json_error('no_such_widget');

            return;
        }
        $page_config = get_widget_config(Conf::get('template_name'), $page);
        $options = empty($page_config['tmp'][$id]['options']) ? $page_config['widgets'][$id]['options'] : $page_config['tmp'][$id]['options'];
        $widget =& widget($id, $name, $options);
        header('Content-Type:text/html;charset=' . CHARSET);
        $widget->display_config();
    }

    /**
     *    ���ùҼ�
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function config_widget()
    {
        if (!IS_POST)
        {
            return;
        }
        $name = !empty($_GET['name']) ? trim($_GET['name']) : null;
        $id   = !empty($_GET['id']) ? trim($_GET['id']) : null;
        /* ��ǰ���༭��ҳ�� */
        $page    = !empty($_GET['page']) ? trim($_GET['page']) : null;
        if (!$name || !$id || !$page)
        {
            $this->_config_respond('_d.setTitle("' . Lang::get('no_such_widget') . '");_d.setContents("message", {text:"' . Lang::get('no_such_widget') . '"});');

            return;
        }
        $page_config = get_widget_config(Conf::get('template_name'), $page);
        $widget =& widget($id, $name, $page_config['widgets'][$id]['options']);
        $options = $widget->parse_config($_POST);
        if ($options === false)
        {
            $this->json_error($widget->get_error());

            return;
        }
        $page_config['tmp'][$id]['options'] = $options;

        /* ����������Ϣ */
        $this->_save_page_config(Conf::get('template_name'), $page, $page_config);

        /* ���ؼ�ʱ���µ����� */
        $widget->set_options($options);
        $contents = $widget->get_contents();
        $this->_config_respond('DialogManager.close("config_dialog");parent.disableLink(parent.$(document.body));parent.$("#' . $id . '").replaceWith(document.getElementById("' . $id . '").parentNode.innerHTML);parent.init_widget("#' . $id . '");', $contents);
    }

    /**
     *    ��Ӧ��������
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _config_respond($js, $widget = '')
    {
        header('Content-Type:text/html;charset=' . CHARSET);
        echo  '<div>' . $widget . '</div>' . '<script type="text/javascript">var DialogManager = parent.DialogManager;var _d = DialogManager.get("config_widget");' . $js . '</script>';
    }

    /**
     *    ����ҳ�������ļ�
     *
     *    @author    Garbin
     *    @param     string $template_name
     *    @param     string $page
     *    @param     array  $page_config
     *    @return    void
     */
    function _save_page_config($template_name, $page, $page_config)
    {
        $page_config_file = ROOT_PATH . '/data/page_config/' . $template_name . '.' . $page . '.config.php';
        $php_data = "<?php\n\nreturn " . var_export($page_config, true) . ";\n\n?>";

        return file_put_contents($page_config_file, $php_data);
    }

    /**
     *    ��ȡ���༭��ҳ���HTML
     *
     *    @author    Garbin
     *    @param     string $page
     *    @return    string
     */
    function _get_page_html($page)
    {
        $pages = $this->_get_editable_pages();
        if (empty($pages[$page]))
        {
            return false;
        }

        return file_get_contents($pages[$page]);
    }

    /**
     *    ��ҳ����б༭����
     *
     *    @author    Garbin
     *    @param     string $html
     *    @return    string
     */
    function _make_editable($page, $html)
    {
        $editmode = '<script type="text/javascript" src="administr/index.php?act=jslang"></script><script type="text/javascript">__PAGE__ = "' . $page . '";</script><script type="text/javascript" src="' . SITE_URL . '/includes/libraries/javascript/jquery.ui/jquery.ui.js"></script><script type="text/javascript" charset="utf-8" src="' . SITE_URL . '/includes/libraries/javascript/jquery.ui/i18n/' . i18n_code() . '.js"></script><script id="dialog_js" type="text/javascript" src="' . SITE_URL . '/includes/libraries/javascript/dialog/dialog.js"></script><script id="template_editor_js" type="text/javascript" src="' . SITE_URL . '/administr/includes/javascript/template_panel.js"></script><link id="template_editor_css" href="' . SITE_URL . '/administr/templates/style/template_panel.css" rel="stylesheet" type="text/css" /><link rel="stylesheet" href="' . SITE_URL . '/includes/libraries/javascript/jquery.ui/themes/ui-lightness/jquery.ui.css" type="text/css" media="screen" /><link rel="stylesheet" href="' . SITE_URL . '/includes/libraries/javascript/hack.css" type="text/css" media="screen" />';

        return str_replace('<!--<editmode></editmode>-->', $editmode, $html);
    }

    /**
     *    ��ȡ���Ա༭��ҳ���б�
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_editable_pages()
    {
        return array(
            'index' => SITE_URL . '/index.php',
            'gcategory' => SITE_URL . '/index.php?app=category',
        	'pailaIndex' => SITE_URL.'/index.php?app=mall',
 			'search'	=> SITE_URL.'/index.php?app=search&cate_id=1',
        	'special'	=> SITE_URL . '/index.php?app=special',
        	'brand'     => SITE_URL . '/index.php?app=brand_mandate',
        	'plb_deal'  => SITE_URL . '/index.php?app=plb_deal',
        	'agroIndex' => SITE_URL .'/index.php?app=agro',
        	'pailabi_mall' => SITE_URL . '/index.php?app=pailabi_mall',
        	'newpaila_mall' => SITE_URL . '/index.php?app=newpaila_mall',
        	'paila_special' => SITE_URL .'/index.php?app=paila_special',
        	'mother_day' => SITE_URL . '/index.php?app=mother',
        	'promotion'  => SITE_URL . '/index.php?app=promotion_index',
        );
    }
}

?>
