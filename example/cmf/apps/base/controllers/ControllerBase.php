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

	public function showDbDebug(){
		//Get the generated profiles from the profiler
		$profiles = $this->di->get('profiler')->getProfiles();
		$content = '';
		$elapsedTime = 0;
		if($profiles) foreach ($profiles as $profile) {
			$content .= 'SQL Statement: '. $profile->getSQLStatement(). "\n";
			$content .= 'Start Time: '. $profile->getInitialTime(). "\n";
			$content .= 'Final Time: '. $profile->getFinalTime(). "\n";
			$content .= 'Elapsed Time: '. $profile->getTotalElapsedSeconds(). "\n";
			$elapsedTime+=$profile->getTotalElapsedSeconds();
		}
		echo '<div style="position:fixed;top:0;right:0;padding:3px 15px;background:#000;color:#FFF;box-shadow:1px 1px 5px #555;" onmouseover="document.getElementById(\'__SqlQueryInfo__\').style.display=\'\';" onmouseout="document.getElementById(\'__SqlQueryInfo__\').style.display=\'none\';">';
		echo '<pre>Total Queries: ',count($profiles),PHP_EOL,'Total Elapsed: ',$elapsedTime,PHP_EOL,'</pre>';
		echo '<pre id="__SqlQueryInfo__" style="display:none">eeeee',$content,'</pre>';
		echo '</div>';
	}
}