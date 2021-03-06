<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Generator;

class SwaggerController extends AbstractController
{
	/**
	 * @Route("/swagger/", name="web_swagger-index")
	 */
	public function index()
	{
		return $this->redirect('/crm/swagger/index.html');
	}

	/**
 	 * @Route("/swagger-config.json", name="web_swagger-config")
	 */
	public function configJson()
	{
	   $openapi = Generator::scan(['../../src']); // /Controller/Api
		return new Response($openapi->toJson(), 200, array(
			'Content-Type: application/json'
		));
	}


}
