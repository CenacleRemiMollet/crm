<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserClubSubscribeRepository")
 */
class UserClubSubscribe
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userClubSubscribes")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
	 * @ORM\Column(type="json")
	 */
	private $roles;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	private $subscribe_date;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	private $unsubscribe_date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Club", inversedBy="userClubSubscribes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $club;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUser(): ?User
	{
		return $this->user;
	}

	public function setUser(?User $user): self
	{
		$this->user = $user;
		return $this;
	}

	public function getRoles(): ?array
	{
		return array_unique($this->roles);
	}

	public function setRoles(array $roles): self
	{
		$this->roles = $roles;
		return $this;
	}

	public function getSubscribeDate(): ?\DateTimeInterface
	{
		return $this->subscribe_date;
	}

	public function setSubscribeDate(?\DateTimeInterface $subscribe_date): self
	{
		$this->subscribe_date = $subscribe_date;
		return $this;
	}

	public function getUnsubscribeDate(): ?\DateTimeInterface
	{
		return $this->unsubscribe_date;
	}

	public function setUnsubscribeDate(?\DateTimeInterface $unsubscribe_date): self
	{
		$this->unsubscribe_date = $unsubscribe_date;
		return $this;
	}

	public function getClub(): ?Club
	{
		return $this->club;
	}

	public function setClub(?Club $club): self
	{
		$this->club = $club;
		return $this;
	}
}
