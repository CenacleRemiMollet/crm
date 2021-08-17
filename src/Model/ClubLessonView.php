<?php

namespace App\Model;

use App\Entity\ClubLesson;
use App\Entity\ClubLocation;

class ClubLessonView
{
	private $uuid;
	private $point;
	private $discipline;
	private $age_level;
	private $day_of_week;
	private $start_time;
	private $end_time;
	private $location;

	public function __construct(ClubLesson $clubLesson)
	{
		$this->uuid = $clubLesson->getUuid();
		$this->point = $clubLesson->getPoint();
		$this->discipline = $clubLesson->getDiscipline();
		$this->age_level = $clubLesson->getAgeLevel();
		$this->day_of_week = $clubLesson->getDayOfWeek();
		$this->start_time = $clubLesson->getStartTime()->format('H:i');
		$this->end_time = $clubLesson->getEndTime()->format('H:i');
		$this->location = new ClubLocationView($clubLesson->getClubLocation());
	}

	public function getUuid(): ?string
	{
		return $this->uuid;
	}

	public function getPoint(): ?int
	{
		return $this->point;
	}

	public function getDiscipline(): ?string
	{
		return $this->discipline;
	}

	public function getAgeLevel(): ?string
	{
		return $this->age_level;
	}

	public function getDayOfWeek(): ?string
	{
		return $this->day_of_week;
	}
	
	public function getStartTime(): ?string
	{
		return $this->start_time;
	}
	
	public function getEndTime(): ?string
	{
		return $this->end_time;
	}

	public function getLocation(): ?ClubLocationView
	{
		return $this->location;
	}
	
}
