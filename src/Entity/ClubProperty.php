<?php

namespace App\Entity;

use App\Repository\ClubPropertyRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Util\StringUtils;

/**
 * @ORM\Entity(repositoryClass=ClubPropertyRepository::class)
 */
class ClubProperty
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Club", inversedBy="clubProperties", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $club;
    
    /**
     * @ORM\Column(type="string", length=128)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $value;

    
    public function __construct()
    {
        $this->uuid = StringUtils::random_str(16);
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
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

}
