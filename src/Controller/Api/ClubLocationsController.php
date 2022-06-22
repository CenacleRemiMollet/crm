<?php

namespace App\Controller\Api;

use App\Entity\Club;
use Hateoas\HateoasBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\ClubLocation;
use OpenApi\Annotations as OA;
use App\Media\MediaManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Service\ClubService;
use App\Model\ClubLocationView;
use App\Security\ClubAccess;
use App\Entity\Events;
use Symfony\Component\Security\Core\Role\Role;
use App\Security\Roles;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Util\RequestUtil;
use App\Exception\ViolationException;
use App\Model\ClubLocationCreate;
use App\Util\StringUtils;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\Model\ClubLocationUpdate;
use App\Entity\EntityFinder;
use App\Exception\CRMException;


class ClubLocationsController extends AbstractController
{

    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    
    /**
	 * @Route("/api/club/{club_uuid}/locations", name="api_get_club_locations", methods={"GET"}, requirements={"club_uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Get(
	 *     operationId="getClubLocations",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/locations",
	 *     summary="Give some locations",
	 *     @OA\Parameter(
	 *         description="UUID of club",
	 *         in="path",
	 *         name="club_uuid",
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
	 *             @OA\Schema(
	 *                 type="array",
	 *                 @OA\Items(ref="#/components/schemas/ClubLocation")
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(response="404", description="Club not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function getLocations(string $club_uuid): Response
	{
		$doctrine = $this->container->get('doctrine');
		
		$entityFinder = new EntityFinder($doctrine);
		$club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubLocations = $doctrine->getManager()
			->getRepository(ClubLocation::class)
			->findBy(['club' => $club]);

		$clubLocationViews = array();
	    foreach($clubLocations as &$clubLocation) {
	        array_push($clubLocationViews, new ClubLocationView($clubLocation));
	    }
	    
		$hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize($clubLocationViews, 'json'),
		    Response::HTTP_OK, // 200
		    array('Content-Type' => 'application/hal+json'));
	}

	
	/**
	 * @Route("/api/club/{club_uuid}/locations/{location_uuid}", name="api_get_club_location", methods={"GET"}, requirements={"club_uuid"="[a-z0-9_]{2,64}","location_uuid"="[a-zA-Z0-9_]{2,64}"})
	 * @OA\Get(
	 *     operationId="getClubLocation",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/locations/{location_uuid}",
	 *     summary="Give a location for a club",
	 *     @OA\Parameter(
	 *         description="UUID of club",
	 *         in="path",
	 *         name="club_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[a-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         description="UUID of location",
	 *         in="path",
	 *         name="location_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[A-Za-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Items(ref="#/components/schemas/ClubLocation")
	 *         )
	 *     ),
	 *     @OA\Response(response="404", description="Club or location not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function getLocation(string $club_uuid, string $location_uuid): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    $clubLocation = $entityFinder->findOneByOrThrow(ClubLocation::class, ['uuid' => $location_uuid, 'club' => $club]); // 404

	    $hateoas = HateoasBuilder::create()->build();
	    return new Response(
	        $hateoas->serialize(new ClubLocationView($clubLocation), 'json'),
	        Response::HTTP_OK, // 200
	        array('Content-Type' => 'application/hal+json'));
	}
	
	
	/**
	 * @Route("/api/club/{club_uuid}/locations", name="api_create_club_locations", methods={"POST"}, requirements={"club_uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Post(
	 *     operationId="createClubLocation",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/locations",
	 *     summary="Create a location for a club",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(
	 *         description="UUID of club",
	 *         in="path",
	 *         name="club_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[a-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\Parameter(name="X-ClientId", in="header", required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
     *     @OA\RequestBody(
     *         description="Location object that needs to be added",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ClubLocationCreate"),
     *     ),
	 *     @OA\Response(
	 *         response="201",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Schema(ref="#/components/schemas/ClubLocation")
	 *         )
	 *     ),
	 *     @OA\Response(response="400", description="Request contains not valid field", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="403", description="Forbidden to create a location", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="404", description="Club not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function createLocation(string $club_uuid, Request $request, SerializerInterface $serializer, TranslatorInterface $translator): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $requestUtil = new RequestUtil($serializer, $translator);
        $locationToCreate = $requestUtil->validate($request, ClubLocationCreate::class); // 400
	    
	    $name = $locationToCreate->getName();
	    $uuid = $locationToCreate->getUuid();
	    if($uuid == null || trim($uuid) === '') {
	        $uuid = StringUtils::nameToUuid($name).'_'.StringUtils::random_str(4);
	    }
	    
	    $location = new ClubLocation();
	    $location->setAddress(StringUtils::defaultOrEmpty($locationToCreate->getAddress()));
	    $location->setCity(StringUtils::defaultOrEmpty($locationToCreate->getCity()));
	    $location->setClub($club);
	    $location->setCountry(StringUtils::defaultOrEmpty($locationToCreate->getCountry()));
	    $location->setCounty(StringUtils::defaultOrEmpty($locationToCreate->getCounty()));
	    $location->setName($name);
	    $location->setUuid($uuid);
	    $location->setZipcode(StringUtils::defaultOrEmpty($locationToCreate->getZipcode()));
	    $doctrine->getManager()->persist($location);
          
	    $data = ['name' => $name, 'uuid' => $uuid, 'address' => $location->getAddress(), 'city' => $location->getCity()];
	    Events::add($doctrine, Events::CLUB_LOCATION_CREATED, $this->getUser(), $request, $data);
	    $this->logger->debug('Club location created: '.json_encode($data));
	    
	    $hateoas = HateoasBuilder::create()->build();
	    return new Response(
	        $hateoas->serialize(new ClubLocationView($location), 'json'),
	        Response::HTTP_CREATED, // 201
	        array('Content-Type' => 'application/hal+json'));
	}
	
	
	/**
	 * @Route("/api/club/{club_uuid}/locations/{location_uuid}", name="api_update_club_locations", methods={"PATCH"}, requirements={"club_uuid"="[a-z0-9_]{2,64}","location_uuid"="[a-zA-Z0-9_]{2,64}"})
	 * @OA\Patch(
	 *     operationId="updateClubLocation",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/locations/{location_uuid}",
	 *     summary="Update a location for a club",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(name="X-ClientId", in="header", required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
	 *     @OA\Parameter(
	 *         description="UUID of club",
	 *         in="path",
	 *         name="club_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[a-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         description="UUID of location",
	 *         in="path",
	 *         name="location_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[A-Za-z0-9_]{2,64}"
	 *         )
	 *     ),
     *     @OA\RequestBody(
     *         description="Location object that needs to be added",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ClubLocationUpdate"),
     *     ),
	 *     @OA\Response(response="204", description="Successful"),
	 *     @OA\Response(response="400", description="Request contains not valid field", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="403", description="Forbidden to update a location", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="404", description="Club or location not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function updateLocation(string $club_uuid, string $location_uuid, Request $request, SerializerInterface $serializer, TranslatorInterface $translator): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $requestUtil = new RequestUtil($serializer, $translator);
        $locationToUpdate = $requestUtil->validate($request, ClubLocationUpdate::class); // 400
	    
        $entityFinder = new EntityFinder($doctrine);
        $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $location = $entityFinder->findOneByOrThrow(ClubLocation::class, ['uuid' => $location_uuid, 'club' => $club]); // 404
	    
	    $uuid = $locationToUpdate->getUuid();
	    if( ! empty($uuid) && $uuid !== $location->getUuid()) {
	        $entityFinder->findNoneByOrThrow(ClubLocation::class, ['uuid' => $uuid],
	            function() use($uuid) {
	                throw new CRMException(Response::HTTP_BAD_REQUEST, 'Location UUID already used: '.$uuid); // 400
	            });
	    }
	    
	    $entityUpdater = new EntityUpdater($doctrine, $request, $this->getUser(), Events::CLUB_LOCATION_UPDATED, $this->logger);
	    $entityUpdater->update('name', $locationToUpdate->getName(), $location->getName(), function($v) use($location) { $location->setName($v); });
	    $entityUpdater->update('uuid', $locationToUpdate->getUuid(), $location->getUuid(), function($v) use($location) { $location->setUuid($v); });
	    $entityUpdater->update('address', $locationToUpdate->getAddress(), $location->getAddress(), function($v) use($location) { $location->setAddress($v); });
	    $entityUpdater->update('city', $locationToUpdate->getCity(), $location->getCity(), function($v) use($location) { $location->setCity($v); });
	    $entityUpdater->update('zipcode', $locationToUpdate->getZipcode(), $location->getZipcode(), function($v) use($location) { $location->setZipcode($v); });
	    $entityUpdater->update('county', $locationToUpdate->getCounty(), $location->getCounty(), function($v) use($location) { $location->setCounty($v); });
	    $entityUpdater->update('country', $locationToUpdate->getCountry(), $location->getCountry(), function($v) use($location) { $location->setCountry($v); });
	    return $entityUpdater->toResponse($location, 'Club location updated', ['id' => $location->getId()]);
	}
	    
	    
	/**
	 * @Route("/api/club/{club_uuid}/locations/{location_uuid}", name="api_delete_club_locations", methods={"DELETE"}, requirements={"club_uuid"="[a-z0-9_]{2,64}","location_uuid"="[a-zA-Z0-9_]{2,64}"})
	 * @OA\Delete(
	 *     operationId="deleteClubLocation",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/locations/{location_uuid}",
	 *     summary="Delete a location for a club",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(
	 *         description="UUID of club",
	 *         in="path",
	 *         name="club_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[a-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         description="UUID of location",
	 *         in="path",
	 *         name="location_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[A-Za-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\Parameter(name="X-ClientId", in="header", required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
	 *     @OA\Response(response="204", description="Successful"),
	 *     @OA\Response(response="403", description="Forbidden to delete a location", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="404", description="Club or location not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function deleteLocation(Request $request, string $club_uuid, string $location_uuid): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	   
	    $clubLocation = $entityFinder->findOneByOrThrow(ClubLocation::class, ['uuid' => $location_uuid, 'club' => $club]); // 404
	    	    
	    $doctrine->getManager()->remove($clubLocation);
	    
	    $data = ['club_uuid' => $club_uuid, 'location_uuid' => $location_uuid, 'name' => $clubLocation->getName()];
	    Events::add($doctrine, Events::CLUB_LOCATION_DELETED, $this->getUser(), $request, $data);
	    $this->logger->debug('Club location deleted: '.json_encode($data));
	    
	    return new Response('', Response::HTTP_NO_CONTENT); // 204
	}
}
