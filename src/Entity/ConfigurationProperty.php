<?php

namespace App\Entity;

use App\Repository\ConfigurationPropertyRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConfigurationPropertyRepository::class)
 * @ORM\Table(
 *	  indexes={@ORM\Index(name="idx_configuration_property_key", columns={"property_key"})},
 *	  uniqueConstraints={@ORM\UniqueConstraint(columns={"property_key"})})
 */
class ConfigurationProperty
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $property_key;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $property_value;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $updater_user_id;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $previous_value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPropertyKey(): ?string
    {
        return $this->property_key;
    }

    public function setPropertyKey(string $property_key): self
    {
        $this->property_key = $property_key;

        return $this;
    }

    public function getPropertyValue(): ?string
    {
        return $this->property_value;
    }

    public function setPropertyValue(string $property_value): self
    {
        $this->property_value = $property_value;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updated_date;
    }

    public function setUpdatedDate(\DateTimeInterface $updated_date): self
    {
        $this->updated_date = $updated_date;

        return $this;
    }

    public function getUpdaterUserId(): ?int
    {
        return $this->updater_user_id;
    }

    public function setUpdaterUserId(?int $updater_user_id): self
    {
        $this->updater_user_id = $updater_user_id;

        return $this;
    }

    public function getPreviousValue(): ?string
    {
        return $this->previous_value;
    }

    public function setPreviousValue(string $previous_value): self
    {
        $this->previous_value = $previous_value;

        return $this;
    }
}
