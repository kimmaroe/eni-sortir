<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LocationRepository::class)
 */
class Location
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $streetNumber;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $streetName;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=7, nullable=true)
     */
    private $lat;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=7, nullable=true)
     */
    private $lng;

    /**
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="locations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $city;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(?string $streetNumber): self
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setStreetName(?string $streetName): self
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(?string $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?string
    {
        return $this->lng;
    }

    public function setLng(?string $lng): self
    {
        $this->lng = $lng;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
}
