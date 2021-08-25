<?php
namespace App\Model;

use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use App\Entity\User;

/**
 * @Serializer\XmlRoot("user")
 * @OA\Schema(schema="UserMe")
 */
class UserMeView extends UserView
{
	/**
	 * @OA\Property(type="array", example="abcDEF654", items = @OA\Items(type="string"))
	 */
	private $grantedRoles;

	public function __construct(User $user, $grantedRoles)
	{
		parent::__construct($user);
		$this->grantedRoles = $grantedRoles;
	}

	public function getGrantedRoles()
	{
		return $this->grantedRoles;
	}

}

