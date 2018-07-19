<?php
use Illuminate\Database\Capsule\Manager as DB;

abstract class BCoreController extends Yaf_Controller_Abstract {

    protected $module;
    protected $controller;
    protected $action;
	protected $files;
    protected $curr_url;
    protected $lang_arr;
    protected $config;
	protected $session;
	protected $auth;
	protected $postData;
	protected $formData;
    /**
     * 初始化
     *
     */
    public function init() {		
        $this->module	 		= Yaf_Dispatcher::getInstance()->getRequest()->getModuleName();
        $this->controller	 	= Yaf_Dispatcher::getInstance()->getRequest()->getControllerName();
        $this->action	 		= Yaf_Dispatcher::getInstance()->getRequest()->getActionName();
        $this->method			= Yaf_Dispatcher::getInstance()->getRequest()->getMethod();
		$this->curr_url 		= Yaf_Dispatcher::getInstance()->getRequest()->getRequestUri();				
        $this->config 			= Yaf_Application::app()->getConfig();
		$this->formData			= $this->getPost();
		$this->postData			= array_merge($this->getQuery(), $this->getParam(), $this->formData);
		$this->session			= Yaf_Session::getInstance();
		
		$this->checkAuth();		
    }
	#验证权限
	private function checkAuth(){
		$this->auth	= new Auth(_RBACCookieKey_);
		if(!$this->auth->isLogin()){
				$this->redirect('/office/login');
		}
		#判断控制器&方法权限
		$ownAuth = explode(',', DB::table('roles')->find($this->auth->role)['auth_ids']);
		#DB::enableQueryLog();		
		$rows	 = DB::table('auths')	->whereIn('id',array_values($ownAuth))
										->where('controller','=',$this->controller)						  
										->whereRaw('FIND_IN_SET(?,action)', [$this->action])
										->get();
		#$this->sqllog();		
		if( empty($rows) )	throw new Exception('无访问权限.');
	}
	
	/**
     * get one parameter
     *
     */
    protected function get($name='', $default = ''){		
		if( empty($name) ){			
			return $this->postData;
		}else{
			$value = $this->postData[$name] ?? $default;
			$value = Tools::filter($value);
			return $value;
		}		
    }
		
	/**
     * Get
     *
     */
	protected function getQuery($name= '', $default = ''){
		if( empty($name) ){
			return $this->getRequest()->getQuery();
		}else{
			$value = $this->getRequest()->getQuery($name, $default);
			$value = Tools::filter($value);
			return $value;
		}
    }
	
	/**
     * GetParams
     *
     */
	protected function getParam($name= '', $default = ''){
		if( empty($name) ){
			return $this->getRequest()->getParams();
		}else{
			$value = $this->getRequest()->getParam($name, $default);
			$value = Tools::filter($value);
			return $value;
		}
    }
	
    /**
     * Post
     *
     */
    protected function getPost($name= '', $default = ''){
		$json	=	$this->parse_json(file_get_contents("php://input"));		
		if(empty($json)){
			if( empty($name) ){
				return $this->getRequest()->getPost();
			}else{
				$value = $this->getRequest()->getPost($name, $default);
				$value = Tools::filter($value);
				return $value;
			}
		}else{			
			if( empty($name) ){
				return $json;
			}else{
				$value = $json[$name];
				$value = Tools::filter($value);
				return $value;
			}
		}
    }
	
	protected function parse_json($string) {
		$json = json_decode($string, TRUE);		
		if(json_last_error() == JSON_ERROR_NONE){
			return $json;
		}else{
			return [];
		}
	}
	
    /**
     * request
     *
     */
    protected function getCookie($name='', $default = '') {
        $value = $this->getRequest()->getCookie($name, $default);
        $value = Tools::filter($value);
        return $value;
    }	
	/**
     * files
     *
     */
    protected function getFiles($name) {
        $value = $this->getRequest()->getFiles($name);       
        return $value;
    }
	/**
     * xml
     *
     */
    protected function isXml() {
        return $this->getRequest()->isXmlHttpRequest();
    }
	
	
	/**
	  *记录SQL日志
	  *前置 DB::enableQueryLog();
	  */
	protected function sqllog(){
		$sqllogs		= DB::getQueryLog();
		foreach($sqllogs as $onesql){
			$query		= str_replace('?','%s',$onesql['query']);
			$bindings	= $onesql['bindings'];		
			array_walk($bindings, function(&$v){ $v = "'$v'"; });
					
			array_unshift($bindings, $query);
			$sql = call_user_func_array('sprintf', $bindings);
			Log::out('sql', 'I', call_user_func_array('sprintf', $bindings));
		}
	}	

}
