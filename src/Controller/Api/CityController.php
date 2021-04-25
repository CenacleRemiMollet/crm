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

	/**
	 * @Route("/api/city", name="api_city", methods={"GET"})
	 * @OA\Parameter(
	 *    @OA\Schema(type="string"),
	 *    in="path",
	 *    allowReserved=true,
	 *    name="q",
	 *    parameter="q"
	 * )
	 * @OA\Get(
	 *     path="/api/city",
	 *     summary="Search city",
	 *     @OA\Parameter(
	 *         ref="#/components/parameters/q"
	 *     ),
	 *     @OA\Response(response="200", description="Successful")
	 * )
	 */
	public function searchCity(Request $request)
	{
		$query = $request->query->get('q');
		
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

}
