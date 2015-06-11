<?php
if(defined('APPS_PATH')==false)die('Access Denied.');
include(APPS_PATH . 'base/library/MyUrl.php');
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Router;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Events\Manager as EventsManager;

CMF :: $view = new View();
CMF :: $view -> registerEngines(array('.volt' => 'volt'));

CMF :: $loader = new Loader();
CMF :: $loader -> registerNamespaces(array(
	'CMF\Base\Plugins' => APPS_PATH . 'base/plugins/',
	'CMF\Base\Library' => APPS_PATH . 'base/library/',
	'CMF\Base\Controllers' => APPS_PATH . 'base/controllers/',
	'CMF\Base\Models' => APPS_PATH . 'base/models/',
),true);
$eventsManager = new EventsManager();
include(APPS_PATH.'base/plugins/NotFoundPlugin.php');
include(APPS_PATH.'base/plugins/SecurityPlugin.php');
#$eventsManager -> attach('dispatch:beforeDispatch', new \CMF\Base\Plugins\SecurityPlugin);
$eventsManager -> attach('dispatch:beforeException', new \CMF\Base\Plugins\NotFoundPlugin);
CMF :: $dispatcher = new Dispatcher();
CMF :: $dispatcher -> setEventsManager($eventsManager);

CMF :: $di = new FactoryDefault();
// 自定义路由
CMF :: $di -> set('router', function () {
		$router = new Router();
		$router -> setDefaultModule(CMF::$config->system->defaultModule);
		$router -> add('/errors/:action', array(
			'module' => 'base',
			'controller' => 'errors',
			'action' => 1,
		));
		$router -> add('/login', array(
			'module' => 'frontend',
			'controller' => 'login',
			'action' => 'index',
		));
		$router -> add('/admin/errors/:action', array(
			'module' => 'base',
			'controller' => 'errors',
			'action' => 1,
		));
		$router -> add('/admin/:controller/:action', array(
			'module' => 'backend',
			'controller' => 1,
			'action' => 2,
		));
		return $router;
	}
);


CMF :: $di -> set('url', function() {
		$url = new \CMF\Base\Library\MyUrl();
		\CMF\Base\Library\MyUrl :: $hasDynamicUrl = strpos(CMF :: $config -> system -> baseUri, '?') !== false;
		$url -> setBaseUri(CMF :: $config -> system -> baseUri);
		$url -> setStaticBaseUri(CMF :: $config -> system -> staticBaseUri);
		return $url;
	}
);


CMF :: $di -> set('volt', function($view, $di) {
		$volt = new VoltEngine($view, $di);
		$volt -> setOptions(array('compiledPath' => ROOT_PATH . 'cache/volt/'));
		$compiler = $volt -> getCompiler();
		$compiler -> addFunction('is_a', 'is_a');
		return $volt;
	} , true);


CMF :: $di -> set('db', function() {
		$dbclass = 'Phalcon\Db\Adapter\Pdo\\' . CMF :: $config -> database -> adapter;
		return new $dbclass(array('host' => CMF :: $config -> database -> host,
				'username' => CMF :: $config -> database -> username,
				'password' => CMF :: $config -> database -> password,
				'dbname' => CMF :: $config -> database -> name
				));
	}
	);

//If the configuration specify the use of metadata adapter use it or use memory otherwise
CMF :: $di -> set('modelsMetadata', function() {
		return new MetaData();
	}
);

//Start the session the first time some component request the session service
CMF :: $di -> set('session', function() {
		$session = new SessionAdapter();
		$session -> start();
		return $session;
	}
);

//Register the flash service with custom CSS classes
CMF :: $di -> set('flash', function() {
		return new FlashSession(array('error' => 'alert alert-danger',
				'success' => 'alert alert-success',
				'notice' => 'alert alert-info',
				));
	}
);

/**
 * Register a user component
 *
 * CMF :: $di->set('elements', function(){
 * return new Elements();
 * });
 */