<?php
namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use App\Entity\Account;


/**
 * @Serializer\XmlRoot("account")
 * @OA\Schema(schema="Account")
 */
class AccountView
{
	/**
	 * @OA\Property(type="string", example="john")
	 */
	private string $login;

	/**
	 * @OA\Property(type="boolean", example="true")
	 */
	private bool $has_access;
	
	public function __construct(Account $account)
	{
	    $this->login = $account->getLogin();
		$this->accounts = $account->getHasAccess();
	}

	public function getLogin()
	{
	    return $this->login;
	}

	public function getHasAccess()
	{
	    return $this->has_access;
	}
}
