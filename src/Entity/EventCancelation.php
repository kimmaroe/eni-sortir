<?php

namespace App\Entity;

use App\Repository\EventCancelationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventCancelationRepository::class)
 */
class EventCancelation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Event::class, inversedBy="cancelation", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $event;

    /**
     * @ORM\Column(type="text")
     */
    private $reason;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCanceled;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getDateCanceled(): ?\DateTimeInterface
    {
        return $this->dateCanceled;
    }

    public function setDateCanceled(\DateTimeInterface $dateCanceled): self
    {
        $this->dateCanceled = $dateCanceled;

        return $this;
    }
}
