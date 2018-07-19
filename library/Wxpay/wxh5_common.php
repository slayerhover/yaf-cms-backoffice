<?php
header("Access-Control-Allow-Origin: *"); // 允许任意域名发起的跨域请求  
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); 
include 'wxH5Pay.php';
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
class wxh5{
    //$data 金额和订单号
    public function wxh5Request($data){
        $appid = 'wxd61e5ed1a5c7b8b5';
        $mch_id = '1488194562';//商户号
        $key = '0598e9a56c45cd5243d27394cb21be3b';//商户key
        $notify_url = $data['notify_url'];//回调地址
        $wechatAppPay = new wechatAppPay($appid, $mch_id, $notify_url, $key);
        $params['body'] = $data['subject'];           //商品描述
        $params['out_trade_no'] = $data['out_trade_no'];    //自定义的订单号
        $params['total_fee'] = $data['total_fee'] * 100;    //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'MWEB';                   //交易类型 JSAPI | NATIVE | APP | WAP 
        $params['scene_info'] = '{"h5_info": {"type":"Wap","wap_url": "'.$data['return_url'].'","wap_name": "保证金"}}';
        $result = $wechatAppPay->unifiedOrder( $params );		
		$err_msg= isset($result['err_msg'])?$result['err_msg']:'';
		$rt = array(
			'err_msg' => $err_msg,
			'url'	  => $result['mweb_url'].'&redirect_url='. urlencode($data['return_url']),
		);			
		return $rt;
    }
}
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

	do{
		$data	=	array(
			'out_trade_no'	=>	$out_trade_no,
			'total_fee'		=>	$total_fee,
			'subject'		=>	'保证金',
			'return_url'	=>	$return_url,
			'notify_url'	=>	$notify_url,
		);
		$wxx = new wxh5;
		$result = $wxx->wxh5Request($data);	
	}while(FALSE);

	header('Content-type: application/json');
	exit(json_encode($result, JSON_UNESCAPED_UNICODE));
}catch(Exception $e){
	exit(json_encode(['err_msg'=>$e->getMessage(), 'url'=>'']));
}