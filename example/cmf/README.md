结构说明：

	┣apps 文件夹中存放各个模块应用
	┃ ┣━ base 文件夹中存放控制器、模型等的基类，它们作为其它模块的控制器或模型的父类被用来继承
	┃ ┃    ┣━ config 文件夹保存全站配置文件
	┃ ┃    ┣━ controllers 文件夹保存控制器基类和通用控制器类
	┃ ┃    ┣━ library 文件夹保存通用类库
	┃ ┃    ┣━ models 文件夹保存模型基类和通用模型类
	┃ ┃    ┣━ plugins 文件夹保存插件文件
	┃ ┃    ┗━ views 文件夹保存视图文件
	┃ ┣━ frontend 文件夹中存放前台模块
	┃ ┃    ┣━ config 文件夹保存本模块专用的配置文件
	┃ ┃    ┣━ controllers 文件夹保存本模块控制器类
	┃ ┃    ┣━ library 文件夹保存本模块专用类库
	┃ ┃    ┣━ models 文件夹保存本模块专用模型类
	┃ ┃    ┣━ plugins 文件夹保存本模块专用插件文件
	┃ ┃    ┗━ views 文件夹保存视图文件
	┃ ┗━ backend 文件夹中存放管理后台模块
	┃      ┣━ config 文件夹保存本模块专用的配置文件
	┃      ┣━ controllers 文件夹保存本模块控制器类
	┃      ┣━ library 文件夹保存本模块专用类库
	┃      ┣━ models 文件夹保存本模块专用模型类
	┃      ┣━ plugins 文件夹保存本模块专用插件文件
	┃      ┗━ views 文件夹保存视图文件
	┃ 
	┣cache 文件夹中存放缓存
	┣public 文件夹中存放可通过网址访问的文件
	┗schemas 文件夹中存放sql文件等

数据库相关的配置在“apps\base\config\config.ini.php”中设置。