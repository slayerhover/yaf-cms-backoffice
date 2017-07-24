package.path= '/opt/nginx/lualib/?.lua;/home/webroot/changpei/lua/inc/?.lua;';
local func	= require "function";


local str = func.curl("http://www.chemcatch.cn");
ngx.say(str);

local str = '{    "a": 123,    "c": "d",    "e": "f"}';
local res = func.json_decode(str);
func.dump(res);
ngx.say(type(res));



local t = {a="b",c="de", f="ji"};
local res = func.json_encode(t);
ngx.say(res);
