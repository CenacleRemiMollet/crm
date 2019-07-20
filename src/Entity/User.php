<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @Assert\NotNull
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=1)
     */
    private $sex = "-";

    /**
     * @Assert\NotNull
     * @ORM\Column(type="date")
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $zipcode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $phone_emergency;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $nationality;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $mails;

    /**
     * @Assert\NotNull
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $blacklist_date;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $blacklist_reason;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Account", mappedBy="user")
     */
    private $accounts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserLessonSubscribe", mappedBy="user", orphanRemoval=true)
     */
    private $userLessonSubscribes;

    public function __construct()
    {
        //$this->login = new ArrayCollection();
        $this->accounts = new ArrayCollection();
        $this->created = new \DateTime();
        $this->userLessonSubscribes = new ArrayCollection(); 
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(string $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhoneEmergency(): ?string
    {
        return $this->phone_emergency;
    }

    public function setPhoneEmergency(?string $phone_emergency): self
    {
        $this->phone_emergency = $phone_emergency;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getMails(): ?string
    {
        return $this->mails;
    }

    public function setMails(?string $mails): self
    {
        $this->mails = $mails;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getBlacklistDate(): ?\DateTimeInterface
    {
        return $this->blacklist_date;
    }

    public function setBlacklistDate(?\DateTimeInterface $blacklist_date): self
    {
        $this->blacklist_date = $blacklist_date;

        return $this;
    }

    public function getBlacklistReason(): ?string
    {
        return $this->blacklist_reason;
    }

    public function setBlacklistReason(?string $blacklist_reason): self
    {
        $this->blacklist_reason = $blacklist_reason;

        return $this;
    }

    /**
     * @return Collection|Account[]
     */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function addAccount(Account $account): self
    {
        if (!$this->accounts->contains($account)) {
            $this->accounts[] = $account;
            $account->setUser($this);
        }

        return $this;
    }

    public function removeAccount(Account $account): self
    {
        if ($this->accounts->contains($account)) {
            $this->accounts->removeElement($account);
            // set the owning side to null (unless already changed)
            if ($account->getUser() === $this) {
                $account->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserLessonSubscribe[]
     */
    public function getUserLessonSubscribes(): Collection
    {
        return $this->userLessonSubscribes;
    }

    public function addUserLessonSubscribe(UserLessonSubscribe $userLessonSubscribe): self
    {
        if (!$this->userLessonSubscribes->contains($userLessonSubscribe)) {
            $this->userLessonSubscribes[] = $userLessonSubscribe;
            $userLessonSubscribe->setUser($this);
        }

        return $this;
    }

    public function removeUserLessonSubscribe(UserLessonSubscribe $userLessonSubscribe): self
    {
        if ($this->userLessonSubscribes->contains($userLessonSubscribe)) {
            $this->userLessonSubscribes->removeElement($userLessonSubscribe);
            // set the owning side to null (unless already changed)
            if ($userLessonSubscribe->getUser() === $this) {
                $userLessonSubscribe->setUser(null);
            }
        }

        return $this;
    }

}
