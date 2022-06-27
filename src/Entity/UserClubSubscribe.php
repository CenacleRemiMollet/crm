<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Util\StringUtils;

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
	 * @ORM\Column(type="string", length=64)
	 */
	private $uuid;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userClubSubscribes")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Club", inversedBy="userClubSubscribes", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $club;
	
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

    public function __construct()
    {
        $this->uuid = StringUtils::random_str(16);
        $this->subscribe_date = new \DateTime();
     }
    
    public function getId(): ?int
	{
		return $this->id;
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
	    return $this->roles !== null ? array_unique($this->roles) : null;
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
