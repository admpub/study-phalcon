<?php
include(__DIR__.'/common.inc.php');
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
