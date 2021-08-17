<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountSessionHistoryRepository")
 */
class AccountSessionHistory
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
	 * @ORM\Column(type="string", length=48)
	 */
	private $ip;

	/**
	 * @ORM\Column(type="string", length=400)
	 */
	private $user_agent;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $start_datetime;

	public function __construct()
	{
		$this->start_datetime = new \DateTime();
	}


	public function getId(): ?int
	{
		return $this->id;
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

	public function getIp(): ?string
	{
		return $this->ip;
	}

	public function setIp(string $ip): self
	{
		$this->ip = $ip;
		return $this;
	}

	public function getUserAgent(): ?string
	{
		return $this->user_agent;
	}

	public function setUserAgent(string $user_agent): self
	{
		$this->user_agent = $user_agent;
		return $this;
	}

	public function getStartDatetime(): ?\DateTimeInterface
	{
		return $this->start_datetime;
	}

	public function setStartDatetime(\DateTimeInterface $start_datetime): self
	{
		$this->start_datetime = $start_datetime;
		return $this;
	}
}
