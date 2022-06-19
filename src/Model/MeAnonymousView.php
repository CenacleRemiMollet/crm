<?php
namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;
use OpenApi\Annotations as OA;

/**
 * @Serializer\XmlRoot("me")
 * @Hateoas\Relation("self", href = "/crm/api/user/me")
 * @OA\Schema(schema="MeAnonymous")
 */
class MeAnonymousView
{
	/**
	 * @OA\Property(type="array", example="abcDEF654", items = @OA\Items(type="string"))
	 */
	private $grantedRoles;

	public function __construct($grantedRoles)
	{
		$this->grantedRoles = $grantedRoles;
	}

	public function getGrantedRoles()
	{
		return $this->grantedRoles;
	}

}

