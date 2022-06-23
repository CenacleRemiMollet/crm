<?php

namespace App\Controller\Api;

use App\Entity\Club;
use Hateoas\HateoasBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\ClubLocation;
use OpenApi\Annotations as OA;
use App\Media\MediaManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Entity\ClubLesson;
use App\Model\ClubLocationView;
use App\Entity\City;
use App\Model\CityModel;
use App\Controller\ControllerUtils;
use App\Service\ClubService;


class ClubSearchController extends AbstractController
{

	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @Route("/api/clubsearch", name="api_club_search", methods={"GET"})
	 * @OA\Get(
	 *     operationId="searchClub",
	 *     tags={"Club"},
	 *     path="/api/clubsearch",
	 *     summary="Search clubs",
	 *     @OA\Parameter(
	 *         description="Zip code",
	 *         in="query",
	 *         name="zc",
	 *         required=false,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="\d{4,6}"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         description="Distance in kilometers",
	 *         in="query",
	 *         name="d",
	 *         required=false,
	 *         @OA\Schema(
	 *             type="integer",
	 *             default=5
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         description="Disciplines separated by comma",
	 *         in="query",
	 *         name="dis",
	 *         required=false,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         description="Days of week separated by comma",
	 *         in="query",
	 *         name="days",
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
	 *                 @OA\Items(ref="#/components/schemas/ClubLocation")
	 *             )
	 *         )
	 *     )
	 * )
	 */
	public function search(Request $request)
	{
	    $zipcode = $request->query->get('zc', '');
	    $distance = $request->query->get('d', 5);
	    $disciplines = ControllerUtils::parseDisciplines($request->query->get('dis', ''));
	    $days = ControllerUtils::parseDays($request->query->get('days', ''));
	    
	    $clubLocations = $this->getDoctrine()->getManager()
	    ->getRepository(ClubLocation::class)
	    ->findByZipcodeAndDistance($zipcode, $distance, $disciplines, $days, true);
	    
	    $this->logger->debug('Search club around '.$zipcode.' in '.$distance.' km : '.count($clubLocations).' club(s)');
	    
	    //$clubLocationByIds = array();
	    $clubLocationIds = array();
	    foreach ($clubLocations as &$clubLocation) {
	        //$clubLocationByIds[$clubLocation->getId()] = new ClubLocationView($clubLocation);
	        array_push($clubLocationIds, $clubLocation->getId());
	    }
	    
	    $clubs = $clubLocations = $this->getDoctrine()->getManager()
	    ->getRepository(Club::class)
	    ->findByClubLocationIds($clubLocationIds);
	    
	    $clubService = new ClubService($this->getDoctrine());
	    $clubViews = $clubService->convertToView($clubs);
	    
	    
	    $hateoas = HateoasBuilder::create()->build();
	    $json = json_decode($hateoas->serialize($clubViews, 'json'));
	    
	    return new Response(json_encode($json), 200, array(
	        'Content-Type' => 'application/hal+json'
	    ));
	}
	
}
