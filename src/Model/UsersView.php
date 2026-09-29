<?php
namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use OpenApi\Attributes as OA;


#[OA\Schema(schema: 'Users')]
#[Serializer\XmlRoot('users')]
class UsersView
{
	#[OA\Property(ref: '#/components/schemas/Pagination')]
	private $pagination;

	#[OA\Property(type: 'array', items: new OA\Items(ref: '#/components/schemas/Account'))]
	private $accounts;

	public function __construct($pagination, $accounts)
	{
		$this->pagination = $pagination;
		$this->accounts = $accounts;
	}

	public function getPagination()
	{
		return $this->pagination;
	}

	public function getAccounts()
	{
		return $this->accounts;
	}
}
