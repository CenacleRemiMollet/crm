<?php

namespace App\Model;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="ClubLocationCreate",
 *     required={"name"}
 * )
 *
 * @author f.agu
 */
class ClubLocationCreate
{

	/**
	 * @Assert\NotBlank
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 128)
	 * @OA\Property(type="string", example="Gymnase Abc")
	 */
	private $name;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @OA\Property(type="string", example="120 avenue des Champs-Elysées")
	 */
	private $address;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @OA\Property(type="string", example="Paris")
	 */
	private $city;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 20)
	 * @OA\Property(type="string", example="75008")
	 */
	private $zipcode;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @OA\Property(type="string", example="Ile de France")
	 */
	private $county;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
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
