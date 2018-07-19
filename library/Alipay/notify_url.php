<?php
require_once("./config.php");
require_once './wappay/service/AlipayTradeService.php';
include 'db.class.php';

function  log_result($file,$word) 
{
	$fp = fopen($file,"a+");
	flock($fp, LOCK_EX) ;
	fwrite($fp,"执行日期：".strftime("%Y-%m-%d %H:%M:%S",time())."\n".$word."\r\n");
	flock($fp, LOCK_UN);
	fclose($fp);
}

$arr=$_POST;
if(empty($arr)){
	exit('fail');
}	
$log_name="./log/notify_url.log";//log文件路径
log_result($log_name,"【接收到的notify通知】:\r\n".json_encode($_POST, JSON_UNESCAPED_UNICODE)."\r\n");

$alipaySevice = new AlipayTradeService($config); 
$alipaySevice->writeLog(var_export($_POST,true));
$result = $alipaySevice->check($arr);

if($result) {//验证成功
	//商户订单号
	$out_trade_no = $_POST['out_trade_no'];
	//支付宝交易号
	$trade_no = $_POST['trade_no'];
	//交易状态
	$trade_status = $_POST['trade_status'];	
	//交易金额
	$total_amount = floatval($_POST['total_amount']);

    if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
			
			$dbconfig = array(
							'dsn'         =>    'mysql:host=rdsproxy56.rdsprwt7mveezzq.rds.bj.baidubce.com;dbname=scsj',
							'name'        =>    'scsj_proxy_root',
							'password'    =>    'eccbd73df5ff4848aab5fd29069f5530',
			);
			$_DB = new DB($dbconfig);			
			$orders	= $_DB->getRow("select * from orders where out_trade_no='" . $out_trade_no . "'");			
			if ($orders) {				
                    if ($orders['total_amount']==$total_amount) {
						$rows = array(
							'pay_status'	=> 2,
							'trade_no'		=> $trade_no,
							'pay_at'		=> date('Y-m-d H:i:s'),							
							'updated_at'	=> date('Y-m-d H:i:s'),							
						);
						$_DB->update('orders', $rows, "out_trade_no='{$out_trade_no}'");
						
						if($orders['type']==1){							
							$rows = array(
								'baozhengjin'			=>	$orders['total_amount'],
								'baozhengjin_status'	=>	2,
							);
							$_DB->update('t_user', $rows, "id='{$orders['user_id']}'");
							$rows = array(
								'content'	=>	'恭喜您，您的保证金'.$orders['total_amount'].'元已支付成功!',
							);
							$_DB->insert('t_notice', $rows);
						}else{
							$rows = array(
								'content'	=>	'恭喜您，您的订单总额为'.$orders['total_amount'].'元已支付成功!',
							);
							$_DB->insert('t_notice', $rows);
						}
						exit('success');
                    }
            }else{
				exit('fail');
			}
    }
}
echo "fail";
?>
