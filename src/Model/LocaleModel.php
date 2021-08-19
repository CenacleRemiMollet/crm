<?php

namespace App\Model;


use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use App\Entity\City;

/**
 * @Serializer\XmlRoot("locale")
 */
class LocaleModel
{
	private $locale;

	public function __construct($locale)
	{
		$this->locale = $locale;
	}

	public function getLocale(): ?string
	{
		return $this->locale;
	}

	public function setLocale($locale)
	{
		$this->locale = $locale;
	}

}
