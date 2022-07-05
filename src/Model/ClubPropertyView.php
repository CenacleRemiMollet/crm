<?php

namespace App\Model;

use App\Entity\ClubLocation;
use OpenApi\Annotations as OA;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use App\Entity\Club;
use App\Entity\ClubProperty;

/**
 * @OA\Schema(schema="ClubProperty")
 * @Serializer\XmlRoot("club")
 *
 * @author f.agu
 */
class ClubPropertyView
{
    /**
     * @OA\Property(type="string", example="abcd-xyz")
     */
    private $club_uuid;
    
    /**
	 * @OA\Property(type="string", example="abcd-xyz")
	 */
	private $uuid;

	/**
	 * @OA\Property(type="string", example="Gymnase Abc")
	 */
	private $name;

	/**
	 * @OA\Property(type="string", example="a value")
	 */
	private $value;

	public function __construct(Club $club, ClubProperty $clubProperty)
	{
	    $this->club_uuid = $club === null ? $clubProperty->getClub()->getUuid() : $club->getUuid();
	    $this->uuid = $clubProperty->getUuid();
	    $this->name = $clubProperty->getName();
	    $this->value = $clubProperty->getValue();
	}

	public function getClubUuid(): ?string
	{
	    return $this->club_uuid;
	}
	
	public function getUuid(): ?string
	{
		return $this->uuid;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

}
