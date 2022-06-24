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
class Birthday
{
    /**
     * @OA\Property(type="string", format="date", example="1997-07-16")
     */
    private $date;
    
    /**
     * @OA\Property(type="string", example="16/07/1997")
     */
    private $date_fr;
    
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
	    $this->date = $date->format("Y-m-d");
	    $this->date_fr = $date->format("d/m/Y");
		$now = new \DateTime();
		$interval = $date->diff($now);
		$this->age_in_year = $interval->y;
		$this->age_iso8601 = DateIntervalUtils::toIso8601($interval);
	}

	public function getDate()
	{
	    return $this->date;
	}
	
	public function getDate_fr()
	{
	    return $this->date_fr;
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

