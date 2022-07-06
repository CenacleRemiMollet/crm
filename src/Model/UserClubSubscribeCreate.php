<?php

namespace App\Model;

use App\Validator\Constraints as AcmeAssert;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="UserClubSubscribeCreate",
 *     description="Create a user club subscription",
 *     title="UserClubSubscribeCreate",
 *     required={"roles", "club_uuid"},
 *     @OA\Xml(
 *         name="UserClubSubscribeCreate"
 *     )
 * )
 */
class UserClubSubscribeCreate
{

    /**
	 * @var string[]
	 * @AcmeAssert\Roles
	 * @OA\Property(type="array", example="ROLE_STUDENT", @OA\Items(type="string"))
	 */
	private $roles;

	/**
	 * @Assert\NotBlank
	 * @Assert\Type("string")
	 * @Assert\Length(min=2, max = 64)
	 * @Assert\Regex(pattern="/[a-z0-9_]{2,64}/")
	 * @OA\Property(type="string", example="abcdef13245", pattern="^[a-z0-9_]{2,64}$")
	 */
	private $club_uuid;
	
	public function getRoles(): ?array
	{
	    return $this->roles === null ? null : array_unique(array_map('strtoupper', $this->roles));
	}
	
	public function setRoles($roles)
	{
	    $this->roles = $roles !== null ? array_unique(array_map('strtoupper', $roles)) : [];
	}
	
	public function getClubUuid(): ?string
	{
	    return $this->club_uuid;
	}
	
	public function setClubUuid($club_uuid)
	{
	    $this->club_uuid = $club_uuid;
	}
	
	
}
