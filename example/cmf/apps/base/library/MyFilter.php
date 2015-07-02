<?php
namespace CMF\Base\Library;

class MyFilter extends \Phalcon\Filter {
	private $_historyFilters=array();
	public function sanitize($value, $filters, $noRecursive = false){
		if(is_string($filters)){
			if (!isset($_historyFilters[$filters])) {
				$_historyFilters[$filters]=preg_split('/[^\\w!]+/', $filters);
			}
			$filters=$_historyFilters[$filters];
		}
		return parent::sanitize($value, $filters, $noRecursive);
	}
}