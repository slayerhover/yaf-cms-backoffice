<?php
/**
 * 命令行请求入口
 *
 * Created by IntelliJ IDEA.
 * User: chenzhidong
 * Date: 13-12-5
 * Time: 上午11:43
 */
define('APP_PATH', dirname(__FILE__));

if (!extension_loaded("yaf"))
{
	include(APP_PATH . '/framework/loader.php');
}
$application = new Yaf_Application(APP_PATH . "/conf/application.ini");
$application->bootstrap()->getDispatcher()->dispatch(new Yaf_Request_Simple());
?>
