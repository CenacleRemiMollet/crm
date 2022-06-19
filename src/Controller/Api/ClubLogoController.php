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


class ClubLogoController extends AbstractController
{

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



}
