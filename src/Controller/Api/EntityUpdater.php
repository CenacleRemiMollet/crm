<?php
namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\Events;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Psr\Log\LoggerInterface;

class EntityUpdater
{

    private ManagerRegistry $manager;
    private Request $request;
    private UserInterface $account;
    private string $eventName;
    private array $updatedFields = array();
    private LoggerInterface $logger;
    
    public function __construct(ManagerRegistry $manager, Request $request, UserInterface $account, string $eventName, LoggerInterface $logger)
    { 
        $this->manager = $manager;
        $this->request = $request;
        $this->account = $account;
        $this->eventName = $eventName;
        $this->logger = $logger;
    }
    
    public function update(string $fieldName, $updateValue, $currentValue, $updator, $updatorToNull = null): bool
    {
        return $this->fieldUpdater($fieldName, $updateValue, $currentValue, $updator, $updatorToNull);
    }
    
    public function toResponse($object, string $logMessage = 'Updated', array $eventData = null): Response
    {
        if(empty($this->updatedFields)) {
            $this->logger->debug('Nothing to update');
            return new Response('Nothing to update', Response::HTTP_NO_CONTENT); // 204
        }
        
        $this->manager->getManager()->persist($object);
        $alldata = $this->updatedFields;
        if($eventData != null) {
            $alldata = array_merge($alldata, $eventData);
        }
        
        Events::add($this->manager, $this->eventName, $this->account, $this->request, $alldata);
        $this->logger->debug($logMessage.': '.json_encode($alldata));
        
        return new Response('Updated', Response::HTTP_NO_CONTENT); // 204
    }
    
    //**************************************************
    
    private function fieldUpdater(string $fieldName, $updateValue, $currentValue, $updator, $updatorToNull = null): bool
    {
        if($updateValue !== null && $currentValue !== $updateValue) {
            $updator($updateValue);
            $this->updatedFields = array_merge($this->updatedFields, array($fieldName => $updateValue));
            return true;
        } else if($updateValue === null) {
            $this->logger->debug('Field \''.$fieldName.'\' is null');
            $updatorToNull(null);
            $this->updatedFields = array_merge($this->updatedFields, array($fieldName => null));
            return true;
        } else if($currentValue === $updateValue) {
            //$this->logger->debug('Field \''.$fieldName.'\' has same value: '.$currentValue.' === '.$updateValue);
        }
        return false;
    }
}

