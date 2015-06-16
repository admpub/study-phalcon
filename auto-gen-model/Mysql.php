<?php
namespace Phalcon\Builder;

use Phalcon\Db\Adapter\Pdo\Mysql as MysqlBase;
use Phalcon\Db\Column;
/**
* 重写Phalcon的Mysql类，修复提示“Column type does not support scale parameter”问题
*
* @author:S.W.H
* @E-mail:swh@admpub.com
* @update:2015/6/16
*/

class Mysql extends MysqlBase{

	public function describeColumns($table, $schema = null)
    {
        if (is_string($table) === false ||
            (is_string($schema) === false &&
                is_null($schema) === false)) {
            throw new Exception('Invalid parameter type.');
        }
        $dialect = $this->_dialect;
        //Get the SQL to describe a table
        $sql = $dialect->describeColumns($table, $schema);
        //Get the describe
        $describe = $this->fetchAll($sql, 3);
        $oldColumn = null;
        $columns = array();
        //Field Indexes: 0 - Name, 1 - Type, 2 - Not Null, 3 - Key, 4 - Default, 5 - Extra
        foreach ($describe as $field) {
            //By default the bind type is two
            $definition = array('bindType' => Column::BIND_PARAM_STR);
            //By checking every column type we convert it to a Phalcon\Db\Column
            $columnType = $field[1];
            //Check the column type to get the current Phalcon type
            while (true) {
                //Point are varchars
                if (strpos($columnType, 'point') !== false) {
                    $definition['type'] = Column::TYPE_VARCHAR;
                    break;
                }
                //Enum are treated as char
                if (strpos($columnType, 'enum') !== false) {
                    $definition['type'] = Column::TYPE_CHAR;
                    break;
                }
                //Tinyint(1) is boolean
                if (strpos($columnType, 'tinyint(1)') !== false) {
                    $definition['type'] = Column::TYPE_BOOLEAN;
                    $definition['bindType'] = Column::BIND_PARAM_BOOL;
                    $columnType = 'boolean';
                    break;
                }
                //Smallint/Bigint/Integer/Int are int
                if (strpos($columnType, 'int') !== false) {
                    $definition['type'] = Column::TYPE_INTEGER;
                    $definition['isNumeric'] = true;
                    $definition['bindType'] = Column::BIND_PARAM_INT;
                    break;
                }
                //Varchar are varchars
                if (strpos($columnType, 'varchar') !== false) {
                    $definition['type'] = Column::TYPE_VARCHAR;
                    break;
                }
                //Special type for datetime
                if (strpos($columnType, 'datetime') !== false) {
                    $definition['type'] = Column::TYPE_DATETIME;
                    break;
                }
                //Decimals are floats
                if (strpos($columnType, 'decimal') !== false) {
                    $definition['type'] = Column::TYPE_DECIMAL;
                    $definition['isNumeric'] = true;
                    $definition['bindType'] = Column::BIND_PARAM_DECIMAL;
                    break;
                }
                //Chars are chars
                if (strpos($columnType, 'char') !== false) {
                    $definition['type'] = Column::TYPE_CHAR;
                    break;
                }
                //Date/Datetime are varchars
                if (strpos($columnType, 'date') !== false) {
                    $definition['type'] = Column::TYPE_DATE;
                    break;
                }
                //Timestamp as date
                if (strpos($columnType, 'timstamp') !== false) {
                    $definition['type'] = Column::TYPE_DATE;
                    break;
                }
                //Text are varchars
                if (strpos($columnType, 'text') !== false) {
                    $definition['type'] = Column::TYPE_TEXT;
                    break;
                }
                //Floats/Smallfloats/Decimals are float
                if (strpos($columnType, 'float') !== false) {
                    $definition['type'] = Column::TYPE_FLOAT;
                    $definition['isNumeric'] = true;
                    $definition['bindType'] = Column::BIND_PARAM_DECIMAL;
                    break;
                }
                //Doubles are floats
                if (strpos($columnType, 'double') !== false) {
                    $definition['type'] = Column::TYPE_DECIMAL;
                    $definition['isNumeric'] = true;
                    $definition['bindType'] = Column::BIND_PARAM_DECIMAL;
                    break;
                }
                //By default: String
                $definition['type'] = Column::TYPE_VARCHAR;
                break;
            }
            //If the column type has a parentheses we try to get the column size from it
            if (strpos($columnType, '(') !== false) {
                $matches = null;
                $pos = preg_match("#\\(([0-9]++)(?:,\\s*([0-9]++))?\\)#", $columnType, $matches);
                if ($pos == true) {
                    if (isset($matches[1]) === true) {
                        $definition['size'] = $matches[1];
                    }
                    if (isset($matches[2]) === true) {
                        $definition['scale'] = $matches[2];
                    }
                }
            }
            //Check if the column is unsigned, only MySQL supports this
            if (strpos($columnType, 'unsigned') !== false) {
                $definition['unsigned'] = true;
            }
            //Positions
            if ($oldColumn != true) {
                $definition['first'] = true;
            } else {
                $definition['after'] = $oldColumn;
            }
            //Check if the field is primary key
            if ($field[3] === 'PRI') {
                $definition['primary'] = true;
            }
            //Check if the column allows null values
            if ($field[2] == 'NO') {
                $definition['notNull'] = true;
            }
            //Check if the column is auto increment
            if ($field[5] === 'auto_increment') {
                $definition['autoIncrement'] = true;
            }
			/**
			 * Check if the column is default values
			 */
			if ($field[4] != 'null') {
				$definition['default'] = $field[4];
			}
			if(isset($definition['scale'])){//小数
				if ($definition['type'] == Column::TYPE_INTEGER || $definition['type'] == Column::TYPE_FLOAT || $definition['type'] == Column::TYPE_DECIMAL || $definition['type'] == Column::TYPE_DOUBLE) {
				} else {
					switch($definition['bindType']){
						case Column::BIND_PARAM_DECIMAL:
							$definition['type'] = Column::TYPE_DECIMAL;
							break;
						default:
							break;
					}
				}
			}
            $column = new Column($field[0], $definition);
            $columns[] = $column;
            $oldColumn = $field[0];
        }
        return $columns;
    }
}