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

    protected function forward($uri,$module=null)
    {
        $uriParts = explode('/', $uri);
        $params = array_slice($uriParts, 2);
		$data = array('controller' => $uriParts[0],'action' => $uriParts[1],'params' => $params);
		if($module) $data['namespace'] = 'CMF\\'.ucfirst($module).'\Controllers';
    	return $this->dispatcher->forward($data);
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

	public function showDbDebug(){
		//Get the generated profiles from the profiler
		$profiles = $this->di->get('profiler')->getProfiles();
		$content = '';
		$elapsedTime = 0;
		if($profiles) foreach ($profiles as $profile) {
			$content .= '<span style="color:#ff0">['.sprintf('%5f',$profile->getTotalElapsedSeconds()).']</span> '. $profile->getSQLStatement(). '<br />';
			$elapsedTime+=$profile->getTotalElapsedSeconds();
		}
		echo '<div style="position:fixed;top:0;right:0;padding:3px 15px;background:#000;color:#FFF;box-shadow:1px 1px 5px #555;max-width:80%;font-size:13px" onmouseover="document.getElementById(\'__SqlQueryInfo__\').style.display=\'\';" onmouseout="document.getElementById(\'__SqlQueryInfo__\').style.display=\'none\';">';
		echo '<div>Total Queries: ',count($profiles),' &nbsp; Total Elapsed: ',sprintf('%5f',$elapsedTime),'s</div>';
		echo '<div id="__SqlQueryInfo__" style="display:none;width:100%;word-wrap:break-word;word-break:normal;word-break:break-all;">',$content,'</div>';
		echo '</div>';
	}
}