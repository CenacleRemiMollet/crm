<?php

namespace App\Model;

use App\Validator\Constraints as AcmeAssert;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'UserClubSubscribeUpdate',
    title: 'UserClubSubscribeUpdate',
    description: 'Update a user club subscription',
    xml: new OA\Xml(name: 'UserClubSubscribeUpdate')
)]
class UserClubSubscribeUpdate
{
	#[OA\Property(type: 'string', pattern: '^[A-Za-z0-9_]{2,64}$', example: 'abcdef13245')]
    #[Assert\Type('string')]
    #[Assert\Length(min: 2, max: 64)]
    #[Assert\Regex(pattern: '/[A-Za-z0-9_]{2,64}/')]
    private $uuid;

	/**
	 * @var string[]
	 */
	#[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: 'ROLE_STUDENT')]
    #[AcmeAssert\Roles]
    private $roles;

	#[OA\Property(type: 'string', pattern: '^[a-z0-9_]{2,64}$', example: 'abcdef13245')]
    #[Assert\Type('string')]
    #[Assert\Length(min: 2, max: 64)]
    #[Assert\Regex(pattern: '/[a-z0-9_]{2,64}/')]
    private $club_uuid;

	
	public function getUuid(): ?string
	{
	    return $this->uuid;
	}
	
	public function setUuid($uuid)
	{
	    $this->uuid = $uuid;
	}

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
