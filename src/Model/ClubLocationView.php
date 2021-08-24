<?php

namespace App\Model;

use App\Entity\ClubLocation;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="ClubLocation")
 *
 * @author f.agu
 */
class ClubLocationView
{
	/**
	 * @OA\Property(type="string", example="abcd-xyz")
	 */
	private $uuid;

	/**
	 * @OA\Property(type="string", example="Gymnase Abc")
	 */
	private $name;

	/**
	 * @OA\Property(type="string", example="120 avenue des Champs-Elysées")
	 */
	private $address;

	/**
	 * @OA\Property(type="string", example="Paris")
	 */
	private $city;

	/**
	 * @OA\Property(type="string", example="75008")
	 */
	private $zipcode;

	/**
	 * @OA\Property(type="string", example="Ile de France")
	 */
	private $county;

	/**
	 * @OA\Property(type="string", example="France")
	 */
	private $country;

	public function __construct(ClubLocation $location)
	{
		$this->uuid = $location->getUuid();
		$this->name = $location->getName();
		$this->address = $location->getAddress();
		$this->city = $location->getCity();
		$this->zipcode = $location->getZipcode();
		$this->county = $location->getCounty();
		$this->country = $location->getCountry();
	}

	public function getUuid(): ?string
	{
		return $this->uuid;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function getAddress(): ?string
	{
		return $this->address;
	}

	public function getCity(): ?string
	{
		return $this->city;
	}

	public function getZipcode(): ?string
	{
		return $this->zipcode;
	}

	public function getCounty(): ?string
	{
		return $this->county;
	}

	public function getCountry(): ?string
	{
		return $this->country;
	}

}
