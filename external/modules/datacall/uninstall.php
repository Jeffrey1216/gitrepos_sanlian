<?php

/**
 * ������Է�һЩж��ģ��ʱ��Ҫִ�еĴ��룬����ɾ����ɾ��Ŀ¼���ļ�֮���
 */

$filename = ROOT_PATH . '/data/datacall.inc.php';
if (file_exists($filename))
{
    @unlink($filename);
}

?>