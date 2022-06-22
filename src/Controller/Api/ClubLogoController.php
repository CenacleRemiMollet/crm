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
use App\Entity\EntityFinder;
use App\Security\ClubAccess;
use App\Exception\CRMException;


class ClubLogoController extends AbstractController
{

    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
	 * @Route("/api/club/{uuid}/logo", name="api_club_get_logo", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
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
	public function getLogo($uuid, KernelInterface $appKernel)
	{
		$mediaManager = new MediaManager($appKernel, $this->logger);
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
	 *     @OA\Parameter(name="X-ClientId", in="header", required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
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
	 *     @OA\Response(response="200", description="Successful"),
	 *     @OA\Response(response="403", description="Forbidden to update a club", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="404", description="Club not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error"))),
	 *     @OA\Response(response="422", description="Logo file not found", @OA\MediaType(mediaType="application/hal+json", @OA\Schema(ref="#/components/schemas/Error")))
	 * )
	 */
	public function uploadLogo(Request $request, $uuid, KernelInterface $appKernel)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $uuid]); // 404

	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
		$file = $request->files->get('logo');
		if (empty($file)) {
		    throw new CRMException(Response::HTTP_UNPROCESSABLE_ENTITY, 'No file specified');
		}

		$mediaManager = new MediaManager($appKernel, $this->logger);
		$newFileName = $mediaManager->upload('club', $uuid, $file);
		if($newFileName !== $club->getLogo()) {
			$previousFileName = $club->getLogo();
			$club->setLogo($newFileName);
			$doctrine->getManager()->flush();

			$mediaManager->delete('club', $previousFileName);
		}

		return new Response(
		    "File uploaded",
		    Response::HTTP_OK,
			['content-type' => 'text/plain']);
	}



}
