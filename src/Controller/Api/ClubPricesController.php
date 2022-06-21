<?php

namespace App\Controller\Api;

use Hateoas\HateoasBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use App\Entity\ClubPrice;
use App\Model\ClubPriceView;
use App\Service\ClubPriceService;
use App\Entity\Club;
use App\Security\ClubAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Security\Roles;
use App\Util\RequestUtil;
use App\Exception\ViolationException;
use App\Model\ClubPriceCreate;
use App\Entity\Events;
use App\Util\StringUtils;
use App\Model\ClubPriceUpdate;


class ClubPricesController extends AbstractController
{
    
    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    

	/**
	 * @Route("/api/club/{club_uuid}/prices", name="api_get_club_prices", methods={"GET"}, requirements={"club_uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Get(
	 *     operationId="getClubPrices",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/prices",
	 *     summary="Give prices for a club",
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
	 *                 @OA\Items(ref="#/components/schemas/ClubPrice")
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(response="404", description="Club not found")
	 * )
	 */
	public function getPrices($club_uuid)
	{
	    $clubPriceService = new ClubPriceService($this->getDoctrine());
	    $priceViews = $clubPriceService->convertByClubUuidToView($club_uuid);
	    if($priceViews == null) {
	        return new Response('Club not found: '.$club_uuid, 404);
	    }

		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($priceViews, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}

	
	/**
	 * @Route("/api/club/{club_uuid}/prices/{price_uuid}", name="api_get_club_price", methods={"GET"}, requirements={"club_uuid"="[a-z0-9_]{2,64}","price_uuid"="[a-zA-Z0-9_]{2,64}"})
	 * @OA\Get(
	 *     operationId="getClubPrice",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/prices/{price_uuid}",
	 *     summary="Give a price for a club",
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
	 *         description="UUID of price",
	 *         in="path",
	 *         name="price_uuid",
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
	 *             @OA\Items(ref="#/components/schemas/ClubPrice")
	 *         )
	 *     ),
	 *     @OA\Response(response="404", description="Club or price not found")
	 * )
	 */
	public function getPrice($club_uuid, $price_uuid)
	{
	    $clubPriceService = new ClubPriceService($this->container->get('doctrine'));
	    $priceView = $clubPriceService->convertByClubUuidAndPriceUuidToView($club_uuid, $price_uuid);
	    if($priceView == null) {
	        return new Response('Club '.$club_uuid.' or price '.$price_uuid.'not found', 404);
	    }
	    
	    $hateoas = HateoasBuilder::create()->build();
	    $json = json_decode($hateoas->serialize($priceView, 'json'));
	    
	    return new Response(json_encode($json), 200, array(
	        'Content-Type' => 'application/hal+json'
	    ));
	}
	
	
	/**
	 * @Route("/api/club/{club_uuid}/prices", name="api_create_club_prices", methods={"POST"}, requirements={"club_uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Post(
	 *     operationId="createClubPrice",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/prices",
	 *     summary="Create prices for a club",
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
     *         description="Price object that needs to be added",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ClubPriceCreate"),
     *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Schema(
	 *                 type="array",
	 *                 @OA\Items(ref="#/components/schemas/ClubPrice")
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(response="403", description="Forbidden"),
	 *     @OA\Response(response="404", description="Club or price not found")
	 * )
	 */
	public function createPrice(string $club_uuid, Request $request, SerializerInterface $serializer, TranslatorInterface $translator)
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
        $club = $clubs[0];
        
        $clubAccess = new ClubAccess($this->container, $this->logger);
        if(! $clubAccess->hasAccessForUser($club, $this->getUser())) {
            return new Response('', Response::HTTP_FORBIDDEN); // 403
        }
        
        $requestUtil = new RequestUtil($serializer, $translator);
        try {
            $priceToCreate = $requestUtil->validate($request, ClubPriceCreate::class);
        } catch (ViolationException $e) {
            return new Response(
                json_encode($e->getErrors()),
                Response::HTTP_BAD_REQUEST, // 400
                array('Content-Type' => 'application/hal+json'));
        }
        
        $uuid = $priceToCreate->getUuid();
        if($uuid == null || trim($uuid) === '') {
            $uuid = StringUtils::random_str(16);
        }
        
        $price = new ClubPrice();
        $price->setUuid($uuid);
        $price->setClub($club);
        $price->setDiscipline($priceToCreate->getDiscipline());
        $price->setCategory($priceToCreate->getCategory());
        $price->setComment($priceToCreate->getComment());
        $price->setPriceChild1($priceToCreate->getChild1());
        $price->setPriceChild2($priceToCreate->getChild2());
        $price->setPriceChild3($priceToCreate->getChild3());
        $price->setPriceAdult($priceToCreate->getAdult());
        $doctrine->getManager()->persist($price);
        
        $data = ['discipline' => $price->getDiscipline(), 'uuid' => $price->getUuid()];
        Events::add($doctrine, Events::CLUB_PRICE_CREATED, $this->getUser(), $request, $data);
        $this->logger->debug('Club price created: '.json_encode($data));
        
        $hateoas = HateoasBuilder::create()->build();
        return new Response(
            $hateoas->serialize(new ClubPriceView($price), 'json'),
            Response::HTTP_CREATED, // 201
            array('Content-Type' => 'application/hal+json'));
	}
	
	
	/**
	 * @Route("/api/club/{club_uuid}/prices/{price_uuid}", name="api_update_club_prices", methods={"PATCH"}, requirements={"club_uuid"="[a-z0-9_]{2,64}","price_uuid"="[a-zA-Z0-9_]{2,64}"})
	 * @OA\Patch(
	 *     operationId="updateClubPrice",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/prices/{price_uuid}",
	 *     summary="Update a price for a club",
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
	 *         description="UUID of price",
	 *         in="path",
	 *         name="price_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[A-Za-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\RequestBody(
	 *         description="Price object that needs to be added",
	 *         required=true,
	 *         @OA\JsonContent(ref="#/components/schemas/ClubPriceUpdate"),
	 *     ),
	 *     @OA\Response(response="204", description="Successful"),
	 *     @OA\Response(response="403", description="Forbidden to update a price"),
	 *     @OA\Response(response="404", description="Club or price not found")
	 * )
	 */
	public function updatePrice(string $club_uuid, string $price_uuid, Request $request, SerializerInterface $serializer, TranslatorInterface $translator): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $requestUtil = new RequestUtil($serializer, $translator);
	    try {
	        $priceToUpdate = $requestUtil->validate($request, ClubPriceUpdate::class);
	    } catch (ViolationException $e) {
	        return new Response(
	            json_encode($e->getErrors()),
	            Response::HTTP_BAD_REQUEST, // 400
	            array('Content-Type' => 'application/hal+json'));
	    }
	    
	    
	    $clubs = $doctrine->getManager()
	    ->getRepository(Club::class)
	    ->findBy(['uuid' => $club_uuid]);
	    if(empty($clubs)) {
	        return new Response('Club not found', Response::HTTP_NOT_FOUND); // 404
	    }
	    $club = $clubs[0];
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    if(! $clubAccess->hasAccessForUser($club, $this->getUser())) {
	        return new Response('', Response::HTTP_FORBIDDEN); // 403
	    }
	    
	    $prices = $doctrine->getManager()
    	    ->getRepository(ClubPrice::class)
    	    ->findBy(['uuid' => $price_uuid, 'club' => $club]);
    	if(empty($prices)) {
	        return new Response('Price not found', Response::HTTP_NOT_FOUND); // 404
	    }
	    $price = $prices[0];
	    
	    $uuid = $priceToUpdate->getUuid();
	    if( ! empty($uuid) && $uuid !== $price->getUuid()) {
	        $pricesUsed = $doctrine->getManager()
    	        ->getRepository(ClubPrice::class)
    	        ->findBy(['uuid' => $uuid]);
    	    if(!empty($pricesUsed)) {
	            return new Response('Price UUID already used', Response::HTTP_BAD_REQUEST); // 400
	        }
	    }
	    
	    $entityUpdater = new EntityUpdater($doctrine, $request, $this->getUser(), Events::CLUB_PRICE_UPDATED, $this->logger);
	    $entityUpdater->update('uuid', $priceToUpdate->getUuid(), $price->getUuid(), function($v) use($price) { $price->setUuid($v); });
	    $entityUpdater->update('discipline', $priceToUpdate->getDiscipline(), $price->getDiscipline(), function($v) use($price) { $price->setDiscipline($v); });
	    $entityUpdater->update('category', $priceToUpdate->getCategory(), $price->getCategory(), function($v) use($price) { $price->setCategory($v); });
	    $entityUpdater->update('comment', $priceToUpdate->getComment(), $price->getComment(), function($v) use($price) { $price->setComment($v); });
	    $entityUpdater->update('child1', $priceToUpdate->getChild1(), $price->getPriceChild1(), function($v) use($price) { $price->setPriceChild1($v); });
	    $entityUpdater->update('child2', $priceToUpdate->getChild2(), $price->getPriceChild2(), function($v) use($price) { $price->setPriceChild2($v); });
	    $entityUpdater->update('child3', $priceToUpdate->getChild3(), $price->getPriceChild3(), function($v) use($price) { $price->setPriceChild3($v); });
	    $entityUpdater->update('adult', $priceToUpdate->getAdult(), $price->getPriceAdult(), function($v) use($price) { $price->setPriceAdult($v); });
	    return $entityUpdater->toResponse($price, 'Club price updated', ['id' => $price->getId()]);
	}
	
	
	/**
	 * @Route("/api/club/{club_uuid}/prices/{price_uuid}", name="api_delete_club_price", methods={"DELETE"}, requirements={"club_uuid"="[a-z0-9_]{2,64}","price_uuid"="[a-zA-Z0-9_]{2,64}"})
	 * @OA\Delete(
	 *     operationId="deleteClubPrice",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/prices/{price_uuid}",
	 *     summary="Delete a price for a club",
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
	 *         description="UUID of price",
	 *         in="path",
	 *         name="price_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[a-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\Parameter(name="X-ClientId", in="header", required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
	 *     @OA\Response(response="204", description="Successful"),
	 *     @OA\Response(response="403", description="Forbidden"),
	 *     @OA\Response(response="404", description="Club or price not found")
	 * )
	 */
	public function deletePrice(Request $request, string $club_uuid, string $price_uuid)
	{
	    $doctrine = $this->container->get('doctrine');
        $manager = $doctrine->getManager();
	    $clubs = $manager->getRepository(Club::class)
    	    ->findBy(['uuid' => $club_uuid]);
	    if(empty($clubs)) {
	        return new Response('Club not found: '.$club_uuid, Response::HTTP_NOT_FOUND); // 404
	    }
	    $club = $clubs[0];

	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    if(! $clubAccess->hasAccessForUser($club, $this->getUser())) {
	        return new Response('', Response::HTTP_FORBIDDEN); // 403
	    }

	    $prices = $manager->getRepository(ClubPrice::class)
    	    ->findBy(['club' => $club, 'uuid' => $price_uuid]);
	    if(empty($prices)) {
	        return new Response('Price '.$price_uuid.' not found in club '.$club_uuid, Response::HTTP_NOT_FOUND); // 404
	    }
	    $price = $prices[0];
	    
	    //$doctrine->tr
	    $manager->remove($price);
	    
	    $data = ['club_uuid' => $club_uuid, 'price_uuid' => $price_uuid, 'discipline' => $price->getDiscipline()];
	    Events::add($doctrine, Events::CLUB_PRICE_DELETED, $this->getUser(), $request, $data);
	    $this->logger->debug('Club price deleted: '.json_encode($data));
	    
        return new Response('', Response::HTTP_NO_CONTENT); // 204
	}
}
