<?php
define('_RBACCookieKey_', 'RBACUser');
define('_EXPIRETIME_', 86000);
 
define('_COOKIE_KEY_', 'f6j5r@ziqpei&vkjapip19lo6kl8t4');
define('_COOKIE_IV_', 'x6j5r@ziqpei&vkjapip19lo6kl8t3');

define('APP_KEY', '');
define('APP_SECRET', '');

define('LOG_DIR', APP_PATH . '/log/');
/**
 * 输出变量的内容，通常用于调试
 *
 * @package Core
 *
 * @param mixed $vars 要输出的变量
 * @param string $label
 * @param boolean $return
 */
function dump($vars, $label = '', $return = false)
{
    if (ini_get('html_errors')) {
        $content = "<pre>\n";
        if ($label != '') {
            $content .= "<strong>{$label} :</strong>\n";
        }
        $content .= htmlspecialchars(print_r($vars, true));
        $content .= "\n</pre>\n";
    } else {
        $content = $label . " :\n" . print_r($vars, true);
    }
    if ($return) { return $content; }
    echo $content;
    return null;
}
function json($vars)
{	
	header("Content-type: application/json");
	$data = updateNull($vars);	
    die(json_encode($data));
}
function updateNull(& $onearr){
	if(!empty($onearr)&&is_array($onearr)){
	foreach($onearr as $k=>$v){
		if(is_array($v)){
			$onearr[$k]	=	updateNull($v);
		}else{
			if($v===NULL){
				$onearr[$k] = '';
			}
		}
	}}
	return $onearr;
}

function getIp(){
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
	   return $_SERVER['HTTP_CLIENT_IP']; 
	}elseif(!empty($_SERVER['HTTP_X_FORVARDED_FOR'])){
	   return $_SERVER['HTTP_X_FORVARDED_FOR'];
	}elseif(!empty($_SERVER['REMOTE_ADDR'])){
	   return $_SERVER['REMOTE_ADDR'];
	}else{
	   return "unknow IP";
	}
}

function url($controller='index', $action='index', $args=array()){
	$router = Yaf_Dispatcher::getInstance()->getRouter();
	$urls	= array(
						':c'=>$controller,
						':a'=>$action
				);

	if( !empty($args) && is_array($args) ){
		foreach($args	as $k=>$v){
			$args[$k]	=	strval($v);
		}
	}
		
	$url	= $router->getRoute($router->getCurrentRoute())->assemble($urls, $args);
	$suffix = Yaf_Registry::get('config')->application->suffix;
	if(!empty($suffix)) {
		if( substr($url, -1, 1)=='/' ){
			$url= substr($url,0,strlen($url)-1).'.'.$suffix;
		}else{
			if(preg_match('#(\/?\?)#', $url)){
				$url = preg_replace('#(\/?\?)#', '.'.$suffix.'?', $url);
			}else{
				$url.= '.'.$suffix;
			}
		}
	}	
	return $url;
}


/**
 * 跳转
 *
 * @param      $url
 * @param null $headers
 */
function redirect($url) {
	echo "<script>top.location.href='{$url}';</script>";
	exit;
	/* if (!empty($url))
	{
		if ($headers)
		{
			if (!is_array($headers))
				$headers = array($headers);

			foreach ($headers as $header)
				header($header);
		}

		header('Location: ' . $url);
		exit;
	} */
}

function postSMS($url,$data='')
{
	$row = parse_url($url);
	$host = $row['host'];
	$port = isset($row['port']) ? $row['port']:80;
	$file = $row['path'];
	$post = "";
	while (list($k,$v) = each($data)) 
	{
		$post .= rawurlencode($k)."=".rawurlencode($v)."&";	//转URL标准码
	}
	$post = substr( $post , 0 , -1 );
	$len = strlen($post);
	$fp = @fsockopen( $host ,$port, $errno, $errstr, 10);
	if (!$fp) {
		return "$errstr ($errno)\n";
	} else {
		$receive = '';
		$out = "POST $file HTTP/1.1\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Content-type: application/x-www-form-urlencoded\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Content-Length: $len\r\n\r\n";
		$out .= $post;		
		fwrite($fp, $out);
		while (!feof($fp)) {
			$receive .= fgets($fp, 128);
		}
		fclose($fp);
		$receive = explode("\r\n\r\n",$receive);
		unset($receive[0]);
		return implode("",$receive);
	}
}

/**
 *采集函数
 *
 */
function curl_data($url,$postdata='',$pre_url='http://www.baidu.com',$proxyip=false,$compression='gzip, deflate'){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT,5);
		
		$client_ip	= rand(1,254).'.'.rand(1,254).'.'.rand(1,254).'.'.rand(1,254);
		$x_ip		= rand(1,254).'.'.rand(1,254).'.'.rand(1,254).'.'.rand(1,254);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$x_ip,'CLIENT-IP:'.$client_ip));//构造IP				
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		if($postdata!=''){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		}
		$pre_url = $pre_url ? $pre_url : "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		curl_setopt($ch, CURLOPT_REFERER, $pre_url);
		if($proxyip){
			curl_setopt($ch, CURLOPT_PROXY, $proxyip);
		}		
		if($compression!='') {	
			curl_setopt($ch, CURLOPT_ENCODING, $compression);	
		}
		
		//Mozilla/5.0 (Linux; U; Android 2.3.7; zh-cn; c8650 Build/GWK74) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1s		
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11'); 
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
}

/**
 * 加密/解密字符串
 *
 * @param  string     $string    原始字符串
 * @param  string     $operation 操作选项: DECODE：解密；其它为加密
 * @param  string     $key       混淆码
 * @return string     $result    处理后的字符串
 */
function authcode($string, $operation, $key = '') {
		$authorization=Yaf_Registry::get('config')->application->rpcAuth;
		$key = md5($key ? $key : $authorization);
		$key_length = strlen($key);
	
		$string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
		$string_length = strlen($string);
	
		$rndkey = $box = array();
		$result = '';
	
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($key[$i % $key_length]);
			$box[$i] = $i;
		}
	
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
	
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
	
		if($operation == 'DECODE') {
			if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
				return substr($result, 8);
			} else {
				return '';
			}
		} else {
			return str_replace('=', '', base64_encode($result));
		}	
}