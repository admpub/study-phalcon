#结构说明：

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

#名称空间命名规则
所有名称空间必须作为根名称空间“CMF”的子空间。
子名称空间与该文件所处的文件夹名称相同，但首字母必须大写。

比如：  
mobileapp模块下controllers文件内的文件，应该这样定义名称空间  
`namespace CMF\Mobileapp\Controllers;`

如果定义成（首字母小写了）  
`namespace CMF\mobileapp\controllers;`  
或者(MobileApp与模块目录不一致，除非你的模块目录名为mobileApp或MobileApp)  
`namespace CMF\MobileApp\Controllers;` 
都是不符合规范的。