# yaf-cms-backoffice
a simple cms backoffice by yaf &amp; easyui.  Base project for expansions.

部署环境
===================================
  推荐使用OpenResty,方便使用luajit
  
PHP扩展
===================================
  1. Yaf
  2. Yar
  3. Redis
  
模板框架
===================================
  easyUI
  
 CDN
===================================
  七牛cdn, 上传文件、图片使用
  
Vendor
===================================
  laravel Eloquent
   
配置文件
===================================
  nginx配置
  ```
  server {
        listen       80;
        server_name  www.yafcms.com;
        root   /home/webroot/yafcms/public;
        index  index.html index.php;
        lua_code_cache off;

        location / {
                include php.conf;
                if (!-e $request_filename) {
                        rewrite  ^(.*)$  /index.php?$1  last;
                        break;
                }
        }
        location ~ ^/lapi/([-_a-zA-Z0-9/]+) {
                default_type 'text/html';
                set $path $1;
                content_by_lua_file /home/webroot/yafcms/lua/$path.lua;
        }

        location ~ .*\.(jpg|png|js|css|gif|jpeg|ttf|woff)
        {
                expires 3s;
                access_log off;
        }
        error_log   /home/logs/error.log;
        access_log  /home/logs/yafcms.log access;
}
```
 
  网站配置文件： /conf/app.ini
  
访问路径
===================================   
###1.前端访问 http://www.yafcms.com/index.php ,主要用于接口调用输出，不调用模板显示,可用控制器index和user.
  
###2.后端访问 http://www.yafcms.com/admin,  easyUI和smarty模板输出。
  
###3.rpc远程服务调用 http://www.yafcms.com/rpc
  
###4.lua高性能接口地址 http://www.yafcms.com/lapi/xxx, xxx会自动导入对应的xxx.lua文件执行。
  
  
