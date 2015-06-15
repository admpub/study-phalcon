<?php
include(__DIR__ . '/common.inc.php');
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection as MicroCollection;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Loader;

// $_GET['_url'] = '/contact/send';
// $_SERVER['REQUEST_METHOD'] = 'POST';
try {
	CMF :: $config = new ConfigIni(APPS_PATH . 'base/config/config.ini.php');
	CMF :: $loader = new Loader();
	CMF :: $loader -> registerNamespaces(array(
		'CMF\Base\Plugins' => APPS_PATH . 'base/plugins/',
		'CMF\Base\Library' => APPS_PATH . 'base/library/',
		'CMF\Base\Controllers' => APPS_PATH . 'base/controllers/',
		'CMF\Base\Models' => APPS_PATH . 'base/models/',
	),true);
	CMF :: $loader -> registerDirs(array(
		APPS_PATH . 'base/models/'
	)) -> register();

	$app = new Micro();
	// 设置数据库服务实例
	$app['db'] = function() {
		$dbclass = 'Phalcon\Db\Adapter\Pdo\\' . CMF :: $config -> database -> adapter;
		$connection = new $dbclass(array(
			'host' => CMF :: $config -> database -> host,
			'username' => CMF :: $config -> database -> username,
			'password' => CMF :: $config -> database -> password,
			'dbname' => CMF :: $config -> database -> name,
			'charset' => CMF :: $config -> database -> charset
		));
		$connection->query('SET NAMES "'.CMF :: $config -> database -> charset.'"');
		return $connection;
	};
	#$app->mount(groupRoute(new Products(),'/product',array('get|/'=>'index','post|get|/post'=>'publish')));
	$app -> get('/', function () use ($app) {
		$result=\CMF\Base\Models\MemberAccount::find(array('limit'=>10));
		$content=array();
		foreach($result as $k=>$v){
			$content[$k]=$v;
		}
		$app->response->setContentType('application/json')->sendHeaders();
		$app->response->setJsonContent($content);
		#CMF::dump($content);
		return $app->response;
	});
	$app->notFound(function () use ($app) {
		$app->response->setStatusCode(404, "Not Found")->sendHeaders();
		echo 'This is crazy, but this page was not found!';
	});
	$app->handle();
}
catch (Exception $e) {
	echo $e -> getMessage();
}


/**
 * 微应用组路由
 * example:
 * $app->mount(
 *	groupRoute(
 * 		new Products(),
 * 		'/product',
 * 		array('get|/'=>'index','post|get|/post'=>'publish')
 *	)
 * );
 */
function groupRoute($handlerObject,$prefix=null,$routeMap=array()){
	$collect = new MicroCollection();

	// 设置主处理器，这里是控制器的实例
	$collect->setHandler($handlerObject);

	// 对所有路由设置前缀
	$collect->setPrefix($prefix?$prefix:'/'.get_class($handlerObject));

	// 使用PostsController中的index action
	//$collect->get('/', 'index');
	if($routeMap){
		foreach($routeMap as $route=>$action){
			$rs=explode('|',$route);
			$ct=count($rs);
			if($ct==1){
				$rs=array('get',$route);
				$ct=2;
			}
			$ed=$ct-1;
			if(is_array($action)){
				for($i=0,$len=$ed-1; $i<$len; $i++){
					$collect->$rs[$i]($rs[$ed], $action[0], $action[1]);
				}
				continue;
			}
			for($i=0,$len=$ed-1; $i<$len; $i++){
				$collect->$rs[$i]($rs[$ed], $action);
			}
		}
	}
	return $collect;
}