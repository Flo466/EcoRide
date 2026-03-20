<?php

namespace App\Entity;

use App\Repository\CarpoolingUserRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: CarpoolingUserRepository::class)]
#[ORM\UniqueConstraint(fields: ["user", "carpooling"])] // <-- Ajoute cette ligne
class CarpoolingUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'carpoolingUsers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['carpooling:read'])]
    #[MaxDepth(1)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'carpoolingUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Carpooling $carpooling = null;

    #[ORM\Column]
    #[Groups(['carpooling:read'])]
    private ?bool $isDriver = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['carpooling:read'])]
    private ?bool $isCancelled = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCarpooling(): ?Carpooling
    {
        return $this->carpooling;
    }

    public function setCarpooling(?Carpooling $carpooling): static
    {
        $this->carpooling = $carpooling;

        return $this;
    }

    public function isDriver(): ?bool
    {
        return $this->isDriver;
    }

    public function setIsDriver(bool $isDriver): static
    {
        $this->isDriver = $isDriver;

        return $this;
    }

    public function isCancelled(): ?bool
    {
        return $this->isCancelled;
    }

    public function setIsCancelled(bool $isCancelled): static
    {
        $this->isCancelled = $isCancelled;

        return $this;
    }
}
