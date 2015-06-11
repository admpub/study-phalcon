<?php
namespace CMF\Base\Models;
use Phalcon\Mvc\Model;

//分页功能
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\Model\Query\BuilderInterface;


/**
* description
*
* @author:S.W.H
* @E-mail:swh@admpub.com
* @update:2015/6/11
*/

class ModelBase extends Model{

	//保存之前要执行的操作
	public function beforeSave(){
		//Convert the array into a string
		//$this->status = join(',', $this->status);
	}

	//查询之后要执行的操作
	public function afterFetch(){
		//Convert the string to an array
		//$this->status = explode(',', $this->status);
	}

	/**
	 * 万能分页函数
	 *
	 * @param	mix		$data
	 * @param	number	$limit
	 * @param	number	$currentPage
	 * @return	stdclass对象属性如下：
	 * items 	The set of records to be displayed at the current page
	 * current 	The current page
	 * before 	The previous page to the current one
	 * next 	The next page to the current one
	 * last 	The last page in the set of records
	 * total_pages 	The number of pages
	 * total_items 	The number of items in the source data
	 * @author	AdamShen (swh@admpub.com)
	 * @update	2015/6/11
	 */
	public function pageSplit($data,$limit=10,$currentPage=null){
		if(is_null($currentPage)){
			$currentPage=isset($_REQUEST['page'])?intval($_REQUEST['page']):0;
		}
		if($currentPage<1)$currentPage=1;
		if($data instanceof ResultsetInterface){
			$paginator   = new PaginatorModel(array(
				'data'  => $data,
				'limit' => $limit,
				'page'  => $currentPage
			));
		}elseif($data instanceof BuilderInterface){
			$paginator = new PaginatorQueryBuilder(array(
				'builder' => $data,
				'limit'   => $limit,
				'page'    => $currentPage
			));
		}else{//数组
			$paginator = new PaginatorArray(array(
				'data'  => $data,
				'limit' => $limit,
				'page'  => $currentPage
			));
		}

		// Get the paginated results
		$page = $paginator->getPaginate();
		return $page;
	}

}