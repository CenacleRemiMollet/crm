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
	    $clubService = new ClubService($this->getDoctrine());
	    $clubViews = $clubService->convertAllActiveToView();
	    $hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize($clubViews, 'json'),
		    Response::HTTP_OK,
		    array(
                'Content-Type' => 'application/hal+json'
		    ));
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
	 *     @OA\Response(response="404", description="Club not found")
	 * )
	 */
	public function one($uuid)
	{
		$clubs = $this->container->get('doctrine')->getManager()
			->getRepository(Club::class)
			->findBy(['uuid' => $uuid]);
		$clubView = null;
		if(count($clubs) > 0) {
		    $clubService = new ClubService($this->getDoctrine());
		    $clubViews = $clubService->convertToView($clubs);
			$clubView = $clubViews[0];
		} else {
			return new Response('Club not found: '.$uuid, 404);
		}

		$hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize($clubView, 'json'),
		    Response::HTTP_OK,
		    array(
                'Content-Type' => 'application/hal+json'
            ));
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
	 *     @OA\Response(response="400", description="Request contains not valid field"),
	 *     @OA\Response(response="403", description="Forbidden to create a club"),
	 *     @OA\Response(response="404", description="Club UUID already used")
	 * )
	 */
	public function create(Request $request, SerializerInterface $serializer, TranslatorInterface $translator)
	{
		$this->denyAccessUnlessGranted(Roles::ROLE_ADMIN);
		
	    $requestUtil = new RequestUtil($serializer, $translator);
		try {
			$clubToCreate = $requestUtil->validate($request, ClubCreate::class);
		} catch (ViolationException $e) {
		    return new Response(
		        json_encode($e->getErrors()),
		        Response::HTTP_BAD_REQUEST, // 400
		        array('Content-Type' => 'application/hal+json'));
		}

		$name = $clubToCreate->getName();
		$uuid = $clubToCreate->getUuid();
		if($uuid == null || trim($uuid) === '') {
		    $uuid = StringUtils::nameToUuid($name);
		}
		
		$clubs = $this->container->get('doctrine')->getManager()
		  ->getRepository(Club::class)
		  ->findBy(['uuid' => $uuid]);
	    if( ! empty($clubs)) {
	        return  new Response('uuid already used: '.$uuid, Response::HTTP_METHOD_NOT_ALLOWED); // 405
		}
		
		$doctrine = $this->container->get('doctrine');
		
		$club = new Club();
		$club->setActive($clubToCreate->isActive());
		$club->setUuid($uuid);
        $club->setName($name);
        $club->setLogo('default.png');
		$club->setContactEmail($clubToCreate->getContactEmails());
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
	 *     @OA\Parameter(name="X-ClientId", in="header",  required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
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
	 *     @OA\Response(response="400", description="Request contains not valid field"),
	 *     @OA\Response(response="403", description="Forbidden to update a club"),
	 *     @OA\Response(response="404", description="Club not found")
	 * )
	 */
	public function update(Request $request, $uuid, SerializerInterface $serializer, TranslatorInterface $translator)
	{
	    $this->denyAccessUnlessGranted("ROLE_ADMIN");
	    
	    $requestUtil = new RequestUtil($serializer, $translator);
	    try {
	        $clubToUpdate = $requestUtil->validate($request, ClubUpdate::class);
	    } catch (ViolationException $e) {
	        return new Response(
	            json_encode($e->getErrors()),
	            Response::HTTP_BAD_REQUEST, // 400
	            array('Content-Type' => 'application/hal+json'));
	    }
	    
	    $doctrine = $this->container->get('doctrine');
	    $clubs = $doctrine->getManager()
    	    ->getRepository(Club::class)
    	    ->findBy(['uuid' => $uuid]);
	    if(empty($clubs)) {
	        return  new Response('Club not found: '.$uuid, Response::HTTP_NOT_FOUND);// 404
	    }
	    $club = $clubs[0];
	    
	    $uuid = $clubToUpdate->getUuid();
	    if( ! empty($uuid) && $uuid !== $club->getUuid()) {
	        $clubsUsed = $doctrine->getManager()
    	        ->getRepository(Club::class)
    	        ->findBy(['uuid' => $uuid]);
    	    if(!empty($clubsUsed)) {
	            return new Response('Club UUID already used', Response::HTTP_BAD_REQUEST); // 400
	        }
	    }
	    
	    $entityUpdater = new EntityUpdater($doctrine, $request, $this->getUser(), Events::CLUB_UPDATED, $this->logger);
	    $entityUpdater->update('active', $clubToUpdate->isActive(), $club->getActive(), function($v) use($club) { $club->setActive($v); });
	    $entityUpdater->update('uuid', $clubToUpdate->getUuid(), $club->getUuid(), function($v) use($club) { $club->setUuid($v); });
	    $entityUpdater->update('name', $clubToUpdate->getName(), $club->getName(), function($v) use($club) { $club->setName($v); });
	    $entityUpdater->update('contactemails', $clubToUpdate->getContactEmails(), $club->getContactEmails(), function($v) use($club) { $club->getContactEmails($v); });
	    $entityUpdater->update('contactphone', $clubToUpdate->getContactPhone(), $club->getContactPhone(), function($v) use($club) { $club->getContactPhone($v); });
	    $entityUpdater->update('facebookurl', $clubToUpdate->getFacebookUrl(), $club->getFacebookUrl(), function($v) use($club) { $club->setFacebookUrl($v); });
	    $entityUpdater->update('instagramurl', $clubToUpdate->getInstagramUrl(), $club->getInstagramUrl(), function($v) use($club) { $club->setInstagramUrl($v); });
	    $entityUpdater->update('mailinglist', $clubToUpdate->getMailingList(), $club->getMailingList(), function($v) use($club) { $club->setMailingList($v); });
	    $entityUpdater->update('twitterurl', $clubToUpdate->getTwitterUrl(), $club->getTwitterUrl(), function($v) use($club) { $club->setTwitterUrl($v); });
	    $entityUpdater->update('websiteurl', $clubToUpdate->getWebsiteUrl(), $club->getWebsiteUrl(), function($v) use($club) { $club->setWebsiteUrl($v); });
	    return $entityUpdater->toResponse($club, 'Club updated', ['id' => $club->getId()]);
	}

}
