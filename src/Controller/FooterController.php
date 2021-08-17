<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\MenuItem;

class FooterController extends AbstractController
{

	public function viewFooter()
	{
		return $this->render(
			'footer.html.twig', []
			//['menuItems' => $filteredMenuItems, 'club' => $club]
		);
	}
}
