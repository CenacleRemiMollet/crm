<?php

namespace App\Model;

use App\Validator\Constraints as AcmeAssert;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="ClubLocationUpdate",
 *     description="Update a club location",
 *     title="ClubLocationUpdate",
 *     @OA\Xml(
 *         name="ClubLocationUpdate"
 *     )
 * )
 */
class ClubLocationUpdate
{

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 128)
	 * @AcmeAssert\NoHTML
	 * @OA\Property(type="string", example="Gymnase Abc")
	 */
	private $name;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min=2, max = 64)
	 * @Assert\Regex(pattern="/[A-Za-z0-9_]{2,64}/")
	 * @OA\Property(type="string", example="abcdef13245", pattern="^[A-Za-z0-9_]{2,64}$")
	 */
	private $uuid;
	
	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @AcmeAssert\NoHTML
	 * @OA\Property(type="string", example="120 avenue des Champs-Elysées")
	 */
	private $address;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @AcmeAssert\NoHTML
	 * @OA\Property(type="string", example="Paris")
	 */
	private $city;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 20)
	 * @AcmeAssert\NoHTML
	 * @OA\Property(type="string", example="75008")
	 */
	private $zipcode;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @AcmeAssert\NoHTML
	 * @OA\Property(type="string", example="Ile de France")
	 */
	private $county;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @AcmeAssert\NoHTML
	 * @OA\Property(type="string", example="France")
	 */
	private $country;

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getUuid(): ?string
	{
	    return $this->uuid;
	}
	
	public function setUuid($uuid)
	{
	    $this->uuid = $uuid;
	}
	
	public function getAddress(): ?string
	{
		return $this->address;
	}

	public function setAddress($address)
	{
		$this->address = $address;
	}

	public function getCity(): ?string
	{
		return $this->city;
	}

	public function setCity($city)
	{
		$this->city = $city;
	}

	public function getZipcode(): ?string
	{
		return $this->zipcode;
	}

	public function setZipcode($zipcode)
	{
		$this->zipcode = $zipcode;
	}

	public function getCounty(): ?string
	{
		return $this->county;
	}

	public function setCounty($county)
	{
		$this->county = $county;
	}

	public function getCountry(): ?string
	{
		return $this->country;
	}

	public function setCountry($country)
	{
		$this->country = $country;
	}

}
