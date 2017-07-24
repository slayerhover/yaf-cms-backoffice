<?php
use Illuminate\Database\Capsule\Manager as DB;

class UserController extends CoreController {
    private static $datatype;
    private static $callback;
    private static $client;
	private static $user_id;
	private static $userinfo;
	/**
	 *
	 * 初始化验证
	 *
	 **/
	public function init(){
        parent::init();
        Yaf_Dispatcher::getInstance()->disableView();

        self::$datatype	= $this->get('datatype', 'json');
        self::$callback	= $this->get('callback', 'callback');
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
		/***验证登陆***/
		$token	  = addslashes($this->get('token', ''));
		if( (self::$user_id = self::checklogin($token))==FALSE ){
			$result	= array(
				'code'	=>	'0',
				'msg'	=>	'用户未登陆，请先登陆吧',
				'data'	=>	array(),
			);
			json($result);
		}
		self::$userinfo	=DB::table('members')->find(self::$user_id);
		unset(self::$userinfo['password']);
		
		if(self::$userinfo['company_id']>0){
			self::$userinfo['company']	=	DB::table('company')->find(self::$userinfo['company_id']);
		}

        self::$client	=new Yar_client('http://cp.uu235.com/rpc');
        self::$client->SetOpt(YAR_OPT_CONNECT_TIMEOUT, 5000);
	}
	
	/**
	 *接口名称	用户中心首页
	 *接口地址	http://api.com/user/index/
	 *接口说明	显示欢迎页图片
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @images   图片地址组
	 **/
	public function indexAction(){
		do{
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'用户中心',
							'data'	=>	self::$userinfo,
			);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	个人资料
	 *接口地址	http://api.com/user/info/
	 *接口说明	显示个人资料
	 *参数 @param
	 * @token		登陆令牌
	 *返回 @return
	 * @rows
	 *
	 **/
	public function infoAction(){
		do{	
			if(self::$userinfo['company_id']>0){
				self::$userinfo['company']	=	DB::table('company')->find(self::$userinfo['company_id']);
			}
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'个人及公司资料',
							'data'	=>	self::$userinfo,
						);
		}while(FALSE);

		json($result);
	}
	
	/**
	 *接口名称	实名认证
	 *接口地址	http://api.com/user/consummate/
	 *接口说明	新注册用户完善个人信息
	 *参数 @param
	 * @realname 	姓名
	 * @card_id 	身份证号
	 * @email		邮箱
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function authAction(){
		do{
			if( self::$userinfo['is_root']!=1 ){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'公司主账号才可以提交认证申请.',
						'data'	=>	array(),
					);
					break;
			}
			if( empty(self::$userinfo['name']) || empty(self::$userinfo['company_id']) || empty(self::$userinfo['company']['company']) ){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'请在完善信息后提交.',
						'data'	=>	array(),
					);
					break;
			}
			if( self::$userinfo['company']['authstatus']==2 ){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'认证已通过，无需重复认证.',
						'data'	=>	array(),
					);
					break;
			}			
			
			if (DB::table('company')->where('id','=',self::$userinfo['company_id'])->update(['authstatus'=>1])===FALSE) {
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'提交申请认证信息失败.',
							'data'	=>	array(),
						);
			}else{
						$result	= array(
							'code'	=>	'1',
							'msg'	=>	'提交申请认证成功,请等待审核.',
							'data'	=>	array(
											'status'=>	1,
										),
						);
			}
		}while(FALSE);

		json($result);
	}
	
	/**
	 *接口名称	完善信息
	 *接口地址	http://api.com/user/consummate/
	 *接口说明	新注册用户完善个人信息
	 *参数 @param
	 * @realname 	姓名
	 * @card_id 	身份证号
	 * @email		邮箱
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function consummateAction(){

		do{			
			$name       = $this->get('name', '');
            $email	    = $this->get('email', '');
			$company    = $this->get('company', '');
			$city_id	= $this->get('city_id', '');
			$address	= $this->get('address', '');
			$tel    	= $this->get('tel', '');
			$description= $this->get('description', '');
            $inputs	= array(
                ['name'=>'name', 	'value'=>$name,	 'fun'=>'isChinese', 'msg'=>'姓名格式有误'],                
                ['name'=>'tel', 	'value'=>$tel,	 'fun'=>'isTel',  'msg'=>'电话号码格式有误'],
            );
			if(!empty($email))
				array_push($inputs, ['name'=>'email',	'value'=>$email, 'fun'=>'isEmail','msg'=>'邮箱格式有误']);
            $result		= Validate::check($inputs);
            if(	!empty($result) ){
                $result	= array(
                    'code'	=>	'0',
                    'msg'	=>	'输入参数有误.',
                    'data'	=>	$result,
                );
                break;
            }			
			$rows		=	array(				
								'name'		=>	$name,
								'email'		=>	$email,
							);
			if (DB::table('members')->where('id','=',self::$user_id)->update($rows)===FALSE) {
						$result	= array(
								'code'	=>	'0',
								'msg'	=>	'更新用户信息失败.',
								'data'	=>	array(),
						);
			}else{
						if(self::$userinfo['is_root']==1){
								$rows	=	array(
										'type'		=>	self::$userinfo['type'],
										'city_id'	=>	$city_id,
										'company'	=>	$company,
										'address'	=>	$address,
										'tel'		=>	$tel,
										'description'=>	$description,
								);
								if(self::$userinfo['company_id']==0){							
									$rows['created_at']	=	date('Y-m-d H:i:s');
									$company_id	=	DB::table('company')->insertGetId($rows);
									DB::table('members')->where('id','=',self::$userinfo['id'])->update(['company_id'=>$company_id]);
								}else{
									$rows['updated_at']	=	date('Y-m-d H:i:s');
									DB::table('company')->where('id','=',self::$userinfo['company_id'])->update($rows);
								}
						}
						self::$userinfo	=DB::table('members')->select('id','is_root','name','phone','headlogo','type','status','company_id')
															 ->find(self::$user_id);
						if(self::$userinfo['company_id']>0){
							self::$userinfo['company']	=	DB::table('company')->find(self::$userinfo['company_id']);
						}
						$result	= array(
							'code'	=>	'1',
							'msg'	=>	'用户信息更新成功.',
							'data'	=>	self::$userinfo,
						);
			}
		}while(FALSE);

		json($result);
	}
		
	/**
	 *接口名称	上传头像
	 *接口地址	http://api.com/user/uploadphoto/
	 *接口说明	上传图片，更新用户头像
	 *参数 @param
	 * @logo 		图片文件
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function uploadheadphotoAction(){

		do{			
			$type	= addslashes($this->get('type', ''));
			$files	= $this->get('logo', '');
			if( $files=='' || $type=='' ){
						$result	= array(
								'code'	=>	'0',
								'msg'	=>	'图片类型或者内容为空',
								'data'	=>	array(),
							);
						break;
			}
			
			$config	  = Yaf_Registry::get('config');
			$filename = 'logo-t' . time() . '.' . $type;				
			$descdir  = $config['application']['uploadpath'] . '/logo/' . date('Ym') . '/';
			if( !is_dir($descdir) ){ mkdir($descdir, 0777, TRUE); }
			$realpath = $descdir . $filename;				
			
			if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $files, $base64result)){			 
			  if (file_put_contents($realpath, base64_decode(str_replace(' ', '+', str_replace($base64result[1], '', $files))))){
				$newfile = str_replace('./', '', $realpath);
			  }else{
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'储存图片出错.',
								'data'	=>	array(),
							);
				break;
			  }
			}elseif (file_put_contents($realpath, base64_decode(str_replace(' ', '+', $files)))){
				$newfile = str_replace('./', '', $realpath);
			}else{
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'储存图片出错.',
								'data'	=>	array(),
							);
				break;
			}
			$photourl	=	$config['application']['scheme'] . '://' . $_SERVER['HTTP_HOST']  . '/' . $newfile;
			$rows		=	array(								
								'headlogo'	=>	$photourl,
							);
			if (DB::table('members')->where('id','=',self::$user_id)->update($rows)===FALSE) {
				$result	= array(
					'code'	=>	'0',
					'msg'	=>	'更新用户头像更新失败.',
					'data'	=>	array(),
				);		
			}else{
				$result	= array(
					'code'	=>	'1',
					'msg'	=>	'用户头像更新成功.',
					'data'	=>	array(
									'status'	=>	1,
									'photourl'	=>	$photourl,
								),
				);
			}
					
		}while(FALSE);

		json($result);
	}
	
	public function uploadcompanylogoAction(){
		do{			
			if(self::$userinfo['is_root']!=1){
				$result	= array(
						'code'	=>	'0',
						'msg'	=>	'唯公司主账号，可更新公司logo.',
						'data'	=>	[],
				);
				break;
			}
			$type	= addslashes($this->get('type', 'jpg'));
			$files	= $this->get('logo', '');
			if( $files=='' || $type=='' ){
						$result	= array(
								'code'	=>	'0',
								'msg'	=>	'图片类型或者内容为空',
								'data'	=>	array(),
							);
						break;
			}
			
			$config	  = Yaf_Registry::get('config');
			$filename = 'logo-t' . time() . '.' . $type;				
			$descdir  = $config['application']['uploadpath'] . '/logo/' . date('Ym') . '/';
			if( !is_dir($descdir) ){ mkdir($descdir, 0777, TRUE); }
			$realpath = $descdir . $filename;				
			
			if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $files, $base64result)){			 
			  if (file_put_contents($realpath, base64_decode(str_replace(' ', '+', str_replace($base64result[1], '', $files))))){
				$newfile = str_replace('./', '', $realpath);
			  }else{
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'储存图片出错.',
								'data'	=>	array(),
							);
				break;
			  }
			}elseif (file_put_contents($realpath, base64_decode(str_replace(' ', '+', $files)))){
				$newfile = str_replace('./', '', $realpath);
			}else{
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'储存图片出错.',
								'data'	=>	array(),
							);
				break;
			}
			$photourl	=	$config['application']['scheme'] . '://' . $_SERVER['HTTP_HOST']  . '/' . $newfile;
			$rows		=	array(								
								'logo'	=>	$photourl,
							);
			if (DB::table('company')->where('id','=',self::$userinfo['company_id'])->update($rows)===FALSE) {
				$result	= array(
					'code'	=>	'0',
					'msg'	=>	'更新公司logo更新失败.',
					'data'	=>	array(),
				);		
			}else{
				$result	= array(
					'code'	=>	'1',
					'msg'	=>	'公司logo更新成功.',
					'data'	=>	array(
									'status'	=>	1,
									'photourl'	=>	$photourl,
								),
				);
			}
					
		}while(FALSE);

		json($result);
	}
	
	public function staffAction() {
		do{
			if( self::$userinfo['is_root']!=1 ){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'公司主账号才可以查看员工账号.',
						'data'	=>	array(),
					);
					break;
			}
			$members_id	  = addslashes($this->get('members_id', NULL));
			if($members_id==NULL){
				$rows	= DB::table('members')->where('company_id','=',self::$userinfo['company_id'])->get();
				foreach($rows as $k=>$v) unset($rows[$k]['password']);
			}else{
				$rows	= DB::table('members')->find($members_id);
				if($rows['company_id']!=self::$userinfo['company_id']){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'非本公司员工账号.',
						'data'	=>	[],
					);
					break;
				}
				unset($rows['passowrd']);
			}
			$result	= array(
						'code'	=>	'1',
						'msg'	=>	'公司员工账号.',
						'data'	=>	$rows,
			);
		}while(FALSE);
		
		json($result);
	}

	public function staffaddAction() {
		do{
			if( self::$userinfo['is_root']!=1 ){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'公司主账号才可以创建员工账号.',
						'data'	=>	array(),
					);
					break;
			}
			if( self::$userinfo['company']['authstatus']!=2 ){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'认证已通过后，才可以创建员工账号.',
						'data'	=>	array(),
					);
					break;
			}
			
			$phone		= $this->get('phone', 		'');			
			$password	= $this->get('password', 	'');			
			$name       = $this->get('name', '');
            $email	    = $this->get('email', '');			
			$inputs	= array(
					['name'=>'phone',  'value'=>$phone,	 'fun'=>'isPhone', 'msg'=>'手机号码格式有误'],
			);
			if(!empty($name))	array_push($inputs, ['name'=>'name',	'value'=>$name,  'fun'=>'isName','msg'=>'姓名格式有误']);            
			if(!empty($email))	array_push($inputs, ['name'=>'email',	'value'=>$email, 'fun'=>'isEmail','msg'=>'邮箱格式有误']);
            $result		= Validate::check($inputs);
			if( $password=='' ){
					$result['password']		= '密码不能为空';				
			}
			if(	!empty($result) ){
					$result	= array(
							'code'	=>	'0',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}			
			if( DB::table('members')->where('phone','=',$phone)->count()>0 ){
					$result	= array(
							'code'	=>	'0',
							'msg'	=>	'此手机号已存在.',
							'data'	=>	[],
						);
					break;
			}			
			
			$rows	=	array(
							'type'			=>	self::$userinfo['type'],
							'phone'			=>	$phone,
							'password'		=>	md5($password),
							'company_id'	=>	self::$userinfo['company_id'],
							'is_root'		=>	0,
							'name'			=>	$name,
							'email'			=>	$email,
							'status'		=>	1,
							'created_at'	=>	date('Y-m-d H:i:s'),
			);
			$lastId = DB::table('members')->insertGetId($rows);
			if ($lastId) {												
					$result	= array(
							'code'	=>	'1',
							'msg'	=>	'添加员工账号成功.',
							'data'	=>	array(
											'user_id'		=>	$lastId,
											'type'			=>	self::$userinfo['type'],
											'phone'			=>	$phone,
											'name'			=>	$name,
											'email'			=>	$email,
											'status'		=>	1,
										)
					);
					break;
			}
			$result	= array(
					'code'	=>	'0',
					'msg'	=>	'用户注册失败',
					'data'	=>	array(),
			);
		}while(FALSE);
		
		json($result, self::$datatype, self::$callback);	
	}

	public function staffeditAction() {
		do{
			if( self::$userinfo['is_root']!=1 ){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'公司主账号才可以编辑员工账号.',
						'data'	=>	array(),
					);
					break;
			}
			$members_id	  = addslashes($this->get('members_id', NULL));
			if($members_id==NULL){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'账号ID参数为空.',
						'data'	=>	array(),
					);
					break;
			}else{
				$rows	= DB::table('members')->find($members_id);
				if($rows['company_id']!=self::$userinfo['company_id']){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'非本公司员工账号.',
						'data'	=>	[],
					);
					break;
				}				
			}

			$phone		= $this->get('phone', 		'');			
			$password	= $this->get('password', 	'');			
			$name       = $this->get('name', '');
            $email	    = $this->get('email', '');		
			$status		= $this->get('status', 0);		
			$inputs	= array(
					['name'=>'phone',  'value'=>$phone,	 'fun'=>'isPhone', 'msg'=>'手机号码格式有误'],
			);
			if(!empty($name))	array_push($inputs, ['name'=>'name',	'value'=>$name,  'fun'=>'isName','msg'=>'姓名格式有误']);            
			if(!empty($email))	array_push($inputs, ['name'=>'email',	'value'=>$email, 'fun'=>'isEmail','msg'=>'邮箱格式有误']);
            $result		= Validate::check($inputs);			
			if(	!empty($result) ){
					$result	= array(
							'code'	=>	'0',
							'msg'	=>	'输入参数有误.',
							'data'	=>	$result,
					);
					break;
			}			
			if( DB::table('members')->where('id','<>',$members_id)->where('phone','=',$phone)->count()>0 ){
					$result	= array(
							'code'	=>	'0',
							'msg'	=>	'此手机号已存在.',
							'data'	=>	[],
						);
					break;
			}			
			
			$rows	=	array(			
							'phone'			=>	$phone,			
							'name'			=>	$name,
							'email'			=>	$email,
							'status'		=>	$status,
							'updated_at'	=>	date('Y-m-d H:i:s'),
			);
			if( $password=='' )	$rows['password']=md5($password);			
			if (DB::table('members')->where('id','=',$members_id)->update($rows)!==FALSE) {												
					$result	= array(
							'code'	=>	'1',
							'msg'	=>	'编辑员工账号成功.',
							'data'	=>	array(
											'user_id'		=>	$members_id,
											'type'			=>	self::$userinfo['type'],
											'phone'			=>	$phone,
											'name'			=>	$name,
											'email'			=>	$email,
											'status'		=>	$status,
										)
					);
					break;
			}
			$result	= array(
					'code'	=>	'0',
					'msg'	=>	'用户注册失败',
					'data'	=>	array(),
			);
		}while(FALSE);
		
		json($result, self::$datatype, self::$callback);	
	}
	
	public function staffstatusAction() {
		do{
			if( self::$userinfo['is_root']!=1 ){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'公司主账号才可以修改员工账号.',
						'data'	=>	array(),
					);
					break;
			}
			$members_id	  = addslashes($this->get('members_id', NULL));
			if($members_id==NULL){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'账号ID参数为空.',
						'data'	=>	array(),
					);
					break;
			}
			$members	= DB::table('members')->find($members_id);
			if($members['company_id']!=self::$userinfo['company_id']){
				$result	= array(
					'code'	=>	'0',
					'msg'	=>	'非本公司员工账号.',
					'data'	=>	[],
				);
				break;
			}
			$status	=	$members['status']==1 ? 0 : 1;
			$rows	=	array(
							'status'		=>	$status,
							'updated_at'	=>	date('Y-m-d H:i:s'),
			);
			if (DB::table('members')->where('id','=',$members_id)->update($rows)!==FALSE) {												
					$result	= array(
							'code'	=>	'1',
							'msg'	=>	'修改员工账号状态成功.',
							'data'	=>	array(
											'user_id'		=>	$members_id,											
											'status'		=>	$status,
										)
					);
					break;
			}
			$result	= array(
					'code'	=>	'0',
					'msg'	=>	'用户注册失败',
					'data'	=>	array(),
			);
		}while(FALSE);
		
		json($result, self::$datatype, self::$callback);	
	}
	
	public function walletAction() {
		do{
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'我的钱包',
							'data'	=>	[
											"account"	=> self::$userinfo['company']['account'],
											"frozen"	=> self::$userinfo['company']['frozen'],
											"usemoney"	=> self::$userinfo['company']['usemoney'],
										]
			);
		}while(FALSE);
		
		json($result, self::$datatype, self::$callback);		
	}
			
	/**
	 *接口名称	我的消息
	 *接口地址	http://api.com/user/message/
	 *接口说明	列出我的消息
	 *参数 @param
	 * @status   	整数  0:未读  1：已读
	 * @pagenum		页码 
	 * @pagesize	每页数量
	 * @token		登陆标记
	 *返回 @return
	 * @list		消息列表
	 *
	 **/
	public function messageAction(){		
		$pagenum        =  intval($this->get('pagenum', 1));
        $pagesize    	=  intval($this->get('pagesize', 10));
		
		$rows 		= (new Table('message'))->findAll("receive_user='".self::$user_id."'", '', array($pagenum-1, $pagesize), 'id,name,status,type,content,addtime');
		$counter	= (new Table('message'))->findCount("receive_user='".self::$user_id."'");
		
		$result		= array(
						'code'	=>	'1',
						'msg'	=>	'数据读取成功',
						'data'	=>	array(
										
										'total'		=>	$counter,
										'pagenum'	=>	$pagenum,
										'pagesize'	=>	$pagesize,
										'totalpage'	=>	ceil($counter/$pagesize),
										'list'		=>	(array)$rows,
									),
					);
		json($result);
	}
	
	/**
	 *接口名称	未读消息数
	 *接口地址	http://api.com/user/newmessagenum/
	 *接口说明	列出我的消息
	 *参数 @param
	 * @token		登陆标记
	 *返回 @return
	 * @num			未读消息条数
	 *
	 **/
	public function newmessagenumAction(){	
	
		$counter	= (new Table('message'))->findCount("receive_user='".self::$user_id."' AND status=0");
		
		$result		= array(
						'code'	=>	'1',
						'msg'	=>	'未读消息条数',
						'data'	=>	array(										
										'num'	=>	$counter,
									),
					);
		json($result);
	}
	
	
	/**
	 *接口名称	消息删除
	 *接口地址	http://api.com/user/messagedelete/
	 *接口说明	删除我的消息
	 *参数 @param
	 * @id   		消息ID
	 * @token		登陆标记
	 *返回 @return
	 * @list		消息列表
	 *
	 **/
	public function messagedeleteAction(){		
		do{	
			$id         =  intval($this->get('id',  0));
			$all        =  intval($this->get('all', 0));
			
			if($id==0 && $all==0){			
				$result	= array(
					'code'	=>	'0',
					'msg'	=>	'参数异常.',
					'data'	=>	array(),
				);
				break;				
			}
			if($id>0){
				$rows 		= (new Table('message'))->delete("receive_user='".self::$user_id."' AND id='{$id}'");
			}elseif($all==1){
				$rows 		= (new Table('message'))->delete("receive_user='".self::$user_id."'");
			}
			
			if($rows){
				$result		= array(
							'code'	=>	'1',
							'msg'	=>	'消息删除成功',
							'data'	=>	array(										
											'status'	=> 1,
										),
						);
			}else{
				$result		= array(
							'code'	=>	'1',
							'msg'	=>	'消息删除失败',
							'data'	=>	array(),
						);
			}
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	我的积分
	 *接口地址	http://api.com/user/points/
	 *接口说明	列出我的积分记录
	 *参数 @param
	 * @status   	整数  0:未读  1：已读
	 * @pagenum		页码 
	 * @pagesize	每页数量
	 * @token		登陆标记
	 *返回 @return
	 * @list		消息列表
	 *
	 **/
	public function pointsAction(){
		$pagenum        =  intval($this->get('pagenum', 1));
        $pagesize    	=  intval($this->get('pagesize', 10));
		
		$creditNum	= (new Table('credit'))->find("user_id='".self::$user_id."'");		
		if( empty($creditNum) ){
			$rows	= array( 'user_id'=>self::$user_id, 'value'=>0, 'op_user'=>0, 'addtime'=>time(), 'addip'=>getIp() );
			$_DBcredit->add($rows);
			$creditNum['value']= 0;
		}
		
		$creditLog	= (new Table('credit_log'))->findAll("user_id='".self::$user_id."'", '', array($pagenum-1, $pagesize), 'id,type_id,value,remark,addtime');
		$counter	= (new Table('credit_log'))->findCount("user_id='".self::$user_id."'");
		
		if(is_array($creditLog) && !empty($creditLog)){
			$creditType	=	new Table('credit_type');
			foreach($creditLog as $k=>$v){
					$type	=	$creditType->find($v['type_id']);
					$creditLog[$k]['type']	=	$type['name'];
			}
		}
		
		$result		= array(
						'code'	=>	'1',
						'msg'	=>	'数据读取成功',
						'data'	=>	array(
										'credit'	=>	$creditNum['value'],
										'total'		=>	$counter,
										'pagenum'	=>	$pagenum,
										'pagesize'	=>	$pagesize,
										'totalpage'	=>	ceil($counter/$pagesize),
										'list'		=>	(array)$creditLog,
									),
					);
		json($result);
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
}
