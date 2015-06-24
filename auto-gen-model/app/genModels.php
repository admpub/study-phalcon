<?php
spl_autoload_register(function($className) {
	$classDir = __DIR__ . '/../../scripts/';
	$classFile = $classDir . str_replace('\\', '/', $className) . '.php';
	if (file_exists($classFile)) require_once($classFile);
});
//设置区开始=======================
$modelsDir = __DIR__ . '/models';				//模型文件保存路径
$tablePrefix = 'Cge';							//数据表前缀
$baseClass = 'ModelBase';						//基类
$namespace = 'CMF\Base\Models';					//模型类所属名称空间
$dbschemaNamespace = 'CMF\Base\Models\Base';	//
$dbschemaClassSuffix = 'Base';					//
$dbschemaDir = __DIR__ . '/models/base';		//
//设置区结束========================

if(!is_dir($modelsDir))mkdir($modelsDir,0777,true);
if(!is_dir($dbschemaDir))mkdir($dbschemaDir,0777,true);

$allModel = new \Phalcon\Builder\AllModels(array('directory' => __DIR__ . '/../',
	'modelsDir' => $dbschemaDir,
	'extends' => $baseClass,
	'namespace' => $dbschemaNamespace,
));
$allModel -> build();

if($tablePrefix)seekDir($dbschemaDir,function($dbschemaDir, $file) use($tablePrefix,$dbschemaClassSuffix) {
	$newFile = $dbschemaDir . '/' . preg_replace('/^('.$tablePrefix.')/', '', $file);
	rename($dbschemaDir . '/' . $file, $newFile);
	echo 'rename ' . $dbschemaDir . '/' . $file . '=>' . $newFile . PHP_EOL;
});

seekDir($dbschemaDir,function($dbschemaDir, $file) use($tablePrefix,$namespace,$baseClass,$dbschemaClassSuffix,$dbschemaNamespace,$modelsDir) {
	$suffix = $dbschemaClassSuffix;
	$info=explode('.',$file);
	if($baseClass.'.php'==$file || $file==$info[0].$suffix.'.'.$info[1])return;
	$content = file_get_contents($dbschemaDir . '/' . $file);
	if($tablePrefix)$content = preg_replace('/(class )'.$tablePrefix.'([^ ]+)( extends )/','$1$2'.$suffix.'$3', $content);
	else $content = preg_replace('/(class )([^ ]+)( extends )/','$1$2'.$suffix.'$3', $content);
	$content = preg_replace('/(public function getSource\(\)\s*{\s+)return \'[^\']+\';(\s+})/', '$1return parent::getSource();$2', $content);
	file_put_contents($dbschemaDir . '/' . $info[0].$suffix.'.'.$info[1], $content);
	unlink($dbschemaDir . '/' . $file);
	echo 'modify ' . $dbschemaDir . '/' . $info[0].$suffix.'.'.$info[1] . PHP_EOL;
	if(file_exists($modelsDir . '/' . $file)){
		echo 'skip generate ' . $modelsDir . '/' . $file . PHP_EOL;
		return;
	}
	$date=date('Y-m-d');
	$content=<<<EOT
<?php
namespace $namespace;
use $dbschemaNamespace\\{$info[0]}$suffix;
/**
* {$info[0]}
*
* @author:S.W.H
* @E-mail:swh@admpub.com
* @update:$date
*/

class {$info[0]} extends {$info[0]}$suffix{

}
EOT;
	file_put_contents($modelsDir . '/' . $file, $content);
	echo 'generate ' . $modelsDir . '/' . $file . PHP_EOL;
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
