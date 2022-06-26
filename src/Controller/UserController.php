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
		$json = json_decode($response->getContent());
		return $this->render('user/users.html.twig', [
		    'users' => $json
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
	    return $this->render('user/user.html.twig', [
	        'user' => json_decode($userResponse->getContent()),
	        'clubs' => json_decode($clubsResponse->getContent()),
	        'roles' => Roles::ROLES
	    ]);
	}
	
	/**
	 * @Route("/user-new", name="web_new_user", methods={"GET"})
	 */
	public function getUserNew(string $club_uuid, Request $request, SessionInterface $session)
	{
	    // TODO grant
// 	    $doctrine = $this->container->get('doctrine');
	    
// 	    $entityFinder = new EntityFinder($doctrine);
// 	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
// 	    $clubAccess = new ClubAccess($this->container, $this->logger);
// 	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
// 	    return $this->render('club/config-price-new.html.twig', [
// 	        'club' => $club
// 	    ]);

	}
	
}
