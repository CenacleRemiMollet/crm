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
	 *             pattern="[a-z0-9_]{2,64}"
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
	    $clubPriceService = new ClubPriceService($this->getDoctrine());
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
	 * @Route("/api/club/{club_uuid}/prices", name="api_create_club_prices", methods={"PUT"}, requirements={"club_uuid"="[a-z0-9_]{2,64}"})
	 * @IsGranted({"ROLE_ADMIN", "ROLE_CLUB_MANAGER", "ROLE_TEACHER"})
	 * @OA\Put(
	 *     operationId="createClubPrices",
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
	public function create(Request $request, SerializerInterface $serializer, TranslatorInterface $translator)
	{
	    // TODO
	}
	
	
	/**
	 * @Route("/api/club/{club_uuid}/prices/{price_uuid}", name="api_delete_club_price", methods={"DELETE"}, requirements={"club_uuid"="[a-z0-9_]{2,64}","price_uuid"="[a-zA-Z0-9_]{2,64}"})
	 * @IsGranted({"ROLE_ADMIN", "ROLE_CLUB_MANAGER", "ROLE_TEACHER"})
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
	public function deletePrice($club_uuid, $price_uuid)
	{
	    $manager = $this->getDoctrine()->getManager();
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
    	    ->findBy(['club_id' => $club->getId(), 'uuid' => $price_uuid]);
	    if(empty($prices)) {
	        return new Response('Price '.$price_uuid.' not found in club '.$club_uuid, Response::HTTP_NOT_FOUND); // 404
	    }

	    $manager->remove($prices[0]);
        $manager->flush();
	    
        return new Response('', Response::HTTP_NO_CONTENT); // 204
	}
}
