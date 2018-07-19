package.path= '/opt/nginx/lualib/?.lua;/home/webroot/changpei/lua/inc/?.lua;';
local func	= require "function";


local str = func.curl("https://www.baidu.com/s", 'wd=vice');
ngx.say(str);

local str = '{    "a": 123,    "c": "d",    "e": "f"}';
local res = func.json_decode(str);
func.dump(res);

local t = {a="b",c="de", f="ji"};
local res = func.json_encode(t);
func.dump(res);

local result = func.preg_match("([a-z]+)", "abc 1234, hello");
if result then
	func.dump(result);
else
	ngx.say("no matched.");
end

--ngx.exit(200);

local result = func.preg_match_all("(?<numbers>[\\d]+)", "abc 1234, hello, 78d90");
if result then
	func.dump(result);
	func.dump(func.json_encode(result));
else
	ngx.say("no matched.");
end
