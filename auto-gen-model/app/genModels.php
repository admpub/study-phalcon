<?php
spl_autoload_register(function($className) {
	$classDir = __DIR__ . '/../../scripts/';
	$classFile = $classDir . str_replace('\\', '/', $className) . '.php';
	if (file_exists($classFile)) require_once($classFile);
});
//设置区开始=======================
$modelsDir = __DIR__ . '/models';		//模型文件保存路径
$tablePrefix = 'Cge';					//数据表前缀
$baseClass = 'ModelBase';				//基类
$namespace = 'CMF\Base\Models';			//模型类所属名称空间
//设置区结束========================


$allModel = new \Phalcon\Builder\AllModels(array('directory' => __DIR__ . '/../',
	'modelsDir' => $modelsDir,
	'extends' => $baseClass,
	'namespace' => $namespace,
));
$allModel -> build();

if($tablePrefix)seekDir($modelsDir,function($modelsDir, $file) use($tablePrefix) {
	$newFile = $modelsDir . '/' . preg_replace('/^('.$tablePrefix.')/', '', $file);
	rename($modelsDir . '/' . $file, $newFile);
	echo 'rename ' . $modelsDir . '/' . $file . '=>' . $newFile . PHP_EOL;
});

seekDir($modelsDir,function($modelsDir, $file) use($tablePrefix) {
	$content = file_get_contents($modelsDir . '/' . $file);
	if($tablePrefix)$content = preg_replace('/(class )'.$tablePrefix.'([^ ]+ extends )/','$1$2', $content);
	$content = preg_replace('/(public function getSource\(\)\s*{\s+)return \'[^\']+\';(\s+})/', '$1return parent::getSource();$2', $content);
	file_put_contents($modelsDir . '/' . $file, $content);
	echo 'modify ' . $modelsDir . '/' . $file . PHP_EOL;
});

function seekDir($modelsDir,$callback) {
	if ($dh = opendir($modelsDir)) {
		while (($file = readdir($dh)) !== false) {
			if ($file != '.' && $file != '..' && $file != '.svn') {
				if (!is_dir($modelsDir . '/' . $file)) { // 如果是文件
					call_user_func_array($callback, array($modelsDir, $file));
				}
			}
		}
		closedir($dh);
	}
}
