<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Util\DateIntervalUtils;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\ClubPrice;
use App\Entity\Club;
use App\Security\ClubAccess;
use App\Entity\EntityFinder;
use Symfony\Component\HttpFoundation\Response;
use App\Security\Roles;
use App\Service\ClubService;
use Hateoas\HateoasBuilder;

class UserController extends AbstractController
{

    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    
	/**
	 * @Route("/users", name="web_view_users", methods={"GET"})
	 */
    public function getUsers(Request $request, SessionInterface $session)
	{
	    $response = $this->forward('App\Controller\Api\UserController::getUsers', ['request' => $request]);
	    if($response->getStatusCode() != 200) {
	        return new Response(
	            $response->getContent(),
	            $response->getStatusCode(),
	            $response->headers->all());
	    }
	    $json = json_decode($response->getContent());
		$clubAccess = new ClubAccess($this->container, $this->logger);
		$clubs = $clubAccess->getClubsForAccount($this->getUser());
		$clubService = new ClubService($this->container->get('doctrine'));
		$clubViews = $clubService->convertToView($clubs);
		return $this->render('user/users.html.twig', [
		    'users' => $json,
		    'clubs' => $clubViews,
		    'roles' => Roles::ROLES,
		    
		]);
	}

	/**
	 * @Route("/users/{user_uuid}", name="web_view_user", methods={"GET"})
	 */
	public function getAUser(string $user_uuid)
	{
	    $userResponse = $this->forward('App\Controller\Api\UserController::getAUser', ["user_uuid" => $user_uuid]);
	    if($userResponse->getStatusCode() != 200) {
	        return new Response(
	            $userResponse->getContent(),
	            $userResponse->getStatusCode(),
	            $userResponse->headers->all());
	    }
	    $clubsResponse = $this->forward('App\Controller\Api\ClubController::listActive');
	    $account = $this->getUser();
	    $itisme = $account->getUser()->getUuid() !== $user_uuid;
	    return $this->render('user/user.html.twig', [
	        'user' => json_decode($userResponse->getContent()),
	        'clubs' => json_decode($clubsResponse->getContent()),
	        'roles' => Roles::ROLES,
	        'itisme' => $itisme ? 'true' : 'false',
	        'canupdatesubscribes' => ($itisme || $this->isGranted(Roles::ROLE_ADMIN)) ? 'true' : 'false'
	    ]);
	}
	
	/**
	 * @Route("/user-new", name="web_new_user", methods={"GET"})
	 */
	public function getUserNew()
	{
	    if( ! $this->isGranted(Roles::ROLE_ADMIN)
	        && ! $this->isGranted(Roles::ROLE_SUPER_ADMIN)
	        && ! $this->isGranted(Roles::ROLE_CLUB_MANAGER)
	        && ! $this->isGranted(Roles::ROLE_TEACHER)) {
	            throw $this->createAccessDeniedException();
	        }
	        
        $clubsResponse = $this->forward('App\Controller\Api\ClubController::listActive');
	        
	    return $this->render('user/user-new.html.twig', [
	        'clubs' => json_decode($clubsResponse->getContent()),
	        'canupdatesubscribes' => $this->isGranted(Roles::ROLE_ADMIN) ? 'true' : 'false'
	    ]);

	}
	
}
