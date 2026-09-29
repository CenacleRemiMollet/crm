<?php

namespace App\Model;

use App\Validator\Constraints as AcmeAssert;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'ClubCreate',
    title: 'ClubCreate',
    description: 'Create a club',
    required: ['name'],
    xml: new OA\Xml(name: 'ClubCreate')
)]
class ClubCreate
{

	#[OA\Property(type: 'string', example: 'Abc Club')]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 1, max: 255)]
    #[AcmeAssert\NoHTML]
    private $name;

	#[OA\Property(type: 'boolean', example: 'true')]
	private $active = true;

	#[OA\Property(type: 'string', example: 'abc_club')]
    #[Assert\Length(max: 64)]
    #[AcmeAssert\NoHTML]
    private $uuid;
	
	#[OA\Property(type: 'string', example: 'mail_1@adresse.fr, mail_2@adresse.fr')]
    #[Assert\Length(max: 512)]
    #[AcmeAssert\NoHTML]
    private $contact_emails;
	
	#[OA\Property(type: 'string', example: '0 892 70 12 39')]
    #[Assert\Length(max: 32)]
    #[AcmeAssert\NoHTML]
    private $contact_phone;
	
	#[OA\Property(type: 'string', example: 'mail_1@adresse.fr, mail_2@adresse.fr')]
    #[Assert\Length(max: 512)]
    #[AcmeAssert\NoHTML]
    private $mailing_list;
	
	#[OA\Property(type: 'string', example: 'https://www.google.com')]
    #[Assert\Length(max: 512)]
    #[Assert\Url(requireTld: false)]
    private $website_url;

	#[OA\Property(
	    type: 'string',
	    example: 'https://facebook.com/pages/category/Local-Business/Taekwonkido-Cenacle-Rémi-Mollet-158619684187704/'
	)]
    #[Assert\Length(max: 512)]
    #[Assert\Url(requireTld: false)]
    private $facebook_url;

	#[OA\Property(type: 'string', example: 'https://twitter.com/abc')]
    #[Assert\Length(max: 512)]
    #[Assert\Url(requireTld: false)]
    private $twitter_url;

	#[OA\Property(type: 'string', example: 'https://www.instagram.com/abc')]
    #[Assert\Length(max: 512)]
    #[Assert\Url(requireTld: false)]
    private $instagram_url;

	#[OA\Property(type: 'string', example: 'https://www.dailymotion.com/abc')]
    #[Assert\Length(max: 512)]
    #[Assert\Url(requireTld: false)]
    private $dailymotion_url;
	
	#[OA\Property(type: 'string', example: 'https://www.youtube.com/abc')]
    #[Assert\Length(max: 512)]
    #[Assert\Url(requireTld: false)]
    private $youtube_url;
	
	#[OA\Property(type: 'number', format: 'float', example: '29', nullable: true)]
    #[Assert\Type('float')]
    private $price_cenacle_joining;
	
	#[OA\Property(type: 'number', format: 'float', example: '79', nullable: true)]
    #[Assert\Type('float')]
    private $price_base_subscribe;
	
	
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
	
	public function getPriceCenacleJoining(): ?float
	{
	    return $this->price_cenacle_joining;
	}
	
	public function setPriceCenacleJoining(?float $price_cenacle_joining)
	{
	    $this->price_cenacle_joining = $price_cenacle_joining;
	}
	
	public function getPriceBaseSubscribe(): ?float
	{
	    return $this->price_base_subscribe;
	}
	
	public function setPriceBaseSubscribe(?float $price_base_subscribe)
	{
	    $this->price_base_subscribe = $price_base_subscribe;
	}
}
