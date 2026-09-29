<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Dao\SearchDao;
use Symfony\Component\HttpFoundation\Response;
use Hateoas\HateoasBuilder;
use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;
use App\Model\SearchResultsView;
use App\Model\Pagination;
use App\Util\Page\Pageable;

class SearchController extends AbstractController
{

	public function __construct(private readonly \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
    }
    #[OA\Get(
	    path: '/api/search',
	    operationId: 'search',
	    summary: 'Search',
	    tags: ['Search'],
	    parameters: [
	        new OA\Parameter(
	            name: 'q',
	            description: 'query',
	            in: 'query',
	            required: true,
	            schema: new OA\Schema(type: 'string')
	        ),
	        new OA\Parameter(
	            name: 'page',
	            description: 'page number',
	            in: 'query',
	            required: false,
	            schema: new OA\Schema(type: 'string', format: 'string')
	        ),
	        new OA\Parameter(
	            name: 'n',
	            description: 'max number of result in a page',
	            in: 'query',
	            required: false,
	            schema: new OA\Schema(type: 'string', format: 'string')
	        )
	    ],
	    responses: [
	        new OA\Response(response: '200', description: 'Successful search')
	    ]
	)]
    #[Route(path: '/api/search', name: 'api_search', methods: ['GET'])]
    public function search(Request $request, LoggerInterface $logger)
	{
	    $pageable = Pageable::of($request);
		$query = trim($request->query->get('q', ''));
		$logger->debug('query: ['.$query.']');
		$searched = array();
		if(strlen($query) >= 2) {
			$search = new SearchDao($this->managerRegistry->getManager(), $this->container->get('security.authorization_checker'));
			$searched = $search->search($query, $this->getUser(), $pageable);
		}

		$pagination = new Pagination(
		    $this->generateUrl('api_search'),
		    $pageable,
		    $searched['results'],
		    $searched['hasmore']);
		$output = new SearchResultsView($query, $searched['results'], $pagination);
		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($output, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}
}
