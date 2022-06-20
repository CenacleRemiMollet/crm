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
use App\Entity\ClubLesson;
use App\Model\ClubLessonView;
use App\Model\ClubCreate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\ClubService;
use App\Model\ClubLocationView;
use App\Security\ClubAccess;
use App\Entity\Events;
use Symfony\Component\Security\Core\Role\Role;
use App\Security\Roles;


class ClubLocationsController extends AbstractController
{

    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    
    /**
	 * @Route("/api/club/{uuid}/locations", name="api_get_club_locations", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Get(
	 *     operationId="getClubLocations",
	 *     tags={"Club"},
	 *     path="/api/club/{uuid}/locations",
	 *     summary="Give some locations",
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
	 *             @OA\Schema(
	 *                 type="array",
	 *                 @OA\Items(ref="#/components/schemas/ClubLocation")
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(response="404", description="Club not found")
	 * )
	 */
	public function getLocations(string $uuid): Response
	{
		$doctrine = $this->container->get('doctrine');
		
		$clubs = $doctrine->getManager()
		    ->getRepository(Club::class)
		    ->findBy(['uuid' => $uuid]);
	    if(empty($clubs)) {
	        return new Response('Club not found', Response::HTTP_NOT_FOUND); // 404
	    }
	    $clubIds = array();
	    foreach ($clubs as &$club) {
	        $clubByIds[$club->getId()] = $club;
	        array_push($clubIds, $club->getId());
	    }
	    $clubLocations = $doctrine->getManager()
			->getRepository(ClubLocation::class)
			->findByClubIds($clubByIds);

		$clubLocationViews = array();
	    foreach($clubLocations as &$clubLocation) {
	        array_push($clubLocationViews, new ClubLocationView($clubLocation[0]));
	    }
	    
		$hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize($clubLocationViews, 'json'),
		    Response::HTTP_OK, // 200
		    array(
			'Content-Type' => 'application/hal+json'
		));
	}

	/**
	 * @Route("/api/club/{club_uuid}/locations/{location_uuid}", name="api_get_club_location", methods={"GET"}, requirements={"club_uuid"="[a-z0-9_]{2,64}","location_uuid"="[a-zA-Z0-9_]{2,64}"})
	 * @OA\Get(
	 *     operationId="getClubLocation",
	 *     tags={"Club"},
	 *     path="/api/club/{uuid}/locations/{location_uuid}",
	 *     summary="Give a location for a club",
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
	 *     @OA\Response(response="404", description="Club or location not found")
	 * )
	 */
	public function getLocation(string $club_uuid, string $location_uuid): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $clubs = $doctrine->getManager()
    	    ->getRepository(Club::class)
    	    ->findBy(['uuid' => $club_uuid]);
	    if(empty($clubs)) {
	        return new Response('Club not found', Response::HTTP_NOT_FOUND); // 404
	    }
	    $clubIds = array();
	    foreach ($clubs as &$club) {
	        $clubByIds[$club->getId()] = $club;
	        array_push($clubIds, $club->getId());
	    }
	    $clubLocations = $doctrine->getManager()
    	    ->getRepository(ClubLocation::class)
    	    ->findByClubIds($clubByIds);

    	$clubLocationView = null;
    	// TODO refactor...
    	foreach($clubLocations as &$clubLocationArray) {
    	    $clubLocation = $clubLocationArray[0]; 
    	    if($clubLocation->getUuid() === $location_uuid) {
    	        $clubLocationView = new ClubLocationView($clubLocation);
    	        break;
    	    }
	    }
	    if($clubLocationView == null) {
	        return new Response('Location not found', Response::HTTP_NOT_FOUND); // 404
	    }
	    
	    $hateoas = HateoasBuilder::create()->build();
	    return new Response(
	        $hateoas->serialize($clubLocationView, 'json'),
	        Response::HTTP_OK, // 200
	        array(
	            'Content-Type' => 'application/hal+json'
	        ));
	}
	
	
	/**
	 * @Route("/api/club/{club_uuid}/locations", name="api_create_club_locations", methods={"POST"}, requirements={"club_uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Post(
	 *     operationId="createClubLocation",
	 *     tags={"Club"},
	 *     path="/api/club/{uuid}/locations/{location_uuid}",
	 *     summary="Create a location for a club",
	 *     security = {{"basicAuth": {}}},
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
	 *     @OA\Parameter(name="X-ClientId", in="header", required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
	 *     @OA\Response(response="201", description="Successful"),
	 *     @OA\Response(response="403", description="Forbidden to create a location"),
	 *     @OA\Response(response="404", description="Club not found")
	 * )
	 */
	public function createLocation(Request $request, string $club_uuid): Response
	{
	    if(! $this->isGranted(Roles::ROLE_SUPER_ADMIN)
	        && ! $this->isGranted(Roles::ROLE_ADMIN)
	        && ! $this->isGranted(Roles::ROLE_CLUB_MANAGER)) {
	            throw $this->createAccessDeniedException('Access Denied');
	    }
	    
	    $doctrine = $this->container->get('doctrine');
	    
	    $clubs = $doctrine->getManager()
    	    ->getRepository(Club::class)
    	    ->findBy(['uuid' => $club_uuid]);
	    if(empty($clubs)) {
	        return new Response('Club not found', Response::HTTP_NOT_FOUND); // 404
	    }
	    
	    // TODO location dépend de lesson
	    
          
	    
	    return new Response('', Response::HTTP_NO_CONTENT); // 204
	}
	
	
	/**
	 * @Route("/api/club/{club_uuid}/locations/{location_uuid}", name="api_delete_club_locations", methods={"DELETE"}, requirements={"club_uuid"="[a-z0-9_]{2,64}","location_uuid"="[a-zA-Z0-9_]{2,64}"})
	 * @OA\Delete(
	 *     operationId="deleteClubLocation",
	 *     tags={"Club"},
	 *     path="/api/club/{uuid}/locations/{location_uuid}",
	 *     summary="Delete a location for a club",
	 *     security = {{"basicAuth": {}}},
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
	 *     @OA\Response(response="403", description="Forbidden to delete a location"),
	 *     @OA\Response(response="404", description="Club or location not found")
	 * )
	 */
	public function deleteLocation(Request $request, string $club_uuid, string $location_uuid): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $clubs = $doctrine->getManager()
    	    ->getRepository(Club::class)
    	    ->findBy(['uuid' => $club_uuid]);
	    if(empty($clubs)) {
	        return new Response('Club not found', Response::HTTP_NOT_FOUND); // 404
	    }
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    if(! $clubAccess->hasAccessForUser($clubs[0], $this->getUser())) {
	        return new Response('', Response::HTTP_FORBIDDEN); // 403
	    }
	    
	    $clubIds = array();
	    foreach ($clubs as &$club) {
	        $clubByIds[$club->getId()] = $club;
	        array_push($clubIds, $club->getId());
	    }
	    $clubLocations = $doctrine->getManager()
    	    ->getRepository(ClubLocation::class)
    	    ->findByClubIds($clubByIds);
	    
	    $clubLocation = null;
	    // TODO refactor...
	    foreach($clubLocations as &$clubLocationArray) {
	        if($clubLocationArray[0]->getUuid() === $location_uuid) {
	            $clubLocation = $clubLocationArray[0];
	            break;
	        }
	    }
	    if($clubLocation == null) {
	        return new Response('Location not found', Response::HTTP_NOT_FOUND); // 404
	    }
	    
	    $doctrine->getManager()->remove($clubLocation);
	    $doctrine->getManager()->flush();
	    
	    $data = ['club_uuid' => $club_uuid, 'location_uuid' => $location_uuid, 'name' => $clubLocation->getName()];
	    Events::add($doctrine, Events::CLUB_LOCATION_DELETED, $this->getUser(), $request, $data);
	    $this->logger->debug('Club location deleted: '.json_encode($data));
	    
	    return new Response('', Response::HTTP_NO_CONTENT); // 204
	}
}
