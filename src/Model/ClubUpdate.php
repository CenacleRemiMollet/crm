<?php

namespace App\Model;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="ClubUpdate",
 *     description="Update a club",
 *     title="ClubUpdate",
 *     @OA\Xml(
 *         name="ClubUpdate"
 *     )
 * )
 */
class ClubUpdate
{

	/**
	 * @Assert\NotBlank
	 * @Assert\Type("string")
	 * @Assert\Length(min = 1, max = 255)
	 * @OA\Property(type="string", example="Abc Club")
	 */
	private $name;

	/**
	 * @Assert\Length(min = 1, max = 512)
	 * @OA\Property(type="string", example="https://www.google.com")
	 */
	private $website_url;

	/**
	 * @Assert\Length(min = 1, max = 512)
	 * @OA\Property(type="string", example="https://facebook.com/pages/category/Local-Business/Taekwonkido-Cenacle-Rémi-Mollet-158619684187704/")
	 */
	private $facebook_url;

	/**
	 * @Assert\Length(min = 1, max = 512)
	 * @OA\Property(type="string", example="https://twitter.com/abc")
	 */
	private $twitter_url;

	/**
	 * @Assert\Length(min = 1, max = 512)
	 * @OA\Property(type="string", example="https://www.instagram.com/abc")
	 */
	private $instagram_url;

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getWebsiteUrl(): ?string
	{
		return $this->website_url;
	}

	public function setWebsiteUrl($website_url)
	{
		$this->website_url = $website_url;
	}

	public function getFacebookUrl(): ?string
	{
		return $this->facebook_url;
	}

	public function setFacebookUrl($facebook_url)
	{
		$this->facebook_url = $facebook_url;
	}

	public function getTwitterUrl(): ?string
	{
		return $this->twitter_url;
	}

	public function setTwitterUrl($twitter_url)
	{
		$this->twitter_url = $twitter_url;
	}

	public function getInstagramUrl(): ?string
	{
		return $this->instagram_url;
	}

	public function setInstagramUrl($instagram_url)
	{
		$this->instagram_url = $instagram_url;
	}


}
