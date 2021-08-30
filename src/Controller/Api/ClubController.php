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


class ClubController extends AbstractController
{

	/**
	 * @Route("/api/club", name="api_club_list-active", methods={"GET"})
	 * @OA\Get(
	 *     operationId="getActiveClubList",
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
	 *     operationId="getClub",
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
	 * @Route("/api/club/", name="api_club_create", methods={"POST"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @IsGranted({"ROLE_ADMIN", "ROLE_CLUB_MANAGER"})
	 * @OA\Post(
	 *     operationId="createClub",
	 *     tags={"Club"},
	 *     path="/api/club",
	 *     summary="Create a club",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(name="X-ClientId", in="header",  required=true, @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
     *     @OA\RequestBody(
     *         description="User object that needs to be added",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ClubCreate"),
     *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful"
	 *     )
	 * )
	 */
	public function create(Request $request, SerializerInterface $serializer, TranslatorInterface $translator)
	{
		$requestUtil = new RequestUtil($serializer, $translator);
		try {
			$clubToCreate = $requestUtil->validate($request, ClubCreate::class);
		} catch (ViolationException $e) {
			return ShortResponse::error("data", $e->getErrors())
				->setStatusCode(Response::HTTP_BAD_REQUEST);
		}

		$account = $this->getUser();

		if($this->isGranted(\Roles::ROLE_ADMIN)) {
			// ok
		} elseif($this->isGranted(\Roles::ROLE_CLUB_MANAGER)) {
			//$account
		} else {
			return ShortResponse::error("role", "")
				->setStatusCode(Response::HTTP_FORBIDDEN);
		}

		// 	TODO
	}

	/**
	 * @Route("/api/club/{uuid}", name="api_club_update", methods={"PUT"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Put(
	 *     operationId="updateClub",
	 *     tags={"Club"},
	 *     path="/api/club/{uuid}",
	 *     summary="Update a club",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(name="X-ClientId", in="header",  required=true, @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
     *     @OA\RequestBody(
     *         description="User object that needs to be added",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ClubUpdate"),
     *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful"
	 *     )
	 * )
	 */
	public function update(Request $request, $uuid, SerializerInterface $serializer, TranslatorInterface $translator)
	{
		// 	TODO
	}

	/**
	 * @Route("/api/club/{uuid}/logo", name="api_club_one_logo", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Get(
	 *     operationId="getClubLogo",
	 *     tags={"Club"},
	 *     path="/api/club/{uuid}/logo",
	 *     summary="Give an image logo club",
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
	 *     operationId="updateClubLogo",
	 *     tags={"Club"},
	 *     path="/api/club/{uuid}/logo",
	 *     summary="Upload an image logo club",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(name="X-ClientId", in="header",  required=true, @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
	 *     @OA\Parameter(
	 *         description="UUID of club",
	 *         in="path",
	 *         name="uuid",
	 *         required=true,
	 *         @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")
	 *     ),
	 *     @OA\RequestBody(
	 *         request="Logo",
	 *         required=true,
	 *         description="Logo",
	 *         @OA\MediaType(
	 *            mediaType="multipart/form-data",
	 *            @OA\Schema(@OA\Property(property="logo", type="string", format="binary"))
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
		$clubs = $this->getDoctrine()->getManager()
			->getRepository(Club::class)
			->findBy(['uuid' => $uuid]);
		if(count($clubs) == 0) {
			return new Response("Club not found",
				Response::HTTP_NOT_FOUND, ['content-type' => 'text/plain']);
		}


		$file = $request->files->get('logo');
		if (empty($file)) {
			return new Response("No file specified",
				Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
		}

		$mediaManager = new MediaManager($appKernel, $logger);
		$newFileName = $mediaManager->upload('club', $uuid, $file);
		$club = $clubs[0];
		if($newFileName !== $club->getLogo()) {
			$previousFileName = $club->getLogo();
			$club->setLogo($newFileName);
			$this->getDoctrine()->getManager()->flush();

			$mediaManager->delete('club', $previousFileName);
		}

		return new Response("File uploaded",  Response::HTTP_OK,
			['content-type' => 'text/plain']);
	}

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
