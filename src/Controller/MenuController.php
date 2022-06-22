<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Service\ConfigurationPropertyService;
use App\Security\ClubAccess;
use App\Entity\EntityFinder;
use App\Entity\Club;


class MenuController extends AbstractController
{      
    
    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function viewMenu(SessionInterface $session)
	{
		$doctrine = $this->container->get('doctrine');
        $propService = new ConfigurationPropertyService($doctrine->getManager());
		$menuProperties = $propService->findStartsWithToMap('menu.');

		$club = $session->get('club-selected');
		$lessons = $session->get('lessons-selected');

		$canConfigure = false;
		if($club !== null) {
		    $entityFinder = new EntityFinder($doctrine);
		    $clubObj = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club->uuid]); // 404, never happen !
		    
		    $clubAccess = new ClubAccess($this->container, $this->logger);
		    $canConfigure = $clubAccess->hasAccessForUser($clubObj, $this->getUser());
		}
		
		return $this->render('modules/menu.html.twig', [
			'club' => $club,
			'lessons' => $lessons,
            'menuProperties' => $menuProperties,
		    'canConfigure' => $canConfigure
		]);
	}
}
