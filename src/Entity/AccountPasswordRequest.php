<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Util\StringUtils;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountPasswordRequestRepository")
 */
class AccountPasswordRequest
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Account")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $account;

	/**
	 * @ORM\Column(type="string", length=40)
	 */
	private $uuid;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $create_date;

	public function __construct()
	{
		$this->uuid = StringUtils::random_str(40);
		$this->create_date = new \DateTime();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(int $id): self
	{
		$this->id = $id;
		return $this;
	}
	
	public function getAccount(): ?Account
	{
		return $this->account;
	}

	public function setAccount(?Account $account): self
	{
		$this->account = $account;
		return $this;
	}

	public function getUuid(): ?string
	{
		return $this->uuid;
	}

	public function setUuid(string $uuid): self
	{
		$this->uuid = $uuid;
		return $this;
	}

	public function getCreateDate(): ?\DateTimeInterface
	{
		return $this->create_date;
	}

	public function setCreateDate(\DateTimeInterface $create_date): self
	{
		$this->create_date = $create_date;
		return $this;
	}
}
