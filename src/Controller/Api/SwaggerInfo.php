<?php

namespace App\Controller\Api;

use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

// see https://github.com/zircote/swagger-php/blob/master/Examples/swagger-spec/petstore-with-external-docs/controllers/PetWithDocsController.php
// see http://localhost/crm/swagger-config.json


/**
 * @OA\Info(
 *   title="API Cénacle Rémi Mollet",
 *   version="0.1",
 *   @OA\License(
 *     name="Apache License 2.0",
 *     url="http://www.apache.org/licenses/LICENSE-2.0.txt"
 *   )
 * )
 *
 * @OA\SecurityScheme(
 *     name="authorization",
 *     type="http",
 *     in="query",
 *     securityScheme="http",
 *     scheme="basic"
 * )
 *
 * @OA\Parameter(
 *     description="ClientId",
 *     in="header",
 *     name="X-ClientId",
 *     required=true,
 *     @OA\Schema(format="string", type="string", pattern="[a-z0-9_]{2,64}")
 * )
 * @OA\Server(
 *     url="/crm"
 * )
 */
class SwaggerInfo {}