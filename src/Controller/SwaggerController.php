<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Generator;

class SwaggerController extends AbstractController
{
	#[Route(path: '/swagger/', name: 'web_swagger-index')]
    public function index()
	{
		return $this->redirect('/crm/swagger/index.html');
	}

	#[Route(path: '/swagger-config.json', name: 'web_swagger-config')]
    public function configJson()
	{
	   $openapi = (new Generator())->generate(['../../src']); // /Controller/Api
		return new Response($openapi->toJson(), 200, array(
			'Content-Type: application/json'
		));
	}


}
