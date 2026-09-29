<?php

namespace App\Controller\Api;

use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Attribute\Route;

// see https://github.com/zircote/swagger-php/blob/master/Examples/swagger-spec/petstore-with-external-docs/controllers/PetWithDocsController.php
// see http://localhost/crm/swagger-config.json


#[OA\Info(
    version: '0.1',
    title: 'API Cénacle Rémi Mollet',
    license: new OA\License(name: 'Apache License 2.0', url: 'http://www.apache.org/licenses/LICENSE-2.0.txt')
)]
#[OA\SecurityScheme(securityScheme: 'http', type: 'http', name: 'authorization', in: 'query', scheme: 'basic')]
#[OA\Parameter(
    name: 'X-ClientId',
    description: 'ClientId',
    in: 'header',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'string', pattern: '[a-z0-9_]{2,64}')
)]
#[OA\Server(url: '/crm')]
class SwaggerInfo {}