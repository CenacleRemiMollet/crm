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


class UserController extends AbstractController
{

	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * @Route("/api/users", name="api_get_users", methods={"GET"})
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
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Items(ref="#/components/schemas/Pagination")
	 *         )
	 *     )
	 * )
	 */
	public function getUsers(Request $request): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $pager = new Pager($request);

		$account = $this->getUser();
		$users = array();
		if($this->isGranted(Roles::ROLE_ADMIN)) {
		    $users = $doctrine->getManager()
				->getRepository(User::class)
				->findBy([], [
					'lastname' => 'ASC',
					'firstname' => 'ASC'
				], $pager->getElementByPage() + 1, $pager->getOffset());
		} elseif($this->isGranted(Roles::ROLE_CLUB_MANAGER) || $this->isGranted(Roles::ROLE_TEACHER)) {
		    $users = $doctrine->getManager()
				->getRepository(User::class)
				->findInMyClubs($account->getId(), null, $pager->getOffset(), $pager->getElementByPage() + 1);
		} elseif($account !== null) {
		    $users = array($account->getUser());
		} else {
			throw $this->createAccessDeniedException();
		}
		
		$pagination = new Pagination(
		    $this->generateUrl('api_get_users'),
		    $pager,
		    $users,
		    function($u) {
		      return new UserView($u);
		    },
		    []);
		
		$hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize($pagination, 'json'),
		    Response::HTTP_OK,
		    array('Content-Type' => 'application/hal+json'));
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
	        $hateoas->serialize(new UserView($user), 'json'),
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
	 *             @OA\Schema(ref="#/components/schemas/UserMe")
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
	        $me = new UserMeView($account->getUser(), $grantedRoles);
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

		$output = array('user' => new UserView($user));
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
 	    $entityUpdater->update('address', $userToUpdate->getAddress(), $user->getAddress(), function($v) use($user) { $user->setAddress($v); });
 	    $entityUpdater->update('city', $userToUpdate->getCity(), $user->getCity(), function($v) use($user) { $user->setCity($v); });
 	    $entityUpdater->update('zipcode', $userToUpdate->getZipcode(), $user->getZipcode(), function($v) use($user) { $user->setZipcode($v); });
 	    $entityUpdater->update('phone', $userToUpdate->getPhone(), $user->getPhone(), function($v) use($user) { $user->setPhone($v); });
 	    $entityUpdater->update('phone_emergency', $userToUpdate->getPhoneEmergency(), $user->getPhoneEmergency(), function($v) use($user) { $user->setPhoneEmergency($v); });
 	    $entityUpdater->update('nationality', $userToUpdate->getNationality(), $user->getNationality(), function($v) use($user) { $user->setNationality($v); });
 	    $entityUpdater->update('mails', $userToUpdate->getMails(), $user->getMails(), function($v) use($user) { $user->setMails($v); });
	    return $entityUpdater->toResponse($user, 'User updated', ['id' => $user->getId()]);
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
