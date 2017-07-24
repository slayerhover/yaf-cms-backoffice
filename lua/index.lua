package.path= '/opt/nginx/lualib/?.lua;/home/webroot/changpei/lua/inc/?.lua;'
local cjson	= require "cjson"
local mysql	= require "mysql"
local redis	= require "redis"
local req	= require "req"

local args = req.get()
local id = args['id']
if id == nil or id == "" then
	id = "2"	
end
id = ngx.quote_sql_str(id) -- SQL 转义，将 ' 转成 \', 防SQL注入，并且转义后的变量包含了引号，所以可以直接当成条件值使用


local db = mysql:new()
local sql = "select * from go_carbrand where id=" .. id
local res, err, errno, sqlstate = db:query(sql)
if not res then
	ngx.say(err)
    return {}
end
db:close()


ngx.say(cjson.encode(res))
