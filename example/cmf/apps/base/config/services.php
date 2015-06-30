<?php
if(defined('APPS_PATH')==false)die('Access Denied.');

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Db\Profiler as ProfilerDb;

CMF :: $view = new View();
CMF :: $view -> registerEngines(array('.volt' => 'volt'));

CMF :: $loader = new Loader();
$namespaces=array(
	'CMF\Base\Plugins' => APPS_PATH . 'base/plugins/',
	'CMF\Base\Library' => APPS_PATH . 'base/library/',
	'CMF\Base\Controllers' => APPS_PATH . 'base/controllers/',
	'CMF\Base\Models' => APPS_PATH . 'base/models/',
);
$annotationRoutes=array();
if (CMF :: $config->module) {
	foreach (CMF :: $config->module as $k => $v) {
		if (!$v) continue;
		$namespaces['CMF\\'.ucfirst($k).'\Controllers']=APPS_PATH .$k. '/controllers/';
		if(strpos($v,':')){
			$routes=explode(';',trim($v));
			if($routes){
				foreach($routes as $r){
					$r=trim($r);
					if(!$r)continue;
					$tmp=explode(':',$r);
					$controller=trim($tmp[0]);
					$route=trim($tmp[1]);
					if($controller&&$route)$annotationRoutes[$k][$controller]=$route;
					unset($tmp,$controller,$route);
				}
			}
		}
	}
}
CMF :: $loader -> registerNamespaces($namespaces,true);
CMF :: $loader -> register();
$eventsManager = new EventsManager();
if(!CMF::$config->system->debug){
	#$eventsManager -> attach('dispatch:beforeDispatch', new \CMF\Base\Plugins\SecurityPlugin);
	$eventsManager -> attach('dispatch:beforeException', new \CMF\Base\Plugins\NotFoundPlugin);
}
CMF :: $dispatcher = new Dispatcher();
CMF :: $dispatcher -> setEventsManager($eventsManager);

CMF :: $di = new FactoryDefault();

/* 注解
CMF :: $di->set('annotations', function () {
	$annotations = new \Phalcon\Annotations\Adapter\Files(array(
		'annotationsDir' => CACHE_PATH.'annotations/'
	));
	return $annotations;
},true);// */

// 定义注解路由
CMF :: $di->set('router', function () use($annotationRoutes) {
	$router = new \Phalcon\Mvc\Router\Annotations(false);
	$router->removeExtraSlashes(true); //删除末尾的斜杠
	$router->setDefaultModule(CMF::$config->system->defaultModule);
	//$router->addModuleResource('frontend', 'CSQ\Frontend\Controllers\Member', '/member');
	if($annotationRoutes){
		foreach($annotationRoutes as $module=>$routes){
			if(!$routes) continue;
			$moduleNamespace=ucfirst($module);
			foreach($routes as $c=>$r){
				if($r{0}!='/')$r='/'.$r;
				$router->addModuleResource($module, 'CMF\\'.$moduleNamespace.'\Controllers\\'.$c, $r);
			}
		}
	}
	$route=json_decode(file_get_contents(APPS_PATH . 'base/config/route.json.php'),true);
	if($route){
		foreach($route as $k=>$v){
			if(!$v)continue;
			if($k{0}=='<'){
				$pos=strpos($k,'>');
				if($pos>0){
					$methods=substr($k,1,$pos-1);
					$methods=trim($methods,', ');
					$k=substr($k,$pos+1);
					$k=trim($k);
					if($methods){
						$methods=explode(',',$methods);
						if(empty($v['action'])){
							if(isset($v['method'])) unset($v['method']);
							foreach($methods as $key=>$val){
								$val=trim($val);
								if($val){
									$method=strtoupper($val);
									$v['action']=strtolower($val);
									$router->add($k, $v)->via(array($method));
								}
							}
							continue;
						}else{
							$v['method']=array();
							foreach($methods as $key=>$val){
								$val=trim($val);
								if($val)$v['method'][]=strtoupper($val);
							}
						}
					}
				}else{
					$k=ltrim($k,'<');
				}
			}
			$methods=null;
			if(isset($v['method'])){
				$methods=$v['method'];
				unset($v['method']);
			}
			if($methods){
				$router->add($k, $v)->via((array)$methods);
			}else{
				$router->add($k, $v);
			}
		}
	}
	return $router;
},true);

CMF :: $di -> set('url', function() {
	$url = new \CMF\Base\Library\MyUrl();
	\CMF\Base\Library\MyUrl :: $hasDynamicUrl = strpos(CMF :: $config -> system -> baseUri, '?') !== false;
	$url -> setBaseUri(CMF :: $config -> system -> baseUri);
	$url -> setStaticBaseUri(CMF :: $config -> system -> staticBaseUri);
	return $url;
});


CMF :: $di -> set('volt', function($view, $di) {
	$volt = new VoltEngine($view, $di);
	$volt -> setOptions(array('compiledPath' => ROOT_PATH . 'cache/volt/'));
	$compiler = $volt -> getCompiler();
	$compiler -> addFunction('is_a', 'is_a');
	return $volt;
} , true);

CMF :: $di -> set('profiler', function(){
	return new ProfilerDb();
}, true);

CMF :: $di -> set('db', function() {
	if(CMF::$config->system->debug){
		$eventsManager = new EventsManager();
		//Get a shared instance of the DbProfiler
		$profiler      = CMF :: $di->getProfiler();
		//Listen all the database events
		$eventsManager->attach('db', function($event, $connection) use ($profiler) {
			if ($event->getType() == 'beforeQuery') {
				$profiler->startProfile($connection->getSQLStatement());
			}
			if ($event->getType() == 'afterQuery') {
				$profiler->stopProfile();
			}
		});
	}
	$dbclass = 'Phalcon\Db\Adapter\Pdo\\' . CMF :: $config -> database -> adapter;
	$connection = new $dbclass(array(
		'host' => CMF :: $config -> database -> host,
		'username' => CMF :: $config -> database -> username,
		'password' => CMF :: $config -> database -> password,
		'dbname' => CMF :: $config -> database -> name,
		'charset' => CMF :: $config -> database -> charset
	));
	$connection->query('SET NAMES "'.CMF :: $config -> database -> charset.'"');
	if(CMF::$config->system->debug){
		//Assign the eventsManager to the db adapter instance
		$connection->setEventsManager($eventsManager);
	}
	return $connection;
});

//If the configuration specify the use of metadata adapter use it or use memory otherwise
CMF :: $di -> set('modelsMetadata', function() {
	return new MetaData();//Memory
	//new MetaData(array('metaDataDir'=>ROOT_PATH.'cache/'));//Files
});

//Start the session the first time some component request the session service
CMF :: $di -> set('session', function() {
	$session = new SessionAdapter();
	$session -> start();
	return $session;
});

//Register the flash service with custom CSS classes
CMF :: $di -> set('flash', function() {
	return new FlashSession(array(
		'error' => 'alert alert-danger',
		'success' => 'alert alert-success',
		'notice' => 'alert alert-info',
	));
});

/**
 * Register a user component
 *
 * CMF :: $di->set('elements', function(){
 * return new Elements();
 * });
 */