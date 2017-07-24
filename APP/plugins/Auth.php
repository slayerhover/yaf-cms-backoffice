<?php
use Illuminate\Database\Capsule\Manager as DB;
class AuthPlugin extends Yaf_Plugin_Abstract {
	public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {		
		/***检查控制器是否存在***/
		$config	=	Yaf_Registry::get('config');
		if( !file_exists( $config['application']['directory'].'/controllers/' . ucfirst($request->controller) . '.' . $config['application']['ext']) )
			throw new Exception('不存在控制器'.$request->controller);
		
		global $auth;
		if (!Tools::isSpider())	{
			$auth	= new Auth(_RBACCookieKey_);			
			$ggdata	= DB::table('roles')->where('rolename', 'EVERYONE')->first();/***查询公共控制器权限***/
			$ggcontrollers = explode(',', $ggdata['controllers']);			
			if( !in_array(strtolower($request->controller), array_map('strtolower', $ggcontrollers)) ){/***权限验证***/
				if (!$auth->isLogin()){					
					redirect( url('public', 'login') );
				}
				
				//已登陆，但无此控制器权限
				$owndata=	DB::table('roles')->find($auth->role);
				$owncontrollers = explode(',', $owndata['controllers']);
				if( !in_array(strtolower($request->controller), array_map('strtolower', $owncontrollers)) ){			
					throw new Exception('无访问权限.');
				}				
			}		
		}else{
			$auth = new Auth('spider');
		}
	}
}
