<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
define('PUBLIC_PATH', __DIR__ . '/');
define('ROOT_PATH', realpath('..') . '/');
define('APPS_PATH', ROOT_PATH . 'apps/');
define('CACHE_PATH', ROOT_PATH . 'cache/');

CMF :: $startTime = microtime(true);
class CMF {
	public static $view, $dispatcher, $loader, $config, $di, $startTime;
	private static $_imageEngine;

	public static function table($table = '') {
		return self :: $config -> database -> prefix . $table;
	}

	public static function image($file, $width = null, $height = null) {
		if(!self::$_imageEngine)self::$_imageEngine = new \ReflectionClass('\Phalcon\Image\Adapter\\'.(class_exists('imagick',false)?'Imagick':'Gd'));
		return self::$_imageEngine->newInstance($file, $width, $height);
	}

	public static function verifyFile($file, $mimeTypes, $extTypes = null, $maxSize = -1, $genRandFileName = true){
		return \CMF\Base\Library\FileHelper::verifyAll($file, $mimeTypes, $extTypes, $maxSize, $genRandFileName);
	}

	public static function dump($var, $exit = false) {
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
		$exit && exit;
	}


	public static function getClassInfo($obj, $showType = true) {
		$r = new \ReflectionClass($obj);
		$constants = $r -> getConstants();
		$properties = $r -> getProperties();
		$methods = $r -> getMethods();
		$m = compact('constants', 'properties', 'methods');
		if (!$showType) {
			return $m;
		}
		if ($showType == 'list') {
			if ($m) {
				echo '<h1>constants:</h1>';
				foreach($constants as $k => $v) {
					echo $k . '=' . $v . '<br />' . PHP_EOL;
				}
				echo '<h1>properties:</h1>';
				foreach($properties as $v) {
					echo $v -> class . '->$' . $v -> name . '<br />' . PHP_EOL;
				}
				echo '<h1>methods:</h1>';
				foreach($methods as $v) {
					echo $v -> class . '->' . $v -> name . '()<br />' . PHP_EOL;
				}
			}
			return;
		}
		self :: dump($m);
	}


	public static function getMethods($objOrClass, $dump = true) {
		if ($dump) {
			self :: dump(get_class_methods($objOrClass));
			return;
		}
		return get_class_methods($objOrClass);
	}


	public static function getNamespaces($rootNamespace = '', $dump = true) {
		if (!$rootNamespace)$rootNamespace = 'Phalcon|CMF';
		$namespaces = preg_grep('/^(' . $rootNamespace . ')/', get_declared_classes());
		if ($dump) {
			self :: dump($namespaces);
			return;
		}
		return $namespaces;
	}


	public static function elapsedTime() {
		return microtime(true) - self :: $startTime;
	}

	/**
	 * 取得页码编号
	 *
	 * @return number
	 */
	public static function pageno() {
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):0;
		if ($page < 1)$page = 1;
		return $page;
	}

	/**
	 * 根据页码取得偏移值
	 *
	 * @param number $page 页码
	 * @param number $number 每页数据量
	 * @return number
	 */
	public static function poffset($page, $number = 20, $retArr = true) {
		$offset = ($page-1) * $number;
		if (!$retArr)return $offset;
		return array('number' => $number, 'offset' => $offset);
	}
}
