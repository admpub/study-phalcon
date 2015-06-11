<?php
namespace CMF\Frontend\Controllers;
/**
* description
*
* @author:S.W.H
* @E-mail:swh@admpub.com
* @update:2015/6/10
*/

class IndexController extends ControllerFrontend{
	public function indexAction(){
		$this->showRunnerInfo();
		echo '<h1>hello.</h1>';exit;
	}
}