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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\ClubService;

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
    
    public function checkAccessForUser(Club $club, $account)
    {
        $this->hasAccessForUser($club, $account, function($msg) {
            throw new AccessDeniedException($msg);
        });
    }
    
    public function hasAccessForUser(Club $club, $account, ?object $ifDenied = null)
    {
        if($this->authorizationChecker->isGranted(Roles::ROLE_ADMIN) || $this->authorizationChecker->isGranted(Roles::ROLE_SUPER_ADMIN)) {
            $this->logger->debug('ClubAccess.hasAccessForUser(): current user ('.$account->getId().') is an admin');
            return true;
        }
        if(($this->authorizationChecker->isGranted(Roles::ROLE_CLUB_MANAGER) || $this->authorizationChecker->isGranted(Roles::ROLE_TEACHER)) && $account != null) {
            $this->logger->debug('ClubAccess.hasAccessForUser(): current user ('.$account->getId().') is a teacher or a manager');
            $userClubSubscribes = $this->manager
                ->getRepository(UserClubSubscribe::class)
                ->findBy(['club' => $club, 'user' => $account->getUser()]);
            if(! empty($userClubSubscribes)) {
                foreach ($userClubSubscribes as &$userClubSubscribe) {
                    foreach ($userClubSubscribe->getRoles() as &$role) {
                        $granted = $this->authorizationChecker->isGranted($role);
                        $this->logger->debug('ClubAccess.hasAccessForUser(): verify access for current user ('.$account->getId().') in club '.$club->getId().' role '.$role.' => '.($granted ? 'accepted':'reject'));
                        if($granted) {
                            //$this->logger->debug('ClubAccess: access authorized for current user ('.$account->getId().') in club '.$club->getId().' with role '.$role);
                            return true;
                        }
                    }
                }
            }
            if($ifDenied !== null) {
                $msg = 'Access denied for a ';
                if($this->authorizationChecker->isGranted(Roles::ROLE_CLUB_MANAGER) && $this->authorizationChecker->isGranted(Roles::ROLE_TEACHER)) {
                    $msg = $msg.'manager and teacher';
                } elseif($this->authorizationChecker->isGranted(Roles::ROLE_CLUB_MANAGER)) {
                    $msg = $msg.'manager';
                } elseif($this->authorizationChecker->isGranted(Roles::ROLE_TEACHER)) {
                    $msg = $msg.'teacher';
                }
                if($ifDenied !== null) {
                    $ifDenied($msg.' in the club \''.$club->getName().'\'');
                }
            }
            $this->logger->debug('ClubAccess.hasAccessForUser(): access denied for current user ('.$account->getId().') in club '.$club->getId().' ('.$club->getName().')');
            return false;
        }
        if($ifDenied !== null) {
            $ifDenied('Access denied for anonymous');
        }
        $this->logger->debug('ClubAccess.hasAccessForUser(): current user is anonymous');
        return false;
    }
    
    public function getClubsForAccount($account): ?array
    {
        if($this->authorizationChecker->isGranted(Roles::ROLE_ADMIN) || $this->authorizationChecker->isGranted(Roles::ROLE_SUPER_ADMIN)) {
            $this->logger->debug('ClubAccess.getClubsForAccount(): current user ('.$account->getId().') is an admin');
            $clubService = new ClubService( $this->manager);
            return $clubService->getAllActive();
        }
        if(($this->authorizationChecker->isGranted(Roles::ROLE_CLUB_MANAGER) || $this->authorizationChecker->isGranted(Roles::ROLE_TEACHER)) && $account != null) {
            $this->logger->debug('ClubAccess: current user ('.$account->getId().') is a teacher or a manager');
            $clubs = $this->manager
                ->getRepository(Club::class)
                ->findAllForUser($account->getUser()->getUuid());
                $this->logger->debug('ClubAccess.getClubsForAccount(): current user ('.$account->getId().') is a teacher or a manager, count '.count($clubs).' clubs');
            // TODO maybe filter with roles
            return $clubs;
        }
        return [];
    }
    
}

