<?php

/* 应用根目录 */
define('APP_ROOT', dirname(__FILE__));          //该常量只在后台使用
define('ROOT_PATH', dirname(APP_ROOT));   //该常量是Core要求的
define('IN_BACKEND', true);
include(ROOT_PATH . '/core/paila.php');

/* 定义配置信息 */
pl_define(ROOT_PATH . '/data/config.inc.php');

/* 启动ECMall */
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