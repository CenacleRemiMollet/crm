<?php

namespace App\Controller\Api;

use OpenApi\Annotations as OA;

// see https://github.com/zircote/swagger-php/blob/master/Examples/swagger-spec/petstore-with-external-docs/controllers/PetWithDocsController.php
// see http://localhost/swagger/swagger-config.json


/**
 * @OA\Info(
 *   title="Cénacle Rémi Mollet",
 *   version="0.1",
 *   @OA\License(
 *     name="Apache License 2.0",
 *     url="http://www.apache.org/licenses/LICENSE-2.0.txt"
 *   )
 * )
 */
class SwaggerInfo {}