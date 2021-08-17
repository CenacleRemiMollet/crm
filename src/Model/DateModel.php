<?php
namespace App\Model;


/**
 * @Serializer\XmlRoot("date")
 * @OA\Schema(schema="DateModel")
 */
class DateModel
{

	/**
	 * @OA\Property(type="string", format="date-time", example="1997-07-16T19:20:30.4+0100")
	 */
	private $iso8601;

	/**
	 * @OA\Property(type="string", format="date", example="1997-07-16")
	 */
	private $date;

	/**
	 * @OA\Property(type="string", example="19:20:30")
	 */
	private $time;

	/**
	 * @OA\Property(type="string", example="16/07/1997")
	 */
	private $date_fr;

	public function __construct(\DateTime $date)
	{
		$this->iso8601 = $date->format(\DateTime::ISO8601);
		$this->date = $date->format("Y-m-d");
		$this->time = $date->format("H:i:s");
		$this->date_fr = $date->format("d/m/Y");
	}

	public function getIso8601()
	{
		return $this->iso8601;
	}

	public function getDate()
	{
		return $this->date;
	}

	public function getTime()
	{
		return $this->time;
	}

	public function getDate_fr()
	{
		return $this->date_fr;
	}

	public function __toString()
	{
		return $this->iso8601;
	}
	

}

