<?php

use Phalcon\Mvc\View;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Events\Manager as EventsManager;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

/**
 * We register the events manager
 */
$di->set('dispatcher', function() use ($di) {

	$eventsManager = new EventsManager;

	/**
	 * Check if the user is allowed to access certain action using the SecurityPlugin
	 */
	$eventsManager->attach('dispatch:beforeDispatch', new SecurityPlugin);

	/**
	 * Handle exceptions and not-found exceptions using NotFoundPlugin
	 */
	$eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);

	$dispatcher = new Dispatcher;
	$dispatcher->setEventsManager($eventsManager);

	return $dispatcher;
});

/**
* 重写Url，修复动态网址中关于问号的bug
*
* @author:S.W.H
* @E-mail:swh@admpub.com
* @update:2015/6/9
*/

class MyUrl extends UrlProvider{
	static public $hasDynamicUrl=null;
	public function get($uri=null, $args=null, $local=null){
		if(self::$hasDynamicUrl && strpos($uri,'?')!==false){
			$uri=str_replace('?','&',$uri);
		}
		return parent::get($uri, $args, $local);
	}
}
/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set('url', function() use ($config){
	$url = new \MyUrl();
	\MyUrl::$hasDynamicUrl=strpos($config->application->baseUri,'?')!==false;
	$url->setBaseUri($config->application->baseUri);
	$url->setStaticBaseUri($config->application->staticBaseUri);
	return $url;
});


$di->set('view', function() use ($config) {

	$view = new View();

	$view->setViewsDir(APP_PATH . $config->application->viewsDir);

	$view->registerEngines(array(
		".volt" => 'volt'
	));

	return $view;
});

/**
 * Setting up volt
 */
$di->set('volt', function($view, $di) {

	$volt = new VoltEngine($view, $di);

	$volt->setOptions(array(
		"compiledPath" => APP_PATH . "cache/volt/"
	));

	$compiler = $volt->getCompiler();
	$compiler->addFunction('is_a', 'is_a');

	return $volt;
}, true);

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function() use ($config) {
	$dbclass = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
	return new $dbclass(array(
		"host"     => $config->database->host,
		"username" => $config->database->username,
		"password" => $config->database->password,
		"dbname"   => $config->database->name
	));
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function() {
	return new MetaData();
});

/**
 * Start the session the first time some component request the session service
 */
$di->set('session', function() {
	$session = new SessionAdapter();
	$session->start();
	return $session;
});

/**
 * Register the flash service with custom CSS classes
 */
$di->set('flash', function(){
	return new FlashSession(array(
		'error'   => 'alert alert-danger',
		'success' => 'alert alert-success',
		'notice'  => 'alert alert-info',
	));
});

/**
 * Register a user component
 */
$di->set('elements', function(){
	return new Elements();
});
