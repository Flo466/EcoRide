<?php

namespace App\Entity;

use App\Repository\CarpoolingUserRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarpoolingUserRepository::class)]
class CarpoolingUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Pas de groupe ici si l'ID n'est pas directement exposé dans ce contexte
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'carpoolingUsers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['carpooling:read'])] // Uniformisé avec le deux-points
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'carpoolingUsers')]
    #[ORM\JoinColumn(nullable: false)]
    // Pas de groupe ici si la relation carpooling n'est pas directement exposée
    private ?Carpooling $carpooling = null;

    #[ORM\Column]
    #[Groups(['carpooling:read'])] // Uniformisé avec le deux-points
    private ?bool $isDriver = null;

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
}
