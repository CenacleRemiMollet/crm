<?php
namespace App\Entity;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class Events
{
    public const CLUB_CREATED = 'CLUB_CREATED';
    public const CLUB_UPDATED = 'CLUB_UPDATED';
    
    public const EVENTS = array(
        self::CLUB_CREATED,
        self::CLUB_UPDATED
    );
    
    public static function create(string $eventName, Account $account, Request $request, $data = null)
    {
        $accountSessionHistory = $request->getSession()->get('AccountSessionHistory');
        if($accountSessionHistory == null) {
            throw new \ErrorException('AccountSessionHistory not found in session');
        }
        
        $eventTrackingHistory = new EventTrackingHistory();
        $eventTrackingHistory->setAccountId($account->getId());
        $eventTrackingHistory->setAccountSessionHistoryId($accountSessionHistory->getId());
        $eventTrackingHistory->setEventName($eventName);
        $eventTrackingHistory->setModifierLogin($account->getLogin());
        $eventTrackingHistory->setModifierName($account->getUser()->getLastname().' '.$account->getUser()->getFirstname());
        $eventTrackingHistory->setData($data);
        return $eventTrackingHistory;
    }
    
    public static function add(ManagerRegistry $manager, string $eventName, Account $account, Request $request, $data = null)
     {
         $event = Events::create($eventName, $account, $request, $data);
         $manager->getManager()->persist($event);
         $manager->getManager()->flush();
     }
}

