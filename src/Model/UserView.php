<?php

namespace App\Model;

use Hateoas\Configuration\Annotation as Hateoas;
use App\Entity\User;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Attributes as OA;
use Hateoas\Helper\LinkHelper;
use App\Entity\UserClubSubscribe;
use App\Entity\Account;

#[OA\Schema(schema: 'User')]
#[Serializer\XmlRoot('user')]
#[Hateoas\Relation('self', href: "expr('/crm/api/users/' ~ object.getUuid())")]
class UserView extends UserViewModel
{
	#[OA\Property(type: 'string', example: 'j.doe')]
	private $login;

	#[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: 'abcDEF654')]
	private $roles;
	
	#[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: 'abcDEF654')]
	private $granted_roles;
	
	#[OA\Property(type: 'object')]
	private $subscribes;

	#[OA\Property(type: 'boolean')]
	private $myself;
	
	public function __construct(User $user, $myself = null, ?bool $attachClubSubscribe = false)
	{
		parent::__construct($user);
		$account = $user->getAccount();
		if($account) {
		    $this->login = $account->getLogin();
		    $this->roles = $account->getDeclaredRoles();
		    $this->granted_roles = $account->getRoles();
		}
		if($attachClubSubscribe) {
		    $this->subscribes = array();
		    foreach($user->getUserClubSubscribes() as &$ucs) {
		        array_push($this->subscribes, new UserClubSubscribeView($ucs));
		    }
		}
		if(($myself instanceof Account && $myself->getUser()->getUuid() == $this->getUuid())
		    || (gettype($myself) === 'boolean' && $myself)){
		    $this->myself = true;
		}
	}

	public function getLogin()
	{
		return $this->login;
	}

	public function getRoles()
	{
	    return $this->defined_roles;
	}

	public function getGrantedRoles()
	{
	    return $this->granted_roles;
	}
	
	public function getSubscribes()
	{
	    return $this->subscribes;
	}
	
}