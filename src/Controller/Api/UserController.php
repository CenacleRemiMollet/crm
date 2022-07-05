<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Exception\ViolationException;
use App\Model\UserCreate;
use App\Model\UserView;
use App\Service\UserService;
use App\Util\RequestUtil;
use Hateoas\HateoasBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use primus852\ShortResponse\ShortResponse;
use OpenApi\Annotations as OA;
use App\Model\UsersView;
use App\Model\Pagination;
use App\Util\Pager;
use App\Model\UserMeView;
use App\Model\MeAnonymousView;
use App\Security\Roles;
use App\Entity\EntityFinder;
use App\Model\UserUpdate;
use App\Entity\Events;
use App\Entity\Account;
use App\Model\AccountView;
use App\Entity\UserClubSubscribe;
use App\Util\StringUtils;
use App\Entity\Club;
use App\Model\UserClubSubscribeUpdate;
use App\Security\ClubAccess;


class UserController extends AbstractController
{

	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	
	/**
	 * @Route("/api/users", name="api_get_users", methods={"GET"}, format="text/plain")
	 * @OA\Get(
	 *     operationId="getUsers",
	 *     path="/api/users",
	 *     summary="List of users",
	 *     tags={"User"},
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(
     *         description="page number",
     *         in="query",
     *         name="page",
     *         required=false,
     *         @OA\Schema(
     *             format="string",
     *             type="string"
     *         )
     *     ),
	 *     @OA\Parameter(
     *         description="max number of result in a page",
     *         in="query",
     *         name="n",
     *         required=false,
     *         @OA\Schema(
     *             format="string",
     *             type="string"
     *         )
     *     ),
	 *     @OA\Parameter(
     *         description="pattern filter",
     *         in="query",
     *         name="q",
     *         required=false,
     *         @OA\Schema(
     *             format="string",
     *             type="string"
     *         )
     *     ),
	 *     @OA\Parameter(
     *         description="club filter",
     *         in="query",
     *         name="club",
     *         required=false,
     *         @OA\Schema(
     *             format="string",
     *             type="string",
     *             pattern="[a-z0-9_]{2,64}"
     *         )
     *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Items(ref="#/components/schemas/Pagination")
	 *         ),
	 *         @OA\MediaType(mediaType="text/csv")
	 *         )
	 *     ),
	 *     @OA\Response(response="404", description="Club not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function getUsers(Request $request): Response
	{
	    $accept = $request->headers->get('accept');
	    if('text/csv' === $accept) {
	        return $this->getUsersCSV($request);
	    }
	    
	    $doctrine = $this->container->get('doctrine');
	    
	    $pager = new Pager($request);
	    $q = $request->query->get('q');
	    $club_uuid = $request->query->get('club');

		$account = $this->getUser();
		$users = array();
		if($this->isGranted(Roles::ROLE_ADMIN)) {
		    $users = $doctrine->getManager()
				->getRepository(User::class)
				->findInAll(null, $club_uuid, $q, $pager->getOffset(), $pager->getElementByPage() + 2);
		} elseif($this->isGranted(Roles::ROLE_CLUB_MANAGER) || $this->isGranted(Roles::ROLE_TEACHER)) {
		    if($club_uuid !== null) {
		        $entityFinder = new EntityFinder($doctrine);
		        $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]);
                $clubAccess = new ClubAccess($this->container, $this->logger);
                if( ! $clubAccess->hasAccessForUser($club, $account)) {
                    throw $this->createAccessDeniedException();
                }
                
		    }
		    $users = $doctrine->getManager()
				->getRepository(User::class)
				->findInMyClubs($account->getId(), null, $club_uuid, $q, $pager->getOffset(), $pager->getElementByPage() + 1);
		} elseif($account !== null) {
		    $users = array($account->getUser());
		} else {
			throw $this->createAccessDeniedException();
		}
		//$this->logger->debug('count('.count($users).') > pager.getElementByPage('.$pager->getElementByPage().')');
		
		$queryParameters = [];
		if($club_uuid != null) {
		    $queryParameters['club'] = $club_uuid;
		}
		$pagination = new Pagination(
		    $this->generateUrl('api_get_users'),
		    $pager,
		    $users,
		    function($u) {
		        return new UserView($u, $this->getUser());
		    },
		    $queryParameters);
		
		$hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize($pagination, 'json'),
		    Response::HTTP_OK,
		    array('Content-Type' => 'application/hal+json'));
	}

	
	public function getUsersCSV(Request $request): Response
	{
	    $doctrine = $this->container->get('doctrine');
	   
	    $q = $request->query->get('q');
	    $delimiter = $request->query->get('delimiter', ';');
	    $club_uuid = $request->query->get('club');
	    
	    $account = $this->getUser();
	    $users = array();
	    if($this->isGranted(Roles::ROLE_ADMIN)) {
	        $users = $doctrine->getManager()
	        ->getRepository(User::class)
	        ->findInAll(null, $club_uuid, $q);
	    } elseif($this->isGranted(Roles::ROLE_CLUB_MANAGER) || $this->isGranted(Roles::ROLE_TEACHER)) {
	        if($club_uuid !== null) {
	            $entityFinder = new EntityFinder($doctrine);
	            $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]);
	            $clubAccess = new ClubAccess($this->container, $this->logger);
	            if( ! $clubAccess->hasAccessForUser($club, $account)) {
	                throw $this->createAccessDeniedException();
	            }
	            
	        }
	        $users = $doctrine->getManager()
	           ->getRepository(User::class)
	           ->findInMyClubs($account->getId(), null, $club_uuid, $q);
	    } elseif($account !== null) {
	        $users = array($account->getUser());
	    } else {
	        throw $this->createAccessDeniedException();
	    }
	    
	    $f = fopen('php://output', 'w');
	    fputcsv($f, array('UUID', 'Nom', 'Prénom', 'Sexe', 'Né(e) le', 'Adresse', 'Code postal', 'Ville', 'Nationalité', 'Emails', 'Tel', 'Tel accident'), $delimiter);
	    foreach ($users as $user) {
	        /** @var User $user */
	        fputcsv(
	            $f,
	            array(
	                $user->getUuid(),
	                $user->getLastname(),
	                $user->getFirstname(),
	                $user->getSex(),
	                $user->getBirthday()->format("d/m/Y"),
	                $user->getAddress(),
	                $user->getZipcode(),
	                $user->getCity(),
	                $user->getNationality(),
	                implode(', ', $user->getMails()),
	                $user->getPhone(),
	                $user->getPhoneEmergency()
	            ),
	            $delimiter);
	    }
	    $response = new Response();
	    $response->headers->set('Content-Type', 'text/csv');
	    $response->headers->set('Content-Disposition', 'attachment; filename="users.csv"');
	    return $response;
	}
	
	
	/**
	 * @Route("/api/users/{user_uuid}", name="api_get_user", methods={"GET"})
	 * @OA\Get(
	 *     operationId="getUser",
	 *     path="/api/users/{user_uuid}",
	 *     summary="Get a user",
	 *     tags={"User"},
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(
	 *         description="UUID of user",
	 *         in="path",
	 *         name="user_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[a-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Items(ref="#/components/schemas/User")
	 *         )
	 *     ),
	 *     @OA\Response(response="404", description="User not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function getAUser(string $user_uuid): Response
	{
	    $user = $this->findUserOrAccessDenied($user_uuid);
	    $hateoas = HateoasBuilder::create()->build();
	    return new Response(
	        $hateoas->serialize(new UserView($user, $this->getUser(), true), 'json'),
	        Response::HTTP_OK,
	        array('Content-Type' => 'application/hal+json'));
	}
	
	
	/**
	 * @Route("/api/user/me", name="api_user_me", methods={"GET"})
	 * @OA\Get(
	 *     operationId="getUserMe",
	 *     path="/api/user/me",
	 *     summary="Gives informations about me",
	 *     tags={"User"},
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Schema(ref="#/components/schemas/User")
	 *         )
	 *     )
	 * )
	 */
	public function me()
	{
	    $grantedRoles = array();
	    foreach (Roles::ROLES as &$role) {
	        if($this->isGranted($role)) {
	            array_push($grantedRoles, $role);
	        }
	    }
	    
	    $account = $this->getUser();
	    if($account) {
	        $me = new UserView($account->getUser(), $this->getUser());
	    } else {
	        $me = new MeAnonymousView($grantedRoles);
	    }
	    
	    $hateoas = HateoasBuilder::create()->build();
	    return new Response(
	        $hateoas->serialize($me, 'json'),
	        Response::HTTP_OK,
	        array('Content-Type' => 'application/hal+json'));
	}
	
	
    /**
 	 * @Route("/api/users", name="api_create_user", methods={"POST"})
	 * @OA\Post(
	 *     operationId="createUser",
	 *     path="/api/users",
	 *     summary="Create an user",
	 *     tags={"User"},
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(name="X-ClientId", in="header", required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
     *     @OA\RequestBody(
     *         description="User object that needs to be added",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserCreate"),
     *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Schema(ref="#/components/schemas/User")
	 *         )
	 *     )
	 * )
	 */
	public function createUser(Request $request, SerializerInterface $serializer, TranslatorInterface $translator)
	{
	    //@IsGranted("ROLE_CLUB_MANAGER")
	    $requestUtil = new RequestUtil($serializer, $translator);

		try {
			$userCreate = $requestUtil->validate($request, UserCreate::class);
		} catch (ViolationException $e) {
			return ShortResponse::error("data", $e->getErrors())
				->setStatusCode(Response::HTTP_BAD_REQUEST);
		}

		try {
			$service = new UserService($this->getDoctrine()->getManager(), $request);
		} catch (\Exception $e) {
			return ShortResponse::exception('Initialization failed, '.$e->getMessage());
		}

		try {
			$user = $service->create($this->getUser(), $userCreate);
		} catch (\Exception $e) {
			return ShortResponse::exception('Query failed, please try again shortly ('.$e->getMessage().')');
		}

		$output = array('user' => new UserView($user, $this->getUser()));
		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($output, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}

	
	/**
	 * @Route("/api/users/{user_uuid}", name="api_update_user", methods={"PATCH"}, requirements={"user_uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Patch(
	 *     operationId="updateUser",
	 *     tags={"User"},
	 *     path="/api/users/{user_uuid}",
	 *     summary="Update an user",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(name="X-ClientId", in="header", required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
	 *     @OA\Parameter(
	 *         description="UUID of user",
	 *         in="path",
	 *         name="user_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[a-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\RequestBody(
	 *         description="Location object that needs to be added",
	 *         required=true,
	 *         @OA\JsonContent(ref="#/components/schemas/UserUpdate"),
	 *     ),
	 *     @OA\Response(response="204", description="Successful"),
	 *     @OA\Response(response="400", description="Request contains not valid field", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="403", description="Forbidden to update an user", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="404", description="User not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function updateUser(string $user_uuid, Request $request, SerializerInterface $serializer, TranslatorInterface $translator): Response
	{
	    $doctrine = $this->container->get('doctrine');
    
	    $requestUtil = new RequestUtil($serializer, $translator);
	    $userToUpdate = $requestUtil->validate($request, UserUpdate::class); // 400

	    $user = $this->findUserOrAccessDenied($user_uuid);
	    
	    $entityUpdater = new EntityUpdater($doctrine, $request, $this->getUser(), Events::USER_UPDATED, $this->logger);
	    $entityUpdater->update('lastname', $userToUpdate->getLastname(), $user->getLastname(), function($v) use($user) { $user->setName($v); });
	    $entityUpdater->update('firstname', $userToUpdate->getFirstname(), $user->getFirstname(), function($v) use($user) { $user->setFirstname($v); });
	    $entityUpdater->update('sex', $userToUpdate->getSex(), $user->getSex(), function($v) use($user) { $user->setSex($v); });
	    $entityUpdater->update('birthday', $userToUpdate->getBirthdayDateTime(), $user->getBirthday(), function($v) use($user) { $user->setBirthday($v); });
 	    $entityUpdater->update('address', $userToUpdate->getAddress(), $user->getAddress(), function($v) use($user) { $user->setAddress($v); });
 	    $entityUpdater->update('city', $userToUpdate->getCity(), $user->getCity(), function($v) use($user) { $user->setCity($v); });
 	    $entityUpdater->update('zipcode', $userToUpdate->getZipcode(), $user->getZipcode(), function($v) use($user) { $user->setZipcode($v); });
 	    $entityUpdater->update('phone', $userToUpdate->getPhone(), $user->getPhone(), function($v) use($user) { $user->setPhone($v); });
 	    $entityUpdater->update('phone_emergency', $userToUpdate->getPhoneEmergency(), $user->getPhoneEmergency(), function($v) use($user) { $user->setPhoneEmergency($v); });
 	    $entityUpdater->update('nationality', $userToUpdate->getNationality(), $user->getNationality(), function($v) use($user) { $user->setNationality($v); });
 	    $entityUpdater->update('mails', $userToUpdate->getMails(), $user->getMails(), function($v) use($user) { $user->setMails($v); });
 	    
 	    $currentAccount = $this->getUser();
 	    if($currentAccount->getUser()->getId() !== $user->getId() || $this->isGranted(Roles::ROLE_ADMIN)) { // can't update myself except admin
            $this->updateSubscribes($user, $userToUpdate->getSubscribes(), $entityUpdater);
            $entityFinder = new EntityFinder($doctrine);
            $account = $entityFinder->findOneByOrThrow(Account::class, ['user' => $user]);
            if($entityUpdater->update('roles', $userToUpdate->getRoles(), $account->getRoles(), function($v) use($account) { $account->setRoles($v); })) {
                $doctrine->getManager()->persist($account);
 	        }
 	    }
 	    return $entityUpdater->toResponse($user, 'User updated', ['id' => $user->getId()]);
	}
	
	
	private function updateSubscribes(User $user, $subscribes, EntityUpdater $entityUpdater)
	{
	    if(empty($subscribes)) {
	        return;
	    }
	    $doctrine = $this->container->get('doctrine');
	    $entityFinder = new EntityFinder($doctrine);
	    
	    $subscMap = array();
	    foreach($subscribes as &$subscribe) { // map from request
	        $uuid = $subscribe->getUuid();
	        if(empty($uuid) || strlen(trim($uuid)) < 4) {
	            $uuid = StringUtils::random_str(16);
	        }
	        $subscMap[$uuid] = $subscribe;
	    }
	    
	    foreach($user->getUserClubSubscribes() as &$userClubSubscribe) {
	        $this->logger->debug('Update user : '.$userClubSubscribe->getUuid());
	        if( ! array_key_exists($userClubSubscribe->getUuid(), $subscMap)) { // to delete
	            $this->logger->debug('Update user : remove UserClubSubscribe('.$userClubSubscribe->getId().')');
	            $user->removeUserClubSubscribe($userClubSubscribe);
	            continue;
	        }
	        $userSubscUpdate = $subscMap[$userClubSubscribe->getUuid()];
	        unset($subscMap[$userClubSubscribe->getUuid()]);
	        $this->updateSubscribeEntity($entityFinder, $entityUpdater, $userClubSubscribe, $userSubscUpdate);
	    }
	    $this->logger->debug('Update user : add '.count($subscMap).' UserClubSubscribe');
	    foreach($subscMap as $uuid => $userClubSubscribeUpdate) {
	        $this->logger->debug('Update user : add UserClubSubscribe '.$uuid);
	        $userClubSubscribe = new UserClubSubscribe();
	        $userClubSubscribe->setUuid($uuid);
	        $this->updateSubscribeEntity($entityFinder, $entityUpdater, $userClubSubscribe, $userClubSubscribeUpdate);
	        $user->addUserClubSubscribe($userClubSubscribe);
	    }
	}
	
	
	private function updateSubscribeEntity(EntityFinder $entityFinder, EntityUpdater $entityUpdater, UserClubSubscribe $userClubSubscribe, UserClubSubscribeUpdate $userClubSubscribeUpdate)
	{
	    $entityUpdater->update(
	        'subsc-'.$userClubSubscribe->getUuid().'-club',
	        $userClubSubscribeUpdate->getClubUuid(),
	        $userClubSubscribe->getClub() !== null ? $userClubSubscribe->getClub()->getUuid() : null,
	        function($v) use($userClubSubscribe, $entityFinder, $userClubSubscribeUpdate) {
	            $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $userClubSubscribeUpdate->getClubUuid()]); // 404
	            $userClubSubscribe->setClub($club);
	        });
	    $entityUpdater->update(
	        'subsc-'.$userClubSubscribe->getUuid().'-roles',
	        $userClubSubscribeUpdate->getRoles(),
	        $userClubSubscribe->getRoles(),
	        function($v) use($userClubSubscribe) { $userClubSubscribe->setRoles($v); });
	}
	
	
	private function findUserOrAccessDenied($user_uuid): User
	{
	    $account = $this->getUser();
	    if($this->isGranted(Roles::ROLE_ADMIN)) {
	        $doctrine = $this->container->get('doctrine');
	        $entityFinder = new EntityFinder($doctrine);
	        return $entityFinder->findOneByOrThrow(User::class, ['uuid' => $user_uuid]); // 404
	    } elseif($this->isGranted(Roles::ROLE_CLUB_MANAGER) || $this->isGranted(Roles::ROLE_TEACHER)) {
	        $doctrine = $this->container->get('doctrine');
	        $users = $doctrine->getManager()
    	        ->getRepository(User::class)
    	        ->findInMyClubs($account->getId(), $user_uuid);
	        if( ! empty($users)) {
	            return $users[0];
	        }
	    } elseif($account !== null && $account->getUser()->getUuid() === $user_uuid) {
	        return $account->getUser();
	    } else {
	        throw $this->createAccessDeniedException();
	    }
	    throw $this->createNotFoundException('User');
	}
}
