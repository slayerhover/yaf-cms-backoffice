<?php
use Illuminate\Database\Capsule\Manager as DB;

abstract class CoreController extends Yaf_Controller_Abstract {

    protected $moduleName;
    protected $controllerName;
    protected $actionName;
	protected $files;
    protected $curr_url;
    protected $lang_arr;
    protected $config;
	protected $session;
	protected $auth;
	protected $postData;
    /**
     * 初始化
     *
     */
    public function init() {		
        $this->moduleName 		= Yaf_Dispatcher::getInstance()->getRequest()->getModuleName();
        $this->controllerName 	= Yaf_Dispatcher::getInstance()->getRequest()->getControllerName();
        $this->actionName 		= Yaf_Dispatcher::getInstance()->getRequest()->getActionName();
        $this->method			= Yaf_Dispatcher::getInstance()->getRequest()->getMethod();
		$this->curr_url 		= Yaf_Dispatcher::getInstance()->getRequest()->getRequestUri();				
        $this->config 			= Yaf_Application::app()->getConfig();
		$this->postData			= array_merge($this->getQuery(), $this->getPost());		
		$midware=$this->config['application']['directory'].'/middleware/' . ucfirst($this->controllerName) . '.' . $this->config['application']['ext'];
		if(file_exists($midware)){	
			Yaf_Loader::import($midware);			
			$middle =	ucfirst($this->controllerName) . 'Middle';
			if(class_exists($middle, false)) (new $middle)->handle($this->postData);
		}
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
        $value = $this->getRequest()->getFiles($name, $default);       
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
	  *记录最后一条SQL日志
	  *前置 DB::enableQueryLog();
	  */
	protected function sqllog(){
		$sqllog		= DB::getQueryLog()[0];
		$query		= str_replace('?','%s',$sqllog['query']);
		$bindings	= $sqllog['bindings'];		
		array_walk($bindings, function(&$v){ $v = "'$v'"; });
				
		array_unshift($bindings, $query);
		$sql = call_user_func_array('sprintf', $bindings);
		Log::out('sql', 'I', call_user_func_array('sprintf', $bindings));
	}	

}
