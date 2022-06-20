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
    
    public function __construct(ServiceLocator $container, LoggerInterface $logger)
    {
        $this->authorizationChecker = $container->get('security.authorization_checker');
        $this->manager = $container->get('doctrine');
        $this->logger = $logger;
    }
    
    public function hasAccessForUser(Club $club, $account)
    {
        if($this->authorizationChecker->isGranted(Roles::ROLE_ADMIN) || $this->authorizationChecker->isGranted(Roles::ROLE_SUPER_ADMIN)) {
            $this->logger->debug('ClubAccess: current user ('.$account->getId().') is an admin');
            return true;
        }
        if(($this->authorizationChecker->isGranted(Roles::ROLE_CLUB_MANAGER) || $this->authorizationChecker->isGranted(Roles::ROLE_TEACHER)) && $account != null) {
            $this->logger->debug('ClubAccess: current user ('.$account->getId().') is a teacher or a manager');
            $userClubSubscribes = $this->manager
                ->getRepository(UserClubSubscribe::class)
                ->findBy(['club' => $club, 'user' => $account->getUser()]);
            if(! empty($userClubSubscribes)) {
                foreach ($userClubSubscribes as &$userClubSubscribe) {
                    foreach ($userClubSubscribe->getRoles() as &$role) {
                        $granted = $this->authorizationChecker->isGranted($role);
                        $this->logger->debug('ClubAccess: verify access for current user ('.$account->getId().') in club '.$club->getId().' role '.$role.' => '.($granted ? 'accepted':'reject'));
                        if($granted) {
                            //$this->logger->debug('ClubAccess: access authorized for current user ('.$account->getId().') in club '.$club->getId().' with role '.$role);
                            return true;
                        }
                    }
                }
            }
            $this->logger->debug('ClubAccess: access denied for current user ('.$account->getId().') in club '.$club->getId());
            return false;
        }
        $this->logger->debug('ClubAccess: current user is anonymous');
        return false;
    }
}

