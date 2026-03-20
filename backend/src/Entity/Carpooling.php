<?php

namespace App\Entity;

use App\Repository\CarpoolingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use App\Enum\CarpoolingStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: CarpoolingRepository::class)]
class Carpooling
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['carpooling:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['carpooling:read'])]
    private ?\DateTime $departureDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['carpooling:read'])]
    private ?\DateTime $departureTime = null;

    #[ORM\Column(length: 50)]
    #[Groups(['carpooling:read'])]
    private ?string $departurePlace = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['carpooling:read'])]
    private ?\DateTime $arrivalDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['carpooling:read'])]
    private ?\DateTime $arrivalTime = null;

    #[ORM\Column(length: 50)]
    #[Groups(['carpooling:read'])]
    private ?string $arrivalPlace = null;

    #[ORM\Column]
    #[Groups(['carpooling:read'])]
    private ?int $seatCount = null;

    #[ORM\Column]
    #[Groups(['carpooling:read'])]
    private ?float $pricePerPerson = null;

    #[ORM\Column]
    #[Groups(['carpooling:read'])]
    private ?bool $isEco = null;

    #[ORM\ManyToOne(inversedBy: 'carpoolings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['carpooling:read'])]
    private ?Car $car = null;

    #[ORM\Column]
    #[Groups(['carpooling:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['carpooling:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'string', enumType: CarpoolingStatus::class)]
    #[Groups(['carpooling:read'])]
    private CarpoolingStatus $status = CarpoolingStatus::OPEN;

    /**
     * @var Collection<int, CarpoolingUser>
     */
    #[ORM\OneToMany(targetEntity: CarpoolingUser::class, mappedBy: 'carpooling', orphanRemoval: true, cascade: ['remove'])]
    #[Groups(['carpooling:read'])]
    private Collection $carpoolingUsers;

    public function __construct()
    {
        $this->carpoolingUsers = new ArrayCollection();
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

    public function getStatus(): CarpoolingStatus
    {
        return $this->status;
    }

    public function setStatus(CarpoolingStatus $status): static
    {
        $this->status = $status;
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
            $carpoolingUser->setCarpooling($this);
        }

        return $this;
    }

    public function removeCarpoolingUser(CarpoolingUser $carpoolingUser): static
    {
        if ($this->carpoolingUsers->removeElement($carpoolingUser)) {
            // set the owning side to null (unless already changed)
            if ($carpoolingUser->getCarpooling() === $this) {
                $carpoolingUser->setCarpooling(null);
            }
        }

        return $this;
    }

    public function addDriver(User $user): static
    {
        foreach ($this->carpoolingUsers as $cu) {
            if ($cu->getUser() === $user && $cu->isDriver()) {
                return $this;
            }
        }

        $carpoolingUser = new CarpoolingUser();
        $carpoolingUser->setUser($user);
        $carpoolingUser->setCarpooling($this);
        $carpoolingUser->setIsDriver(true);

        $this->addCarpoolingUser($carpoolingUser);

        return $this;
    }

    public function getDriver(): ?User
    {
        foreach ($this->carpoolingUsers as $carpoolingUser) {
            if ($carpoolingUser->isDriver()) {
                return $carpoolingUser->getUser();
            }
        }
        return null;
    }
}
