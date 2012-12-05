<?php

function &cache_server()
{
    import('cache.lib');
    static $CS = null;
    if ($CS === null)
    {
        switch (CACHE_SERVER)
        {
            case 'memcached':
                $CS = new MemcacheServer(array(
                    'host'  => MEMCACHE_HOST,
                    'port'  => MEMCACHE_PORT,
                ));
            break;
            default:
                $CS = new PhpCacheServer;
                $CS->set_cache_dir(ROOT_PATH . '/temp/caches');
            break;
        }
    }

    return $CS;
}

/**
 *    ��ȡ��Ʒ���Ͷ���
 *
 *    @author    Garbin
 *    @param     string $type
 *    @param     array  $params
 *    @return    void
 */
function &gt($type, $params = array())
{
    static $types = array();
    if (!isset($types[$type]))
    {
        /* ���ض������ͻ����� */
        include_once(ROOT_PATH . '/includes/goods.base.php');
        include(ROOT_PATH . '/includes/goodstypes/' . $type . '.gtype.php');
        $class_name = ucfirst($type) . 'Goods';
        $types[$type]   =   new $class_name($params);
    }

    return $types[$type];
}

/**
 *    ��ȡ�������Ͷ���
 *
 *    @author    Garbin
 *    @param    none
 *    @return    void
 */
function &ot($type, $params = array())
{
    static $order_type = null;
    if ($order_type === null)
    {
        /* ���ض������ͻ����� */
        include_once(ROOT_PATH . '/includes/order.base.php');
        include(ROOT_PATH . '/includes/ordertypes/' . $type . '.otype.php');
        $class_name = ucfirst($type) . 'Order';
        $order_type = new $class_name($params);
    }
    return $order_type;
}

/**
 *    ��ȡ�����ļ�����
 *
 *    @author    Garbin
 *    @param     string $type
 *    @param     array  $params
 *    @return    void
 */
function &af($type, $params = array())
{
    static $types = array();
    if (!isset($types[$type]))
    {
        /* ���������ļ������� */
        include_once(ROOT_PATH . '/includes/arrayfile.base.php');
        include(ROOT_PATH . '/includes/arrayfiles/' . $type . '.arrayfile.php');
        $class_name = ucfirst($type) . 'Arrayfile';
        $types[$type]   =   new $class_name($params);
    }
    return $types[$type];
}

/**
 *    ���ӻ�Աϵͳ
 *
 *    @author    Garbin
 *    @return    Passport ��Աϵͳ���ӽӿ�
 */
function &ms()
{
    static $ms = null;
    if ($ms === null)
    {
        include(ROOT_PATH . '/includes/passport.base.php');
        include(ROOT_PATH . '/includes/passports/' . MEMBER_TYPE . '.passport.php');
        $class_name  = ucfirst(MEMBER_TYPE) . 'Passport';
        $ms = new $class_name();
    }

    return $ms;
}


/**
 *    ��ȡ�û�ͷ���ַ
 *
 *    @author    Garbin
 *    @param     string $portrait
 *    @return    void
 */
function portrait($user_id, $portrait, $size = 'small')
{
    switch (MEMBER_TYPE)
    {
        case 'uc':
            return UC_API . '/avatar.php?uid=' . $user_id . '&amp;size=' . $size;
        break;
        default:
            return empty($portrait) ? IMAGE_URL.Conf::get('default_user_portrait') : IMAGE_URL.$portrait;
        break;
    }
}

/**
 *    ��ȡ��������
 *
 *    @author    Garbin
 *    @param     string $key
 *    @param     mixed  $val
 *    @return    mixed
 */
function &env($key, $val = null)
{
    !isset($GLOBALS['EC_ENV']) && $GLOBALS['EC_ENV'] = array();
    $vkey = $key ? strtokey("{$key}", '$GLOBALS[\'EC_ENV\']') : '$GLOBALS[\'EC_ENV\']';
    if ($val === null)
    {
        /* ���ظ�ָ���������� */
        $v = eval('return isset(' . $vkey . ') ? ' . $vkey . ' : null;');

        return $v;
    }
    else
    {
        /* ����ָ���������� */
        eval($vkey . ' = $val;');
        return $val;
    }
}

/**
 *    ��ȡ����״̬��Ӧ�����ֱ���
 *
 *    @author    Garbin
 *    @param     int $order_status
 *    @return    string
 */
function order_status($order_status)
{
    $lang_key = '';
    switch ($order_status)
    {
        case ORDER_PENDING:
            $lang_key = 'order_pending';
        break;
        case ORDER_SUBMITTED:
            $lang_key = 'order_submitted';
        break;
        case ORDER_ACCEPTED:
            $lang_key = 'order_accepted';
        break;
        case ORDER_SHIPPED:
            $lang_key = 'order_shipped';
        break;
        case ORDER_FINISHED:
            $lang_key = 'order_finished';
        break;
        case ORDER_CANCELED:
            $lang_key = 'order_canceled';
        break;
        case ORDER_REFUND:
       		$lang_key = 'order_refund';
      	break;
        case ORDER_REFUND_FINISH:
        	$lang_key = 'order_refund_finish';
        break;
    }

    return $lang_key  ? Lang::get($lang_key) : $lang_key;
}

/**
 *    ת������״ֵ̬
 *
 *    @author    Garbin
 *    @param     string $order_status_text
 *    @return    void
 */
function order_status_translator($order_status_text)
{
    switch ($order_status_text)
    {
        case 'canceled':    //��ȡ���Ķ���
            return ORDER_CANCELED;
        break;
        case 'all':         //���ж���
            return '';
        break;
        case 'pending':     //������Ķ���
            return ORDER_PENDING;
        break;
        case 'submitted':   //���ύ�Ķ���
            return ORDER_SUBMITTED;
        break;
        case 'accepted':    //��ȷ�ϵĶ������������Ķ���
            return ORDER_ACCEPTED;
        break;
        case 'shipped':     //�ѷ����Ķ���
            return ORDER_SHIPPED;
        break;
        case 'finished':    //����ɵĶ���
            return ORDER_FINISHED;
        break;
        default:            //���ж���
            return '';
        break;
    }
}

/**
 *    ��ȡ�ʼ�����
 *
 *    @author    Garbin
 *    @param     string $mail_tpl
 *    @param     array  $var
 *    @return    array
 */
function get_mail($mail_tpl, $var = array())
{
    $subject = '';
    $message = '';

    /* ��ȡ�ʼ�ģ�� */
    $model_mailtemplate =& af('mailtemplate');
    $tpl_info   =   $model_mailtemplate->getOne($mail_tpl);
    if (!$tpl_info)
    {
        return false;
    }

    /* �������б��� */
    $tpl =& v(true);
    $tpl->direct_output = true;
    $tpl->assign('site_name', Conf::get('site_name'));
    $tpl->assign('site_url', SITE_URL);
    $tpl->assign('mail_send_time', local_date('Y-m-d H:i', gmtime()));
    foreach ($var as $key => $val)
    {
        $tpl->assign($key, $val);
    }
    $subject = $tpl->fetch('str:' . $tpl_info['subject']);
    $message = $tpl->fetch('str:' . $tpl_info['content']);

    /* �����ʼ� */

    return array(
        'subject'   => $subject,
        'message'   => $message
    );
}

/**
 *    ��ȡ��Ϣ����
 *
 *    @author    Garbin
 *    @param     string $msg_tpl
 *    @param     array  $var
 *    @return    string
 */
function get_msg($msg_tpl, $var = array())
{
    /* ��ȡ��Ϣģ�� */
    $ms = &ms();
    $msg_content = Lang::get($msg_tpl);
    $var['site_url'] = SITE_URL; // ������Ϣģ��������һ��site_url����
    $search = array_keys($var);
    $replace = array_values($var);

    /* �������б��� */
    array_walk($search, create_function('&$str', '$str = "{\$" . $str. "}";'));
    $msg_content = str_replace($search, $replace, $msg_content);
    return $msg_content;
}

/**
 *    ��ȡ�ʼ���������
 *
 *    @author    Garbin
 *    @return    object
 */
function &get_mailer()
{
    static $mailer = null;
    if ($mailer === null)
    {
        /* ʹ��mailer�� */
        import('mailer.lib');
        $sender     = Conf::get('site_name');
        $from       = Conf::get('email_addr');
        $protocol   = Conf::get('email_type');
        $host       = Conf::get('email_host');
        $port       = Conf::get('email_port');
        $username   = Conf::get('email_id');
        $password   = Conf::get('email_pass');
        $mailer = new Mailer($sender, $from, $protocol, $host, $port, $username, $password);
    }

    return $mailer;
}

/**
 *    ģ���б�
 *
 *    @author    Garbin
 *    @param     strong $who
 *    @return    array
 */
function list_template($who)
{
    $theme_dir = ROOT_PATH . '/themes/' . $who;
    $dir = dir($theme_dir);
    $array = array();
    while (($item  = $dir->read()) !== false)
    {
        if (in_array($item, array('.', '..')) || $item{0} == '.' || $item{0} == '$')
        {
            continue;
        }
        $theme_path = $theme_dir . '/' . $item;
        if (is_dir($theme_path))
        {
            if (is_file($theme_path . '/theme.info.php'))
            {
                $array[] = $item;
            }
        }
    }

    return $array;
}

/**
 *    �б���
 *
 *    @author    Garbin
 *    @param     string $who
 *    @return    array
 */
function list_style($who, $template = 'default')
{
    $style_dir = ROOT_PATH . '/themes/' . $who . '/' . $template . '/styles';
    $dir = dir($style_dir);
    $array = array();
    while (($item  = $dir->read()) !== false)
    {
        if (in_array($item, array('.', '..')) || $item{0} == '.' || $item{0} == '$')
        {
            continue;
        }
        $style_path = $style_dir . '/' . $item;
        if (is_dir($style_path))
        {
            if (is_file($style_path . '/style.info.php'))
            {
                $array[] = $item;
            }
        }
    }

    return $array;
}


/**
 *    ��ȡ�Ҽ��б�
 *
 *    @author    Garbin
 *    @return    array
 */
function list_widget()
{
    $widget_dir = ROOT_PATH . '/external/widgets';
    static $widgets    = null;
    if ($widgets === null)
    {
        $widgets = array();
        if (!is_dir($widget_dir))
        {
            return $widgets;
        }
        $dir = dir($widget_dir);
        while (false !== ($entry = $dir->read()))
        {
            if (in_array($entry, array('.', '..')) || $entry{0} == '.' || $entry{0} == '$')
            {
                continue;
            }
            if (!is_dir($widget_dir . '/' . $entry))
            {
                continue;
            }
            $info = get_widget_info($entry);
            $widgets[$entry] = $info;
        }
    }

    return $widgets;
}

/**
 *    ��ȡ�Ҽ���Ϣ
 *
 *    @author    Garbin
 *    @param     string $id
 *    @return    array
 */
function get_widget_info($name)
{
    $widget_info_path = ROOT_PATH . '/external/widgets/' . $name . '/widget.info.php';

    return include($widget_info_path);
}

function i18n_code()
{
    $code = 'zh-CN';
    $lang_code = substr(LANG, 0, 2);
    switch ($lang_code)
    {
        case 'sc':
            $code = 'zh-CN';
        break;
        case 'tc':
            $code = 'zh-TW';
        break;
        default:
            $code = 'zh-CN';
        break;
    }

    return $code;
}

/**
 *    ���ַ�����ȡָ�����ڵĽ���ʱ��(24:00)
 *
 *    @author    Garbin
 *    @param     string $str
 *    @return    int
 */
function gmstr2time_end($str)
{
    return gmstr2time($str) + 86400;
}

/**
 *    ��ȡURL��ַ
 *
 *    @author    Garbin
 *    @param     mixed $query
 *    @param     string $rewrite_name
 *    @return    string
 */
function url($query, $rewrite_name = null)
{
    $re_on  = Conf::get('rewrite_enabled');
    $url = '';
    if (!$re_on)
    {
        /* Rewriteδ���� */
        $url = 'index.php?' . $query;
    }
    else
    {
        /* Rewrite�ѿ��� */
        $re =& rewrite_engine();
        $rewrite = $re->get($query, $rewrite_name);

        $url = ($rewrite !== false) ? $rewrite : 'index.php?' . $query;
    }

    return str_replace('&', '&amp;', $url);
}

/**
 *    ��ȡrewrite engine
 *
 *    @author    Garbin
 *    @return    Object
 */
function &rewrite_engine()
{
    $re_name= Conf::get('rewrite_engine');
    static $re = null;
    if ($re === null)
    {
        include(ROOT_PATH . '/includes/rewrite.base.php');
        include(ROOT_PATH . '/includes/rewrite_engines/' . $re_name . '.rewrite.php');
        $re_class_name = ucfirst($re_name) . 'Rewrite';
        $re = new $re_class_name();
    }

    return $re;
}

/**
 *    ת���Ź��״ֵ̬
 *
 *    @author    Garbin
 *    @param     string $status_text
 *    @return    void
 */
function groupbuy_state_translator($state_text)
{
    switch ($state_text)
    {
        case 'all':         //ȫ���Ź��
            return '';
        break;
        case 'on':         //�����е��Ź��
            return GROUP_ON;
        break;
        case 'canceled':    //��ȡ�����Ź��
            return GROUP_CANCELED;
        break;
        case 'pending':     //δ�������Ź��
            return GROUP_PENDING;
        break;
        case 'finished':     //����ɵ��Ź��
            return GROUP_FINISHED;
        break;
        case 'end':     //����ɵ��Ź��
            return GROUP_END;
        break;
        default:            //ȫ���Ź��
            return '';
        break;
    }
}

/**
 *    ��ȡ�Ź�״̬��Ӧ�����ֱ���
 *
 *    @author    Garbin
 *    @param     int $group_state
 *    @return    string
 */
function group_state($group_state)
{
    $lang_key = '';
    switch ($group_state)
    {
        case GROUP_PENDING:
            $lang_key = 'group_pending';
        break;
        case GROUP_ON:
            $lang_key = 'group_on';
        break;
        case GROUP_CANCELED:
            $lang_key = 'group_canceled';
        break;
        case GROUP_FINISHED:
            $lang_key = 'group_finished';
        break;
        case GROUP_END:
            $lang_key = 'group_end';
        break;
    }

    return $lang_key  ? Lang::get($lang_key) : $lang_key;
}


/**
 *    ����ʣ��ʱ��
 *
 *    @author    Garbin
 *    @param     string $format
 *    @param     int $time;
 *    @return    string
 */
function lefttime($time, $format = null)
{
    $lefttime = $time - gmtime();
    if ($lefttime < 0)
    {
        return '';
    }
    if ($format === null)
    {
        if ($lefttime < 3600)
        {
            $format = Lang::get('lefttime_format_1');
        }
        elseif ($lefttime < 86400)
        {
            $format = Lang::get('lefttime_format_2');
        }
        else
        {
            $format = Lang::get('lefttime_format_3');
        }
    }
    $d = intval($lefttime / 86400);
    $lefttime -= $d * 86400;
    $h = intval($lefttime / 3600);
    $lefttime -= $h * 3600;
    $m = intval($lefttime / 60);
    $lefttime -= $m * 60;
    $s = $lefttime;

    return str_replace(array('%d', '%h', '%i', '%s'),array($d, $h,$m, $s), $format);
}


/**
 * ��ά�������򣨶������ļ��������ݣ�
 *
 * @author Hyber
 * @param array $array
 * @param array $cols
 * @return array
 *
 * e.g. $data = array_msort($data, array('sort_order'=>SORT_ASC, 'add_time'=>SORT_DESC));
 */
function array_msort($array, $cols)
{
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
    }
    $eval = substr($eval,0,-1).');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k,1);
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;
}

/**
 * ����Ϣ����
 *
 * @return string
 */
function short_msg_filter($string)
{
    $ms = & ms();
    return $ms->pm->msg_filter($string);
}

?>
