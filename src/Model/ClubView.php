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
 * @Hateoas\Relation("self", href = "expr('/crm/api/club/' ~ object.getUuid())")
 * @Hateoas\Relation("logo", href = "expr('/crm/api/club/' ~ object.getUuid() ~ '/logo')")
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
	 * @OA\Property(type="boolean", example="true")
	 */
	private $active;

	/**
	 * @OA\Property(type="string", example="foo@bar.com")
	 */
	private $mailing_list;
	
	/**
	 * @OA\Property(type="string", example="foo@bar.com")
	 */
	private $contact_emails;
	
	/**
	 * @OA\Property(type="string", example="0 892 70 12 39")
	 */
	private $contact_phone;
	
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
	 * @OA\Property(type="string", example="https://www.dailymotion.com/abc")
	 */
	private $dailymotion_url;
	
    /**
	 * @OA\Property(type="string", example="https://www.youtube.com/abc")
	 */
	private $youtube_url;

	/**
	 * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/ClubLocation"))
	 */
	private $locations;

	/**
	 * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/ClubPrice"))
	 */
	private $prices;
	
	public function __construct(Club $club, $locations = null, $prices = null)
	{
		$this->uuid = $club->getUuid();
		$this->name = $club->getName();
		$this->active = $club->getActive();
		$this->mailing_list = $club->getMailingList();
		$this->contact_emails = $club->getContactEmails();
		$this->contact_phone = $club->getContactPhone();
		$this->website_url = $club->getWebsiteUrl();
		$this->facebook_url = $club->getFacebookUrl();
		$this->twitter_url = $club->getTwitterUrl();
		$this->instagram_url = $club->getInstagramUrl();
		$this->dailymotion_url = $club->getDailymotionUrl();
		$this->youtube_url = $club->getYoutubeUrl();
		$this->locations = $locations;
		$this->prices = $prices;
	}

	public function getUuid(): ?string
	{
		return $this->uuid;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function getActive(): ?bool
	{
	    return $this->active;
	}
	
	public function getMailingList(): ?string
	{
	    return $this->mailing_list;
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

	public function getDailymotionUrl(): ?string
	{
	    return $this->dailymotion_url;
	}
	
	public function getYoutubeUrl(): ?string
	{
	    return $this->youtube_url;
	}
	
	public function getLocations()
	{
		return $this->locations;
	}

	public function getPrices()
	{
	    return $this->prices;
	}
	
	public function getContactPhone(): ?string
	{
	    return $this->contact_phone;
	}
	
	public function getContactEmails(): ?string
	{
	    return $this->contact_emails;
	}
	
	
}
