<?php

namespace App\Entity;

use App\Repository\CarRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: CarRepository::class)]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['car:read', 'car:list',  'carpooling_read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['car:read', 'car:write', 'carpooling_read'])]
    private ?string $model = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['car:read', 'car:write', 'carpooling_read'])]
    private ?string $color = null;

    #[ORM\Column(length: 50)]
    #[Groups(['car:read', 'car:write', 'carpooling_read'])]
    private ?string $licencePlate = null;

    #[ORM\Column(length: 50)]
    #[Groups(['car:read', 'car:write', 'carpooling_read'])]
    private ?string $energy = null;

    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    #[Groups(['car:read', 'car:write'])]
    private ?string $firstRegistrationDate = null;

    #[ORM\ManyToOne(inversedBy: 'cars', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[MaxDepth(1)]
    #[Groups(['car:read', 'car:write'])]
    private ?User $user = null;


    #[ORM\ManyToOne(inversedBy: 'cars')]
    #[ORM\JoinColumn(nullable: false)]
    #[MaxDepth(1)]
    #[Groups(['car:read', 'car:write', 'carpooling_read'])]
    private ?Brand $brand = null;

    /**
     * @var Collection<int, Carpooling>
     */
    #[ORM\OneToMany(targetEntity: Carpooling::class, mappedBy: 'car', orphanRemoval: true)]
    private Collection $carpoolings;

    #[ORM\Column]
    #[Groups(['car:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['car:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->carpoolings = new ArrayCollection();
    }

    // Getters et setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getLicencePlate(): ?string
    {
        return $this->licencePlate;
    }

    public function setLicencePlate(string $licencePlate): static
    {
        $this->licencePlate = $licencePlate;

        return $this;
    }

    public function getEnergy(): ?string
    {
        return $this->energy;
    }

    public function setEnergy(string $energy): static
    {
        $this->energy = $energy;

        return $this;
    }

    public function getFirstRegistrationDate(): ?string
    {
        return $this->firstRegistrationDate;
    }

    public function setFirstRegistrationDate(?string $firstRegistrationDate): static
    {
        $this->firstRegistrationDate = $firstRegistrationDate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return Collection<int, Carpooling>
     */
    public function getCarpoolings(): Collection
    {
        return $this->carpoolings;
    }

    public function addCarpooling(Carpooling $carpooling): static
    {
        if (!$this->carpoolings->contains($carpooling)) {
            $this->carpoolings->add($carpooling);
            $carpooling->setCar($this);
        }

        return $this;
    }

    public function removeCarpooling(Carpooling $carpooling): static
    {
        if ($this->carpoolings->removeElement($carpooling)) {
            // set the owning side to null (unless already changed)
            if ($carpooling->getCar() === $this) {
                $carpooling->setCar(null);
            }
        }

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

    public function isValidFirstRegistrationDate(): bool
    {
        if ($this->firstRegistrationDate === null) {
            return false;
        }

        $date = \DateTime::createFromFormat('d/m/Y', $this->firstRegistrationDate);

        return $date && $date->format('d/m/Y') === $this->firstRegistrationDate;
    }
}
