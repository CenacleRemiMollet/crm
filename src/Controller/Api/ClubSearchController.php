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
use App\Model\ClubLessonView;
use App\Model\ClubLocationView;
use App\Entity\City;
use App\Model\CityModel;


class ClubSearchController extends AbstractController
{

	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @Route("/api/clubsearch/cityname", name="api_club_search-city", methods={"GET"})
	 * @OA\Get(
	 *     path="/api/clubsearch/cityname",
	 *     summary="Search club by a city",
	 *     @OA\Parameter(
	 *         @OA\Schema(type="string"),
	 *         in="query",
	 *         allowReserved=true,
	 *         name="q",
     *         required=true
     *     ),
	 *     @OA\Response(response="200", description="Successful")
	 * )
	 */
	public function searchByCity(Request $request)
	{
		$query = $request->query->get('q');
		$query = trim($query);
		if(mb_strlen($query) < 3) {
			return new Response('{}', 200, array(
				'Content-Type' => 'application/hal+json'
			));
		}

		$cities = $this->getDoctrine()->getManager()
		->getRepository(City::class)
		->findByStartsWith($query);

		$cityModels = array();
		foreach ($cities as &$city) {
			array_push($cityModels, new CityModel($city));
		}

		//$output = array('cities' => $cities);
		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($cityModels, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}


	/**
	 * @Route("/api/clubsearch/zc/{zipcode}", name="api_club_search-zipcode", methods={"GET"})
	 * @OA\Get(
	 *     path="/api/clubsearch/zc/{zipcode}",
	 *     summary="Search all clubs around a zipcode with a distance in kilometers",
	 *     @OA\Parameter(
     *         description="Zip code",
     *         in="path",
     *         name="zipcode",
     *         required=true,
     *         @OA\Schema(
     *             format="string",
     *             type="string",
     *             pattern="[0-_]{4,7}"
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
	 *     @OA\Response(response="200", description="Successful")
	 * )
	 */
	public function searchAroundWithdistance(Request $request, $zipcode)
	{
		$distance = $request->query->get('distance', 5);

		$clubLocations = $this->getDoctrine()->getManager()
		->getRepository(ClubLocation::class)
		->findByZipcodeAndDistance($zipcode, $distance);

		$this->logger->debug('Search club around '.$zipcode.' in '.$distance.' km : '.count($clubLocations).' club(s)');

		$clubLocationByIds = array();
		$clubLocationIds = array();
		foreach ($clubLocations as &$clubLocation) {
			$clubLocationByIds[$clubLocation->getId()] = new ClubLocationView($clubLocation);
			array_push($clubLocationIds, $clubLocation->getId());
		}

		$clubs = $clubLocations = $this->getDoctrine()->getManager()
		->getRepository(Club::class)
		->findByClubLocationIds($clubLocationIds);

		$clubViews = $this->getDoctrine()->getManager()
		->getRepository(ClubLocation::class)
		->findByClubs($clubs);


		$output = array('clubs' => $clubViews);
		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($output, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}


}
