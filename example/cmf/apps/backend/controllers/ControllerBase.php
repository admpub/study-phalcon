<?php
namespace CMF\Backend\Controllers;
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
}