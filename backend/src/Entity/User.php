<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\Repository\UserRepository;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:minimal', 'review:read', 'car:read', 'carpooling:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:minimal', 'review:read', 'car:read', 'carpooling:read'])]
    private ?string $username = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['user:minimal', 'review:read'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['user:minimal'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:minimal'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Review::class)]
    // #[Groups(['user:reviews'])]
    #[MaxDepth(1)]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Car::class, orphanRemoval: true)]
    // #[Groups(['user:cars'])]
    #[MaxDepth(1)]
    private Collection $cars;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CarpoolingUser::class, orphanRemoval: true)]
    // #[Groups(['user:carpooling_associations'])]
    #[MaxDepth(1)]
    private Collection $carpoolingUsers;


    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->cars = new ArrayCollection();
        $this->carpoolingUsers = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
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
            $this->reviews[] = $review;
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
            if ($carpoolingUser->getUser() === $this) {
                $carpoolingUser->setUser(null);
            }
        }
        return $this;
    }
}
