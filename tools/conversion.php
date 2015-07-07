<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
//===============================================\
//配置区开始
//===============================================
//源数据库配置
$srcHost = 'localhost';
$srcUser = 'root';
$srcPwd = 'root';
$srcDatabase = 'testcsq';
$srcCharset = 'utf8';

//目标数据库配置
$dstHost = 'localhost';
$dstUser = 'root';
$dstPwd = 'root';
$dstDatabase = 'newcsq';
$dstCharset = 'utf8';
//===============================================
//配置区结束
//===============================================/

/**
 * 连接源数据库
 */
$srcLink = mysql_connect($srcHost, $srcUser, $srcPwd);
if (!$srcLink) {
	echo mysql_error();
	exit();
}
/**
 * 连接目标数据库
 */
$dstLink = mysql_connect($dstHost, $dstUser, $dstPwd);
if (!$dstLink) {
	echo mysql_error();
	exit();
}

$curDatabase = null;

/**
 * 定义通用函数
 */
function selectSrc() {
	global $srcLink, $srcDatabase, $curDatabase, $srcCharset;
	mysql_set_charset($srcCharset, $srcLink);
	mysql_select_db($srcDatabase, $srcLink);
	$curDatabase = $srcDatabase;
}

function selectDst() {
	global $dstLink, $dstDatabase, $curDatabase, $dstCharset;
	mysql_set_charset($dstCharset, $dstLink);
	mysql_select_db($dstDatabase, $dstLink);
	$curDatabase = $dstDatabase;
}

function querySrc($sql) {
	global $srcLink, $curDatabase, $srcDatabase;
	if ($curDatabase != $srcDatabase) {
		selectSrc();
	}
	$query = mysql_query($sql, $srcLink);
	if (!$query) {
		echos('[Src] ' . $sql . PHP_EOL);
		echo mysql_error() . PHP_EOL;
	}
	return $query;
}

function queryDst($sql) {
	global $dstLink, $curDatabase, $dstDatabase;
	if ($curDatabase != $dstDatabase) {
		selectDst();
	}
	$query = mysql_query($sql, $dstLink);
	if (!$query) {
		echos('[Dst] ' . $sql . PHP_EOL);
		echo mysql_error() . PHP_EOL;
	}
	return $query;
}

function fetchRow($query) {
	return mysql_fetch_assoc($query);
}

function fetchRowSrc($sql) {
	return fetchRow(querySrc($sql));
}

function fetchRowDst($sql) {
	return fetchRow(queryDst($sql));
}

/**
 * 数据表遍历查询处理
 * @param  closure  $fn     匿名函数
 * @param  string  $table  要查询的数据表名称
 * @param  integer $number 每次循环处理的数据量
 * @return void
 */
function processing($fn, $table, $number = 100) {
	$offset = 0;
	for ($i = 2;($query = querySrc('SELECT * FROM `' . $table . '` ORDER BY uid ASC LIMIT ' . $offset . ',' . $number)) && ($row = fetchRow($query)); $i++) {
		do {

			queryDst('START TRANSACTION');

			$result = call_user_func_array($fn, array($row));

			queryDst($result ? 'COMMIT' : 'ROLLBACK');

		} while ($row = fetchRow($query));
		$offset = ($i - 1) * $number;
	}
}

if (PHP_SAPI != 'cli') {
	header('content-type:text/html;charset=utf-8');
	echo '<pre>';
}

//===============================================\
//自定义执行代码开始
//===============================================
//主要是调用 processing($fn, $table, $number = 100)

/**
 * 执行相关操作
 */
function cge_member_account($number = 100) {
	$table = __FUNCTION__;
	processing(function ($row) use ($table) {
		$holding_value = $row['left_money'];
		unset($row['left_money']);
		$result = queryDst('INSERT INTO `' . $table . '` (`' . implode('`,`', array_keys($row)) . '`)
			VALUES (\'' . implode('\',\'', array_map('addslashes', array_values($row))) . '\')');

		$result && $result = queryDst('INSERT INTO cge_member_holding (`member_uid`,`holding_type`,`holding_value`,`holding_account`,`register_date`)
		 VALUES
		 (\'' . $row['member_uid'] . '\',\'4\',\'' . $holding_value . '\',\'' . $row['uid'] . '\',\'' . $row['register_date'] . '\')');

		echos('创建数据 ' . $table . '.uid：' . $row['uid'] . ' ' . ($result ? '成功' : '失败') . PHP_EOL);
		return $result;
	}, $table, $number);
}

#cge_member_account();

//===============================================
//自定义执行代码结束
//===============================================/

if (PHP_SAPI != 'cli') {
	echo '</pre>';
}
function echos($string) {
	echo PHP_SAPI == 'cli' ? iconv('utf-8', 'gbk', $string) : $string;
}