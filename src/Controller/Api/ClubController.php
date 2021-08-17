<?php

namespace App\Controller\Api;

use App\Entity\Club;
use Hateoas\HateoasBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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


class ClubController extends AbstractController
{

	/**
	 * @Route("/api/club", name="api_club_list-active", methods={"GET"})
	 * @OA\Get(
	 *     path="/api/club",
	 *     summary="List all active clubs",
	 *     @OA\Response(response="200", description="Successful")
	 * )
	 */
	public function listActive()
	{
		$clubs = $this->getDoctrine()->getManager()
			->getRepository(Club::class)
			->findAllActiveWithLocations();

		$output = array('clubs' => $clubs);
		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($output, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}

	/**
	 * @Route("/api/club/{uuid}", name="api_club_one", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Get(
	 *     path="/api/club/{uuid}",
	 *     summary="Give a club",
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
	 *     @OA\Response(response="200", description="Successful")
	 * )
	 */
	public function one($uuid)
	{
		$clubs = $this->getDoctrine()->getManager()
			->getRepository(Club::class)
			->findBy(['uuid' => $uuid]);
		$output = [];
		if(count($clubs) > 0) {
			$clubloc = $this->getDoctrine()->getManager()
				->getRepository(ClubLocation::class)
				->findByClubs([$clubs[0]]);
			$output = array('club' => $clubloc[0]);
		} else {
			return new Response('Club not found: '.$uuid, 404);
		}

		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($output, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}

	/**
	 * @Route("/api/club/{uuid}/logo", name="api_club_one_logo", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Get(
	 *     path="/api/club/{uuid}/logo",
	 *     summary="Give a club",
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
	 *     @OA\Response(response="200", description="Successful")
	 * )
	 */
	public function getLogo($uuid, KernelInterface $appKernel, LoggerInterface $logger)
	{
		$mediaManager = new MediaManager($appKernel, $logger);
		$media = $mediaManager->find('club', $uuid);
		return new BinaryFileResponse($media->getFileOrDefault('assets/clubs/defaultlogo.gif'));
	}

	/**
	 * @Route("/api/club/{uuid}/lessons", name="api_club_lessons", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Get(
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
	 *     @OA\Response(response="200", description="Successful")
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
