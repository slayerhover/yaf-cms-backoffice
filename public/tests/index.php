<?php
header('content-type:text/html;charset=utf-8');
date_default_timezone_set('PRC');

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
function pick($url, $postData='',$preUrl='http://www.baidu.com',$proxyIp=false,$compression='gzip, deflate'){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT,5);
		
		$client_ip	= rand(1,254).'.'.rand(1,254).'.'.rand(1,254).'.'.rand(1,254);
		$x_ip		= rand(1,254).'.'.rand(1,254).'.'.rand(1,254).'.'.rand(1,254);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$x_ip,'CLIENT-IP:'.$client_ip));//构造IP				
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		if($postData!=''){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		}
		$preUrl = $preUrl ? $preUrl : "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		curl_setopt($ch, CURLOPT_REFERER, $preUrl);
		if($proxyIp){
			curl_setopt($ch, CURLOPT_PROXY, $proxyIp);
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
		return json_decode($result, TRUE);
}