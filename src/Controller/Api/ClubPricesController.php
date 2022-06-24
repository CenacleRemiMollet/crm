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
use App\Entity\EntityFinder;
use App\Exception\CRMException;


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
	 *     @OA\Response(response="404", description="Club not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function getPrices($club_uuid)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $prices = $doctrine->getManager()
    	    ->getRepository(ClubPrice::class)
    	    ->findBy(['club' => $club]);
    	    $priceViews = array();
	    foreach($prices as &$price) {
	        array_push($priceViews, new ClubPriceView($club, $price));
	    }

		$hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize($priceViews, 'json'),
		    Response::HTTP_OK,
		    array('Content-Type' => 'application/hal+json'));
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
	 *     @OA\Response(response="404", description="Club or price not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function getPrice($club_uuid, $price_uuid)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    $price = $entityFinder->findOneByOrThrow(ClubPrice::class, ['club' => $club, 'uuid' => $price_uuid]); // 404
	    
	    $hateoas = HateoasBuilder::create()->build();
	    return new Response(
	        $hateoas->serialize(new ClubPriceView($club, $price), 'json'),
	        Response::HTTP_OK,
	        array('Content-Type' => 'application/hal+json'));
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
	 *     @OA\Response(response="400", description="Request contains not valid field", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="403", description="Forbidden", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="404", description="Club or price not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function createPrice(string $club_uuid, Request $request, SerializerInterface $serializer, TranslatorInterface $translator)
	{
        $doctrine = $this->container->get('doctrine');
       
        $entityFinder = new EntityFinder($doctrine);
        $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
        
        $clubAccess = new ClubAccess($this->container, $this->logger);
        $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
        
        $requestUtil = new RequestUtil($serializer, $translator);
        $priceToCreate = $requestUtil->validate($request, ClubPriceCreate::class); // 400
        
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
        if($priceToCreate->getChild1() !== null) {
            $price->setPriceChild1($priceToCreate->getChild1());
        }
        if($priceToCreate->getChild2() !== null) {
        $price->setPriceChild2($priceToCreate->getChild2());
        }
        if($priceToCreate->getChild3() !== null) {
            $price->setPriceChild3($priceToCreate->getChild3());
        }
        if($priceToCreate->getAdult() !== null) {
            $price->setPriceAdult($priceToCreate->getAdult());
        }
        $doctrine->getManager()->persist($price);
        
        $data = ['discipline' => $price->getDiscipline(), 'uuid' => $price->getUuid()];
        Events::add($doctrine, Events::CLUB_PRICE_CREATED, $this->getUser(), $request, $data);
        $this->logger->debug('Club price created: '.json_encode($data));
        
        $hateoas = HateoasBuilder::create()->build();
        return new Response(
            $hateoas->serialize(new ClubPriceView($club, $price), 'json'),
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
	 *     @OA\Response(response="400", description="Request contains not valid field", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="403", description="Forbidden to update a price", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="404", description="Club or price not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function updatePrice(string $club_uuid, string $price_uuid, Request $request, SerializerInterface $serializer, TranslatorInterface $translator): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $requestUtil = new RequestUtil($serializer, $translator);
	    $priceToUpdate = $requestUtil->validate($request, ClubPriceUpdate::class);
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $price = $entityFinder->findOneByOrThrow(ClubPrice::class, ['uuid' => $price_uuid, 'club' => $club]); // 404
	    
	    $uuid = $priceToUpdate->getUuid();
	    if( ! empty($uuid) && $uuid !== $price->getUuid()) {
	        $entityFinder->findNoneByOrThrow(ClubPrice::class, ['uuid' => $uuid],
	            function() use($uuid) {
	                throw new CRMException(Response::HTTP_BAD_REQUEST, 'Price UUID already used: '.$uuid); // 400
	            });
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
	 *     @OA\Response(response="403", description="Forbidden", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="404", description="Club or price not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function deletePrice(Request $request, string $club_uuid, string $price_uuid)
	{
	    $doctrine = $this->container->get('doctrine');
	  
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403

	    $price = $entityFinder->findOneByOrThrow(ClubPrice::class, ['club' => $club, 'uuid' => $price_uuid]); // 404
	    
	    $doctrine->getManager()->remove($price);
	    
	    $data = ['club_uuid' => $club_uuid, 'price_uuid' => $price_uuid, 'discipline' => $price->getDiscipline()];
	    Events::add($doctrine, Events::CLUB_PRICE_DELETED, $this->getUser(), $request, $data);
	    $this->logger->debug('Club price deleted: '.json_encode($data));
	    
        return new Response('', Response::HTTP_NO_CONTENT); // 204
	}
}
