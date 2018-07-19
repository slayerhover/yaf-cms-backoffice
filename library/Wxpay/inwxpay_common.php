<?php 
require_once "./lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
function  log_result($file,$word) 
{
	$fp = fopen($file,"a+");
	flock($fp, LOCK_EX) ;
	fwrite($fp,"执行日期：".strftime("%Y-%m-%d %H:%M:%S",time())."\n".$word."\r\n");
	flock($fp, LOCK_UN);
	fclose($fp);
}
$log_name="./log/".date("Y-m-d").".log";//log文件路径
log_result($log_name,"【接收到的参数】:\n".json_encode($_REQUEST)."\n");
try{	
	$total_fee		=	isset($_REQUEST['total_fee'])?$_REQUEST['total_fee']:'';	
	$out_trade_no	=	isset($_REQUEST['orders_no'])?$_REQUEST['orders_no']:'';
	$subject		=	isset($_REQUEST['subject'])?$_REQUEST['subject']:'';
	$return_url		=	isset($_REQUEST['return_url'])?$_REQUEST['return_url']:'';
	$notify_url		=	isset($_REQUEST['notify_url'])?$_REQUEST['notify_url']:'';
	if( empty($total_fee) ){	exit('支付金额为空');	}
	if( empty($out_trade_no) ){	exit('订单编号为空');	}
	if( empty($subject) ){		exit('支付项目为空');	}
	if( empty($return_url) ){	exit('支付后跳转链接为空');	}
	if( empty($notify_url) ){	exit('支付后回调链接为空');	}

	$data = array(
			'out_trade_no'	=> $out_trade_no,
			'total_amount'	=> $total_fee,
			'subject'   	=> $subject,		
			'return_url'	=> $return_url,
			'notify_url'	=> $notify_url,
	);	
	$tools = new JsApiPay();
	$openId = $tools->GetOpenid(urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']));	
	$input = new WxPayUnifiedOrder();
	$input->SetBody($data['subject']);
	$input->SetAttach($data['subject']);
	$input->SetOut_trade_no($data['out_trade_no']);
	$input->SetTotal_fee($data['total_amount']*100);
	$input->SetTime_start(date("YmdHis"));
	$input->SetTime_expire(date("YmdHis", time() + 600));
	$input->SetGoods_tag($data['subject']);
	$input->SetNotify_url($data['notify_url']);
	$input->SetTrade_type("JSAPI");
	$input->SetOpenid($openId);
	$order = WxPayApi::unifiedOrder($input);
	$jsApiParameters = $tools->GetJsApiParameters($order);	
}catch(Exception $e){
	exit(json_encode(['err_msg'=>$e->getMessage(), 'url'=>'']));
}	
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
				if(res.err_msg!='get_brand_wcpay_request:ok'){
					alert('支付取消.');
					history.back(-1);
				}
				window.location.href="<?php echo $return_url;?>";
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
            <span>微信支付</span>
        </header>
        <div class="main">
            <p style="text-align:center;font-weight:700;margin-bottom:0.3rem;display: -webkit-box;-webkit-box-orient: vertical;	-webkit-line-clamp: 2;overflow: hidden;">
				<?php echo $subject;?>
			</p>
            <p style="text-align:center;height: 1.2rem;line-height:1.2rem;font-size:0.38rem;font-weight:700">￥<?php echo $total_fee;?></p>
            <div class="infor">
                <span class="fl">收款方</span>
                <span class="fr">商超世界电子商务有限公司</span>
            </div>
            <a class="confirm" >
                <input type="button" value="支付中···" id="btn" disabled style="background-color:#ccc">
            </a>
        </div>
    </div>
</body>

</html>