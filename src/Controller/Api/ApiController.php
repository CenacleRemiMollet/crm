<?php
namespace App\Controller\Api;

use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Model\ApiHome;
use Hateoas\HateoasBuilder;

class ApiController extends AbstractController
{

    #[OA\Get(
        path: '/api',
        operationId: 'getApi',
        summary: 'API Home',
        tags: ['API'],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Successful',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/hal+json',
                        schema: new OA\Schema(type: 'array', items: new OA\Items(ref: '#/components/schemas/ApiHome'))
                    )
                ]
            )
        ]
    )]
    #[Route(path: '/api', name: 'api_root_get', methods: ['GET'])]
    public function getApiHome()
    {
        $hateoas = HateoasBuilder::create()->build();
        return new Response(
            $hateoas->serialize(new ApiHome(), 'json'),
            Response::HTTP_OK,
            array('Content-Type' => 'application/hal+json'));
    }
}

