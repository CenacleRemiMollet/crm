<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use App\Security\Roles;

class ConfigController extends AbstractController
{
	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @Route("/config", methods={"GET"}, name="web_config-get")
	 */
	public function getConfig(Request $request): Response
	{
		$this->denyAccessUnlessGranted(Roles::ROLE_ADMIN); // 403
		
	    $response = $this->forward('App\Controller\Api\ConfigController::getAllProperties');
		$json = json_decode($response->getContent());
		return $this->render('admin/config.html.twig', [
			'properties' => $json
		]);
	}

}
