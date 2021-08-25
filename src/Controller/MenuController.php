<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\MenuItem;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\ConfigurationProperty;
use App\Service\ConfigurationPropertyService;


class MenuController extends AbstractController
{

	public function viewMenu(SessionInterface $session)
	{
		$propService = new ConfigurationPropertyService($this->getDoctrine()->getManager());
		$menuProperties = $propService->findStartsWithToMap('menu.');

		$club = $session->get('club-selected');
		$lessons = $session->get('lessons-selected');
// 		$menuItems = $this->getDoctrine()->getManager()
// 				->getRepository(MenuItem::class)
// 				->findBy([], ['priority' => 'ASC']);

		// filter
// 		$filteredMenuItems = array();
// 		foreach($menuItems as $menuItem) {
// 			foreach($menuItem->getAvailableForRoles() as $role) {
// 				if($this->get('security.authorization_checker')->isGranted($role)) {
// 					array_push($filteredMenuItems, $menuItem);
// 					break;
// 				}
// 			}
// 		}

		return $this->render(
			'modules/menu.html.twig',
			[
				'club' => $club,
				'lessons' => $lessons,
				'menuProperties' => $menuProperties
			]
		);
	}
}
