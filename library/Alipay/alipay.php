<?php
header("Content-type: text/html; charset=utf-8");
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'wappay/service/AlipayTradeService.php';
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'config.php';
include 'db.class.php';

	$token = isset($_GET['token'])?$_GET['token']:'';
	$token = addslashes($token);
	if(empty($token)){
		$result = array(
			'err_msg'	=>	'参数为空',
			'url'		=>	'',
		);
		exit($result['err_msg']);
	}	
	$dbconfig = array(
					'dsn'         =>    'mysql:host=rdsproxy56.rdsprwt7mveezzq.rds.bj.baidubce.com;dbname=scsj',
					'name'        =>    'scsj_proxy_root',
					'password'    =>    'eccbd73df5ff4848aab5fd29069f5530',
	);
	$_DB = new DB($dbconfig);
	$total_fee		= 0.01;		
	$user= $_DB->getRow("select id,phone,baozhengjin_status from t_user where token='{$token}'");
	if(empty($user)){
		$result = array(
			'err_msg'	=>	'请重新登陆.',
			'url'		=>	'',
		);
		exit($result['err_msg']);
	}	
	if($user['baozhengjin_status']>1){
		$result = array(
			'err_msg'	=>	'请勿重复支付.',
			'url'		=>	'',
		);
		exit($result['err_msg']);
	}	 
	$orders = $_DB->getRow("select * from orders where user_id='{$user['id']}' and type=1 and pay_status=1 and pay_type=2");
	if(!empty($orders)){
		$out_trade_no	= $orders['out_trade_no'];
	}else{
		$out_trade_no	= date('YmdHis') . mt_rand(100000, 999999);
		$orders = [
			'out_trade_no' => $out_trade_no,
			'total_amount' => $total_fee,
			'subject'      => '保证金',		
			'pay_type'	   => 2,
			'user_id'	   => $user['id'],
			'type'		   => 1,
			'pay_status'   => 1,
			'gateway_type'  => 2,
			'created_at'   => date('Y-m-d H:i:s'),
		];
		if($_DB->insert('orders',$orders)===FALSE){
			$result = array(
				'err_msg'	=>	'插入订单失败',
				'url'		=>	'',
			);
			exit($result['err_msg']);
		}
	}

//超时时间
$timeout_express="1m";
$payRequestBuilder = new AlipayTradeWapPayContentBuilder();
$payRequestBuilder->setBody($orders['subject']);
$payRequestBuilder->setSubject($orders['subject']);
$payRequestBuilder->setOutTradeNo($out_trade_no);
$payRequestBuilder->setTotalAmount($orders['total_amount']);
$payRequestBuilder->setTimeExpress($timeout_express);

$payResponse = new AlipayTradeService($config);
$result=$payResponse->wapPay($payRequestBuilder,$config['return_url'],$config['notify_url']);
return ;
?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
    <title>支付宝支付</title>    
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
            <span>支付中...</span>
        </header>
        <div class="main">
            <p style="text-align:center;height: 0.9rem;line-height:0.9rem;font-weight:700">支付保证金</p>
            <p style="text-align:center;height: 1.2rem;line-height:1.2rem;font-size:0.38rem;font-weight:700">￥10,000.00</p>
            <div class="infor">
                <span class="fl">收款方</span>
                <span class="fr">商超世界电子商务有限公司</span>
            </div>
            <div class="variety">
				<div class="ali">
                    <img src="../static/images/ali_icon.png" alt="">
                    <span>支付宝支付</span>
                    <input type="radio" name="payList" class="ali_radio fr" checked>
                </div>
            </div>
            <a class="confirm" >
                <input type="button" value="支付" id="btn" disabled style="background-color:#ccc">
            </a>
        </div>
    </div>
</body>

</html>