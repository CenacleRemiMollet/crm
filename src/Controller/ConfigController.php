<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use App\Security\Roles;
use App\Service\PlanningColors;

class ConfigController extends AbstractController
{
	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	#[Route(path: '/config', methods: ['GET'], name: 'web_config-get')]
    public function getConfig(Request $request, PlanningColors $planningColors): Response
	{
		$this->denyAccessUnlessGranted(Roles::ROLE_ADMIN); // 403
		
	    $response = $this->forward('App\Controller\Api\ConfigController::getAllProperties');
		$json = json_decode($response->getContent());
		return $this->render('admin/config.html.twig', [
			'properties' => $json,
			'planningColors' => $planningColors->getAll(),
			'planningColorPrefix' => PlanningColors::PREFIX,
			'planningColorReset' => PlanningColors::RESET_VALUE
		]);
	}

}
