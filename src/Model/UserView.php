<?php

namespace App\Model;

use Hateoas\Configuration\Annotation as Hateoas;
use App\Entity\User;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;

/**
 * @Serializer\XmlRoot("user")
 * @Hateoas\Relation("self", href = "expr('/crm/api/user/' ~ object.getUuid())")
 * @OA\Schema(schema="User")
 */
class UserView extends UserViewModel
{
	/**
	 * @OA\Property(type="string", example="j.doe")
	 */
	private $login;

	/**
	 * @OA\Property(type="array", example="abcDEF654", @OA\Items(type="string"))
	 */
	private $roles;

	public function __construct(User $user)
	{
		parent::__construct($user);
		$account = $user->getAccount();
		if($account) {
			$this->login = $account->getLogin();
			$this->roles = $account->getRoles();
		}
	}

	public function getLogin()
	{
		return $this->login;
	}

	public function getRoles()
	{
		return $this->roles;
	}

}