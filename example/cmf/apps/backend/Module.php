<?php
namespace CMF\Backend;

use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Config\Adapter\Ini as ConfigIni;

class Module implements ModuleDefinitionInterface{
	private static $_modulePath=null,$_config=null,$_namespace=null;
	private static $_moduleName='backend';

	public static function getConfig(){
		if(is_null(self::$_config)) self::$_config = new ConfigIni(self::getModulePath() . 'config/config.ini.php');
		return self::$_config;
	}

	public static function getNamespace(){
		if(is_null(self::$_namespace)) self::$_namespace = 'CMF\\'.ucfirst(self::$_moduleName);
		return self::$_namespace;
	}

	public static function getModulePath(){
		if(is_null(self::$_modulePath)) self::$_modulePath=APPS_PATH.self::$_moduleName.'/';
		return self::$_modulePath;
	}

	//注册加载器
	public function registerAutoloaders(\Phalcon\DiInterface $di=null){
		$loader=&\CMF::$loader;
		$ns=ucfirst(self::$_moduleName);
		$loader->registerNamespaces(array(
			self::getNamespace().'\Plugins' => self::getModulePath().'plugins/',
			self::getNamespace().'\Library' => self::getModulePath().'library/',
			self::getNamespace().'\Controllers' => self::getModulePath().'controllers/',
			self::getNamespace().'\Models' => self::getModulePath().'models/',
		),true);
		$loader->register();
	}

	//注册服务
	public function registerServices(\Phalcon\DiInterface $di){
		$di->setShared('dispatcher',function(){
			$dispatcher=&\CMF::$dispatcher;
			$dispatcher->setDefaultNamespace(\CMF\Backend\Module::getNamespace().'\Controllers');
			//\CMF::dump($dispatcher);
			return $dispatcher;
		});
		$di->setShared('view',function(){
			$view=&\CMF::$view;
			$view->setViewsDir(\CMF\Backend\Module::getModulePath().'views/');
			return $view;
		});
	}
}