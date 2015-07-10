<?php
namespace CMF\Base\Models;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query\BuilderInterface;
// 分页功能
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * description
 *
 * @author :S.W.H
 * @E -mail:swh@admpub.com
 * @update :2015/6/11
 */

class ModelBase extends Model {
	private static $_transactionId = null;

	public function initialize() {
		// 以下为主从分离范例
		// $this->setReadConnectionService('dbSlave');
		// $this->setWriteConnectionService('dbMaster');
	}

	// 表前缀
	public function getSource() {
		return \CMF::$config->database->prefix . parent::getSource();
	}

	static public function dbConn($rw = 'r') {
		return \Phalcon\Di::getDefault()->getShared('db');
	}

	// =======================================================\
	// 原生SQL支持
	// =======================================================|

	// 原生SQL查询
	public function rawQuery($sql, $params = null) {
		if (\CMF::$config->system->debug) {
			\CMF::$di->getProfiler()->startProfile($sql);
			\CMF::$di->getShared('dbLogger')->log($sql, \Phalcon\Logger::INFO);
		}
		$sth = self::dbConn('r')->prepare($sql);
		if ($params !== null) {
			if (is_array($params)) {
				if (is_array(reset($params))) {
					foreach ($params as $k => $v) {
						if (gettype($k) == 'integer') {
							$k += 1;
						}

						$sth->bindValue($k, $v[0], $v[1]/*PDO::PARAM_INT*/);
					}
					$params = null;
				}
			} else {
				$params = array($params);
			}
			$sth->execute($params);
		} else {
			$sth->execute();
		}
		$sth->setFetchMode(\PDO::FETCH_ASSOC);
		if (\CMF::$config->system->debug) {
			\CMF::$di->getProfiler()->stopProfile();
		}
		return $sth;
	}

	// 执行原生SQL
	public function rawExec($sql, $params = null, $returnLastInsertId = false) {
		if (\CMF::$config->system->debug) {
			\CMF::$di->getProfiler()->startProfile($sql);
			\CMF::$di->getShared('dbLogger')->log($sql, \Phalcon\Logger::INFO);
		}
		$dbh = self::dbConn('w');
		if ($params) {
			$sth = $dbh->prepare($sql);
			if (is_array($params)) {
				if (is_array(reset($params))) {
					foreach ($params as $k => $v) {
						if (gettype($k) == 'integer') {
							$k += 1;
						}

						$sth->bindValue($k, $v[0], $v[1]/*PDO::PARAM_INT*/);
					}
					$params = null;
				}
			} else {
				$params = array($params);
			}
			$res = $sth->execute($params);
			$affected = $res ? $sth->rowCount() : 0;
		} else {
			$affected = $dbh->exec($sql);
		}
		if (\CMF::$config->system->debug) {
			\CMF::$di->getProfiler()->stopProfile();
		}
		if ($returnLastInsertId) {
			return $affected > 0 ? $dbh->lastInsertId() : 0;
		}

		return $affected;
	}

	public function rawSelect($fields = '*', $table, $condtion = '', $params = null) {
		if ($table{0} != '`') {
			$table = '`' . \CMF::table($table) . '`';
		}

		if ($condtion) {
			$condtion = ' WHERE ' . $condtion;
		}

		if (!$fields) {
			$fields = '*';
		}

		$sql = 'SELECT ' . $fields . ' FROM ' . $table . $condtion;
		return self::rawQuery($sql, $params);
	}

	public function rawInsert($table, $data, $retId = true) {
		$values = '';
		foreach ($data as $k => $v) {
			if ($values) {
				$values .= ',';
			}

			$values .= '?';
		}
		if ($table{0} != '`') {
			$table = '`' . \CMF::table($table) . '`';
		}

		$sql = 'INSERT INTO ' . $table . ' (`' . implode('`,`', array_keys($data)) . '`) VALUES (' . $values . ')';
		return self::rawExec($sql, array_values($data), $retId);
	}

	public function rawInsertBatch($table, $data) {
		$values = '';
		$fields = '`' . implode('`,`', array_keys(reset($data))) . '`';
		$params = array();
		foreach ($data as $val) {
			$vstring = '';
			foreach ($val as $v) {
				if ($vstring) {
					$vstring .= ',';
				}

				$vstring .= '?';
				$params[] = $v;
			}
			if ($vstring) {
				$values .= '(' . $vstring . '),';
			}

		}
		$values = rtrim($values, ',');
		if ($table{0} != '`') {
			$table = '`' . \CSQ::table($table) . '`';
		}

		$sql = 'INSERT INTO ' . $table . ' (' . $fields . ') VALUES ' . $values;
		return self::rawExec($sql, $params);
	}

	public function rawUpdate($table, $data, $where = '', $params = array()) {
		$values = '';
		$_params = array();
		foreach ($data as $k => $v) {
			if ($values) {
				$values .= ',';
			}

			$values .= '`' . $k . '`=?';
			$_params[] = $v;
		}
		if ($params) {
			foreach ($params as $v) {
				$_params[] = $v;
			}

		}
		if ($where) {
			$where = ' WHERE ' . $where;
		}

		if ($table{0} != '`') {
			$table = '`' . \CMF::table($table) . '`';
		}

		$sql = 'UPDATE ' . $table . ' SET ' . $values . $where;
		return self::rawExec($sql, $_params);
	}

	public function rawDelete($table, $where, $data = null) {
		if ($table{0} != '`') {
			$table = '`' . \CMF::table($table) . '`';
		}

		$sql = 'DELETE FROM ' . $table . ' WHERE ' . $where;
		return self::rawExec($sql, $data);
	}

	public function rawReplace($table, $data, $retId = true) {
		$values = '';
		foreach ($data as $k => $v) {
			if ($values) {
				$values .= ',';
			}

			$values .= '?';
		}
		if ($table{0} != '`') {
			$table = '`' . \CMF::table($table) . '`';
		}

		$sql = 'REPLACE INTO ' . $table . ' (`' . implode('`,`', array_keys($data)) . '`) VALUES (' . $values . ')';
		return self::rawExec($sql, array_values($data), $retId);
	}
	//========================================================/

	public function sqlRead($sql, $params = array()) {
		// Execute the query
		return new Resultset(null, $this, $this->getReadConnection()->query($sql, $params));
	}

	public function sqlWrite($sql, $params = array()) {
		// Execute the query
		return new Resultset(null, $this, $this->getWriteConnection()->query($sql, $params));
	}

	// 开始事务
	public function begin($id = null) {
		if (!self::$_transactionId) {
			self::dbConn('w')->begin();
			self::$_transactionId = $id;
		}
	}

	// 结束事务
	public function end($isOk = true, $id = null) {
		if ($isOk) {
			if (self::$_transactionId != $id) {
				return;
			}

			self::dbConn('w')->commit();
		} else {
			self::dbConn('w')->rollback();
		}
		self::$_transactionId = null;
	}

	/**
	 * 更便捷的获取分页数据
	 * @param  array  			$parameters 查询条件
	 * @param  integer 			$number     每页数据量
	 * @param  string|array  	$className  执行查询的操作。可以是：类名(如未指定方法名，会自动调用该类中的find方法)、数组(包含 对象/类名 和方法名)、匿名函数
	 * @return object
	 */
	public function pageItems($parameters, $number = 20, $className = null) {
		$page = isset($_REQUEST['page']) && ($_REQUEST['page'] = intval($_REQUEST['page'])) > 0 ? $_REQUEST['page'] : 1;
		$offset = ($page - 1) * $number;
		$parameters['limit'] = array('number' => $number, 'offset' => $offset);
		if (is_string($className) || is_null($className)) {
			$data = !$className ? self::find($parameters) : call_user_func_array(strpos($className, '::') ? $className : $className . '::find', array($parameters));
		} else {
			$data = call_user_func_array($className, array($parameters));
		}
		$data = self::pageSplit($data, $number, $page);
		return $data;
	}

	/**
	 * 万能分页函数
	 *
	 * @param mix $data
	 * @param number $limit
	 * @param number $currentPage
	 * @return stdclass 对象属性如下：
	 * items 	The set of records to be displayed at the current page
	 * current 	The current page
	 * before 	The previous page to the current one
	 * next 	The next page to the current one
	 * last 	The last page in the set of records
	 * total_pages 	The number of pages
	 * total_items 	The number of items in the source data
	 * @author AdamShen (swh@admpub.com)
	 * @update 2015/6/11
	 */
	public function pageSplit($data, $limit = 10, $currentPage = null) {
		if (is_null($currentPage)) {
			$currentPage = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;
		}
		if ($currentPage < 1) {
			$currentPage = 1;
		}

		if ($data instanceof ResultsetInterface) {
			$paginator = new PaginatorModel(array('data' => $data,
				'limit' => $limit,
				'page' => $currentPage,
			));
		} elseif ($data instanceof BuilderInterface) {
			$paginator = new PaginatorQueryBuilder(array('builder' => $data,
				'limit' => $limit,
				'page' => $currentPage,
			));
		} else {
			// 数组
			$paginator = new PaginatorArray(array('data' => $data,
				'limit' => $limit,
				'page' => $currentPage,
			));
		}
		// Get the paginated results
		$page = $paginator->getPaginate();
		return $page;
	}

	/**
	 * Dynamically selects a shard
	 *
	 * @param array $intermediate
	 * @param array $bindParams
	 * @param array $bindTypes
	 */
	public function selectReadConnection($intermediate, $bindParams, $bindTypes) {
		return $this->getDI()->getShared('db');
		// 以下是范例
		// Check if there is a 'where' clause in the select
		if (isset($intermediate['where'])) {
			$conditions = $intermediate['where'];
			// Choose the possible shard according to the conditions
			if ($conditions['left']['name'] == 'id') {
				$id = $conditions['right']['value'];
				if ($id > 0 && $id < 10000) {
					return $this->getDI()->get('dbShard1');
				}
				if ($id > 10000) {
					return $this->getDI()->get('dbShard2');
				}
			}
		}
		// Use a default shard
		return $this->getDI()->get('dbShard0');
	}
}
