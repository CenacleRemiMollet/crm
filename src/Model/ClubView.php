<?php

namespace App\Model;

use App\Entity\Club;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Club",
 *     description="Club",
 *     title="Club",
 *     @OA\Xml(
 *         name="Club"
 *     )
 * )
 * @Serializer\XmlRoot("club")
 * @Hateoas\Relation("self", href = "expr('/api/club/' ~ object.getUuid())")
 * @Hateoas\Relation("logo", href = "expr('/api/club/' ~ object.getUuid() ~ '/logo')")
 */
class ClubView
{
	/**
	 * @OA\Property(type="string", example="abcd-xyz")
	 */
	private $uuid;

	/**
	 * @OA\Property(type="string", example="Abc Club")
	 */
	private $name;

	/**
	 * @OA\Property(type="string", example="https://www.google.com")
	 */
	private $website_url;

	/**
	 * @OA\Property(type="string", example="https://facebook.com/pages/category/Local-Business/Taekwonkido-Cenacle-Rémi-Mollet-158619684187704/")
	 */
	private $facebook_url;

	/**
	 * @OA\Property(type="string", example="https://twitter.com/abc")
	 */
	private $twitter_url;

	/**
	 * @OA\Property(type="string", example="https://www.instagram.com/abc")
	 */
	private $instagram_url;

	/**
	 * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/ClubLocation"))
	 */
	private $locations;

	public function __construct(Club $club, $locations)
	{
		$this->uuid = $club->getUuid();
		$this->name = $club->getName();
		$this->website_url = $club->getWebsiteUrl();
		$this->facebook_url = $club->getFacebookUrl();
		$this->twitter_url = $club->getTwitterUrl();
		$this->instagram_url = $club->getInstagramUrl();
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

	public function getTwitterUrl(): ?string
	{
		return $this->twitter_url;
	}

	public function getInstagramUrl(): ?string
	{
		return $this->instagram_url;
	}

	public function getLocations()
	{
		return $this->locations;
	}

}
