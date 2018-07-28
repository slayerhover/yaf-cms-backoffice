<?php
use Illuminate\Database\Capsule\Manager as DB;

class Message{
	
	#获取消息类型及短信模板
	private static function getTemplate($type){
		$template = ['code'='', 'title'=>'', 'content'=>''];
		switch ($type){
            case 1:
                #注册
                $template['code']   ='SMS_13735560';
                break;
            case 2:
                #手机验证码登陆
                $template['code']   ='SMS_13735562';
                break;
            case 3:
                #修改登陆密码
                $template['code']   ='SMS_13735558';
                break;
            case 4:
                #修改支付密码
                $template['code']   ='SMS_13735557';
                break;
            case 5:
                #好友注册成功
                $template['code']   ='SMS_135525176';
				$template['title']	='好友注册成功';
				$template['content']='您的好友已经注册成功，感谢您对平台的支持';
                break;
            case 6:
                #客户下单成功
                $template['code']   ='SMS_135415173';
				$template['title']	='订单支付成功';
				$template['content']='您的订单已经支付成功，感谢您的使用，我们会尽处理';
                break;
            case 7:
                #客户申请退款
                $template['code']   ='SMS_135360173';
				$template['title']	='订单申请退款';
				$template['content']='您的订单已经申请退款，感谢您的使用';
                break;
            case 8:
                #好友充值成功
                $template['code']   ='SMS_135345173';
				$template['title']	='好友充值成功';
				$template['content']='您的好友已经为您充值成功，请注意查收';
                break;
            case 9:
                #发货通知
                $template['code']   ='SMS_135355190';
				$template['title']	='订单发货通知';
				$template['content']='您的订单已经发货，请注意查收';
                break;            
            case 10:
                #提现申请
                $template['code']   ='SMS_135390188';
				$template['title']	='申请提现';
				$template['content']='您的提现申请已经提交，请等待审核';
                break;
        }
		return $template;
	}
	
	#统一消息发送 Message::send($members_id, $type);
	public static function send($members_id, $type){
		if($this->config->application->debug) return TRUE;
		
		$members  = DB::table('members')->find($members_id);
		$template = self::getTemplate($type);
		
		self::sms($members['phone'], $type);
		self::zMail($members_id, $template['title'], $template['content']);
		pushMsg($cid, $template['content']);
	}
	
	#发短信	Message::sms($phone, $type);
	public static function sms($phone='', $type=0]){
        Yaf_Loader::import(APP_PATH . '/library/Alidayu/TopSdk.php');                
        $template   =self::getTemplate($type);
		if(in_array($type, [1,2,3,4])){
			$code	= rand(1000, 9999);        
		}else{
			$code	= '';
		}
        $product= 'SIGN';
        $c = new TopClient;
        $c->appkey = '23446822';
        $c->secretKey = '0380ab9b5e9309d2f6a63518c71bccf8';
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setSmsType("normal");
        $req->setSmsFreeSignName("SIGN");
        $req->setSmsParam("{\"code\":\"{$code}\", \"product\":\"{$product}\"}");
        $req->setRecNum($phone);
        $req->setSmsTemplateCode($template['code']);
		
        $resp = $c->execute($req);		
		if($resp->result->success && !empty($code)) {
			Cache::set('sms' . $phone, $code);
		}		
    }
	
	#站内信	Message::zMail($members_id, '支付成功', '您的订单已支付成功，我们会尽快为您发货，感谢您的使用!');
	public static function zMail($members_id, $title, $content){
		$rows	=	array(
                    'members_id'    =>  $members_id,
                    'title'		    =>	$title,
                    'content'       =>  $content,
                    'status'		=>	0,
                    'created_at'	=>	date('Y-m-d H:i:s'),
        );
        DB::table('message')->insert($rows);
	}
	
	#推送消息 Message::jPush($members_id, '您的订单已支付成功，我们会尽快为您发货，感谢您的使用!');
	public static function jPush(members_id, $title){
		$cid = DB::table('members')->find($members_id)['cid'];
		if(!empty($cid)){
			pushMsg($cid, $title);
		}
	}
			
}
