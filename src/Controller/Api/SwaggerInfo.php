<?php

namespace App\Controller\Api;

use OpenApi\Annotations as OA;

// see https://github.com/zircote/swagger-php/blob/master/Examples/swagger-spec/petstore-with-external-docs/controllers/PetWithDocsController.php
// see http://localhost/swagger/swagger-config.json


/**
 * @OA\Info(
 *   title="API Cénacle Rémi Mollet",
 *   version="0.1",
 *   @OA\License(
 *     name="Apache License 2.0",
 *     url="http://www.apache.org/licenses/LICENSE-2.0.txt"
 *   )
 * )
 * @OA\SecurityScheme(
 *     type="oauth2",
 *     name="main_auth",
 *     securityScheme="main_auth",
 *     @OA\Flow(
 *         flow="implicit",
 *         authorizationUrl="http://petstore.swagger.io/oauth/dialog",
 *         scopes={
 *             "write:pets": "modify pets in your account",
 *             "read:pets": "read your pets",
 *         }
 *     )
 * )
 * @OA\SecurityScheme(
 *     type="apiKey",
 *     in="header",
 *     securityScheme="api_key",
 *     name="api_key"
 * )
 */
class SwaggerInfo {}