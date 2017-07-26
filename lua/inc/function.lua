local cjson	= require "cjson"
local http	= require "resty.http"

local _M = { _VERSION="1.0.0"};

function _M.empty(t)
    if t == nil or _G.next(t) == nil then
        return true
    else
        return false
    end
end

function _M.dump(t)
    local function parse_array(key, tab)
		local str = ''
		for _, v in pairs(tab) do
			str	=	str .. key .. ' = ' .. v .. '\r\n'
		end
		
		return str
	end
	
	local str = ''
	for k,v in pairs(t) do
		if type(v)=="table" then
			str = str .. parse_array(k, v)
		else
			str = str .. k .. ' = ' .. (v) ..  '\r\n'
		end
	end
	
	ngx.say(str)
end	

function _M.curl(url, params, method)
	local httpc	=	http.new()
	httpc:set_timeout(2000) --2秒超时
	local resp, err = httpc:request_uri(url, {  
		method	= method,  
		body 	= params,
		headers = {  
			["User-Agent"]  = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.111 Safari/537.36",
			["Content-Type"]= "application/x-www-form-urlencoded"
			
		}  
	})
	if not resp then  		
		return false
	end  
	  	
	httpc:close()
	return resp.body
end

function _M.json_encode(t)
	cjson.encode_empty_table_as_object(true)
	local str = cjson.encode(t)
	return str
end

function _M.json_decode(str)
	local json = false
	pcall(function(str) json = cjson.decode(str) end, str)
	return json
end

function _M:split(split, str)
	local str_split_tab = {}
	while true do
		local idx = string.find(str,split,1,true);
		if nil~=idx then
			local insert_str = '';
			if 1==idx then
				insert_str = string.sub(str, 1,idx + #split - 1);
			else
				insert_str = string.sub(str, 1,idx - 1);
			end

			if (insert_str ~= split) and (nil ~= insert_str or '' ~= insert_str) then
				table.insert(str_split_tab,insert_str);
			end
			str = string.sub(str,idx + #split,-1);
		else
			if nil ~= str or '' ~= str then
				table.insert(str_split_tab,str);
			end
			break;
		end
	end
	return str_split_tab;
end

function _M:replace(str, find, replace)
	local res,res_count = string.gsub(str,find,replace);
	return res,res_count
end

function _M:trim(str)
	str = _M:ltrim( str );
	str = _M:rtrim( str );
	return str;
end

function _M:ltrim( str )
	if ''==str or nil==str then
		return str;
	end
	local len = string.len( str );
	
	local substart = 1;
	local  lenadd = 1;
	while ( string.find ( str," ",lenadd) == lenadd ) do
		substart = substart + 1;
		lenadd= lenadd + 1;
	end
	
	str=string.sub ( str ,substart ,len);
	
	local substart1 = 1;
	local lenadd1 = 1;
	len=string.len(str);
	while(string.find(str,"%s",lenadd1 )==lenadd1) do
        		substart1 = substart1 +1
        		lenadd1 = lenadd1 +1
	end
	
	str=string.sub(str,substart1,len);
	
	return str;
end

function _M:rtrim( str )
	if ''==str or nil==str then
		return str;
	end
	local len = string.len(str);
	
	local substart = len;
	local  lenadd = len;
	while ( string.find ( str,"%s",lenadd) == lenadd ) do
		substart = substart - 1;
		lenadd= lenadd - 1;
	end
	str =string.sub(str , 1,substart );
	len=string.len(str);

	local substart1 = len;
	local lenadd1 = len;
	while(string.find(str," ",lenadd1 )==lenadd1) do
        		substart1 = substart1 -1
        		lenadd1 = lenadd1 -1
	end
	
	str=string.sub(str,1,substart);

	return str;

end

function _M:upper(str)
	if ''==str or nil==str then
		return str;
	end
	return string.upper(str);
end

function _M:lower(str)
	if ''==str or nil==str then
		return str;
	end
	return string.lower(str);
end


return _M;
