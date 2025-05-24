<?php

namespace App\Entity;

use App\Repository\CarpoolingRepository;
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
}
