<?php

namespace App\Model;

use App\Validator\Constraints as AcmeAssert;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="ClubLessonUpdate",
 *     description="Update a club lesson",
 *     title="ClubLessonUpdate",
 *     @OA\Xml(
 *         name="ClubLessonUpdate"
 *     )
 * )
 */
class ClubLessonUpdate
{

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min=2, max = 64)
	 * @Assert\Regex(pattern="/[A-Za-z0-9_]{2,64}/")
	 * @OA\Property(type="string", example="abcdef13245", pattern="^[A-Za-z0-9_]{2,64}$")
	 */
	private $location_uuid;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min=2, max = 64)
	 * @Assert\Regex(pattern="/[A-Za-z0-9_]{2,64}/")
	 * @OA\Property(type="string", example="abcdef13245", pattern="^[A-Za-z0-9_]{2,64}$")
	 */
	private $uuid;
	
	/**
	 * @Assert\Type("integer")
	 * @Assert\Range(min = 1, max = 20)
	 * @OA\Property(type="integer", default = 1)
	 */
	private $point;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @OA\Property(type="string", example="Taekwondo")
	 */
	private $discipline;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(max = 512)
	 * @OA\Property(type="string", example="baby")
	 */
	private $age_level;

	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 20)
	 * @OA\Property(type="string", example="monday", enum={"monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"})
	 */
	private $day_of_week;

	/**
	 * @AcmeAssert\HourMinute
	 * @OA\Property(type="time", example="19:00")
	 */
	private $start_time;

	/**
	 * @AcmeAssert\HourMinute
	 * @OA\Property(type="time", example="20:00")
	 */
	private $end_time;
	
	/**
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @OA\Property(type="string")
	 */
	private $description;
	
	public function getLocationUuid(): ?string
	{
		return $this->location_uuid;
	}

	public function setLocationUuid($location_uuid)
	{
	    $this->location_uuid = $location_uuid;
	}

	public function getUuid(): ?string
	{
	    return $this->uuid;
	}
	
	public function setUuid($uuid)
	{
	    $this->uuid = $uuid;
	}
	
	public function getPoint(): ?int
	{
	    return $this->point;
	}

	public function setPoint($point)
	{
	    $this->point = $point;
	}

	public function getDiscipline(): ?string
	{
	    return $this->discipline;
	}

	public function setDiscipline($discipline)
	{
	    $this->discipline = $discipline;
	}

	public function getAgeLevel(): ?string
	{
		return $this->age_level;
	}

	public function setAgeLevel($age_level)
	{
	    $this->age_level = $age_level;
	}

	public function getDayOfWeek(): ?string
	{
	    return $this->day_of_week;
	}

	public function setDayOfWeek($day_of_week)
	{
	    $this->day_of_week = $day_of_week;
	}

	public function getStartTime():? \DateTimeInterface
	{
	    return $this->start_time !== null ? new \DateTime($this->start_time) : null;
	}

	public function setStartTime($start_time)
	{
	    $this->start_time = $start_time;
	}

	public function getEndTime():? \DateTimeInterface
	{
	    return $this->end_time !== null ? new \DateTime($this->end_time) : null;
	}
	
	public function setEndTime($end_time)
	{
	    $this->end_time = $end_time;
	}

	public function getDescription(): ?string
	{
	    return $this->description;
	}
	
	public function setDescription($description)
	{
	    $this->description = $description;
	}
	
}
