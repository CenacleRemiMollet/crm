<?php
namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Serializer\XmlRoot("me")
 * @Hateoas\Relation("self", href = "/api/user/me")
 */
class MeAnonymousView
{
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

