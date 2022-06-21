<?php
namespace App\Entity;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class Events
{
    public const CLUB_CREATED = 'CLUB_CREATED';
    public const CLUB_UPDATED = 'CLUB_UPDATED';
    
    public const CLUB_LESSON_CREATED = 'CLUB_LESSON_CREATED';
    public const CLUB_LESSON_UPDATED = 'CLUB_LESSON_UPDATED';
    public const CLUB_LESSON_DELETED = 'CLUB_LESSON_DELETED';
    
    public const CLUB_LOCATION_CREATED = 'CLUB_LOCATION_CREATED';
    public const CLUB_LOCATION_UPDATED = 'CLUB_LOCATION_UPDATED';
    public const CLUB_LOCATION_DELETED = 'CLUB_LOCATION_DELETED';
    
    public const CLUB_PRICE_CREATED = 'CLUB_PRICE_CREATED';
    public const CLUB_PRICE_UPDATED = 'CLUB_PRICE_UPDATED';
    public const CLUB_PRICE_DELETED = 'CLUB_PRICE_DELETED';
    
    public const CONFIG_PROPERTIES_SAVED = 'CONFIG_PROPERTIES_SAVED';
    
    
    public const EVENTS = array(
        self::CLUB_CREATED,
        self::CLUB_UPDATED,
        
        self::CLUB_LESSON_CREATED,
        self::CLUB_LESSON_UPDATED,
        self::CLUB_LESSON_DELETED,
        
        self::CLUB_LOCATION_CREATED,
        self::CLUB_LOCATION_UPDATED,
        self::CLUB_LOCATION_DELETED,
        
        self::CLUB_PRICE_CREATED,
        self::CLUB_PRICE_UPDATED,
        self::CLUB_PRICE_DELETED,
        
        self::CONFIG_PROPERTIES_SAVED
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

