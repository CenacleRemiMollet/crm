<?php

namespace App\Model;

use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ApiHome",
 *     description="ApiHome",
 *     title="ApiHome",
 *     @OA\Xml(
 *         name="ApiHome"
 *     )
 * )
 * @Serializer\XmlRoot("api")
 * @Hateoas\Relation("self", href = "expr('/crm/api')")
 * @Hateoas\Relation("city", href = "expr('/crm/api/city')")
 * @Hateoas\Relation("club", href = "expr('/crm/api/club')")
 * @Hateoas\Relation("clubsearch", href = "expr('/crm/api/clubsearch')")
 */
class ApiHome
{
	
}
