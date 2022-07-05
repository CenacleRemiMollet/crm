<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Psr\Log\LoggerInterface;
use App\Model\ErrorView;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Hateoas\HateoasBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Exception\ViolationException;
use App\Exception\CRMException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Exception\UnauthorizedTypeUploadException;
use App\Exception\FileTooLargeUploadException;

class ExceptionListener
{

    private $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    
    public function onKernelException(ExceptionEvent $event)
    {
        $request = $event->getRequest();
        if(! str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }
        $exception = $event->getThrowable();

        $errorView = new ErrorView();
        if($exception instanceof NotFoundHttpException) {
            $errorView->setStatus(Response::HTTP_NOT_FOUND);
            $errorView->setMessage($exception->getMessage());
            
        } elseif($exception instanceof AccessDeniedException || $exception instanceof AccessDeniedHttpException) {
            $errorView->setStatus(Response::HTTP_FORBIDDEN);
            $errorView->setMessage($exception->getMessage());
            
        } elseif($exception instanceof ViolationException) {
            $errorView->setStatus(Response::HTTP_BAD_REQUEST);
            $errorView->setMessage($this->joinArrayKV($exception->getErrors()));
            $errorView->setDetails($exception->getErrors());
        
        } elseif($exception instanceof UnauthorizedTypeUploadException) {
            $errorView->setStatus(Response::HTTP_BAD_REQUEST);
            $errorView->setMessage('Unauthorized type');
            $errorView->setDetails(['type' => $exception->getType()]);
            
        } elseif($exception instanceof FileTooLargeUploadException) {
            $errorView->setStatus(Response::HTTP_BAD_REQUEST);
            $errorView->setMessage('File too large: '.$exception->getType());
            $errorView->setDetails(['uploaded' => $exception->getUploaded(), 'max' => $exception->getMax()]);
        
        } elseif($exception instanceof CRMException) {
            $errorView->setStatus($exception->getStatusCode());
            $errorView->setMessage($exception->getMessage());
            $errorView->setDetails($exception->getDetails());
            
        } else {
            return;
        }
        
        $hateoas = HateoasBuilder::create()->build();
        $response = new Response(
            $hateoas->serialize($errorView, 'json'),
            Response::HTTP_OK,
            array('Content-Type' => 'application/hal+json'));
        if ($exception instanceof HttpExceptionInterface) {
            $response->headers->replace($exception->getHeaders());
        }
        $event->setResponse($response);
    }
    
    private static function joinArrayKV($arr) {
       return implode(', ', array_map(
            function ($v, $k) {
                return str_replace('This value', '\''.$k.'\'', $v);
            },
            $arr,
            array_keys($arr)));
    }
}

