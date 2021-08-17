<?php

namespace App\Model;

use App\Entity\ClubLocation;

class ClubLocationView
{
	private $uuid;
	private $name;
	private $address;
	private $city;
	private $zipcode;
	private $county;
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
