<?php

namespace App\Model;


use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use App\Entity\City;

/**
 * @Serializer\XmlRoot("city")
 */
class CityModel
{
	private $zipcode;
	private $name;

	public function __construct(City $city)
	{
		$this->zipcode = $city->getZipCode();
		$this->name = $city->getCityName();
	}

	public function getZipcode(): ?string
	{
		return $this->zipcode;
	}

	public function getName(): ?string
	{
		return $this->name;
	}
	
}
