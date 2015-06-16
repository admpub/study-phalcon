<?php
namespace CMF\Base\Models;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
// 分页功能
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\Model\Query\BuilderInterface;

/**
 * description
 *
 * @author :S.W.H
 * @E -mail:swh@admpub.com
 * @update :2015/6/11
 */

class ModelBase extends Model {

	public function initialize() {
		// 以下为主从分离范例
		// $this->setReadConnectionService('dbSlave');
		// $this->setWriteConnectionService('dbMaster');
	}

	// 表前缀
	public function getSource() {
		return \CMF :: $config -> database -> prefix . parent :: getSource();
	}

	public function sqlRead($sql, $params=array()) {
		// Execute the query
		return new Resultset(null, $this, $this -> getReadConnection() -> query($sql, $params));
	}

	public function sqlWrite($sql, $params=array()) {
		// Execute the query
		return new Resultset(null, $this, $this -> getWriteConnection() -> query($sql, $params));
	}

	// 保存之前要执行的操作
	public function beforeSave() {
		// Convert the array into a string
		// $this->status = join(',', $this->status);
	}

	// 查询之后要执行的操作
	public function afterFetch() {
		// Convert the string to an array
		// $this->status = explode(',', $this->status);
	}

	// 开始事务
	public function begin() {
		$this -> db -> begin();
	}

	// 结束事务
	public function end($isOk = true) {
		if ($isOk) {
			$this -> db -> commit();
		} else {
			$this -> db -> rollback();
		}
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
			$currentPage = isset($_REQUEST['page'])?intval($_REQUEST['page']):0;
		}
		if ($currentPage < 1)$currentPage = 1;
		if ($data instanceof ResultsetInterface) {
			$paginator = new PaginatorModel(array('data' => $data,
					'limit' => $limit,
					'page' => $currentPage
					));
		} elseif ($data instanceof BuilderInterface) {
			$paginator = new PaginatorQueryBuilder(array('builder' => $data,
					'limit' => $limit,
					'page' => $currentPage
					));
		} else { // 数组
			$paginator = new PaginatorArray(array('data' => $data,
					'limit' => $limit,
					'page' => $currentPage
					));
		}
		// Get the paginated results
		$page = $paginator -> getPaginate();
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
		return $this -> getDI() -> get('db');
		// 以下是范例
		// Check if there is a 'where' clause in the select
		if (isset($intermediate['where'])) {
			$conditions = $intermediate['where'];
			// Choose the possible shard according to the conditions
			if ($conditions['left']['name'] == 'id') {
				$id = $conditions['right']['value'];
				if ($id > 0 && $id < 10000) {
					return $this -> getDI() -> get('dbShard1');
				}
				if ($id > 10000) {
					return $this -> getDI() -> get('dbShard2');
				}
			}
		}
		// Use a default shard
		return $this -> getDI() -> get('dbShard0');
	}
}
