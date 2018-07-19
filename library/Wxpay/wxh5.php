<?php
header("Access-Control-Allow-Origin: *"); // 允许任意域名发起的跨域请求  
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); 
include 'wxH5Pay.php';
include 'db.class.php';
class wxh5{
    //$data 金额和订单号
    public function wxh5Request($data){
        $appid = 'wxd61e5ed1a5c7b8b5';
        $mch_id = '1488194562';//商户号
        $key = '0598e9a56c45cd5243d27394cb21be3b';//商户key
        $notify_url = "http://h5.scsj.net.cn/wxpay/notify_url.php";//回调地址
        $wechatAppPay = new wechatAppPay($appid, $mch_id, $notify_url, $key);
        $params['body'] = $data['subject'];           //商品描述
        $params['out_trade_no'] = $data['out_trade_no'];    //自定义的订单号
        $params['total_fee'] = 10000 * 100;                       //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'MWEB';                   //交易类型 JSAPI | NATIVE | APP | WAP 
        $params['scene_info'] = '{"h5_info": {"type":"Wap","wap_url": "http://h5.scsj.net.cn/wxpay/h5/templates/ensureMoney.html","wap_name": "保证金"}}';
        $result = $wechatAppPay->unifiedOrder( $params );
		if(empty($result['err_code'])){
			$rt = array(
				'err_msg' => '',
				'url'	  => $result['mweb_url'].'&redirect_url='. urlencode('http://h5.scsj.net.cn/wxpay/h5/templates/personal.html'),
			);
		}else{
			$rt = array(
				'err_msg' => $result['err_msg'],
				'url'	  => $result['mweb_url'].'&redirect_url='. urlencode('http://h5.scsj.net.cn/wxpay/h5/templates/personal.html'),
			);
		}		
		return $rt;
    }
}

do{
	$token = isset($_POST['token'])?$_POST['token']:'';
	$token = addslashes($token);
	if(empty($token)){
		$result = array(
			'err_msg'	=>	'参数为空',
			'url'		=>	'',
		);
		break;
	}	
	$dbconfig = array(
					'dsn'         =>    'mysql:host=rdsproxy56.rdsprwt7mveezzq.rds.bj.baidubce.com;dbname=scsj',
					'name'        =>    'scsj_proxy_root',
					'password'    =>    'eccbd73df5ff4848aab5fd29069f5530',
	);
	$_DB = new DB($dbconfig);
	$user= $_DB->getRow("select id,phone from t_user where token='{$token}'");	
	if(empty($user)){
		$result = array(
			'err_msg'	=>	'请重新登陆.',
			'url'		=>	'',
		);
		break;
	}
	
	$orders = $_DB->getRow("select * from orders where user_id='{$user['id']}' and type=1 and pay_status=1 and pay_type=1");
	if(!empty($orders)){
		$out_trade_no	= $orders['out_trade_no'] . mt_rand(10000, 99999);
		$orders['out_trade_no']	=$out_trade_no;
	}else{
		$out_trade_no	= date('YmdHis') . mt_rand(100000, 999999);
		$total_fee		= 10000.00;
		$orders = [
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
		if($_DB->insert('orders',$orders)===FALSE){
			$result = array(
				'err_msg'	=>	'插入订单失败',
				'url'		=>	'',
			);
			break;
		}
	}	
	$wxx = new wxh5;
	$result = $wxx->wxh5Request($orders);	
}while(FALSE);

header('Content-type: application/json');
exit(json_encode($result));