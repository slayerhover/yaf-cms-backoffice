<?php 
ini_set('date.timezone','Asia/Shanghai');
require_once "./lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
include 'db.class.php';

$token = isset($_GET['token'])?$_GET['token']:'';
$token = addslashes($token);
if(empty($token)){
	$result = array(
		'err_msg'	=>	'参数为空',
		'url'		=>	'',
	);
	echo "<script>alert('" . $result['err_msg'] . "'); history.back(-1);</script>";
	exit(200);
}	
$dbconfig = array(
							'dsn'         =>    'mysql:host=192.168.0.21:3309;dbname=scsj',
							'name'        =>    'rootuser',
							'password'    =>    'asdfasdf',
);
$_DB = new DB($dbconfig);
$user= $_DB->getRow("select id,phone,baozhengjin_status from t_user where token='{$token}'");
if(empty($user)){
	$result = array(
		'err_msg'	=>	'请重新登陆.',
		'url'		=>	'',
	);
	echo "<script>alert('" . $result['err_msg'] . "'); history.back(-1);</script>";
	exit(200);
}

$tools = new JsApiPay();
$openId = $tools->GetOpenid(urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']));	
?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
    <title>绑定微信</title>    
	<link rel="stylesheet" href="/wxpay/h5/public/css/weui.min.css">
    <link rel="stylesheet" href="/wxpay/h5/public/css/jquery-weui.css">
    <link rel="stylesheet" type="text/css" href="/wxpay/h5/public/css/reset.css">    
    <link rel="stylesheet" type="text/css" href="/wxpay/h5/static/css/ensureMoney.css">
</head>
<body>
</head>

<body>

    <div class="container">
        <!-- 头部 -->
        <header style="position:relative;">
            <span>绑定微信中...</span>
        </header>
        <div class="main">            
            <div class="variety">
                <div class="wx">
                    <img src="../static/images/wx_icon.png" alt="">
                    <span>绑定微信</span>
                    <input type="radio" name="payList" class="wx_radio fr" checked>
                </div>               
            </div>
            <a class="confirm" >
                <input type="button" value="绑定" id="btn" disabled style="background-color:#ccc">
            </a>
        </div>
    </div>
</body>

</html>