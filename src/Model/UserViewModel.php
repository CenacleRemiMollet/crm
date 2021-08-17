<?php

namespace App\Model;

use App\Entity\User;

/**
 * @Serializer\XmlRoot("user")
 * @OA\Schema(schema="UserViewModel")
 */
class UserViewModel
{
	/**
	 * @OA\Property(type="string", example="abcDEF654")
	 */
	private $uuid;

	/**
	 * @OA\Property(type="string", example="Doe")
	 */
	private $lastname;

	/**
	 * @OA\Property(type="string", example="John")
	 */
	private $firstname;

	/**
	 * @OA\Property(type="object", items = @OA\Items(ref="#/components/schemas/DateModel"))
	 */
	private $birthday;

	/**
	 * @OA\Property(type="string", example="F")
	 */
	private $sex;

	/**
	 * @OA\Property(type="string", example="5 Avenue Anatole France")
	 */
	private $address;

	/**
	 * @OA\Property(type="string", example="75007")
	 */
	private $zipcode;

	/**
	 * @OA\Property(type="string", example="Paris")
	 */
	private $city;

	/**
	 * @OA\Property(type="string", example="0 892 70 12 39")
	 */
	private $phone;

	/**
	 * @OA\Property(type="string", example="0 892 70 12 39")
	 */
	private $phone_emergency;

	/**
	 * @OA\Property(type="string", example="Française")
	 */
	private $nationality;

	/**
	 * @OA\Property(type="string", example="mail_1@adresse.fr, mail_2@adresse.fr")
	 */
	private $mails;

	/**
	 * @OA\Property(type="object", items = @OA\Items(ref="#/components/schemas/DateModel"))
	 */
	private $created;

	public function __construct(User $user)
	{
		$this->uuid = $user->getUuid();
		$this->lastname = $user->getLastname();
		$this->firstname = $user->getFirstname();
		$this->birthday = new Birthday($user->getBirthday());
		$this->sex = $user->getSex();
		$this->address = $user->getAddress();
		$this->zipcode = $user->getZipcode();
		$this->city = $user->getCity();
		$this->phone = $user->getPhone();
		$this->phone_emergency = $user->getPhoneEmergency();
		$this->nationality = $user->getNationality();
		$this->mails = implode(', ', $user->getMails());
		$this->created = new DateModel($user->getCreated());
	}

	public function getUuid()
	{
		return $this->uuid;
	}

	public function getLastname()
	{
		return $this->lastname;
	}

	public function getFirstname()
	{
		return $this->firstname;
	}

	public function getBirthday()
	{
		return $this->birthday;
	}

	public function getSex(): ?string
	{
		return $this->sex;
	}

	public function getAddress(): ?string
	{
		return $this->address;
	}

	public function getZipcode(): ?string
	{
		return $this->zipcode;
	}

	public function getCity(): ?string
	{
		return $this->city;
	}

	public function getPhone(): ?string
	{
		return $this->phone;
	}

	public function getPhoneEmergency(): ?string
	{
		return $this->phone_emergency;
	}

	public function getNationality(): ?string
	{
		return $this->nationality;
	}

	public function getMails(): ?string
	{
		return $this->mails;
	}

	public function getCreated()
	{
		return $this->created;
	}

}