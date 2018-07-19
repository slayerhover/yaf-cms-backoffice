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
		local function parse_array(tab)
			local str = ''
			for _, v in pairs(tab) do
				str	=	str .. '\t\t' .. _ .. ' => ' .. v .. '\n'
			end			
			return str
		end
		
		local str = type(t);		
		if str=='table' then		
			str = str .. '(' .. #t .. ')' .. '\n{\n' 
			for k,v in pairs(t) do
				if type(v)=="table" then
					str = str .. '\t' .. k .. ' = > {\n' .. parse_array(v) .. '\t}' .. '\n'
				else
					str = str .. '\t' .. k .. ' => ' .. (v) ..  '\n'
				end
			end
		else
			str = str .. '\n{\n' .. tostring(t) .. '\n'
		end		
		str = str .. '}'
		
		ngx.say('\n' .. str .. '\n')
end	

function _M.curl(url, params, method)
	local httpc	=	http.new()
	httpc:set_timeout(2000) --2秒超时
	local resp, err = httpc:request_uri(url, {  
		method	= method or 'GET',
		body 	= params,
		ssl_verify = false, --兼容https
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

function _M.sizeof(t)
	local count = 0
	for _, v in pairs(t) do
		count = count + 1
	end			
	return count;
end
function _M.count(t)
	local count = 0
	for _, v in pairs(t) do
		count = count + 1
	end			
	return count;
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

function _M.explode(split, str)
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

function _M.implode(split, t)
	local tab = {}
	for _, v in pairs(t) do
		table.insert(tab, v)
	end			
	return table.concat(tab, split);
end

function _M.str_replace(str, find, replace)
	local res,res_count = string.gsub(str,find,replace);
	return res,res_count
end

function _M.strpos(str, find)
	local res,res_end = string.find(str, find)
	if ''==res or nil==res then
		return false;
	end	
	return res;
end

function _M.stripos(str, find)
	local str = string.lower(str);
	local find= string.lower(find);
	
	local res,res_end = string.find(str, find)
	if ''==res or nil==res then
		return false;
	end	
	return res;
end

function _M.preg_match(regex, str)
	local res,err = ngx.re.match(str, regex, "io")
	if res then
		return res;
	else
		ngx.log(ngx.ERR, "error: ", err)
		return false;
	end
end

function _M.preg_match_all(regex, str)
	local it,err = ngx.re.gmatch(str, regex, "io")
    if not it then
        ngx.log(ngx.ERR, "error: ", err)
        return false;
    end
	local res = {};	
    while true do
        local m, err = it()
        if err then
            ngx.log(ngx.ERR, "error: ", err)
            return false;
        end 
        if not m then
            break
        end
		table.insert(res, m);
    end			
	return res;
end

function _M.preg_replace(regex, replacement, str, option)
	local newstr, n, err = ngx.re.gsub(str, regex, replacement, option)
    if newstr then
		return newstr;		
        -- newstr == "[hello,h], [world,w]"
        -- n == 2
    else
        ngx.log(ngx.ERR, "error: ", err)
        return false;
    end
end

function _M.trim(str)
	str = _M:ltrim( str );
	str = _M:rtrim( str );
	return str;
end

function _M.ltrim( str )
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

function _M.rtrim( str )
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

function _M.upper(str)
	if ''==str or nil==str then
		return str;
	end
	return string.upper(str);
end

function _M.lower(str)
	if ''==str or nil==str then
		return str;
	end
	return string.lower(str);
end


return _M;