<?php
namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'MeAnonymous')]
#[Serializer\XmlRoot('me')]
#[Hateoas\Relation('self', href: '/crm/api/user/me')]
class MeAnonymousView
{
	#[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: 'abcDEF654')]
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

