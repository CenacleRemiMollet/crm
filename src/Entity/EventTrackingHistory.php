<?php

namespace App\Entity;

use App\Repository\EventTrackingHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Util\StringUtils;

/**
 * @ORM\Entity(repositoryClass=EventTrackingHistoryRepository::class)
 */
class EventTrackingHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $uuid;

    /**
     * @ORM\Column(type="datetime")
     */
    private $event_date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $modifier_name;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $modifier_login;

    /**
     * @ORM\Column(type="integer")
     */
    private $account_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $account_session_history_id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $event_name;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    public function __construct()
    {
        $this->uuid = StringUtils::random_str(16);
        $this->event_date = new \DateTime();
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

    public function getEventDate(): ?\DateTimeInterface
    {
        return $this->event_date;
    }

    public function setEventDate(\DateTimeInterface $event_date): self
    {
        $this->event_date = $event_date;

        return $this;
    }

    public function getModifierName(): ?string
    {
        return $this->modifier_name;
    }

    public function setModifierName(string $modifier_name): self
    {
        $this->modifier_name = $modifier_name;

        return $this;
    }

    public function getModifierLogin(): ?string
    {
        return $this->modifier_login;
    }

    public function setModifierLogin(string $modifier_login): self
    {
        $this->modifier_login = $modifier_login;

        return $this;
    }

    public function getAccountId(): ?int
    {
        return $this->account_id;
    }

    public function setAccountId(int $account_id): self
    {
        $this->account_id = $account_id;

        return $this;
    }

    public function getAccountSessionHistoryId(): ?int
    {
        return $this->account_session_history_id;
    }

    public function setAccountSessionHistoryId(int $account_session_history_id): self
    {
        $this->account_session_history_id = $account_session_history_id;

        return $this;
    }

    public function getEventName(): ?string
    {
        return $this->event_name;
    }

    public function setEventName(string $event_name): self
    {
        $this->event_name = $event_name;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }
}
