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


class ClubLessonsController extends AbstractController
{

	/**
	 * @Route("/api/club/{uuid}/lessons", name="api_club_lessons", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Get(
	 *     operationId="getClubLessons",
	 *     tags={"Club"},
	 *     path="/api/club/{uuid}/lessons",
	 *     summary="Give some hours",
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
	 *                 @OA\Items(ref="#/components/schemas/ClubLesson")
	 *             )
	 *         )
	 *     )
	 * )
	 */
	public function getLessons($uuid)
	{
		$clubLessons = $this->getDoctrine()->getManager()
			->getRepository(ClubLesson::class)
			->findByClubUuid($uuid);
		if(count($clubLessons) == 0) {
			return new Response('Club not found: '.$uuid, 404);
		}

		$lessonList = array();
		foreach($clubLessons as &$clubLesson) {
			array_push($lessonList, new ClubLessonView($clubLesson));
		}

		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($lessonList, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}

}
