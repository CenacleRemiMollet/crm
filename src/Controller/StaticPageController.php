<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StaticPageController extends AbstractController
{

	/**
	 * @Route("/", name="home")
	 */
	public function viewHome(): Response
	{
		$user = $this->getUser();
		return $this->render('home.html.twig', [
			'connectedUser' => $user
		]);
	}

	/**
	 * @Route("/master", name="master")
	 */
	public function viewMaster(): Response
	{
		$user = $this->getUser();
		return $this->render('showcase/master.html.twig', [
			'connectedUser' => $user
		]);
	}

	/**
	 * @Route("/taekwonkido", name="Taekwonkido")
	 */
	public function viewTaekwonkido(): Response
	{
		$user = $this->getUser();
		return $this->render('showcase/taekwonkido.html.twig', [
			'connectedUser' => $user
		]);
	}

}
