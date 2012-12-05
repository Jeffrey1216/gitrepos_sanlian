<?php

/**
 *    ������������
 *
 *    @author    Garbin
 */

/* ��ȡ�������� */
$subdomain = get_subdomain();

/* û�ж��������������� */
if ($subdomain === false)
{
    return;
}

/* ������������δ������������ */
if (!ENABLED_SUBDOMAIN)
{
    //header('Location:' . SITE_URL);
    return;
}

/* ������Ӧ�Ķ�����������Ӧ�ĵ����� */
$store_id = get_subdomain_store_id($subdomain);
if ($store_id === false)
{
    /* ��Ч�Ķ������� */
    header('Location:' . SITE_URL);
    return;
}

/* Ŀǰֻ֧�ֵ�����ҳ�������� */
define('SUBDOMAIN', $subdomain);
$_GET['app'] = $_REQUEST['app'] = 'store';
$_GET['act'] = $_REQUEST['act'] = 'index';
$_GET['id'] = $_REQUEST['id'] = $store_id;


/**
 *    ��ȡ�Զ����������
 *
 *    @author    Garbin
 *    @return    string     �ɹ�
 *               false      ʧ��
 */
function get_subdomain()
{
    $curr_url_info = parse_url(get_domain());
    $main_url_info = parse_url(SITE_URL);
    $curr_domain = strtolower($curr_url_info['host']);
    $main_domain = strtolower($main_url_info['host']);
    if ($curr_domain == $main_domain)
    {
        /* ��ǰ�������Ƕ������� */
        return false;
    }
    $tmp = explode('.', $curr_domain);

    return $tmp[0];
}

/**
 *    ��ȡ����������Ӧ�ĵ���ID
 *
 *    @author    Garbin
 *    @param     string $subdomain
 *    @return    int    �ɹ�
 *               false  ʧ��
 */
function get_subdomain_store_id($subdomain)
{
    #TODO ��ȡ��Ӧ�ĵ���ID
    $model_store =& m('store');
    $store_info = $model_store->get(array(
        'conditions'    => "domain='{$subdomain}'",
        'join'          => 'belongs_to_sgrade',
        'fields'        => 'store_id, functions',
    ));
    if (empty($store_info))
    {
        return false;
    }
    /* �ȼ�������ʹ�ö������� */
    if (!in_array('subdomain', explode(',', $store_info['functions'])))
    {
        return false;
    }

    return $store_info['store_id'];
}

?>
