<?php
namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;

/**
 * @Serializer\XmlRoot("users")
 * @OA\Schema(schema="UsersView")
 */
class UsersView
{
	/**
	 * @OA\Property(type="object", @OA\Items(ref="#/components/schemas/Pagination"))
	 */
	private $pagination;

	/**
	 * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/UserView"))
	 */
	private $users;

	public function __construct($pagination, $users)
	{
		$this->pagination = $pagination;
		$this->users = $users;
	}

	public function getPagination()
	{
		return $this->pagination;
	}

	public function getUsers()
	{
		return $this->users;
	}
}
