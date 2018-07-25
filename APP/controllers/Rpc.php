<?php
use Illuminate\Database\Capsule\Manager as DB;

/**
 * @name RpcController
 * @author slayer.hover
 * @desc   Rpc控制器
 */
class RpcController extends CoreController {

	public function init(){
		parent::init();
		
		Yaf_Dispatcher::getInstance()->disableView();
		$server=new Yar_Server($this);
		$server->handle();
	}
	
	public function indexAction(){
		return 'Greeting...';
	}
	
	/**
	 *接口名称	APP欢迎页
	 *接口说明	显示欢迎页图片
	 *参数 @arr['type'] 0:欢迎页图片 1：主页滚动图
	 *返回 @return 
	 *返回格式	数组
	 **/
	public function getImages($arr=[], $authcode=''){
		if( $this->auth(__FUNCTION__, $arr, $authcode)==FALSE ){
			return '签名验证失败';
		}		
		if( $this->config->cache->object_cache_enable==TRUE && Cache::exists('Images'.$arr['type']) ){			
			return Cache::get('Images'.$arr['type']);
		}
		$rows	= DB::table('images')->where('type','=', $arr['type'])
									 ->where('status','=', 1)
									 ->orderBy('sortorder', 'desc')
									 ->limit(5)
									 ->select('file','title','links')
									 ->get();
		if( $this->config->cache->object_cache_enable==TRUE ){			
			Cache::set('Images'.$arr['type'], $rows, $this->config->cache->redis->expire);
		}							 
		return $rows;
	}
	
	
	
	/** 
	* 返回城市数据信息 
	* 
	* 	
	* @return $rows 数组 返回对应的数据包
	*/
	public function getCity($arr=[], $authcode='123456')
	{
		if( $this->auth(__FUNCTION__, $arr, $authcode)==FALSE ){
			return '签名验证失败';
		}		
		if( $this->config->cache->object_cache_enable==TRUE && Cache::exists('city'.$arr['up']) ){			
			return Cache::get('city'.$arr['up']);
		}
		$rows	= DB::table('city')->where('up','=', $arr['up'])
									 ->orderBy('id', 'asc')
									 ->select('id','name','level','sortorder')
									 ->get();
		if( $this->config->cache->object_cache_enable==TRUE ){			
			Cache::set('city'.$arr['up'], $rows, $this->config->cache->redis->expire);
		}							 
		return $rows;
	}
	
	/**
	 *接口名称	页面内容
	 *接口说明	获取单页信息
	 *参数 @arr['id'] 1:关于我们 4:服务协议 9:版本更新 11:联系我们
	 *返回 @return 
	 *返回格式	数组
	 **/
	public function getPages($arr=[], $authcode=''){
		if( $this->auth(__FUNCTION__, $arr, $authcode)==FALSE ){
			return '签名验证失败';
		}		
		if( $this->config->cache->object_cache_enable==TRUE && Cache::exists('Pages'.$arr['id']) ){			
			return Cache::get('Pages'.$arr['id']);
		}
		$rows	= DB::table('pages')->where('id','=', $arr['id'])
									->where('status','=', 1)
									->first();
		if( $this->config->cache->object_cache_enable==TRUE ){			
			Cache::set('Pages'.$arr['id'], $rows, $this->config->cache->redis->expire);
		}							 
		return $rows;
	}
	
	
	/** 
	* 返回汽车品牌数据信息 
	* 
	* 	
	* @return $rows 数组 返回对应的数据包
	*/
	public function carBrand($arr=[], $authcode='123456')
	{
		if( $this->auth(__FUNCTION__, $arr, $authcode)==FALSE ){
			return '签名验证失败';
		}				
		if(isset($arr['recommend']) && $arr['recommend']==1){
				if( $this->config->cache->object_cache_enable==TRUE && Cache::exists('carBrandrecommend') ){			
					return Cache::get('carBrandrecommend');
				}else{
					$rows	= DB::table('carbrand')->where('recommend', '=', 1)
									 ->orderBy('letter', 'asc')
									 ->get();
					Cache::set('carBrandrecommend', $rows, $this->config->cache->redis->expire);				 
					return $rows;
				}
		}else{
				if( $this->config->cache->object_cache_enable==TRUE && Cache::exists('carBrand') ){			
					return Cache::get('carBrand');
				}else{
					$rows	= DB::table('carbrand')
									 ->orderBy('letter', 'asc')
									 ->get();			
					Cache::set('carBrand', $rows, $this->config->cache->redis->expire);
					return $rows;
				}
		}
	}
	
	/** 
	* 返回汽车厂家数据信息 
	* 
	* 	
	* @return $rows 数组 返回对应的数据包
	*/
	public function carFactory($arr=[], $authcode='123456')
	{
		if( $this->auth(__FUNCTION__, $arr, $authcode)==FALSE ){
			return '签名验证失败';
		}		
		if( $this->config->cache->object_cache_enable==TRUE && Cache::exists('carfactory'.$arr['brand_id']) ){			
			return Cache::get('carfactory'.$arr['brand_id']);
		}
		$rows	= DB::table('carfactory')->where('brand_id','=', $arr['brand_id'])
										 ->orderBy('id', 'asc')
										 ->get();
		if(!empty($rows)){
		foreach($rows as $k=>$v){
				$rows[$k]['series']	=	DB::table('carseries')->where('factory_id','=', $v['id'])
										 ->orderBy('id', 'asc')
										 ->get();
		}}
		if( $this->config->cache->object_cache_enable==TRUE ){			
			Cache::set('carfactory'.$arr['brand_id'], $rows, $this->config->cache->redis->expire);
		}							 
		return $rows;
	}
	
	/** 
	* 返回汽车系列数据信息 
	* 
	* 	
	* @return $rows 数组 返回对应的数据包
	*/
	public function carSeries($arr=[], $authcode='123456')
	{
		if( $this->auth(__FUNCTION__, $arr, $authcode)==FALSE ){
			return '签名验证失败';
		}		
		if( $this->config->cache->object_cache_enable==TRUE && Cache::exists('carseries'.$arr['factory_id']) ){			
			return Cache::get('carseries'.$arr['factory_id']);
		}
		$rows	= DB::table('carseries')->where('factory_id','=', $arr['factory_id'])
										 ->orderBy('id', 'asc')
										 ->get();
		if( $this->config->cache->object_cache_enable==TRUE ){			
			Cache::set('carseries'.$arr['factory_id'], $rows, $this->config->cache->redis->expire);
		}							 
		return $rows;
	}
	
	/** 
	* 返回汽车型号数据信息 
	* 
	* 	
	* @return $rows 数组 返回对应的数据包
	*/
	public function carModel($arr=[], $authcode='123456')
	{
		if( $this->auth(__FUNCTION__, $arr, $authcode)==FALSE ){
			return '签名验证失败';
		}		
		if( $this->config->cache->object_cache_enable==TRUE && Cache::exists('carmodel'.$arr['series_id']) ){			
			return Cache::get('carmodel'.$arr['series_id']);
		}
		$rows	= DB::table('carmodel')->where('series_id','=', $arr['series_id'])
										 ->orderBy('id', 'asc')
										 ->get();
		if( $this->config->cache->object_cache_enable==TRUE ){			
			Cache::set('carmodel'.$arr['series_id'], $rows, $this->config->cache->redis->expire);
		}							 
		return $rows;
	}
	
	/** 
	* 返回汽车配件数据信息 
	* 
	* 	
	* @return $rows 数组 返回对应的数据包
	*/
	public function carParts($arr=[], $authcode='123456')
	{
		if( $this->auth(__FUNCTION__, $arr, $authcode)==FALSE ){
			return '签名验证失败';
		}		
		if( $this->config->cache->object_cache_enable==TRUE && Cache::exists('carparts'.$arr['parts_id'].'sub'.$arr['is_showsub']) ){			
			return Cache::get('carparts'.$arr['parts_id'].'sub'.$arr['is_showsub']);
		}
		$query	= DB::table('autoparts');
		if($arr['parts_id']==0){
			$query	=	$query->where('up','=', 0);
		}else{
			$query	=	$query->where('id', '=', $arr['parts_id']);
		}
		$rows	=	$query  ->orderBy('id', 'asc')
							->select('id', 'title')
							->get();		
		if($arr['is_showsub']&&!empty($rows)){
		foreach($rows as $k=>$v){
				$rows[$k]['subparts']	=	DB::table('autoparts')->where('up','=', $v['id'])
																 ->orderBy('id', 'asc')
																 ->select('id', 'title')
																 ->get();
		}}
		if( $this->config->cache->object_cache_enable==TRUE ){			
			Cache::set('carparts'.$arr['parts_id'].'sub'.$arr['is_showsub'], $rows, $this->config->cache->redis->expire);
		}							 
		return $rows;
	}
	
	/** 
	* 验证数字签名
	* 
	* @param  $authcode 字符型
	* 	
	* @return bool型 成功true, 失败false
	*/
	private function auth($functionName, $params, $authcode='123456'){		
		/***ksort($params);***/
		$decodewords	=	$functionName.implode($params);
		return strcmp($this->authcode($decodewords, 'ENCODE'), $authcode)===0;
	}
		
	/**
	 * 加密/解密字符串
	 *
	 * @param  string     $string    原始字符串
	 * @param  string     $operation 操作选项: DECODE：解密；其它为加密
	 * @param  string     $key       混淆码
	 * @return string     $result    处理后的字符串
	 */
	private function authcode($string, $operation, $key = '') {
		$authorization=$this->config->application->rpcAuth;
		$key = md5($key ? $key : $authorization);
		$key_length = strlen($key);
	
		$string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
		$string_length = strlen($string);
	
		$rndkey = $box = array();
		$result = '';
	
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($key[$i % $key_length]);
			$box[$i] = $i;
		}
	
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
	
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
	
		if($operation == 'DECODE') {
			if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
				return substr($result, 8);
			} else {
				return '';
			}
		} else {
			return str_replace('=', '', base64_encode($result));
		}	
	}

}
