<?php

namespace App\Model;

use App\Validator\Constraints as AcmeAssert;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'UserClubSubscribeCreate',
    title: 'UserClubSubscribeCreate',
    description: 'Create a user club subscription',
    required: ['roles', 'club_uuid'],
    xml: new OA\Xml(name: 'UserClubSubscribeCreate')
)]
class UserClubSubscribeCreate
{

    /**
     * @var string[]
     */
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: 'ROLE_STUDENT')]
    #[AcmeAssert\Roles]
    private $roles;

	#[OA\Property(type: 'string', pattern: '^[a-z0-9_]{2,64}$', example: 'abcdef13245')]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 2, max: 64)]
    #[Assert\Regex(pattern: '/[a-z0-9_]{2,64}/')]
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
