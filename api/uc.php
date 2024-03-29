<?php

/* 应用根目录 */
define('APP_ROOT', dirname(__FILE__));
define('ROOT_PATH', dirname(APP_ROOT));
include(ROOT_PATH . '/core/paila.php');

/* 定义配置信息 */
pl_define(ROOT_PATH . '/data/config.inc.php');

/* 启动ECMall */
PaiLa::startup(array(
    'default_app'   =>  'uc',
    'default_act'   =>  'index',
    'app_root'      =>  APP_ROOT,
    'external_libs' =>  array(
        ROOT_PATH . '/includes/global.lib.php',
        ROOT_PATH . '/includes/libraries/time.lib.php',
        ROOT_PATH . '/includes/ecapp.base.php',
        ROOT_PATH . '/includes/plugin.base.php',
        APP_ROOT  . '/api.base.php',
    ),
));

?>