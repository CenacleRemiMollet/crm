<?php

namespace App\Model;

use App\Entity\Club;

use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("club")
 * @Hateoas\Relation("self", href = "expr('/api/club/' ~ object.getUuid())")
 * @Hateoas\Relation("logo", href = "expr('/api/club/' ~ object.getUuid() ~ '/logo')")
 */
class ClubView
{
	private $uuid;
	private $name;
	private $website_url;
	private $facebook_url;
	private $locations;

	public function __construct(Club $club, $locations)
	{
		$this->uuid = $club->getUuid();
		$this->name = $club->getName();
		$this->website_url = $club->getWebsiteUrl();
		$this->facebook_url = $club->getFacebookUrl();
		$this->locations = $locations;
	}

	public function getUuid(): ?string
	{
		return $this->uuid;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function getWebsiteUrl(): ?string
	{
		return $this->website_url;
	}

	public function getFacebookUrl(): ?string
	{
		return $this->facebook_url;
	}

	public function getLocations()
	{
		return $this->locations;
	}
	
}
