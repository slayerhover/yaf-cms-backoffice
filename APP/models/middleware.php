<?php
use Illuminate\Database\Capsule\Manager as DB;

abstract class middlewareModel{

	/**
     * @api 中间件处理程序
     */
	public abstract function handle($postData);
	
	/**
     * @api 前端验证登陆标记token是否合法
     */
	protected function checkToken($token){
		if(empty($token)) { throw new Exception('token为空.');}		
		remember('auth_'.$token, -1, function() use($token){			
			$user	= DB::table('members')->where('token','=',$token)->first();
			if(empty($user)){throw new Exception('token无效.');}
			$tokenuser	=	array(
				'uid'			=> $user['id'],
				'username'		=> $user['username'],	
				'roles_id'		=> $user['roles_id'],
				'lastlogintime'	=> date('Y-m-d H:i:s', time()),
				'lastloginip'	=> getIp(),
			);
			return $tokenuser;
		});
	}
		
	/**
     * @api 后台验证登陆标记token是否合法
     */
	protected function checkBackToken($token){				
		if(empty($token)) { throw new Exception('token为空.');}		
		remember('auth_'.$token, -1, function() use($token){
			$user	= DB::table('admin')->where('token','=',$token)->first();				
			if(empty($user)){throw new Exception('token无效.');}
			$tokenuser	=	array(
				'uid'			=> $user['id'],
				'username'		=> $user['username'],	
				'roles_id'		=> $user['roles_id'],
				'lastlogintime'	=> date('Y-m-d H:i:s', time()),
				'lastloginip'	=> getIp(),
			);
			return $tokenuser;			
		});
	}
	
	#验证权限
	protected function checkAuth($token){		
		$tokenuser = Cache::getInstance()->get('auth_'.$token);
		#判断控制器&方法权限
		$ownAuth = explode(',', DB::table('roles')->find($tokenuser['roles_id'])['auth_ids']);				
		#DB::enableQueryLog();		
		$rows	 = DB::table('auths')	->whereIn('id',array_values($ownAuth))
										->where('controller','=',$this->controller)						  
										->whereRaw('FIND_IN_SET(?,action)', [$this->action])
										->get();
		#$this->sqllog();		
		if( empty($rows) )	throw new Exception('无访问权限.');
	}

}
