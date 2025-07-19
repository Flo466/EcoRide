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
use Doctrine\DBAL\Types\Types;
use App\Enum\ReviewStatus;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'carpooling:read', 'car:read', 'car:write', 'review:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read', 'carpooling:read'])]
    private ?string $email = null;

    /**
     * @var list<string>
     */
    #[ORM\Column]
    #[Groups(['user:read'])]
    private array $roles = [];

    /**
     * @var string
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['carpooling:read', 'user:read', 'review:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['carpooling:read', 'user:read', 'review:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $address = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read'])]
    private ?\DateTime $birthDate = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $photo;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $photoMimeType = null;

    #[ORM\Column(length: 50)]
    #[Groups(['user:read', 'carpooling:read', 'review:read'])]
    private ?string $userName = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $credits = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read'])]
    private ?string $apiToken = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups(['user:read', 'user:write'])]
    private ?bool $isDriver = false;

    /**
     * @var Collection<int, Configuration>
     */
    #[ORM\OneToMany(targetEntity: Configuration::class, mappedBy: 'user', orphanRemoval: true)]
    #[Groups(['user:read'])]
    private Collection $configurations;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $reviews;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(mappedBy: 'reviewedUser', targetEntity: Review::class)]
    private Collection $receivedReviews;

    /**
     * @var Collection<int, Car>
     */
    #[ORM\OneToMany(targetEntity: Car::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $cars;

    /**
     * @var Collection<int, CarpoolingUser>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CarpoolingUser::class, orphanRemoval: true)]
    private Collection $carpoolingUsers;

    #[ORM\ManyToOne(targetEntity: Car::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['user:read', 'carpooling:read'])]
    private ?Car $usedCar = null;

    /** @throws \Exception */
    public function __construct()
    {
        $this->configurations = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->receivedReviews = new ArrayCollection();
        $this->cars = new ArrayCollection();
        $this->carpoolingUsers = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->credits = 0;
        $this->roles = ['ROLE_USER'];
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void {}

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
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

    /**
     * @return resource|string|null
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param resource|string|null $photo
     */
    public function setPhoto($photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    #[Groups(['user:read', 'carpooling:read'])]
    public function getPhotoBase64(): ?string
    {
        if ($this->photo === null) {
            return null;
        }

        $photoContent = null;
        if (is_resource($this->photo)) {
            fseek($this->photo, 0);
            $photoContent = stream_get_contents($this->photo);
        } elseif (is_string($this->photo)) {
            $photoContent = $this->photo;
        }

        if ($photoContent === false || $photoContent === '') {
            return null;
        }

        $mimeType = $this->photoMimeType ?? 'image/jpeg';

        return 'data:' . $mimeType . ';base64,' . base64_encode($photoContent);
    }

    public function getPhotoMimeType(): ?string
    {
        return $this->photoMimeType;
    }

    public function setPhotoMimeType(?string $photoMimeType): static
    {
        $this->photoMimeType = $photoMimeType;

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

    public function getUsedCar(): ?Car
    {
        return $this->usedCar;
    }

    public function setUsedCar(?Car $usedCar): static
    {
        $this->usedCar = $usedCar;

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

    public function isDriver(): ?bool
    {
        return $this->isDriver;
    }

    public function setDriver(bool $isDriver): static
    {
        $this->isDriver = $isDriver;

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
     * @return Collection<int, Review>
     */
    public function getReceivedReviews(): Collection
    {
        return $this->receivedReviews;
    }

    public function addReceivedReview(Review $review): static
    {
        if (!$this->receivedReviews->contains($review)) {
            $this->receivedReviews->add($review);
            $review->setReviewedUser($this);
        }

        return $this;
    }

    public function removeReceivedReview(Review $review): static
    {
        if ($this->receivedReviews->removeElement($review)) {
            if ($review->getReviewedUser() === $this) {
                $review->setReviewedUser(null);
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
            if ($carpoolingUser->getUser() === $this) {
                $carpoolingUser->setUser(null);
            }
        }

        return $this;
    }

    #[Groups(['user:read', 'carpooling:read', 'review:read'])]
    public function getAverageRating(): ?float
    {
        $approvedReviews = $this->receivedReviews->filter(function (Review $review) {
            return $review->getStatus() === \App\Enum\ReviewStatus::APPROVED;
        });

        if ($approvedReviews->isEmpty()) {
            return null;
        }

        $totalRating = array_sum(
            $approvedReviews->map(fn(Review $review) => $review->getRatting())->toArray()
        );

        return round($totalRating / $approvedReviews->count(), 1);
    }
}
