<?php

namespace App\EventSubscriber;

use App\Controller\TokenAuthenticatedController;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class XClientIdEventSubscribe implements EventSubscriberInterface
{

	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	public function onKernelRequest(RequestEvent $event)
	{
		$request = $event->getRequest();
		$method = strtoupper($request->getMethod());
		if(in_array($method, array("GET", "HEAD", "OPTIONS", "TRACE", "CONNECT"))) {
			return;
		}
		if('/login' == $request->getPathInfo() && 'POST' == $request->getMethod()) {
		    return;
		}
		$clientId = $request->headers->get('x-clientid');
		if(empty($clientId)) {
		    $this->logger->warning('X-ClientId not defined: '.$request->getMethod().' '.$request->getPathInfo());
		    throw new AccessDeniedHttpException('Modification forbidden (x-cli...)');
		}
	}

	public static function getSubscribedEvents()
	{
		return [
			KernelEvents::REQUEST => 'onKernelRequest'
		];
	}
}