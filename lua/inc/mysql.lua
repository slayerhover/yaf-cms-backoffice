-- file name: mysql.lua
local mysql = require "resty.mysql"

local config = {
    host = "127.0.0.1",
    port = 3306,
    database = "changpei",
    user = "uu235",
    password = "yZJYj3Ns32WDnRCL",
	charset = "utf8",
    max_packet_size = 1024 * 1024,
}

local _M = {}


function _M.new(self)
    local db, err = mysql:new()
    if not db then
        return nil
    end
    db:set_timeout(3000) -- 1 sec

    local ok, err, errno, sqlstate = db:connect(config)
    if not ok then
		return nil
    end
	db:query("set names utf8")
    db.close = close
    return db
end

function close(self)
	local sock = self.sock
    if not sock then
        return nil, "not initialized"
    end
    if self.subscribed then
        return nil, "subscribed state"
    end
    return sock:setkeepalive(10000, 50)
end

return _M