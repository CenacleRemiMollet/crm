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
	private $mailing_list;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $active;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\ClubLesson", mappedBy="club", orphanRemoval=true)
	 */
	private $clubLessons;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\UserClubSubscribe", mappedBy="club", orphanRemoval=true)
	 */
	private $userClubSubscribes;

	public function __construct()
	{
		$this->clubLessons = new ArrayCollection();
		$this->uuid = StringUtils::random_str(16);
		$this->userClubSubscribes = new ArrayCollection();
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
}
