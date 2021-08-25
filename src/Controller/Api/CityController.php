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
use App\Entity\City;
use App\Model\CityView;
use App\Model\CityModel;


class CityController extends AbstractController
{
	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @Route("/api/city", name="api_club_search-city", methods={"GET"})
	 * @OA\Get(
	 *     tags={"City"},
	 *     path="/api/city",
	 *     summary="Search a city",
	 *     @OA\Parameter(
	 *         @OA\Schema(type="string"),
	 *         in="query",
	 *         allowReserved=true,
	 *         name="q",
	 *         required=true
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Schema(
	 *                 type="array",
	 *                 @OA\Items(ref="#/components/schemas/City")
	 *             )
	 *         )
	 *     )
	 * )
	 */
	public function searchByCity(Request $request)
	{
		$query = $request->query->get('q');
		$limit = $request->query->get('limit', 10);
		$query = trim($query);
		if(mb_strlen($query) < 3) {
			return new Response('{}', 200, array(
				'Content-Type' => 'application/hal+json'
			));
		}

		$cities = $this->getDoctrine()->getManager()
		->getRepository(City::class)
		->findByStartsWith($query, $limit);

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

}
