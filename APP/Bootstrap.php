<?php
use Illuminate\Database\Capsule\Manager as Capsule;
class Bootstrap extends Yaf_Bootstrap_Abstract {
	protected $config;

	public function _initConfig(Yaf_Dispatcher $dispatcher) {
		$this->config = Yaf_Application::app()->getConfig();		
		Yaf_Registry::set('config', $this->config);		
		
		Yaf_Loader::import(APP_PATH . "/vendor/autoload.php");		
		Yaf_Loader::import(APP_PATH . '/conf/function.php');		
		//判断请求方式，命令行请求应跳过一些HTTP请求使用的初始化操作，如模板引擎初始化		
		if(!empty($this->config->application->suffix) && !$dispatcher->getRequest()->isCli()) {
			$requesturi	=str_replace('.'.$this->config->application->suffix,'/',$_SERVER['REQUEST_URI']);
			$requesturi =str_replace('?', '/', $requesturi);
			$requesturi =str_replace('//', '/', $requesturi);
            $dispatcher->getRequest()->setRequestUri($requesturi);						
        }
		$dispatcher->autoRender($this->config->application->autoRender);
	}

	public function _initError(Yaf_Dispatcher $dispatcher) {
		if ($this->config->application->debug)
		{
			define('DEBUG_MODE', true);
			ini_set('display_errors', 'On');
		}
		else
		{
			define('DEBUG_MODE', false);
			ini_set('display_errors', 'Off');
		}
	}

	public function _initPlugin(Yaf_Dispatcher $dispatcher) {
		if (isset($this->config->application->benchmark) && $this->config->application->benchmark == true)
		{
			$benchmark = new BenchmarkPlugin();
			$dispatcher->registerPlugin($benchmark);
		}
		//cookie涉及HTTP请求，命令行下应禁用
		if ($dispatcher->getRequest()->getMethod() != 'CLI')
		{						
			$antizy = new AntizyPlugin();
			$dispatcher->registerPlugin($antizy);
		}
	}

	public function _initRoute(Yaf_Dispatcher $dispatcher) {
		$routes = $this->config->routes;
		if (!empty($routes))
		{
			$router = $dispatcher->getRouter();
			$router->addConfig($routes);
		}
	}

	public function _initMemcache() {
		if ($this->config->cache->redis->enable==TRUE){
			define('CACHE_ENABLE', true);
			define('CACHE_KEY_PREFIX', $this->config->cache->redis->prefix);
		}else{
			define('CACHE_ENABLE', false);
		}
	}

	public function _initDatabase() {
		$capsule = new Capsule;
        // 创建默认链接
        $capsule->addConnection(Yaf_Application::app()->getConfig()->database->toArray());
        // biz业务链接
        // $capsule->addConnection(Yaf_Application::app()->getConfig()->biz->toArray(), 'biz');
        // 设置全局静态可访问
        $capsule->setAsGlobal();
        // 启动Eloquent
        $capsule->bootEloquent();
	}
		
}
