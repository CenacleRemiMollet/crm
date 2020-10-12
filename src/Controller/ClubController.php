<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
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
		return $this->render('club/clubs.html.twig', [
			'connectedUser' => $user,
			'clubs' => $json->clubs
		]);
	}

	/**
	 * @Route("/club/{uuid}", name="web_club_one", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 */
	public function viewOne($uuid, LoggerInterface $logger, SessionInterface $session)
	{
		$user = $this->getUser();
		$response = $this->forward('App\Controller\Api\ClubController::one', ['uuid' => $uuid]);
		if($response->getStatusCode() != 200) {
			return $this->render('club/club-not-found.html.twig', [
				'connectedUser' => $user
			]);
		}
		$club = json_decode($response->getContent())->club;
		$session->set('club-selected', $club);
		return $this->render('club/club.html.twig', [
			'connectedUser' => $user//,
			//'club' => $club
		]);
	}

	/**
	 * @Route("/club/{uuid}/lessons", name="web_club_lessons", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 */
	public function viewLessons($uuid, LoggerInterface $logger, SessionInterface $session)
	{
		$user = $this->getUser();
		
		$response = $this->forward('App\Controller\Api\ClubController::one', ['uuid' => $uuid]);
		if($response->getStatusCode() != 200) {
			return $this->render('club/club-not-found.html.twig', [
				'connectedUser' => $user
			]);
		}
		$club = json_decode($response->getContent())->club;
		
		$response = $this->forward('App\Controller\Api\ClubController::getLessons', ['uuid' => $uuid]);
		if($response->getStatusCode() != 200) {
			return $this->render('club/club-lessons-not-found.html.twig', [
				'connectedUser' => $user
			]);
		}
		$clubLessons = json_decode($response->getContent());
		$session->set('club-selected', $club);
		return $this->render('club/club-lessons.html.twig', [
			'connectedUser' => $user,
			'clubLessons' => $clubLessons
		]);
	}
	
	
	/**
	 * @Route("/club/{uuid}/edit", name="web_club_one_edit", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 */
	public function viewEdit($uuid)
	{
		$user = $this->getUser();
		$response = $this->forward('App\Controller\Api\ClubController::one', ['uuid' => $uuid]);
		$json = json_decode($response->getContent());
		return $this->render('club/club-edit.html.twig', [
			'connectedUser' => $user,
			'club' => $json->club
		]);
	}
	
}
