<?php
namespace CMF\Frontend\Controllers;
use \CMF\Base\Models\MemberAccount;
/**
* description
*
* @author:S.W.H
* @E-mail:swh@admpub.com
* @update:2015/6/10
*/

class IndexController extends ControllerFrontend{
	public function indexAction(){
		#$this->showRunnerInfo();
		$this->response->setContentType('text/html','utf-8')->sendHeaders();
		$result=MemberAccount::find(array('limit'=>10));
		foreach($result as $k=>$v){
			echo $v->uid,':',$v->account_name,'<br/>';
		}

		echo '<h1>-------</h1>';
		$result=MemberAccount::findByAccountName('');
		foreach($result as $k=>$v){
			echo $v->uid,':',$v->account_name,'<br/>';
		}
		#\CMF::dump($result);
		$this->showDbDebug();
		echo '<h1>hello.</h1>';
	}
}