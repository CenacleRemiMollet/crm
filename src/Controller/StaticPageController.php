<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\ClubLocation;

class StaticPageController extends AbstractController
{

	/**
	 * @Route("/", methods={"GET"}, name="home")
	 */
	public function viewHome(): Response
	{
		$user = $this->getUser();
		return $this->render('home.html.twig', [
			'connectedUser' => $user
		]);
	}

	/**
	 * @Route("/master", methods={"GET"}, name="master")
	 */
	public function viewMaster(): Response
	{
		$user = $this->getUser();
		return $this->render('showcase/master.html.twig', [
			'connectedUser' => $user
		]);
	}

	/**
	 * @Route("/taekwonkido", methods={"GET"}, name="Taekwonkido")
	 */
	public function viewTaekwonkido(): Response
	{
		$user = $this->getUser();
		return $this->render('showcase/taekwonkido.html.twig', [
			'connectedUser' => $user
		]);
	}

	/**
	 * @Route("/taekwondo", methods={"GET"}, name="Taekwondo")
	 */
	public function viewTaekwondo(): Response
	{
		$user = $this->getUser();
		return $this->render('showcase/taekwondo.html.twig', [
			'connectedUser' => $user
		]);
	}

	/**
	 * @Route("/hapkido", methods={"GET"}, name="Hapkido")
	 */
	public function viewHapkido(): Response
	{
		$user = $this->getUser();
		return $this->render('showcase/hapkido.html.twig', [
			'connectedUser' => $user
		]);
	}

	/**
	 * @Route("/sinkido", methods={"GET"}, name="Sinkido")
	 */
	public function viewSinkido(): Response
	{
		$user = $this->getUser();
		return $this->render('showcase/sinkido.html.twig', [
			'connectedUser' => $user
		]);
	}

	/**
	 * @Route("/searchclub", methods={"GET"}, name="Search club around a city")
	 */
	public function searchClub(Request $request): Response
	{
		$user = $this->getUser();

		$query = $request->query->get('q');
		if($query == null) {
			return $this->render('searchclub.html.twig', [
				'connectedUser' => $user
			]);
		}
		if(mb_strlen($query) < 3) {
			return $this->render('searchclub.html.twig', [
				'error' => 'query is too short',
				'connectedUser' => $user
			]);
		}

		$limit = $request->query->get('limit', 20);
		$cities = $this->getDoctrine()->getManager()
		->getRepository(City::class)
		->findByStartsWith($query, $limit);

		foreach ($cities as &$city) {

		}

		$response = $this->forward('App\Controller\Api\ClubSearchController::searchAroundWithdistance', ['request' => $request, "zipcode" => "???"]);
		$json = json_decode($response->getContent());

		return $this->render('searchclub.html.twig', [
			'connectedUser' => $user
		]);
	}

}
