<?php

namespace App\Entity;

use App\Repository\SearchEventRepository;
use Doctrine\ORM\Mapping as ORM;

//ICI IL N'Y A PAS l'annotation @Entity, donc aucune table n'est créée pour cette classe
//la classe ne sert qu'au formulaire de recherche


class SearchEvent
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class)
     */
    private $campus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $keyword;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateEnd;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $includeRegistered = true;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $includeNotRegistered = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(?string $keyword): self
    {
        $this->keyword = $keyword;

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(?\DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?\DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getIncludeRegistered(): ?bool
    {
        return $this->includeRegistered;
    }

    public function setIncludeRegistered(?bool $includeRegistered): self
    {
        $this->includeRegistered = $includeRegistered;

        return $this;
    }

    public function getIncludeNotRegistered(): ?bool
    {
        return $this->includeNotRegistered;
    }

    public function setIncludeNotRegistered(?bool $includeNotRegistered): self
    {
        $this->includeNotRegistered = $includeNotRegistered;

        return $this;
    }

}
