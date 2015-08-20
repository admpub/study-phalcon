<?php
namespace CMF\Base\Library;

class MySqlHelper extends SqlHelper {

	static public function randNumber($startNum, $endNum) {
		$startNum = intval($startNum);
		$endNum = intval($endNum);
		return 'FLOOR(' . $startNum . ' + RAND() * (' . $endNum . ' - ' . $startNum . ' + 1))';
	}

	static public function randResult($table, $count = 1, $pkField = 'id') {
		$count = intval($count);
		return 'SELECT * FROM `' . $table . '` AS t1
    JOIN (
        SELECT ROUND(
            RAND() * (
                (SELECT MAX(`' . $pkField . '`) FROM `' . $table . '`)-(SELECT MIN(`' . $pkField . '`) FROM `' . $table . '`)
            )+(SELECT MIN(`' . $pkField . '`) FROM `' . $table . '`)
        ) AS `' . $pkField . '`) AS t2
    WHERE t1.`' . $pkField . '` >= t2.`' . $pkField . '`
    ORDER BY t1.`' . $pkField . '` LIMIT ' . $count;
	}
}