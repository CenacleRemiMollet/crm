<?php

namespace App\Model;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="ClubPriceUpdate",
 *     description="Update a club price",
 *     title="ClubPriceUpdate",
 *     @OA\Xml(
 *         name="ClubPriceUpdate"
 *     )
 * )
 */
class ClubPriceUpdate
{
	/**
	 * @Assert\Length(max = 64)
	 * @OA\Property(type="string", example="abcdef13245")
	 */
	private $uuid;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @OA\Property(type="string", example="Baby Taekwondo")
	 */
	private $discipline;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @OA\Property(type="string", example="Baby")
	 */
	private $category;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @OA\Property(type="string", example="(4-6 ans)")
	 */
	private $comment;
	
	/**
	 * @Assert\Type("float")
	 * @Assert\Range(min = 0, max = 999)
	 * @OA\Property(type="float", example = 190)
	 */
	private $child1;
	
	/**
	 * @Assert\Type("float")
	 * @Assert\Range(min = 0, max = 999)
	 * @OA\Property(type="float", example = 180)
	 */
	private $child2;
	
	/**
	 * @Assert\Type("float")
	 * @Assert\Range(min = 0, max = 999)
	 * @OA\Property(type="float", example = 170)
	 */
	private $child3;
	
	/**
	 * @Assert\Type("float")
	 * @Assert\Range(min = 0, max = 999)
	 * @OA\Property(type="float", example = 210)
	 */
	private $adult;
	
	public function getUuid(): ?string
	{
	    return $this->uuid;
	}
	
	public function setUuid($uuid)
	{
	    $this->uuid = $uuid;
	}

	public function getDiscipline(): ?string
	{
	    return $this->discipline;
	}

	public function setDiscipline($discipline)
	{
	    $this->discipline = $discipline;
	}

	public function getCategory(): ?string
	{
		return $this->category;
	}

	public function setCategory($category)
	{
	    $this->category = $category;
	}

	public function getComment(): ?string
	{
	    return $this->comment;
	}
	
	public function setComment($comment)
	{
	    $this->comment = $comment;
	}
	
	public function getChild1(): ?float
	{
	    return $this->child1;
	}

	public function setChild1($child1)
	{
	    $this->child1 = $child1;
	}

	public function getChild2(): ?float
	{
	    return $this->child2;
	}
	
	public function setChild2($child2)
	{
	    $this->child2 = $child2;
	}
	
	public function getChild3(): ?float
	{
	    return $this->child3;
	}
	
	public function setChild3($child3)
	{
	    $this->child3 = $child3;
	}
	
	public function getAdult(): ?float
	{
	    return $this->adult;
	}
	
	public function setAdult($adult)
	{
	    $this->adult = $adult;
	}
	
}
