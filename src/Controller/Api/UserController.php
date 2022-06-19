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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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


class UserController extends AbstractController
{

	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * @Route("/api/user", name="api_user_list-all", methods={"GET"})
	 * @IsGranted("ROLE_TEACHER")
	 * @OA\Get(
	 *     operationId="listAllUsers",
	 *     path="/api/user",
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
	 *             @OA\Schema(
	 *                 type="array",
	 *                 @OA\Items(ref="#/components/schemas/Users")
	 *             )
	 *         )
	 *     )
	 * )
	 */
	public function listAll(Request $request)
	{
		$pager = new Pager($request);

		$account = $this->getUser();
		$data = array();
		if($this->isGranted("ROLE_ADMIN")) {
			$this->logger->debug('List users for ROLE_ADMIN');
			$data = $this->getDoctrine()->getManager()
				->getRepository(User::class)
				->findBy([], [
					'lastname' => 'ASC',
					'firstname' => 'ASC'
				], $pager->getElementByPage() + 1, $pager->getOffset());
		} elseif($this->isGranted("ROLE_TEACHER")) {
			$this->logger->debug('List users for ROLE_TEACHER');
			$data = $this->getDoctrine()->getManager()
				->getRepository(User::class)
				->findInMyClubs($account->getId(), null, $pager->getOffset(), $pager->getElementByPage() + 1);
		} elseif($this->isGranted("ROLE_USER")) {
			$this->logger->debug('List users for ROLE_USER');
			$data = array($account->getUser());
		} else {
			$this->logger->debug('List users for nobody !');
		}

		$datasliced = array_slice($data, 0, $pager->getElementByPage());

		$userviews = array();
		foreach ($datasliced as &$u) {
			array_push($userviews, new UserView($u));
		}
		$pagination = new Pagination($pager->getPage(), $pager->getElementByPage(), count($data) > $pager->getElementByPage());
		$output = new UsersView($pagination, $userviews);
		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($output, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}

	/**
 	 * @Route("/api/user", name="api_user_create-one", methods={"POST"})
	 * @IsGranted("ROLE_CLUB_MANAGER")
	 * @OA\Post(
	 *     operationId="getUser",
	 *     path="/api/user",
	 *     summary="Create an user",
	 *     tags={"User"},
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(name="X-ClientId", in="header",  required=true, @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
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
	public function createOne(Request $request, SerializerInterface $serializer, TranslatorInterface $translator)
	{
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
		$json = json_decode($hateoas->serialize($me, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}
}
