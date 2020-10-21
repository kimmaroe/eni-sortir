<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateStart;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateEnd;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxRegistrations;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="organizedEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creator;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\ManyToOne(targetEntity=EventState::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $state;

    /**
     * @ORM\OneToMany(targetEntity=Registration::class, mappedBy="event")
     */
    private $registrations;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateRegistrationEnded;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\ManyToOne(targetEntity=Location::class)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity=City::class)
     */
    private $city;

    /**
     * @ORM\OneToOne(targetEntity=EventCancelation::class, mappedBy="event", cascade={"persist", "remove"})
     */
    private $cancelation;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDateStart(): ?\DateTime
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTime $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTime
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTime $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getMaxRegistrations(): ?int
    {
        return $this->maxRegistrations;
    }

    public function setMaxRegistrations(int $maxRegistrations): self
    {
        $this->maxRegistrations = $maxRegistrations;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getDateCreated(): ?\DateTime
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTime $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateUpdated(): ?\DateTime
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTime $dateUpdated): self
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getState(): ?EventState
    {
        return $this->state;
    }

    public function setState(?EventState $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Collection|Registration[]
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): self
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations[] = $registration;
            $registration->setEvent($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): self
    {
        if ($this->registrations->contains($registration)) {
            $this->registrations->removeElement($registration);
            // set the owning side to null (unless already changed)
            if ($registration->getEvent() === $this) {
                $registration->setEvent(null);
            }
        }

        return $this;
    }

    public function getDateRegistrationEnded(): ?\DateTime
    {
        return $this->dateRegistrationEnded;
    }

    public function setDateRegistrationEnded(\DateTime $dateRegistrationEnded): self
    {
        $this->dateRegistrationEnded = $dateRegistrationEnded;

        return $this;
    }

    public function isRegistered(User $user): bool
    {
        foreach($this->getRegistrations() as $registration){
            if ($registration->getUser() === $user){
                return true;
            }
        }

        return false;
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

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

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

    public function getCancelation(): ?EventCancelation
    {
        return $this->cancelation;
    }

    public function setCancelation(EventCancelation $cancelation): self
    {
        $this->cancelation = $cancelation;

        // set the owning side of the relation if necessary
        if ($cancelation->getEvent() !== $this) {
            $cancelation->setEvent($this);
        }

        return $this;
    }

    public function getCssClass(User $user)
    {
        $class = "white-card";
        $stateName = $this->getState()->getName();
        if ($stateName === EventState::CLOSED || $stateName === EventState::CANCELED){
            $class = "red-card";
        }
        elseif($stateName === EventState::OPEN || $this->isRegistered($user)){
            $class = "green-card";
        }
        return $class;
    }
}
