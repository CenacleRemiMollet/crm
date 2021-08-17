<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\MenuItem;

class MenuController extends AbstractController
{

	public function viewMenu()
	{
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
			'menu.html.twig', []
			//['menuItems' => $filteredMenuItems, 'club' => $club]
		);
	}
}
