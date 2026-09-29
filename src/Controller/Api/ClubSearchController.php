<?php

namespace App\Controller\Api;

use App\Entity\Club;
use Hateoas\HateoasBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\ClubLocation;
use OpenApi\Attributes as OA;
use App\Media\MediaManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Entity\ClubLesson;
use App\Model\ClubLocationView;
use App\Entity\City;
use App\Model\CityModel;
use App\Controller\ControllerUtils;
use App\Service\ClubService;


class ClubSearchController extends AbstractController
{
    use \App\Controller\DoctrineSubscriberTrait;


	private $logger;

	public function __construct(LoggerInterface $logger, private readonly \Doctrine\Persistence\ManagerRegistry $managerRegistry)
	{
		$this->logger = $logger;
	}

	#[OA\Get(
	    path: '/api/clubsearch',
	    operationId: 'searchClub',
	    summary: 'Search clubs',
	    tags: ['Club'],
	    parameters: [
	        new OA\Parameter(
	            name: 'zc',
	            description: 'Zip code',
	            in: 'query',
	            required: false,
	            schema: new OA\Schema(type: 'string', format: 'string', pattern: '\\d{4,6}')
	        ),
	        new OA\Parameter(
	            name: 'd',
	            description: 'Distance in kilometers',
	            in: 'query',
	            required: false,
	            schema: new OA\Schema(type: 'integer', default: 5)
	        ),
	        new OA\Parameter(
	            name: 'dis',
	            description: 'Disciplines separated by comma',
	            in: 'query',
	            required: false,
	            schema: new OA\Schema(type: 'string', format: 'string')
	        ),
	        new OA\Parameter(
	            name: 'days',
	            description: 'Days of week separated by comma',
	            in: 'query',
	            required: false,
	            schema: new OA\Schema(type: 'string', format: 'string')
	        )
	    ],
	    responses: [
	        new OA\Response(
	            response: '200',
	            description: 'Successful',
	            content: [
	                new OA\MediaType(
	                    mediaType: 'application/hal+json',
	                    schema: new OA\Schema(type: 'array', items: new OA\Items(ref: '#/components/schemas/ClubLocation'))
	                )
	            ]
	        )
	    ]
	)]
    #[Route(path: '/api/clubsearch', name: 'api_club_search', methods: ['GET'])]
    public function search(Request $request)
	{
	    $zipcode = $request->query->get('zc', '');
	    $distance = $request->query->get('d', 5);
	    $disciplines = ControllerUtils::parseDisciplines($request->query->get('dis', ''));
	    $days = ControllerUtils::parseDays($request->query->get('days', ''));
	    
	    $clubLocations = $this->managerRegistry->getManager()
	    ->getRepository(ClubLocation::class)
	    ->findByZipcodeAndDistance($zipcode, $distance, $disciplines, $days, true);
	    
	    $this->logger->debug('Search club around '.$zipcode.' in '.$distance.' km : '.count($clubLocations).' club(s)');
	    
	    //$clubLocationByIds = array();
	    $clubLocationIds = array();
	    foreach ($clubLocations as &$clubLocation) {
	        //$clubLocationByIds[$clubLocation->getId()] = new ClubLocationView($clubLocation);
	        array_push($clubLocationIds, $clubLocation->getId());
	    }
	    
	    $clubs = $clubLocations = $this->managerRegistry->getManager()
    	    ->getRepository(Club::class)
    	    ->findByClubLocationIds($clubLocationIds);
	    
	    $clubService = new ClubService($this->container->get('doctrine'));
	    $clubViews = $clubService->convertToView($clubs);
	    
	    
	    $hateoas = HateoasBuilder::create()->build();
	    $json = json_decode($hateoas->serialize($clubViews, 'json'));
	    
	    return new Response(json_encode($json), 200, array(
	        'Content-Type' => 'application/hal+json'
	    ));
	}
	
}
