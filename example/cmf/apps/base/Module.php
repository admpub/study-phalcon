<?php
namespace CMF\Base;

use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface{
	private static $_modulePath=null,$_config=null,$_namespace=null;
	private static $_moduleName='base';

	public function getConfig(){
		if(is_null(self::$_config)) self::$_config = &\CMF::$config;
		return self::$_config;
	}

	public function getNamespace(){
		if(is_null(self::$_namespace)) self::$_namespace = 'CMF\\'.ucfirst(self::$_moduleName);
		return self::$_namespace;
	}

	public function getModulePath(){
		if(is_null(self::$_modulePath)) self::$_modulePath=APPS_PATH.self::$_moduleName.'/';
		return self::$_modulePath;
	}

	//注册加载器
	public function registerAutoloaders(\Phalcon\DiInterface $di=null){
		$loader=&\CMF::$loader;
		$ns=ucfirst(self::$_moduleName);
		$loader->registerNamespaces(array(
			self::getNamespace().'\Controllers'=>self::getModulePath().'controllers/',
			self::getNamespace().'\Models'=>self::getModulePath().'models/',
		),true);
		$loader->register();
	}

	//注册服务
	public function registerServices(\Phalcon\DiInterface $di){
		$di->setShared('dispatcher',function(){
			$dispatcher=&\CMF::$dispatcher;
			$dispatcher->setDefaultNamespace(\CMF\Base\Module::getNamespace().'\Controllers');
			return $dispatcher;
		});
		$di->setShared('view',function(){
			$view=&\CMF::$view;
			$view->setViewsDir(\CMF\Base\Module::getModulePath().'views/');
			return $view;
		});
	}
}