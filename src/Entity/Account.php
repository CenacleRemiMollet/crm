<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 */
class Account implements UserInterface
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="accounts")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
	 * @ORM\Column(type="string", length=255, unique=true)
	 */
	private $login;

	/**
	 * @var string The hashed password
	 * @ORM\Column(type="string", length=1024)
	 */
	private $password;

	 /**
	 * @ORM\Column(type="json")
	 */
	private $roles = [];

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $has_access;


	public function __construct()
	{
		$this->has_access = true;
	}

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

	public function getLogin(): ?string
	{
		return $this->login;
	}

	public function setLogin(string $login): self
	{
		$this->login = $login;
		return $this;
	}

	public function getPassword(): ?string
	{
		return $this->password;
	}

	public function setPassword(string $password): self
	{
		$this->password = $password;
		return $this;
	}

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUsername(): string
	{
		return (string) $this->login;
	}

	/**
	 * @see UserInterface
	 */
	public function getRoles(): array
	{
		$roles = $this->roles;
		// guarantee every user at least has ROLE_USER
		$roles[] = 'ROLE_USER';
		return array_unique($roles);
	}

	public function setRoles(array $roles): self
	{
		$this->roles = $roles;
		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function getSalt()
	{
		// not needed when using the "bcrypt" algorithm in security.yaml
	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials()
	{
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}

	public function __toString()
	{
		return 'Account[id: '.$this->id.' ; '.$this->login.']';
	}

	public function getHasAccess(): ?bool
	{
		return $this->has_access;
	}

	public function setHasAccess(bool $has_access): self
	{
		$this->has_access = $has_access;
		return $this;
	}

}
