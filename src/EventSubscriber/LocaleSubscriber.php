<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
	private $logger;

	private $defaultLocale;

	public function __construct(string $defaultLocale = 'fr', LoggerInterface $logger)
    {
    	$this->defaultLocale = $defaultLocale;
    	$this->logger = $logger;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if( ! $request->hasPreviousSession()) { //  || ( $request->getPathInfo() === "/locale" && $request->getMethod() === "PUT")
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        //$this->logger->debug('LocaleSubscriber: Request.getLocale(): '.$request->getLocale());
        if ($locale = $request->getSession()->get('_locale2')) {
        	$this->logger->debug('LocaleSubscriber set locale from request.session.locale2: "'.$locale.'"');
        	$request->getSession()->set('_locale', $locale);
        	$request->setLocale($locale);
        } else if ($locale = $request->getLocale()) {
        	$this->logger->debug('LocaleSubscriber set locale from request.locale: "'.$locale.'"');
        	$request->getSession()->set('_locale', $locale);
        	$request->setLocale($locale);
//         } elseif ($locale = $request->getSession()->get('_locale')) {
//         	$this->logger->debug('LocaleSubscriber set locale from request.session.locale: "'.$locale.'"');
//         	$request->getSession()->set('_locale', $locale);
//         	$request->setLocale($locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
        	$this->logger->debug('LocaleSubscriber set default locale: "'.$this->defaultLocale.'"');
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
            $request->setLocale($this->defaultLocale);
        }
    }


    public static function getSubscribedEvents()
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
        	KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}