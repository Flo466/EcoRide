<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use App\Enum\ReviewStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth; // Garde si utilisé pour d'autres relations

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['review:read', 'review:write'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['review:read', 'review:write'])]
    private ?string $comment = null;

    #[ORM\Column]
    #[Groups(['review:read', 'review:write'])]
    private ?int $ratting = null; // Note: 'ratting' avec deux 't' comme dans tes données

    #[ORM\Column(type: 'string', enumType: ReviewStatus::class)]
    #[Groups(['review:read', 'review:write'])]
    private ReviewStatus $status;

    // Utilisateur qui a laissé le commentaire (l'auteur)
    #[ORM\ManyToOne(inversedBy: 'reviews')] // Assure-toi que l'entité User a bien une propriété 'reviews' qui fait référence à ces avis
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['review:read'])]
    #[MaxDepth(1)] // Pour éviter les boucles de sérialisation si User a des relations circulaires
    private ?User $user = null; // Reste 'user' comme tu l'as demandé

    // Utilisateur qui reçoit le commentaire (la cible, ex: le conducteur)
    #[ORM\ManyToOne(targetEntity: User::class)] // Fais référence à l'entité User
    #[ORM\JoinColumn(name: "reviewed_user_id", referencedColumnName: "id", nullable: false)] // Nouvelle colonne dans la base de données
    #[Groups(['review:read', 'review:write'])] // Ajout des groupes de sérialisation
    #[MaxDepth(1)] // Pour éviter les boucles de sérialisation
    private ?User $reviewedUser = null; // Nouvelle propriété

    #[ORM\Column]
    #[Groups(['review:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['review:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    public function getRatting(): ?int
    {
        return $this->ratting;
    }

    public function setRatting(int $ratting): static
    {
        $this->ratting = $ratting;
        return $this;
    }

    public function getStatus(): ReviewStatus
    {
        return $this->status;
    }

    public function setStatus(ReviewStatus $status): self
    {
        $this->status = $status;
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

    // Nouveaux Getters et Setters pour 'reviewedUser'
    public function getReviewedUser(): ?User
    {
        return $this->reviewedUser;
    }

    public function setReviewedUser(?User $reviewedUser): static
    {
        $this->reviewedUser = $reviewedUser;
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
