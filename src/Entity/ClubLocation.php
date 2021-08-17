<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Util\StringUtils;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClubLocationRepository")
 * @ORM\Table(
 *	  indexes={@ORM\Index(name="idx_club_location_uuid", columns={"uuid"})},
 *	  uniqueConstraints={@ORM\UniqueConstraint(columns={"uuid"})})
 */
class ClubLocation
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
	private $address;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $city;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $zipcode;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $county;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $country;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\ClubLesson", mappedBy="club_location", orphanRemoval=true)
	 */
	private $clubLessons;

	public function __construct()
	{
		$this->clubLessons = new ArrayCollection();
		$this->uuid = StringUtils::random_str(16);
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

	public function getAddress(): ?string
	{
		return $this->address;
	}

	public function setAddress(string $address): self
	{
		$this->address = $address;

		return $this;
	}

	public function getCity(): ?string
	{
		return $this->city;
	}

	public function setCity(string $city): self
	{
		$this->city = $city;

		return $this;
	}

	public function getZipcode(): ?string
	{
		return $this->zipcode;
	}

	public function setZipcode(string $zipcode): self
	{
		$this->zipcode = $zipcode;

		return $this;
	}

	public function getCounty(): ?string
	{
		return $this->county;
	}

	public function setCounty(string $county): self
	{
		$this->county = $county;

		return $this;
	}

	public function getCountry(): ?string
	{
		return $this->country;
	}

	public function setCountry(string $country): self
	{
		$this->country = $country;

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
			$clubLesson->setClubLocation($this);
		}

		return $this;
	}

	public function removeClubLesson(ClubLesson $clubLesson): self
	{
		if ($this->clubLessons->contains($clubLesson)) {
			$this->clubLessons->removeElement($clubLesson);
			// set the owning side to null (unless already changed)
			if ($clubLesson->getClubLocation() === $this) {
				$clubLesson->setClubLocation(null);
			}
		}

		return $this;
	}
}
