<?php
use Illuminate\Database\Capsule\Manager as DB;

class IndexController extends CoreController {		
	/**
	 *接口名称	APP欢迎页
	 *接口地址	http://api.com/public/index/
	 *接口说明	显示欢迎页图片
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function indexAction(){		
		ret(0, 'Hello,World', 'ok');
	}
	
	public function syncTokenAction(){
		$now	=	time();
		foreach(DB::table('t_user')->where('token','<>','')->select('id','phone','type','code','inviter_id','saleuser_state','token')->get() as $v){
			if(Cache::exists('auth_'.$v['token'])==FALSE){
				$rows	=	array(
					'uid'		=> $v['id'],
					'phone'		=> $v['phone'],
					'type'		=> $v['type'],
					'code'		=> $v['code'],
					'inviter_id'=> $v['inviter_id'],
					'saleuser_state'=>$v['saleuser_state'],
					'created'	=> $now,
				);
				Cache::set('auth_'.$v['token'], $rows, $this->config['cache']['redis']['expire']);
			}
		}
		ret(0, 'Token同步完成', 'ok');
	}
		
	/**
	 *接口名称	APP注册
	 *接口地址	http://api.com/public/register/
	 *接口说明	APP客户端注册
	 *POST参数 @param
	 * @phone    	手机号码
	 * @password  	登陆密码
	 * @repassword	重复密码
	 * @invite	  	邀请码
	 *返回 @return
	 * @token   	令牌
	 *
	 **/
	public function registerAction() {
		do{	
			$phone		= $this->get('phone', 		'');
			$smscode	= $this->get('smscode', 	'');
			$password	= $this->get('password', 	'');
			$repassword	= $this->get('repassword',	'');
			$type		= $this->get('type',		 4);  //默认4：业务员
			$inviter_code= $this->get('inviter_code',	'');			
			/***参数验证BOF***/
			$inputs	= array(					
					['name'=>'phone',  'value'=>$phone,	 'role'=>'required|unique:t_user,phone', 'fun'=>'isPhone', 'msg'=>'手机号码有错误异常'],
					['name'=>'smscode','value'=>$smscode,'role'=>'gte:100000|lte:999999', 'fun'=>'isInteger','msg'=>'短信验证码格式有误'],					
					['name'=>'password','value'=>$password,	'role'=>'min:6|max:32|required', 'msg'=>'密码格式有误'],
					['name'=>'repassword','value'=>$repassword,	'role'=>'eq:'.$password, 'msg'=>'重复密码不一致'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}			
			/***参数验证EOF***/
			/***验证smscodeBOF***/
			if(DB::table('scsj_smslog')->where('phone','=',$phone)->orderby('created','DESC')->first()['sn']!=$smscode){
					$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'短信验证码不正确.',
								'data'	=>	[],
							);
					break;
			}
			/***验证smscodeEOF***/
			/***获取邀请者信息BOF***/
			if(!empty($inviter_code)){
				$inviter 	= DB::table('t_user')->where('code','=',$inviter_code)->select('id', 'inviter_code', 'max')->first();
				$invitcount	= DB::table('t_user')->where('code','=',$inviter_code)->count(); 
				if (!$inviter){
					$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'无效的邀请码',
								'data'	=>	[],
							);
					break;
				}
				if ($invitcount>$inviter['max']){
					$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'无法注册,该邀请码达到最大使用次数',
								'data'	=>	[],
							);
					break;
				}
				$inviter_id = $inviter['id'];
			}else{
				$inviter_id = 0;
			}
			/***获取邀请者信息EOF***/
			/***注册用户BOF***/
			$salt = str_random(8);
			$now  = time();
			$ip	  = getIp();
			$token= md5($phone.$now.$ip.$salt);
			$code = trim(str_replace('uid_', '', file_get_contents('http://id.scsj.net.cn/uid')));
			$uid = DB::transaction(function () use ($phone,$password,$salt,$token,$inviter_id,$code,$inviter_code,$ip,$now){
				$rows = [
					'phone'			=> $phone,
					'user_name'		=> $phone,
					'user_passwd'	=> md5($password . $salt),
					'salt'			=> $salt,
					'token'			=> $token,
					'type'			=> $type,   
					'code'			=> $code,
					'inviter_id'	=> $inviter_id,
					'inviter_code'	=> $inviter_code,
					'user_mobile_bind'=>1,
					'name'			=> $phone,
					'user_mobile'	=> $phone,
					'user_avatar'	=> 'http://scsj-v2-bos.bj.bcebos.com/headImg/default.jpg',
					'user_time'		=>date('Y-m-d H:i:s', $now),
					'user_login_time'=>date('Y-m-d H:i:s', $now),
					'user_old_login_time'=>date('Y-m-d H:i:s', $now),
					'user_login_ip'	=> $ip,
					'user_old_login_ip'	=> $ip,
					'user_login_num'=> 0,
					'saleuser_state'=> 0,
					'create_date'	=> date('Y-m-d H:i:s', $now),
					'from'			=> '财富宝',
				];				
				$uid = DB::table('t_user')->insertGetId($rows);				
				if($inviter_id>0){
					$content="恭喜您，成功邀约{$phone}注册成为新用户";
					DB::table('user_notice')->insert(['user_id'=>$inviter_id, 'content'=>$content, 'created_at'=>date('Y-m-d H:i:s')]);
				}
				return $uid;
			});
			/***注册用户EOF***/
			/***设置登陆tokenBOF***/
			if($uid>0){				
				$rows	=	array(
					'uid'		=> $uid,
					'phone'		=> $phone,
					'type'		=> $type,
					'code'		=> code,
					'inviter_id'=> $inviter_id,
					'saleuser_state'=>0,
					'created'	=> $now,
				);
				if( Cache::set('auth_'.$token, $rows, $this->config['cache']['redis']['expire']) ){
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'注册成功，感谢您的使用.',
							'data'	=>	array(
											'token'		=>	$token,
											'userinfo'	=>	array(
													'uid'			=>	$uid,	
													'type'			=>	$type,
													'code'			=>	$code,
													'phone'			=>	$phone,
													'inviter_id'	=>	$inviter_id,
													'saleuser_state'=>	0,
													'create_date'	=>	date('Y-m-d H:i:s', $now),
											)
							)
					);
					break;
				}				
			}
			/***设置登陆tokenEOF***/
			$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'注册失败，请重试.',
							'data'	=>	array()
			);
		}while(FALSE);
		
		json($result);	
	}
	
	/**
	 *接口名称	APP登陆
	 *接口地址	http://api.com/public/login/
	 *接口说明	生成token，用户登陆
	 *参数 @param
	 * @phone 	用户名
	 * @password 	密码
	 *返回 @return	
	 * @token   	登陆标记
	 * @status		登陆状态
	 **/
	public function loginAction(){
		do{			
			$phone  	= $this->get('phone', 	 '');
			$password	= $this->get('password', '');
			/***验证参数BOF***/
			$inputs		= array(
								['name'=>'phone',  'value'=>$phone,	 	'role'=>'min:11|max:11|required','fun'=>'isPhone', 'msg'=>'手机号码格式有误'],
								['name'=>'password','value'=>$password,	'role'=>'min:6|max:32|required', 'msg'=>'密码格式有误'],
			);						
			$result		= Validate::check($inputs);
			if(	!empty($result) ){
					$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'输入参数有误.',
								'data'	=>	$result,
					);
					break;
			}
			/***验证参数EOF***/
			/***检测手机号BOF***/
			$salter= DB::table('t_user')->where('phone','=',$phone)->first();
			if(empty($salter)){				
					$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'用户不存在.',
								'data'	=>	$result,
					);
					break;
			}
			/***检测手机号EOF***/
			/***检测用户状态BOF***/
			$now= time();
			$ip	= getIp();
			if($now<$salter['lockuntil']){
				$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'该用户当前处于锁定状态.',
								'data'	=>	['lockuntil'=>date('Y-m-d H:i:s', $salter['lockuntil'])],
				);
				break;
			}			
			/***检测用户状态EOF***/
			/***检测用户密码BOF***/
			$user = DB::table('t_user')->where('phone','=',$phone)									   
									   ->where('user_passwd','=',md5($password.$salter['salt']))
									   ->select('id','token','type','code','phone','salt','inviter_id','lockuntil','user_login_time','user_login_ip','saleuser_state')
									   ->first();	
			if(empty($user)){
				$failedTimes = Cache::incr('loginFailTimes_'.$phone);
				if($failedTimes>=5){
					DB::table('t_user')->where('phone','=',$phone)->update(['lockuntil'=>time()+20*60]);
				}
				$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'密码有误,登陆失败.',
								'data'	=>	['failedTimes'=>$failedTimes],
				);
				break;
			}
			/***检测用户密码EOF***/
			/***更新tokenBOF***/
			$rows = array(
					'id'			=>	$user['id'],
					'phone'			=>	$user['phone'],
					'user_old_login_time'=> $user['user_login_time'],
					'user_old_login_ip'	 => $user['user_login_ip'],					
					'user_login_time'	=>	date('Y-m-d H:i:s', $now),
					'user_login_ip'	=>	$ip,
					'modify_date'	=>	date('Y-m-d H:i:s', $now),
			);
			$token	= empty($user['token']) ? md5($user['phone'].$now.$ip.$user['salt']) : $user['token'];
			$rows['token']	=	$token;			
			if(Cache::exists('auth_'.$token)==FALSE){
				$tokenuser	=	array(
					'uid'		=> $user['id'],
					'phone'		=> $user['phone'],					
					'type'		=> $user['type'],
					'code'		=> $user['code'],
					'inviter_id'=> $user['inviter_id'],
					'saleuser_state'=>$user['saleuser_state'],
					'created'	=> $now,
				);
				Cache::set('auth_'.$token, $tokenuser, $this->config['cache']['redis']['expire']);
			}
			if(Cache::exists('loginFailTimes_'.$phone)){
				Cache::delete('loginFailTimes_'.$phone);
			}
			/***更新tokenEOF***/			
			/***更新用户登陆信息BOF***/
			if(DB::table('t_user')->where('id','=',$user['id'])->update($rows)!==FALSE){			
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'登陆成功.',
							'data'	=>	array(
											'token'		=>	$token,
											'userinfo'	=>	array(
													'uid'			=>	$user['id'],
													'phone'			=>	$user['phone'],
													'type'			=>	$user['type'],
													'code'			=>	$user['code'],
													'inviter_id'	=>	$user['inviter_id'],
													'saleuser_state'=>	$user['saleuser_state'],
													'modify_date'	=>	date('Y-m-d H:i:s', $now),
											)
							)
					);
					break;
			}
			/***更新用户登陆信息EOF***/
			$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'登陆失败.',
			);
		}while(FALSE);

		json($result);
	}
	
	/**
	 *接口名称	APP登陆
	 *接口地址	http://api.com/public/login/
	 *接口说明	生成token，用户登陆
	 *参数 @param
	 * @phone 	用户名
	 * @password 	密码
	 *返回 @return	
	 * @token   	登陆标记
	 * @status		登陆状态
	 **/
	public function smsloginAction(){
		do{			
			$phone  	= $this->get('phone', 	 '');
			$smscode	= $this->get('smscode', '');			
			/***参数验证BOF***/
			$inputs	= array(					
					['name'=>'phone',  'value'=>$phone,	 'role'=>'required', 'fun'=>'isPhone', 'msg'=>'手机号码格式有误'],
					['name'=>'smscode','value'=>$smscode,'role'=>'gte:100000|lte:999999', 'fun'=>'isInteger','msg'=>'短信验证码格式有误'],
			);
			$result		= Validate::check($inputs);
			if(	!empty($result) ){
					$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'输入参数有误.',
								'data'	=>	$result,
					);
					break;
			}
			/***验证参数EOF***/
			/***检测手机号BOF***/
			$salter= DB::table('t_user')->where('phone','=',$phone)->orWhere('user_name','=',$phone)->first();
			if(empty($salter)){				
					$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'手机号不存在.',
								'data'	=>	$result,
					);
					break;
			}
			/***检测手机号EOF***/
			/***检测用户状态BOF***/
			$now= time();
			$ip	= getIp();
			if($now<$salter['lockuntil']){
				$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'该用户当前处于锁定状态.',
								'data'	=>	['lockuntil'=>date('Y-m-d H:i:s', $salter['lockuntil'])],
				);
				break;
			}			
			/***检测用户状态EOF***/
			/***检测短信验证码BOF***/
			if(DB::table('scsj_smslog')->where('phone','=',$phone)->orderby('created','DESC')->first()['sn']!=$smscode){
				$failedTimes = Cache::incr('loginFailTimes_'.$phone);
				if($failedTimes>=5){
					DB::table('t_user')->where('phone','=',$phone)->update(['lockuntil'=>time()+20*60]);
				}
				$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'短信验证码不正确.',
								'data'	=>	['failedTimes'=>$failedTimes],
				);
				break;				
			}
			/***检测短信验证码EOF***/			
			/***更新tokenBOF***/
			$user = DB::table('t_user')->where('phone','=',$phone)
									   ->select('id','token','type','code','phone','salt','inviter_id','lockuntil','user_login_time','user_login_ip','saleuser_state')
									   ->first();
			$rows = array(
					'id'				=>	$user['id'],
					'phone'				=>	$user['phone'],
					'user_old_login_time'=> $user['user_login_time'],
					'user_old_login_ip'	=>	$user['user_login_ip'],					
					'user_login_time'	=>	date('Y-m-d H:i:s', $now),
					'user_login_ip'		=>	$ip,
					'modify_date'		=>	date('Y-m-d H:i:s', $now),
			);
			$token	= empty($user['token']) ? md5($user['phone'].$now.$ip.$user['salt']) : $user['token'];
			$rows['token']	=	$token;			
			if(Cache::exists('auth_'.$token)==FALSE){
				$tokenuser	=	array(
					'uid'		=> $user['id'],
					'phone'		=> $user['phone'],
					'type'		=> $user['type'],
					'code'		=> $user['code'],					
					'inviter_id'=> $user['inviter_id'],
					'saleuser_state'=>$user['saleuser_state'],
					'created'	=> $now,
				);
				Cache::set('auth_'.$token, $tokenuser, $this->config['cache']['redis']['expire']);
			}
			if(Cache::exists('loginFailTimes_'.$phone)){
				Cache::delete('loginFailTimes_'.$phone);
			}
			/***更新tokenEOF***/			
			/***更新用户登陆信息BOF***/
			if(DB::table('t_user')->where('id','=',$user['id'])->update($rows)!==FALSE){			
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'登陆成功.',
							'data'	=>	array(
											'token'		=>	$token,
											'userinfo'	=>	array(
													'uid'			=>	$user['id'],
													'phone'			=>	$user['phone'],
													'type'			=>	$user['type'],
													'code'			=>	$user['code'],
													'inviter_id'	=>	$user['inviter_id'],
													'saleuser_state'=>	$user['saleuser_state'],
													'modify_date'	=>	date('Y-m-d H:i:s', $now),
											)
							)
					);
					break;
			}
			/***更新用户登陆信息EOF***/
			$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'登陆失败.',
			);
		}while(FALSE);

		json($result);
	}
	/**
	 *接口名称	找回密码，修改密码，重置密码
	 *接口地址	http://api.com/user/resetpwd/
	 *接口说明	清除token，退出登陆
	 *参数 @param无
	 *返回 @return无
	 **/	
	public function resetPasswdAction(){
		do{	
			$phone		= $this->get('phone', 		'');
			$smscode	= $this->get('smscode', 	'');
			$password	= $this->get('password', 	'');
			$repassword	= $this->get('repassword',	'');			
			/***参数验证BOF***/
			$inputs	= array(					
					['name'=>'phone',  'value'=>$phone,	 'role'=>'required', 'fun'=>'isPhone', 'msg'=>'手机号码格式有误'],
					['name'=>'smscode','value'=>$smscode,'role'=>'gte:100000|lte:999999', 'fun'=>'isInteger','msg'=>'短信验证码格式有误'],					
					['name'=>'password','value'=>$password,	'role'=>'min:6|max:32|required', 'msg'=>'密码格式有误'],
					['name'=>'repassword','value'=>$repassword,	'role'=>'eq:'.$password, 'msg'=>'重复密码不一致'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			if( DB::table('t_user')->where('phone','=',$phone)->count()<=0 ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'手机号不存在.',
							'data'	=>	[],
						);
					break;
			}
			/***参数验证EOF***/
			/***验证smscodeBOF***/
			if(DB::table('scsj_smslog')->where('phone','=',$phone)->orderby('created','DESC')->first()['sn']!=$smscode){
					$result	= array(
								'ret'	=>	'1',
								'msg'	=>	'短信验证码不正确.',
							);
					break;
			}
			/***验证smscodeEOF***/
			/***更新密码BOF***/
			$myuser		=	DB::table('t_user')->where('phone','=',$phone);
			$rows	=	array(
					'user_passwd'	=>	md5($password.$myuser->first()['salt']),
					'modify_date'	=>	date('Y-m-d H:i:s'),
			);
			if ($myuser->update($rows)!==FALSE) {
						$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'更新密码成功.',
							'data'	=>	array(
										'phone'	=> $phone,
							)
						);			
			}else{
						$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'更新失败.',
							'data'	=>	array(
										'phone'	=> $phone,
							)
						);
			}								
			/***更新密码EOF***/
		}while(FALSE);

		json($result);
	}
	/**
	 *接口名称	邀请联盟会员二维码
	 *接口地址	http://api.com/public/qrcode/
	 *接口说明	显示二维码图片
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 **/
	public function qrcodeAction(){		
		do{	
			$token		= $this->get('token', 		'');			
			/***参数验证BOF***/
			$inputs	= array(					
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识为空.'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/			
			
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'请重新登陆.',
							'data'	=>	$result,
					);
					break;
			}
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'邀请联盟会员二维码',
							'data'	=>	array(
								'url'	=>	'http://qrcode.scsj.net.cn/?s=http://h5.scsj.net.cn/h5/templates/register.html?code=' . $myuser['code'],
							),
			);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	APP注册
	 *接口地址	http://api.com/public/register/
	 *接口说明	APP客户端注册
	 *POST参数 @param
	 * @phone    	手机号码
	 * @password  	登陆密码
	 * @repassword	重复密码
	 * @invite	  	邀请码
	 *返回 @return
	 * @token   	令牌
	 *
	 **/
	public function checkphoneAction() {
		do{	
			$phone		= $this->get('phone', 		'');
			/***参数验证BOF***/
			$inputs	= array(					
					['name'=>'phone',  'value'=>$phone,	 'role'=>'required', 'fun'=>'isPhone', 'msg'=>'手机号码格式有误'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			if( DB::table('t_user')->where('phone','=',$phone)->count()>0 ){
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'手机号存在.',
							'data'	=>	[
								'phone'	=>	$phone,
							],
					);
					break;
			}			
			$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'手机号不存在.',
							'data'	=>	[
								'phone'	=>	$phone,
							],
			);
		}while(FALSE);
		
		json($result);
	}
	
	/**
     * @api 验证登陆标记token是否合法
     */
	private function checkTokenValid($token){		
		if(	!empty($checkResult) ){return FALSE;}
		if(Cache::exists('auth_'.$token)==FALSE){ return FALSE; }
		//$myuser	= DB::table('t_user')->where('token','=',$token)->first();
		//if(empty($myuser)){return FALSE;}
		
		return Cache::get('auth_'.$token);
	}
		
	/**
	 *接口名称	主页内容
	 *接口说明	询价列表页
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function homepageAction(){			
		do{	
			$token		= $this->get('token', 		'');			
			/***参数验证BOF***/
			$inputs	= array(					
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识为空.'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'请重新登陆.',
							'data'	=>	$result,
					);
					break;
			}
			$cuser  = DB::table('t_user')->find($myuser['uid']);
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'主页',
							'data'	=>	array(
								"uid"					=>	$myuser['uid'],
								"code"					=>	$myuser['code'],
								"sum"					=>	"23787.44",
								"usemoney"				=>	"34726.00",
								"saleuser_state"		=>	$cuser['saleuser_state'],
								"baozhengjin_status"	=>	$cuser['baozhengjin_status'],
								'url'	=>	'http://qrcode.scsj.net.cn/?s=http://h5.scsj.net.cn/h5/templates/register.html?code=' . $myuser['code'],
							),
			);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	联盟会员列表
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function membersAction(){			
		do{	
			$token		= $this->get('token', 		'');
			$page		= $this->get('page', 		1);
			$pagesize	= $this->get('pagesize', 	20);
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识为空.'],
					['name'=>'page',   'value'=>$page,	 'role'=>'required|gte:1', 'fun'=>'isInteger',   'msg'=>'页码格式有误.'],
					['name'=>'pagesize','value'=>$pagesize,'role'=>'required|gte:1', 'fun'=>'isInteger', 'msg'=>'页量格式有误.'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'请重新登陆.',
							'data'	=>	$result,
					);
					break;
			}
			$members= DB::table('t_user')->where('inviter_code', '=', $myuser['code']);
			$total	= $members->count();
			$members= $members->select('id','phone','code','user_avatar','create_date','saleuser_state','baozhengjin_status','user_truename')
							 ->offset(($page-1)*$pagesize)
							 ->limit($pagesize)
							 ->get();
			if(!empty($members)&&is_array($members)){
			foreach($members as $k=>&$v){	
					$unionNum		= $this->getUnionNumber($v['code']);
					$v['total']		= $unionNum['total'];
					$v['certied']	= $unionNum['certied'];
					$v['create_date']=substr($v['create_date'], 0, 10);
			}}
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'联盟会员列表',
							'data'	=>	array(
										'page'		=>	$page,
										'pagesize'	=>	$pagesize,
										'totalpage'	=>	ceil($total/$pagesize),
										'members'	=>	$members,
							),
			);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	联盟会员列表
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function membersInfoAction(){			
		do{	
			$code		= $this->get('code', 		'');
			$page		= $this->get('page', 		1);
			$pagesize	= $this->get('pagesize', 	20);
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'code',   'value'=>$code,	 'role'=>'required', 'msg'=>'邀请码为空.'],
					['name'=>'page',   'value'=>$page,	 'role'=>'required|gte:1', 'fun'=>'isInteger',   'msg'=>'页码格式有误.'],
					['name'=>'pagesize','value'=>$pagesize,'role'=>'required|gte:1', 'fun'=>'isInteger', 'msg'=>'页量格式有误.'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			$info	= DB::table('t_user')->where('code', '=', $code)
										 ->select('phone','user_truename','code','user_avatar','create_date','saleuser_state','baozhengjin_status')
										 ->first();										 
			$members= DB::table('t_user')->where('inviter_code', '=', $code);
			$total	= $members->count();
			$members= $members->select('phone','code','user_avatar','create_date','saleuser_state','baozhengjin_status','user_truename')
							 ->offset(($page-1)*$pagesize)
							 ->limit($pagesize)
							 ->get();
			if(!empty($members)&&is_array($members)){
			foreach($members as $k=>&$v){	
					$unionNum		= $this->getUnionNumber($v['code']);
					$v['total']		= $unionNum['total'];
					$v['certied']	= $unionNum['certied'];
					$v['create_date']=substr($v['create_date'], 0, 10);
			}}
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'联盟个人主页',
							'data'	=>	array(
										'page'		=>	$page,
										'pagesize'	=>	$pagesize,
										'totalpage'	=>	ceil($total/$pagesize),
										'info'		=>	$info,
										'members'	=>	$members,
							),
			);
		}while(FALSE);
		
		json($result);
	}
	
	/***查询联盟会员的下属会员数量***/
	private function getUnionNumber($code){
		$unionmembers	= DB::table('t_user')->where('inviter_code', '=', $code);		
		$total	=	$unionmembers->count();
		$certied=	$unionmembers->where('saleuser_state','=',2)->count();
		
		return ['total'=>$total, 'certied'=>$certied];
	}
	
	/**
	 *接口名称	合伙人
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function partnerAction(){
		do{	
			$token		= $this->get('token', 		'');			
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识为空.'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'请重新登陆.',
							'data'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			$members= DB::table('t_user')->select('phone','user_truename','code','user_avatar','create_date','saleuser_state','baozhengjin_status')
										 ->find($myuser['inviter_id']);			
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'我的合伙人',
							'data'	=>	array(										
									'members'	=>	$members,
							),
			);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	合伙人信息
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function partnerInfoAction(){
		return $this->partnerAction();
	}
	
	/**
	 *接口名称	公告列表
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function noticeAction(){			
		do{	
			$page		= $this->get('page', 		1);
			$pagesize	= $this->get('pagesize', 	20);
			/***参数验证BOF***/
			$inputs	= array(
					['name'=>'page',   'value'=>$page,	 'role'=>'required|gte:1', 'fun'=>'isInteger',   'msg'=>'页码格式有误.'],
					['name'=>'pagesize','value'=>$pagesize,'role'=>'required|gte:1', 'fun'=>'isInteger', 'msg'=>'页量格式有误.'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			$notice = DB::table('t_notice')->select('id','title','create_date')
										  ->where('is_use','=',1);
			$total	= $notice->count();							  
			$notice = $notice->offset(($page-1)*$pagesize)
						  ->limit($pagesize)
						  ->get();			
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'公告列表',
							'data'	=>	array(
										'page'		=>	$page,
										'pagesize'	=>	$pagesize,
										'totalpage'	=>	ceil($total/$pagesize),
										'notice'	=>	$notice,
							),
			);
		}while(FALSE);
		
		json($result);
	}
	/**
	 *接口名称	公告详情
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function noticeViewAction(){			
		do{	
			$id			= $this->get('id', 		1);
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'id',  'value'=>$id,	 'role'=>'required|gte:1', 'msg'=>'公告ID格式有误.'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/			
			$notice = DB::table('t_notice')->select('id','title','introduce','content','create_date')->find($id);
			$preid	= DB::table('t_notice')->select('id')->where('create_date','<',$notice['create_date'])->orderby('create_date', 'DESC')->first()['id'];
			$nextid	= DB::table('t_notice')->select('id')->where('create_date','>',$notice['create_date'])->orderby('create_date', 'ASC')->first()['id'];
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'公告详情',
							'data'	=>	array(						
										'notice'	=>	$notice,
										'preid'		=>	$preid,
										'nextid'	=>	$nextid,
							),
			);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	会员消息列表
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function messageAction(){			
		do{	
			$token		= $this->get('token', 		'');
			$page		= $this->get('page', 		1);
			$pagesize	= $this->get('pagesize', 	20);
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识为空.'],
					['name'=>'page',   'value'=>$page,	 'role'=>'required|gte:1', 'fun'=>'isInteger',   'msg'=>'页码格式有误.'],
					['name'=>'pagesize','value'=>$pagesize,'role'=>'required|gte:1', 'fun'=>'isInteger', 'msg'=>'页量格式有误.'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}		
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'请重新登陆.',
							'data'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			$message= DB::table('user_notice')->select('id','content','has_read','created_at')
										  ->where('user_id','=',$myuser['uid']);
			$total	= $members->count();							  
			$members= $members->offset(($page-1)*$pagesize)
										  ->limit($pagesize)
										  ->get();
			if(!empty($message)&&is_array($message)){
			foreach($message as $k=>&$v){	
					$v['content']	= mb_substr($v['content'], 0, 20, 'utf-8');
			}}
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'个人列表',
							'data'	=>	array(
										'page'		=>	$page,
										'pagesize'	=>	$pagesize,
										'totalpage'	=>	ceil($total/$pagesize),
										'message'	=>	$message,
							),
			);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	会员消息详情
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function messageViewAction(){			
		do{	
			$token		= $this->get('token', 		'');
			$id			= $this->get('id', 			0);
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识为空.'],
					['name'=>'id',   	'value'=>$id,	 'role'=>'required|gte:1',  'fun'=>'isInteger',   'msg'=>'消息ID格式有误.'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}		
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'请重新登陆.',
					);
					break;
			}
			/***参数验证EOF***/
			$message= DB::table('user_notice')->select('id','content','has_read','created_at')
										  ->where('user_id','=',$myuser['uid'])
										  ->where('id','=',$id)
										  ->first();
			if(empty($message)){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'只能读自己的消息.',
					);
					break;
			}
			if($message['has_read']==0){
				if(DB::table('user_notice')->where('user_id','=',$myuser['uid'])->where('id','=',$id)->update(['has_read'=>1])){
					$message['has_read']	=	1;
				}
			}
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'个人列表',
							'data'	=>	array(										
										'message'	=>	$message,
							),
			);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	上传身份证
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function uploadIdCardAction(){
		do{	
			$token		= $this->get('token', 		'');
			$type		= $this->get('type', 		'1');
			$img		= $this->get('img', 		'');			
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识为空.'],
					['name'=>'type',   'value'=>$type,	 'role'=>'required|in:1,2,3', 'msg'=>'上传图片类型为空.'],
					['name'=>'img',    'value'=>$img,	 'role'=>'required', 'msg'=>'上传图片为空.'],
			);
			$result		= Validate::check($inputs);
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}		
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'请重新登陆.',
					);
					break;
			}
			$ret =  json_decode(http_post_json('http://api.scsj.net.cn/userUpload?token=c1e5a22922af88c7483aa86bde1ccae9', json_encode(['img'=>$img])), TRUE);			
			if($ret['ret']>0){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'照片上传失败,请重试.',
					);
					break;
			}
			$photourl	=	$ret['data']['cdn_url'];
			switch($type){
				case 1:
					$rows	=['front_img'	=>	$photourl];
					break;
				case 2:
					$rows	=['reverse_img'	=>	$photourl];
					break;
				case 3:
					$rows	=['idcard_img'	=>	$photourl];
					break;
			}			
			if (DB::table('t_user')->where('id','=',$myuser['uid'])->update($rows)===FALSE) {
				$result	= array(
					'ret'	=>	'1',
					'msg'	=>	'更新身份证照片更新失败.',
				);
			}else{
				$result	= array(
					'ret'	=>	'0',
					'msg'	=>	'身份证照片更新成功.',
					'data'	=>	array(			
									'type'		=>	$type,
									'photourl'	=>	$photourl
					)
				);
			}
		}while(FALSE);

		json($result);
	}
	
	/**
	 *接口名称	上传头像
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function uploadavatarAction(){
		do{	
			$token		= $this->get('token', 		'');
			$img		= $this->get('img', 		'');			
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识为空.'],
					['name'=>'img',    'value'=>$img,	 'role'=>'required', 'msg'=>'上传图片为空.'],
			);
			$result		= Validate::check($inputs);
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}		
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'请重新登陆.',
					);
					break;
			}
			$ret =  json_decode(http_post_json('http://api.scsj.net.cn/userUpload?token=c1e5a22922af88c7483aa86bde1ccae9', json_encode(['img'=>$img])), TRUE);			
			if($ret['ret']>0){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'照片上传失败,请重试.',
					);
					break;
			}
			$photourl	=	$ret['data']['cdn_url'];			
			$rows		=	['user_avatar'	=>	$photourl];					
			if (DB::table('t_user')->where('id','=',$myuser['uid'])->update($rows)===FALSE) {
				$result	= array(
					'ret'	=>	'1',
					'msg'	=>	'更新头像更新失败.',
				);
			}else{
				$result	= array(
					'ret'	=>	'0',
					'msg'	=>	'会员头像更新成功.',
					'data'	=>	array(			
									'photourl'	=>	$photourl
					)
				);
			}
		}while(FALSE);

		json($result);
	}
	
	/**
	 *接口名称	实名认证
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function authenticationAction(){			
		do{	
			$token		= $this->get('token', 		'');
			$realname	= $this->get('realname', 	'');
			$idcard		= $this->get('idcard', 		'');
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识为空.'],
					['name'=>'realname',   	'value'=>$realname,	 'fun'=>'isName',   'msg'=>'真实姓名格式有误.'],
					['name'=>'idcard',   	'value'=>$idcard,	 'fun'=>'isIdcard', 'msg'=>'身份证格式有误.'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}		
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'请重新登陆.',
					);
					break;
			}			
			/***参数验证EOF***/			
			if($myuser['saleuser_state']==2){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'已认证过.',
					);
					break;
			}
			if(!empty($realname)){
				DB::table('t_user')->where('id','=',$myuser['uid'])->update(['user_truename'=>$realname]);
			}
			if(!empty($idcard)){
				DB::table('t_user')->where('id','=',$myuser['uid'])->update(['idcard'=>$idcard]);
			}
			$saleuser	= DB::table('t_user')->select('saleuser_state', 'front_img', 'reverse_img', 'idcard_img', 'user_truename', 'idcard' )
											  ->where('id','=',$myuser['uid'])
											  ->first();											  
			if(empty($saleuser['user_truename'])){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'真实姓名未填.',
					);
					break;
			}
			if(empty($saleuser['idcard'])){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'身份证号未填.',
					);
					break;
			}
			if(empty($saleuser['front_img'])||empty($saleuser['reverse_img'])){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'身份证照片未上传完整.',
					);
					break;
			}			
			if(DB::table('t_user')->where('id','=',$myuser['uid'])->update(['saleuser_state'=>1])===FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'提交认证失败，请重试.',
					);
					break;
			}
			$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'已提交认证，请等待审核.',
			);
		}while(FALSE);
		
		json($result);
	}
	
	
	#留言反馈
	public function feedbackAction(){		
		$token		= $this->get('token', 		'');
		$message	= $this->get('message', 	'');
		/***参数验证BOF***/
		$inputs	= array(			
				['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识为空.'],
				['name'=>'message','value'=>$message,'role'=>'required', 'msg'=>'反馈内容为空.'],
		);
		$result		= Validate::check($inputs);			
		if(!empty($result)){
			ret(1, $result, '输入参数有误.');
		}
		$myuser	= $this->checkTokenValid($token);
		if($myuser==FALSE){
			ret(1, '', '请重新登陆.');				
		}
		/***参数验证EOF***/
		if(DB::table('sc_feedbackmessage')->insert(['userid'=>$myuser['uid'],'message'=>$message])===FALSE){
			ret(1, '', '提交意见失败，请重试.');				
		}
		ret(0, '', '意见已提交成功，感谢您的反馈.');		
	}
		
	/**
	 *接口名称	发送短信
	 *接口地址	http://api.com/public/smscode/
	 *接口说明	发送验证码短信
	 *参数 @param
	 * @phone    手机号码 
	 *返回 @return
	 *返回格式	Json
	 * @code   验证码
	 *
	 **/
	public function smscodeAction(){
		do{
			$type	= $this->get('type', 	1);		//1：登陆短信 2：注册 3：修改密码 4：消息短信
			$phone	= $this->get('phone', 	'');			
			$inputs	= array(
					['name'=>'type',  'value'=>$type,	'role'=>'in:1,2,3,4', 'fun'=>'isInteger', 'msg'=>'短信类型格式有误'],
					['name'=>'phone', 'value'=>$phone,	'role'=>'min:11|max:11|required', 'fun'=>'isPhone', 'msg'=>'手机号码格式有误'],
			);
			$result		= Validate::check($inputs);
			if(	!empty($result) ){
				$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
				);
				break;
			}
			if($type!=2){
				if(DB::table('t_user')->where('phone','=',$phone)->count()==0){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'手机号不存在.',
							'data'	=>	[],
					);
					break;	
				}
			}
			
			if($phone!='15378790388'){
			if(DB::table('scsj_smslog')->where('phone','=',$phone)->where('created','>',strtotime(date('Y-m-d 00:00:00')))->count()>=5){
				$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'本日发送次数已超过五条.',
							'data'	=>	[],
				);
				break;
			}
			}
			$smscode= rand(100000, 999999);
			$rows	=	array(
				'phone'	=>	$phone,
				'from'	=>	'财富宝',
				'type'	=>	$type,
				'sn'	=>	$smscode,
				'created'=>	time(),
			);
			if(DB::table('scsj_smslog')->insert($rows)===FALSE){
				$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'记录短信失败.',
							'data'	=>	[],
				);
				break;
			}			
			/***测试环境，不发短信bof***			
			if( $this->config->application->debug==TRUE ){
				$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'短信发送成功',
							'data'	=>	array(
										'type'		=>	$type,
										'phone'		=>	$phone
							),
				);
				break;
			}
			/***测试环境，不发短信eof***/
			
			/***发送短信bof***/
			$rows	=	array(
				'token'		=>	'c1e5a22922af88c7483aa86bde1ccae9',
				'phoneNo'	=>	$phone,
				'sn'		=>	$smscode,
			);
			echo curl_data('http://api.scsj.net.cn/sms', $rows);
			exit;			
			/***发送短信eof***/
		}while(FALSE);
		
		json($result);
	}
		
	/**
	 *接口名称	退出登陆
	 *接口地址	http://api.com/public/logout/
	 *接口说明	清除token，退出登陆
	 *参数 @param无
	 *返回 @return无
	 **/
	public function logoutAction(){	
		do{
			$token		= $this->get('token', 		'');			
			/***参数验证BOF***/
			$inputs	= array(			
					['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'登陆标识为空.'],
			);
			$result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			/***参数验证EOF***/
			$myuser	= $this->checkTokenValid($token);
			if($myuser==FALSE){
					$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'不在登陆状态.'
					);
					break;
			}
			if( Cache::exists('auth_'.$token) ){
					#DB::table('t_user')->where('token','=',$token)->update(['token'=>'']);
					#Cache::delete('auth_'.$token);
					$result	= array(
							'ret'	=>	'0',
							'msg'	=>	'退出成功.',
					);
					break;
			}
			$result	= array(
							'ret'	=>	'1',
							'msg'	=>	'已经退出.'
					);
			break;			
		}while(FALSE);
		
		json($result);
	}	
	
}
