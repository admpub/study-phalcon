<?php
//require('phar://phpunit.phar');
//var_dump(class_exists('PHPUnit_Framework_TestCase'));
/**
* description
*
* @author:S.W.H
* @E-mail:swh@admpub.com
* @update:2015/7/6
*/

class TestCase extends PHPUnit_Framework_TestCase{


	public function testAdd(){
		$arr=array();
		$this->assertEquals(0,count($arr));
		array_push($arr,'one');
		$this->assertEquals(1,count($arr));
	}

	/**
	 * 默认会执行以“test”作为前缀的方法。也可以像下面这样在注释中添加“@test”来测试一个非“test”前缀的方法。
	 * @test
	 */
	public function isOk(){
		$this->assertEquals('1','1');
	}

}