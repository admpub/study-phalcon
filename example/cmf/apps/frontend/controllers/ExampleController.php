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

class ExampleController extends ControllerFrontend{
	public function indexAction(){
		#$this->showRunnerInfo();
		$this->response->setContentType('text/html','utf-8')->sendHeaders();
		\CMF::getClassInfo($this);
		//可访问的属性：
		//$this->di->...
		//$this-><服务名>->...
		//$this->persistent->...(使用$this->persistent->xxx，只能在同一控制器中的不同Action中进行访问，不能在其他控制器中访问到数据。)

		/*//数据查询：
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
		// */
		$this->showDbDebug();
		echo '<h1>hello.</h1>';
	}

	public function i18nAction(){
		$this->response->setContentType('text/html','utf-8')->sendHeaders();
		\CMF::getClassInfo('Locale');
		$locale = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		// Locale could be something like "en_GB" or "en"
		echo $locale,'<br/>';
		$formatter = new \MessageFormatter($locale, '€ {0, number, integer}');
		echo $formatter->format(array(4560.5)),'<br/>';
	}
}