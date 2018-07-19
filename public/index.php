<?php
header('content-type:text/html;charset=utf-8');
date_default_timezone_set('PRC');
define('APP_PATH', dirname(__FILE__).'/..');

$application = new Yaf_Application(APP_PATH . "/conf/app.ini");
$application->bootstrap()->run();
