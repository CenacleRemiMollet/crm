<?php

namespace App\Model;

use App\Entity\ClubLesson;
use App\Entity\ClubLocation;
use OpenApi\Annotations as OA;
use App\Entity\ConfigurationProperty;

/**
 * @OA\Schema(schema="ConfigurationProperty")
 *
 * @author f.agu
 */
class ConfigurationPropertyView
{

	/**
	 * @OA\Property(type="string", example="my.key")
	 */
	private $key;

	/**
	 * @OA\Property(type="string", example="my-value")
	 */
	private $value;

	//private $updated_date;

	//private $updater_user_id;

	//private $previous_value;

	public function __construct(ConfigurationProperty $configurationProperty)
	{
		$this->key = $configurationProperty->getPropertyKey();
		$this->value = $configurationProperty->getPropertyValue();
	}

	public function getKey(): ?string
	{
		return $this->key;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

// 	public function getUpdatedDate(): ?\DateTimeInterface
// 	{
// 		return $this->updated_date;
// 	}

// 	public function getUpdaterUserId(): ?int
// 	{
// 		return $this->updater_user_id;
// 	}

// 	public function getPreviousValue(): ?string
// 	{
// 		return $this->previous_value;
// 	}

}
