<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\MenuItem;
use App\Service\ConfigurationPropertyService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FooterController extends AbstractController
{

    public function viewFooter(SessionInterface $session)
	{
	    $propService = new ConfigurationPropertyService($this->container->get('doctrine')->getManager());
	    $cenacleProperties = $propService->findStartsWithToMap('cenacle.');
	    $clubProperties = $propService->findStartsWithToMap('club.');
	    $club = $session->get('club-selected');
	    
	    return $this->render(
			'modules/footer.html.twig',
	        ['cenacleProperties' => $cenacleProperties,
	         'clubProperties' => $clubProperties,
	         'club' => $club]
		);
	}
}
