<?php

namespace App\Model;

use App\Validator\Constraints as AcmeAssert;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'ClubLessonCreate',
    title: 'ClubLessonCreate',
    description: 'Create a club lesson',
    required: ['discipline', 'day_of_week', 'start_time', 'end_time'],
    xml: new OA\Xml(name: 'ClubLessonCreate')
)]
class ClubLessonCreate
{

	#[OA\Property(type: 'string', pattern: '^[A-Za-z0-9_]{2,64}$', example: 'abcdef13245')]
    #[Assert\Type('string')]
    #[Assert\Length(min: 2, max: 64)]
    #[Assert\Regex(pattern: '/[A-Za-z0-9_]{2,64}/')]
    private $location_uuid;

	#[OA\Property(type: 'string', pattern: '^[A-Za-z0-9_]{2,64}$', example: 'abcdef13245')]
    #[Assert\Type('string')]
    #[Assert\Length(min: 2, max: 64)]
    #[Assert\Regex(pattern: '/[A-Za-z0-9_]{2,64}/')]
    private $uuid;
	
	#[OA\Property(type: 'integer', default: 1)]
    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 20)]
    private $point = 1;

	#[OA\Property(type: 'string', example: 'Taekwondo')]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 1, max: 255)]
    #[AcmeAssert\NoHTML]
    private $discipline;

	#[OA\Property(type: 'string', example: 'baby')]
    #[Assert\Type('string')]
    #[Assert\Length(max: 512)]
    #[AcmeAssert\NoHTML]
    private $age_level;

	#[OA\Property(
	    type: 'string',
	    enum: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
	    example: 'monday'
	)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 1, max: 20)]
    #[AcmeAssert\DayOfWeek]
    private $day_of_week;

	#[OA\Property(type: 'time', example: '19:00')]
    #[Assert\NotNull]
    #[AcmeAssert\HourMinute]
    private $start_time;

	#[OA\Property(type: 'time', example: '20:00')]
    #[Assert\NotNull]
    #[AcmeAssert\HourMinute]
    private $end_time;
	
	#[OA\Property(type: 'string')]
    #[Assert\Type('string')]
    #[Assert\Length(max: 255)]
    #[AcmeAssert\NoHTML]
    private $description;

	/**
	 * Planning color, empty = color of the discipline
	 */
	#[OA\Property(type: 'string', pattern: '^(#[0-9a-fA-F]{6})?$', example: '#E66F78')]
    #[Assert\Type('string')]
    #[Assert\Regex(pattern: '/^(#[0-9a-fA-F]{6})?$/', message: 'Color must be #RRGGBB')]
    private $color;
	
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

	public function getStartTime(): \DateTimeInterface
	{
	    return new \DateTime($this->start_time);
	}

	public function setStartTime($start_time)
	{
	    $this->start_time = $start_time;
	}

	public function getEndTime(): \DateTimeInterface
	{
	    return new \DateTime($this->end_time);
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

	public function getColor(): ?string
	{
	    return $this->color;
	}

	public function setColor($color)
	{
	    $this->color = $color;
	}
}
