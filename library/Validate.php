<?php
class Validate {
	/***
	 *检测输入参数
	 *传送参数格式,调用方式：
	 *	$username= trim($this->get('username', ''));
	 * 	$email	 = trim($this->get('email', ''));
	 * 	$inputs	= array(
	 *		['name'=>'username','value'=>$username,	'fun'=>'isUsername','role'=>'min:2|max:32|required','msg'=>'用户名请输入2-32位的字符.' ],
	 *		['name'=>'email','value'=>$email,'fun'=>'isEmail','role'=>'min:5|max:64|required','msg'=>'邮件地址格式有误.' ],
	 *	);
	 *	$result	= Validate::check($inputs);
	 *	if(!empty($result)){
	 *		$result	= array(
	 *				'code'	=>	'0',
	 *				'msg'	=>	'输入参数有误.',
	 *				'data'	=>	$result,
	 *		);
	 *		json($result);
	 * 	}
	 *输出参数格式：
	 *	array(
	 *		'username' => '用户名格式有误,请输入2-30位的字符.',
	 *		'email'	   => '邮件地址格式有误.',
	 *	);
	 ***/
	public static function check($options){
		$result	=	[];
		if(is_array($options)&&!empty($options)){
		foreach($options as $k=>$v){
			if(isset($v['role'])&&!empty($v['role'])){
				$role	= explode('|',$v['role']);
				if(!empty($role)&&is_array($role)){
				foreach($role as $cv){
					$cv = explode(':',$cv);
					switch(strtolower(trim($cv[0]))){
						case 'required':
							if($v['value']===''){				
									$result[$v['name']]	=	'必填项不能为空' . ';' . $v['msg'];
							}
							break;
						case 'min':
							if($v['value']!==''&&isset($cv[1])&&$cv[1]>0&&strlen($v['value'])<$cv[1]){				
									$result[$v['name']]	=	'最小长度不足' . $cv[1] . ';' . $v['msg'];
							}
							break;
						case 'max':
							if($v['value']!==''&&isset($cv[1])&&$cv[1]>0&&strlen($v['value'])>$cv[1]){
									$result[$v['name']]	=	'最大长度超过' . $cv[1] . ';' . $v['msg'];
							}
							break;						
						case 'gt':
							if($v['value']!==''&&isset($cv[1])&&$v['value']<=$cv[1]){
									$result[$v['name']]	=	'值必须大于' . $cv[1] . ';' . $v['msg'];
							}
							break;
						case 'gte':
							if($v['value']!==''&&isset($cv[1])&&$v['value']<$cv[1]){
									$result[$v['name']]	=	'值必须大于等于' . $cv[1] . ';' . $v['msg'];
							}
							break;
						case 'lt':
							if($v['value']!==''&&isset($cv[1])&&$v['value']>=$cv[1]){
									$result[$v['name']]	=	'值必须小于' . $cv[1] . ';' . $v['msg'];
							}
							break;
						case 'lte':
							if($v['value']!==''&&isset($cv[1])&&$v['value']>$cv[1]){
									$result[$v['name']]	=	'值必须小于等于' . $cv[1] . ';' . $v['msg'];
							}
							break;
						case 'eq':
							if($v['value']!==''&&isset($cv[1])&&$v['value']<>$cv[1]){
									$result[$v['name']]	=	'值不相等：' . $v['name'] . ';' . $v['msg'];
							}
							break;
						case 'neq':
							if($v['value']!==''&&isset($cv[1])&&$v['value']==$cv[1]){
									$result[$v['name']]	=	'值必须不等于' . $cv[1] . ';' . $v['msg'];
							}
							break;
						case 'in':
							$in = explode(',',$cv[1]);
							if($v['value']!==''&&!in_array($v['value'], $in)){
									$result[$v['name']]	=	'值必须包含在[' . $cv[1] . ']' . ';' . $v['msg'];
							}
							break;
						case 'like':
							if($v['value']!==''&&stripos($v['value'], $cv[1])===FALSE){
									$result[$v['name']]	=	'值必须相似于%' . $cv[1] . '%' . ';' . $v['msg'];
							}
							break;
						case 'between':
							$between = explode(',',$cv[1]);
							if($v['value']!==''&&isset($between[0])&&($v['value']<$between[0]||$v['value']>$between[1])){
									$result[$v['name']]	=	'值必须间于' . $between[0].'-'.$between[1] . ';' . $v['msg'];
							}
							break;
					}
				}}
			}
			if($v['value']!==''&&isset($v['fun'])&&!empty($v['fun'])){			
				$yz	=	call_user_func('self::'.$v['fun'], $v['value']);
				if( $yz['code']==0 ){
						$result[$v['name']]	=	empty($v['msg']) ? $yz['msg'] : $v['msg'];
				}
			}
		}}
		return $result;
	}
	
	public static function isNotempty($value){
		if(!empty($value)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'字段不能为空.'];
		}
	}
	
	public static function isUsername($value,$minLen=2,$maxLen=32){
		if(preg_match('/^[_\.\w\d\x{4e00}-\x{9fa5}]{'.$minLen.','.$maxLen.'}$/ius',$value)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'用户名格式有误,请输入2-30位的字符.'];
		}
	}
	public static function isEmail($email) {
		if(preg_match(Tools::cleanNonUnicodeSupport('/^[a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z\p{L}0-9]+[._a-z\p{L}0-9-]*\.[a-z0-9]+$/ui'), $email)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'邮箱格式有误.'];
		}
	}	
	public static function isPhone($mobilePhone) {
		if(preg_match("/^1[34578][0-9]{9}$/", $mobilePhone)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'手机号格式有误.'];
		}
	}
	public static function isTel($tel) {
		if(preg_match("/^(0[0-9]{2,3}\-)?([2-9][0-9]{6,7})+(\-[0-9]{1,4})?$|^[400|800][\-\d]{7,12}$/", $tel)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'手机号格式有误.'];
		}
	}
	public static function isYzcode($code) {
		if(preg_match("/^[a-z0-9A-Z]{4,6}$/", $code)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'验证码格式有误.'];
		}
	}
	public static function isDate($date) {
		if (!preg_match('/^([0-9]{4})-((0?[1-9])|(1[0-2]))-((0?[1-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}(:[0-9]{2})?)?$/ui', $date, $matches)){
			return	['code'=>0, 'msg'=>'日期格式有误.'];
		}elseif(!checkdate(intval($matches[2]), intval($matches[5]), intval($matches[0]))){
			return	['code'=>0, 'msg'=>'日期格式有误.'];
		}else{
			return	['code'=>1];
		}
	}
	public static function isNumber($data) {
		if(preg_match("/^[0-9]+$/u", $data)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'数字格式有误.'];
		}
	}
	public static function isType($type) {
		if(preg_match("/^[1-3]$/u", $type)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'类型格式有误.'];
		}
	}
	public static function isIp($data) {
		$ary = explode('.', $data);
		if (preg_match('/[^\.\d]/', $data) && count($ary) == 4 && $ary[0] >= 0 && $ary[1] >= 0 && $ary[2] >= 0 && $ary[3] >= 0 && $ary[0] <= 255 && $ary[1] <= 255 && $ary[2] <= 255 && $ary[3] <= 255){			
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'IP地址格式有误.'];
		}
	}	
	public static function isChinese($data) {
		if(preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $data)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'输入中文格式有误.'];
		}
	}
	public static function isPrice($price) {
		if(preg_match('/^[0-9]{1,10}(\.[0-9]{1,9})?$/', $price)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'价格格式有误.'];
		}
	}
	public static function isBirthday($date) {
		if (empty($date) || $date == '0000-00-00')
			return ['code'=>0, 'msg'=>'生日日期格式有误.'];
		if (preg_match('/^([0-9]{4})-((?:0?[1-9])|(?:1[0-2]))-((?:0?[1-9])|(?:[1-2][0-9])|(?:3[01]))([0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date, $birth_date))
		{
			if ($birth_date[1] > date('Y') && $birth_date[2] > date('m') && $birth_date[3] > date('d'))
				return ['code'=>0, 'msg'=>'生日日期格式有误.'];

			return	['code'=>1];
		}
		return ['code'=>0, 'msg'=>'生日日期格式有误.'];
	}	
	public static function isPassword($passwd, $size = 6) {
		if(preg_match('/^\w{' . $size . ',32}$/ui', $passwd)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'密码格式有误.'];
		}
	}
	public static function isIdcard($value){
		if(!$value || strlen($value)!=18) return	['code'=>0, 'msg'=>'身份证格式有误.'];
	
		if(preg_match('/^\d{6}((1[89])|(2\d))\d{2}((0\d)|(1[0-2]))((3[01])|([0-2]\d))\d{3}(\d|X)$/i',$value)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'身份证格式有误.'];
		}
	}
	public static function isUrl($url) {
		if(preg_match('/^[~:#,%&_=\(\)\.\? \+\-@\/a-zA-Z0-9]+$/', $url)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'URL格式有误.'];
		}
	}
	public static function isFileName($name) {
		if(preg_match('/^[a-zA-Z0-9_.-]+$/', $name)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'文件名格式有误.'];
		}
	}
	public static function isDirName($dir) {
		if(preg_match('/^[a-zA-Z0-9_.-]+$/', $dir)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'目录名格式有误.'];
		}
	}
	public static function isFloat($float) {
		if(strval((float)$float) == strval($float)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'浮点数格式有误.'];
		}
	}
	public static function isName($name) {
		if(preg_match("/^[\x{4e00}-\x{9fa5}]{2,9}$/u", $name)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'输入中文姓名有误.'];
		}
	}
	public static function isDatetime($date) {
		if(preg_match('/^([0-9]{4})-((0?[0-9])|(1[0-2]))-((0?[0-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'日期格式有误.'];
		}	
	}
	public static function isToken($token, $length=36) {
		if(preg_match('/^[a-zA-Z0-9=]{'.$length.'}$/', $token)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'Token格式有误.'];
		}
	}
	public static function isOpenid($openid, $length=28) {
		if(preg_match('/^[a-zA-Z0-9=\-]{'.$length.'}$/', $openid)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'openid格式有误.'];
		}
	}
	public static function isInteger($value) {
		if( is_int($value) || preg_match('/^[0-9]+$/', $value) ){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'整数格式有误.'];
		}
	}
	public static function isInt($value) {
		if( is_int($value) || preg_match('/^[0-9]+$/', $value) ){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'整数格式有误.'];
		}
	}
	public static function isUinteger($value) {
		if(preg_match('#^[0-9]+$#', (string)$value) && $value<4294967296 && $value>0){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'无符号整数格式有误.'];
		}
	}
	public static function isBool($bool) {
		if( is_bool($bool) || preg_match('/^0|1$/', $bool) ){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'布尔格式有误.'];
		}
	}	
	public static function isClearhtml($html) {
		$events = 'onmousedown|onmousemove|onmmouseup|onmouseover|onmouseout|onload|onunload|onfocus|onblur|onchange';
		$events .= '|onsubmit|ondblclick|onclick|onkeydown|onkeyup|onkeypress|onmouseenter|onmouseleave|onerror|onselect|onreset|onabort|ondragdrop|onresize|onactivate|onafterprint|onmoveend';
		$events .= '|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onmove';
		$events .= '|onbounce|oncellchange|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondeactivate|ondrag|ondragend|ondragenter|onmousewheel';
		$events .= '|ondragleave|ondragover|ondragstart|ondrop|onerrorupdate|onfilterchange|onfinish|onfocusin|onfocusout|onhashchange|onhelp|oninput|onlosecapture|onmessage|onmouseup|onmovestart';
		$events .= '|onoffline|ononline|onpaste|onpropertychange|onreadystatechange|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onsearch|onselectionchange';
		$events .= '|onselectstart|onstart|onstop';
		
		if(!preg_match('/<[ \t\n]*script/ims', $html) && !preg_match('/(' . $events . ')[ \t\n]*=/ims', $html) && !preg_match('/.*script\:/ims', $html) && !preg_match('/<[ \t\n]*i?frame/ims', $html)){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'文本格式有误.'];
		}
	}
	public static function isTimestamp($time) {
		//return ctype_digit($time) && $time <= 2147483647;
		if( (int)$time > 0 && strtotime(date('Y-m-d H:i:s', $time))===(int)$time ){
			return	['code'=>1];
		}else{
			return	['code'=>0, 'msg'=>'时间戳格式有误.'];
		}
	}
	

	
	
	public static function isMd5($md5) {
		return preg_match('/^[a-f0-9A-F]{32}$/', $md5);
	}

	public static function isSha1($sha1) {
		return preg_match('/^[a-fA-F0-9]{40}$/', $sha1);
	}
	
	public static function isUFloat($float) {
		return strval((float)$float) == strval($float) && $float >= 0;
	}

	public static function isOFloat($float) {
		return empty($float) || Validate::isFloat($float);
	}
	public static function isAlias($alias) {
		return preg_match('/^[a-zA-Z-]{4-12}$/u', $alias);
	}
	public static function isNPrice($price) {
		return preg_match('/^[-]?[0-9]{1,10}(\.[0-9]{1,9})?$/', $price);
	}

	public static function isSearch($search) {
		return preg_match('/^[^<>;=#{}]{1,64}$/u', $search);
	}
	public static function isGenericName($name) {
		return preg_match(Tools::cleanNonUnicodeSupport('/^[^<>;=+@#"°{}$%:]+$/u'), stripslashes($name));
	}

	public static function isMessage($message) {
		return !empty($message) && !preg_match('/[<>{}]/i', $message);
	}

	public static function isOrderWay($way) {
		return ($way === 'ASC' | $way === 'DESC' | $way === 'asc' | $way === 'desc');
	}
	
	public static function isUnsignedInt($value) {
		return (preg_match('#^[0-9]+$#', (string)$value) && $value < 4294967296 && $value >= 0);
	}

	public static function isPercentage($value) {
		return (Validate::isFloat($value) && $value >= 0 && $value <= 100);
	}

	public static function isUnsignedId($id) {
		return Validate::isUnsignedInt($id); /* Because an id could be equal to zero when there is no association */
	}

	public static function isNullOrUnsignedId($id) {
		return $id === null || Validate::isUnsignedId($id);
	}

	public static function isLoadedObject($object) {
		return is_object($object) && $object->id;
	}

	public static function isUrlOrEmpty($url) {
		return empty($url) || self::isUrl($url);
	}

	public static function isAbsoluteUrl($url) {
		return preg_match('/^https?:\/\/[!,:#%&_=\(\)\.\? \+\-@\/a-zA-Z0-9]+$/', $url);
	}

	public static function isMySQLEngine($engine) {
		return (in_array($engine, array('InnoDB', 'MyISAM')));
	}

	public static function isUnixName($data) {
		return preg_match('/^[a-z0-9\._-]+$/ui', $data);
	}

	public static function isCookie($data) {
		return (is_object($data) && (get_class($data) == 'Cookie' && get_class($data) == 'CookieModel'));
	}

	public static function isOptUnsignedId($id) {
		return is_null($id) OR self::isUnsignedId($id);
	}

	public static function isString($data) {
		return !empty($data) && is_string($data);
	}

	public static function isSerializedArray($data) {
		return $data === null || (is_string($data) && preg_match('/^a:[0-9]+:{.*;}$/s', $data));
	}

	public static function isIMEI($data) {
		return preg_match('/^[0-9a-z]{15}$/i', $data);
	}

	public static function isISBN($isbn) {
		return preg_match('/^[0-9]{13}$/', $isbn);
	}

	public static function isPublishTime($time) {
		return preg_match('/^[0-9]{4}-[0-9]{2}$/', $time);
	}

	public static function isNickname($data) {
		return preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z_]{2,16}$/u", $data);
	}

	public static function isOptNickname($data) {
		if ($data == null || self::isNickname($data))
		{
			return true;
		}
		return false;
	}
	public static function isCorrectImageExt($data) {
		return ImageManager::isCorrectImageFileExt($data);
	}

	public static function isExpressNumber($data) {
		return preg_match('/^[0-9A-Za-z]+$/', $data);
	}
}
