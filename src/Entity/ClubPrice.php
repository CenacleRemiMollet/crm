<?php

namespace App\Entity;

use App\Repository\ClubPriceRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Util\StringUtils;

/**
 * @ORM\Entity(repositoryClass=ClubPriceRepository::class)
 */
class ClubPrice
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
     * @ORM\Column(type="integer")
     */
    private $club_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $discipline;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price_child_1;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price_child_2;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price_child_3;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price_adult;

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
    
    public function getClubId(): ?int
    {
        return $this->club_id;
    }

    public function setClubId(int $club_id): self
    {
        $this->club_id = $club_id;

        return $this;
    }

    public function getDiscipline(): ?string
    {
        return $this->discipline;
    }

    public function setDiscipline(string $discipline): self
    {
        $this->discipline = $discipline;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = empty($category) ? null : $category;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = empty($comment) ? null : $comment;

        return $this;
    }

    public function getPriceChild1(): ?float
    {
        return $this->price_child_1;
    }

    public function setPriceChild1(float $price_child_1): self
    {
        $this->price_child_1 = $price_child_1 == 0 ? null : $price_child_1;

        return $this;
    }

    public function getPriceChild2(): ?float
    {
        return $this->price_child_2;
    }

    public function setPriceChild2(?float $price_child_2): self
    {
        $this->price_child_2 = $price_child_2 == 0 ? null : $price_child_2;

        return $this;
    }

    public function getPriceChild3(): ?float
    {
        return $this->price_child_3;
    }

    public function setPriceChild3(?float $price_child_3): self
    {
        $this->price_child_3 = $price_child_3 == 0 ? null : $price_child_3;

        return $this;
    }

    public function getPriceAdult(): ?float
    {
        return $this->price_adult;
    }

    public function setPriceAdult(?float $price_adult): self
    {
        $this->price_adult = $price_adult == 0 ? null : $price_adult;

        return $this;
    }
}
