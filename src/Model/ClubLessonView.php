<?php

namespace App\Model;

use App\Entity\ClubLesson;
use App\Entity\ClubLocation;
use OpenApi\Annotations as OA;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use App\Entity\Club;

/**
 * @OA\Schema(schema="ClubLesson")
 * @Serializer\XmlRoot("club")
 * @Hateoas\Relation("self", href = "expr('/crm/api/club/' ~ object.getClubUuid() ~ '/lessons/' ~ object.getUuid())")
 *
 * @author f.agu
 */
class ClubLessonView
{
    /**
     * @OA\Property(type="string", example="abcd-xyz")
     */
    private $club_uuid;
    
	/**
	 * @OA\Property(type="string", example="abcd-xyz")
	 */
	private $uuid;

	/**
	 * @OA\Property(type="integer", format="int32", example="1", minimum=0, maximum=10)
	 */
	private $point;

	/**
	 * @OA\Property(type="string", example="Taekwondo")
	 */
	private $discipline;

	/**
	 * @OA\Property(type="string", example="Enfants")
	 */
	private $age_level;

	/**
	 * @OA\Property(type="string", example="monday", enum={"monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"})
	 */
	private $day_of_week;

	/**
	 * @OA\Property(type="string", example="19:30", pattern="^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$")
	 */
	private $start_time;

	/**
	 * @OA\Property(type="string", example="20:45", pattern="^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$")
	 */
	private $end_time;

	/**
	 * @OA\Property(type="string", example="20:45", pattern="^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$")
	 */
	private $duration;
	
	/**
	 * @OA\Property(ref="#/components/schemas/ClubLocation")
	 */
	private $location;

	public function __construct(Club $club, ClubLesson $clubLesson)
	{
		$this->club_uuid = $club->getUuid();
		$this->uuid = $clubLesson->getUuid();
		$this->point = $clubLesson->getPoint();
		$this->discipline = $clubLesson->getDiscipline();
		$this->age_level = $clubLesson->getAgeLevel();
		$this->day_of_week = $clubLesson->getDayOfWeek();
		$this->start_time = $clubLesson->getStartTime()->format('H:i');
		$this->end_time = $clubLesson->getEndTime()->format('H:i');
		$this->duration = $clubLesson->getStartTime()->diff($clubLesson->getEndTime());
		$this->location = new ClubLocationView($clubLesson->getClubLocation());
	}

	public function getClubUuid(): ?string
	{
	    return $this->club_uuid;
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
	
	public function getDuration(): ?string
	{
	    return $this->duration;
	}

	public function getLocation(): ?ClubLocationView
	{
		return $this->location;
	}

}
