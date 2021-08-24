<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ConfigController extends AbstractController
{
	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @Route("/config", methods={"GET"}, name="web_config-get")
	 * @IsGranted("ROLE_ADMIN")
	 */
	public function getConfig(Request $request): Response
	{
		$response = $this->forward('App\Controller\Api\ConfigController::getAllProperties');
		$json = json_decode($response->getContent());
		return $this->render('home.html.twig', [
			'properties' => $json
		]);
	}

}
