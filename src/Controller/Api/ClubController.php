<?php

namespace App\Controller\Api;

use App\Entity\Club;
use Hateoas\HateoasBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use App\Model\ClubCreate;
use App\Service\ClubService;
use App\Util\RequestUtil;
use App\Exception\ViolationException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Model\ClubView;
use App\Util\StringUtils;
use App\Entity\Events;
use App\Model\ClubUpdate;
use App\Security\Roles;
use App\Entity\EntityFinder;
use App\Exception\CRMException;


class ClubController extends AbstractController
{

    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
   
	/**
	 * @Route("/api/club", name="api_club_list-active", methods={"GET"})
	 * @OA\Get(
	 *     operationId="getActiveClubList",
	 *     tags={"Club"},
	 *     path="/api/club",
	 *     summary="List all active clubs",
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Schema(
	 *                 type="array",
	 *                 @OA\Items(ref="#/components/schemas/Club")
	 *             )
	 *         )
	 *     )
	 * )
	 */
	public function listActive()
	{
	    $clubService = new ClubService($this->container->get('doctrine'));
	    $clubViews = $clubService->convertAllActiveToView();
	    $hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize($clubViews, 'json'),
		    Response::HTTP_OK,
		    array('Content-Type' => 'application/hal+json'));
	}

	/**
	 * @Route("/api/club/{uuid}", name="api_get_club", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Get(
	 *     operationId="getClub",
	 *     tags={"Club"},
	 *     path="/api/club/{uuid}",
	 *     summary="Give a club",
	 *     @OA\Parameter(
     *         description="UUID of club",
     *         in="path",
     *         name="uuid",
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
	 *             @OA\Schema(ref="#/components/schemas/Club")
	 *         )
	 *     ),
	 *     @OA\Response(response="404", description="Club not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function one($uuid)
	{
		$entityFinder = new EntityFinder($this->container->get('doctrine'));
		$club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $uuid]); // 404
		
		$clubService = new ClubService($this->container->get('doctrine'));
		$clubViews = $clubService->convertToView($club);
		$clubView = $clubViews[0];
		
		$hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize($clubView, 'json'),
		    Response::HTTP_OK,
		    array('Content-Type' => 'application/hal+json'));
	}

	/**
	 * @Route("/api/club", name="api_club_create", methods={"POST"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Post(
	 *     operationId="createClub",
	 *     tags={"Club"},
	 *     path="/api/club",
	 *     summary="Create a club",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(name="X-ClientId", in="header",  required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
     *     @OA\RequestBody(
     *         description="User object that needs to be added",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ClubCreate"),
     *     ),
	 *     @OA\Response(
	 *         response="201",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Schema(ref="#/components/schemas/Club")
	 *         )
	 *     ),
	 *     @OA\Response(response="400", description="Request contains not valid field", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="403", description="Forbidden to create a club", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function create(Request $request, SerializerInterface $serializer, TranslatorInterface $translator)
	{
		$this->denyAccessUnlessGranted(Roles::ROLE_ADMIN); // 403
		
	    $requestUtil = new RequestUtil($serializer, $translator);
	    $clubToCreate = $requestUtil->validate($request, ClubCreate::class); // 400
	    
		$name = $clubToCreate->getName();
		$uuid = $clubToCreate->getUuid();
		if($uuid == null || trim($uuid) === '') {
		    $uuid = StringUtils::nameToUuid($name);
		}
		
		$doctrine = $this->container->get('doctrine');
		
		$entityFinder = new EntityFinder($doctrine);
		$entityFinder->findNoneByOrThrow(Club::class, ['uuid' => $uuid],
		    function() use($uuid) {
		        throw new CRMException(Response::HTTP_BAD_REQUEST, 'UUID already used: '.$uuid); // 400
		    });
		
		$club = new Club();
		$club->setActive($clubToCreate->isActive());
		$club->setUuid($uuid);
        $club->setName($name);
        $club->setLogo('default.png');
		$club->setContactEmails($clubToCreate->getContactEmails());
		$club->setContactPhone($clubToCreate->getContactPhone());
		$club->setFacebookUrl($clubToCreate->getFacebookUrl());
		$club->setInstagramUrl($clubToCreate->getInstagramUrl());
		$club->setMailingList($clubToCreate->getMailingList());
		$club->setTwitterUrl($clubToCreate->getTwitterUrl());
		$club->setWebsiteUrl($clubToCreate->getWebsiteUrl());
		$doctrine->getManager()->persist($club);
		
		$data = ['name' => $name, 'uuid' => $uuid, 'active' => $clubToCreate->isActive()];
		Events::add($doctrine, Events::CLUB_CREATED, $this->getUser(), $request, $data);
		$this->logger->debug('Club created: '.json_encode($data));

		$hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize(new ClubView($club, null, null), 'json'),
		    Response::HTTP_CREATED, // 201
		    array('Content-Type' => 'application/hal+json'));
	}

	/**
	 * @Route("/api/club/{uuid}", name="api_club_update", methods={"PATCH"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Patch(
	 *     operationId="updateClub",
	 *     tags={"Club"},
	 *     path="/api/club/{uuid}",
	 *     summary="Update a club",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(name="X-ClientId", in="header", required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
	 *     @OA\Parameter(
     *         description="UUID of club",
     *         in="path",
     *         name="uuid",
     *         required=true,
     *         @OA\Schema(
     *             format="string",
     *             type="string",
     *             pattern="[a-z0-9_]{2,64}"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="User object that needs to be added",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ClubUpdate"),
     *     ),
	 *     @OA\Response(response="204", description="Successful"),
	 *     @OA\Response(response="400", description="Request contains not valid field", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="403", description="Forbidden to update a club", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="404", description="Club not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function update(Request $request, $uuid, SerializerInterface $serializer, TranslatorInterface $translator)
	{
	    $this->denyAccessUnlessGranted(Roles::ROLE_ADMIN); // 403
	    
	    $requestUtil = new RequestUtil($serializer, $translator);
	    $clubToUpdate = $requestUtil->validate($request, ClubUpdate::class); // 400
	    
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $uuid]); // 404
	    
	    $uuid = $clubToUpdate->getUuid();
	    if( ! empty($uuid) && $uuid !== $club->getUuid()) {
	        $entityFinder->findNoneByOrThrow(Club::class, ['uuid' => $uuid],
	            function() use($uuid) {
	                throw new CRMException(Response::HTTP_BAD_REQUEST, 'UUID already used: '.$uuid); // 400
	            });
	    }
	    
	    $entityUpdater = new EntityUpdater($doctrine, $request, $this->getUser(), Events::CLUB_UPDATED, $this->logger);
	    $entityUpdater->update('active', $clubToUpdate->isActive(), $club->getActive(), function($v) use($club) { $club->setActive($v); });
	    $entityUpdater->update('uuid', $clubToUpdate->getUuid(), $club->getUuid(), function($v) use($club) { $club->setUuid($v); });
	    $entityUpdater->update('name', $clubToUpdate->getName(), $club->getName(), function($v) use($club) { $club->setName($v); });
	    $entityUpdater->update('contactemails', $clubToUpdate->getContactEmails(), $club->getContactEmails(), function($v) use($club) { $club->setContactEmails($v); });
	    $entityUpdater->update('contactphone', $clubToUpdate->getContactPhone(), $club->getContactPhone(), function($v) use($club) { $club->setContactPhone($v); });
	    $entityUpdater->update('facebookurl', $clubToUpdate->getFacebookUrl(), $club->getFacebookUrl(), function($v) use($club) { $club->setFacebookUrl($v); });
	    $entityUpdater->update('instagramurl', $clubToUpdate->getInstagramUrl(), $club->getInstagramUrl(), function($v) use($club) { $club->setInstagramUrl($v); });
	    $entityUpdater->update('mailinglist', $clubToUpdate->getMailingList(), $club->getMailingList(), function($v) use($club) { $club->setMailingList($v); });
	    $entityUpdater->update('twitterurl', $clubToUpdate->getTwitterUrl(), $club->getTwitterUrl(), function($v) use($club) { $club->setTwitterUrl($v); });
	    $entityUpdater->update('websiteurl', $clubToUpdate->getWebsiteUrl(), $club->getWebsiteUrl(), function($v) use($club) { $club->setWebsiteUrl($v); });
	    return $entityUpdater->toResponse($club, 'Club updated', ['id' => $club->getId()]);
	}

}
