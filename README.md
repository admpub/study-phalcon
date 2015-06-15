# phalcon（费尔康）框架学习笔记 #

以实例程序invo为例（invo程序放在网站根目录下的invo文件夹里，推荐php版本>=5.4）



## 环境不支持伪静态网址时的配置 ##
第一步：
在`app\config\config.ini`文件中的`[application]`节点内修改`baseUri`参数值为`/invo/index.php/`或`/invo/index.php?_url=/`，并增加一个参数`staticBaseUri`，值设为`/invo/`。
例如：

    ;支持非伪静态网址
    baseUri        = "/invo/index.php?_url=/"
    ;静态资源文件网址
    staticBaseUri  = /invo/

如果将baseUri设置为`/invo/index.php/`的话，需要在router服务中作如下设置，才能正常工作：

	$di -> set('router', function () {
		$router = new Router();
		$router->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI);//重要
		return $router;
	});
	


第二步：
在文件`app\config\services.php`中找到`$di->set('url',`所在位置，在其中的匿名函数内return语句前增加一行，输入`$url->setStaticBaseUri($config->application->staticBaseUri);`
> 
> 这里使用的是phalcon v2.0.2，此版本在使用非伪静态网址的过程中，发现存在一个bug：当在模板中使用`$this->tag->linkTo('products/search?page=1')`函数生成网址时，由于第一个参数中包含了问号，再加上配置文件中的`baseUri`中也包含问号，这样生成的网址中就包含两处问号，只能通过自己扩展Url类来修复了，下面是修复步骤。
> 
> 在文件`app\config\services.php`中添加以下代码：
> 
>     /**
>     * 重写Url，修复动态网址中关于问号的bug
>     *
>     * @author:S.W.H
>     * @E-mail:swh@admpub.com
>     * @update:2015/6/9
>     */
>     
>     class MyUrl extends UrlProvider{
>     	static public $hasDynamicUrl=null;
>     	public function get($uri=null, $args=null, $local=null){
>     		if(self::$hasDynamicUrl && strpos($uri,'?')!==false){
>     			$uri=str_replace('?','&',$uri);
>     		}
>     		return parent::get($uri, $args, $local);
>     	}
>     }
> 
> 并将代码：
> 
> 	$url = new UrlProvider();
> 
> 替换为：
> 
> 	$url = new \MyUrl();  
> 	\MyUrl::$hasDynamicUrl=strpos($config->application->baseUri,'?')!==false;
> 
> 即可解决。

## 路由规则 ##
添加路由规则：

	<?php

	use Phalcon\Mvc\Router;

	// Create the router
	$router = new Router();

	//Define a route
	$router->add(
    	"/admin/:controller/a/:action/:params",
    	array(
        	"controller" => 1, //匹配第一个占位符(:controller)
        	"action"     => 2, //匹配第二个占位符(:action)
        	"params"     => 3, //匹配第三个占位符(:params)
    	)
	);

支持的占位符有：
<table border="1" class="docutils">
<colgroup>
<col width="10%">
<col width="15%">
<col width="75%">
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">占位符</th>
<th class="head">正则表达式</th>
<th class="head">Usage</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>/:module</td>
<td>/([a-zA-Z0-9_-]+)</td>
<td>Matches a valid module name with alpha-numeric characters only</td>
</tr>
<tr class="row-odd"><td>/:controller</td>
<td>/([a-zA-Z0-9_-]+)</td>
<td>Matches a valid controller name with alpha-numeric characters only</td>
</tr>
<tr class="row-even"><td>/:action</td>
<td>/([a-zA-Z0-9_]+)</td>
<td>Matches a valid action name with alpha-numeric characters only</td>
</tr>
<tr class="row-odd"><td>/:params</td>
<td>(/.*)*</td>
<td>Matches a list of optional words separated by slashes. Use only this placeholder at the end of a route</td>
</tr>
<tr class="row-even"><td>/:namespace</td>
<td>/([a-zA-Z0-9_-]+)</td>
<td>Matches a single level namespace name</td>
</tr>
<tr class="row-odd"><td>/:int</td>
<td>/([0-9]+)</td>
<td>Matches an integer parameter</td>
</tr>
</tbody>
</table>

Controller名称是采用驼峰命名法（camel），这意味着“-”和“_”将会被删除并将其后的一个字符大写。
例如，some_controller 会被转换为 SomeController。

### 指定参数名称 ###
- 方式一，在数组中指定：


	<?php  
	$router->add(  
		"/news/([0-9]{4})/([0-9]{2})/([0-9]{2})/:params",  
		array(  
			"controller" => "posts",  
			"action"     => "show",  
			"year"       => 1, // ([0-9]{4})  
			"month"      => 2, // ([0-9]{2})  
			"day"        => 3, // ([0-9]{2})  
			"params"     => 4, // :params  
		)  
	);  


在上面的例子中，路由没有定义“controller”和“action”部分，而是被指定为“posts”和“show”，这样，用户将不知道控制器的真实请求路径。
在controller中，这些被命名的参数可以用如下方式这样访问：

	<?php
	use Phalcon\Mvc\Controller;
	class PostsController extends Controller{

    	public function indexAction(){
    	}

   	 	public function showAction(){
        	// Return "year" parameter
        	$year = $this->dispatcher->getParam("year");

        	// Return "month" parameter
        	$month = $this->dispatcher->getParam("month");

        	// Return "day" parameter
        	$day = $this->dispatcher->getParam("day");
    	}
	}


- 方式二，在路由中指定：


	$router->add(  
		"/documentation/{chapter}/{name}.{type:[a-z]+}",  
		array(  
			"controller" => "documentation",  
			"action"     => "show"  
		)  
	);  

看见了吗？花括号中的chaper、name和type就是相对应的名称了。

总结：路由中的匹配项，可以使用

- 占位符
- 正则表达式
- 带命名的正则表达式（命名与正则表达式间用冒号“:”隔开，并整个用花括号括起来）
- {命名}

指定名称空间的例子：

	$router->add("/login", array(
    	'namespace'  => 'Backend\Controllers',
    	'controller' => 'login',
    	'action'     => 'index'
	));


### 钩子事件 ###
转换某个参数的值：

	<?php
	//The action name allows dashes, an action can be: /products/new-ipod-nano-4-generation
	$router->add('/products/{slug:[a-z\-]+}', array(
        'controller' => 'products',
        'action'     => 'show'
    ))->convert('slug', function($slug) {
        //Transform the slug removing the dashes
        return str_replace('-', '', $slug);
    });

除了convert方法之外，还支持：

- 匹配回调函数


	->beforeMatch(function($uri, $route) {  
    	//Check if the request was made with Ajax  
    	if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'xmlhttprequest') {  
        	return false;  
    	}  
    	return true;  
	});//参数可以是匿名函数，也可以采用数组的方式指定某个对象的方法：array(new AjaxFilter(), 'check')  

- 限制主机名


	->setHostName('([a-z+]).company.com');


### 路由分组 ###
	<?php
	use Phalcon\Mvc\Router;
	use Phalcon\Mvc\Router\Group as RouterGroup;

	$router = new Router();

	//Create a group with a common module and controller
	$blog = new RouterGroup(array(
    	'module'     => 'blog',
    	'controller' => 'index'
	));

	//All the routes start with /blog
	$blog->setPrefix('/blog');

	//Add another route to the group
	$blog->add('/edit/{id}', array(
    	'action' => 'edit'
	));

	//Add the group to the router
	$router->mount($blog);

或者：

	<?php
	use Phalcon\Mvc\Router\Group as RouterGroup;

	class BlogRoutes extends RouterGroup{
    	public function initialize(){
        	//Default paths
        	$this->setPaths(array(
            	'module'    => 'blog',
            	'namespace' => 'Blog\Controllers'
        	));

        	//All the routes start with /blog
        	$this->setPrefix('/blog');

        	//Add another route to the group
        	$this->add('/edit/{id}', array(
            	'action' => 'edit'
        	));
    	}
	}

Then mount the group in the router:

	<?php
	//Add the group to the router
	$router->mount(new BlogRoutes());


### 路由命名 ###

	<?php
	$route = $router->add("/posts/{year}/{title}", "Posts::show");
	$route->setName("show-posts");

	//或者这样
	$router->add("/posts/{year}/{title}", "Posts::show")->setName("show-posts");

然后，我们就可以根据命名来生成符合这条路由的网址了：

	<?php
	// returns /posts/2012/phalcon-1-0-released
	echo $url->get(array(
    	"for"   => "show-posts",				//路由名称
    	"year"  => "2012",					//参数year的值
    	"title" => "phalcon-1-0-released" 	//参数title的值
	));

限制http请求方式：$router->addGet()、$router->addPut()、$router->addPost()……


### 指定URI来源 ###

	<?php
	use Phalcon\Mvc\Router;
	...
	$router->setUriSource(Router::URI_SOURCE_GET_URL); // use $_GET['_url'] (default)
	$router->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI); // use $_SERVER['REQUEST_URI'] (default)


## 控制器命名 ##
默认调用IndexController控制器中的indexAction方法。  
控制器名称需要加`Controller`后缀，动作名称需要加`Action`后缀。  
控制器的首字母要大写且继承自`Phalcon\Mvc\Controller`。  
控制器的文件名称与控制器全名完全相同并加扩展名“.php”。

## 视图渲染 ##
`Phalcon\Mvc\View` 默认采用PHP本身作为模板引擎，此时应该以`.phtml`作为视图文件扩展名。

可以在控制器方法中使用`$this->view->setVar("postId", $postId);`来传递变量到视图，然后在视图中用php来使用此变量，比如：`<?php echo $postId;?>`，setVar方法也可以通过接收关键字索引数组来一次传递多个值(类似于smarty中assign的批量赋值)。

`Phalcon\Mvc\View` 支持视图分层。
### 分层渲染 ###
第一步、渲染模板：
`视图文件目录`/`小写的控制器名（不含后缀）`/`方法名（不含后缀）`.phtml  
并保存结果。级别代号`LEVEL_ACTION_VIEW`。

> 可在此模板中通过调用`<?php echo $this->getContent() ?>`输出控制器中的输出内容（比如在控制器中使用echo输出一些内容）。

第二步、渲染模板（如果有）：    
`视图文件目录`/layouts/`小写的控制器名（不含后缀）`.phtml  
并保存结果。级别代号`LEVEL_LAYOUT`。

> 可在此模板中通过调用`<?php echo $this->getContent() ?>`输出第一步的模板结果。

第三步、渲染模板（如果有）：  
`视图文件目录`/index.phtml  
并保存结果。级别代号`LEVEL_MAIN_LAYOUT`。

> 同样的，可在此模板中通过调用`<?php echo $this->getContent() ?>`输出第二步的模板结果。

最后保存的结果就是视图的最终结果。


可以在控制器方法中使用`$this->view->setTemplateAfter('common');`来在第三步之前插入一个渲染操作，比如这里渲染模板：`视图文件目录`/layouts/common.phtml 

### 渲染级别控制 ###
可以在控制器方法中使用`$this->view->setRenderLevel(View::LEVEL_NO_RENDER);`来关闭渲染，或者仅仅渲染某个级别`$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);` 

也可以使用`$this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);`来禁止某个级别的渲染。


可以用`$this->view->pick('index/pick');`选择视图：

1. 如果pick方法接收到一个不包含“/”的字符串则仅仅设置`LEVEL_ACTION_VIEW`级视图；如果包含“/”则同时还会把第一个“/”前面的部分作为`LEVEL_LAYOUT`级视图,比如这里会使用“`视图文件目录`/layouts/index.phtml”文件
2. 如果接收到一个数字索引数组，则会将编号为0的元素作为`LEVEL_ACTION_VIEW`级视图，将编号为1的元素作为`LEVEL_LAYOUT`级视图

### 关闭视图 ###
如果你的控制器不在视图里产生(或没有)任何输出，你可以禁用视图组件来避免不必要的处理：

    $this->view->disable();


### 在模板中包含局部模板 ###

	<?php $this->partial('shared/login');?>
	
或者同时传递变量给局部模板,每一个索引最终会作为变量在局部模板中被赋值：

	<?php
	$this->partial('shared/login',array(
		'var1'=>'val1',
		'var2'=>'val2'
	));
	?>

## 缓存视图 ##
在控制器方法中的代码例子：

		//Check whether the cache with key "downloads" exists or has expired
        if ($this->view->getCache()->exists('downloads')) {

            //Query the latest downloads
            $latest = Downloads::find(array(
                'order' => 'created_at DESC'
            ));

            $this->view->latest = $latest;
        }

        //Enable the cache with the same key "downloads"
        $this->view->cache(array(
            'service'  => 'myCache',//使用自己的缓存服务，不设置时默认为viewCache
            'lifetime' => 86400,	//缓存时间
            'key' => 'downloads'	//缓存索引名
        ));


注册缓存服务：

    <?php
    
    use Phalcon\Cache\Frontend\Output as OutputFrontend;
    use Phalcon\Cache\Backend\Memcache as MemcacheBackend;
    
    //Set the views cache service
    $di->set('viewCache', function() {
    
    //Cache data for one day by default
    $frontCache = new OutputFrontend(array(
    	'lifetime' => 86400
    ));
    
    //Memcached connection settings
    $cache = new MemcacheBackend($frontCache, array(
    	'host' => 'localhost',
    	'port' => '11211'
    ));
    
    return $cache;
    });


其中“Phalcon\Cache\Frontend”中包含了对前台数据的处理操作（比如数据格式编码等）；  
“Phalcon\Cache\Backend”中包含了对各种后台缓存引擎的操作。

## 使用模板引擎
- 在控制器方法中指定模板引擎：

 		// Using more than one template engine
        $this->view->registerEngines(
            array(
                '.my-html' => 'MyTemplateAdapter',
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
            )
        );
方法`Phalcon\Mvc\View::registerEngines()`接受一个包含定义模板引擎数据的数组。每个引擎的键名是一个区别于其他引擎的拓展名。模板文件和特定的引擎关联必须有这些扩展名。
`Phalcon\Mvc\View::registerEngines()`会按照相关模板引擎定义的顺序来执行。如果`Phalcon\Mvc\View`发现视图文件具有相同名称但扩展名不同，它只会使用第一个。

- 在注册`view`服务时全局指定模板引擎：


	<?php  
	use Phalcon\Mvc\View;  
	//Setting up the view component  
	$di->set('view', function() {  
		$view = new View();  
		//A trailing directory separator is required  
		$view->setViewsDir('../app/views/');  
		$view->registerEngines(array(  
			'.my-html' ='MyTemplateAdapter' //元素值可以是类名、服务名或返回模板引擎对象的匿名函数  
		));  
		return $view;  
	}, true);  

Volt 视图最终会被编译成纯PHP代码

### Volt模板引擎语法

#### 3种不同含义的起始标签

1. ` {% ... %} `包裹的标签用于赋值或执行for循环、if条件判断等语句
2. ` {{ ... }} `包裹的标签用于打印表达式的结果到模板
3. ` {# ... #} `包裹注释，前后标签可以处于不同行

#### 语法详解

- `{{ post.title }}`相当于`$post->title`；  
	`{{ post.getTypes().name }}`相当于`$post->getTypes()->name`；

- `{{ post['title'] }}`相当于`$post['title']`；
- `{{ post.title|e }}`使用过滤器，竖线左边表达式的值将会作为过滤器的第一个参数；  
	`{{ '%.2f'|format(post.price) }}`相当于执行`sprintf('%.2f', $post->price)`；

	默认过滤器列表：
<table class="docutils" border="1">
<colgroup>
<col width="22%">
<col width="78%">
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">Filter</th>
<th class="head">Description</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>e</td>
<td>Applies Phalcon\Escaper-&gt;escapeHtml to the value</td>
</tr>
<tr class="row-odd"><td>escape</td>
<td>Applies Phalcon\Escaper-&gt;escapeHtml to the value</td>
</tr>
<tr class="row-even"><td>escape_css</td>
<td>Applies Phalcon\Escaper-&gt;escapeCss to the value</td>
</tr>
<tr class="row-odd"><td>escape_js</td>
<td>Applies Phalcon\Escaper-&gt;escapeJs to the value</td>
</tr>
<tr class="row-even"><td>escape_attr</td>
<td>Applies Phalcon\Escaper-&gt;escapeHtmlAttr to the value</td>
</tr>
<tr class="row-odd"><td>trim</td>
<td>Applies the <a class="reference external" href="http://php.net/manual/en/function.trim.php">trim</a> PHP function to the value. Removing extra spaces</td>
</tr>
<tr class="row-even"><td>left_trim</td>
<td>Applies the <a class="reference external" href="http://php.net/manual/en/function.ltrim.php">ltrim</a> PHP function to the value. Removing extra spaces</td>
</tr>
<tr class="row-odd"><td>right_trim</td>
<td>Applies the <a class="reference external" href="http://php.net/manual/en/function.rtrim.php">rtrim</a> PHP function to the value. Removing extra spaces</td>
</tr>
<tr class="row-even"><td>striptags</td>
<td>Applies the <a class="reference external" href="http://php.net/manual/en/function.striptags.php">striptags</a> PHP function to the value. Removing HTML tags</td>
</tr>
<tr class="row-odd"><td>slashes</td>
<td>Applies the <a class="reference external" href="http://php.net/manual/en/function.slashes.php">slashes</a> PHP function to the value. Escaping values</td>
</tr>
<tr class="row-even"><td>stripslashes</td>
<td>Applies the <a class="reference external" href="http://php.net/manual/en/function.stripslashes.php">stripslashes</a> PHP function to the value. Removing escaped quotes</td>
</tr>
<tr class="row-odd"><td>capitalize</td>
<td>Capitalizes a string by applying the <a class="reference external" href="http://php.net/manual/en/function.ucwords.php">ucwords</a> PHP function to the value</td>
</tr>
<tr class="row-even"><td>lower</td>
<td>Change the case of a string to lowercase</td>
</tr>
<tr class="row-odd"><td>upper</td>
<td>Change the case of a string to uppercase</td>
</tr>
<tr class="row-even"><td>length</td>
<td>Counts the string length or how many items are in an array or object</td>
</tr>
<tr class="row-odd"><td>nl2br</td>
<td>Changes newlines \n by line breaks (&lt;br /&gt;). Uses the PHP function <a class="reference external" href="http://php.net/manual/en/function.nl2br.php">nl2br</a></td>
</tr>
<tr class="row-even"><td>sort</td>
<td>Sorts an array using the PHP function <a class="reference external" href="http://php.net/manual/en/function.asort.php">asort</a></td>
</tr>
<tr class="row-odd"><td>keys</td>
<td>Returns the array keys using <a class="reference external" href="http://php.net/manual/en/function.array-keys.php">array_keys</a></td>
</tr>
<tr class="row-even"><td>join</td>
<td>Joins the array parts using a separator <a class="reference external" href="http://php.net/manual/en/function.join.php">join</a></td>
</tr>
<tr class="row-odd"><td>format</td>
<td>Formats a string using <a class="reference external" href="http://php.net/manual/en/function.sprintf.php">sprintf</a>.</td>
</tr>
<tr class="row-even"><td>json_encode</td>
<td>Converts a value into its <a class="reference external" href="http://php.net/manual/en/function.json-encode.php">JSON</a> representation</td>
</tr>
<tr class="row-odd"><td>json_decode</td>
<td>Converts a value from its <a class="reference external" href="http://php.net/manual/en/function.json-encode.php">JSON</a> representation to a PHP representation</td>
</tr>
<tr class="row-even"><td>abs</td>
<td>Applies the <a class="reference external" href="http://php.net/manual/en/function.abs.php">abs</a> PHP function to a value.</td>
</tr>
<tr class="row-odd"><td>url_encode</td>
<td>Applies the <a class="reference external" href="http://php.net/manual/en/function.urlencode.php">urlencode</a> PHP function to the value</td>
</tr>
<tr class="row-even"><td>default</td>
<td>Sets a default value in case that the evaluated expression is empty
(is not set or evaluates to a falsy value)</td>
</tr>
<tr class="row-odd"><td>convert_encoding</td>
<td>Converts a string from one charset to another</td>
</tr>
</tbody>
</table>

- for循环用法

基础用法：

		 {% for robot in robots %}
        	{{ robot.name|e }}
		 {% endfor %}

嵌套循环：

	{% for robot in robots %}  
  		{% for part in robot.parts %}  
  			Robot: {{ robot.name|e }} Part: {{ part.name|e }}  
		{% endfor %}  
	{% endfor %}  

获取索引值


    {% set numbers = ['one': 1, 'two': 2, 'three': 3] %}
    
    {% for name, value in numbers %}
      Name: {{ name }} Value: {{ value }}
    {% endfor %}

用if进行筛选


    {% for value in numbers if value < 2 %}
      Value: {{ value }}
    {% endfor %}
    
    {% for name, value in numbers if name != 'two' %}
      Name: {{ name }} Value: {{ value }}
    {% endfor %}

	else、elsefor


    {% for robot in robots %}
        Robot: {{ robot.name|e }} Part: {{ part.name|e }} <br/>
    {% else %}{# else也可以写成elsefor #}
        There are no robots to show
    {% endfor %}

可以在for结构中使用`{% break %}`和`{% continue %}`来跳出和执行下一次循环

- if条件判断
基本用法


    {% if robot.type == "cyborg" %}
    	  {{ robot.name|e }}
    {% endif %}
    
    {% if robot.type == "cyborg" %}
    	{{ robot.name|e }}
    {% else %}
    	{{ robot.name|e }} (not a cyborg)
    {% endif %}
    
    {% if robot.type == "cyborg" %}
        Robot is a cyborg
    {% elseif robot.type == "virtual" %}
        Robot is virtual
    {% elseif robot.type == "mechanical" %}
        Robot is mechanical
    {% endif %}

if中可以使用的内置变量：
<table class="docutils" border="1">
<colgroup>
<col width="22%">
<col width="78%">
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">Variable</th>
<th class="head">Description</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>loop.index</td>
<td>The current iteration of the loop. (1 indexed)</td>
</tr>
<tr class="row-odd"><td>loop.index0</td>
<td>The current iteration of the loop. (0 indexed)</td>
</tr>
<tr class="row-even"><td>loop.revindex</td>
<td>The number of iterations from the end of the loop (1 indexed)</td>
</tr>
<tr class="row-odd"><td>loop.revindex0</td>
<td>The number of iterations from the end of the loop (0 indexed)</td>
</tr>
<tr class="row-even"><td>loop.first</td>
<td>True if in the first iteration.</td>
</tr>
<tr class="row-odd"><td>loop.last</td>
<td>True if in the last iteration.</td>
</tr>
<tr class="row-even"><td>loop.length</td>
<td>The number of items to iterate</td>
</tr>
</tbody>
</table>

- 赋值  
	- 单个变量赋值： 

    {% set fruits = ['Apple', 'Banana', 'Orange'] %}  
    {% set name = robot.name %}


 	- 多个变量赋值：  

    {% set fruits = ['Apple', 'Banana', 'Orange'], name = robot.name, active = true %}

	- 支持的字面值：
<table border="1" class="docutils">
<colgroup>
<col width="22%">
<col width="78%">
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">字面值</th>
<th class="head">说明</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>“this is a string”</td>
<td>被单引号或双引号括起来的内容作为字符串</td>
</tr>
<tr class="row-odd"><td>100.25</td>
<td>带小数部分的数字作为(double/float)</td>
</tr>
<tr class="row-even"><td>100</td>
<td>不带小数的数字作为整数(integer)</td>
</tr>
<tr class="row-odd"><td>false</td>
<td>静态内容“false”作为布尔值中false</td>
</tr>
<tr class="row-even"><td>true</td>
<td>Constant “true” is the boolean true value</td>
</tr>
<tr class="row-odd"><td>null</td>
<td>Constant “null” is the Null value</td>
</tr>
</tbody>
</table>

	数组可以用中括号或花括号定义
	{# Other simple array #}
	{{ ['Apple', 1, 2.5, false, null] }}

	{# Multi-Dimensional array #}
	{{ [[1, 2], [3, 4], [5, 6]] }}

	{# Hash-style array #}
	{{ ['first': 1, 'second': 4/2, 'third': '3'] }}

	{% set myArray = {'Apple', 'Banana', 'Orange'} %}
	{% set myHash  = {'first': 1, 'second': 4/2, 'third': '3'} %}

算术运算符和比较符与PHP语法中的一致，逻辑运算符为：`or`,`and`,`not`

- if中的is测试操作  
	内置支持的测试：
<table border="1" class="docutils">
<colgroup>
<col width="19%">
<col width="81%">
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">Test</th>
<th class="head">Description</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>defined</td>
<td>Checks if a variable is defined (isset)</td>
</tr>
<tr class="row-odd"><td>empty</td>
<td>Checks if a variable is empty</td>
</tr>
<tr class="row-even"><td>even</td>
<td>Checks if a numeric value is even</td>
</tr>
<tr class="row-odd"><td>odd</td>
<td>Checks if a numeric value is odd</td>
</tr>
<tr class="row-even"><td>numeric</td>
<td>Checks if value is numeric</td>
</tr>
<tr class="row-odd"><td>scalar</td>
<td>Checks if value is scalar (not an array or object)</td>
</tr>
<tr class="row-even"><td>iterable</td>
<td>Checks if a value is iterable. Can be traversed by a “for” statement</td>
</tr>
<tr class="row-odd"><td>divisibleby</td>
<td>Checks if a value is divisible by other value</td>
</tr>
<tr class="row-even"><td>sameas</td>
<td>Checks if a value is identical to other value</td>
</tr>
<tr class="row-odd"><td>type</td>
<td>Checks if a value is of the specified type</td>
</tr>
</tbody>
</table>

- 宏定义：https://docs.phalconphp.com/zh/latest/reference/volt.html#macros


    {%- macro my_input(name, class="input-text") %}  
    {% return text_field(name, 'class': class) %}  
    {%- endmacro %}  
    
    {# Call the macro #}  
    {{ '&lt;p&gt;' ~ my_input('name') ~ '&lt;/p&gt;' }}  
    {{ '&lt;p&gt;' ~ my_input('name', 'input-text') ~ '&lt;/p&gt;' }}

	由以上代码可见，模板中字符串间连接符为`~`！

- 使用标签助手：https://docs.phalconphp.com/zh/latest/reference/volt.html#using-tag-helpers
<table border="1" class="docutils">
<colgroup>
<col width="61%">
<col width="39%">
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">Method</th>
<th class="head">Volt function</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>Phalcon\Tag::linkTo</td>
<td>link_to</td>
</tr>
<tr class="row-odd"><td>Phalcon\Tag::textField</td>
<td>text_field</td>
</tr>
<tr class="row-even"><td>Phalcon\Tag::passwordField</td>
<td>password_field</td>
</tr>
<tr class="row-odd"><td>Phalcon\Tag::hiddenField</td>
<td>hidden_field</td>
</tr>
<tr class="row-even"><td>Phalcon\Tag::fileField</td>
<td>file_field</td>
</tr>
<tr class="row-odd"><td>Phalcon\Tag::checkField</td>
<td>check_field</td>
</tr>
<tr class="row-even"><td>Phalcon\Tag::radioField</td>
<td>radio_field</td>
</tr>
<tr class="row-odd"><td>Phalcon\Tag::dateField</td>
<td>date_field</td>
</tr>
<tr class="row-even"><td>Phalcon\Tag::emailField</td>
<td>email_field</td>
</tr>
<tr class="row-odd"><td>Phalcon\Tag::numberField</td>
<td>number_field</td>
</tr>
<tr class="row-even"><td>Phalcon\Tag::submitButton</td>
<td>submit_button</td>
</tr>
<tr class="row-odd"><td>Phalcon\Tag::selectStatic</td>
<td>select_static</td>
</tr>
<tr class="row-even"><td>Phalcon\Tag::select</td>
<td>select</td>
</tr>
<tr class="row-odd"><td>Phalcon\Tag::textArea</td>
<td>text_area</td>
</tr>
<tr class="row-even"><td>Phalcon\Tag::form</td>
<td>form</td>
</tr>
<tr class="row-odd"><td>Phalcon\Tag::endForm</td>
<td>end_form</td>
</tr>
<tr class="row-even"><td>Phalcon\Tag::getTitle</td>
<td>get_title</td>
</tr>
<tr class="row-odd"><td>Phalcon\Tag::stylesheetLink</td>
<td>stylesheet_link</td>
</tr>
<tr class="row-even"><td>Phalcon\Tag::javascriptInclude</td>
<td>javascript_include</td>
</tr>
<tr class="row-odd"><td>Phalcon\Tag::image</td>
<td>image</td>
</tr>
<tr class="row-even"><td>Phalcon\Tag::friendlyTitle</td>
<td>friendly_title</td>
</tr>
</tbody>
</table>

- 函数
<table border="1" class="docutils">
<colgroup>
<col width="22%">
<col width="78%">
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">Name</th>
<th class="head">Description</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>content</td>
<td>Includes the content produced in a previous rendering stage</td>
</tr>
<tr class="row-odd"><td>get_content</td>
<td>Same as ‘content’</td>
</tr>
<tr class="row-even"><td>partial</td>
<td>Dynamically loads a partial view in the current template</td>
</tr>
<tr class="row-odd"><td>super</td>
<td>Render the contents of the parent block</td>
</tr>
<tr class="row-even"><td>time</td>
<td>Calls the PHP function with the same name</td>
</tr>
<tr class="row-odd"><td>date</td>
<td>Calls the PHP function with the same name</td>
</tr>
<tr class="row-even"><td>dump</td>
<td>Calls the PHP function ‘var_dump’</td>
</tr>
<tr class="row-odd"><td>version</td>
<td>Returns the current version of the framework</td>
</tr>
<tr class="row-even"><td>constant</td>
<td>Reads a PHP constant</td>
</tr>
<tr class="row-odd"><td>url</td>
<td>Generate a URL using the ‘url’ service</td>
</tr>
</tbody>
</table>

- 模板的继承
  - 父模板（templates/base.volt）  

		`{% block title %}默认标题{% endblock %}`

  - 子模板  

		{% extends "templates/base.volt" %}  
		{% block title %}重新定义的标题{% endblock %}

父模板中块(block)内的内容会被子模板中的同名块中的内容替换，除非在子模板中不存在该块的定义。  
如果想要保留或引用父模板中某block的内容，可以在子模板的同名块中使用`{{ super() }}`


- 新增模板函数  


    <?php
    
    use Phalcon\Mvc\View\Engine\Volt;
    
    $volt = new Volt($view, $di);
    
    $compiler = $volt->getCompiler();
    
    //This binds the function name 'shuffle' in Volt to the PHP function 'str_shuffle'  
    $compiler->addFunction('shuffle', 'str_shuffle');//第二个参数可以是函数名或匿名函数
    
- 新增过滤器


 	//This creates a filter 'hash' that uses the PHP function 'md5'   
	$compiler->addFilter('hash', 'md5');//第二个参数可以是函数名或匿名函数

- 编写扩展：https://docs.phalconphp.com/zh/latest/reference/volt.html#extensions

- 缓存视图片段


	{% cache ("article-" ~ post.id) 3600 %}
    	<h1>{{ post.title }}</h1>
    	<p>{{ post.content }}</p>
	{% endcache %}

- 可以在模板中直接通过服务名访问通过DI注册的服务。  
	在php模板中使用“$this->`服务名`”来访问。

## 设计表单
[https://docs.phalconphp.com/zh/latest/reference/tags.html](https://docs.phalconphp.com/zh/latest/reference/tags.html)


## 模型
[https://docs.phalconphp.com/zh/latest/reference/models.html](https://docs.phalconphp.com/zh/latest/reference/models.html)

模型类的名称使用表名称且首字母大写（如果表名称含下划线“_”，需要删除下划线并将原下划线位置后的一个字符大写），继承于`Phalcon\Mvc\Model`。  

例如，我们有数据表`member_account`,那么我们需要创建一个模型类`MemberAccount`。

模型类的文件名称与模型类名称一致。

### 数据库操作方法

- 添加:  直接设置传递过来的值即可 或可以使用save()方法
- 更新:  save()
- 删除:  delete()
- 查找:  find() findFirst()
- 运算:  count() sum() average() maximum() minimum()
- 保存:  save() 


	$robots = Robots::find(array(  
    	"type = 'virtual'",  
    	"order" => "name",  
    	"limit" => 100  
	));  
	foreach ($robots as $robot) {  
   		echo $robot->name, "\n";  
	}  
	$robots = Robots::find(array(
    	"conditions" => "type = ?1",
    	"bind"       => array(1 => "virtual")
	));

	$robot = Robots::findFirst(array("type = 'virtual'", "order" => "name"));
	echo "The first virtual robot name is ", $robot->name, "\n";


可用的查询选项如下：
<table border="1" class="docutils">
<colgroup>
<col width="5%">
<col width="70%">
<col width="26%">
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">参数</th>
<th class="head">描述</th>
<th class="head">举例</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>conditions</td>
<td>查询操作的搜索条件。用于提取只有那些满足指定条件的记录。默认情况下 Phalcon\Mvc\Model 假定第一个参数就是查询条件。</td>
<td>“conditions” =&gt; “name LIKE ‘steve%’”</td>
</tr>
<tr class="row-odd"><td>columns</td>
<td>只返回指定的字段，而不是模型所有的字段。 当用这个选项时，返回的是一个不完整的对象。</td>
<td>“columns” =&gt; “id, name”</td>
</tr>
<tr class="row-even"><td>bind</td>
<td>绑定与选项一起使用，通过替换占位符以及转义字段值从而增加安全性。</td>
<td>“bind” =&gt; array(“status” =&gt; “A”, “type” =&gt; “some-time”)</td>
</tr>
<tr class="row-odd"><td>bindTypes</td>
<td>当绑定参数时，可以使用这个参数为绑定参数定义额外的类型限制从而更加增强安全性。</td>
<td>“bindTypes” =&gt; array(Column::BIND_TYPE_STR, Column::BIND_TYPE_INT)</td>
</tr>
<tr class="row-even"><td>order</td>
<td>用于结果排序。使用一个或者多个字段，逗号分隔。</td>
<td>“order” =&gt; “name DESC, status”</td>
</tr>
<tr class="row-odd"><td>limit</td>
<td>限制查询结果的数量在一定范围内。</td>
<td>“limit” =&gt; 10 / “limit” =&gt; array(“number” =&gt; 10, “offset” =&gt; 5)</td>
</tr>
<tr class="row-even"><td>group</td>
<td>从多条记录中获取数据并且根据一个或多个字段对结果进行分组。</td>
<td>“group” =&gt; “name, status”</td>
</tr>
<tr class="row-odd"><td>for_update</td>
<td>通过这个选项， <a href="../api/Phalcon_Mvc_Model.html" class="reference internal"><em>Phalcon\Mvc\Model</em></a>  读取最新的可用数据，并且为读到的每条记录设置独占锁。</td>
<td>“for_update” =&gt; true</td>
</tr>
<tr class="row-even"><td>shared_lock</td>
<td>通过这个选项， <a href="../api/Phalcon_Mvc_Model.html" class="reference internal"><em>Phalcon\Mvc\Model</em></a>  读取最新的可用数据，并且为读到的每条记录设置共享锁。</td>
<td>“shared_lock” =&gt; true</td>
</tr>
<tr class="row-odd"><td>cache</td>
<td>缓存结果集，减少了连续访问数据库。</td>
<td>“cache” =&gt; array(“lifetime” =&gt; 3600, “key” =&gt; “my-find-key”)</td>
</tr>
<tr class="row-even"><td>hydration</td>
<td>Sets the hydration strategy to represent each returned record in the result</td>
<td>“hydration” =&gt; Resultset::HYDRATE_OBJECTS</td>
</tr>
</tbody>
</table>

如果你愿意，除了使用数组作为查询参数外，还可以通过一种面向对象的方式来创建查询：

	<?php
	$robots = Robots::query()
    ->where("type = :type:")
    ->andWhere("year < 2000")
    ->bind(array("type" => "mechanical"))
    ->order("name")
    ->execute();

最后，还有一个 findFirstBy&lt;property-name&gt;() 方法。这个方法扩展了前面提及的 “findFirst()” 方法。它允许您利用方法名中的属性名称，通过将要搜索的该字段的内容作为参数传给它，来快速从一个表执行检索操作。

&lt;property-name&gt;的内容为首字母大写的数据表字段名（如果字段名称含下划线“_”，需要删除下划线并将原下划线位置后的一个字符大写）。

例如，数据表字段名为`user_name`,可以采用`findFirstByUserName('admpub')`方法查询。

### 指定数据返回类型
`$findResult->setHydrateMode(Resultset::HYDRATE_ARRAYS);`

可选的值有：`Resultset::HYDRATE_ARRAYS`、`Resultset::HYDRATE_OBJECTS`、`Resultset::HYDRATE_RECORDS`。

也可以这样指定：

	$robots = Robots::find(array(
    	'hydration' => Resultset::HYDRATE_ARRAYS
	));

### 模型关联
有四种关联类型：1对1,1对多，多对1，多对多。关联可以是单向或者双向的，每个关联可以是简单的（一个1对1的模型）也可以是复杂的（1组模型）。

在Phalcon中,关联必须定义在某个模型的initialize()方法。通过方法belongsTo(),hasOne(),hasMany()和hasManyToMany()来定义当前模型中字段到另一个模型中字段之间的关联。上述每种方法都需要三个参数:本地字段，引用的模型，引用的字段。  

方法的具体含义：
<table border="1" class="docutils">
<colgroup>
<col width="35%">
<col width="65%">
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">Method</th>
<th class="head">Description</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>hasMany</td>
<td>Defines a 1-n relationship</td>
</tr>
<tr class="row-odd"><td>hasOne</td>
<td>Defines a 1-1 relationship</td>
</tr>
<tr class="row-even"><td>belongsTo</td>
<td>Defines a n-1 relationship</td>
</tr>
<tr class="row-odd"><td>hasManyToMany</td>
<td>Defines a n-n relationship</td>
</tr>
</tbody>
</table>

多对多必须关联3个模型，并分别设置它们的关联字段

	<?php
	use Phalcon\Mvc\Model;

	class Robots extends Model
	{
    	public $id;

    	public $name;

    	public function initialize()
   	 	{
        	$this->hasManyToMany(
            	"id",						//当前模型中的字段
            	"RobotsParts",				//关联到的中间表模型
            	"robots_id", "parts_id",	//分别为当前模型id与中间表相关联的字段和中间表与第三张表关联的字段，这两个字段都在中间表内
            	"Parts",					//第三张表模型名
            	"id"						//第三张表中与中间表关联的字段
        	);
    	}

	}


对于使用名称空间的情况下，可以设置别名，或在model类中使用以下方法，但是对于多对多的情况，对于第三张表由于无法设置别名，只能使用以下方法：

	$this->getRelated('Robots\Parts');

## PHQL
在执行操作之前必须要有相应的model文件存在。
### 创建 PHQL 查询
- 方式一、直接通过创建`Phalcon\Mvc\Model\Query`类的实例来查询：


	<?php
	use Phalcon\Mvc\Model\Query;

	// Instantiate the Query
	$query = new Query("SELECT * FROM Cars", $this->getDI());

	// Execute the query returning a result if any
	$cars = $query->execute();

- 方式二、在控制器或视图中，通过modelsManager(模型管理器)来查询：


	<?php
	//Executing a simple query
	$query  = $this->modelsManager->createQuery("SELECT * FROM Cars");
	$cars   = $query->execute();

	//With bound parameters
	$query  = $this->modelsManager->createQuery("SELECT * FROM Cars WHERE name = :name:");
	$cars   = $query->execute(array(
    	'name' => 'Audi'
	));

也可以简化的写为：

	//Executing a simple query
	$cars = $this->modelsManager->executeQuery("SELECT * FROM Cars");

	//Executing with bound parameters
	$cars = $this->modelsManager->executeQuery("SELECT * FROM Cars WHERE name = :name:", array(
    	'name' => 'Audi'
	));

注意：FROM后面的那个不是表名称而是模型类名称，这与真正的SQL语句是不同的。由于是模型类名称，所以也可以带名称空间。

- executeQuery($phql)与Cars::find()的查询结果是一样的；
- executeQuery($phql)->getFirst()与Cars::findFirst()结果一样。

### 插入数据：

	// Inserting using placeholders
	$phql = "INSERT INTO Cars (name, brand_id, year, style) "
      . "VALUES (:name:, :brand_id:, :year:, :style:)";
	$status=$manager->executeQuery($sql,
    	array(
        	'name'     => 'Lamborghini Espada',
        	'brand_id' => 7,
        	'year'     => 1969,
        	'style'    => 'Grand Tourer',
    	)
	);
	//Create a response
    #$response = new Response();
	//Check if the insertion was successful
    if ($status->success() == true) {
        //Change the HTTP status
        #$response->setStatusCode(201, "Created");
        #$robot->id = $status->getModel()->id;
        #$response->setJsonContent(array('status' => 'OK', 'data' => $robot));
    } else {
        //Change the HTTP status
        #$response->setStatusCode(409, "Conflict");
        //Send errors to the client
        $errors = array();
        foreach ($status->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }
        #$response->setJsonContent(array('status' => 'ERROR', 'messages' => $errors));
    }

更新、删除数据与插入数据类似。



### 使用查询构建器创建查询

	//Getting a whole set
	$robots = $this->modelsManager->createBuilder()
    ->from('Robots')
    ->join('RobotsParts')
    ->orderBy('Robots.name')
    ->getQuery()
    ->execute();

	//Getting the first row
	$robots = $this->modelsManager->createBuilder()
    ->from('Robots')
    ->join('RobotsParts')
    ->orderBy('Robots.name')
    ->getQuery()
    ->getSingleResult();

### 绑定参数

	//Passing parameters in the query construction
	$robots = $this->modelsManager->createBuilder()
    ->from('Robots')
    ->where('name = :name:', array('name' => $name))
    ->andWhere('type = :type:', array('type' => $type))
    ->getQuery()
    ->execute();

	//Passing parameters in query execution
	$robots = $this->modelsManager->createBuilder()
    ->from('Robots')
    ->where('name = :name:')
    ->andWhere('type = :type:')
    ->getQuery()
    ->execute(array('name' => $name, 'type' => $type));

### 转义保留字
将保留字用中括号括起来。例如：

	$phql   = "SELECT * FROM [Update]";
	$result = $manager->executeQuery($phql);

	$phql   = "SELECT id, [Like] FROM Posts";
	$result = $manager->executeQuery($phql);




## 其它

### URL重定向

重定向用来在当前的处理中跳转到其它的处理流：
    
    <?php
	// 此路由重定向到其它的路由
	$app->post('/old/welcome', function () use ($app) {
		$app->response->redirect("new/welcome")->sendHeaders();
	});

	$app->post('/new/welcome', function () use ($app) {
		echo 'This is the new Welcome';
	});

有以下跳转方式：

	//设置一个内部跳转
	$this->response->redirect( 'posts/index' );
	// 外部跳转url
	$this->response->redirect( 'http://www.admpub.com/blog', true );
	// 设置跳转 http状态
	$this->resopnse->redirect( 'http://www.admpub.com/blog' , true , 301 );

重定向不会禁用视图组件。因此，如果你想从一个controller/action重定向到另一个controller/acton上，视图将正常显示。当然，你也可以使用 $this->view->disable() 禁用视图输出。

### 存储/获取 Session数据

	$this->session->set("session_name", "session_value");
	$this->session->has("session-name");
	$this->session->get("session-name");
	$this->session->remove("session-name");
	$this->session->destroy();

### From 表单接收

	//获取$_POST['name'],第二个参数是过滤器，还可以传递第三个参数作为默认值,第四个参数为是否允许为空
    $name= $this->request->getPost("name", "string");

	//获取$_GET['email']
    $email=$this->request->getQuery("email", "email");

	//获取$_REQUEST['email']
    $email=$this->request->get("email", "email");

request的更多方法请参考phalcon源代码：`phalcon/http/request.zep`


从容器中获取的服务的最简单方式就是只用get方法，它将从容器中返回一个新的实例：

	<?php $request = $di->get('request'); ?>

或者通过下面这种魔术方法的形式调用：

	<?php $request = $di->getRequest(); ?>

### 处理Not-Found

当用户访问未定义的路由时， 微应用会试着执行 "Not-Found"处理器。

	<?php
	$app->notFound(function () use ($app) {
    	$app->response->setStatusCode(404, "Not Found")->sendHeaders();
    	echo 'This is crazy, but this page was not found!';
	});



### 微应用
[https://docs.phalconphp.com/zh/latest/reference/micro.html](https://docs.phalconphp.com/zh/latest/reference/micro.html)  
支持如下的中间件事件：
<table border="1" class="docutils">
<colgroup>
<col width="22%">
<col width="55%">
<col width="23%">
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">事件名</th>
<th class="head">触发</th>
<th class="head">是否可中止操作?</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>before</td>
<td>应用请求处理之前执行，常用来控制应用的访问权限</td>
<td>Yes</td>
</tr>
<tr class="row-odd"><td>after</td>
<td>请求处理后执行，可以用来准备回复内容</td>
<td>No</td>
</tr>
<tr class="row-even"><td>finish</td>
<td>发送回复内容后执行， 可以用来执行清理工作</td>
<td>No</td>
</tr>
</tbody>
</table>
### REST API
[https://docs.phalconphp.com/zh/latest/reference/tutorial-rest.html](https://docs.phalconphp.com/zh/latest/reference/tutorial-rest.html)


# 使用 phalcon devtools
如果提醒无法找到类这样的错误提示，需要在phalcon.php文件中添加以下代码：

	spl_autoload_register(function($className){
		$classDir = __DIR__.'/scripts/';
		$classFile = $classDir . str_replace('\\', '/', $className) . '.php';
		if (file_exists($classFile)) require_once($classFile);
	});

把所有文件复制到现有phalcon项目下新建的“devtools”文件夹中，并将其中的`webtools.php`复制到public文件夹下，并在public文件夹内新建文件`webtools.config.php`,内容为：


	define('PTOOLSPATH',__DIR__.'/../devtools/');
	define('PTOOLS_IP','127.0.0.1');
	spl_autoload_register(function($className){
		$classDir = PTOOLSPATH.'/scripts/';
		$classFile = $classDir . str_replace('\\', '/', $className) . '.php';
		if (file_exists($classFile)) require_once($classFile);
	});

修改public文件夹下的`webtools.php`文件，将其中的`require 'webtools.config.php';`剪切到文件最开头的`<?php`下一行。

经过测试，该工具对PHP版本要求较高，我在PHP5.4下无法使用。

#End
