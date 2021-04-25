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


class ClubSearchController extends AbstractController
{

	/**
	 * @Route("/api/club-around/{zipcode}", name="api_club_around-zipcode", methods={"GET"})
	 * @OA\Get(
	 *     path="/api/club-around/{zipcode}",
	 *     summary="Search all clubs around a zipcode with a distance in kilometers",
	 *     @OA\Parameter(
     *         description="Distance in kilometers",
     *         in="path",
     *         name="d",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
	 *     @OA\Response(response="200", description="Successful")
	 * )
	 */
	public function searhcAroundWithdistance(Request $request, $zipcode)
	{
		$query = $request->query->get('distance');

		$json = "{}";

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}

	
}
