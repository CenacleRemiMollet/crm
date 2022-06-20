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
use App\Util\RequestUtil;
use App\Exception\ViolationException;
use App\Security\Roles;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


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
	    $clubService = new ClubService($this->getDoctrine());
	    $clubViews = $clubService->convertAllActiveToView();

	    $hateoas = HateoasBuilder::create()->build();
	    $json = json_decode($hateoas->serialize($clubViews, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}

	/**
	 * @Route("/api/club/{uuid}", name="api_get_club", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
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
	 *     ),
	 *     @OA\Response(response="404", description="Club not found")
	 * )
	 */
	public function one($uuid)
	{
		$clubs = $this->getDoctrine()->getManager()
			->getRepository(Club::class)
			->findBy(['uuid' => $uuid]);
		$clubView = null;
		if(count($clubs) > 0) {
		    $clubService = new ClubService($this->getDoctrine());
		    $clubViews = $clubService->convertToView($clubs);
			$clubView = $clubViews[0];
		} else {
			return new Response('Club not found: '.$uuid, 404);
		}

		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($clubView, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}

	/**
	 * @Route("/api/club", name="api_club_create", methods={"POST"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 * @IsGranted({"ROLE_ADMIN"})
	 * @OA\Post(
	 *     operationId="createClub",
	 *     tags={"Club"},
	 *     path="/api/club",
	 *     summary="Create a club",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(name="X-ClientId", in="header",  required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
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

		if($this->isGranted(Roles::ROLE_ADMIN)) {
			// ok
		} else {
		    // nok
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
	 *     @OA\Parameter(name="X-ClientId", in="header",  required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
     *     @OA\RequestBody(
     *         description="User object that needs to be added",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ClubUpdate"),
     *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful"
	 *     ),
	 *     @OA\Response(response="404", description="Club not found")
	 * )
	 */
	public function update(Request $request, $uuid, SerializerInterface $serializer, TranslatorInterface $translator)
	{
		// 	TODO
	}

}
