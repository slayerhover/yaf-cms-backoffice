<?php
use Illuminate\Database\Capsule\Manager as DB;

abstract class CoreController extends Yaf_Controller_Abstract {

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
    /**
     * 初始化
     *
     */
    public function init() {		
		$Request = Yaf_Dispatcher::getInstance()->getRequest();
        $this->module	 		= $Request->getModuleName();
        $this->controller	 	= $Request->getControllerName();
        $this->action	 		= $Request->getActionName();
        $this->method			= $Request->getMethod();
		$this->curr_url 		= $Request->getRequestUri();				
        $this->config 			= Yaf_Application::app()->getConfig();
		$this->session			= Yaf_Session::getInstance();
		$this->postData			= array_merge($this->getQuery(), $this->getParam(), $this->getPost());
		#控制器中间件
		$middle = ucfirst($this->controller) . 'Middle';
		$midwarePath= $this->config['application']['directory'].'/middleware/'.ucfirst($this->controller).'.'.$this->config['application']['ext'];
		if(file_exists($midwarePath)){	
			Yaf_Loader::import($midwarePath);
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
        if( empty($name) ){
            return $this->getRequest()->getFiles();
        }else {
            return $this->getRequest()->getFiles($name);
        }
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

	#上传base64图片
	protected function uploader($files){
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $files, $base64result)){
				$ext	  = $base64result[2];
				$ext	  = stristr($ext, 'jpeg')?'jpg':$ext;
				$config	  = Yaf_Registry::get('config');
				$fileName = 'img-t' . time() . rand(10000,99999) . '.' . $ext;
				$path	  = '/logo/' . date('Ym') . '/';
				$desdir  = $config['application']['uploadpath'] . $path;
				if(!is_dir($desdir)){ mkdir($desdir, 0777, TRUE); }
				$realpath = $desdir . $fileName;

				if(file_put_contents($realpath, base64_decode(str_replace(' ', '+', str_replace($base64result[1], '', $files))))){
					if( $image = $this->uploadToCDN($desdir.$fileName, $fileName) ){
						return $image;
					}					
				}			
		}else{
			return $files;
		}
		return FALSE;
	}
	/***上传图片文件***/
	protected function uploadFileToCDN($upfile) {
        $files	= $this->getFiles($upfile);
		if( $files!=NULL && $files['size']>0 ){
			$uploader  = new FileUploader();
			$files     = $uploader->getFile($upfile);
            if(!$files){
				return FALSE;
			}
            if($files->getSize()==0){
				return FALSE;
            }
			$config	= Yaf_Registry::get('config');
            if (!$files->checkExts($config['application']['upfileExts'])){				
            	return FALSE;
            }
			if (!$files->checkSize($config['application']['upfileSize'])){
            	return FALSE;
            }
			$cdnfilename = 'Images-t' . time().rand(100,999) . '.' . $files->getExt();
			if( $image = $this->uploadToCDN($files->getTmpName(), $cdnfileName) ){
                $rows	=	array(
                    "originalName" 	=> $files->getFilename() ,
                    "name" 			=> $cdnfilename ,
                    "url" 			=> $image ,
                    "size" 			=> $files->getSize() ,
                    "type" 			=> $files->getMimeType() ,
                    "state" 		=> 'SUCCESS'
                );
				return $rows;
			}
		}
		return FALSE;
    }
	/***PHP上传文件到七牛cdn***/
	protected function uploadToCDN($filePath, $cdnfileName){					
			// 需要填写你的 Access Key 和 Secret Key
			$accessKey = $this->config['application']['cdn']['accessKey'];
			$secretKey = $this->config['application']['cdn']['secretKey'];

			// 构建鉴权对象
			$auth = new \Qiniu\Auth($accessKey, $secretKey);
			// 要上传的空间
			$bucket = $this->config['application']['cdn']['bucket'];
			
			// 生成上传 Token
			$token = $auth->uploadToken($bucket);

			// 上传到七牛后保存的文件名
			$key = $cdnfileName;

			// 初始化 UploadManager 对象并进行文件的上传
			$uploadMgr = new \Qiniu\Storage\UploadManager;

			// 调用 UploadManager 的 putFile 方法进行文件的上传
			list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
			if ($err !== null) {
				return false;
			} else {
				return $this->config['application']['cdn']['url'] . $ret['key'];
			}
	}
	
}
