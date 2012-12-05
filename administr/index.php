<?php

/* Ӧ�ø�Ŀ¼ */
define('APP_ROOT', dirname(__FILE__));          //�ó���ֻ�ں�̨ʹ��
define('ROOT_PATH', dirname(APP_ROOT));   //�ó�����CoreҪ���
define('IN_BACKEND', true);
include(ROOT_PATH . '/core/paila.php');

/* ����������Ϣ */
pl_define(ROOT_PATH . '/data/config.inc.php');

/* ����ECMall */
PaiLa::startup(array(
    'default_app'   =>  'default',
    'default_act'   =>  'index',
    'app_root'      =>  APP_ROOT . '/app',
    'external_libs' =>  array(
        ROOT_PATH . '/includes/global.lib.php',
        ROOT_PATH . '/includes/libraries/time.lib.php',
        ROOT_PATH . '/includes/ecapp.base.php',
        ROOT_PATH . '/includes/plugin.base.php',
        APP_ROOT . '/app/backend.base.php',
        ROOT_PATH . '/includes/libraries/typedef.func.php',
    ),
));

?>