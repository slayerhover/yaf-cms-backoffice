<?php

class AntizyPlugin extends Yaf_Plugin_Abstract {
	public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {		
		$getfilter="'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?Select|Update.+?SET|Insert\\s+INTO.+?VALUES|(Select|Delete).+?FROM|(Create|Alter|Drop|TRUNCATE)\\s+(TABLE|DATABASE)" ;
		$postfilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?Select|Update.+?SET|Insert\\s+INTO.+?VALUES|(Select|Delete).+?FROM|(Create|Alter|Drop|TRUNCATE)\\s+(TABLE|DATABASE)" ;
		$cookiefilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?Select|Update.+?SET|Insert\\s+INTO.+?VALUES|(Select|Delete).+?FROM|(Create|Alter|Drop|TRUNCATE)\\s+(TABLE|DATABASE)" ;

		//$ArrPGC=array_merge($_GET,$_POST,$_COOKIE);
		foreach($_REQUEST as $key=>$value){
			$this->StopAttack($key,$value,$postfilter);
		}
		foreach($_GET as $key=>$value){
			$this->StopAttack($key,$value,$getfilter);
		}
		foreach($_POST as $key=>$value){
			$this->StopAttack($key,$value,$postfilter);
		}
		foreach($_COOKIE as $key=>$value){
			$this->StopAttack($key,$value,$cookiefilter);
		}
	}
	
	private function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq){
		if(is_array($StrFiltValue)){
			$StrFiltValue=implode($StrFiltValue);
		}
		if (preg_match("/".$ArrFiltReq."/is",$StrFiltValue)==1){
			Log::out('attack', 'I', "操作IP: ".$_SERVER["REMOTE_ADDR"]."\r\n操作时间: ".strftime("%Y-%m-%d %H:%M:%S")."\r\n操作页面:".$_SERVER["PHP_SELF"]."\r\n提交方式: ".$_SERVER["REQUEST_METHOD"]."\r\n提交参数: ".$StrFiltKey."\r\n提交数据: ".$StrFiltValue);
			throw new Exception("notice:非法操作!");
		}
	}
	
}
