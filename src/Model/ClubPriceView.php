<?php

namespace App\Model;

use OpenApi\Annotations as OA;
use App\Entity\ClubPrice;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use App\Entity\Club;

/**
 * @OA\Schema(schema="ClubPrice")
 * @Serializer\XmlRoot("club")
 * @Hateoas\Relation("self", href = "expr('/crm/api/club/' ~ object.getClubUuid() ~ '/locations/' ~ object.getUuid())")
 *
 * @author f.agu
 */
class ClubPriceView
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
	 * @OA\Property(type="string", example="Taekwondo")
	 */
	private $discipline;

	/**
	 * @OA\Property(type="string", example="Enfants")
	 */
	private $category;

	/**
	 * @OA\Property(type="string", example="(4-6 ans)")
	 */
	private $comment;

	/**
	 * @OA\Property(type="number", format="float", nullable="true", example="135")
	 */
	private $child1;

	/**
	 * @OA\Property(type="number", format="float", nullable="true", example="155")
	 */
	private $child2;
	
	/**
	 * @OA\Property(type="number", format="float", nullable="true", example="115")
	 */
	private $child3;
	
	/**
	 * @OA\Property(type="number", format="float", nullable="true", example="150")
	 */
	private $adult;
	
	public function __construct(Club $club, ClubPrice $price)
	{
	    $this->club_uuid = $club === null ? $price->getClub()->getUuid() : $club->getUuid();
	    $this->uuid = $price->getUuid();
	    $this->discipline = $price->getDiscipline();
	    $this->category = $price->getCategory();
	    $this->comment = $price->getComment();
	    $this->child1 = $price->getPriceChild1();
	    $this->child2 = $price->getPriceChild2();
	    $this->child3 = $price->getPriceChild3();
	    $this->adult = $price->getPriceAdult();
	}

	public function getClubUuid(): ?string
	{
	    return $this->club_uuid;
	}
	
	public function getUuid(): ?string
	{
		return $this->uuid;
	}

	public function getDiscipline(): ?string
	{
		return $this->discipline;
	}

	public function getCategory(): ?string
	{
		return $this->category;
	}

	public function getComment(): ?string
	{
		return $this->comment;
	}

	public function getChild1(): ?float
	{
		return $this->child1;
	}

	public function getChild2(): ?float
	{
	    return $this->child2;
	}
	
	public function getChild3(): ?float
	{
	    return $this->child3;
	}
	
	public function getAdult(): ?float
	{
	    return $this->adult;
	}
	
}
