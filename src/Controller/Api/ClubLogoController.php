<?php

namespace App\Controller\Api;

use App\Entity\Club;
use Hateoas\HateoasBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\ClubLocation;
use OpenApi\Attributes as OA;
use App\Media\MediaManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Entity\ClubLesson;
use App\Model\ClubCreate;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\ClubService;
use App\Entity\EntityFinder;
use App\Security\ClubAccess;
use App\Exception\CRMException;
use Symfony\Component\Finder\SplFileInfo;


class ClubLogoController extends AbstractController
{
    use \App\Controller\DoctrineSubscriberTrait;


    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    #[OA\Get(
        path: '/api/club/{uuid}/logo',
        operationId: 'getClubLogo',
        summary: 'Give an image logo club',
        tags: ['Club'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                description: 'UUID of club',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}')
            )
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Successful',
                content: [
                    new OA\MediaType(mediaType: 'image/gif'),
                    new OA\MediaType(mediaType: 'image/jpeg'),
                    new OA\MediaType(mediaType: 'application/octet-stream')
                ]
            )
        ]
    )]
    #[Route(path: '/api/club/{uuid}/logo', name: 'api_club_get_logo', methods: ['GET'], requirements: ['uuid' => '[a-z0-9_]{2,64}'])]
    public function getLogo($uuid, KernelInterface $appKernel)
	{
		$mediaManager = new MediaManager($appKernel, $this->logger);
		$media = $mediaManager->find(MediaManager::MEDIA_FOLDER_CLUB_LOGO, $uuid);
		return new BinaryFileResponse($media->getFileOrDefault('assets/club/default_logo.gif'));
	}

	#[OA\Post(
	    path: '/api/club/{uuid}/logo',
	    operationId: 'updateClubLogo',
	    summary: 'Upload an image logo club',
	    security: [['basicAuth' => []]],
	    requestBody: new OA\RequestBody(
	        request: 'Logo',
	        description: 'Logo',
	        required: true,
	        content: [
	            new OA\MediaType(
	                mediaType: 'multipart/form-data',
	                schema: new OA\Schema(
	                    properties: [
	                        new OA\Property(property: 'logo', type: 'string', format: 'binary')
	                    ]
	                )
	            )
	        ]
	    ),
	    tags: ['Club'],
	    parameters: [
	        new OA\Parameter(
	            name: 'X-ClientId',
	            in: 'header',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}'),
	            example: 'my-client-name'
	        ),
	        new OA\Parameter(
	            name: 'uuid',
	            description: 'UUID of club',
	            in: 'path',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}')
	        )
	    ],
	    responses: [
	        new OA\Response(response: '200', description: 'Successful'),
	        new OA\Response(
	            response: '403',
	            description: 'Forbidden to update a club',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        ),
	        new OA\Response(
	            response: '404',
	            description: 'Club not found',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        ),
	        new OA\Response(
	            response: '422',
	            description: 'Logo file not found',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        )
	    ]
	)]
    #[Route(path: '/api/club/{uuid}/logo', name: 'api_club_upload_logo', methods: ['POST'], requirements: ['uuid' => '[a-z0-9_]{2,64}'])]
    public function uploadLogo(Request $request, $uuid, KernelInterface $appKernel)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    /** @var Club $club */
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $uuid]); // 404

	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    /** @var UploadedFile $file **/
		$file = $request->files->get('logo');
		if (empty($file)) {
		    throw new CRMException(Response::HTTP_UNPROCESSABLE_ENTITY, 'No file specified');
		}

		$mediaManager = new MediaManager($appKernel, $this->logger);
		$newFileName = $mediaManager->upload(MediaManager::MEDIA_FOLDER_CLUB_LOGO, $uuid, $file);
		if($newFileName !== $club->getLogo()) {
			$previousFileName = $club->getLogo();
			$club->setLogo($newFileName);
			$doctrine->getManager()->flush();
			$mediaManager->delete(MediaManager::MEDIA_FOLDER_CLUB_LOGO, $previousFileName);
		}

		return new Response(
		    "File uploaded",
		    Response::HTTP_OK,
			['content-type' => 'text/plain']);
	}


	#[OA\Delete(
	    path: '/api/club/{uuid}/logo',
	    operationId: 'deleteClubLogo',
	    summary: 'Delete the image logo club',
	    security: [['basicAuth' => []]],
	    tags: ['Club'],
	    parameters: [
	        new OA\Parameter(
	            name: 'X-ClientId',
	            in: 'header',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}'),
	            example: 'my-client-name'
	        ),
	        new OA\Parameter(
	            name: 'uuid',
	            description: 'UUID of club',
	            in: 'path',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}')
	        )
	    ],
	    responses: [
	        new OA\Response(response: '204', description: 'Successful'),
	        new OA\Response(
	            response: '403',
	            description: 'Forbidden to delete a logo',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        ),
	        new OA\Response(
	            response: '404',
	            description: 'Club not found',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        )
	    ]
	)]
    #[Route(path: '/api/club/{uuid}/logo', name: 'api_club_delete_logo', methods: ['DELETE'], requirements: ['uuid' => '[a-z0-9_]{2,64}'])]
    public function deleteLogo($uuid, KernelInterface $appKernel)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    /** @var Club $club */
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $mediaManager = new MediaManager($appKernel, $this->logger);
	    $mediaManager->delete(MediaManager::MEDIA_FOLDER_CLUB_LOGO, $uuid);
	    
	    return new Response(
	        "",
	        Response::HTTP_NO_CONTENT,
	        ['content-type' => 'text/plain']);
	}
}
