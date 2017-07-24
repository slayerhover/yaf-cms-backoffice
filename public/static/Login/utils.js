/**
 * @param url
 *            请求地址
 * @param params
 *            请求参数:key/value形式(可选)
 * @param callback
 *            请求成功后的回调函数(可选)
 */
function getJson(url, params, callback) {
	$.getJSON(url, params, callback);
}

/**
 * 登陆
 * @param params
 * @param callback
 */
function login(params, callback) {
	getJson("volunteer/login", params, callback);
}

var weekNames = ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'];

Date.prototype.convertDate = function(date) {
	var flag = true;
	var dateArray = date.split("-");
	if (dateArray.length != 3) {
		dateArray = date.split("/");
		if (dateArray.length != 3) {
			return null;
		}
		flag = false;
	}
	var newDate = new Date();
	if (flag) {
		// month从0开始
		newDate.setFullYear(dateArray[0], dateArray[1] - 1, dateArray[2]);
	} else {
		newDate.setFullYear(dateArray[2], dateArray[1] - 1, dateArray[0]);
	}
	newDate.setHours(0, 0, 0);
	return newDate;
};

Date.prototype.dateAdd = function(date, days) {
	var nd = new Date(date);
	nd = nd.valueOf();
	nd = nd + days * 24 * 60 * 60 * 1000;
	nd = new Date(nd);
	var y = nd.getFullYear();
	var m = nd.getMonth() + 1;
	var d = nd.getDate();
	if (m <= 9)
		m = "0" + m;
	if (d <= 9)
		d = "0" + d;
	var cdate = y + "-" + m + "-" + d;
	return cdate;
};

/**
 * 函数：格式化日期
 * 参数：formatStr-格式化字符串
 * ss：将秒显示为带前导零的数字
 * mm：将分钟显示为带前导零的数字
 * HH：使用24小时制将小时显示为带前导零的数字
 * dd：将日显示为带前导零的数字，如01
 * MM：将月份显示为带前导零的数字，如01
 * yyyy：以四位数字格式显示年份
 * 返回：格式化后的日期
 * 
 */
Date.prototype.format = function(formatStr) {
	var date = this;
	var reg = /"[^"]*"|'[^']*'|\b(?:d{1,4}|M{1,4}|yy(?:yy)?|([hHmstT])\1?|[lLZ])\b/g;
	/*
	 * 函数：填充0字符 参数：value-需要填充的字符串, length-总长度 返回：填充后的字符串
	 */
	var zeroize = function(value, length) {
		if (!length) {
			length = 2;
		}
		value = new String(value);
		for ( var i = 0, zeros = ''; i < (length - value.length); i++) {
			zeros += '0';
		}
		return zeros + value;
	};

	function formatSplit($_) {
		switch ($_) {
		case 'dd':
			return zeroize(date.getDate());
		case 'MM':
			return zeroize(date.getMonth() + 1);
		case 'yyyy':
			return date.getFullYear();
		case 'yy':
			var fullYear = date.getFullYear();
			return new String(fullYear).substring(2);
		case 'HH':
			return zeroize(date.getHours());
		case 'mm':
			return zeroize(date.getMinutes());
		case 'ss':
			return zeroize(date.getSeconds());
		}
	}
	return formatStr.replace(reg, formatSplit);
};

//去除空格函数
String.prototype.trim = function() {
	return trim(this);
};

String.prototype.ltrim = function() {
	return ltrim(this);
};

String.prototype.rtrim = function() {
	return rtrim(this);
};

function trim(str) {
	return ltrim(rtrim(str));
}

function ltrim(str) {
	var i = 0;
	while(i < str.length) {
		if (str.charAt(i) != " ")
			break;
		i++;
	}
	return str.substring(i, str.length);
}

function rtrim(str) {
  var i = str.length - 1;
  while (i >= 0) {
	  if(str.charAt(i) != " ")
		  break;
	  i--;
  }
  return str.substring(0, i + 1);
}

