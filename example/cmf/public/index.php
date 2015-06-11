<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
define('ROOT_PATH', realpath('..') . '/');
define('APPS_PATH', ROOT_PATH . 'apps/');

use Phalcon\Mvc\Application;
use Phalcon\Config\Adapter\Ini as ConfigIni;
// $_GET['_url'] = '/contact/send';
// $_SERVER['REQUEST_METHOD'] = 'POST';
try {
	CMF :: $config = new ConfigIni(APPS_PATH . 'base/config/config.ini.php');
	//Load base services
	require APPS_PATH . 'base/config/services.php';
	$application = new Application(CMF :: $di);
	if (CMF :: $config -> module) {
		$modules = array();
		foreach(CMF :: $config -> module as $k => $v) {
			if (!$v)continue;
			$modules[$k] = array(
				'className' => 'CMF\\' . ucfirst($k) . '\Module',
				'path' => APPS_PATH . $k . '/Module.php'
			);
		} #CMF::dump($modules);
		$application -> registerModules($modules);
	}
	echo $application -> handle() -> getContent();
}
catch (Exception $e) {
	echo $e -> getMessage();
}

class CMF {
	public static $view, $dispatcher, $loader, $config, $di;
	public static function dump($var,$exit=false){
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
		$exit && exit;
	}
	public static function getClassInfo($obj,$showType=true){
		$r = new \ReflectionClass($obj);
		$constants	=$r->getConstants();
		$properties	=$r->getProperties();
		$methods	=$r->getMethods();
		$m			=compact('constants','properties','methods');
		if(!$showType){
			return $m;
		}
		if($showType=='list'){
			if($m){
				echo '<h1>constants:</h1>';
				foreach($constants as $k=>$v){
					echo $k.'='.$v.'<br />'.PHP_EOL;
				}
				echo '<h1>properties:</h1>';
				foreach($properties as $v){
					echo $v->class.'->$'.$v->name.'<br />'.PHP_EOL;
				}
				echo '<h1>methods:</h1>';
				foreach($methods as $v){
					echo $v->class.'->'.$v->name.'()<br />'.PHP_EOL;
				}
			}
			return;
		}
		self::dump($m);
	}
	public function getMethods($objOrClass,$dump=true){
		if($dump){
			self::dump(get_class_methods($objOrClass));
			return;
		}
		return get_class_methods($objOrClass);
	}
}
