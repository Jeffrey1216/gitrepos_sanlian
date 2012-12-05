<?php

/**
 *    ������������
 *
 *    @author    Garbin
 *    @usage    none
 */
class BaseApp extends Object
{
    /* ��������ͼ������ */
    var $_view = null;

    function __construct()
    {
        $this->BaseApp();
    }

    function BaseApp()
    {
        /* ��ʼ��Session */
        $this->_init_session();
    }

    /**
     *    ����ָ���Ķ���
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function do_action($action)
    {
        if ($action && $action{0} != '_' && method_exists($this, $action))
        {
            $this->_curr_action  = $action;
            $this->_run_action();            //���ж���
        }
        else
        {
            exit('missing_action');
        }
    }
    function index()
    {
        echo 'Hello! PaiLa';
    }

    /**
     *    ����ͼ���ݱ���
     *
     *    @author    Garbin
     *    @param     string $k
     *    @param     mixed  $v
     *    @return    void
     */
    function assign($k, $v = null)
    {
        $this->_init_view();
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
     *    ��ʾ��ͼ
     *
     *    @author    Garbin
     *    @param     string $n
     *    @return    void
     */
    function display($n)
    {
        $this->_init_view();
        $this->_view->display($n);
    }

    /**
     *    ��ʼ����ͼ����
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _init_view()
    {
        if ($this->_view === null)
        {
            $this->_view =& v();
            $this->_config_view();  //����
        }
    }

    /**
     *    ������ͼ
     *
     *    @author    Garbin
     *    @return    void
     */
    function _config_view()
    {
        # code...
    }

    /**
     *    ���ж���
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _run_action()
    {
        $action = $this->_curr_action;
        $this->$action();
    }

    /**
     *    ��ʼ��Session
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _init_session()
    {
        import('session.lib');
        $db =& db();
        $this->_session = new SessionProcessor($db, '`pa_sessions`', '`pa_sessions_data`', 'PL_ID');
        define('SESS_ID', $this->_session->get_session_id());
        $this->_session->my_session_start();
    }
    
    

    /**
     *    ��ȡ��������ʱ��
     *
     *    @author:    Garbin
     *    @param:     int $precision
     *    @return:    float
     */
    function _get_run_time($precision = 5)
    {
        return round(pl_microtime() - START_TIME, $precision);
    }

    /**
     *  �������������к�ִ��
     *
     *  @author Garbin
     *  @return void
     */
    function destruct()
    {
    }

    /**
     * ��csv�ļ�����
     *
     * @param string $filename �ļ���
     * @param bool $header �Ƿ��б����У�����б����У��ӵڶ��п�ʼ������
     * @param string $from_charset Դ����
     * @param string $to_charset Ŀ�����
     * @param string $delimiter �ָ���
     * @return array
     */
    function import_from_csv($filename, $header = true, $from_charset = '', $to_charset = '', $delimiter= ',')
    {
        if ($from_charset && $to_charset && $from_charset != $to_charset)
        {
            $need_convert = true;
            import('iconv.lib');
            $iconv = new Chinese(ROOT_PATH . '/');
        }
        else
        {
            $need_convert = false;
        }

        $data = array();
        setlocale (LC_ALL, array ('zh_CN.gbk', 'zh_CN.gb2312', 'zh_CN.gb18030')); // ���linuxϵͳfgetcsv����GBK�ļ�ʱ���ܲ��������bug
        $handle = fopen($filename, "r");
        while (($row = fgetcsv($handle, 100000, $delimiter)) !== FALSE) {
            if ($need_convert)
            {
                foreach ($row as $key => $col)
                {
                    $row[$key] = $iconv->Convert($from_charset, $to_charset, $col);
                }
            }
            $data[] = $row;
        }
        fclose($handle);

        if ($header && $data)
        {
            array_shift($data);
        }

        return addslashes_deep($data);
    }

    /**
     * ����csv�ļ�
     *
     * @param array $data ���ݣ������Ҫ���б���Ҳ���������
     * @param string $filename �ļ�����������չ����
     * @param string $to_charset Ŀ�����
     */
    function export_to_csv($data, $filename, $to_charset = '')
    {
        if ($to_charset && $to_charset != 'utf-8')
        {
            $need_convert = true;
            import('iconv.lib');
            $iconv = new Chinese(ROOT_PATH . '/');
        }
        else
        {
            $need_convert = false;
        }

        header("Content-type: application/unknown");
        header("Content-Disposition: attachment; filename={$filename}.csv");
        foreach ($data as $row)
        {
            foreach ($row as $key => $col)
            {

                if ($need_convert)
                {
                    $col = $iconv->Convert('utf-8', $to_charset, $col);
                }
                $row[$key] = $this->_replace_special_char($col);

            }
            echo join(',', $row) . "\r\n";
        }
    }

    /**
     * �滻Ӱ��csv�ļ����ַ�
     *
     * @param $str string �����ַ���
     */
    function _replace_special_char($str, $replace = true)
    {
        $str = str_replace("\r\n", "", $str);
        $str = str_replace("\t", "    ", $str);
        $str = str_replace("\n", "", $str);
        if ($replace == true)
        {
            $str = '"' . str_replace('"', '""', $str) . '"';
        }
        return $str;
    }

}

?>