<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

/**
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="Cet email est déjà associé à un compte !")
 */
class User implements UserInterface
{
    /**
     * @PrePersist()
     */
    public function prePersist()
    {
        $this->setIsActive(true);
        if (empty($this->getDateCreated())) {
            $this->setDateCreated(new \DateTime());
        }
    }

    /**
     * @PreUpdate()
     */
    public function preUpdate()
    {
        $this->setDateUpdated(new \DateTime());
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\Email(message="Cet email n'est pas valide !")
     * @Assert\NotBlank(message="Veuillez renseigner votre email !")
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * password validation is inside the form
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre nom de famille !")
     * @Assert\Length(
     *     min=2,
     *     minMessage="Votre nom de famille devrait avoir au moins {{ limit }} caractères !",
     *     max=50,
     *     maxMessage="Votre nom de famille devrait avoir maximum {{ limit }} caractères !"
     * )
     * @ORM\Column(type="string", length=50)
     */
    private $lastName;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre prénom !")
     * @Assert\Length(
     *     min=2,
     *     minMessage="Votre prénom devrait avoir au moins {{ limit }} caractères !",
     *     max=50,
     *     maxMessage="Votre prénom devrait avoir maximum {{ limit }} caractères !"
     * )
     * @ORM\Column(type="string", length=50)
     */
    private $firstName;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre téléphone !")
     * @Assert\Length(
     *     min=8,
     *     minMessage="Votre téléphone devrait avoir au moins {{ limit }} caractères !",
     *     max=20,
     *     maxMessage="Votre téléphone devrait avoir maximum {{ limit }} caractères !"
     * )
     * @Assert\Regex(pattern="/^[0-9\s.+-]{8,20}$/", match=true, message="Votre téléphone devrait contenir entre 8 et 20 chiffres, espaces . - et +")

     * @ORM\Column(type="string", length=20)
     */
    private $phone;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateUpdated;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $picture;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

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

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeInterface $dateUpdated): self
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }
}
