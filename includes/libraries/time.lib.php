<?php

/**
 * PaiLa: ʱ�亯����
 * ============================================================================
 * ��Ȩ���� (C) 2010-2011 �����ڴ�������������Ȩ����
 * ��վ��ַ: http://www.paila.com
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: time.lib.php 7634 2009-04-30 03:25:46Z yelin $
 */

if (!defined('IN_PL'))
{
    die('Hacking attempt');
}

/**
 * ��õ�ǰ��������ʱ���ʱ���
 *
 * @return  integer
 */
function gmtime()
{
    return time();
}

/**
 * ��÷�������ʱ��
 *
 * @return  integer
 */
function server_timezone()
{
    if (function_exists('date_default_timezone_get'))
    {
        return date_default_timezone_get();
    }
    else
    {
        return date('Z') / 3600;
    }
}


/**
 *  ����һ���û��Զ���ʱ�����ڵ�GMTʱ���
 *
 * @access  public
 * @param   int     $hour
 * @param   int     $minute
 * @param   int     $second
 * @param   int     $month
 * @param   int     $day
 * @param   int     $year
 *
 * @return void
 */
function local_mktime($hour = NULL , $minute= NULL, $second = NULL,  $month = NULL,  $day = NULL,  $year = NULL)
{
    $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];

    /**
    * $time = mktime($hour, $minute, $second, $month, $day, $year) - date('Z') + (date('Z') - $timezone * 3600)
    * ����mktime����ʱ������ټ�ȥdate('Z')ת��ΪGMTʱ�䣬Ȼ������Ϊ�û��Զ���ʱ�䡣�����ǻ������
    **/
    $time = mktime($hour, $minute, $second, $month, $day, $year) - $timezone * 3600;

    return $time;
}


/**
 * ��GMTʱ�����ʽ��Ϊ�û��Զ���ʱ������
 *
 * @param  string       $format
 * @param  integer      $time       �ò���������һ��GMT��ʱ���
 *
 * @return  string
 */

function local_date($format, $time = NULL)
{
    /* ������ʱ��û���û��Զ���ʱ���Ĺ��ܣ����е�ʱ���������̳ǵ������� */
    $timezone = Conf::get('time_zone');

    if ($time === NULL)
    {
        $time = gmtime();
    }
    elseif ($time <= 0)
    {
        return '';
    }

//    $time += (8 * 3600); //---��ʽ��ʱ�������ڼ���ʱ��ʱ�䡣
    return date($format, $time);
}

/**
 * ����ָ�������е�ʱ�䲢�����ʽ��
 *
 * @param   array   $item
 * @param   string  $key
 * @param   string  $format
 *
 * @return  mix
 */
function deep_local_date(&$arr, $key, $format)
{
    $func = create_function('&$arr', '$arr[\'' .$key. '\'] = $arr[\'' .$key. '\'] > 0 ? local_date(\'' .$format. '\', $arr[\'' .$key. '\']) : \'N/A\';');
    array_walk($arr, $func);

    return $arr;
}



/**
 * ת���ַ�����ʽ��ʱ����ʽΪGMTʱ���
 *
 * @param   string  $str
 *
 * @return  integer
 */
function gmstr2time($str)
{
    $time = strtotime($str);

    if ($time > 0)
    {
        $time -= 0;
    }

    return $time;
}

/**
 *  ��һ���û��Զ���ʱ��������תΪGMTʱ���
 *
 * @access  public
 * @param   string      $str
 *
 * @return  integer
 */
function local_strtotime($str)
{
    $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];

    /**
    * $time = mktime($hour, $minute, $second, $month, $day, $year) - date('Z') + (date('Z') - $timezone * 3600)
    * ����mktime����ʱ������ټ�ȥdate('Z')ת��ΪGMTʱ�䣬Ȼ������Ϊ�û��Զ���ʱ�䡣�����ǻ������
    **/
    $time = strtotime($str) - $timezone * 3600;

    return $time;

}

/**
 * ����û�����ʱ��ָ����ʱ���
 *
 * @param   $timestamp  integer     ��ʱ���������һ�����������ص�ʱ���
 *
 * @return  array
 */
function local_gettime($timestamp = NULL)
{
    $tmp = local_getdate($timestamp);
    return $tmp[0];
}

/**
 * ����û�����ʱ��ָ�������ں�ʱ����Ϣ
 *
 * @param   $timestamp  integer     ��ʱ���������һ�����������ص�ʱ���
 *
 * @return  array
 */
function local_getdate($timestamp = NULL)
{
    $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];

    /* ���ʱ���Ϊ�գ����÷������ĵ�ǰʱ�� */
    if ($timestamp === NULL)
    {
        $timestamp = time();
    }

    $gmt        = $timestamp - date('Z');       // �õ���ʱ��ĸ�������ʱ��
    $local_time = $gmt + ($timezone * 3600);    // ת��Ϊ�û�����ʱ����ʱ���

    return getdate($local_time);
}

?>