<?php

/**
 *    С�Ҽ�������
 *
 *    @author    Garbin
 *    @usage    none
 */
class BaseWidget extends Object
{
    var $options = null;    //��ʾѡ��
    var $_name   = null;    //�Ҽ���ʶ
    var $id      = null;     //��ҳ���е�Ψһ��ʶ
    var $widget_root = '';  //HTTP��Ŀ¼
    var $widget_path = '';  //����·��
    var $_ttl    = 3600;    //����ʱ��
    function __construct($id, $options = array())
    {
        $this->BaseWidget($id, $options);
    }
    function BaseWidget($id, $options = array())
    {
        $this->id = $id;
        $this->widget_path = ROOT_PATH . '/external/widgets/' . $this->_name;
        $this->widget_root = SITE_URL . '/external/widgets/' . $this->_name;

        /* ��ʼ����ͼ���� */
        $this->_view =& _widget_view();
        $this->_view->lib_base = SITE_URL . '/includes/libraries/javascript';
        $this->set_options($options);
        $this->assign('widget_root', $this->widget_root);
        $this->assign('id', $this->id);
        $this->assign('name', $this->_name);
        $this->assign('image_url',IMAGE_URL);
    }

    /**
     *    ����ѡ��
     *
     *    @author    Garbin
     *    @param     array
     *    @return    void
     */
    function set_options($options)
    {
        $this->options = $options;
        $this->assign('options', $this->options);
    }

    /**
     *    ��ȡָ��ģ�������
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function fetch($tpl)
    {
        return $this->_view->fetch('str:' . $this->_get_template($tpl));
    }

    /**
     *    ����ͼ��������
     *
     *    @author    Garbin
     *    @param     mixed $k
     *    @param     mixed $v
     *    @return    void
     */
    function assign($k, $v = null)
    {
        if (is_array($k))
        {
            $args  = func_get_args();
            foreach ($args as $arg)     //��������
            {
                foreach ($arg as $key => $value)    //�������ݲ�������ͼ
                {
                    $this->_view->assign($key, $value);
                }
            }
        }
        else
        {
            $this->_view->assign($k, $v);
        }
    }

    /**
     *    ȡģ��
     *
     *    @author    Garbin
     *    @param     string $tpl
     *    @return    string
     */
    function _get_template($tpl)
    {
        return file_get_contents($this->widget_path . "/{$tpl}.html");
    }

    /**
     *    ȡ����
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_data()
    {
        #code ��ȡ����������

        return array();
    }

    /**
     *    ��ȡ��׼�ĹҼ�HTML
     *
     *    @author    Garbin
     *    @param     string $html
     *    @return    string
     */
    function _wrap_contents($html)
    {
        return "\r\n<div id=\"{$this->id}\" name=\"{$this->_name}\" widget_type=\"widget\" class=\"widget\">\r\n" .
               $html .
               "\r\n</div>\r\n";
    }

    /**
     *    ��ȡ�õ����ݰ�ģ�����ʽ���
     *
     *    @author    Garbin
     *    @return    string
     */
    function get_contents()
    {
        /* ��ȡ�Ҽ����� */
        $this->assign('widget_data', $this->_get_data());

        /*����������*/
        $this->assign('options', $this->options);
        $this->assign('widget_root', $this->widget_root);

        return $this->_wrap_contents($this->fetch('widget'));
    }

    /**
     *    ��ʾ
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function display()
    {
        echo $this->get_contents();
    }

    /**
     *    ��ȡ���ñ�
     *
     *    @author    Garbin
     *    @return    string
     */
    function get_config_form()
    {
        $this->get_config_datasrc();
        return $this->fetch('config');
    }

    /**
     * ��������ҳ����Ҫ��һЩ����
     */
    function get_config_datasrc()
    {
        // $this->assign('var', $var);
    }


    /**
     *    ��ʾ���ñ�
     *
     *    @author    Garbin
     *    @return    void
     */
    function display_config()
    {
        echo $this->get_config_form();
    }

    /**
     *    ����������
     *
     *    @author    Garbin
     *    @param     array $input
     *    @return    array
     */
    function parse_config($input)
    {
        return $input;
    }

    /* ȡ���Ƽ����� */
    function _get_recommends()
    {
        $recom_mod =& bm('recommend', array('_store_id' => 0));
        $recommends = $recom_mod->get_options();
        $recommends[REC_NEW] = Lang::get('recommend_new');

        return $recommends;
    }

    /* ȡ�÷����б� */
    function _get_gcategory_options($layer = 0)
    {
        $gcategory_mod =& bm('gcategory', array('_store_id' => 0));
        $gcategories = $gcategory_mod->get_list();

        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name' ,'nav_img');

        return $tree->getOptions($layer);
    }

    /* ȡ����id */
    function _get_cache_id()
    {
        $config = array(
            'widget_name' => $this->_name,
        );
        if ($this->options)
        {
            $config = array_merge($config, $this->options);
        }

        return md5('widget.' . var_export($config, true));
    }
}

/**
 *    ��ȡ�Ҽ���ͼ������
 *
 *    @author    Garbin
 *    @return    void
 */
function &_widget_view()
{
    //return v(true);
    static $widget_view = null;
    if ($widget_view === null)
    {
        $widget_view = v(true);
    }

    return $widget_view;
}

/**
 *    ��ȡ�Ҽ�ʵ��
 *
 *    @author    Garbin
 *    @param     string $id
 *    @param     string $name
 *    @param     array  $options
 *    @return    Object Widget
 */
function &widget($id, $name, $options = array())
{
    static $widgets = null;
    if (!isset($widgets[$id]))
    {
        $widget_class_path = ROOT_PATH . '/external/widgets/' . $name . '/main.widget.php';
        $widget_class_name = ucfirst($name) . 'Widget';
        include_once($widget_class_path);
        $widgets[$id] = new $widget_class_name($id, $options);
    }

    return $widgets[$id];
}

/**
 *    ��ȡָ�����ָ��ҳ��ĹҼ���������Ϣ
 *
 *    @author    Garbin
 *    @param     string $template_name
 *    @param     string $page
 *    @return    array
 */
function get_widget_config($template_name, $page)
{
    static $widgets = null;
    $key = $template_name . '_' . $page;
    if (!isset($widgets[$key]))
    {
        $tmp = array('widgets' => array(), 'config' => array());
        $config_file = ROOT_PATH . '/data/page_config/' . $template_name . '.' . $page . '.config.php';
        if (is_file($config_file))
        {
            /* �������ļ�����������ļ���ȡ */
            $tmp = include_once($config_file);
        }

        $widgets[$key] = $tmp;
    }
    return $widgets[$key];
}
?>