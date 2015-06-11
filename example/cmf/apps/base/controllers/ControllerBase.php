<?php
namespace CMF\Base\Controllers;
use Phalcon\Mvc\Controller;
/**
* description
*
* @author:S.W.H
* @E-mail:swh@admpub.com
* @update:2015/6/10
*/

class ControllerBase extends Controller{

    protected function initialize()
    {
        //$this->tag->prependTitle('INVO | ');
        //$this->view->setTemplateAfter('main');
    }

    protected function forward($uri)
    {
        $uriParts = explode('/', $uri);
        $params = array_slice($uriParts, 2);
    	return $this->dispatcher->forward(
    		array(
    			'controller' => $uriParts[0],
    			'action' => $uriParts[1],
                'params' => $params
    		)
    	);
    }
	public function loadModuleScript($module){
		if(!class_exists('\CMF\\'.ucfirst($module).'\Module') && file_exists(APPS_PATH . $module . '/Module.php')){
			include(APPS_PATH . $module . '/Module.php');
		}
	}
    public function show404Action(){
		$this->loadModuleScript('base');
		$this->view->setViewsDir(\CMF\Base\Module::getModulePath().'views/');
		$this->view->pick('errors/show404');
    }

    public function show401Action(){
		$this->loadModuleScript('base');
		$this->view->setViewsDir(\CMF\Base\Module::getModulePath().'views/');
		$this->view->pick('errors/show401');
    }

    public function show500Action(){
		$this->loadModuleScript('base');
		$this->view->setViewsDir(\CMF\Base\Module::getModulePath().'views/');
		$this->view->pick('errors/show500');
    }

	public function showRunnerInfo(){
		\CMF::dump(array(
			'Namespace'=>$this->dispatcher->getNamespaceName(),
			'Module'=>$this->dispatcher->getModuleName(),
			'Controller'=>$this->dispatcher->getControllerName(),
			'Action'=>$this->dispatcher->getActionName()
		));
	}
}