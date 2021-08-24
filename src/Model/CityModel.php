<?php

namespace App\Model;

use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use App\Entity\City;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="City")
 * @Serializer\XmlRoot("city")
 */
class CityModel
{
	/**
	 * @OA\Property(type="string", example="75008")
	 */
	private $zipcode;

	/**
	 * @OA\Property(type="string", example="Paris 8ème")
	 */
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
