<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ClubController extends AbstractController
{

	/**
	 * @Route("/club", name="web_club_list-active", methods={"GET"})
	 */
	public function listActive()
	{
		$user = $this->getUser();
		$response = $this->forward('App\Controller\Api\ClubController::listActive');
		$json = json_decode($response->getContent());
		return $this->render('club/club-list.html.twig', [
			'connectedUser' => $user,
			'clubs' => $json
		]);
	}

	/**
	 * @Route("/club/{uuid}", name="web_club_one", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 */
	public function viewOne($uuid, LoggerInterface $logger, SessionInterface $session)
	{
		$response = $this->forward('App\Controller\Api\ClubController::one', ['uuid' => $uuid]);
		if($response->getStatusCode() != 200) {
			return $this->render('club/club-not-found.html.twig', []);
		}
		$club = json_decode($response->getContent());
		$session->set('club-selected', $club);

		$response = $this->forward('App\Controller\Api\ClubController::getLessons', ['uuid' => $uuid]);
		$lessons = json_decode($response->getContent());
		$session->set('lessons-selected', $lessons);

		return $this->render('club/club.html.twig', [
			'club' => $club,
			'lessons' => $lessons
		]);
	}

}
