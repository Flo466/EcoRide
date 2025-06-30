<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth; // Assure-toi que MaxDepth est importé

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_read', 'carpooling_read', 'car:read', 'car:write', 'review:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user_read', 'carpooling_read'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user_read'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['user_read'])]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Groups(['carpooling_read', 'user_read', 'review:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 50)]
    #[Groups(['carpooling_read', 'user_read', 'review:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['user_read'])]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user_read'])]
    private ?string $address = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user_read'])]
    private ?\DateTime $birthDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user_read', 'carpooling_read'])]
    private ?string $photo = null;

    #[ORM\Column(length: 50)]
    #[Groups(['user_read', 'carpooling_read', 'review:read'])]
    private ?string $userName = null;

    #[ORM\Column]
    #[Groups(['user_read'])]
    private ?int $credits = null;

    #[ORM\Column]
    #[Groups(['user_read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user_read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user_read'])]
    private ?string $apiToken = null;

    /**
     * @var Collection<int, Configuration>
     */
    #[ORM\OneToMany(targetEntity: Configuration::class, mappedBy: 'user', orphanRemoval: true)]
    #[Groups(['user_read'])]
    private Collection $configurations;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'user', orphanRemoval: true)]
    #[Groups(['user_read'])]
    private Collection $reviews;

    /**
     * @var Collection<int, Car>
     */
    #[ORM\OneToMany(targetEntity: Car::class, mappedBy: 'user', orphanRemoval: true)]
    #[Groups(['user_read'])]
    private Collection $cars;

    /**
     * @var Collection<int, CarpoolingUser>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CarpoolingUser::class)]
    private Collection $carpoolingUsers;

    // --- NOUVELLE PROPRIÉTÉ POUR LA VOITURE UTILISÉE ---
    #[ORM\ManyToOne(targetEntity: Car::class)] // C'est une ManyToOne
    #[ORM\JoinColumn(nullable: true)] // Peut être null si aucune voiture n'est spécifiée comme utilisée
    #[Groups(['user_read', 'carpooling_read'])] // Si tu veux sérialiser cette voiture avec l'utilisateur
    #[MaxDepth(1)] // Pour éviter les boucles infinies User -> Car
    private ?Car $usedCar = null;

    /** @throws \Exception */
    public function __construct()
    {
        $this->configurations = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->cars = new ArrayCollection();
        $this->carpoolingUsers = new ArrayCollection();
        // Pas besoin d'initialiser usedCar car ce n'est pas une collection
        $this->createdAt = new DateTimeImmutable(); // Initialisation de createdAt
        $this->credits = 0; // Valeur par défaut pour credits si nécessaire
        $this->roles = ['ROLE_USER']; // Rôle par défaut
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTime $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): static
    {
        $this->userName = $userName;

        return $this;
    }

    public function getCredits(): ?int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): static
    {
        $this->credits = $credits;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): static
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @return Collection<int, Configuration>
     */
    public function getConfigurations(): Collection
    {
        return $this->configurations;
    }

    public function addConfiguration(Configuration $configuration): static
    {
        if (!$this->configurations->contains($configuration)) {
            $this->configurations->add($configuration);
            $configuration->setUser($this);
        }

        return $this;
    }

    public function removeConfiguration(Configuration $configuration): static
    {
        if ($this->configurations->removeElement($configuration)) {
            if ($configuration->getUser() === $this) {
                $configuration->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Car>
     */
    public function getCars(): Collection
    {
        return $this->cars;
    }

    public function addCar(Car $car): static
    {
        if (!$this->cars->contains($car)) {
            $this->cars->add($car);
            $car->setUser($this);
        }

        return $this;
    }

    public function removeCar(Car $car): static
    {
        if ($this->cars->removeElement($car)) {
            if ($car->getUser() === $this) {
                $car->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CarpoolingUser>
     */
    public function getCarpoolingUsers(): Collection
    {
        return $this->carpoolingUsers;
    }

    public function addCarpoolingUser(CarpoolingUser $carpoolingUser): static
    {
        if (!$this->carpoolingUsers->contains($carpoolingUser)) {
            $this->carpoolingUsers->add($carpoolingUser);
            $carpoolingUser->setUser($this);
        }

        return $this;
    }

    public function removeCarpoolingUser(CarpoolingUser $carpoolingUser): static
    {
        if ($this->carpoolingUsers->removeElement($carpoolingUser)) {
            // set the owning side to null (unless already changed)
            if ($carpoolingUser->getUser() === $this) {
                $carpoolingUser->setUser(null);
            }
        }

        return $this;
    }

    public function getUsedCar(): ?Car
    {
        return $this->usedCar;
    }

    public function setUsedCar(?Car $usedCar): static
    {
        $this->usedCar = $usedCar;

        return $this;
    }
}
