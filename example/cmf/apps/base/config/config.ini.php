;<?php die;?>
[database]
adapter  = Mysql
host     = localhost
username = root
password = root
name     = testcsq
prefix   = cge_
charset  = utf8

[module]
base            = "Index:/errors;"
backend         = "Index:/admin;"
frontend        = "Index:/index;"

[system]
;支持非伪静态网址
baseUri        = "/cmf/index.php?_url=/"
;静态资源文件网址
staticBaseUri  = /cmf/
defaultModule  = frontend
debug          = 1
