<?php

namespace App\Entity;

use App\Repository\CarpoolingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarpoolingRepository::class)]
class Carpooling
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $departureDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $departureTime = null;

    #[ORM\Column(length: 50)]
    private ?string $departurePlace = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $arrivalDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $arrivalTime = null;

    #[ORM\Column(length: 50)]
    private ?string $arrivalPlace = null;

    #[ORM\Column]
    private ?int $seatCount = null;

    #[ORM\Column]
    private ?float $pricePerPerson = null;

    #[ORM\Column]
    private ?bool $isEco = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'carpooling')]
    private Collection $user;

    #[ORM\ManyToOne(inversedBy: 'carpoolings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Car $car = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepartureDate(): ?\DateTime
    {
        return $this->departureDate;
    }

    public function setDepartureDate(\DateTime $departureDate): static
    {
        $this->departureDate = $departureDate;

        return $this;
    }

    public function getDepartureTime(): ?\DateTime
    {
        return $this->departureTime;
    }

    public function setDepartureTime(\DateTime $departureTime): static
    {
        $this->departureTime = $departureTime;

        return $this;
    }

    public function getDeparturePlace(): ?string
    {
        return $this->departurePlace;
    }

    public function setDeparturePlace(string $departurePlace): static
    {
        $this->departurePlace = $departurePlace;

        return $this;
    }

    public function getArrivalDate(): ?\DateTime
    {
        return $this->arrivalDate;
    }

    public function setArrivalDate(\DateTime $arrivalDate): static
    {
        $this->arrivalDate = $arrivalDate;

        return $this;
    }

    public function getArrivalTime(): ?\DateTime
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(\DateTime $arrivalTime): static
    {
        $this->arrivalTime = $arrivalTime;

        return $this;
    }

    public function getArrivalPlace(): ?string
    {
        return $this->arrivalPlace;
    }

    public function setArrivalPlace(string $arrivalPlace): static
    {
        $this->arrivalPlace = $arrivalPlace;

        return $this;
    }

    public function getSeatCount(): ?int
    {
        return $this->seatCount;
    }

    public function setSeatCount(int $seatCount): static
    {
        $this->seatCount = $seatCount;

        return $this;
    }

    public function getPricePerPerson(): ?float
    {
        return $this->pricePerPerson;
    }

    public function setPricePerPerson(float $pricePerPerson): static
    {
        $this->pricePerPerson = $pricePerPerson;

        return $this;
    }

    public function isEco(): ?bool
    {
        return $this->isEco;
    }

    public function setIsEco(bool $isEco): static
    {
        $this->isEco = $isEco;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): static
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->user->removeElement($user);

        return $this;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): static
    {
        $this->car = $car;

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
}
