<?php
namespace CMF\Base\Controllers;
/**
* description
*
* @author:S.W.H
* @E-mail:swh@admpub.com
* @update:2015/6/10
* @RoutePrefix("/base")
*/
/*
* @RoutePrefix("/base")
*/
class IndexController extends ControllerBase{

	/**
	 * @Route("/index")
	 */
	public function indexAction(){
		parent::show404Action();
	}
}