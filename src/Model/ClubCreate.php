<?php

namespace App\Model;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="ClubCreate",
 *     description="Create a club",
 *     title="ClubCreate",
 *     @OA\Xml(
 *         name="ClubCreate"
 *     )
 * )
 */
class ClubCreate
{

	/**
	 * @Assert\NotBlank
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @OA\Property(type="string", example="Abc Club")
	 */
	private $name;

	/**
	 * @Assert\NotBlank
	 * @Assert\Type("boolean")
	 * @OA\Property(type="boolean", example="true")
	 */
	private $active = true;

	/**
	 * @Assert\Length(max = 64)
	 * @OA\Property(type="string", example="abc_club")
	 */
	private $uuid;
	
	/**
	 * @Assert\Length(max = 512)
	 * @OA\Property(type="string", example="mail_1@adresse.fr, mail_2@adresse.fr")
	 */
	private $contactEmails;
	
	/**
	 * @Assert\Length(max = 32)
	 * @OA\Property(type="string", example="0 892 70 12 39")
	 */
	private $contactPhone;
	
	/**
	 * @Assert\Length(max = 512)
	 * @OA\Property(type="string", example="mail_1@adresse.fr, mail_2@adresse.fr")
	 */
	private $mailingList;
	
	/**
	 * @Assert\Length(min = 1, max = 512)
	 * @OA\Property(type="string", example="https://www.google.com")
	 */
	private $website_url;

	/**
	 * @Assert\Length(min = 1, max = 512)
	 * @OA\Property(type="string", example="https://facebook.com/pages/category/Local-Business/Taekwonkido-Cenacle-Rémi-Mollet-158619684187704/")
	 */
	private $facebook_url;

	/**
	 * @Assert\Length(min = 1, max = 512)
	 * @OA\Property(type="string", example="https://twitter.com/abc")
	 */
	private $twitter_url;

	/**
	 * @Assert\Length(min = 1, max = 512)
	 * @OA\Property(type="string", example="https://www.instagram.com/abc")
	 */
	private $instagram_url;

// 	/**
// 	 * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/ClubLocationCreate"))
// 	 */
// 	private $locations;

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}
	
	public function isActive(): ?string
	{
	    return $this->active;
	}
	
	public function setActive($active)
	{
	    $this->active = $active;
	}
	
	public function getUuid(): ?string
	{
	    return $this->uuid;
	}
	
	public function setUuid($uuid)
	{
	    $this->uuid = $uuid;
	}

	public function getContactEmails(): ?string
	{
	    return $this->contactEmails;
	}
	
	public function getContactEmailsToArray(): array
	{
	    if($this->contactEmails == null || '' === $this->contactEmails) {
	        return [];
	    }
	    return explode(',', str_replace(' ', '', $this->contactEmails));
	}
	
	public function setContactEmails(?string $contactEmails): self
	{
	    $this->contactEmails = $contactEmails;
	    return $this;
	}
	
	public function getContactPhone(): ?string
	{
	    return $this->contactPhone;
	}
	
	public function setContactPhone(?string $contactPhone): self
	{
	    $this->contactPhone = $contactPhone;
	    return $this;
	}
	
	public function getMailingList(): ?string
	{
	    return $this->mailingList;
	}
	
	public function getMailingListToArray(): array
	{
	    if($this->mailingList == null || '' === $this->mailingList) {
	        return [];
	    }
	    return explode(',', str_replace(' ', '', $this->mailingList));
	}
	
	public function setMailingList(?string $mailingList): self
	{
	    $this->mailingList = $mailingList;
	    return $this;
	}
	
	public function getWebsiteUrl(): ?string
	{
		return $this->website_url;
	}

	public function setWebsiteUrl($website_url)
	{
		$this->website_url = $website_url;
	}

	public function getFacebookUrl(): ?string
	{
		return $this->facebook_url;
	}

	public function setFacebookUrl($facebook_url)
	{
		$this->facebook_url = $facebook_url;
	}

	public function getTwitterUrl(): ?string
	{
		return $this->twitter_url;
	}

	public function setTwitterUrl($twitter_url)
	{
		$this->twitter_url = $twitter_url;
	}

	public function getInstagramUrl(): ?string
	{
		return $this->instagram_url;
	}

	public function setInstagramUrl($instagram_url)
	{
		$this->instagram_url = $instagram_url;
	}

// 	public function getLocations()
// 	{
// 		return $this->locations;
// 	}

// 	public function setLocations(ClubLocationCreate... $locations)
// 	{
// 		$this->locations = $locations;
// 	}

}
