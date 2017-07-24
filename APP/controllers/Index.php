<?php
use Illuminate\Database\Capsule\Manager as DB;

class IndexController extends CoreController {
	private static $datatype;
	private static $callback;
	private static $client;
	
	public function init(){
		parent::init();				
		Yaf_Dispatcher::getInstance()->disableView();		
		
		self::$datatype	= $this->getRequest()->get('datatype', 'json');	
		self::$callback	= $this->getRequest()->get('callback', 'callback');	
		$inputs		= array(
						['name'=>'datatype',  	'value'=>self::$datatype,	'fun'=>'isFileName',	'msg'=>'数据类型有误'],
						['name'=>'callback',  	'value'=>self::$callback,	'fun'=>'isFileName',	'msg'=>'回调变量有误'],
					);
		$result		= Validate::check($inputs);
		if(	!empty($result) ){
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'输入参数有误.',
								'data'	=>	$result,
				);
				json($result, self::$datatype, self::$callback);
		}
		
		self::$client	=new Yar_client('http://cp.uu235.com/rpc');
		self::$client->SetOpt(YAR_OPT_CONNECT_TIMEOUT, 5000);
	}
			
	/***生成验证码图片***/
	public function yzcodeAction(){

		Captcha::generate(3);

	}
	
	/**
	 *接口名称	APP欢迎页
	 *接口地址	http://api.com/public/index/
	 *接口说明	显示欢迎页图片
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @images   图片地址组
	 *
	 **/
	public function indexAction(){
		$arr	= ['type'=>0];	
		/***ksort($arr);***/
		$rows	= self::$client->getImages($arr, authcode('getImages'.implode($arr), 'ENCODE'));
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	DB::table('info')->find(1)['value'],
							'data'	=>	$rows,
						);
		json($result, self::$datatype, self::$callback);
	}
	
	/**
	 *接口名称	轮播图
	 *接口地址	http://api.com/public/scrollimg/
	 *接口说明	显示轮播图
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @images   轮播图片地址组
	 *
	 **/
	public function scrollimgAction(){
		$arr	= ['type'=>1];
		$rows	= self::$client->getImages($arr, authcode('getImages'.implode($arr), 'ENCODE')); 
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'主页轮播图片',
							'data'	=>	$rows,
						);
		json($result, self::$datatype, self::$callback);
	}
		
	/**
	 *接口名称	主页内容
	 *接口说明	询价列表页
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @tiyan  		体验标
	 * @recommend	推荐标
	 *
	 **/
	public function homeAction(){
		$arr	= ['type'=>1];
		$rows	= self::$client->getHome($arr, authcode('getImages'.implode($arr), 'ENCODE')); 
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'主页轮播图片',
							'data'	=>	$rows,
						);
		json($result, self::$datatype, self::$callback);
	}
	
	/**
	 *接口名称	搜索询价
	 *接口地址	http://api.com/user/index/
	 *接口说明	显示欢迎页图片
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @images   图片地址组
	 *
	 **/
	public function searchAction(){
		do{	
			$startdate  =  date('Y-m-d', strtotime($this->get('startdate', date('Y-m-d'))));
			$fromcity   =  $this->get('fromcity', '');
			$tocity   	=  $this->get('tocity', '');
			
			$pagenum        =  intval($this->get('pagenum', 1));
			$pagesize    	=  intval($this->get('pagesize', 10));
			$startpagenum	=  ($pagenum-1) * $pagesize;
			$limit			=  " LIMIT {$startpagenum}, {$pagesize} ";			
			$sortorder		=  " ORDER BY a.id DESC ";			
			$conditions		=  " WHERE a.status=1 AND a.fromcity like '%{$fromcity}%' AND a.tocity like '%{$tocity}%' AND left(a.startdate,10)='{$startdate}' ";			
			
			$_DB	=	new Model;					
			$rows	= $_DB->getAll("SELECT a.*,b.showname,b.sex,b.logo,b.address,b.email,b.phone,b.autobrand,b.autovin from {trip} a inner join {members} b 
											on a.members_id=b.id " . $conditions . $sortorder . $limit );
			$total	= $_DB->getValue("SELECT count(*) from {trip} a " . $conditions);											
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'未找到相关行程.',
							'data'	=>	array(),
						);
				break;
			}
						
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'找到的相关行程.',
							'msg'	=>	' ',
							'data'	=>	array(
											'fromcity'	=>	$fromcity,
											'tocity'	=>	$tocity,
											'pagenum'	=>	$pagenum,
											'pagesize'	=>	$pagesize,											
											'total'		=>	$total,
											'totalpage'	=>	ceil($total/$pagesize),
											'list' 		=>	$rows,
										)
						);
		}while(FALSE);
		
		json($result, self::$datatype, self::$callback);
	}
	
	/**
	 *接口名称	提示消息
	 *接口地址	http://api.com/public/tips/
	 *接口说明	提示消息
	 *参数 @param
	 *返回 @return
	 * @caption   	  提示消息
	 *
	 **/
	public function tipsAction(){
		if( $this->method()!='POST' ){
			$result	= array(
						'code'	=>	'0',
						'msg'	=>	'调用方式错误',
						'data'	=>	array(),
					);
			json($result, self::$datatype, self::$callback);
		}
		
		$result		= array(
						'code'	=>	'1',
						'msg'	=>	'数据读取成功',
						'data'	=>	array(
										'caption'	=>	'工作日固定发标时间在 09:00、14:00、18:00，其余时间与周末随机发标',
									),
					);
		json($result, self::$datatype, self::$callback);
	}
			
	/**
	 *接口名称	选择省
	 *接口地址	http://api.com/public/selectprovince/
	 *接口说明	返回省列表
	 *参数 @param
	 * 空
	 *返回 @return
	 * @id		省ID
	 * @title	省名称
	 **/
	public function selectProvinceAction(){		
		$arr	= ['up'=>0];
		$rows	= self::$client->getCity($arr, authcode('getCity'.implode($arr), 'ENCODE')); 
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'选择省份',
							'data'	=>	$rows,
						);
		json($result, self::$datatype, self::$callback);
	}
	
	/**
	 *接口名称	选择市
	 *接口地址	http://api.com/public/selectcity/
	 *接口说明	返回市列表
	 *参数 @param
	 * @province_id	省ID
	 *返回 @return
	 * @id		市ID
	 * @title	市名称
	 **/
	public function selectCityAction(){	
		do{
			$up		= intval($this->get('up', 	'0'));
			if( empty($up) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'传递参数错误',
							'data'	=>	array(
									'up'	=>	'上级参数不能为空.',
							),
						);
				break;
			}
			
			$arr	= ['up'=>$up];
			$rows	= self::$client->getCity($arr, authcode('getCity'.implode($arr), 'ENCODE')); 
			$result	= array(
								'code'	=>	'1',
								'msg'	=>	'选择城市',
								'data'	=>	$rows,
							);		
		}while(FALSE);
		
		json($result, self::$datatype, self::$callback);
	}
	
	/**
	 *接口名称	汽车品牌
	 *接口说明	返回汽车品牌
	 *参数 @param
	 * 空
	 *返回 @return
	 **/
	public function carbrandAction(){		
		$recommend	= intval($this->get('recommend', 	'0'));
		$arr	= ['recommend'=>$recommend];
		$rows	= self::$client->carBrand($arr, authcode('carBrand'.implode($arr), 'ENCODE')); 
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'汽车品牌',
							'data'	=>	$rows,
						);
		json($result, self::$datatype, self::$callback);
	}
	
	/**
	 *接口名称	选择汽车厂家
	 *参数 @param
	 *返回 @return
	 **/
	public function carfactoryAction(){	
		do{
			$brand_id		= intval($this->get('brand_id', 	'0'));
			if( empty($brand_id) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'传递参数错误',
							'data'	=>	array(),
						);
				break;
			}
			
			$arr	= ['brand_id'=>$brand_id];
			$rows	= self::$client->carFactory($arr, authcode('carFactory'.implode($arr), 'ENCODE')); 
			$result	= array(
								'code'	=>	'1',
								'msg'	=>	'选择汽车厂家',
								'data'	=>	$rows,
							);		
		}while(FALSE);
		
		json($result, self::$datatype, self::$callback);
	}
	
	/**
	 *接口名称	选择汽车厂家
	 *参数 @param
	 *返回 @return
	 **/
	public function carseriesAction(){	
		do{
			$factory_id		= intval($this->get('factory_id', 	'0'));
			if( empty($factory_id) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'传递参数错误',
							'data'	=>	array(),
						);
				break;
			}
			
			$arr	= ['factory_id'=>$factory_id];
			$rows	= self::$client->carSeries($arr, authcode('carSeries'.implode($arr), 'ENCODE')); 
			$result	= array(
								'code'	=>	'1',
								'msg'	=>	'选择汽车系列',
								'data'	=>	$rows,
							);		
		}while(FALSE);
		
		json($result, self::$datatype, self::$callback);
	}
	
	/**
	 *接口名称	选择汽车型号
	 *参数 @param
	 *返回 @return
	 **/
	public function carmodelAction(){	
		do{
			$series_id		= intval($this->get('series_id', 	'0'));
			if( empty($series_id) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'传递参数错误',
							'data'	=>	array(),
						);
				break;
			}
			
			$arr	= ['series_id'=>$series_id];
			$rows	= self::$client->carModel($arr, authcode('carModel'.implode($arr), 'ENCODE')); 
			$result	= array(
								'code'	=>	'1',
								'msg'	=>	'选择汽车型号',
								'data'	=>	$rows,
							);		
		}while(FALSE);
		
		json($result, self::$datatype, self::$callback);
	}
	
	/**
	 *接口名称	选择汽车配件
	 *参数 @param
	 *返回 @return
	 **/
	public function carpartsAction(){	
		do{
			$parts_id		= intval($this->get('parts_id', 	'0'));
			$is_showsub		= intval($this->get('is_showsub', 	'0'));
			
			$arr	= ['parts_id'=>$parts_id, 'is_showsub'=>$is_showsub];
			$rows	= self::$client->carParts($arr, authcode('carParts'.implode($arr), 'ENCODE')); 
			$result	= array(
								'code'	=>	'1',
								'msg'	=>	'选择汽车配件',
								'data'	=>	$rows,
							);		
		}while(FALSE);
		
		json($result, self::$datatype, self::$callback);
	}
	
	
	/**
	 *接口名称	发送短信
	 *接口地址	http://api.com/public/sendmsg/
	 *接口说明	发送验证码短信
	 *参数 @param
	 * @phone    手机号码 
	 *返回 @return
	 *返回格式	Json
	 * @code   验证码
	 *
	 **/
	public function sendMsgAction(){
		do{
			$phone		= $this->get('phone', 	'');
			$inputs	= array(
							['name'=>'phone',  'value'=>$phone,	 'fun'=>'isPhone', 'msg'=>'手机号码格式有误'],
						);
			$result		= Validate::check($inputs);
			if(	!empty($result) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
				);
				break;
			}
			
			/***测试环境，不发短信bof***/			
			if( $this->config->application->debug==TRUE ){
				$result	= array(
							'code'	=>	'1',
							'msg'	=>	'短信发送成功',
							'data'	=>	array(
											'status'	=>	1,
											'phone'		=>	$phone,
											'code'		=>	'1111',
										),
				);
				break;
			}
			/***测试环境，不发短信eof***/
			
			$url 	= 'http://www.sendcloud.net/smsapi/send';
			$rand 	= rand(1111,9999);
			$param 	= array(
				'smsUser' 	=> 'sms_web', 
				'templateId'=> '21580',
				'msgType' 	=> '0',
				'phone' 	=> $phone,
				'vars' 		=> '{"%code%":"'.$rand.'"}'
			);			
			$myCache	= Cache::getInstance();
			$myCache->set('msg_'.$phone, $rand, 300);
			
			$sParamStr = "";
			ksort($param);
			foreach ($param as $sKey => $sValue) {
				$sParamStr .= $sKey . '=' . $sValue . '&';
			}
			$sParamStr = trim($sParamStr, '&');
			$smskey = 'Jsspj9dNGOGBVYZnxHgz3WUJYQoiY7Tjj';
			$sSignature = md5($smskey."&".$sParamStr."&".$smskey);
			$param['signature'] = $sSignature;
			$data = http_build_query($param);
			$options = array(
				'http' => array(
					'method' => 'POST',
					'header' => 'Content-Type:application/x-www-form-urlencoded',
					'content' => $data

			));
			$context = stream_context_create($options);
			$result  = json_decode(file_get_contents($url, FILE_TEXT, $context), TRUE);
			
			if( $result['statusCode']=='200' ){
				$result	= array(
							'code'	=>	'1',
							'msg'	=>	'短信发送成功',
							'data'	=>	array(
											'status'	=>	1,
											'phone'		=>	$phone,
											'code'		=>	$rand,
										),
						);
				break;
			}else{
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'短信发送失败，请重试.',
							'data'	=>	array(),
						);
				break;
			}
		}while(FALSE);
		
		json($result, self::$datatype, self::$callback);
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
			$type		= $this->get('type', 		0);
			$phone		= $this->get('phone', 		'');
			$yzcode		= $this->get('yzcode', 		'');
			$password	= $this->get('password', 	'');
			$repassword	= $this->get('repassword',	'');			
			$inputs	= array(
					['name'=>'type',   'value'=>$type,	 'fun'=>'isType',  'msg'=>'身份选择有误'],
					['name'=>'phone',  'value'=>$phone,	 'fun'=>'isPhone', 'msg'=>'手机号码格式有误'],
					['name'=>'yzcode', 'value'=>$yzcode, 'fun'=>'isYzcode','msg'=>'验证码格式有误'],
			);
			$result		= Validate::check($inputs);
			if( $password=='' ){
					$result['password']		= '密码不能为空';				
			}
			if( $password!=$repassword ){
					$result['repassword']	= '重复密码不一致';				
			}
			if(	!empty($result) ){
					$result	= array(
							'code'	=>	'0',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}			
			
			/***验证yzcodeBOF***/
			if(Yaf_Registry::get('config')['application']['debug']==FALSE && Cache::getInstance()->get('msg_'.$phone)!=$yzcode){
					$result	= array(
								'code'	=>	'0',
								'msg'	=>	'验证码不正确.',
								'data'	=>	[],
							);
					break;
			}
			/***验证yzcodeEOF***/
			
			if( DB::table('members')->where('phone','=',$phone)->count()>0 ){
					$result	= array(
							'code'	=>	'0',
							'msg'	=>	'此手机号已存在，请直接登陆.',
							'data'	=>	[],
						);
					break;
			}							
			$rows	=	array(
							'type'			=>	$type,
							'phone'			=>	$phone,
							'password'		=>	md5($password),		
							'is_root'		=>	1,
							'status'		=>	1,
							'created_at'	=>	date('Y-m-d H:i:s'),
			);
			$lastId = DB::table('members')->insertGetId($rows);
			if ($lastId) {				
					/***设置登陆token***/
					if( $token=(new membersModel)->setUserLogin($phone, $password) ){					
							$result	= array(
									'code'	=>	'1',
									'msg'	=>	'注册成功，感谢您的使用.',
									'data'	=>	array(
													'token'		=>	$token,
													'userinfo'	=>	array(
															'user_id'		=>	$lastId,
															'type'			=>	$type,
															'phone'			=>	$phone,
													),
												)
							);
							break;
					}
			}
			$result	= array(
					'code'	=>	'0',
					'msg'	=>	'用户注册失败',
					'data'	=>	array(),
			);
		}while(FALSE);
		
		json($result, self::$datatype, self::$callback);	
	}
	
		
	/**
	 *接口名称	找回密码，修改密码，重置密码
	 *接口地址	http://api.com/user/resetpwd/
	 *接口说明	清除token，退出登陆
	 *参数 @param无
	 *返回 @return无
	 **/	
	public function resetPwdAction(){
		do{	
			$phone		= $this->get('phone',		'');
			$yzcode		= $this->get('yzcode', 		'');
			$password 	= $this->get('password', 	'');
			$repassword = $this->get('repassword',	'');			
			$inputs	= array(					
					['name'=>'phone',  'value'=>$phone,	 'fun'=>'isPhone', 'msg'=>'手机号码格式有误'],
					['name'=>'yzcode', 'value'=>$yzcode, 'fun'=>'isYzcode','msg'=>'验证码格式有误'],
			);
			$result		= Validate::check($inputs);
			if( $password=='' ){
					$result['password']		= '密码不能为空';				
			}
			if( $password!=$repassword ){
					$result['repassword']	= '重复密码不一致';				
			}
			if(	!empty($result) ){
					$result	= array(
							'code'	=>	'0',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}
			
			/***验证yzcodeBOF***/
			if(Yaf_Registry::get('config')['application']['debug']==FALSE && Cache::getInstance()->get('msg_'.$phone)!=$yzcode){
					$result	= array(
								'code'	=>	'0',
								'msg'	=>	'验证码不正确.',
								'data'	=>	[],
							);
					break;
			}
			/***验证yzcodeEOF***/
						
			$myuser		=	DB::table('members')->where('phone','=',$phone);
			if( $myuser->count()<=0 ){
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'未找到对应的手机号.',
							'data'	=>	array(),
						);
						break;
			}
			$rows	=	array(
					'password'		=>	md5($password),
					'updated_at'	=>	date('Y-m-d H:i:s'),
			);
			if ($myuser->update($rows)!==FALSE) {
						$result	= array(
							'code'	=>	'1',
							'msg'	=>	'更新密码成功.',
							'data'	=>	array(
											'status'	=> 1,
										),
						);			
						break;
			}else{

						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'更新失败.',
							'data'	=>	array(),
						);
			}								

		}while(FALSE);

		json($result, self::$datatype, self::$callback);
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
			$inputs		= array(
								['name'=>'phone',  'value'=>$phone,	 'fun'=>'isPhone'],
						);						
			$result		= Validate::check($inputs);
			if( $password=='' ){
					$result['password']	= '密码不能为空';				
			}
			if(	!empty($result) ){
					$result	= array(
								'code'	=>	'0',
								'msg'	=>	'输入参数有误.',
								'data'	=>	$result,
					);
					break;
			}

			
			$sysusers =new membersModel();
			if ($sysusers->checkphone($phone)==FALSE) {
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'未找到匹配手机号.',
							'data'	=>	array(),
						);			
						break;
			}		

			$myCache 		= Cache::getInstance();
			$try_times_key	= 'login_'.$phone;
			if ($sysusers->checkPassword($phone, $password)==FALSE){
				if(!$myCache->exists($try_times_key)){
					$try_times = 1;
					$myCache->set($try_times_key, 1, 600);			
				}else{
					$try_times = $myCache->get($try_times_key);
					$myCache->set($try_times_key, $try_times+1, 600);
				}				
				if($try_times>10){					
					$result	= array(
							'code'	=>	'0',
							'msg'	=>	'重试次数过多了， 10分钟后再重试吧.',
							'data'	=>	array(),
					);					
				}else{
					$result	= array(
							'code'	=>	'0',
							'msg'	=>	'密码有误.',
							'data'	=>	array(),
					);
				}
				break;
			}

			if( $token=$sysusers->setUserLogin($phone, $password) ){
						$myCache->delete($try_times_key);
						$userid	= $myCache->get($token);
						$rows	= (new membersModel)->getUser($userid);
						
						$result	= array(
							'code'	=>	'1',
							'msg'	=>	'登陆成功.',
							'data'	=>	array(
											'token'		=>	$token,
											'userinfo'	=>	array(
															'id'			=>	$rows['id'],
															'type'			=>	$rows['type'],
															'city_id'		=>	$rows['city_id'],
															'phone'			=>	$rows['phone'],
															'name'			=>	$rows['name'],															
															'headlogo'		=>	$rows['headlogo'],																	
															'company'		=>	$rows['company'],
														)
										),
						);
			}else{

						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'登陆失败.',
							'data'	=>	array(),
						);
			}								

		}while(FALSE);

		json($result, self::$datatype, self::$callback);
	}	
	
	/**
	 *接口名称	验证token是否有效
	 *接口地址	http://api.com/public/checktoken/
	 *接口说明	验证token
	 *参数 @param
	 * @token 		登陆标识
	 *返回 @return	
	 * @status		token状态
	 **/
	public function checktokenAction(){
		do{		
			$token	  = addslashes($this->get('token', NULL));
			if( $token==NULL ){
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'参数为空',
							'data'	=>	array(),
						);
						break;
			}			
			if( (self::checklogin($token))==FALSE ){
				$result	= array(
					'code'	=>	'0',
					'msg'	=>	'token无效.',
					'data'	=>	array(),
				);				
			}else{
				$result	= array(
					'code'	=>	'1',
					'msg'	=>	'token有效.',
					'data'	=>	array(
									'status' =>	1,
								),
				);
			}
		}while(FALSE);

		json($result, self::$datatype, self::$callback);
	}

	/**
	 *私有方法	验证登陆
	 *方法说明	验证token，返回用户ID
	 *参数 @param
	 * @token 	标记
	 *返回 @return	
	 * @user_id   	成功返回用户ID
	 * FALSE		失败返回FALSE
	 **/
	private static function checklogin($token){
		$myCache 		= Cache::getInstance();
		if( !$myCache->exists($token) ){
			return FALSE;
		}else{
			$myCache->expire($token, 86400);
			return $myCache->get($token);
		}
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
			$token	  = $this->get('token', NULL);
			if( $token==NULL ){
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'参数为空',
							'data'	=>	array(),
						);
						break;
			}		
			
			$myCache = Cache::getInstance();
			if( $myCache->exists($token) ){							
				$myCache->delete($token);
			}			
			$result	= array(
				'code'	=>	'1',
				'msg'	=>	'安全退出.',
				'data'	=>	array(
								'status' =>	1,
							),
			);
		}while(FALSE);

		json($result, self::$datatype, self::$callback);
	}
	
	/**
	 *接口名称	询价单
	 *接口说明	
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function inquiryListAction(){		
		$arr	= array(
					'keywords'	=>	'',
					'pagenum'	=>	1,
					'pagesize'	=>	10,
				);
		$rows	= self::$client->getInquiryList($arr, authcode('getInquiryList'.implode($arr), 'ENCODE')); 
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'询价单列表',
							'data'	=>	$rows,
						);
		json($result, self::$datatype, self::$callback);		
	}
	
	/**
	 *接口名称	询价单详情
	 *接口说明	
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function inquiryViewAction(){		
		do{	
			$id				=  $this->get('id', 	0);
			if( $id==0 ){
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'参数为空',
							'data'	=>	array(),
						);
						break;
			}	
			
			$arr	= ['id'	=>	$id];
			$rows	= self::$client->getInquiry($arr, authcode('getInquiry'.implode($arr), 'ENCODE')); 
			$result	= array(
								'code'	=>	'1',
								'msg'	=>	'询价单详情',
								'data'	=>	$rows,
							);
		}while(FALSE);
		json($result, self::$datatype, self::$callback);
	}
	
		
	/***关于我们***/
	public function aboutusAction(){
		$arr	= ['id'=>1];
		$rows	= self::$client->getPages($arr, authcode('getPages'.implode($arr), 'ENCODE'));
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'关于我们',
							'data'	=>	$rows,
						);
		json($result, self::$datatype, self::$callback);
    }	
	/***服务协议***/
	public function serviceAction(){						
		$arr	= ['id'=>4];
		$rows	= self::$client->getPages($arr, authcode('getPages'.implode($arr), 'ENCODE'));
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'服务协议',
							'data'	=>	$rows,
						);
		json($result, self::$datatype, self::$callback);
    }
	/***版本更新***/
	public function versionUpAction(){						
		$arr	= ['id'=>9];
		$rows	= self::$client->getPages($arr, authcode('getPages'.implode($arr), 'ENCODE'));
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'版本更新',
							'data'	=>	$rows,
						);
		json($result, self::$datatype, self::$callback);
    }
	/***联系我们***/
	public function contactUsAction(){						
		$arr	= ['id'=>11];
		$rows	= self::$client->getPages($arr, authcode('getPages'.implode($arr), 'ENCODE'));
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'联系我们',
							'data'	=>	$rows,
						);
		json($result, self::$datatype, self::$callback);
    }
	
	/**
	 *接口名称	APP版本号
	 *接口地址	http://api.com/public/version/
	 *接口说明	显示APP当前版本号
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function versionAction(){		
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'APP当前版本号',
							'data'	=>	array(
											'version'	=>	'0.0.1',
											'link'		=>	'/uploads/apk/app-release.apk',
											'remark'	=>	"1. 更新版本至0.0.1",
										),
						);
		json($result, self::$datatype, self::$callback);
	}
	
}
