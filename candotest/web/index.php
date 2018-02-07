<?php

// 定义应用目录
define('APP_PATH','./Apps/');

/* 自定义绑定的应用名称，如果此处定义之后，url访问此应用下的控制器时，则不需要填写应用的名称，
 * 如admin下的index控制器，url可直接写为：http://ServerName/index.php/Index/index
 */
define('BIND_MODULE','Api');

// 定义运行时目录
define('RUNTIME_PATH','./Runtime/');

//关闭目录安全文件的生成(index.html)
define('BUILD_DIR_SECURE', false);

// 开启调试模式
define('APP_DEBUG', true);

//定义ThinkPHP的目录位置
define("THINK_PATH", realpath("./ThinkPHP")."/");

// 更名框架目录名称，并载入框架入口文件
require THINK_PATH . 'ThinkPHP.php';
