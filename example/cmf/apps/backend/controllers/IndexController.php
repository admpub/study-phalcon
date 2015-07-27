<?php
namespace CMF\Backend\Controllers;
/**
* description
*
* @author:S.W.H
* @E-mail:swh@admpub.com
* @update:2015/6/10
*/
/**
 * @RoutePrefix("/admin")
 */
class IndexController extends ControllerBackend{

	/**
	 * Route("/index")
	 */
	public function indexAction(){
		echo '<h1>hello backend.</h1>';exit;
	}


	public function aAction(){
		echo '<h1>hello backend@a.</h1>';exit;
	}
}