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


class ClubController extends AbstractController
{

	/**
	 * @Route("/api/club", name="api_club_list-active", methods={"GET"})
	 * @OA\Get(
	 *     tags={"Club"},
	 *     path="/api/club",
	 *     summary="List all active clubs",
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Schema(
	 *                 type="array",
	 *                 @OA\Items(ref="#/components/schemas/Club")
	 *             )
	 *         )
	 *     )
	 * )
	 */
	public function listActive()
	{
		$clubs = $this->getDoctrine()->getManager()
			->getRepository(Club::class)
			->findAllActiveWithLocations();

		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($clubs, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}

	/**
	 * @Route("/api/club/{uuid}", name="api_club_one", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Get(
	 *     tags={"Club"},
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
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Schema(ref="#/components/schemas/Club")
	 *         )
	 *     )
	 * )
	 */
	public function one($uuid)
	{
		$clubs = $this->getDoctrine()->getManager()
			->getRepository(Club::class)
			->findBy(['uuid' => $uuid]);
		$output = null;
		if(count($clubs) > 0) {
			$clubloc = $this->getDoctrine()->getManager()
				->getRepository(ClubLocation::class)
				->findByClubs([$clubs[0]]);
			$output = $clubloc[0];
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
	 *     tags={"Club"},
	 *     path="/api/club/{uuid}/logo",
	 *     summary="Give a image logo club",
	 *     @OA\Parameter(
	 *         description="UUID of club",
	 *         in="path",
	 *         name="uuid",
	 *         required=true,
	 *         @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(mediaType="image/gif"),
	 *         @OA\MediaType(mediaType="image/jpeg"),
	 *         @OA\MediaType(mediaType="application/octet-stream")
	 *     )
	 * )
	 */
	public function getLogo($uuid, KernelInterface $appKernel, LoggerInterface $logger)
	{
		$mediaManager = new MediaManager($appKernel, $logger);
		$media = $mediaManager->find('club', $uuid);
		return new BinaryFileResponse($media->getFileOrDefault('assets/clubs/defaultlogo.gif'));
	}

	/**
	 * @Route("/api/club/{uuid}/logo", name="api_club_upload_logo", methods={"POST"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Post(
	 *     tags={"Club"},
	 *     path="/api/club/{uuid}/logo",
	 *     summary="Upload a image logo club",
	 *     @OA\Parameter(
	 *         description="UUID of club",
	 *         in="path",
	 *         name="uuid",
	 *         required=true,
	 *         @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")
	 *     ),
	 *     @OA\RequestBody(
	 *         request="Product",
	 *         required=true,
	 *         description="Logo",
	 *         @OA\MediaType(
	 *            mediaType="multipart/form-data",
	 *            @OA\Schema(@OA\Property(property="file", type="string", format="binary"))
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful"
	 *     )
	 * )
	 */
	public function uploadLogo(Request $request, $uuid, KernelInterface $appKernel, LoggerInterface $logger)
	{
		$token = $request->get("token");
		if (!$this->isCsrfTokenValid('upload', $token)) {
			$logger->info("CSRF failure");
			return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
				['content-type' => 'text/plain']);
		}

		$file = $request->files->get('logo');
		if (empty($file)) {
			return new Response("No file specified",
				Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
		}

		//$filename = $file->getClientOriginalName();
		//$uploader->upload($uploadDir, $file, $filename);
		//$mediaManager = new MediaManager($appKernel, $logger);
		//$mediaManager->upload($uploadDir, $inFile, $category, $filename)

		return new Response("File uploaded",  Response::HTTP_OK,
			['content-type' => 'text/plain']);


		//$media = $mediaManager->find('club', $uuid);
		//return new BinaryFileResponse($media->getFileOrDefault('assets/clubs/defaultlogo.gif'));
	}

		/**
	 * @Route("/api/club/{uuid}/lessons", name="api_club_lessons", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Get(
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
