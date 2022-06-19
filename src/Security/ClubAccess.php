<?php
namespace App\Security;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use App\Entity\Club;
use App\Entity\Account;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Argument\ServiceLocator;
use App\Entity\UserClubSubscribe;
use Psr\Log\LoggerInterface;

class ClubAccess
{

    private AuthorizationChecker $authorizationChecker;
    
    private ManagerRegistry $manager;
    
    private LoggerInterface $logger;
    
    public function __construct(ServiceLocator $container)
    {
        $this->authorizationChecker = $container->get('security.authorization_checker');
        $this->manager = $container->get('doctrine');
        $this->logger = $container->get('logger');
    }
    
    public function hasAccessForUser(Club $club, $account)
    {
        if($this->authorizationChecker->isGranted('ROLE_ADMIN') || $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }
        if(($this->authorizationChecker->isGranted('ROLE_CLUB_MANAGER') || $this->authorizationChecker->isGranted('ROLE_TEACHER')) && $account != null) {
            $userClubSubscribe = $this->manager
                ->getRepository(UserClubSubscribe::class)
                ->findBy(['club_id' => $club->getId(), 'user_id' => $account->getUser()->getId()]);
            
            
            //TODO $account
            return true;
        }
        return false;
    }
}

