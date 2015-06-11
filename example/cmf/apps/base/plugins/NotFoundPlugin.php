<?php
namespace CMF\Base\Plugins;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;

/**
 * NotFoundPlugin
 *
 * Handles not-found controller/actions
 */
class NotFoundPlugin extends Plugin
{

	/**
	 * This action is executed before execute any action in the application
	 *
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 */
	public function beforeException(Event $event, MvcDispatcher $dispatcher, \Exception $exception)
	{
		if ($exception instanceof DispatcherException) {
			switch ($exception->getCode()) {
				case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
				case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
					#$dispatcher->setDefaultNamespace('CMF\Base\Controllers');
					$dispatcher->forward(array(
						'namespace' => 'CMF\Base\Controllers',
						'controller' => 'index',
						'action' => 'show404'
					));#\CMF::dump($dispatcher->getHandlerClass(),1);
					return false;
			}
		}

		$dispatcher->forward(array(
			'namespace' => 'CMF\Base\Controllers',
			'controller' => 'index',
			'action'     => 'show500'
		));
		return false;
	}
}
