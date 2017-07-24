package.path= '/opt/nginx/lualib/?.lua;/home/webroot/changpei/lua/inc/?.lua;'
local cjson	= require "cjson"
local mysql	= require "mysql"
local redis	= require "redis"
local req	= require "req"

local key	= "go_carbrand"
local resp	= ""
local err	= ""
local expire= 500
local args	= req.get()

function isTableEmpty(t)
    if t == nil or _G.next(t) == nil then
        return true
    else
        return false
    end
end

if isTableEmpty(args) then
	local rd = redis:new()
	resp, err = rd:get(key)	
	if not resp then  
			local db = mysql:new()
			local sql = "select * from go_carbrand Order by letter ASC"
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
	local id = args['id']
	id = ngx.quote_sql_str(id)
	local db = mysql:new()
	local sql = "select * from go_carbrand where id=" .. id
	local res, err, errno, sqlstate = db:query(sql)
	if not res then
		ngx.say(err)
		return {}
	end
	db:close()
	resp = res
end

local result = {code="1",msg="汽车品牌",data=resp}
ngx.say(cjson.encode(result))
