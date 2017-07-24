<?php
/**
  *核心控制器，其它控制器由此继承
  *
  */
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
		$this->session			= Yaf_Session::getInstance();				
		global $auth;
		$this->auth				= $auth;
    }
	
	
	/**
     * get/post
     *
     */
    protected function get($name, $default = ''){
        $value = $this->getRequest()->get($name, $default);
        $value = Tools::filter($value);
        return $value;
    }

	/**
     * get
     *
     */
	protected function getQuery($name, $default = ''){
        $value = $this->getRequest()->getQuery($name, $default);
        $value = Tools::filter($value);
        return $value;
    }
	
    /**
     * post
     *
     */
    protected function getPost($name= '', $default = ''){
		if( empty($name) ){
			return $this->getRequest()->getPost();
		}else{
			$value = $this->getRequest()->getPost($name, $default);
			$value = Tools::filter($value);
			return $value;
		}
    }

    /**
     * cookie
     *
     */
    protected function getCookie($name, $default = '') {
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
     * request
     *
     */
    protected function isXml() {
        return $this->getRequest()->isXmlHttpRequest();
    }

}
