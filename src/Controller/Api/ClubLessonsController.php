<?php

namespace App\Controller\Api;

use App\Entity\Club;
use Hateoas\HateoasBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
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


class ClubLessonsController extends AbstractController
{

    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    
    /**
	 * @Route("/api/club/{uuid}/lessons", name="api_get_club_lessons", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
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
		$clubLessons = $this->container->get('doctrine')->getManager()
			->getRepository(ClubLesson::class)
			->findByClubUuid($uuid);
		if(empty($clubLessons)) {
		    return new Response('Club not found: '.$uuid, Response::HTTP_NOT_FOUND); // 404
		}

		$lessonList = array();
		foreach($clubLessons as &$clubLesson) {
			array_push($lessonList, new ClubLessonView($clubLesson));
		}

		$hateoas = HateoasBuilder::create()->build();
		return new Response(
		    $hateoas->serialize($lessonList, 'json'),
		    Response::HTTP_OK, // 200
		    array('Content-Type' => 'application/hal+json'));
	}

	/**
	 * @Route("/api/club/{club_uuid}/lessons/{lesson_uuid}", name="api_get_club_lesson", methods={"GET"}, requirements={"club_uuid"="[a-z0-9_]{2,64}","lesson_uuid"="[a-zA-Z0-9_]{2,64}"})
	 * @OA\Get(
	 *     operationId="getClubLesson",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/lessons/{lesson_uuid}",
	 *     summary="Give a lesson for a club",
	 *     @OA\Parameter(
	 *         description="UUID of club",
	 *         in="path",
	 *         name="club_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[a-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         description="UUID of lesson",
	 *         in="path",
	 *         name="lesson_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[A-Za-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response="200",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Items(ref="#/components/schemas/ClubLesson")
	 *         )
	 *     ),
	 *     @OA\Response(response="404", description="Club or lesson not found")
	 * )
	 */
	public function getLesson(string $club_uuid, string $lesson_uuid): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $clubs = $doctrine->getManager()
	    ->getRepository(Club::class)
	    ->findBy(['uuid' => $club_uuid]);
	    if(empty($clubs)) {
	        return new Response('Club not found', Response::HTTP_NOT_FOUND); // 404
	    }
	    $club = $clubs[0];
	    $clubLessons = $doctrine->getManager()
    	    ->getRepository(ClubLesson::class)
    	    ->findBy(['uuid' => $lesson_uuid, 'club' => $club]);
	    if(empty($clubLessons)) {
	        return new Response('Lesson not found', Response::HTTP_NOT_FOUND); // 404
	    }
	    
	    $hateoas = HateoasBuilder::create()->build();
	    return new Response(
	        $hateoas->serialize(new ClubLessonView($clubLessons[0]), 'json'),
	        Response::HTTP_OK, // 200
	        array('Content-Type' => 'application/hal+json'));
	}
	
	/**
	 * @Route("/api/club/{club_uuid}/lessons", name="api_create_club_lessons", methods={"POST"}, requirements={"club_uuid"="[a-z0-9_]{2,64}"})
	 * @OA\Post(
	 *     operationId="createClubLesson",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/lessons",
	 *     summary="Create a lesson for a club",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(
	 *         description="UUID of club",
	 *         in="path",
	 *         name="club_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[a-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\Parameter(name="X-ClientId", in="header", required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
	 *     @OA\RequestBody(
	 *         description="Lesson object that needs to be added",
	 *         required=true,
	 *         @OA\JsonContent(ref="#/components/schemas/ClubLessonCreate"),
	 *     ),
	 *     @OA\Response(
	 *         response="201",
	 *         description="Successful",
	 *         @OA\MediaType(
	 *             mediaType="application/hal+json",
	 *             @OA\Schema(ref="#/components/schemas/ClubLesson")
	 *         )
	 *     ),
	 *     @OA\Response(response="400", description="Unvalid data"),
	 *     @OA\Response(response="403", description="Forbidden to create a lesson"),
	 *     @OA\Response(response="404", description="Club or location not found")
	 * )
	 */
	public function createLesson(string $club_uuid, Request $request, SerializerInterface $serializer, TranslatorInterface $translator): Response
	{
	    if(! $this->isGranted(Roles::ROLE_SUPER_ADMIN)
	        && ! $this->isGranted(Roles::ROLE_ADMIN)
	        && ! $this->isGranted(Roles::ROLE_CLUB_MANAGER)) {
	            throw $this->createAccessDeniedException('Access Denied');
        }
        
        $doctrine = $this->container->get('doctrine');
        
        $clubs = $doctrine->getManager()
	        ->getRepository(Club::class)
	        ->findBy(['uuid' => $club_uuid]);
        if(empty($clubs)) {
            return new Response('Club not found', Response::HTTP_NOT_FOUND); // 404
        }
        $club = $clubs[0];
        
        $requestUtil = new RequestUtil($serializer, $translator);
        try {
            $lessonToCreate = $requestUtil->validate($request, ClubLessonCreate::class);
        } catch (ViolationException $e) {
            return new Response(
                json_encode($e->getErrors()),
                Response::HTTP_BAD_REQUEST, // 400
                array('Content-Type' => 'application/hal+json'));
        }
        
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
               return new Response('Location not found', Response::HTTP_NOT_FOUND); // 404
           }
           if(count($locations) > 1) {
               return new Response('Too many locations found, set a \'location_uuid\'', Response::HTTP_BAD_REQUEST); // 404
           }
        } else {
	        $locations = $doctrine->getManager()
    	        ->getRepository(ClubLocation::class)
    	        ->findBy(['uuid' => $locationUuid]);
    	    if(empty($locations)) {
	            return new Response('Location not found', Response::HTTP_NOT_FOUND); // 404
	        }
        }
        
        if($lessonToCreate->getStartTime() > $lessonToCreate->getEndTime()) {
            return new Response('start_time is over end_time !', Response::HTTP_BAD_REQUEST); // 400
        }
        
        $lesson = new ClubLesson();
        $lesson->setUuid($uuid);
        $lesson->setClubLocation($locations[0]);
        $lesson->setClub($club);
        $lesson->setPoint($lessonToCreate->getPoint());
        $lesson->setDiscipline($lessonToCreate->getDiscipline());
        $lesson->setAgeLevel($lessonToCreate->getAgeLevel());
        $lesson->setDayOfWeek($lessonToCreate->getDayOfWeek());
        $lesson->setStartTime($lessonToCreate->getStartTime());
        $lesson->setEndTime($lessonToCreate->getEndTime());
        $lesson->setDescription(StringUtils::defaultOrEmpty($lessonToCreate->getDescription()));
        $doctrine->getManager()->persist($lesson);
        
        $data = ['day' => $lesson->getDayOfWeek(), 'uuid' => $uuid, 'start' => $lesson->getStartTime(), 'discipline' => $lesson->getDiscipline()];
        Events::add($doctrine, Events::CLUB_LESSON_CREATED, $this->getUser(), $request, $data);
        $this->logger->debug('Club lesson created: '.json_encode($data));
        
        $hateoas = HateoasBuilder::create()->build();
        return new Response(
            $hateoas->serialize(new ClubLessonView($lesson), 'json'),
            Response::HTTP_CREATED, // 201
            array('Content-Type' => 'application/hal+json'));
	}
	
	
	/**
	 * @Route("/api/club/{club_uuid}/lessons/{lesson_uuid}", name="api_update_club_lessons", methods={"PATCH"}, requirements={"club_uuid"="[a-z0-9_]{2,64}","lesson_uuid"="[a-zA-Z0-9_]{2,64}"})
	 * @OA\Patch(
	 *     operationId="updateClublesson",
	 *     tags={"Club"},
	 *     path="/api/club/{club_uuid}/lessons/{lesson_uuid}",
	 *     summary="Update a lesson for a club",
	 *     security = {{"basicAuth": {}}},
	 *     @OA\Parameter(name="X-ClientId", in="header", required=true, example="my-client-name", @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")),
	 *     @OA\Parameter(
	 *         description="UUID of club",
	 *         in="path",
	 *         name="club_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[a-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\Parameter(
	 *         description="UUID of lesson",
	 *         in="path",
	 *         name="lesson_uuid",
	 *         required=true,
	 *         @OA\Schema(
	 *             format="string",
	 *             type="string",
	 *             pattern="[A-Za-z0-9_]{2,64}"
	 *         )
	 *     ),
	 *     @OA\RequestBody(
	 *         description="Lesson object that needs to be added",
	 *         required=true,
	 *         @OA\JsonContent(ref="#/components/schemas/ClubLessonUpdate"),
	 *     ),
	 *     @OA\Response(response="204", description="Successful"),
	 *     @OA\Response(response="403", description="Forbidden to update a lesson"),
	 *     @OA\Response(response="404", description="Club or location or lesson not found")
	 * )
	 */
	public function updateLesson(string $club_uuid, string $lesson_uuid, Request $request, SerializerInterface $serializer, TranslatorInterface $translator): Response
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $requestUtil = new RequestUtil($serializer, $translator);
	    try {
	        $lessonToUpdate = $requestUtil->validate($request, ClubLessonUpdate::class);
	    } catch (ViolationException $e) {
	        return new Response(
	            json_encode($e->getErrors()),
	            Response::HTTP_BAD_REQUEST, // 400
	            array('Content-Type' => 'application/hal+json'));
	    }
	    
	    
	    $clubs = $doctrine->getManager()
    	    ->getRepository(Club::class)
    	    ->findBy(['uuid' => $club_uuid]);
	    if(empty($clubs)) {
	        return new Response('Club not found', Response::HTTP_NOT_FOUND); // 404
	    }
	    
	    $club = $clubs[0];
	    $lessons = $doctrine->getManager()
    	    ->getRepository(ClubLesson::class)
    	    ->findBy(['uuid' => $lesson_uuid, 'club' => $club]);
    	if(empty($lessons)) {
	        return new Response('Lesson not found', Response::HTTP_NOT_FOUND); // 404
	    }
	    $lesson = $lessons[0];
	    
	    $uuid = $lessonToUpdate->getUuid();
	    if( ! empty($uuid) && $uuid !== $lesson->getUuid()) {
	        $lessonsUsed = $doctrine->getManager()
    	        ->getRepository(ClubLesson::class)
    	        ->findBy(['uuid' => $uuid]);
	        if(!empty($lessonsUsed)) {
	            return new Response('Lesson UUID already used', Response::HTTP_BAD_REQUEST); // 400
	        }
	    }
	    
	    $locationUuid = $lessonToUpdate->getLocationUuid();
	    if(! empty($locationUuid)) {
	        $locations = $doctrine->getManager()
    	        ->getRepository(ClubLocation::class)
    	        ->findBy(['uuid' => $locationUuid]);
	        if(empty($locations)) {
	            return new Response('Location not found', Response::HTTP_NOT_FOUND); // 404
	        }
	    }
		    
	    $entityUpdater = new EntityUpdater($doctrine, $request, $this->getUser(), Events::CLUB_LESSON_UPDATED, $this->logger);
	    $entityUpdater->update('uuid', $uuid, $lesson->getUuid(), function($v) use($lesson) { $lesson->setUuid($v); });
	    if(! empty($locationUuid)) {
	        $entityUpdater->update('location', $locationUuid, $lesson->getClubLocation(), function($v) use($lesson) { $lesson->setClubLocation($v); });
	    }
	    $entityUpdater->update('point', $lessonToUpdate->getPoint(), $lesson->getPoint(), function($v) use($lesson) { $lesson->setPoint($v); });
	    $entityUpdater->update('discipline', $lessonToUpdate->getDiscipline(), $lesson->getDiscipline(), function($v) use($lesson) { $lesson->setDiscipline($v); });
	    $entityUpdater->update('agelevel', $lessonToUpdate->getAgeLevel(), $lesson->getAgeLevel(), function($v) use($lesson) { $lesson->setAgeLevel($v); });
	    $entityUpdater->update('day', $lessonToUpdate->getDayOfWeek(), $lesson->getDayOfWeek(), function($v) use($lesson) { $lesson->setDayOfWeek($v); });
	    $entityUpdater->update('start', $lessonToUpdate->getStartTime(), $lesson->getStartTime(), function($v) use($lesson) { $lesson->setStartTime($v); });
	    $entityUpdater->update('end', $lessonToUpdate->getEndTime(), $lesson->getEndTime(), function($v) use($lesson) { $lesson->setEndTime($v); });
	    $entityUpdater->update('description', $lessonToUpdate->getDescription(), $lesson->getDescription(), function($v) use($lesson) { $lesson->setDescription($v); });
	    return $entityUpdater->toResponse($lesson, 'Club lesson updated', ['id' => $lesson->getId()]);
	}
	
	
}
