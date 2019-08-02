<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Dao\SearchDao;
use Symfony\Component\HttpFoundation\Response;
use Hateoas\HateoasBuilder;
use Psr\Log\LoggerInterface;
use App\Util\Pager;
use OpenApi\Annotations as OA;
use App\Model\SearchResultsView;
use App\Model\Pagination;

class SearchController extends AbstractController
{

	/**
	 * @Route("/api/search", name="api_search", methods={"GET"})
	 * @OA\Get(
	 *     path="/api/search",
	 *     summary="Search",
	 *     @OA\Parameter(
     *         description="query",
     *         in="query",
     *         name="q",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
	 *     @OA\Parameter(
     *         description="page number",
     *         in="query",
     *         name="page",
     *         required=false,
     *         @OA\Schema(
     *             format="string",
     *             type="string"
     *         )
     *     ),
	 *     @OA\Parameter(
     *         description="max number of result in a page",
     *         in="query",
     *         name="n",
     *         required=false,
     *         @OA\Schema(
     *             format="string",
     *             type="string"
     *         )
     *     ),
	 *     @OA\Response(response="200", description="Successful search")
	 * )
	 */
	public function search(Request $request, LoggerInterface $logger)
	{
		$pager = new Pager($request);
		$query = trim($request->query->get('q', ''));
		$logger->debug('query: ['.$query.']');
		$searched = array();
		if($pager->isValid() && strlen($query) >= 2) {
			$search = new SearchDao($this->getDoctrine()->getManager(), $this->get('security.authorization_checker'));
			$searched = $search->search($query, $this->getUser(), $pager->getOffset(), $pager->getElementByPage());
		}

		$pagination = new Pagination($pager->getPage(), $pager->getElementByPage(), $searched['hasmore']);
		$output = new SearchResultsView($query, $searched['results'], $pagination);
		$hateoas = HateoasBuilder::create()->build();
		$json = json_decode($hateoas->serialize($output, 'json'));

		return new Response(json_encode($json), 200, array(
			'Content-Type' => 'application/hal+json'
		));
	}
}
