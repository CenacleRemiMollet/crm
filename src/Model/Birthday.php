<?php
namespace App\Model;

use App\Util\DateIntervalUtils;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;

/**
 * @Serializer\XmlRoot("birthday")
 * @OA\Schema(schema="Birthday")
 */
class Birthday extends DateModel
{
	/**
	 * @OA\Property(type="age_in_year", example="25")
	 */
	private $age_in_year;

	/**
	 * @OA\Property(type="age", example="P25Y4MT10M")
	 */
	private $age_iso8601;

	public function __construct(\DateTime $date)
	{
		parent::__construct($date);
		$now = new \DateTime();
		$interval = $date->diff($now);
		$this->age_in_year = $interval->y;
		$this->age_iso8601 = DateIntervalUtils::toIso8601($interval);
	}

	public function getAgeInYear()
	{
		return $this->age_in_year;
	}

	public function getAgeIso8601()
	{
		return $this->age_iso8601;
	}

}

