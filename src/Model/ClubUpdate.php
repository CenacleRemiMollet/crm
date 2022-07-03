<?php

namespace App\Model;

use App\Validator\Constraints as AcmeAssert;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="ClubUpdate",
 *     description="Update a club",
 *     title="ClubUpdate",
 *     @OA\Xml(
 *         name="ClubUpdate"
 *     )
 * )
 */
class ClubUpdate
{

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @AcmeAssert\NoHTML
	 * @OA\Property(type="string", example="Abc Club")
	 */
	private $name;

	/**
	 * @Assert\Type("boolean")
	 * @OA\Property(type="boolean", example="true")
	 */
	private $active;
	
	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min=2, max = 64)
	 * @Assert\Regex(pattern="/[a-z0-9_]{2,64}/")
	 * @OA\Property(type="string", example="abcdef13245", pattern="^[a-z0-9_]{2,64}$")
	 */
	private $uuid;
	
	/**
	 * @Assert\Length(max = 512)
	 * @AcmeAssert\NoHTML
	 * @OA\Property(type="string", example="mail_1@adresse.fr, mail_2@adresse.fr")
	 */
	private $contact_emails;
	
	/**
	 * @Assert\Length(max = 32)
	 * @AcmeAssert\NoHTML
	 * @OA\Property(type="string", example="0 892 70 12 39")
	 */
	private $contact_phone;
	
	/**
	 * @Assert\Length(max = 512)
	 * @AcmeAssert\NoHTML
	 * @OA\Property(type="string", example="mail_1@adresse.fr, mail_2@adresse.fr")
	 */
	private $mailing_list;
	
	/**
	 * @Assert\Length(max = 512)
	 * @Assert\Url
	 * @OA\Property(type="string", example="https://www.google.com")
	 */
	private $website_url;

	/**
	 * @Assert\Length(max = 512)
	 * @Assert\Url
	 * @OA\Property(type="string", example="https://facebook.com/pages/category/Local-Business/Taekwonkido-Cenacle-Rémi-Mollet-158619684187704/")
	 */
	private $facebook_url;

	/**
	 * @Assert\Length(max = 512)
	 * @Assert\Url
	 * @OA\Property(type="string", example="https://twitter.com/abc")
	 */
	private $twitter_url;

	/**
	 * @Assert\Length(max = 512)
	 * @Assert\Url
	 * @OA\Property(type="string", example="https://www.instagram.com/abc")
	 */
	private $instagram_url;

	/**
	 * @Assert\Length(max = 512)
	 * @Assert\Url
	 * @OA\Property(type="string", example="https://www.dailymotion.com/abc")
	 */
	private $dailymotion_url;
	
	/**
	 * @Assert\Length(max = 512)
	 * @Assert\Url
	 * @OA\Property(type="string", example="https://www.youtube.com/abc")
	 */
	private $youtube_url;
	
	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function isActive(): ?bool
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
	    return $this->contact_emails;
	}
	
	public function getContactEmailsToArray(): array
	{
	    if($this->contact_emails == null || '' === $this->contact_emails) {
	        return [];
	    }
	    return explode(',', str_replace(' ', '', $this->contact_emails));
	}
	
	public function setContactEmails(?string $contactEmails): self
	{
	    $this->contact_emails = $contactEmails;
	    return $this;
	}
	
	public function getContactPhone(): ?string
	{
	    return $this->contact_phone;
	}
	
	public function setContactPhone(?string $contactPhone): self
	{
	    $this->contact_phone = $contactPhone;
	    return $this;
	}
	
	public function getMailingList(): ?string
	{
	    return $this->mailing_list;
	}
	
	public function getMailingListToArray(): array
	{
	    if($this->mailing_list == null || '' === $this->mailing_list) {
	        return [];
	    }
	    return explode(',', str_replace(' ', '', $this->mailing_list));
	}
	
	public function setMailingList(?string $mailingList): self
	{
	    $this->mailing_list = $mailingList;
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

	public function getDailymotionUrl(): ?string
	{
	    return $this->dailymotion_url;
	}
	
	public function setDailymotionUrl(?string $dailymotion_url)
	{
	    $this->dailymotion_url = $dailymotion_url;
	}
	
	public function getYoutubeUrl(): ?string
	{
	    return $this->youtube_url;
	}
	
	public function setYoutubeUrl(?string $youtube_url)
	{
	    $this->youtube_url = $youtube_url;
	}
	
}
