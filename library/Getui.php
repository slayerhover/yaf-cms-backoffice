<?php
require_once(dirname(__FILE__) . '/Getui/' . 'IGt.Push.php');

class Getui{
	private static $HOST	='http://sdk.open.api.igexin.com/apiex.htm';
	private static $APPKEY	='0GkbLIC3dq7teU8PdJkrb';
	private static $APPID	='NZLsmofDiJ7fGicOkHgLW8';
	private static $MASTERSECRET='IqYNB3ObEq8V6DXbkSICf';
	

	//单推接口案例
	public static function send($cid, $title, $content){
		$igt = new IGeTui(self::$HOST, self::$APPKEY, self::$MASTERSECRET);
		$template= self::IGtNotificationTemplateDemo($title, $content);
		
		$message = new IGtSingleMessage();
		$message->set_isOffline(true);//是否离线
		$message->set_offlineExpireTime(3600*12*1000);//离线时间
		$message->set_data($template);//设置推送消息类型
		$message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送		
		$target = new IGtTarget();
		$target->set_appId(self::$APPID);
		$target->set_clientId($cid);

		try {
			$rep = $igt->pushMessageToSingle($message, $target);
			#Log::out('getui', 'I', json_encode($rep, JSON_UNESCAPED_UNICODE));
		}catch(RequestException $e){
			$requstId =e.getRequestId();
			$rep = $igt->pushMessageToSingle($message, $target, $requstId);
			#Log::out('getuierr', 'I', json_encode($rep, JSON_UNESCAPED_UNICODE));
		}
		return $rep;
	}
	
	public static function push($cid, $title, $content){
		putenv("gexin_pushList_needDetails=true");
		$igt = new IGeTui(self::$HOST, self::$APPKEY, self::$MASTERSECRET);
		$template= self::IGtNotificationTemplateDemo($title, $content);
		
		$message = new IGtListMessage();
		$message->set_isOffline(true);//是否离线
		$message->set_offlineExpireTime(3600*12*1000);//离线时间
		$message->set_data($template);//设置推送消息类型
		$message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
		$contentId = $igt->getContentId($message);
		//接收方1  
		$target1 = new IGtTarget();
		$target1->set_appId(self::$APPID);
		$target1->set_clientId($cid);		
		$targetList[0] = $target1;
		$rep = $igt->pushMessageToList($contentId, $targetList);		
		return $rep;
	}

	//所有推送接口均支持四个消息模板，依次为通知弹框下载模板，通知链接模板，通知透传模板，透传模板
	//注：IOS离线推送需通过APN进行转发，需填写pushInfo字段，目前仅不支持通知弹框下载功能
	private static function IGtNotyPopLoadTemplateDemo($title, $content){
		$template =  new IGtNotyPopLoadTemplate();
		$template ->set_appId(self::$APPID);//应用appid
		$template ->set_appkey(self::$APPKEY);//应用appkey
		//通知栏
		$template ->set_notyTitle($title);//通知栏标题
		$template ->set_notyContent($content);//通知栏内容
		$template ->set_notyIcon("");//通知栏logo
		$template ->set_isBelled(true);//是否响铃
		$template ->set_isVibrationed(true);//是否震动
		$template ->set_isCleared(true);//通知栏是否可清除		
		return $template;
	}
	
	private static function IGtNotificationTemplateDemo($title, $content){
		$template =  new IGtNotificationTemplate();
		$template->set_appId(self::$APPID);                      //应用appid
		$template->set_appkey(self::$APPKEY);                    //应用appkey
		$template->set_transmissionType(1);               //透传消息类型
		$template->set_transmissionContent("畅配消息");   //透传内容
		$template->set_title($title);                     //通知栏标题
		$template->set_text($content);        //通知栏内容
		$template->set_isRing(true);                      //是否响铃
		$template->set_isVibrate(true);                   //是否震动
		$template->set_isClearable(true);                 //通知栏是否可清除		
		return $template;
	}
	
	public function getPersonaTagsDemo() {
		$igt = new IGeTui(HOST, APPKEY, MASTERSECRET);
		$ret = $igt->getPersonaTags(APPID);
		var_dump($ret);
	}

	public function getUserCountByTagsDemo() {
		$igt = new IGeTui(HOST, APPKEY, MASTERSECRET);
		$tagList = array("金在中","龙卷风");
		$ret = $igt->getUserCountByTags(APPID, $tagList);
		var_dump($ret);
	}

	public function getPushMessageResultDemo(){
	//    putenv("gexin_default_domainurl=http://183.129.161.174:8006/apiex.htm");

		$igt = new IGeTui(HOST,APPKEY,MASTERSECRET);

		$ret = $igt->getPushResult("OSA-0522_QZ7nHpBlxF6vrxGaLb1FA3");
		var_dump($ret);

		$ret = $igt->queryAppUserDataByDate(APPID,"20140807");
		var_dump($ret);

		$ret = $igt->queryAppPushDataByDate(APPID,"20140807");
		var_dump($ret);
	}


	//用户状态查询
	public function getUserStatus() {
		$igt = new IGeTui(HOST,APPKEY,MASTERSECRET);
		$rep = $igt->getClientIdStatus(APPID,CID);
		var_dump($rep);
		echo ("<br><br>");
	}

	//推送任务停止
	public function stoptask(){

		$igt = new IGeTui(HOST,APPKEY,MASTERSECRET);
		$igt->stop("OSA-1127_QYZyBzTPWz5ioFAixENzs3");
	}

	//通过服务端设置ClientId的标签
	public function setTag(){
		$igt = new IGeTui(HOST,APPKEY,MASTERSECRET);
		$tagList = array('','中文','English');
		$rep = $igt->setClientTag(APPID,CID,$tagList);
		var_dump($rep);
		echo ("<br><br>");
	}

	public function getUserTags() {
		$igt = new IGeTui(HOST,APPKEY,MASTERSECRET);
		$rep = $igt->getUserTags(APPID,CID);
		//$rep.connect();
		var_dump($rep);
		echo ("<br><br>");
	}
	
	
}
