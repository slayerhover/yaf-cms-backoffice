<?php
use Illuminate\Database\Capsule\Manager as DB;
class UserMiddle {

	public function handle($postData){
		$token = $postData['token'];
		if(empty($token) || $this->checktoken($token)==FALSE)
			ret(1, [], 'token error');
	}

	public function checkToken($token){
		$inputs	= array(					
				['name'=>'token',  'value'=>$token,	 'role'=>'required', 'msg'=>'token不能为空'],
		);
		$result		= Validate::check($inputs);
		if(!empty($result)) return FALSE;		
		if(Cache::getInstance()->exists('auth_'.$token)==FALSE){ return FALSE; }
		return (DB::table('t_user')->where('token','=',$token)->count()>0);
	}	
	
}
