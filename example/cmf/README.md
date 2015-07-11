#通用基础框架

这是一个用于快速构建新项目的通用基础框架。
  
1. 提供了合理的文件目录组织结构；
2. 增加了用于简化操作的快捷调用接口（对于经常用到，编写起来比较繁复的代码进行包装，以便于调用）；
3. 对于原生SQL的CURD提供良好支持并支持读写分离；
4. 对开发人员友好的其它改进。

## 结构说明：

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

##名称空间命名规则

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

##路由规则说明

支持两种方式注册路由（可同时使用，亦可单独使用）：

### 1. 注解路由
	
通过在控制器类方法的文档注释块中添加路由标签的方式来注册路由，我们称它为注解路由。

例如：



		<?php
		/**
	 	 * @RoutePrefix("/api/products")
	 	 */
		class ProductsController
		{

    	/**
     	 * @Get("/")
     	 */
    	public function indexAction() {}

    	/**
     	 * @Get("/edit/{id:[0-9]+}", name="edit-robot")
     	 */
    	public function editAction($id) {}

    	/**
     	 * @Route("/save", methods={"POST", "PUT"}, name="save-robot")
    	 */
    	public function saveAction() {}

    	/**
      	 * @Route("/delete/{id:[0-9]+}", methods="DELETE",
     	 *      conversors={id="MyConversors::checkId"})
     	 */
    	public function deleteAction($id) {}

    	public function infoAction($id) {}

		}


支持的注解有：
<table border="1" class="docutils">
<colgroup>
<col width="8%">
<col width="55%">
<col width="38%">
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">名称</th>
<th class="head">说明</th>
<th class="head">用法</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>RoutePrefix</td>
<td>作为本控制器中每一个注解路由网址的前缀，此注解必须添加到类的文档注释块中。</td>
<td>@RoutePrefix("/api/products")</td>
</tr>
<tr class="row-odd"><td>Route</td>
<td>标记一个方法作为路由，此注解必须放在方法的文档注释块中。</td>
<td>@Route("/api/products/show")</td>
</tr>
<tr class="row-even"><td>Get</td>
<td>标记一个方法作为GET请求的路由</td>
<td>@Get("/api/products/search")</td>
</tr>
<tr class="row-odd"><td>Post</td>
<td>标记一个方法作为POST请求的路由</td>
<td>@Post("/api/products/save")</td>
</tr>
<tr class="row-even"><td>Put</td>
<td>标记一个方法作为PUT请求的路由</td>
<td>@Put("/api/products/save")</td>
</tr>
<tr class="row-odd"><td>Delete</td>
<td>标记一个方法作为DELETE请求的路由</td>
<td>@Delete("/api/products/delete/{id}")</td>
</tr>
<tr class="row-even"><td>Options</td>
<td>标记一个方法作为OPTIONS请求的路由</td>
<td>@Option("/api/products/info")</td>
</tr>
</tbody>
</table>

用注解添加路由时，支持以下参数：
<table border="1" class="docutils">
<colgroup>
<col width="8%">
<col width="55%">
<col width="38%">
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">参数名</th>
<th class="head">说明</th>
<th class="head">用法</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>methods</td>
<td>用于定义一个或多个接受的HTTP请求方式</td>
<td>@Route("/api/products", methods={"GET", "POST"})</td>
</tr>
<tr class="row-odd"><td>name</td>
<td>为路由定义一个名称</td>
<td>@Route("/api/products", name="get-products")</td>
</tr>
<tr class="row-even"><td>paths</td>
<td>传递一个与传递给 Phalcon\Mvc\Router::add 相似的数组</td>
<td>@Route("/posts/{id}/{slug}", paths={module="backend"})</td>
</tr>
<tr class="row-odd"><td>conversors</td>
<td>应用于参数的处理</td>
<td>@Route("/posts/{id}/{slug}", conversors={id="MyConversor::getId"})</td>
</tr>
</tbody>
</table>

### 2. 在配置文件中注册路由

以json格式配置在“apps\base\config\route.json.php”文件中：

	{
		"<GET,POST>/errors/show404": {"module":"base","controller":"errors","action":"show404"}
	}

- 前面的双引号中（即`"<GET,POST>/errors/show404"`）包含两部分内容：

	1. HTTP请求方式，它们用尖括号括起来，如果支持多种HTTP请求方式则相互用半角逗号“,”隔开；
	2. 路由网址规则。

	第一部分是可选的，如果没有第一部分则意味着任意请求方式均能访问。

- 元素值（即`{"module":"base","controller":"errors","action":"show404"}`）为对应的设置。  

	如果不指定action值的话，会将当前的HTTP请求方式的全小写名称作为action值。

	> 此功能只在设置了上面所说的第一部分内容，也就是明确限制了HTTP请求方式时才有效。  

	比如，上面的规则，如果不设置action值，当用GET方式访问网址时，action值为get，用POST方式访问网址时，action值为post。


