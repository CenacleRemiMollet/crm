<?php

namespace App\Model;

use App\Entity\ClubLocation;
use OpenApi\Annotations as OA;
use App\Entity\ClubPrice;

/**
 * @OA\Schema(schema="ClubPrice")
 *
 * @author f.agu
 */
class ClubPriceView
{
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
	private $description;

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
	
	public function __construct(ClubPrice $price)
	{
	    $this->uuid = $price->getUuid();
	    $this->discipline = $price->getDiscipline();
	    $this->category = $price->getAgeLevelName();
	    $this->description = $price->getAgeLevelDescription();
	    $this->child1 = $price->getPriceChild1();
	    $this->child2 = $price->getPriceChild2();
	    $this->child3 = $price->getPriceChild3();
	    $this->adult = $price->getPriceAdult();
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

	public function getDescription(): ?string
	{
		return $this->description;
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
