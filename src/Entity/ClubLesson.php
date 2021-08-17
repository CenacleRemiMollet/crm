<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Util\StringUtils;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClubLessonRepository")
 * @ORM\Table(
 *	  indexes={@ORM\Index(name="idx_club_lesson_uuid", columns={"uuid"})},
 *	  uniqueConstraints={@ORM\UniqueConstraint(columns={"uuid"})})
 */
class ClubLesson
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=16)
	 */
	private $uuid;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\ClubLocation", inversedBy="clubLessons", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $club_location;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Club", inversedBy="clubLessons", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $club;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $point;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $discipline;

	/**
	 * @ORM\Column(type="string", length=512, nullable=true)
	 */
	private $age_level;

	/**
	 * @ORM\Column(type="string", length=20)
	 */
	private $day_of_week;

	/**
	 * @ORM\Column(type="time")
	 */
	private $start_time;

	/**
	 * @ORM\Column(type="time")
	 */
	private $end_time;

	public function __construct()
	{
		$this->clubLessonTimeSlots = new ArrayCollection();
		$this->userClubSubscribes = new ArrayCollection();
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

	public function getClubLocation(): ?ClubLocation
	{
		return $this->club_location;
	}

	public function setClubLocation(?ClubLocation $club_location): self
	{
		$this->club_location = $club_location;

		return $this;
	}

	public function getClub(): ?Club
	{
		return $this->club;
	}

	public function setClub(?Club $club): self
	{
		$this->club = $club;

		return $this;
	}

	public function getPoint(): ?int
	{
		return $this->point;
	}

	public function setPoint(int $point): self
	{
		$this->point = $point;

		return $this;
	}

	public function getDiscipline(): ?string
	{
		return $this->discipline;
	}

	public function setDiscipline(string $discipline): self
	{
		$this->discipline = $discipline;

		return $this;
	}

	public function getAgeLevel(): ?string
	{
		return $this->age_level;
	}

	public function setAgeLevel(?string $age_level): self
	{
		$this->age_level = $age_level;

		return $this;
	}

	public function getDayOfWeek(): ?string
	{
		return $this->day_of_week;
	}

	public function setDayOfWeek(string $day_of_week): self
	{
		$this->day_of_week = $day_of_week;

		return $this;
	}

	public function getStartTime(): ?\DateTimeInterface
	{
		return $this->start_time;
	}

	public function setStartTime(\DateTimeInterface $start_time): self
	{
		$this->start_time = $start_time;

		return $this;
	}

	public function getEndTime(): ?\DateTimeInterface
	{
		return $this->end_time;
	}

	public function setEndTime(\DateTimeInterface $end_time): self
	{
		$this->end_time = $end_time;

		return $this;
	}



}
