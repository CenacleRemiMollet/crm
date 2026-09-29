<?php

namespace App\Controller\Api;

use App\Entity\Club;
use Hateoas\HateoasBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use App\Entity\ClubLesson;
use App\Model\ClubLessonView;
use App\Security\Roles;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Util\RequestUtil;
use App\Model\ClubLessonCreate;
use App\Exception\ViolationException;
use App\Util\StringUtils;
use App\Entity\ClubLocation;
use App\Entity\Events;
use App\Model\ClubLessonUpdate;
use App\Security\ClubAccess;
use App\Entity\EntityFinder;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use App\Exception\CRMException;


class ClubLessonsController extends AbstractController
{
    use \App\Controller\DoctrineSubscriberTrait;


    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    
    #[OA\Get(
        path: '/api/club/{club_uuid}/lessons',
        operationId: 'getClubLessons',
        summary: 'Give some hours',
        tags: ['Club'],
        parameters: [
            new OA\Parameter(
                name: 'club_uuid',
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
                    new OA\MediaType(
                        mediaType: 'application/hal+json',
                        schema: new OA\Schema(type: 'array', items: new OA\Items(ref: '#/components/schemas/ClubLesson'))
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
    #[Route(path: '/api/club/{club_uuid}/lessons', name: 'api_get_club_lessons', methods: ['GET'], requirements: ['club_uuid' => '[a-z0-9_]{2,64}'])]
    public function getLessons($club_uuid)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    /** @var Club $club */
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubLessons = $this->container->get('doctrine')->getManager()
			->getRepository(ClubLesson::class)
			->findBy(['club' => $club]);

		$lessonList = array();
		foreach($clubLessons as &$clubLesson) {
		    array_push($lessonList, new ClubLessonView($club, $clubLesson));
		}

		$hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize($lessonList, 'json'),
		    Response::HTTP_OK, // 200
		    array('Content-Type' => 'application/hal+json'));
	}

	#[OA\Get(
	    path: '/api/club/{club_uuid}/lessons/{lesson_uuid}',
	    operationId: 'getClubLesson',
	    summary: 'Give a lesson for a club',
	    tags: ['Club'],
	    parameters: [
	        new OA\Parameter(
	            name: 'club_uuid',
	            description: 'UUID of club',
	            in: 'path',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}')
	        ),
	        new OA\Parameter(
	            name: 'lesson_uuid',
	            description: 'UUID of lesson',
	            in: 'path',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[A-Za-z0-9_]{2,64}')
	        )
	    ],
	    responses: [
	        new OA\Response(
	            response: '200',
	            description: 'Successful',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/ClubLesson')
	                )
	            ]
	        ),
	        new OA\Response(
	            response: '404',
	            description: 'Club or lesson not found',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        )
	    ]
	)]
    #[Route(path: '/api/club/{club_uuid}/lessons/{lesson_uuid}', name: 'api_get_club_lesson', methods: ['GET'], requirements: ['club_uuid' => '[a-z0-9_]{2,64}', 'lesson_uuid' => '[a-zA-Z0-9_]{2,64}'])]
    public function getLesson(string $club_uuid, string $lesson_uuid): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    /** @var Club $club */
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    $clubLesson = $entityFinder->findOneByOrThrow(ClubLesson::class, ['uuid' => $lesson_uuid, 'club' => $club]); // 404
	    
	    $hateoas = HateoasBuilder::create()->build();
	    return new Response(
	        $hateoas->serialize(new ClubLessonView($club, $clubLesson), 'json'),
	        Response::HTTP_OK, // 200
	        array('Content-Type' => 'application/hal+json'));
	}
	
	
	#[OA\Post(
	    path: '/api/club/{club_uuid}/lessons',
	    operationId: 'createClubLesson',
	    summary: 'Create a lesson for a club',
	    security: [['basicAuth' => []]],
	    requestBody: new OA\RequestBody(
	        description: 'Lesson object that needs to be added',
	        required: true,
	        content: new OA\JsonContent(ref: '#/components/schemas/ClubLessonCreate')
	    ),
	    tags: ['Club'],
	    parameters: [
	        new OA\Parameter(
	            name: 'club_uuid',
	            description: 'UUID of club',
	            in: 'path',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}')
	        ),
	        new OA\Parameter(
	            name: 'X-ClientId',
	            in: 'header',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}'),
	            example: 'my-client-name'
	        )
	    ],
	    responses: [
	        new OA\Response(
	            response: '201',
	            description: 'Successful',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/ClubLesson')
	                )
	            ]
	        ),
	        new OA\Response(
	            response: '400',
	            description: 'Unvalid data',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        ),
	        new OA\Response(
	            response: '403',
	            description: 'Forbidden to create a lesson',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        ),
	        new OA\Response(
	            response: '404',
	            description: 'Club or location not found',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        )
	    ]
	)]
    #[Route(path: '/api/club/{club_uuid}/lessons', name: 'api_create_club_lessons', methods: ['POST'], requirements: ['club_uuid' => '[a-z0-9_]{2,64}'])]
    public function createLesson(string $club_uuid, Request $request, SerializerInterface $serializer, TranslatorInterface $translator): Response
	{
        $doctrine = $this->container->get('doctrine');
        
        $entityFinder = new EntityFinder($doctrine);
        /** @var Club $club */
        $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
         
        $clubAccess = new ClubAccess($this->container, $this->logger);
        $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
        
        $requestUtil = new RequestUtil($serializer, $translator);
        $lessonToCreate = $requestUtil->validate($request, ClubLessonCreate::class); // 400
        
        $uuid = $lessonToCreate->getUuid();
        if($uuid == null || trim($uuid) === '') {
            $uuid = StringUtils::random_str(16);
        }
        
        $locationUuid = $lessonToCreate->getLocationUuid();
        if(empty($locationUuid)) {
            $locations = $doctrine->getManager()
               ->getRepository(ClubLocation::class)
               ->findBy(['club' => $club]);
           if(empty($locations)) {
               throw $this->createNotFoundException('Location not found'); // 404
           }
           if(count($locations) > 1) {
               throw new CRMException(Response::HTTP_BAD_REQUEST, 'Too many locations found, set a \'location_uuid\'');
           }
           $location = $locations[0];
        } else {
            $location = $entityFinder->findOneByOrThrow(ClubLocation::class, ['uuid' => $locationUuid, 'club' => $club]); // 404
        }
        
        if($lessonToCreate->getStartTime() > $lessonToCreate->getEndTime()) {
            throw new CRMException(Response::HTTP_BAD_REQUEST, 'start_time is after end_time !', ['start_time' => 'start_time is after end_time']); // 400
        }
        
        $lesson = new ClubLesson();
        $lesson->setUuid($uuid);
        $lesson->setClubLocation($location);
        $lesson->setClub($club);
        $lesson->setPoint($lessonToCreate->getPoint());
        $lesson->setDiscipline($lessonToCreate->getDiscipline());
        $lesson->setAgeLevel($lessonToCreate->getAgeLevel());
        $lesson->setDayOfWeek($lessonToCreate->getDayOfWeek());
        $lesson->setStartTime($lessonToCreate->getStartTime());
        $lesson->setEndTime($lessonToCreate->getEndTime());
        $lesson->setDescription(StringUtils::defaultOrEmpty($lessonToCreate->getDescription()));
        $lesson->setColor(empty($lessonToCreate->getColor()) ? null : strtoupper($lessonToCreate->getColor()));
        $doctrine->getManager()->persist($lesson);
        
        $data = ['day' => $lesson->getDayOfWeek(), 'uuid' => $uuid, 'start' => $lesson->getStartTime(), 'discipline' => $lesson->getDiscipline()];
        Events::add($doctrine, Events::CLUB_LESSON_CREATED, $this->getUser(), $request, $data);
        $this->logger->debug('Club lesson created: '.json_encode($data));
        
        $hateoas = HateoasBuilder::create()->build();
        return new Response(
            $hateoas->serialize(new ClubLessonView($club, $lesson), 'json'),
            Response::HTTP_CREATED, // 201
            array('Content-Type' => 'application/hal+json'));
	}
	
	
	#[OA\Patch(
	    path: '/api/club/{club_uuid}/lessons/{lesson_uuid}',
	    operationId: 'updateClubLesson',
	    summary: 'Update a lesson for a club',
	    security: [['basicAuth' => []]],
	    requestBody: new OA\RequestBody(
	        description: 'Lesson object that needs to be added',
	        required: true,
	        content: new OA\JsonContent(ref: '#/components/schemas/ClubLessonUpdate')
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
	            name: 'club_uuid',
	            description: 'UUID of club',
	            in: 'path',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}')
	        ),
	        new OA\Parameter(
	            name: 'lesson_uuid',
	            description: 'UUID of lesson',
	            in: 'path',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[A-Za-z0-9_]{2,64}')
	        )
	    ],
	    responses: [
	        new OA\Response(response: '204', description: 'Successful'),
	        new OA\Response(
	            response: '400',
	            description: 'Unvalid data',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        ),
	        new OA\Response(
	            response: '403',
	            description: 'Forbidden to update a lesson',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        ),
	        new OA\Response(
	            response: '404',
	            description: 'Club or location or lesson not found',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        )
	    ]
	)]
    #[Route(path: '/api/club/{club_uuid}/lessons/{lesson_uuid}', name: 'api_update_club_lessons', methods: ['PATCH'], requirements: ['club_uuid' => '[a-z0-9_]{2,64}', 'lesson_uuid' => '[a-zA-Z0-9_]{2,64}'])]
    public function updateLesson(string $club_uuid, string $lesson_uuid, Request $request, SerializerInterface $serializer, TranslatorInterface $translator): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $requestUtil = new RequestUtil($serializer, $translator);
        $lessonToUpdate = $requestUtil->validate($request, ClubLessonUpdate::class); // 400
	    
        $entityFinder = new EntityFinder($doctrine);
        /** @var Club $club */
        $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $lesson = $entityFinder->findOneByOrThrow(ClubLesson::class, ['uuid' => $lesson_uuid, 'club' => $club]); // 404
	    
	    $uuid = $lessonToUpdate->getUuid();
	    if( ! empty($uuid) && $uuid !== $lesson->getUuid()) {
	        $entityFinder->findNoneByOrThrow(ClubLesson::class, ['uuid' => $uuid],
	            function() use($uuid) {
	                throw new CRMException(Response::HTTP_BAD_REQUEST, 'Lesson UUID already used: '.$uuid, ['uuid' => 'UUID already used']); // 400
	            });
	    }
	    
	    $locationUuid = $lessonToUpdate->getLocationUuid();
	    $location = null;
	    if(! empty($locationUuid)) {
	        // location must exist in the same club
	        $location = $entityFinder->findOneByOrThrow(ClubLocation::class, ['uuid' => $locationUuid, 'club' => $club]); // 404
	    }
	    
	    if($lessonToUpdate->getStartTime() !== null && $lessonToUpdate->getEndTime() != null && $lessonToUpdate->getStartTime() > $lessonToUpdate->getEndTime()) {
	        throw new CRMException(Response::HTTP_BAD_REQUEST, 'start_time is after end_time !', ['start_time' => 'start_time is after end_time']); // 400
	    }
	    
		    
	    $entityUpdater = new EntityUpdater($doctrine, $request, $this->getUser(), Events::CLUB_LESSON_UPDATED, $this->logger);
	    $entityUpdater->update('uuid', $uuid, $lesson->getUuid(), function($v) use($lesson) { $lesson->setUuid($v); });
	    if(! empty($locationUuid)) {
	        $entityUpdater->update('location', $location, $lesson->getClubLocation(), function($v) use($lesson) { $lesson->setClubLocation($v); });
	    }
	    $entityUpdater->update('point', $lessonToUpdate->getPoint(), $lesson->getPoint(), function($v) use($lesson) { $lesson->setPoint($v); });
	    $entityUpdater->update('discipline', $lessonToUpdate->getDiscipline(), $lesson->getDiscipline(), function($v) use($lesson) { $lesson->setDiscipline($v); });
	    $entityUpdater->update('agelevel', $lessonToUpdate->getAgeLevel(), $lesson->getAgeLevel(), function($v) use($lesson) { $lesson->setAgeLevel($v); });
	    $entityUpdater->update('day', $lessonToUpdate->getDayOfWeek(), $lesson->getDayOfWeek(), function($v) use($lesson) { $lesson->setDayOfWeek($v); });
	    $entityUpdater->update('start', $lessonToUpdate->getStartTime(), $lesson->getStartTime(), function($v) use($lesson) { $lesson->setStartTime($v); });
	    $entityUpdater->update('end', $lessonToUpdate->getEndTime(), $lesson->getEndTime(), function($v) use($lesson) { $lesson->setEndTime($v); });
	    $entityUpdater->update('description', $lessonToUpdate->getDescription(), $lesson->getDescription(), function($v) use($lesson) { $lesson->setDescription($v); });
	    if($lessonToUpdate->getColor() !== null) { // null = unchanged, '' = back to the discipline color
	        $color = $lessonToUpdate->getColor() === '' ? null : strtoupper($lessonToUpdate->getColor());
	        if($color !== $lesson->getColor()) {
	            $entityUpdater->update('color', $color ?? '', $lesson->getColor() ?? '', function($v) use($lesson, $color) { $lesson->setColor($color); });
	        }
	    }
	    return $entityUpdater->toResponse($lesson, 'Club lesson updated', ['id' => $lesson->getId()]);
	}
	

	#[OA\Delete(
	    path: '/api/club/{club_uuid}/lessons/{lesson_uuid}',
	    operationId: 'deleteClubLesson',
	    summary: 'Delete a lesson for a club',
	    security: [['basicAuth' => []]],
	    tags: ['Club'],
	    parameters: [
	        new OA\Parameter(
	            name: 'club_uuid',
	            description: 'UUID of club',
	            in: 'path',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}')
	        ),
	        new OA\Parameter(
	            name: 'lesson_uuid',
	            description: 'UUID of lesson',
	            in: 'path',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[A-Za-z0-9_]{2,64}')
	        ),
	        new OA\Parameter(
	            name: 'X-ClientId',
	            in: 'header',
	            required: true,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}'),
	            example: 'my-client-name'
	        )
	    ],
	    responses: [
	        new OA\Response(response: '204', description: 'Successful'),
	        new OA\Response(
	            response: '403',
	            description: 'Forbidden to delete a lesson',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        ),
	        new OA\Response(
	            response: '404',
	            description: 'Club or lesson not found',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(ref: '#/components/schemas/Error')
	                )
	            ]
	        )
	    ]
	)]
    #[Route(path: '/api/club/{club_uuid}/lessons/{lesson_uuid}', name: 'api_delete_club_lessons', methods: ['DELETE'], requirements: ['club_uuid' => '[a-z0-9_]{2,64}', 'lesson_uuid' => '[a-zA-Z0-9_]{2,64}'])]
    public function deleteLesson(Request $request, string $club_uuid, string $lesson_uuid): Response
	{
	    $doctrine = $this->container->get('doctrine');

	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404

	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403

	    $clubLesson = $entityFinder->findOneByOrThrow(ClubLesson::class, ['uuid' => $lesson_uuid, 'club' => $club]); // 404

	    $doctrine->getManager()->remove($clubLesson);

	    $data = ['club_uuid' => $club_uuid, 'lesson_uuid' => $lesson_uuid, 'day' => $clubLesson->getDayOfWeek(), 'start' => $clubLesson->getStartTime()];
	    Events::add($doctrine, Events::CLUB_LESSON_DELETED, $this->getUser(), $request, $data);
	    $this->logger->debug('Club lesson deleted: '.json_encode($data));

	    return new Response('', Response::HTTP_NO_CONTENT); // 204
	}
}
