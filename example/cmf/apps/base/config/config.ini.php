;<?php die;?>
[database]
adapter  = Mysql
host     = localhost
username = root
password = root
name     = test
prefix   = swh_

[module]
base            = 1
backend         = 1
frontend        = 1

[system]
;支持非伪静态网址
baseUri        = "/cmf/index.php?_url=/"
;静态资源文件网址
staticBaseUri  = /cmf/
defaultModule  = frontend
debug          = 0

[route]
/errors/:action = "{'module':'base','controller':'errors','action':1}"
/login          = "{'module':'frontend','controller':'login','action':'index'}"
/admin          = "{'module':'backend','controller':'index','action':'index'}"
/admin/:controller          = "{'module':'backend','controller':1,'action':'index'}"
/admin/:controller/:action  = "{'module':'backend','controller':1,'action':2}"