package.path= '/opt/nginx/lualib/?.lua;/home/webroot/changpei/lua/inc/?.lua;'
local cjson	= require "cjson"
local mysql	= require "mysql"
local redis	= require "redis"
local req	= require "req"

local key	= "go_factory"
local resp	= ""
local err	= ""
local expire= 500
local args	= req.get()

if next(args) == nil then
	local rd = redis:new()
	resp, err = rd:get(key)	
	if not resp then  
			local db = mysql:new()
			local sql = "select * from go_carfactory Order by id ASC"
			local res, err, errno, sqlstate = db:query(sql)
			if not res then
				ngx.say("sql error:"..errno)
				return {}
			end
			db:close()
			
			resp = res
			local ok, err = rd:setex(key, expire, cjson.encode(res))
			if not ok then
				ngx.say("failed to set "..key, err)
				return
			end
	else
		resp = cjson.decode(resp)
	end  
	if resp == ngx.null then  
		resp = ''
	end
else	
	local brand_id = args['brand_id']
	brand_id = ngx.quote_sql_str(brand_id)
	local db = mysql:new()
	local sql = "select * from go_carfactory where brand_id=" .. brand_id
	local res, err, errno, sqlstate = db:query(sql)
	if not res then
		ngx.say(err)
		return {}
	end
	db:close()
	resp = res
end

local result = {code="1",msg="汽车厂家",data=resp}
ngx.say(cjson.encode(result))
