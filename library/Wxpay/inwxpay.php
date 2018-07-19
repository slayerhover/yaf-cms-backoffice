<?php 
ini_set('date.timezone','Asia/Shanghai');
require_once "./lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
include 'db.class.php';
#require_once 'log.php';
//初始化日志
#$logHandler= new CLogFileHandler("./log/".date('Y-m-d').'.log');
#$log = Log::Init($logHandler, 15);
	$token = isset($_GET['tocken'])?$_GET['tocken']:'';
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
	$total_fee		= 10000.00;		
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
	$orders = $_DB->getRow("select * from orders where user_id='{$user['id']}' and type=1 and pay_status=1 and pay_type=1");
	if(!empty($orders)){
		$out_trade_no	= $orders['out_trade_no'] . mt_rand(10000, 99999);
	}else{
		$out_trade_no	= date('YmdHis') . mt_rand(100000, 999999);
		$order = [
			'out_trade_no' => $out_trade_no,
			'total_amount' => $total_fee,
			'subject'      => '保证金',		
			'pay_type'	   => 1,
			'user_id'	   => $user['id'],
			'type'		   => 1,
			'pay_status'   => 1,
			'gateway_type'  => 2,
			'created_at'   => date('Y-m-d H:i:s'),
		];
		if($_DB->insert('orders',$order)===FALSE){
			$result = array(
				'err_msg'	=>	'插入订单失败',
				'url'		=>	'',
			);
			exit($result['err_msg']);
		}
	}	
	$tools = new JsApiPay();
	$openId = $tools->GetOpenid(urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));	
	$input = new WxPayUnifiedOrder();
	$input->SetBody("保证金");
	$input->SetAttach("保证金");
	$input->SetOut_trade_no($out_trade_no);
	$input->SetTotal_fee(10000*100);
	$input->SetTime_start(date("YmdHis"));
	$input->SetTime_expire(date("YmdHis", time() + 600));
	$input->SetGoods_tag("保证金");
	$input->SetNotify_url("http://h5.scsj.net.cn/wxpay/notify_url.php");
	$input->SetTrade_type("JSAPI");
	$input->SetOpenid($openId);
	$order = WxPayApi::unifiedOrder($input);
	$jsApiParameters = $tools->GetJsApiParameters($order);	
?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
    <title>微信支付</title>
    <script type="text/javascript">
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){				
				WeixinJSBridge.log(res.err_msg);				
				if(res.err_msg=='get_brand_wcpay_request:ok'){					
					alert('支付成功.');
					window.location.href="http://h5.scsj.net.cn/wxpay/h5/templates/personal.html";
				}else{
					alert('支付未成功.');
					window.location.href="http://h5.scsj.net.cn/wxpay/h5/templates/ensureMoney.html";
				}
			}
		);
	}
	function callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}
	callpay();
	</script>	
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
                <div class="wx">
                    <img src="../static/images/wx_icon.png" alt="">
                    <span>微信支付</span>
                    <input type="radio" name="payList" class="wx_radio fr" checked>
                </div>               
            </div>
            <a class="confirm" >
                <input type="button" value="支付" id="btn" disabled style="background-color:#ccc">
            </a>
        </div>
    </div>
</body>

</html>