<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Util\StringUtils;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClubRepository")
 * @ORM\Table(
 *	  indexes={@ORM\Index(name="idx_club_uuid", columns={"uuid"})},
 *	  uniqueConstraints={@ORM\UniqueConstraint(columns={"uuid"})})
 */
class Club
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=64)
	 */
	private $uuid;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $name;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $logo;

	/**
	 * @ORM\Column(type="string", length=512, nullable=true)
	 */
	private $website_url;

	/**
	 * @ORM\Column(type="string", length=512, nullable=true)
	 */
	private $facebook_url;

	/**
	 * @ORM\Column(type="string", length=512, nullable=true)
	 */
	private $twitter_url;

	/**
	 * @ORM\Column(type="string", length=512, nullable=true)
	 */
	private $instagram_url;

	/**
	 * @ORM\Column(type="string", length=512, nullable=true)
	 */
	private $dailymotion_url;
	
	/**
	 * @ORM\Column(type="string", length=512, nullable=true)
	 */
	private $youtube_url;

	/**
	 * @ORM\Column(type="string", length=512, nullable=true)
	 */
	private $mailing_list;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $active;

	/**
	 * @ORM\Column(type="string", length=512, nullable=true)
	 */
	private $contact_emails;
	
	/**
	 * @ORM\Column(type="string", length=64, nullable=true)
	 */
	private $contact_phone;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\ClubLesson", mappedBy="club", orphanRemoval=true)
	 */
	private $clubLessons;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\UserClubSubscribe", mappedBy="club", orphanRemoval=true)
	 */
	private $userClubSubscribes;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\ClubProperty", mappedBy="club", orphanRemoval=true)
	 */
	private $clubProperties;
	
	public function __construct()
  	{
  		$this->clubLessons = new ArrayCollection();
  		$this->uuid = StringUtils::random_str(16);
  		$this->userClubSubscribes = new ArrayCollection();
  		$this->clubProperties = new ArrayCollection();
  	}

	public function getId(): ?int
  	{
  		return $this->id;
  	}

	public function getUuid(): ?string
  	{
  		return $this->uuid;
  	}

	public function setUuid(string $uuid): self
  	{
  		$this->uuid = $uuid;
  		return $this;
  	}

	public function getName(): ?string
  	{
  		return $this->name;
  	}

	public function setName(string $name): self
  	{
  		$this->name = $name;
  		return $this;
  	}

	public function getLogo(): ?string
  	{
  		return $this->logo;
  	}

	public function setLogo(string $logo): self
  	{
  		$this->logo = $logo;
  		return $this;
  	}

	public function getWebsiteUrl(): ?string
  	{
  		return $this->website_url;
  	}

	public function setWebsiteUrl(?string $website_url): self
  	{
  		$this->website_url = $website_url;
  		return $this;
  	}

	public function getFacebookUrl(): ?string
  	{
  		return $this->facebook_url;
  	}

	public function setFacebookUrl(?string $facebook_url): self
  	{
  		$this->facebook_url = $facebook_url;
  		return $this;
  	}

	public function getMailingList(): ?string
  	{
  		return $this->mailing_list;
  	}

	public function setMailingList(?string $mailing_list): self
  	{
  		$this->mailing_list = $mailing_list;
  		return $this;
  	}

	public function getActive(): ?bool
  	{
  		return $this->active;
  	}

	public function setActive(bool $active): self
  	{
  		$this->active = $active;
  		return $this;
  	}

  	public function getContactEmails(): ?string
  	{
  	    return $this->contact_emails;
  	}
  	
  	public function setContactEmails(?string $contact_emails): self
  	{
  	    $this->contact_emails = $contact_emails;
  	    return $this;
  	}
                  	
  	public function getContactPhone(): ?string
  	{
  	    return $this->contact_phone;
  	}
  	
  	public function setContactPhone(?string $contact_phone): self
  	{
  	    $this->contact_phone = $contact_phone;
  	    return $this;
  	}
  	
  	/**
	 * @return Collection|ClubLesson[]
	 */
	public function getClubLessons(): Collection
  	{
  		return $this->clubLessons;
  	}

	public function addClubLesson(ClubLesson $clubLesson): self
  	{
  		if (!$this->clubLessons->contains($clubLesson)) {
  			$this->clubLessons[] = $clubLesson;
  			$clubLesson->setClub($this);
  		}
  		return $this;
  	}

	public function removeClubLesson(ClubLesson $clubLesson): self
  	{
  		if ($this->clubLessons->contains($clubLesson)) {
  			$this->clubLessons->removeElement($clubLesson);
  			// set the owning side to null (unless already changed)
  			if ($clubLesson->getClub() === $this) {
  				$clubLesson->setClub(null);
  			}
  		}
  		return $this;
  	}

	/**
	 * @return Collection|UserClubSubscribe[]
	 */
	public function getUserClubSubscribes(): Collection
  	{
  		return $this->userClubSubscribes;
  	}

	public function addUserClubSubscribe(UserClubSubscribe $userClubSubscribe): self
  	{
  		if (!$this->userClubSubscribes->contains($userClubSubscribe)) {
  			$this->userClubSubscribes[] = $userClubSubscribe;
  			$userClubSubscribe->setClub($this);
  		}
  		return $this;
  	}

	public function removeUserClubSubscribe(UserClubSubscribe $userClubSubscribe): self
  	{
  		if ($this->userClubSubscribes->contains($userClubSubscribe)) {
  			$this->userClubSubscribes->removeElement($userClubSubscribe);
  			// set the owning side to null (unless already changed)
  			if ($userClubSubscribe->getClub() === $this) {
  				$userClubSubscribe->setClub(null);
  			}
  		}
  		return $this;
  	}

  	/**
  	 * @return Collection|UserClubSubscribe[]
  	 */
  	public function getClubProperties(): Collection
  	{
  	    return $this->clubProperties;
  	}
  	
  	public function addClubProperty(ClubProperty $clubProperty): self
  	{
  	    if (!$this->clubProperties->contains($clubProperty)) {
  	        $this->clubProperties[] = $clubProperty;
  	        $clubProperty->setClub($this);
  	    }
  	    return $this;
  	}
  	
  	public function removeClubProperty(ClubProperty $clubProperty): self
  	{
  	    if ($this->clubProperties->contains($clubProperty)) {
  	        $this->clubProperties->removeElement($clubProperty);
  	        // set the owning side to null (unless already changed)
  	        if ($clubProperty->getClub() === $this) {
  	            $clubProperty->setClub(null);
  	        }
  	    }
  	    return $this;
  	}
  	
  	public function getTwitterUrl(): ?string
    {
        return $this->twitter_url;
    }

    public function setTwitterUrl(?string $twitter_url): self
    {
        $this->twitter_url = $twitter_url;
        return $this;
    }

    public function getInstagramUrl(): ?string
    {
        return $this->instagram_url;
    }

    public function setInstagramUrl(?string $instagram_url): self
    {
        $this->instagram_url = $instagram_url;
        return $this;
    }
    
    public function getDailymotionUrl(): ?string
    {
        return $this->dailymotion_url;
    }
    
    public function setDailymotionUrl(?string $dailymotion_url): self
    {
        $this->dailymotion_url = $dailymotion_url;
        return $this;
    }

    public function getYoutubeUrl(): ?string
    {
        return $this->youtube_url;
    }
    
    public function setYoutubeUrl(?string $youtube_url): self
    {
        $this->youtube_url = $youtube_url;
        return $this;
    }
    
  
}
