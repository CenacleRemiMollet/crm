<?php

namespace App\Model;

use App\Entity\User;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;
use App\Entity\Account;
use OpenApi\Annotations as OA;

/**
 * @Serializer\XmlRoot("user")
 * @Hateoas\Relation("self", href = "expr('/api/user/' ~ object.getUuid())")
 * @OA\Schema(schema="UserView")
 */
class UserView extends UserViewModel
{
	/**
	 * @OA\Property(type="string", example="j.doe")
	 */
	private $login;
	
	/**
	 * @OA\Property(type="array", example="abcDEF654", items = @OA\Items(type="string"))
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